<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stream;
use App\Services\CloudflareStreamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CloudflareWebhookController extends Controller
{
    public function __construct(
        private CloudflareStreamService $cloudflare,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('Webhook-Signature', '');
        $payload   = $request->getContent();

        if (config('services.cloudflare_stream.webhook_secret')
            && ! $this->cloudflare->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Cloudflare webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $data       = $request->all();
        $liveInputUid = $data['liveInput'] ?? null;
        $videoUid     = $data['uid'] ?? null;
        $status       = $data['status'] ?? null;

        Log::info('Cloudflare webhook received', [
            'live_input' => $liveInputUid,
            'video_uid'  => $videoUid,
            'status'     => $status,
        ]);

        if (! $liveInputUid) {
            return response()->json(['ok' => true]);
        }

        $stream = Stream::where('cloudflare_uid', $liveInputUid)->first();

        if (! $stream) {
            Log::warning('Cloudflare webhook: no matching stream', ['uid' => $liveInputUid]);
            return response()->json(['ok' => true]);
        }

        $statusState = $status['state'] ?? null;

        match ($statusState) {
            'live-inprogress' => $this->handleLiveStart($stream, $videoUid),
            'ready'           => $this->handleReady($stream, $videoUid, $data),
            'error'           => $this->handleError($stream, $data),
            default           => null,
        };

        return response()->json(['ok' => true]);
    }

    private function handleLiveStart(Stream $stream, ?string $videoUid): void
    {
        $updates = ['cloudflare_video_uid' => $videoUid];

        if ($stream->status === 'scheduled') {
            $updates['status']     = 'live';
            $updates['started_at'] = now();
        }

        if ($videoUid) {
            $updates['hls_url'] = "https://iframe.videodelivery.net/{$videoUid}";
        }

        $stream->update($updates);

        Log::info('Stream went live via webhook', ['stream_id' => $stream->id]);
    }

    private function handleReady(Stream $stream, ?string $videoUid, array $data): void
    {
        if ($videoUid) {
            $stream->update([
                'cloudflare_video_uid' => $videoUid,
                'hls_url' => $data['playback']['hls'] ?? $stream->hls_url,
            ]);
        }

        if ($stream->status === 'live') {
            $stream->update([
                'status'   => 'ended',
                'ended_at' => now(),
            ]);
        }

        Log::info('Stream recording ready', ['stream_id' => $stream->id, 'video_uid' => $videoUid]);
    }

    private function handleError(Stream $stream, array $data): void
    {
        Log::error('Cloudflare stream error', [
            'stream_id' => $stream->id,
            'error'     => $data['status']['errorReasonCode'] ?? 'unknown',
            'reason'    => $data['status']['errorReasonText'] ?? '',
        ]);
    }
}
