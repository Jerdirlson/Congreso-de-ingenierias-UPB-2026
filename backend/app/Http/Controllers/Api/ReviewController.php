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
        ]);

        // Historial de revisiones anteriores del mismo revisor sobre la misma ponencia
        $history = Review::where('submission_id', $review->submission_id)
            ->where('reviewer_id', $review->reviewer_id)
            ->where('id', '!=', $review->id)
            ->with('submissionDocument:id,version,original_filename')
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

    /** GET /api/reviews/{review}/document — descargar PDF de la revisión */
    public function downloadDocument(Review $review): StreamedResponse
    {
        $this->authorize('view', $review);

        $doc = $review->submissionDocument;
        abort_if(! $doc, 404, 'No hay documento asociado a esta revisión.');
        abort_unless(Storage::disk('local')->exists($doc->stored_path), 404, 'Archivo no encontrado.');

        return Storage::disk('local')->download(
            $doc->stored_path,
            $doc->original_filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function updateSubmissionStatus(Review $review): void
    {
        $submission = $review->submission;

        if ($review->decision === 'rejected') {
            $submission->advanceTo('revision_requested');
            $submission->latestDocument?->update(['status' => 'revision_requested']);
            return;
        }

        // Solo evaluar las revisiones del documento actual, no el historial de versiones anteriores
        $currentDocReviews = $submission->reviews
            ->where('submission_document_id', $review->submission_document_id);

        $allCompleted = $currentDocReviews->every(fn ($r) => $r->status === Review::STATUS_COMPLETED);
        $allApproved  = $currentDocReviews->every(fn ($r) => $r->decision === Review::DECISION_APPROVED);

        if ($allCompleted && $allApproved) {
            $submission->advanceTo('document_approved');
            $submission->latestDocument?->update(['status' => 'approved']);
        }
    }
}
