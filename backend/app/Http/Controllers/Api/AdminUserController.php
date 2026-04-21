<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    // GET /api/admin/users
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles:id,name')
            ->withCount(['submissions', 'registrations', 'payments'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('institution', 'like', "%{$s}%")
                  ->orWhere('document_number', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('verified')) {
            $request->verified === 'yes'
                ? $query->whereNotNull('email_verified_at')
                : $query->whereNull('email_verified_at');
        }

        $users = $query->paginate(25)->through(fn (User $u) => [
            'id'                => $u->id,
            'name'              => $u->name,
            'email'             => $u->email,
            'role'              => $u->getRoleNames()->first(),
            'institution'       => $u->institution,
            'country'           => $u->country,
            'city'              => $u->city,
            'phone'             => $u->phone,
            'document_type'     => $u->document_type,
            'document_number'   => $u->document_number,
            'email_verified_at' => $u->email_verified_at?->toIso8601String(),
            'created_at'        => $u->created_at->toIso8601String(),
            'submissions_count' => $u->submissions_count,
            'registrations_count' => $u->registrations_count,
            'payments_count'    => $u->payments_count,
        ]);

        return response()->json($users);
    }

    // GET /api/admin/users/{user}
    public function show(User $user): JsonResponse
    {
        $user->load(['submissions:id,title,status,created_at,user_id', 'registrations:id,registration_type,modality,confirmed_at,user_id', 'payments:id,amount,currency,status,created_at,user_id']);

        return response()->json([
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'role'              => $user->getRoleNames()->first(),
            'phone'             => $user->phone,
            'document_type'     => $user->document_type,
            'document_number'   => $user->document_number,
            'institution'       => $user->institution,
            'country'           => $user->country,
            'city'              => $user->city,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at'        => $user->created_at->toIso8601String(),
            'submissions'       => $user->submissions,
            'registrations'     => $user->registrations,
            'payments'          => $user->payments,
        ]);
    }

    // PATCH /api/admin/users/{user}/role
    public function updateRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->syncRoles([$validated['role']]);

        return response()->json([
            'id'   => $user->id,
            'role' => $user->getRoleNames()->first(),
        ]);
    }
}
