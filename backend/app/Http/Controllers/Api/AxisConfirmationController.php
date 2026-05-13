<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\ThematicAxis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AxisConfirmationController extends Controller
{
    /** PATCH /api/submissions/{submission}/axis — confirmar eje temático (manualmente o aceptando la IA) */
    public function update(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);

        abort_if(
            $submission->status !== Submission::STATUS_ABSTRACT_SUBMITTED,
            422,
            'Solo puedes confirmar el eje temático cuando el resumen está pendiente de confirmación.'
        );

        $validated = $request->validate([
            'thematic_axis_id' => 'required|integer|exists:thematic_axes,id',
        ]);

        $axis = ThematicAxis::where('id', $validated['thematic_axis_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $submission->update(['thematic_axis_id' => $axis->id]);

        return response()->json($submission->fresh(['thematicAxis', 'abstracts.llmAxis']));
    }
}
