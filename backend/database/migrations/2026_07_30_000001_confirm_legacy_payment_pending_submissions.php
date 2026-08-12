<?php

use App\Models\Submission;
use Illuminate\Database\Migrations\Migration;

/**
 * El paso "Pago pendiente" sale del flujo: la ponencia queda confirmada en
 * cuanto se aprueba, y la inscripción/pago se gestiona aparte en el portal UPB.
 *
 * Esta migración mueve al paso siguiente (`confirmed`) las ponencias que
 * quedaron en `payment_pending`, para que no se queden en un estado que el
 * flujo ya no produce. El hook de `Submission::updated` deja constancia de
 * cada cambio en `submission_events` (evento `estado_cambiado`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Submission::withTrashed()
            ->where('status', Submission::STATUS_PAYMENT_PENDING)
            ->get()
            ->each(fn (Submission $s) => $s->advanceTo(Submission::STATUS_CONFIRMED));
    }

    public function down(): void
    {
        // Irreversible a propósito: una vez confirmadas no hay forma de
        // distinguirlas del resto de ponencias confirmadas. La bitácora
        // `submission_events` conserva el registro de qué se cambió.
    }
};
