<?php

namespace Database\Seeders;

use App\Models\ThematicAxis;
use Illuminate\Database\Seeder;

class ThematicAxisSeeder extends Seeder
{
    public function run(): void
    {
        $axes = [
            [
                'name'        => 'Inteligencia Artificial e Industria 4.0',
                'description' => 'Trabajos relacionados con machine learning, automatización, IoT, digitalización de procesos industriales y transformación digital.',
                'keywords'    => 'IA, inteligencia artificial, industria 4.0, machine learning, IoT, automatización, digitalización',
            ],
            [
                'name'        => 'Ingeniería Sostenible y Medio Ambiente',
                'description' => 'Investigaciones en energías renovables, gestión ambiental, economía circular, huella de carbono y desarrollo sostenible.',
                'keywords'    => 'sostenibilidad, medio ambiente, energías renovables, economía circular, huella de carbono',
            ],
            [
                'name'        => 'Innovación en Educación en Ingeniería',
                'description' => 'Metodologías activas, ABP, gamificación, laboratorios virtuales, formación por competencias y experiencias pedagógicas.',
                'keywords'    => 'educación, ABP, gamificación, metodologías activas, competencias, laboratorios virtuales',
            ],
            [
                'name'        => 'Ingeniería de Software y Sistemas',
                'description' => 'Desarrollo de software, arquitecturas, DevOps, ciberseguridad, bases de datos y sistemas de información.',
                'keywords'    => 'software, DevOps, ciberseguridad, arquitectura, bases de datos',
            ],
            [
                'name'        => 'Proyectos de Aula y Experiencias Exitosas',
                'description' => 'Trabajos destacados de estudiantes de ingeniería UPB y experiencias de colegios seleccionados.',
                'keywords'    => 'proyecto aula, estudiantes, colegios, experiencias exitosas',
            ],
        ];

        foreach ($axes as $axis) {
            ThematicAxis::firstOrCreate(
                ['name' => $axis['name']],
                array_merge($axis, ['is_active' => true])
            );
        }
    }
}
