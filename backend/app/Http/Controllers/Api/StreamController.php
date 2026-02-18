<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StreamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $streams = Stream::query()
            ->when($request->status,   fn ($q, $s) => $q->where('status', $s))
            ->when($request->event_id, fn ($q, $e) => $q->where('event_id', $e))
            ->with(['event:id,title', 'speaker:id,name,photo', 'creator:id,name'])
            ->orderBy('scheduled_at')
            ->paginate(15);

        return response()->json($streams);
    }

    public function show(Stream $stream): JsonResponse
    {
        $stream->load(['event', 'speaker', 'creator', 'recordings' => fn ($q) => $q->ready()->public()]);
        return response()->json($stream);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'slug'         => 'required|string|unique:streams',
            'description'  => 'nullable|string',
            'event_id'     => 'nullable|exists:events,id',
            'speaker_id'   => 'nullable|exists:speakers,id',
            'scheduled_at' => 'required|date',
            'type'         => 'in:live,recorded,external',
            'platform'     => 'nullable|string',
            'platform_url' => 'nullable|url',
            'chat_enabled' => 'boolean',
        ]);

        $stream = Stream::create([
            ...$validated,
            'created_by' => auth()->id(),
            'stream_key' => Stream::generateStreamKey(),
        ]);

        return response()->json($stream, 201);
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

        $stream->update($validated);

        return response()->json($stream);
    }

    public function goLive(Stream $stream): JsonResponse
    {
        abort_if($stream->status !== 'scheduled', 422, 'Stream is not in scheduled state.');

        $stream->goLive();

        return response()->json($stream);
    }

    public function end(Stream $stream): JsonResponse
    {
        abort_if($stream->status !== 'live', 422, 'Stream is not live.');

        $stream->end();

        return response()->json($stream);
    }

    public function destroy(Stream $stream): JsonResponse
    {
        $stream->delete();
        return response()->json(null, 204);
    }
}
