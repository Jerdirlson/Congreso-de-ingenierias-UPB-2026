<?php

namespace Database\Seeders;

use App\Models\AgendaItem;
use Illuminate\Database\Seeder;

/**
 * Agenda inicial del congreso, transcrita de los pósters oficiales de cada
 * jornada (miércoles 14 a sábado 17 de octubre). A partir de aquí, Liney puede
 * ajustar cualquier fila desde el panel admin (Agenda).
 *
 * Idempotente: limpia la tabla y la vuelve a cargar. Ejecutar con:
 *   php artisan db:seed --class=AgendaSeeder --force
 */
class AgendaSeeder extends Seeder
{
    public function run(): void
    {
        AgendaItem::query()->delete();

        $rows = [];
        $add = function (string $dia, ?string $bloque, string $tipo, string $titulo, ?string $ponente = null, ?string $pais = null, ?string $etiqueta = null, ?string $descripcion = null) use (&$rows) {
            $rows[] = compact('dia', 'bloque', 'tipo', 'titulo', 'ponente', 'pais', 'etiqueta', 'descripcion');
        };

        // ── MIÉRCOLES 14 — Transformación Digital y Tecnología Humanocéntrica ──
        $add('mie', 'manana', 'registro', 'Registro general y entrega de kits');
        $add('mie', 'manana', 'apertura', 'Apertura oficial');
        $add('mie', 'manana', 'conferencia', 'La Revolución de la Tecnología al Servicio de la Humanidad', 'Dra. Sindey C. Bernal Villamarín', 'Colombia', 'Conferencia Central');
        $add('mie', 'manana', 'foto', 'Foto oficial');
        $add('mie', 'manana', 'refrigerio', 'Refrigerio (incluido)');
        $add('mie', 'manana', 'conferencia', 'Economía del H₂ verde: tecnologías y desafíos para su implementación', 'Dr. Néstor Escalona Burgos', 'Chile', 'Conferencia Central');
        $add('mie', 'manana', 'almuerzo', 'Almuerzo libre');
        $add('mie', 'tarde', 'conferencia', 'Adición de hidrógeno a corrientes de gas natural: retos técnicos y económicos', 'Dr. Carlos Eduardo García Sánchez', 'Colombia');
        $add('mie', 'tarde', 'conferencia', 'Rehabilitación inmersiva: uso de realidad extendida y dispositivos vestibles en fisioterapia', 'Dr. Sergio Alexander Salinas', 'Canadá');
        $add('mie', 'tarde', 'ponencia', 'Sesiones paralelas de ponencias presenciales');
        $add('mie', 'tarde', 'refrigerio', 'Refrigerio (incluido)');
        $add('mie', 'tarde', 'conferencia', 'Cuando la IA ataca: cómo defenderse de la amenaza que pocos ven venir', 'Mg. José Vicente Gaviria', 'Estados Unidos');
        $add('mie', 'tarde', 'conferencia', 'Tecnologías limpias para valorizar residuos urbanos en energía', 'Dr. Néstor Escalona Burgos', 'Chile');
        $add('mie', 'tarde', 'conferencia', 'Aplicaciones del procesamiento digital de imágenes y aprendizaje de máquinas en medicina y biología', 'Dr. Manuel Forero Vargas', 'Colombia');
        $add('mie', 'tarde', 'cultural', 'Actividad cultural: Digital Fest');

        // ── JUEVES 15 — Tecnologías Emergentes y Sociedad ─────────────────────
        $add('jue', 'manana', 'conferencia', 'Inteligencia Artificial, bienestar laboral e Industria 5.0: hacia una ingeniería verdaderamente humanocéntrica', 'Dr. Christian Gárate Rodríguez', 'Perú', 'Conferencia Internacional');
        $add('jue', 'manana', 'ponencia', 'TinyML y Edge AI: ingeniería al servicio de las comunidades', 'Ing. Marcelo Rovai', 'Brasil', 'Workshop');
        $add('jue', 'manana', 'conferencia', 'Hidrógeno Verde y Energías Limpias', 'Dr. Néstor Escalona', 'Chile', 'Curso Taller');
        $add('jue', 'manana', 'ponencia', 'Sesiones paralelas de ponencias presenciales');
        $add('jue', 'manana', 'refrigerio', 'Refrigerio (incluido)');
        $add('jue', 'manana', 'almuerzo', 'Almuerzo libre');
        $add('jue', 'tarde', 'conferencia', 'Construyendo ecosistemas de innovación sostenible: experiencias en bioenergía y transferencia tecnológica', 'Dra. Neila Mantilla Barbosa', 'Colombia');
        $add('jue', 'tarde', 'conferencia', 'Tecnología en Logística y cadena de suministro', 'Dr. Miquel Serracanta', 'España');
        $add('jue', 'tarde', 'conferencia', 'Del dato al valor: cómo la transformación digital del mantenimiento impacta la confiabilidad industrial y el bienestar', 'MSc. Jhon A. Narváez Salazar', 'Colombia');
        $add('jue', 'tarde', 'conferencia', 'La Inteligencia Artificial Generativa: ¿Héroe o Villana?', 'Dr. Jesús López', 'Colombia');
        $add('jue', 'tarde', 'refrigerio', 'Refrigerio (incluido)');
        $add('jue', 'tarde', 'ponencia', 'Matriz/cuadrantes Patentes - Papers. Aplicación en toma de decisiones en Ingeniería.', 'Dr. Jhon Wilder Zartha', 'Colombia', 'Workshop');
        $add('jue', 'tarde', 'ponencia', 'Sesiones paralelas de ponencias presenciales');

        // ── VIERNES 16 — Sostenibilidad, Inteligencia Avanzada y Redes del Futuro ──
        $add('vie', 'manana', 'conferencia', 'Small Models, Big Impact: Rethinking AI at the Edge', 'Dr. Diego Méndez', 'Colombia');
        $add('vie', 'manana', 'conferencia', 'Tecnologías Emergentes para la Toma de Decisiones', 'Dr. Luis Asunción Pérez Domínguez', 'México');
        $add('vie', 'manana', 'conferencia', 'Hidrógeno Verde y Energías Limpias', 'Dr. Néstor Escalona', 'Chile', 'Curso Taller');
        $add('vie', 'manana', 'conferencia', 'Research Artificial Intelligence: Real-World Applications from Agriculture to Smart Sensors', 'Dr. Henry Arguello', 'Colombia');
        $add('vie', 'manana', 'conferencia', 'Estudios de Futuro: liderazgo prospectivo para la Ingeniería.', 'Dr. Jhon W. Zartha Sossa', 'Colombia');
        $add('vie', 'manana', 'refrigerio', 'Refrigerio (incluido)');
        $add('vie', 'manana', 'ponencia', 'Sesiones paralelas de ponencias presenciales / virtuales');
        $add('vie', 'manana', 'almuerzo', 'Almuerzo libre');
        $add('vie', 'tarde', 'conferencia', 'Ingeniería Aumentada por IA: El Puente hacia la Nueva Toma de Decisiones en la Industria', 'MSc. Javier Armando González', 'Colombia');
        $add('vie', 'tarde', 'conferencia', 'Vías terrestres', 'Experto por confirmar', null, 'Por confirmar', 'Pendiente por confirmar');
        $add('vie', 'tarde', 'conferencia', 'Construyendo Cadenas de Suministro Resilientes: El Rol Estratégico de la Infraestructura.', 'Dr. Jorge Hernández', 'Chile');
        $add('vie', 'tarde', 'ponencia', 'Workshop U-Think', 'Camilo Andrés Hernández y Silvia Nathalia Mantilla Niño', 'Colombia', 'Workshop');
        $add('vie', 'tarde', 'ponencia', 'Sesión de pósters');
        $add('vie', 'tarde', 'conferencia', 'Conferencia para egresados', null, null, null, 'Participación Internacional dirigida a Egresados y participantes del congreso');
        $add('vie', 'tarde', 'cultural', 'Cóctel y espacio de networking');

        // ── SÁBADO 17 — Integración y Salud (bienestar personal y cierre de comunidad) ──
        $add('sab', null, 'cultural', 'Jornada deportiva de integración', null, null, null, 'Muévete, comparte y fortalece lazos con tu comunidad. Deporte · Amistad · Bienestar');
        $add('sab', null, 'cultural', 'Cierre informal y premiación deportiva', null, null, null, 'Celebramos tu esfuerzo, talento y espíritu de equipo. Reconocimiento · Logros · Inspiración');
        $add('sab', null, 'almuerzo', 'Almuerzo de compañerismo', null, null, 'Incluido', 'Un espacio para compartir, conectar y cerrar esta gran experiencia juntos.');

        // Persistir con orden secuencial por día.
        $ordenPorDia = [];
        foreach ($rows as $row) {
            $dia = $row['dia'];
            $ordenPorDia[$dia] = ($ordenPorDia[$dia] ?? 0) + 1;
            AgendaItem::create($row + ['orden' => $ordenPorDia[$dia]]);
        }
    }
}
