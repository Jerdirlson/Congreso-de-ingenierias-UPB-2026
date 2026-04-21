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
use Illuminate\Support\Str;
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

        // Solo se permite una ponencia por ponente (las soft-deleted no cuentan)
        abort_if($user->submissions()->exists(), 422, 'Ya tienes una ponencia registrada. Solo se permite una por ponente.');

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

        // Título provisional: primeros 150 caracteres del resumen
        $title = Str::limit($abstractText, 150);

        $submission = $user->submissions()->create([
            'title'  => $title,
            'status' => Submission::STATUS_DRAFT,
        ]);

        // Crear registro del resumen
        $abstract = $submission->abstracts()->create([
            'content'    => $abstractText,
            'version'    => 1,
            'llm_status' => SubmissionAbstract::LLM_STATUS_PENDING,
        ]);
        $submission->update(['abstract_attempts' => 1]);

        // Clasificar de forma síncrona (sin cola)
        try {
            $this->classifyAbstract($abstract, $submission);
        } catch (RuntimeException $e) {
            // Limpiar registro creado y devolver error al cliente
            $abstract->delete();
            $submission->delete();
            abort(503, $e->getMessage());
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
