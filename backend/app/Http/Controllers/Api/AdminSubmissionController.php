<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Submission;
use App\Models\SubmissionVideo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminSubmissionController extends Controller
{
    /** GET /api/admin/submissions */
    public function index(Request $request): JsonResponse
    {
        $query = Submission::with(['user:id,name,email', 'thematicAxis:id,name'])
            ->orderByDesc('updated_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->axis) {
            $query->where('thematic_axis_id', $request->axis);
        }

        $submissions = $query->paginate(20);

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
            'reviews.reviewer:id,name',
            'reviews.assignedBy:id,name',
            'video',
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

        $submission->advanceTo(Submission::STATUS_CONFIRMED);

        return response()->json(['status' => 'confirmed']);
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
}
