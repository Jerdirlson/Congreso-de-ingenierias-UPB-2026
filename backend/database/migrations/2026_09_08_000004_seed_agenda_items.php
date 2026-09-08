<?php

use App\Models\AgendaItem;
use Database\Seeders\AgendaSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Carga inicial de la agenda (transcrita de los pósters oficiales) en cualquier
 * entorno, incluido producción, donde el pipeline solo corre `migrate --force`.
 *
 * Idempotente y seguro: solo siembra si la tabla está vacía, así NUNCA sobrescribe
 * los ajustes que el equipo (Liney) haga después desde el panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (AgendaItem::query()->exists()) {
            return; // Ya hay agenda cargada; no tocar.
        }

        (new AgendaSeeder())->run();
    }

    public function down(): void
    {
        // No-op: no borramos la agenda en un rollback para no perder datos.
    }
};
