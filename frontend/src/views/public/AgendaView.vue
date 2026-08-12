<script setup lang="ts">
import NavBar from '../../components/NavBar.vue'
import FooterSection from '../../components/FooterSection.vue'

/**
 * Agenda pública — solo los inicios de cada jornada.
 *
 * El comité pidió publicar únicamente la apertura de cada día: la agenda
 * detallada sigue siendo preliminar y hay actividades sin confirmar, así que
 * el detalle por franja horaria no va a la web todavía. Tampoco se publican
 * los responsables internos de cada actividad.
 *
 * El **martes 13 no se publica** (confirmado por Francisco el 12-ago-2026:
 * "solo hay que publicar a partir del miércoles"). Es la jornada "Futuros
 * Ingenieros", dirigida a semilleros y colegios, y en el Excel venía marcada
 * en rojo completa. La agenda pública va del miércoles 14 al sábado 17.
 *
 * Fuente: "Agenda V10_Agosto5_FISI.xlsx" (comité organizador).
 */

interface Jornada {
  fecha: string
  dia: string
  titulo: string
  enfoque?: string
  hora: string
  apertura: string
  lugar: string
}

const jornadas: Jornada[] = [
  {
    fecha: '14',
    dia: 'Miércoles',
    titulo: 'Transformación Digital y Tecnología Humanocéntrica',
    hora: '7:30 a. m.',
    apertura: 'Registro general y entrega de kits',
    lugar: 'Plazoleta Caracolí, frente al Auditorio Juan Pablo II',
  },
  {
    fecha: '15',
    dia: 'Jueves',
    titulo: 'Tecnologías Emergentes y Sociedad',
    hora: '8:00 a. m.',
    apertura: 'Inicio de jornada académica',
    lugar: 'Auditorio Juan Pablo II',
  },
  {
    fecha: '16',
    dia: 'Viernes',
    titulo: 'Sostenibilidad, Inteligencia Avanzada y Redes del Futuro',
    hora: '8:00 a. m.',
    apertura: 'Inicio de jornada académica',
    lugar: 'Auditorio Juan Pablo II',
  },
  {
    fecha: '17',
    dia: 'Sábado',
    titulo: 'Integración y Salud',
    enfoque: 'Bienestar personal y cierre de comunidad.',
    hora: '8:30 a. m.',
    apertura: 'Jornada deportiva de integración',
    lugar: 'Escenario deportivo',
  },
]
</script>

<template>
  <div class="min-h-screen bg-cgr-bg">
    <NavBar />

    <main class="pt-16 lg:pl-72">

      <!-- HERO -->
      <section class="hero-gradient relative py-28 px-5 lg:px-20 overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cgr-purple-deep/30 rounded-full blur-3xl pointer-events-none" />
        <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-cgr-purple-dark/20 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 max-w-3xl mx-auto text-center">
          <div class="inline-flex items-center gap-2 border border-cgr-purple-dark rounded-full px-4 py-1.5 mb-8">
            <span class="w-2 h-2 rounded-full bg-cgr-purple animate-pulse" />
            <span class="text-cgr-accent text-xs font-semibold tracking-widest uppercase">14 al 17 de octubre · 2026</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight mb-6">
            <span class="text-white">Agenda del</span><br />
            <span class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple bg-clip-text text-transparent">
              Congreso
            </span>
          </h1>

          <p class="text-cgr-muted text-lg leading-relaxed max-w-2xl mx-auto">
            Cuatro jornadas en el campus de la
            <span class="text-white font-semibold">UPB Bucaramanga</span>,
            cada una con un eje temático propio.
          </p>
        </div>
      </section>

      <!-- JORNADAS -->
      <section class="bg-cgr-section py-20 px-5 lg:px-20">
        <div class="max-w-4xl mx-auto">

          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Inicio de cada jornada</span>
            <h2 class="mt-3 text-3xl font-black text-white">Cuatro días, cuatro enfoques</h2>
          </div>

          <!-- Aviso: la agenda todavía es preliminar -->
          <div class="mb-10 flex items-start gap-3 bg-cgr-card border border-cgr-purple/30 rounded-2xl px-5 py-4">
            <svg class="w-5 h-5 text-cgr-purple shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <p class="text-sm text-cgr-muted leading-relaxed">
              Esta agenda es <strong class="text-white font-semibold">preliminar</strong> y puede cambiar.
              Aquí publicamos el inicio de cada jornada; la programación detallada por franja horaria
              se publicará cuando el comité organizador la confirme.
            </p>
          </div>

          <!-- Línea de tiempo de jornadas -->
          <ol class="relative space-y-5">
            <li v-for="(j, i) in jornadas" :key="j.fecha">
              <article
                class="relative bg-cgr-card border border-cgr-border rounded-2xl p-6 sm:p-7 hover:border-cgr-purple/50 transition-colors"
              >
                <div class="flex flex-col sm:flex-row sm:items-start gap-5 sm:gap-7">

                  <!-- Fecha -->
                  <div class="shrink-0 flex sm:flex-col items-center sm:items-start gap-3 sm:gap-0">
                    <div class="text-center sm:text-left">
                      <p class="text-4xl sm:text-5xl font-black leading-none bg-gradient-to-br from-cgr-purple-dark to-cgr-purple bg-clip-text text-transparent">
                        {{ j.fecha }}
                      </p>
                      <p class="text-cgr-subtle text-xs uppercase tracking-widest mt-1.5">Octubre</p>
                    </div>
                    <span class="sm:mt-3 text-cgr-muted text-sm font-semibold">{{ j.dia }}</span>
                  </div>

                  <!-- Divider vertical (desktop) -->
                  <div class="hidden sm:block w-px self-stretch bg-gradient-to-b from-cgr-purple/40 to-transparent shrink-0" />

                  <!-- Contenido -->
                  <div class="flex-1 min-w-0">
                    <p class="text-cgr-purple text-[11px] font-semibold tracking-widest uppercase mb-1.5">
                      Jornada {{ i + 1 }} de {{ jornadas.length }}
                    </p>
                    <h3 class="text-white font-bold text-lg sm:text-xl leading-snug">
                      {{ j.titulo }}
                    </h3>
                    <p v-if="j.enfoque" class="text-cgr-muted text-sm leading-relaxed mt-2">
                      {{ j.enfoque }}
                    </p>

                    <!-- Inicio de la jornada -->
                    <div class="mt-5 bg-cgr-section border border-cgr-border rounded-xl px-4 py-3">
                      <div class="flex items-center gap-2 mb-1.5">
                        <svg class="w-4 h-4 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="text-white font-bold text-sm">{{ j.hora }}</span>
                        <span class="text-cgr-subtle text-xs">— {{ j.apertura }}</span>
                      </div>
                      <div class="flex items-start gap-2 text-cgr-subtle text-xs leading-relaxed">
                        <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                        </svg>
                        {{ j.lugar }}
                      </div>
                    </div>
                  </div>
                </div>
              </article>
            </li>
          </ol>
        </div>
      </section>

      <!-- CTA -->
      <section class="bg-cgr-bg py-20 px-5 lg:px-20">
        <div class="max-w-3xl mx-auto text-center">
          <h2 class="text-2xl sm:text-3xl font-black text-white mb-4">
            ¿Quieres participar como ponente?
          </h2>
          <p class="text-cgr-muted leading-relaxed mb-8">
            Consulta el llamado a ponencias, los ejes temáticos y las fechas clave del congreso.
          </p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <RouterLink
              to="/ponencias"
              class="inline-flex items-center justify-center bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white text-sm font-semibold px-6 py-3 rounded-lg hover:opacity-90 transition-opacity"
            >
              Llamado a Ponencias
            </RouterLink>
            <RouterLink
              to="/conferencistas"
              class="inline-flex items-center justify-center border border-cgr-purple/50 text-cgr-purple hover:bg-cgr-purple/10 text-sm font-semibold px-6 py-3 rounded-lg transition-colors"
            >
              Ver conferencistas
            </RouterLink>
          </div>
        </div>
      </section>

      <FooterSection />
    </main>
  </div>
</template>
