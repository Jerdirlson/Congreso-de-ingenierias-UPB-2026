<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /** GET /api/settings — banderas públicas para que el frontend se adapte */
    public function publicSettings(): JsonResponse
    {
        return response()->json($this->payload());
    }

    /** PUT /api/admin/settings — actualizar banderas (admin) */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ponente_registration_open' => 'sometimes|boolean',
            'submissions_open'          => 'sometimes|boolean',
        ]);

        if (array_key_exists('ponente_registration_open', $validated)) {
            AppSetting::setBool(AppSetting::PONENTE_REGISTRATION_OPEN, $validated['ponente_registration_open']);
        }

        if (array_key_exists('submissions_open', $validated)) {
            AppSetting::setBool(AppSetting::SUBMISSIONS_OPEN, $validated['submissions_open']);
        }

        return response()->json($this->payload());
    }

    private function payload(): array
    {
        return [
            'ponente_registration_open' => AppSetting::getBool(AppSetting::PONENTE_REGISTRATION_OPEN, true),
            'submissions_open'          => AppSetting::getBool(AppSetting::SUBMISSIONS_OPEN, true),
        ];
    }
}
