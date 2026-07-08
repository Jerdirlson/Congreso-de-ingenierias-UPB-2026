<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Submission;
use App\Models\SubmissionAbstract;
use App\Services\AbstractFileExtractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /** POST /api/submissions — crear ponencia con archivo de resumen (eje temático manual) */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Cierre del periodo de ponencias: no se permiten nuevas ponencias.
        abort_if(
            ! AppSetting::getBool(AppSetting::SUBMISSIONS_OPEN, true),
            422,
            'El periodo para registrar nuevas ponencias está cerrado.'
        );

        // Máximo 2 ponencias por ponente (las soft-deleted no cuentan)
        abort_if($user->submissions()->count() >= 2, 422, 'Ya tienes 2 ponencias registradas. El máximo permitido es 2 por ponente.');

        $validated = $request->validate([
            'title'             => 'required|string|max:500',
            'thematic_axis_id'  => 'required|exists:thematic_axes,id',
            'abstract_file'     => 'required|file|mimes:docx,pdf|max:10240',
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

        // Atómico: si falla el guardado del resumen, NO debe quedar la ponencia
        // huérfana en estado abstract_submitted sin resumen.
        $submission = DB::transaction(function () use ($user, $validated, $abstractText) {
            $submission = $user->submissions()->create([
                'title'             => $validated['title'],
                'thematic_axis_id'  => $validated['thematic_axis_id'],
                'status'            => Submission::STATUS_ABSTRACT_SUBMITTED,
                'abstract_attempts' => 1,
            ]);

            // llm_status='approved' aquí significa "no requiere clasificación pendiente"; la columna es NOT NULL en la BD.
            $submission->abstracts()->create([
                'content'      => $abstractText,
                'version'      => 1,
                'llm_status'   => SubmissionAbstract::LLM_STATUS_APPROVED,
                'processed_at' => now(),
            ]);

            return $submission;
        });

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
            'articles',
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
}
