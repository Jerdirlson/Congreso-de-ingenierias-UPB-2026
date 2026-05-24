<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
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
            'phone'           => 'required|string|max:20',
            'document_type'   => 'required|in:cedula,pasaporte,cc_extranjera',
            'document_number' => 'required|string|max:50',
            'institution'     => 'required|string|max:255',
            'country'         => 'required|string|max:100',
            'city'            => 'required|string|max:100',
            'registration_type' => 'required|in:ponente,participante',
        ], [
            'name.required'            => 'El nombre completo es obligatorio para emitir el certificado.',
            'email.required'           => 'El correo electrónico es obligatorio.',
            'phone.required'           => 'El teléfono es obligatorio.',
            'document_type.required'   => 'El tipo de documento es obligatorio.',
            'document_number.required' => 'El número de documento es obligatorio para el certificado.',
            'institution.required'     => 'La institución es obligatoria.',
            'country.required'         => 'El país es obligatorio.',
            'city.required'            => 'La ciudad es obligatoria.',
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => $validated['password'],
            'phone'             => $validated['phone'],
            'document_type'     => $validated['document_type'],
            'document_number'   => $validated['document_number'],
            'institution'       => $validated['institution'],
            'country'           => $validated['country'],
            'city'              => $validated['city'],
        ]);

        $user->assignRole($validated['registration_type']);

        event(new Registered($user));

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
