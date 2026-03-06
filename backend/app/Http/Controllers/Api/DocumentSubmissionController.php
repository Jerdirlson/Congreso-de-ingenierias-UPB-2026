<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Submission;
use App\Models\SubmissionDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentSubmissionController extends Controller
{
    /** POST /api/submissions/{submission}/documents */
    public function store(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);

        $allowed = [
            Submission::STATUS_ABSTRACT_APPROVED,
            Submission::STATUS_REVISION_REQUESTED,
        ];
        abort_if(! in_array($submission->status, $allowed), 422, 'No puede subir documento en el estado actual.');

        $request->validate(['file' => 'required|file|mimes:pdf|max:10240']);

        $isResubmission = $submission->status === Submission::STATUS_REVISION_REQUESTED;

        $file    = $request->file('file');
        $path    = $file->store('submission_documents/' . $submission->id, 'local');
        $version = $submission->document_version + 1;

        $doc = $submission->documents()->create([
            'version'           => $version,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path'       => $path,
            'file_size'         => $file->getSize(),
            'mime_type'         => $file->getMimeType(),
            'status'            => SubmissionDocument::STATUS_PENDING_REVIEW,
            'submitted_at'      => now(),
        ]);

        $submission->update([
            'document_version' => $version,
            'status'           => Submission::STATUS_UNDER_REVIEW,
        ]);

        // Si es una resubida tras correcciones, re-asignar automáticamente
        // los mismos revisores al nuevo documento
        if ($isResubmission) {
            $previousReviewerIds = $submission->reviews()
                ->pluck('reviewer_id')
                ->unique();

            foreach ($previousReviewerIds as $reviewerId) {
                Review::create([
                    'submission_document_id' => $doc->id,
                    'submission_id'          => $submission->id,
                    'reviewer_id'            => $reviewerId,
                    'assigned_by'            => null,
                    'status'                 => Review::STATUS_PENDING,
                    'assigned_at'            => now(),
                ]);
            }
        }

        return response()->json($doc, 201);
    }

    /** GET /api/submissions/{submission}/documents/{document}/download */
    public function download(Request $request, Submission $submission, SubmissionDocument $document): StreamedResponse
    {
        $this->authorize('view', $submission);
        abort_if($document->submission_id !== $submission->id, 404);
        abort_unless(Storage::disk('local')->exists($document->stored_path), 404, 'Archivo no encontrado.');
        return Storage::disk('local')->download(
            $document->stored_path,
            $document->original_filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
