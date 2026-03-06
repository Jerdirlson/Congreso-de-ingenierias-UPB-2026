<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /** GET /api/registrations — mis inscripciones (participante o ponente) */
    public function index(Request $request): JsonResponse
    {
        $registrations = $request->user()
            ->registrations()
            ->with([
                'payment:id,amount,currency,status,paid_at',
                'submission:id,title,status',
                'congressEvent:id,name,event_date',
            ])
            ->orderByDesc('confirmed_at')
            ->get();

        return response()->json($registrations);
    }
}
