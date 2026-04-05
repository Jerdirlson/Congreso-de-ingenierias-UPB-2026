<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * Verifica el correo mediante el código de 6 dígitos enviado por email.
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message'        => 'El correo ya está verificado.',
                'email_verified' => true,
            ]);
        }

        if (
            ! $user->email_verification_code ||
            ! hash_equals($user->email_verification_code, $request->code)
        ) {
            return response()->json(['message' => 'Código incorrecto.'], 422);
        }

        if ($user->email_verification_expires_at->isPast()) {
            return response()->json([
                'message' => 'El código ha expirado. Solicita uno nuevo.',
            ], 422);
        }

        $user->email_verification_code       = null;
        $user->email_verification_expires_at = null;
        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json([
            'message'        => 'Correo verificado correctamente.',
            'email_verified' => true,
        ]);
    }

    /**
     * Reenvía el código de verificación al correo del usuario.
     */
    public function resend(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message'        => 'El correo ya está verificado.',
                'email_verified' => true,
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Se ha enviado un nuevo código de verificación a tu correo.',
        ]);
    }
}
