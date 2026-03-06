<?php

namespace App\Jobs;

use App\Mail\SubmissionConfirmedMail;
use App\Models\SubmissionVideo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProcessVideoUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 2;

    public function __construct(
        private readonly SubmissionVideo $video
    ) {}

    public function handle(): void
    {
        $video = $this->video->fresh();

        if (! $video || $video->status !== SubmissionVideo::STATUS_PROCESSING) {
            return;
        }

        if (! $video->stored_path || ! Storage::disk('local')->exists($video->stored_path)) {
            $this->markError('Archivo de video no encontrado en el servidor.');
            return;
        }

        $video->update([
            'status'   => SubmissionVideo::STATUS_READY,
            'ready_at' => now(),
        ]);

        $submission = $video->submission()->with(['user', 'thematicAxis'])->first();

        if ($submission && in_array($submission->status, ['video_pending', 'video_ready'])) {
            $submission->advanceTo('confirmed');

            // Enviar correo de confirmación al ponente
            try {
                Mail::to($submission->user->email)->send(new SubmissionConfirmedMail($submission));
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar el correo de confirmación', [
                    'submission_id' => $submission->id,
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        Log::info('Video procesado y ponencia confirmada', ['submission_video_id' => $video->id]);
    }

    public function failed(\Throwable $e): void
    {
        $this->markError($e->getMessage());
    }

    private function markError(string $message): void
    {
        $this->video->update([
            'status'        => SubmissionVideo::STATUS_ERROR,
            'error_message' => $message,
        ]);

        Log::error('Error procesando video', [
            'submission_video_id' => $this->video->id,
            'message'             => $message,
        ]);
    }
}
