<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Submission;
use App\Models\SubmissionAbstract;
use App\Models\SubmissionArticle;
use App\Models\SubmissionDocument;
use App\Models\SubmissionVideo;
use App\Models\User;
use App\Services\ReviewOutcomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminSubmissionController extends Controller
{
    public function __construct(private ReviewOutcomeService $outcomes)
    {
    }

    /** GET /api/admin/submissions */
    public function index(Request $request): JsonResponse
    {
        $query = Submission::with([
                'user:id,name,email',
                'thematicAxis:id,name',
                'reviews:id,submission_id,reviewer_id,status,decision',
                'reviews.reviewer:id,name',
                'latestArticle',
            ])
            ->orderByDesc('updated_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->axis) {
            $query->where('thematic_axis_id', $request->axis);
        }

        $submissions = $query->get();

        return response()->json($submissions);
    }

    /** GET /api/admin/submissions/{submission} */
    public function show(Submission $submission): JsonResponse
    {
        $submission->load([
            'user',
            'thematicAxis',
            'abstracts.llmAxis',
            'documents',
            'articles',
            'reviews.reviewer:id,name',
            'reviews.assignedBy:id,name',
            'video',
            'events.user:id,name',
        ]);

        return response()->json($submission);
    }

    /** GET /api/admin/reviewers — lista de usuarios con rol revisor */
    public function reviewers(): JsonResponse
    {
        $reviewers = User::role('revisor')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json($reviewers);
    }

    /**
     * GET /api/admin/submissions/{submission}/abstracts/{abstract}/download
     * Archivo original del resumen; si no se guardó (histórico), sirve el
     * documento reconstruido sobre la plantilla oficial (generated_path).
     */
    public function downloadAbstractFile(Submission $submission, SubmissionAbstract $abstract): StreamedResponse
    {
        abort_if($abstract->submission_id !== $submission->id, 404);

        if ($abstract->stored_path) {
            abort_unless(Storage::disk('local')->exists($abstract->stored_path), 404, 'Archivo no encontrado.');

            return Storage::disk('local')->download(
                $abstract->stored_path,
                $abstract->original_filename ?? 'resumen',
                ['Content-Type' => $abstract->mime_type ?? 'application/octet-stream']
            );
        }

        abort_if(! $abstract->generated_path, 404, 'Este resumen no tiene archivo guardado ni documento reconstruido.');
        abort_unless(Storage::disk('local')->exists($abstract->generated_path), 404, 'Documento reconstruido no encontrado.');

        return Storage::disk('local')->download(
            $abstract->generated_path,
            "Resumen_ponencia{$submission->id}_v{$abstract->version}.docx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        );
    }

    /** GET /api/admin/submissions/{submission}/video/stream — descarga del video */
    public function streamVideo(Submission $submission): StreamedResponse
    {
        $video = $submission->video;
        abort_if(! $video || ! $video->stored_path, 404, 'No hay video para esta ponencia.');
        abort_unless(Storage::disk('local')->exists($video->stored_path), 404, 'Archivo de video no encontrado.');

        $mimeType = $video->mime_type ?? 'video/mp4';
        $filename  = $video->original_filename ?? 'video.mp4';

        return Storage::disk('local')->download($video->stored_path, $filename, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /** PATCH /api/admin/submissions/{submission}/video/approve — aprobar videoponencia */
    public function approveVideo(Submission $submission): JsonResponse
    {
        $video = $submission->video;
        abort_if(! $video || $video->status !== SubmissionVideo::STATUS_READY, 422, 'El video debe estar en estado "listo" para aprobarse.');

        $submission->advanceTo(Submission::STATUS_PAYMENT_PENDING);

        return response()->json(['status' => 'payment_pending']);
    }

    /** PATCH /api/admin/submissions/{submission}/video/reject — rechazar videoponencia */
    public function rejectVideo(Request $request, Submission $submission): JsonResponse
    {
        $video = $submission->video;
        abort_if(! $video || $video->status !== SubmissionVideo::STATUS_READY, 422, 'El video debe estar en estado "listo" para rechazarse.');

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'Debes indicar el motivo del rechazo.',
        ]);

        $video->update([
            'status'        => SubmissionVideo::STATUS_REJECTED,
            'error_message' => $validated['reason'],
        ]);

        $submission->advanceTo(Submission::STATUS_VIDEO_PENDING);

        return response()->json(['status' => 'video_pending']);
    }

    /** POST /api/admin/submissions/{submission}/assign-abstract-reviewer */
    public function assignAbstractReviewer(Request $request, Submission $submission): JsonResponse
    {
        abort_if(
            $submission->status !== Submission::STATUS_ABSTRACT_SUBMITTED,
            422,
            'Solo se puede asignar revisor al resumen cuando está en estado "enviado".'
        );

        $validated = $request->validate([
            'reviewer_id' => 'required|exists:users,id',
        ]);

        $reviewer = User::findOrFail($validated['reviewer_id']);
        abort_unless($reviewer->hasRole('revisor'), 422, 'El usuario debe tener rol revisor.');

        $abstract = $submission->abstracts()->latest()->firstOrFail();

        $alreadyAssigned = Review::where('submission_abstract_id', $abstract->id)
            ->where('reviewer_id', $reviewer->id)
            ->exists();
        abort_if($alreadyAssigned, 422, 'Este revisor ya está asignado a este resumen.');

        $review = Review::create([
            'submission_abstract_id' => $abstract->id,
            'submission_id'          => $submission->id,
            'reviewer_id'            => $reviewer->id,
            'assigned_by'            => $request->user()->id,
            'type'                   => Review::TYPE_ABSTRACT,
            'status'                 => Review::STATUS_PENDING,
            'assigned_at'            => now(),
        ]);

        return response()->json($review->load(['reviewer:id,name']), 201);
    }

    /** PATCH /api/admin/submissions/{submission}/abstract/approve — aprobar resumen (override admin) */
    public function approveAbstract(Submission $submission): JsonResponse
    {
        abort_if($submission->status !== Submission::STATUS_ABSTRACT_SUBMITTED, 422, 'El resumen debe estar en estado "enviado" para aprobarse.');
        $submission->advanceTo(Submission::STATUS_ABSTRACT_APPROVED);
        return response()->json(['status' => Submission::STATUS_ABSTRACT_APPROVED]);
    }

    /** PATCH /api/admin/submissions/{submission}/abstract/reject — rechazar resumen (override admin) */
    public function rejectAbstract(Submission $submission): JsonResponse
    {
        abort_if($submission->status !== Submission::STATUS_ABSTRACT_SUBMITTED, 422, 'El resumen debe estar en estado "enviado" para rechazarse.');
        $submission->advanceTo(Submission::STATUS_ABSTRACT_REJECTED);
        return response()->json(['status' => Submission::STATUS_ABSTRACT_REJECTED]);
    }

    /** POST /api/admin/submissions/{submission}/assign-reviewer */
    public function assignReviewer(Request $request, Submission $submission): JsonResponse
    {
        $validated = $request->validate([
            'reviewer_id' => 'required|exists:users,id',
            'document_id' => 'required|exists:submission_documents,id',
        ]);

        $reviewer = User::findOrFail($validated['reviewer_id']);
        abort_unless($reviewer->hasRole('revisor'), 422, 'El usuario debe tener rol revisor.');

        $doc = $submission->documents()->findOrFail($validated['document_id']);

        $review = Review::create([
            'submission_document_id' => $doc->id,
            'submission_id'          => $submission->id,
            'reviewer_id'            => $reviewer->id,
            'assigned_by'            => $request->user()->id,
            'status'                 => Review::STATUS_PENDING,
            'assigned_at'            => now(),
        ]);

        $doc->update(['status' => 'under_review']);

        return response()->json($review->load(['reviewer:id,name', 'submissionDocument']), 201);
    }

    /** POST /api/admin/submissions/{submission}/assign-article-reviewer */
    public function assignArticleReviewer(Request $request, Submission $submission): JsonResponse
    {
        $validated = $request->validate([
            'reviewer_id' => 'required|exists:users,id',
            'article_id'  => 'required|exists:submission_articles,id',
        ]);

        $reviewer = User::findOrFail($validated['reviewer_id']);
        abort_unless($reviewer->hasRole('revisor'), 422, 'El usuario debe tener rol revisor.');

        $article = $submission->articles()->findOrFail($validated['article_id']);

        $alreadyAssigned = Review::where('submission_article_id', $article->id)
            ->where('reviewer_id', $reviewer->id)
            ->exists();
        abort_if($alreadyAssigned, 422, 'Este revisor ya está asignado a este artículo.');

        $review = Review::create([
            'submission_article_id' => $article->id,
            'submission_id'         => $submission->id,
            'reviewer_id'           => $reviewer->id,
            'assigned_by'           => $request->user()->id,
            'type'                  => Review::TYPE_ARTICLE,
            'status'                => Review::STATUS_PENDING,
            'assigned_at'           => now(),
        ]);

        $article->update(['status' => SubmissionArticle::STATUS_UNDER_REVIEW]);

        return response()->json($review->load(['reviewer:id,name', 'submissionArticle']), 201);
    }

    /** DELETE /api/admin/submissions/{submission}/reviews/{review} */
    public function removeReview(Submission $submission, Review $review): JsonResponse
    {
        abort_unless($review->submission_id === $submission->id, 404);

        $documentId = $review->submission_document_id;
        $articleId  = $review->submission_article_id;
        $abstractId = $review->submission_abstract_id;

        $review->delete();

        // Si era review de documento y ya no quedan revisores en ese doc, revertir el doc a pending_review
        if ($documentId) {
            $remaining = Review::where('submission_document_id', $documentId)->count();
            if ($remaining === 0) {
                SubmissionDocument::where('id', $documentId)
                    ->where('status', SubmissionDocument::STATUS_UNDER_REVIEW)
                    ->update(['status' => SubmissionDocument::STATUS_PENDING_REVIEW]);
            }
        }

        // Lo mismo para artículos
        if ($articleId) {
            $remaining = Review::where('submission_article_id', $articleId)->count();
            if ($remaining === 0) {
                SubmissionArticle::where('id', $articleId)
                    ->where('status', SubmissionArticle::STATUS_UNDER_REVIEW)
                    ->update(['status' => SubmissionArticle::STATUS_PENDING_REVIEW]);
            }
        }

        // Quitar un revisor cambia el conjunto de dictámenes: si los que quedan ya
        // aprobaron, hay que avanzar. Sin esto, quitar al último revisor pendiente
        // dejaba la ponencia congelada aunque su resumen estuviera aprobado.
        $submission->refresh();
        $this->outcomes->syncAbstract($submission, $abstractId);
        $this->outcomes->syncDocument($submission, $documentId);
        $this->outcomes->syncArticle($submission, $articleId ? SubmissionArticle::find($articleId) : null);

        return response()->json(['ok' => true]);
    }
}
