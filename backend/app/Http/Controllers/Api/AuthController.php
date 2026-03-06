<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** POST /api/register */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:8|confirmed',
            'phone'           => 'nullable|string|max:20',
            'document_type'   => 'nullable|in:cedula,pasaporte,cc_extranjera',
            'document_number' => 'nullable|string|max:50',
            'institution'     => 'nullable|string|max:255',
            'country'         => 'nullable|string|max:100',
            'city'            => 'nullable|string|max:100',
            'registration_type' => 'required|in:ponente,participante',
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => $validated['password'],
            'phone'             => $validated['phone'] ?? null,
            'document_type'     => $validated['document_type'] ?? null,
            'document_number'   => $validated['document_number'] ?? null,
            'institution'       => $validated['institution'] ?? null,
            'country'           => $validated['country'] ?? null,
            'city'              => $validated['city'] ?? null,
            'email_verified_at' => now(), // TODO: verificación real cuando se active el correo
        ]);

        $user->assignRole($validated['registration_type']);

        // event(new Registered($user)); // TODO: activar cuando se configure correo real

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $validated['registration_type'],
            ],
        ], 201);
    }

    /** POST /api/login */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->getRoleNames()->first(),
            ],
        ]);
    }

    /** POST /api/logout */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    /** GET /api/me */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'phone'             => $user->phone,
            'document_type'     => $user->document_type,
            'document_number'   => $user->document_number,
            'institution'       => $user->institution,
            'country'           => $user->country,
            'city'              => $user->city,
            'role'              => $user->getRoleNames()->first(),
        ]);
    }
}
