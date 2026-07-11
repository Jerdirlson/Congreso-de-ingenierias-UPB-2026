<?php

namespace App\Services;

use App\Models\SubmissionEvent;
use Illuminate\Support\Facades\Log;

/**
 * Punto único para registrar eventos de trazabilidad de una ponencia.
 * Nunca debe romper el flujo principal: si el log falla, se registra
 * en el log de la app y la operación del usuario continúa.
 */
class SubmissionTrail
{
    public static function log(int $submissionId, string $event, array $details = [], ?\DateTimeInterface $at = null): void
    {
        try {
            SubmissionEvent::create([
                'submission_id' => $submissionId,
                'user_id'       => auth('sanctum')->id() ?? auth()->id(),
                'event'         => $event,
                'details'       => $details ?: null,
                'created_at'    => $at ?? now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("SubmissionTrail: no se pudo registrar '{$event}' para submission {$submissionId}: " . $e->getMessage());
        }
    }
}
