<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $events = Event::query()
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->upcoming, fn ($q) => $q->upcoming())
            ->with(['speakers:id,name,institution,photo'])
            ->orderBy('start_date')
            ->paginate(15);

        return response()->json($events);
    }

    public function show(Event $event): JsonResponse
    {
        $event->load(['speakers', 'documents.category', 'streams']);
        return response()->json($event);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'slug'             => 'required|string|unique:events',
            'status'           => 'in:draft,published,cancelled,finished',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'location'         => 'nullable|string',
            'venue'            => 'nullable|string',
            'max_participants' => 'nullable|integer|min:1',
        ]);

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'title'            => 'sometimes|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'sometimes|in:draft,published,cancelled,finished',
            'start_date'       => 'sometimes|date',
            'end_date'         => 'sometimes|date',
            'location'         => 'nullable|string',
            'venue'            => 'nullable|string',
            'max_participants' => 'nullable|integer|min:1',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();
        return response()->json(null, 204);
    }
}
