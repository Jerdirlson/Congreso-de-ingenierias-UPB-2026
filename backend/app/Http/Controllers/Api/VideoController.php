<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SubmissionConfirmedMail;
use App\Models\AppSetting;
use App\Models\Submission;
use App\Models\SubmissionVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VideoController extends Controller
{
    /** POST /api/submissions/{submission}/videos — guarda el link de YouTube de la videoponencia */
    public function store(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);

        // El envío está en pausa hasta que el comité publique las indicaciones.
        abort_if(
            ! AppSetting::getBool(AppSetting::VIDEO_UPLOAD_OPEN, true),
            422,
            'El envío de videoponencias está temporalmente en pausa. Te avisaremos por correo cuando publiquemos las indicaciones del formato.'
        );

        abort_if(
            $submission->status !== Submission::STATUS_VIDEO_PENDING,
            422,
            'Solo se puede enviar el link del video cuando la ponencia está en estado video_pending.'
        );

        $validated = $request->validate([
            'youtube_url' => ['required', 'string', 'max:500'],
        ], [
            'youtube_url.required' => 'Debes pegar el enlace de YouTube de tu videoponencia.',
        ]);

        $youtubeId = self::extractYoutubeId($validated['youtube_url']);

        abort_if(
            $youtubeId === null,
            422,
            'El enlace no parece ser de YouTube. Debe verse así: https://www.youtube.com/watch?v=… o https://youtu.be/…'
        );

        $canonicalUrl = "https://www.youtube.com/watch?v={$youtubeId}";

        abort_if(
            ! self::isPubliclyEmbeddable($canonicalUrl),
            422,
            'No pudimos abrir ese video: parece que es privado o que el enlace ya no existe. En YouTube ponlo en "No listado (Unlisted)" y vuelve a intentarlo.'
        );

        // Trazabilidad: si ya había un archivo o un link anterior, no se borra
        // nada; el cambio queda registrado en la bitácora (submission_events).
        $video = SubmissionVideo::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'youtube_url'   => $canonicalUrl,
                'status'        => SubmissionVideo::STATUS_READY,
                'uploaded_at'   => now(),
                'ready_at'      => now(),
                'error_message' => null,
            ]
        );

        // Compartir el link confirma la ponencia (antes lo hacía ProcessVideoUploadJob).
        $submission->advanceTo(Submission::STATUS_CONFIRMED);

        try {
            Mail::to($submission->user->email)->send(new SubmissionConfirmedMail($submission));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el correo de confirmación', [
                'submission_id' => $submission->id,
                'error'         => $e->getMessage(),
            ]);
        }

        return response()->json([
            'id'          => $video->id,
            'status'      => $video->status,
            'youtube_url' => $video->youtube_url,
        ], 201);
    }

    /** GET /api/submissions/{submission}/videos/status — estado del video */
    public function status(Submission $submission): JsonResponse
    {
        $this->authorize('view', $submission);

        $video = SubmissionVideo::where('submission_id', $submission->id)->first();

        if (! $video) {
            return response()->json(['status' => null]);
        }

        return response()->json([
            'id'                => $video->id,
            'status'            => $video->status,
            'youtube_url'       => $video->youtube_url,
            'original_filename' => $video->original_filename,
            'error_message'     => $video->error_message,
        ]);
    }

    /**
     * Extrae el ID de 11 caracteres de cualquier forma de enlace de YouTube
     * (watch, youtu.be, embed, live, shorts). Devuelve null si no es YouTube.
     */
    private static function extractYoutubeId(string $url): ?string
    {
        $patterns = [
            '~youtu\.be/([A-Za-z0-9_-]{11})~',
            '~youtube\.com/watch\?(?:[^\s]*&)?v=([A-Za-z0-9_-]{11})~',
            '~youtube(?:-nocookie)?\.com/(?:embed|live|shorts|v)/([A-Za-z0-9_-]{11})~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($url), $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Comprueba con el oEmbed público de YouTube que el video exista y se
     * pueda insertar. Si la consulta falla por red, se deja pasar: no vale la
     * pena bloquear al ponente por un problema nuestro.
     */
    private static function isPubliclyEmbeddable(string $url): bool
    {
        try {
            return Http::timeout(6)
                ->get('https://www.youtube.com/oembed', ['url' => $url, 'format' => 'json'])
                ->successful();
        } catch (\Throwable $e) {
            Log::warning('No se pudo verificar el link de YouTube', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            return true;
        }
    }
}
