<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Submission;
use App\Models\SubmissionAbstract;
use App\Services\AbstractFileExtractorService;
use App\Services\AbstractTemplateValidatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            Submission::STATUS_ABSTRACT_REJECTED,
        ];
        abort_if(! in_array($submission->status, $allowed), 422, 'No puede subir resumen en el estado actual.');

        $validated = $request->validate([
            'abstract_file' => 'required|file|mimes:pdf|max:10240',
        ], [
            'abstract_file.mimes' => 'El resumen debe subirse en formato PDF: diligencia la plantilla oficial en Word y expórtala/guárdala como PDF.',
        ]);

        $file      = $validated['abstract_file'];
        $extractor = app(AbstractFileExtractorService::class);

        try {
            $abstractText = $extractor->extractText($file);
        } catch (RuntimeException $e) {
            abort(422, $e->getMessage());
        }

        $wordCount = count(preg_split('/\s+/', trim($abstractText), -1, PREG_SPLIT_NO_EMPTY));
        if ($wordCount < 100) {
            abort(422, "El archivo contiene muy poco texto legible ({$wordCount} palabras). Asegúrate de que el resumen tenga al menos 100 palabras y no sea un PDF escaneado.");
        }

        try {
            app(AbstractTemplateValidatorService::class)->validateOrFail($abstractText);
        } catch (RuntimeException $e) {
            abort(422, $e->getMessage());
        }

        if (! $extractor->pdfContainsTemplateLogo($file)) {
            abort(422, 'El archivo no parece estar hecho sobre la plantilla oficial del congreso: no contiene el encabezado con el logo. Descarga la plantilla oficial (Plantilla_Resumen.docx), escribe tu contenido sobre ella y expórtala a PDF sin quitar el encabezado.');
        }

        $version = $submission->abstract_attempts + 1;
        $isResubmission = $submission->status === Submission::STATUS_ABSTRACT_REJECTED;

        // Metadatos antes de store(): al mover el archivo, el temporal deja de existir
        $fileMeta = [
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
        ];
        $fileMeta['stored_path'] = $file->store('submission_abstracts/' . $submission->id, 'local');

        // Atómico: si falla el guardado del resumen, el estado de la ponencia
        // no debe avanzar a abstract_submitted sin un resumen real.
        $abstract = DB::transaction(function () use ($submission, $abstractText, $version, $isResubmission, $fileMeta) {
            $abstract = $submission->abstracts()->create([
                'content'      => $abstractText,
                'version'      => $version,
                'llm_status'   => SubmissionAbstract::LLM_STATUS_APPROVED,
                'processed_at' => now(),
                ...$fileMeta,
            ]);

            $submission->update([
                'abstract_attempts' => $version,
                'status'            => Submission::STATUS_ABSTRACT_SUBMITTED,
            ]);

            // Si es una resubida tras "pedir ajustes", re-asignar automáticamente
            // una nueva revisión a los revisores que ya dictaminaron el resumen anterior
            if ($isResubmission) {
                $completedReviewerIds = $submission->reviews()
                    ->where('type', Review::TYPE_ABSTRACT)
                    ->where('status', Review::STATUS_COMPLETED)
                    ->pluck('reviewer_id')
                    ->unique();

                foreach ($completedReviewerIds as $reviewerId) {
                    Review::create([
                        'submission_abstract_id' => $abstract->id,
                        'submission_id'          => $submission->id,
                        'reviewer_id'            => $reviewerId,
                        'assigned_by'            => null,
                        'type'                   => Review::TYPE_ABSTRACT,
                        'status'                 => Review::STATUS_PENDING,
                        'assigned_at'            => now(),
                    ]);
                }
            }

            // Las revisiones de resumen aún abiertas (pendientes o en curso) pasan
            // a apuntar a la nueva versión, para que el revisor dictamine la última
            $submission->reviews()
                ->where('type', Review::TYPE_ABSTRACT)
                ->whereIn('status', [Review::STATUS_PENDING, Review::STATUS_IN_PROGRESS])
                ->where('submission_abstract_id', '!=', $abstract->id)
                ->update(['submission_abstract_id' => $abstract->id]);

            return $abstract;
        });

        return response()->json([
            'abstract'   => $abstract->fresh('llmAxis'),
            'submission' => $submission->fresh('thematicAxis'),
        ], 201);
    }
}
