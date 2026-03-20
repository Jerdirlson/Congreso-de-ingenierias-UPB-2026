<?php

namespace Database\Seeders;

use App\Models\ThematicAxis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ThematicAxisSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar ejes anteriores (deshabilitar FK temporalmente)
        Schema::disableForeignKeyConstraints();
        ThematicAxis::truncate();
        Schema::enableForeignKeyConstraints();

        $axes = [
            [
                'name'        => 'Ingeniería Aplicada con propósito humano y bienestar',
                'description' => 'Diseño centrado en el usuario, ergonomía cognitiva, ética en el diseño y tecnologías para la salud. Biomecánica, diseño de máquinas humanocéntricas. Tecnologías de asistencia e inclusión, ingeniería para el cuidado, sistemas de seguridad proactiva y confort ambiental.',
                'keywords'    => 'diseño centrado en el usuario, ergonomía cognitiva, biomecánica, tecnologías de asistencia, inclusión, salud',
            ],
            [
                'name'        => 'Tecnologías 5.0',
                'description' => 'Evolución de la IA, Robótica colaborativa (Cobots), Gemelos Digitales, IoT e hiper-personalización industrial. Automatización y sistemas inteligentes, Redes Inteligentes y Autónomas, Transformación Digital, Industria 4.0, Ciencia de Datos, Sistemas Ciber-Físico-Sociales (CPSS), Edge Computing, IA Explicable (XAI).',
                'keywords'    => 'IA, robótica colaborativa, cobots, gemelos digitales, IoT, edge computing, XAI, industria 4.0, CPSS',
            ],
            [
                'name'        => 'Sostenibilidad y Logística Verde',
                'description' => 'Energías limpias, economía circular, descarbonización y cadenas de suministro resilientes. Materiales para Ingeniería, Movilidad Inteligente, Transición Energética, economía circular de plásticos, tecnologías energéticas sostenibles, pilas de combustible, textiles inteligentes.',
                'keywords'    => 'energías limpias, economía circular, descarbonización, movilidad inteligente, transición energética, sostenibilidad',
            ],
            [
                'name'        => 'Innovación y Transformación Digital',
                'description' => 'Nuevos modelos de negocio, agilidad organizacional, digitalización en la productividad, sistemas mecánicos, manufactura avanzada, gestión de activos, Servitización (MaaS), PLM y Ecosistemas de Innovación Abierta.',
                'keywords'    => 'transformación digital, manufactura avanzada, servitización, PLM, innovación abierta, gestión de activos',
            ],
            [
                'name'        => 'Gobernanza y Ciberseguridad',
                'description' => 'Ética de los datos, protección de infraestructuras críticas, legislación tecnológica, soberanía digital, derecho digital, ciberseguridad industrial, Resiliencia Ciber-Física, Privacidad por Diseño (PbD) e Identidad Digital Segura.',
                'keywords'    => 'ciberseguridad, ética de datos, soberanía digital, privacidad por diseño, infraestructuras críticas, identidad digital',
            ],
            [
                'name'        => 'Retos Regionales y Sector Productivo',
                'description' => 'Transformación local, cierre de brechas tecnológicas, políticas públicas, gestión de activos, transferencia tecnológica rural/urbana, encadenamientos productivos regionales e innovación social de base tecnológica.',
                'keywords'    => 'transformación regional, brechas tecnológicas, políticas públicas, transferencia tecnológica, innovación social',
            ],
            [
                'name'        => 'Desarrollo tecnológico, sociedad y educación',
                'description' => 'Impacto de tecnologías emergentes en la sociedad, estrategias pedagógicas en ingeniería, ecosistemas educativos digitales, formación en Soft Skills, EdTech y apropiación social del conocimiento.',
                'keywords'    => 'educación en ingeniería, EdTech, soft skills, pedagogía, tecnologías emergentes, apropiación del conocimiento',
            ],
            [
                'name'        => 'Ingeniería de Software Inteligente y Automatizada',
                'description' => 'DevOps, DevSecOps y GitOps; MLOps; AIOps; Infraestructura como Código; Arquitecturas cloud-native y microservicios; Ingeniería de requisitos asistida por IA; Testing automatizado; Generación de código con IA; Gobierno de modelos; Platform Engineering. Modelado y Simulación.',
                'keywords'    => 'DevOps, MLOps, AIOps, cloud-native, microservicios, platform engineering, IA generativa, testing automatizado',
            ],
        ];

        foreach ($axes as $axis) {
            ThematicAxis::create(array_merge($axis, ['is_active' => true]));
        }
    }
}
