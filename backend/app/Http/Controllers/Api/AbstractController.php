<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionAbstract;
use App\Models\ThematicAxis;
use App\Services\AbstractFileExtractorService;
use App\Services\LlmClassificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AbstractController extends Controller
{
    /** POST /api/submissions/{submission}/abstracts — reenviar archivo de resumen */
    public function store(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);

        $allowed = [
            Submission::STATUS_DRAFT,
            Submission::STATUS_ABSTRACT_SUBMITTED,
        ];
        abort_if(! in_array($submission->status, $allowed), 422, 'No puede subir resumen en el estado actual.');

        $validated = $request->validate([
            'abstract_file' => 'required|file|mimes:docx,pdf|max:10240',
        ]);

        try {
            $abstractText = app(AbstractFileExtractorService::class)->extractText($validated['abstract_file']);
        } catch (RuntimeException $e) {
            abort(422, $e->getMessage());
        }

        if (mb_strlen($abstractText) < 100) {
            abort(422, 'El archivo debe contener al menos 100 caracteres de texto legible para análisis.');
        }

        $version = $submission->abstract_attempts + 1;

        $abstract = $submission->abstracts()->create([
            'content'    => $abstractText,
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
        } else {
            $result         = $llm->classify($abstractText, $axes);
            $highConfidence = $llm->isApproved($result['confidence_score']) && $result['axis_id'] !== null;

            $abstract->update([
                'llm_status'           => $highConfidence
                    ? SubmissionAbstract::LLM_STATUS_APPROVED
                    : SubmissionAbstract::LLM_STATUS_REJECTED,
                'llm_axis_id'          => $result['axis_id'],
                'llm_confidence_score' => $result['confidence_score'],
                'llm_justification'    => $result['justification'],
                'llm_raw_response'     => $result['raw_response'],
                'processed_at'         => now(),
            ]);
        }

        // Siempre pasa a abstract_submitted: el ponente elige/confirma el eje
        $submission->advanceTo(Submission::STATUS_ABSTRACT_SUBMITTED);

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
