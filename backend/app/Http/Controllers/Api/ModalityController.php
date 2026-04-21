<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModalityController extends Controller
{
    /** PATCH /api/submissions/{submission}/modality */
    public function update(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);

        abort_if($submission->status !== Submission::STATUS_DOCUMENT_APPROVED, 422, 'Debe tener documento aprobado para elegir modalidad.');

        $validated = $request->validate([
            'modality' => 'required|in:presencial_oral,presencial_poster,virtual',
        ]);

        $submission->update(['modality' => $validated['modality']]);

        // El pago ocurre al final: presencial/póster esperan pago; virtual sube video primero
        $nextStatus = $validated['modality'] === Submission::MODALITY_VIRTUAL
            ? Submission::STATUS_VIDEO_PENDING
            : Submission::STATUS_PAYMENT_PENDING;

        $submission->advanceTo($nextStatus);

        return response()->json($submission->fresh('thematicAxis'));
    }
}
