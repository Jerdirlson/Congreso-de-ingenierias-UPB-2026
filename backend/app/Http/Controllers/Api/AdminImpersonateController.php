<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminImpersonateController extends Controller
{
    public function store(Request $request, User $user): JsonResponse
    {
        abort_if(
            $user->hasAnyRole(['admin', 'administrativo']),
            403,
            'No se puede impersonar a un administrador.'
        );

        $token = $user->createToken('impersonation')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => array_merge($user->toArray(), [
                'role' => $user->getRoleNames()->first(),
            ]),
        ]);
    }
}
