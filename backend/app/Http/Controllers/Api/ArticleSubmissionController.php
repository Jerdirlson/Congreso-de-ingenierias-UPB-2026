<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Submission;
use App\Models\SubmissionArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArticleSubmissionController extends Controller
{
    /**
     * Estados desde los que el ponente puede optar por publicación en revista:
     * desde que su resumen fue aprobado, en adelante (carril opcional paralelo).
     * Incluye los estados del antiguo paso 2 por compatibilidad.
     */
    private const ALLOWED_STATUSES = [
        Submission::STATUS_ABSTRACT_APPROVED,
        Submission::STATUS_UNDER_REVIEW,
        Submission::STATUS_REVISION_REQUESTED,
        Submission::STATUS_DOCUMENT_APPROVED,
        Submission::STATUS_MODALITY_SELECTED,
        Submission::STATUS_VIDEO_PENDING,
        Submission::STATUS_VIDEO_READY,
        Submission::STATUS_PAYMENT_PENDING,
        Submission::STATUS_CONFIRMED,
    ];

    /** POST /api/submissions/{submission}/journal-opt-in — quiero publicar en revista */
    public function optIn(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);
        abort_if(! in_array($submission->status, self::ALLOWED_STATUSES), 422, 'La opción de publicación en revista está disponible cuando el resumen de la ponencia ha sido aprobado.');

        if (! $submission->journal_opt_in_at) {
            $submission->update(['journal_opt_in_at' => now()]);
        }

        return response()->json(['journal_opt_in_at' => $submission->journal_opt_in_at]);
    }

    /** DELETE /api/submissions/{submission}/journal-opt-in — ya no quiero publicar */
    public function optOut(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);
        abort_if($submission->articles()->exists(), 422, 'No puede retirar la opción de publicación: ya subió un artículo. Contacte al comité si desea retirarlo.');

        $submission->update(['journal_opt_in_at' => null]);

        return response()->json(['journal_opt_in_at' => null]);
    }

    /** POST /api/submissions/{submission}/articles — subir artículo (Word) */
    public function store(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);
        abort_if(! in_array($submission->status, self::ALLOWED_STATUSES), 422, 'Puede subir el artículo cuando el resumen de su ponencia haya sido aprobado.');

        $latest = $submission->latestArticle;
        abort_if(
            $latest && ! in_array($latest->status, [SubmissionArticle::STATUS_REVISION_REQUESTED]),
            422,
            'Su artículo actual está en revisión o ya fue aprobado; no puede reemplazarlo por ahora.'
        );

        $request->validate([
            'file' => 'required|file|mimes:doc,docx|max:10240',
        ], [
            'file.mimes' => 'El artículo debe subirse en formato Word (.doc o .docx).',
        ]);

        $isResubmission = $latest && $latest->status === SubmissionArticle::STATUS_REVISION_REQUESTED;

        $file    = $request->file('file');
        $path    = $file->store('submission_articles/' . $submission->id, 'local');
        $version = ($latest?->version ?? 0) + 1;

        $article = $submission->articles()->create([
            'version'           => $version,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path'       => $path,
            'file_size'         => $file->getSize(),
            'mime_type'         => $file->getMimeType(),
            'status'            => SubmissionArticle::STATUS_PENDING_REVIEW,
            'submitted_at'      => now(),
        ]);

        // Subir artículo implica opt-in aunque no haya pulsado el botón antes
        if (! $submission->journal_opt_in_at) {
            $submission->update(['journal_opt_in_at' => now()]);
        }

        // Resubida tras "pedir ajustes": re-asignar automáticamente los mismos
        // revisores del artículo a la nueva versión
        if ($isResubmission) {
            $previousReviewerIds = $submission->reviews()
                ->where('type', Review::TYPE_ARTICLE)
                ->pluck('reviewer_id')
                ->unique();

            foreach ($previousReviewerIds as $reviewerId) {
                Review::create([
                    'submission_article_id' => $article->id,
                    'submission_id'         => $submission->id,
                    'reviewer_id'           => $reviewerId,
                    'assigned_by'           => null,
                    'type'                  => Review::TYPE_ARTICLE,
                    'status'                => Review::STATUS_PENDING,
                    'assigned_at'           => now(),
                ]);
            }

            $article->update(['status' => SubmissionArticle::STATUS_UNDER_REVIEW]);
        }

        return response()->json($article->fresh(), 201);
    }

    /** GET /api/submissions/{submission}/articles/{article}/download */
    public function download(Request $request, Submission $submission, SubmissionArticle $article): StreamedResponse
    {
        $this->authorize('view', $submission);
        abort_if($article->submission_id !== $submission->id, 404);
        abort_unless(Storage::disk('local')->exists($article->stored_path), 404, 'Archivo no encontrado.');

        return Storage::disk('local')->download(
            $article->stored_path,
            $article->original_filename,
            ['Content-Type' => $article->mime_type]
        );
    }
}
