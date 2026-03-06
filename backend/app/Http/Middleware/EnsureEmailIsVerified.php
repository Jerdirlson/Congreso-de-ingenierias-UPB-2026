<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Bloquea el acceso si el correo no está verificado.
     * Devuelve 403 JSON para APIs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Debes verificar tu correo electrónico para acceder a esta función.',
                'email_verified' => false,
            ], 403);
        }

        return $next($request);
    }
}
