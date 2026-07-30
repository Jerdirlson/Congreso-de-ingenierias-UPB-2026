<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewOutcomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReviewController extends Controller
{
    public function __construct(private ReviewOutcomeService $outcomes)
    {
    }

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

    /** GET /api/reviews/{review}/document — descargar el archivo de la revisión (PDF, artículo Word o resumen original) */
    public function downloadDocument(Review $review): StreamedResponse
    {
        $this->authorize('view', $review);

        $file = match ($review->type) {
            Review::TYPE_ARTICLE  => $review->submissionArticle,
            Review::TYPE_ABSTRACT => $review->submissionAbstract,
            default               => $review->submissionDocument,
        };
        abort_if(! $file, 404, 'No hay documento asociado a esta revisión.');

        if ($file->stored_path) {
            abort_unless(Storage::disk('local')->exists($file->stored_path), 404, 'Archivo no encontrado.');

            return Storage::disk('local')->download(
                $file->stored_path,
                $file->original_filename ?? 'archivo',
                ['Content-Type' => $file->mime_type ?? 'application/pdf']
            );
        }

        // Resúmenes históricos sin archivo original: servir el documento
        // reconstruido sobre la plantilla oficial, si ya fue generado.
        $generated = $review->type === Review::TYPE_ABSTRACT ? $file->generated_path : null;
        abort_if(! $generated, 404, 'Este documento no tiene archivo guardado.');
        abort_unless(Storage::disk('local')->exists($generated), 404, 'Documento reconstruido no encontrado.');

        return Storage::disk('local')->download(
            $generated,
            "Resumen_v{$file->version}.docx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
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

        $this->outcomes->syncDocument($submission, $review->submission_document_id);
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

        $this->outcomes->syncArticle($submission, $article);
    }

    private function updateAbstractStatus(Review $review, \App\Models\Submission $submission): void
    {
        if ($review->decision === Review::DECISION_REJECTED) {
            $submission->advanceTo(\App\Models\Submission::STATUS_ABSTRACT_REJECTED);
            return;
        }

        $this->outcomes->syncAbstract($submission, $review->submission_abstract_id);
    }
}
