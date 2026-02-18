<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Speaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpeakerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $speakers = Speaker::query()
            ->when($request->event, fn ($q, $e) => $q->whereHas('events', fn ($q) => $q->where('events.id', $e)))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                                                       ->orWhere('institution', 'like', "%{$s}%"))
            ->withCount('streams')
            ->orderBy('name')
            ->paginate(20);

        return response()->json($speakers);
    }

    public function show(Speaker $speaker): JsonResponse
    {
        $speaker->load(['events:id,title,start_date', 'streams:id,title,status,scheduled_at']);
        return response()->json($speaker);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email',
            'institution' => 'nullable|string',
            'country'     => 'nullable|string|max:2',
            'bio'         => 'nullable|string',
            'linkedin'    => 'nullable|url',
            'orcid'       => 'nullable|string',
            'user_id'     => 'nullable|exists:users,id',
        ]);

        $speaker = Speaker::create($validated);

        return response()->json($speaker, 201);
    }

    public function update(Request $request, Speaker $speaker): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'email'       => 'nullable|email',
            'institution' => 'nullable|string',
            'country'     => 'nullable|string|max:2',
            'bio'         => 'nullable|string',
            'linkedin'    => 'nullable|url',
            'orcid'       => 'nullable|string',
        ]);

        $speaker->update($validated);

        return response()->json($speaker);
    }

    public function destroy(Speaker $speaker): JsonResponse
    {
        $speaker->delete();
        return response()->json(null, 204);
    }
}
