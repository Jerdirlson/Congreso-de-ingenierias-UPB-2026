<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BulkMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminMailController extends Controller
{
    /**
     * POST /api/admin/mail/preview
     * Devuelve la cantidad de destinatarios que recibirían el correo.
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient_type'  => 'required|in:all,by_role,external,combined',
            'roles'           => 'array',
            'roles.*'         => 'string|in:ponente,participante,revisor,admin,administrativo',
            'external_emails' => 'nullable|string',
        ]);

        [$internal, $external] = $this->resolveRecipients($validated);

        return response()->json([
            'internal_count' => count($internal),
            'external_count' => count($external),
            'total'          => count($internal) + count($external),
        ]);
    }

    /**
     * POST /api/admin/mail/send
     * Envía el correo masivo a los destinatarios seleccionados.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject'         => 'required|string|max:255',
            'body'            => 'required|string|max:10000',
            'recipient_type'  => 'required|in:all,by_role,external,combined',
            'roles'           => 'array',
            'roles.*'         => 'string|in:ponente,participante,revisor,admin,administrativo',
            'external_emails' => 'nullable|string',
        ], [
            'subject.required' => 'El asunto es obligatorio.',
            'body.required'    => 'El cuerpo del mensaje es obligatorio.',
        ]);

        [$internal, $external] = $this->resolveRecipients($validated);

        $sent  = 0;
        $errors = [];

        foreach ($internal as $user) {
            try {
                Mail::to($user->email)->send(
                    new BulkMail($validated['subject'], $validated['body'], $user->name)
                );
                $sent++;
            } catch (\Throwable $e) {
                $errors[] = $user->email;
            }
        }

        foreach ($external as $email) {
            try {
                Mail::to($email)->send(
                    new BulkMail($validated['subject'], $validated['body'])
                );
                $sent++;
            } catch (\Throwable $e) {
                $errors[] = $email;
            }
        }

        return response()->json([
            'sent'   => $sent,
            'failed' => count($errors),
            'errors' => $errors,
        ]);
    }

    /** Resuelve listas de destinatarios internos (Users) y externos (strings). */
    private function resolveRecipients(array $validated): array
    {
        $type    = $validated['recipient_type'];
        $roles   = $validated['roles'] ?? [];
        $extRaw  = $validated['external_emails'] ?? '';

        // Externos: separados por coma, salto de línea o punto y coma
        $external = [];
        if (in_array($type, ['external', 'combined']) && $extRaw) {
            $external = array_values(array_filter(
                array_map('trim', preg_split('/[\s,;]+/', $extRaw) ?: []),
                fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL)
            ));
        }

        // Internos
        $internal = collect();
        if (in_array($type, ['all', 'by_role', 'combined'])) {
            $query = User::whereNotNull('email_verified_at')->select('id', 'name', 'email');
            if ($type === 'by_role' && count($roles)) {
                $query->whereHas('roles', fn ($q) => $q->whereIn('name', $roles));
            } elseif ($type === 'combined' && count($roles)) {
                $query->whereHas('roles', fn ($q) => $q->whereIn('name', $roles));
            }
            $internal = $query->get();
        }

        return [$internal, $external];
    }
}
