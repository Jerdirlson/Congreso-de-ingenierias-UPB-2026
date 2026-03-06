<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /** POST /api/payments — iniciar pago
     *  TODO: integrar pasarela real (Wompi, PayU, etc.)
     *  Por ahora: modo demo — confirma el pago inmediatamente y genera ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'registration_type' => 'required|in:participant,speaker',
            'submission_id'     => 'nullable|exists:submissions,id',
            'congress_event_id' => 'nullable|exists:congress_events,id',
        ]);

        $user = $request->user();

        // Precio según el evento seleccionado o valor por defecto
        $congressEvent = $validated['congress_event_id']
            ? \App\Models\CongressEvent::find($validated['congress_event_id'])
            : null;

        if ($validated['registration_type'] === 'speaker') {
            // El pago del ponente es previo a la ponencia — no requiere submission_id
            abort_if(
                $user->registrations()->where('registration_type', 'speaker')->whereNotNull('ticket_code')->exists(),
                422,
                'Ya tienes una inscripción como ponente confirmada.'
            );
            $submission = null;
            $amount = $congressEvent ? (float) $congressEvent->price : 200000;
        } else {
            abort_if(
                $user->registrations()->where('registration_type', 'participant')->whereNotNull('ticket_code')->exists(),
                422,
                'Ya tienes una inscripción como participante confirmada.'
            );
            $submission = null;
            $amount = $congressEvent ? (float) $congressEvent->price : 200000;
        }

        // Modo demo: marcar como completado directamente
        $payment = Payment::create([
            'user_id'           => $user->id,
            'submission_id'     => $submission?->id,
            'registration_type' => $validated['registration_type'],
            'amount'            => $amount,
            'currency'          => 'COP',
            'status'            => Payment::STATUS_COMPLETED,
            'paid_at'           => now(),
        ]);

        $ticketCode = Registration::generateTicketCode();
        $registration = Registration::create([
            'user_id'           => $user->id,
            'payment_id'        => $payment->id,
            'submission_id'     => $submission?->id,
            'congress_event_id' => $validated['congress_event_id'] ?? null,
            'registration_type' => $validated['registration_type'],
            'modality'          => $submission?->modality ? $this->mapModality($submission->modality) : null,
            'ticket_code'       => $ticketCode,
            'confirmed_at'      => now(),
        ]);

        if ($submission) {
            $submission->advanceTo('confirmed');
        }

        return response()->json([
            'demo'         => true,
            'payment'      => $payment,
            'registration' => $registration,
            'ticket_code'  => $ticketCode,
        ], 201);
    }

    /** POST /api/webhooks/payment — callback de pasarela (mock) */
    public function webhook(Request $request): JsonResponse
    {
        // En producción: verificar firma HMAC de la pasarela
        $paymentId = $request->input('payment_id');
        $status = $request->input('status', 'completed');

        $payment = Payment::find($paymentId);
        if (! $payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($status === 'completed') {
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'paid_at' => now(),
            ]);

            $ticketCode = Registration::generateTicketCode();
            Registration::create([
                'user_id'          => $payment->user_id,
                'payment_id'       => $payment->id,
                'submission_id'   => $payment->submission_id,
                'registration_type' => $payment->registration_type,
                'modality'        => $payment->submission?->modality ? $this->mapModality($payment->submission->modality) : null,
                'ticket_code'     => $ticketCode,
                'confirmed_at'    => now(),
            ]);

            if ($payment->submission_id) {
                $payment->submission->advanceTo('confirmed');
            }
        } else {
            $payment->update(['status' => Payment::STATUS_FAILED]);
        }

        return response()->json(['ok' => true]);
    }

    private function mapModality(string $modality): string
    {
        return match ($modality) {
            'presencial_oral', 'presencial_poster' => 'presencial',
            'virtual' => 'virtual',
            'proyecto_aula' => 'proyecto_aula',
            default => 'presencial',
        };
    }
}
