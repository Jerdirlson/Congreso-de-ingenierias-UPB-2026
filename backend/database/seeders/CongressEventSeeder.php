<?php

namespace Database\Seeders;

use App\Models\CongressEvent;
use Illuminate\Database\Seeder;

class CongressEventSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar eventos existentes (desactivar FK temporalmente)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CongressEvent::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
}
