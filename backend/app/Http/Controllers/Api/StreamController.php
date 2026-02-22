<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stream;
use App\Services\CloudflareStreamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StreamController extends Controller
{
    public function __construct(
        private CloudflareStreamService $cloudflare,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $streams = Stream::query()
            ->when($request->status,   fn ($q, $s) => $q->where('status', $s))
            ->when($request->event_id, fn ($q, $e) => $q->where('event_id', $e))
            ->with(['event:id,title', 'speaker:id,name,photo', 'creator:id,name'])
            ->orderBy('scheduled_at')
            ->paginate(15);

        $streams->getCollection()->transform(fn ($s) => $this->appendPlayback($s));

        return response()->json($streams);
    }

    public function show(Stream $stream): JsonResponse
    {
        $stream->load(['event', 'speaker', 'creator', 'recordings' => fn ($q) => $q->ready()->public()]);

        return response()->json($this->appendPlayback($stream));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'slug'         => 'nullable|string|unique:streams',
            'description'  => 'nullable|string',
            'event_id'     => 'nullable|exists:events,id',
            'speaker_id'   => 'nullable|exists:speakers,id',
            'scheduled_at' => 'required|date',
            'type'         => 'in:live,recorded,external',
            'platform'     => 'nullable|string|in:cloudflare,youtube,custom',
            'platform_url' => 'nullable|url',
            'chat_enabled' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['created_by'] = auth()->id();

        $platform = $validated['platform'] ?? 'cloudflare';

        if ($platform === 'cloudflare' && $this->cloudflare->isConfigured()) {
            $liveInput = $this->cloudflare->createLiveInput($validated['title']);

            if (! $liveInput) {
                return response()->json([
                    'message' => 'No se pudo crear el Live Input en Cloudflare. Verifica las credenciales.',
                ], 502);
            }

            $validated['platform']       = 'cloudflare';
            $validated['cloudflare_uid'] = $liveInput['uid'];
            $validated['stream_key']     = $liveInput['uid'];
            $validated['rtmp_url']       = $liveInput['rtmps']['url'] ?? $liveInput['rtmpsPlayback']['url'] ?? null;
            $validated['cloudflare_meta'] = [
                'rtmps_url'       => $liveInput['rtmps']['url'] ?? null,
                'rtmps_stream_key' => $liveInput['rtmps']['streamKey'] ?? null,
                'srt_url'         => $liveInput['srt']['url'] ?? null,
                'srt_passphrase'  => $liveInput['srt']['passphrase'] ?? null,
                'webrtc_url'      => $liveInput['webRTC']['url'] ?? null,
            ];
        } else {
            $validated['stream_key'] = Stream::generateStreamKey();
        }

        $stream = Stream::create($validated);

        $response = $this->appendPlayback($stream);

        if ($stream->isCloudflare()) {
            $response->ingest_credentials = [
                'rtmps_url'        => $stream->cloudflare_meta['rtmps_url'] ?? null,
                'rtmps_stream_key' => $stream->cloudflare_meta['rtmps_stream_key'] ?? null,
                'srt_url'          => $stream->cloudflare_meta['srt_url'] ?? null,
                'srt_passphrase'   => $stream->cloudflare_meta['srt_passphrase'] ?? null,
            ];
        }

        return response()->json($response, 201);
    }

    public function update(Request $request, Stream $stream): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'status'       => 'sometimes|in:scheduled,live,ended,cancelled',
            'scheduled_at' => 'sometimes|date',
            'platform'     => 'nullable|string',
            'platform_url' => 'nullable|url',
            'chat_enabled' => 'sometimes|boolean',
        ]);

        if (isset($validated['title']) && $stream->isCloudflare()) {
            $this->cloudflare->updateLiveInput($stream->cloudflare_uid, [
                'meta' => ['name' => $validated['title']],
            ]);
        }

        $stream->update($validated);

        return response()->json($this->appendPlayback($stream));
    }

    public function goLive(Stream $stream): JsonResponse
    {
        abort_if($stream->status !== 'scheduled', 422, 'Stream is not in scheduled state.');

        $stream->goLive();

        if ($stream->isCloudflare()) {
            $videos = $this->cloudflare->listVideos($stream->cloudflare_uid);
            if ($videos && count($videos) > 0) {
                $latest = $videos[count($videos) - 1];
                $stream->update([
                    'cloudflare_video_uid' => $latest['uid'] ?? null,
                    'hls_url'              => $latest['playback']['hls'] ?? null,
                ]);
            }
        }

        return response()->json($this->appendPlayback($stream->fresh()));
    }

    public function end(Stream $stream): JsonResponse
    {
        abort_if($stream->status !== 'live', 422, 'Stream is not live.');

        $stream->end();

        return response()->json($this->appendPlayback($stream));
    }

    /**
     * GET /api/streams/{stream}/credentials
     * Returns the ingest credentials (only for admins).
     */
    public function credentials(Stream $stream): JsonResponse
    {
        if (! $stream->isCloudflare()) {
            return response()->json([
                'message' => 'Este stream no utiliza Cloudflare.',
            ], 422);
        }

        return response()->json([
            'rtmps_url'        => $stream->cloudflare_meta['rtmps_url'] ?? null,
            'rtmps_stream_key' => $stream->cloudflare_meta['rtmps_stream_key'] ?? null,
            'srt_url'          => $stream->cloudflare_meta['srt_url'] ?? null,
            'srt_passphrase'   => $stream->cloudflare_meta['srt_passphrase'] ?? null,
            'webrtc_url'       => $stream->cloudflare_meta['webrtc_url'] ?? null,
        ]);
    }

    public function destroy(Stream $stream): JsonResponse
    {
        if ($stream->isCloudflare()) {
            $this->cloudflare->deleteLiveInput($stream->cloudflare_uid);
        }

        $stream->delete();

        return response()->json(null, 204);
    }

    // ── Private ─────────────────────────────────────────────────────────────

    private function appendPlayback(Stream $stream): Stream
    {
        $stream->playback_url = $stream->playback_url;

        if ($stream->isCloudflare() && $stream->cloudflare_uid) {
            $stream->iframe_url = "https://iframe.videodelivery.net/{$stream->cloudflare_uid}";
        }

        return $stream;
    }
}
