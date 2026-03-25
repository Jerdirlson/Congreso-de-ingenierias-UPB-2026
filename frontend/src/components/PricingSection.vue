<script setup lang="ts">
import { useScrollReveal } from '../composables/useScrollReveal'
import { registrationOpen } from '../composables/useRegistration'

const { setRef } = useScrollReveal()

const categories = [
  {
    label: 'Inscripciones Estudiantes UPB',
    obs: 'Presencial con Ponencia o sin Ponencia',
    early: '$300.000',
    late: '$350.000',
  },
  {
    label: 'Inscripciones Estudiantes Externos',
    obs: 'Estudiantes externos con o sin ponencia',
    early: '$370.000',
    late: '$420.000',
  },
  {
    label: 'Inscripciones Profesionales',
    obs: 'Ponencia Presencial',
    early: '$670.000',
    late: '$720.000',
  },
  {
    label: 'Inscripciones Profesionales',
    obs: 'Ponencia Virtual',
    early: '$670.000',
    late: '$720.000',
  },
  {
    label: 'Inscripciones Estudiantes UPB',
    obs: 'Asistencias virtuales',
    early: '$350.000',
    late: '$400.000',
  },
  {
    label: 'Egresados',
    obs: 'Evento del viernes',
    early: '$50.000',
    late: '$50.000',
  },
]
</script>

<template>
  <section id="precios" class="bg-cgr-section py-24 px-5 lg:px-20">
    <div class="max-w-4xl mx-auto">

      <!-- Encabezado -->
      <div class="text-center mb-14">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Tarifas oficiales</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-black text-white">
          Inscripciones
        </h2>
        <p class="mt-4 text-cgr-muted max-w-xl mx-auto text-base leading-relaxed">
          Elige la categoría que corresponde a tu perfil. Las tarifas aumentan a partir del 10 de julio de 2026.
        </p>
      </div>

      <!-- Tabla de precios -->
      <div
        :ref="(el) => setRef(el as Element, 0)"
        class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl overflow-hidden mb-6"
      >
        <!-- Cabecera tabla -->
        <div class="grid grid-cols-[1fr_1fr_auto_auto] gap-x-4 px-6 py-3 bg-cgr-bg border-b border-cgr-border">
          <span class="text-xs font-semibold text-cgr-muted uppercase tracking-wider">Categoría</span>
          <span class="text-xs font-semibold text-cgr-muted uppercase tracking-wider">Observación</span>
          <span class="text-xs font-semibold text-green-400 uppercase tracking-wider text-right whitespace-nowrap">Hasta 10 jul</span>
          <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider text-right whitespace-nowrap">Después del 10 jul</span>
        </div>

        <!-- Filas -->
        <div
          v-for="(cat, i) in categories"
          :key="i"
          :ref="(el) => setRef(el as Element, i + 1)"
          class="animate-reveal grid grid-cols-[1fr_1fr_auto_auto] gap-x-4 items-center px-6 py-4 border-b border-cgr-border last:border-b-0 hover:bg-cgr-section transition-colors duration-150"
        >
          <span class="text-white text-sm font-medium leading-snug">{{ cat.label }}</span>
          <span class="text-cgr-muted text-sm leading-snug">{{ cat.obs }}</span>
          <span class="text-green-400 text-sm font-bold text-right whitespace-nowrap">{{ cat.early }}</span>
          <span class="text-amber-400 text-sm font-bold text-right whitespace-nowrap">{{ cat.late }}</span>
        </div>
      </div>

      <!-- Nota NRC -->
      <div
        :ref="(el) => setRef(el as Element, 6)"
        class="animate-reveal flex items-start gap-3 bg-cgr-card border border-cgr-border rounded-xl px-5 py-4 mb-8"
      >
        <svg class="w-4 h-4 text-cgr-purple shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-cgr-muted text-sm leading-relaxed">
          Los <span class="text-white font-medium">códigos NRC para el pago</span> serán habilitados próximamente.
          Los valores incluyen acceso a todas las sesiones, libro de memorias con ISSN y certificado digital de participación.
        </p>
      </div>

      <!-- CTA de inscripción -->
      <div class="flex flex-col items-center gap-1.5">
        <RouterLink
          v-if="registrationOpen"
          to="/login"
          class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-10 py-3.5 rounded-xl hover:opacity-90 transition-opacity text-sm"
        >
          Inscribirme ahora
        </RouterLink>
        <button
          v-else
          disabled
          class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-10 py-3.5 rounded-xl opacity-50 cursor-not-allowed pointer-events-none text-sm"
        >
          Inscribirme ahora
        </button>
        <span v-if="!registrationOpen" class="text-cgr-muted text-xs">
          Pronto serán activadas las inscripciones
        </span>
      </div>

    </div>
  </section>
</template>
