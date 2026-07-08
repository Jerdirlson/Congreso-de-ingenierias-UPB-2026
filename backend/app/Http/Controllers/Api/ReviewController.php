<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReviewController extends Controller
{
    /** GET /api/reviews — revisiones asignadas al revisor (con filtros) */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()
            ->reviews()
            ->with([
                'submission.user:id,name,email,institution',
                'submission.thematicAxis:id,name',
                'submissionDocument',
                'submissionAbstract',
                'submissionArticle',
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('axis')) {
            $query->whereHas('submission', fn ($q) => $q->where('thematic_axis_id', $request->axis));
        }

        $reviews = $query
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
            ->orderByDesc('assigned_at')
            ->paginate(20);

        return response()->json($reviews);
    }

    /** GET /api/reviews/{review} */
    public function show(Review $review): JsonResponse
    {
        $this->authorize('view', $review);

        $review->load([
            'submission.user:id,name,email,institution,country',
            'submission.thematicAxis',
            'submission.abstracts',
            'submissionDocument',
            'submissionAbstract',
            'submissionArticle',
        ]);

        // Historial de revisiones anteriores del mismo revisor sobre la misma ponencia
        $history = Review::where('submission_id', $review->submission_id)
            ->where('reviewer_id', $review->reviewer_id)
            ->where('id', '!=', $review->id)
            ->with([
                'submissionDocument:id,version,original_filename',
                'submissionAbstract:id,version',
                'submissionArticle:id,version,original_filename',
            ])
            ->orderByDesc('assigned_at')
            ->get();

        return response()->json(array_merge($review->toArray(), ['history' => $history]));
    }

    /** PATCH /api/reviews/{review} — iniciar o completar revisión */
    public function update(Request $request, Review $review): JsonResponse
    {
        $this->authorize('update', $review);

        if ($review->status === Review::STATUS_PENDING) {
            $review->update([
                'status'     => Review::STATUS_IN_PROGRESS,
                'started_at' => now(),
            ]);
            return response()->json($review->fresh());
        }

        if ($review->status === Review::STATUS_IN_PROGRESS) {
            $validated = $request->validate([
                'decision' => 'required|in:approved,rejected',
                'comments' => [
                    'nullable',
                    'string',
                    'max:5000',
                    $request->decision === 'rejected' ? 'required' : 'nullable',
                ],
            ], [
                'comments.required' => 'Los comentarios son obligatorios al rechazar una ponencia.',
            ]);

            $review->update([
                'status'       => Review::STATUS_COMPLETED,
                'decision'     => $validated['decision'],
                'comments'     => $validated['comments'] ?? null,
                'completed_at' => now(),
            ]);

            $this->updateSubmissionStatus($review);

            return response()->json($review->fresh());
        }

        return response()->json($review);
    }

    /** GET /api/reviews/{review}/document — descargar el archivo de la revisión (PDF o artículo Word) */
    public function downloadDocument(Review $review): StreamedResponse
    {
        $this->authorize('view', $review);

        $file = $review->type === Review::TYPE_ARTICLE
            ? $review->submissionArticle
            : $review->submissionDocument;
        abort_if(! $file, 404, 'No hay documento asociado a esta revisión.');
        abort_unless(Storage::disk('local')->exists($file->stored_path), 404, 'Archivo no encontrado.');

        return Storage::disk('local')->download(
            $file->stored_path,
            $file->original_filename,
            ['Content-Type' => $file->mime_type ?? 'application/pdf']
        );
    }

    private function updateSubmissionStatus(Review $review): void
    {
        $submission = $review->submission;

        if ($review->type === Review::TYPE_ABSTRACT) {
            $this->updateAbstractStatus($review, $submission);
            return;
        }

        if ($review->type === Review::TYPE_ARTICLE) {
            $this->updateArticleStatus($review, $submission);
            return;
        }

        // Revisión de documento
        if ($review->decision === Review::DECISION_REJECTED) {
            $submission->advanceTo('revision_requested');
            $submission->latestDocument?->update(['status' => 'revision_requested']);
            return;
        }

        // Solo evaluar las revisiones del documento actual (no historial de versiones anteriores)
        $currentDocReviews = $submission->reviews
            ->where('submission_document_id', $review->submission_document_id)
            ->where('type', Review::TYPE_DOCUMENT);

        $allCompleted = $currentDocReviews->every(fn ($r) => $r->status === Review::STATUS_COMPLETED);
        $allApproved  = $currentDocReviews->every(fn ($r) => $r->decision === Review::DECISION_APPROVED);

        if ($allCompleted && $allApproved) {
            $submission->advanceTo('document_approved');
            $submission->latestDocument?->update(['status' => 'approved']);
        }
    }

    /**
     * El artículo es un carril paralelo: su dictamen solo cambia el estado del
     * artículo, nunca el estado de la ponencia (modalidad/video/pago siguen su curso).
     */
    private function updateArticleStatus(Review $review, \App\Models\Submission $submission): void
    {
        $article = $review->submissionArticle;
        if (! $article) {
            return;
        }

        if ($review->decision === Review::DECISION_REJECTED) {
            $article->update(['status' => \App\Models\SubmissionArticle::STATUS_REVISION_REQUESTED]);
            return;
        }

        // Solo evaluar las revisiones del artículo actual
        $articleReviews = $submission->reviews
            ->where('submission_article_id', $review->submission_article_id)
            ->where('type', Review::TYPE_ARTICLE);

        $allCompleted = $articleReviews->every(fn ($r) => $r->status === Review::STATUS_COMPLETED);
        $allApproved  = $articleReviews->every(fn ($r) => $r->decision === Review::DECISION_APPROVED);

        if ($allCompleted && $allApproved) {
            $article->update(['status' => \App\Models\SubmissionArticle::STATUS_APPROVED]);
        }
    }

    private function updateAbstractStatus(Review $review, \App\Models\Submission $submission): void
    {
        if ($review->decision === Review::DECISION_REJECTED) {
            $submission->advanceTo(\App\Models\Submission::STATUS_ABSTRACT_REJECTED);
            return;
        }

        // Verificar si todos los revisores del resumen actual aprobaron
        $abstractReviews = $submission->reviews
            ->where('submission_abstract_id', $review->submission_abstract_id)
            ->where('type', Review::TYPE_ABSTRACT);

        $allCompleted = $abstractReviews->every(fn ($r) => $r->status === Review::STATUS_COMPLETED);
        $allApproved  = $abstractReviews->every(fn ($r) => $r->decision === Review::DECISION_APPROVED);

        if ($allCompleted && $allApproved) {
            $submission->advanceTo(\App\Models\Submission::STATUS_ABSTRACT_APPROVED);
        }
    }
}
