<script setup lang="ts">
const plans = [
  {
    label: 'Estudiantes',
    price: 'Gratis',
    sub: 'Con carné vigente',
    highlight: false,
  },
  {
    label: 'Profesionales',
    price: '$150.000',
    sub: 'COP · IVA incluido',
    highlight: true,
  },
  {
    label: 'Empresas',
    price: '$300.000',
    sub: 'COP · 3 accesos',
    highlight: false,
  },
]
</script>

<template>
  <section
    id="inscripcion"
    class="relative py-24 px-5 lg:px-20 overflow-hidden"
    style="background: radial-gradient(ellipse at center, #2E1065 0%, #0A0A1A 70%);"
  >
    <!-- Círculos decorativos -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-cgr-purple/5 rounded-full blur-3xl pointer-events-none" />

    <div class="relative z-10 max-w-5xl mx-auto text-center">

      <!-- Icono cohete -->
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-cgr-purple/20 border border-cgr-purple/30 mb-8">
        <svg class="w-8 h-8 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.63 2.17a14.98 14.98 0 0 0-5.84 7.38m11.8 4.82A6 6 0 0 1 2 16.37m13.59-2V12a6 6 0 0 0-6-6H8.37"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </div>

      <!-- Título -->
      <h2 class="text-3xl sm:text-5xl font-black text-white mb-4 leading-tight">
        Sé parte del congreso<br />
        <span class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple bg-clip-text text-transparent">
          más importante del año
        </span>
      </h2>
      <p class="text-cgr-muted text-lg mb-12 max-w-2xl mx-auto">
        Cupos limitados. Regístrate antes del 30 de septiembre y accede a todos los eventos, talleres y materiales del congreso.
      </p>

      <!-- Planes de precios -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-12">
        <div
          v-for="plan in plans"
          :key="plan.label"
          class="rounded-2xl p-8 flex flex-col items-center gap-2 transition-transform hover:-translate-y-1"
          :class="plan.highlight
            ? 'bg-gradient-to-b from-cgr-purple-dark/40 to-cgr-purple-deep/40 border-2 border-cgr-purple'
            : 'bg-cgr-card border border-cgr-border'"
        >
          <span
            class="text-xs font-semibold tracking-widest uppercase mb-2"
            :class="plan.highlight ? 'text-cgr-accent' : 'text-cgr-muted'"
          >
            {{ plan.label }}
          </span>
          <span
            class="text-3xl font-black"
            :class="plan.highlight ? 'text-cgr-purple' : 'text-white'"
          >
            {{ plan.price }}
          </span>
          <span class="text-cgr-subtle text-xs">{{ plan.sub }}</span>

          <div v-if="plan.highlight" class="mt-3 text-xs bg-cgr-purple/20 text-cgr-accent px-3 py-1 rounded-full border border-cgr-purple/30">
            Más popular
          </div>
        </div>
      </div>

      <!-- Botones -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <a
          href="#"
          class="w-full sm:w-auto bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-10 py-3.5 rounded-xl hover:opacity-90 transition-opacity"
        >
          Registrarme ahora
        </a>
        <a
          href="#programa"
          class="w-full sm:w-auto border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple font-semibold px-10 py-3.5 rounded-xl transition-colors"
        >
          Ver programa completo
        </a>
      </div>
    </div>
  </section>
</template>
