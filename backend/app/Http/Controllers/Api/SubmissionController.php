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
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SubmissionController extends Controller
{
    /** GET /api/submissions — mis ponencias (ponente) */
    public function index(Request $request): JsonResponse
    {
        $submissions = $request->user()
            ->submissions()
            ->with(['thematicAxis:id,name', 'abstracts', 'latestDocument'])
            ->orderByDesc('updated_at')
            ->paginate(15);

        return response()->json($submissions);
    }

    /** POST /api/submissions — crear ponencia con archivo de resumen y clasificación IA */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Máximo 2 ponencias por ponente (las soft-deleted no cuentan)
        abort_if($user->submissions()->count() >= 2, 422, 'Ya tienes 2 ponencias registradas. El máximo permitido es 2 por ponente.');

        $validated = $request->validate([
            'title'         => 'required|string|max:500',
            'abstract_file' => 'required|file|mimes:docx,pdf|max:10240',
        ]);

        try {
            $abstractText = app(AbstractFileExtractorService::class)->extractText($validated['abstract_file']);
        } catch (RuntimeException $e) {
            abort(422, $e->getMessage());
        }

        $wordCount = count(preg_split('/\s+/', trim($abstractText), -1, PREG_SPLIT_NO_EMPTY));
        if ($wordCount < 100) {
            abort(422, "El archivo contiene muy poco texto legible ({$wordCount} palabras). Asegúrate de que el resumen tenga al menos 100 palabras y no sea un PDF escaneado.");
        }

        $submission = $user->submissions()->create([
            'title'  => $validated['title'],
            'status' => Submission::STATUS_DRAFT,
        ]);

        // Crear registro del resumen
        $abstract = $submission->abstracts()->create([
            'content'    => $abstractText,
            'version'    => 1,
            'llm_status' => SubmissionAbstract::LLM_STATUS_PENDING,
        ]);
        $submission->update(['abstract_attempts' => 1]);

        // Clasificar de forma síncrona (con reintentos internos)
        try {
            $this->classifyAbstract($abstract, $submission);
        } catch (RuntimeException $e) {
            // La IA no está disponible: marcar como rejected para que el ponente elija manualmente
            $abstract->update([
                'llm_status'        => SubmissionAbstract::LLM_STATUS_REJECTED,
                'llm_justification' => 'La clasificación automática no está disponible en este momento. Por favor selecciona el eje temático manualmente.',
                'processed_at'      => now(),
            ]);
            $submission->advanceTo(Submission::STATUS_ABSTRACT_SUBMITTED);
            Log::warning('Clasificación IA falló al crear ponencia', [
                'submission_id' => $submission->id,
                'error'         => $e->getMessage(),
            ]);
        }

        return response()->json([
            'submission' => $submission->fresh(['thematicAxis', 'abstracts.llmAxis']),
        ], 201);
    }

    /** GET /api/submissions/{submission} */
    public function show(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('view', $submission);

        $submission->load([
            'thematicAxis',
            'abstracts.llmAxis',
            'documents',
            'video',
            'reviews.reviewer:id,name',
        ]);

        return response()->json($submission);
    }

    /** PATCH /api/submissions/{submission} — actualizar título (solo draft) */
    public function update(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);

        abort_if($submission->status !== Submission::STATUS_DRAFT, 422, 'Solo se puede editar en estado draft.');

        $validated = $request->validate([
            'title' => 'required|string|max:500',
        ]);

        $submission->update($validated);

        return response()->json($submission);
    }

    /** DELETE /api/submissions/{submission} — soft delete (solo en estados iniciales) */
    public function destroy(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('delete', $submission);

        $deletableStatuses = [
            Submission::STATUS_DRAFT,
            Submission::STATUS_ABSTRACT_REJECTED,
            Submission::STATUS_ABSTRACT_SUBMITTED,
        ];

        abort_unless(
            in_array($submission->status, $deletableStatuses),
            422,
            'No puedes eliminar una ponencia que ya está en proceso de revisión o confirmada.'
        );

        $submission->delete(); // SoftDelete: solo marca deleted_at

        return response()->json(['message' => 'Ponencia eliminada correctamente.']);
    }

    /** Clasificar resumen con IA de forma síncrona */
    private function classifyAbstract(SubmissionAbstract $abstract, Submission $submission): void
    {
        $llm  = app(LlmClassificationService::class);
        $axes = ThematicAxis::active()->get();

        if ($axes->isEmpty()) {
            $abstract->update([
                'llm_status'        => SubmissionAbstract::LLM_STATUS_REJECTED,
                'llm_justification' => 'No hay ejes temáticos activos configurados.',
                'processed_at'      => now(),
            ]);
            $submission->advanceTo(Submission::STATUS_ABSTRACT_SUBMITTED);
            return;
        }

        $result = $llm->classify($abstract->content, $axes);

        // llm_status es solo informativo (alta/baja confianza); el eje lo confirma el ponente
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

        // Siempre pasa a abstract_submitted: el ponente elige/confirma el eje
        $submission->advanceTo(Submission::STATUS_ABSTRACT_SUBMITTED);
    }
}
