<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionAbstract;
use App\Models\ThematicAxis;
use App\Services\LlmClassificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbstractController extends Controller
{
    /** POST /api/submissions/{submission}/abstracts — reenviar resumen (tras rechazo) */
    public function store(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);

        $allowed = [
            Submission::STATUS_DRAFT,
            Submission::STATUS_ABSTRACT_REJECTED,
        ];
        abort_if(! in_array($submission->status, $allowed), 422, 'No puede subir resumen en el estado actual.');

        $validated = $request->validate([
            'content' => 'required|string|min:100|max:10000',
        ]);

        $version = $submission->abstract_attempts + 1;

        $abstract = $submission->abstracts()->create([
            'content'    => $validated['content'],
            'version'    => $version,
            'llm_status' => SubmissionAbstract::LLM_STATUS_PENDING,
        ]);

        $submission->update([
            'abstract_attempts' => $version,
            'status'            => Submission::STATUS_ABSTRACT_SUBMITTED,
        ]);

        // Clasificar de forma síncrona (sin cola)
        try {
        $llm  = app(LlmClassificationService::class);
        $axes = ThematicAxis::active()->get();

        if ($axes->isEmpty()) {
            $abstract->update([
                'llm_status'        => SubmissionAbstract::LLM_STATUS_REJECTED,
                'llm_justification' => 'No hay ejes temáticos activos configurados.',
                'processed_at'      => now(),
            ]);
            $submission->advanceTo(Submission::STATUS_ABSTRACT_REJECTED);
        } else {
            $result   = $llm->classify($validated['content'], $axes);
            $approved = $llm->isApproved($result['confidence_score']) && $result['axis_id'] !== null;

            $abstract->update([
                'llm_status'           => $approved ? SubmissionAbstract::LLM_STATUS_APPROVED : SubmissionAbstract::LLM_STATUS_REJECTED,
                'llm_axis_id'          => $result['axis_id'],
                'llm_confidence_score' => $result['confidence_score'],
                'llm_justification'    => $result['justification'],
                'llm_raw_response'     => $result['raw_response'],
                'processed_at'         => now(),
            ]);

            $submission->advanceTo(
                $approved ? Submission::STATUS_ABSTRACT_APPROVED : Submission::STATUS_ABSTRACT_REJECTED
            );

            if ($approved && $result['axis_id']) {
                $submission->update(['thematic_axis_id' => $result['axis_id']]);
            }
        }

        } catch (RuntimeException $e) {
            // Revertir estado a draft para permitir reintento
            $abstract->delete();
            $submission->update(['abstract_attempts' => $version - 1, 'status' => Submission::STATUS_DRAFT]);
            abort(503, $e->getMessage());
        }

        return response()->json([
            'abstract'   => $abstract->fresh('llmAxis'),
            'submission' => $submission->fresh('thematicAxis'),
        ], 201);
    }
}
