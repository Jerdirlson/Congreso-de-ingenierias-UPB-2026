<?php

use App\Models\AppSetting;
use App\Models\Submission;
use App\Models\SubmissionVideo;
use App\Services\SubmissionTrail;
use Illuminate\Database\Migrations\Migration;

/**
 * Adapta las ponencias existentes al nuevo flujo de videoponencia por link:
 *
 * 1. Las que subieron un archivo pero **todavía no lo tenían aceptado** vuelven
 *    al paso anterior (`video_pending`) para que compartan su enlace. El archivo
 *    subido NO se borra: la fila conserva `stored_path` y queda constancia
 *    en la bitácora.
 * 2. El envío de videoponencias queda DESACTIVADO, para que el comité lo
 *    abra desde el panel de administración cuando envíe las indicaciones.
 *
 * ⚠️ A quien **ya entregó** (video `ready` y ponencia `confirmed`/`video_ready`)
 * NO se le toca el estado. La versión original de esta migración sí lo hacía, y
 * el efecto real medido en producción el 12-ago-2026 habría sido devolver 4
 * ponencias confirmadas a "falta el video" **con el envío apagado**: quedaban
 * bloqueadas sin poder hacer nada. Se les deja el estado intacto y solo se
 * registra en la bitácora que hay que pedirles el link; son pocas y el comité
 * las contacta una a una cuando abra el flujo.
 */
return new class extends Migration
{
    /** Ya entregó: el comité no debe reabrirle el paso del video. */
    private function yaEntregado(SubmissionVideo $video): bool
    {
        if ($video->status !== SubmissionVideo::STATUS_READY) {
            return false;
        }

        $submission = $video->submission;

        return $submission !== null && in_array($submission->status, [
            Submission::STATUS_CONFIRMED,
            Submission::STATUS_VIDEO_READY,
        ], true);
    }

    public function up(): void
    {
        SubmissionVideo::query()
            ->whereNull('youtube_url')
            ->whereNotNull('stored_path')
            ->get()
            ->each(function (SubmissionVideo $video) {
                if ($this->yaEntregado($video)) {
                    SubmissionTrail::log($video->submission_id, 'video_link_pendiente_de_pedir', [
                        'motivo'          => 'Entregó el archivo antes del cambio a link de YouTube; conserva su estado.',
                        'archivo_previo'  => $video->original_filename,
                        'ruta_conservada' => $video->stored_path,
                    ]);

                    return;
                }

                $video->update([
                    'status'        => SubmissionVideo::STATUS_PENDING,
                    'ready_at'      => null,
                    'error_message' => null,
                ]);

                SubmissionTrail::log($video->submission_id, 'video_link_requerido', [
                    'motivo'          => 'La videoponencia pasa de archivo subido a link de YouTube.',
                    'archivo_previo'  => $video->original_filename,
                    'ruta_conservada' => $video->stored_path,
                ]);

                $submission = $video->submission;

                if ($submission && in_array($submission->status, [
                    Submission::STATUS_CONFIRMED,
                    Submission::STATUS_VIDEO_READY,
                ], true)) {
                    $submission->advanceTo(Submission::STATUS_VIDEO_PENDING);
                }
            });

        AppSetting::setBool(AppSetting::VIDEO_UPLOAD_OPEN, false);
    }

    public function down(): void
    {
        // Irreversible a propósito: no hay forma de distinguir después qué
        // ponencias se devolvieron a `video_pending` por este cambio.
        // La bitácora `submission_events` conserva el registro.
    }
};
