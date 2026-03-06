<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThematicAxis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThematicAxisController extends Controller
{
    /** GET /api/admin/thematic-axes */
    public function index(): JsonResponse
    {
        $axes = ThematicAxis::orderBy('name')->get();

        return response()->json($axes);
    }

    /** GET /api/admin/thematic-axes/{thematicAxis} */
    public function show(ThematicAxis $thematicAxis): JsonResponse
    {
        return response()->json($thematicAxis);
    }

    /** POST /api/admin/thematic-axes */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'keywords'    => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $axis = ThematicAxis::create(array_merge($validated, ['is_active' => $validated['is_active'] ?? true]));

        return response()->json($axis, 201);
    }

    /** PUT /api/admin/thematic-axes/{thematicAxis} */
    public function update(Request $request, ThematicAxis $thematicAxis): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'keywords'    => 'nullable|string',
            'is_active'   => 'sometimes|boolean',
        ]);

        $thematicAxis->update($validated);

        return response()->json($thematicAxis);
    }

    /** DELETE /api/admin/thematic-axes/{thematicAxis} */
    public function destroy(ThematicAxis $thematicAxis): JsonResponse
    {
        $thematicAxis->delete();

        return response()->json(null, 204);
    }
}
