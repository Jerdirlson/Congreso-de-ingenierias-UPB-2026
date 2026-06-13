<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    /**
     * POST /api/password/forgot
     * Envía un código de 6 dígitos al correo si existe una cuenta asociada.
     * Responde siempre de forma genérica para no revelar si el correo existe.
     */
    public function sendCode(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email'], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'Ingresa un correo electrónico válido.',
        ]);

        $user = User::where('email', $request->email)->first();

        $user?->sendPasswordResetCode();

        return response()->json([
            'message' => 'Si el correo está registrado, recibirás un código para restablecer tu contraseña.',
        ]);
    }

    /**
     * POST /api/password/verify-code
     * Verifica que el código sea válido (sin consumirlo). Útil para el paso
     * intermedio del flujo en el frontend.
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $this->codeIsValid($user, $request->code)) {
            return response()->json(['message' => 'Código incorrecto o expirado.'], 422);
        }

        return response()->json(['message' => 'Código verificado.']);
    }

    /**
     * POST /api/password/reset
     * Restablece la contraseña si el código es válido y no ha expirado.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required'  => 'La nueva contraseña es obligatoria.',
            'password.min'       => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $this->codeIsValid($user, $request->code)) {
            return response()->json(['message' => 'Código incorrecto o expirado.'], 422);
        }

        $user->password                  = $request->password;
        $user->password_reset_code       = null;
        $user->password_reset_expires_at = null;
        $user->save();

        // Por seguridad, cierra todas las sesiones activas tras el cambio.
        $user->tokens()->delete();

        return response()->json(['message' => 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.']);
    }

    private function codeIsValid(?User $user, string $code): bool
    {
        return $user
            && $user->password_reset_code
            && $user->password_reset_expires_at
            && ! $user->password_reset_expires_at->isPast()
            && hash_equals($user->password_reset_code, $code);
    }
}
