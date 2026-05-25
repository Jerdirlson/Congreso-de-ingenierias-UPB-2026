<?php

use App\Models\CongressEvent;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (CongressEvent::query()->exists()) {
            return;
        }

        CongressEvent::create([
            'name'        => 'Congreso Internacional de Ingeniería 2026',
            'description' => 'El Congreso Internacional de Ingeniería 2026, organizado por la Universidad Pontificia Bolivariana seccional Bucaramanga, es el espacio académico donde investigadores, docentes, estudiantes y profesionales del sector convergen para compartir avances científicos, proyectos de innovación y tendencias en las distintas ramas de la ingeniería.',
            'location'    => 'Universidad Pontificia Bolivariana — Bucaramanga, Colombia',
            'modality'    => 'hibrido',
            'event_date'  => '2026-10-14',
            'start_time'  => '08:00:00',
            'end_time'    => null,
            'speaker'     => null,
            'category'    => 'Congreso',
            'capacity'    => null,
            'is_free'     => false,
            'price'       => 80000,
            'currency'    => 'COP',
            'is_active'   => true,
        ]);
    }

    public function down(): void
    {
        // No-op: si hay un evento creado por el admin no debe perderse al revertir.
    }
};
