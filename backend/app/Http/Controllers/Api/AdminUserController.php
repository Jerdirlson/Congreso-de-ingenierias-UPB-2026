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

        // Estado de la inscripción en el portal UPB:
        // paid = pago verificado · confirmed = dijo que se inscribió, pago sin verificar · pending = nada.
        if ($request->filled('payment')) {
            match ($request->payment) {
                'paid'      => $query->whereNotNull('external_registration_paid_at'),
                'confirmed' => $query->whereNotNull('external_registration_at')->whereNull('external_registration_paid_at'),
                'pending'   => $query->whereNull('external_registration_at')->whereNull('external_registration_paid_at'),
                default     => null,
            };
        }

        $users = $query->paginate(25)->through(fn (User $u) => [
            'id'                  => $u->id,
            'name'                => $u->name,
            'email'               => $u->email,
            'role'                => collect($u->getRoleNames())->first(fn ($r) => $r !== 'revisor') ?? $u->getRoleNames()->first(),
            'roles'               => $u->getRoleNames()->values()->all(),
            'institution'         => $u->institution,
            'country'             => $u->country,
            'city'                => $u->city,
            'phone'               => $u->phone,
            'document_type'       => $u->document_type,
            'document_number'     => $u->document_number,
            'email_verified_at'   => $u->email_verified_at?->toIso8601String(),
            'created_at'          => $u->created_at->toIso8601String(),
            'submissions_count'   => $u->submissions_count,
            'registrations_count' => $u->registrations_count,
            'payments_count'      => $u->payments_count,
            'external_registration_at'      => $u->external_registration_at?->toIso8601String(),
            'external_registration_paid_at' => $u->external_registration_paid_at?->toIso8601String(),
        ]);

        return response()->json($users);
    }

    // GET /api/admin/users/{user}
    public function show(User $user): JsonResponse
    {
        $user->load(['submissions:id,title,status,created_at,user_id', 'registrations:id,registration_type,modality,confirmed_at,user_id', 'payments:id,amount,currency,status,created_at,user_id']);

        $roleNames = $user->getRoleNames()->values()->all();

        return response()->json([
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'role'              => collect($roleNames)->first(fn ($r) => $r !== 'revisor') ?? $roleNames[0] ?? null,
            'roles'             => $roleNames,
            'phone'             => $user->phone,
            'document_type'     => $user->document_type,
            'document_number'   => $user->document_number,
            'institution'       => $user->institution,
            'country'           => $user->country,
            'city'              => $user->city,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at'        => $user->created_at->toIso8601String(),
            'external_registration_at'      => $user->external_registration_at?->toIso8601String(),
            'external_registration_paid_at' => $user->external_registration_paid_at?->toIso8601String(),
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
            'id'    => $user->id,
            'role'  => $user->getRoleNames()->first(),
            'roles' => $user->getRoleNames()->values()->all(),
        ]);
    }

    // PATCH /api/admin/users/{user}/payment — marcar/desmarcar a mano el pago verificado
    // de la inscripción en el portal UPB (lo que el usuario ve como "totalmente inscrito").
    public function updatePayment(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'paid' => 'required|boolean',
        ]);

        if ($validated['paid']) {
            // Conserva la fecha original si ya estaba marcado (idempotente).
            $user->external_registration_paid_at ??= now();
        } else {
            $user->external_registration_paid_at = null;
        }
        $user->save();

        return response()->json([
            'id'                            => $user->id,
            'external_registration_at'      => $user->external_registration_at?->toIso8601String(),
            'external_registration_paid_at' => $user->external_registration_paid_at?->toIso8601String(),
        ]);
    }

    // POST /api/admin/users/{user}/assign-reviewer
    public function assignReviewer(User $user): JsonResponse
    {
        $user->assignRole('revisor');

        return response()->json([
            'id'    => $user->id,
            'roles' => $user->getRoleNames()->values()->all(),
        ]);
    }

    // DELETE /api/admin/users/{user}/remove-reviewer
    public function removeReviewer(User $user): JsonResponse
    {
        $user->removeRole('revisor');

        return response()->json([
            'id'    => $user->id,
            'roles' => $user->getRoleNames()->values()->all(),
        ]);
    }
}
