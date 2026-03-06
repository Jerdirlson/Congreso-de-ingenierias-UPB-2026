<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CongressEvent;
use Illuminate\Http\JsonResponse;

class CongressEventController extends Controller
{
    /** GET /api/events — listar eventos activos del congreso */
    public function index(): JsonResponse
    {
        $events = CongressEvent::where('is_active', true)
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get()
            ->map(fn($e) => [
                'id'          => $e->id,
                'name'        => $e->name,
                'description' => $e->description,
                'location'    => $e->location,
                'modality'    => $e->modality,
                'event_date'  => $e->event_date?->toDateString(),
                'start_time'  => $e->start_time,
                'end_time'    => $e->end_time,
                'speaker'     => $e->speaker,
                'category'    => $e->category,
                'capacity'    => $e->capacity,
                'is_free'     => $e->is_free,
                'price'       => $e->is_free ? 0 : (float) $e->price,
                'currency'    => $e->currency,
                'is_full'     => $e->isFull(),
                'registered_count' => $e->registrationCount(),
            ]);

        return response()->json($events);
    }
}
