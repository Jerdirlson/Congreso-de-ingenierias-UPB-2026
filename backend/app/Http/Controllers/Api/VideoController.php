<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessVideoUploadJob;
use App\Models\Submission;
use App\Models\SubmissionVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /** POST /api/submissions/{submission}/videos — sube el archivo de video */
    public function store(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);

        abort_if(
            $submission->status !== Submission::STATUS_VIDEO_PENDING,
            422,
            'Solo se puede subir video cuando la ponencia está en estado video_pending.'
        );

        $request->validate([
            'file' => ['required', 'file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm,video/mpeg', 'max:2097152'], // 2 GB
        ], [
            'file.required'  => 'Debes seleccionar un archivo de video.',
            'file.mimetypes' => 'El archivo debe ser un video (mp4, mov, avi, webm).',
            'file.max'       => 'El video no puede superar los 2 GB.',
        ]);

        $file = $request->file('file');
        $dir  = "submission_videos/{$submission->id}";

        // Remove any previous video file
        $existing = SubmissionVideo::where('submission_id', $submission->id)->first();
        if ($existing?->stored_path) {
            Storage::disk('local')->delete($existing->stored_path);
        }

        $path = Storage::disk('local')->putFile($dir, $file);

        $video = SubmissionVideo::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'stored_path'       => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type'         => $file->getMimeType(),
                'file_size'         => $file->getSize(),
                'status'            => SubmissionVideo::STATUS_PROCESSING,
                'uploaded_at'       => now(),
                'ready_at'          => null,
                'error_message'     => null,
                'cloudflare_uid'    => null,
                'cloudflare_playback_url' => null,
            ]
        );

        ProcessVideoUploadJob::dispatch($video);

        return response()->json([
            'id'     => $video->id,
            'status' => $video->status,
        ], 201);
    }

    /** GET /api/submissions/{submission}/videos/status — estado del video (polling) */
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
            'original_filename' => $video->original_filename,
            'error_message'     => $video->error_message,
        ]);
    }
}
