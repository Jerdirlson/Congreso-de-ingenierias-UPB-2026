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
     * Verifica el correo al hacer clic en el enlace del email.
     * No requiere auth: la URL firmada + hash validan la identidad.
     */
    public function verify(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            $frontendUrl = rtrim(config('app.frontend_url', config('app.url')), '/');
            return redirect()->away("{$frontendUrl}/verify-email?verified=1");
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Enlace de verificación inválido.'], 403);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        $frontendUrl = rtrim(config('app.frontend_url', config('app.url')), '/');
        return redirect()->away("{$frontendUrl}/verify-email?verified=1");
    }

    /**
     * Reenvía el correo de verificación.
     */
    public function resend(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'El correo ya está verificado.',
                'email_verified' => true,
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Se ha enviado un nuevo enlace de verificación a tu correo.',
        ]);
    }
}
