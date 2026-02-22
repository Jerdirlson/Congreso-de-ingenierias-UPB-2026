<script setup lang="ts">
import { ref } from 'vue'

const days = [
  { label: 'Lun 13', key: 'lun' },
  { label: 'Mar 14', key: 'mar' },
  { label: 'Mié 15', key: 'mie' },
  { label: 'Jue 16', key: 'jue' },
  { label: 'Vie 17', key: 'vie' },
]

const activeDay = ref('lun')

type BadgeType = 'Plenaria' | 'Keynote' | 'Taller' | 'Panel'

interface AgendaItem {
  time: string
  title: string
  location: string
  type: BadgeType
}

const agenda: Record<string, AgendaItem[]> = {
  lun: [
    { time: '08:00', title: 'Ceremonia de apertura y bienvenida institucional', location: 'Auditorio Principal', type: 'Plenaria' },
    { time: '10:00', title: 'IA generativa y su impacto en la ingeniería moderna', location: 'Sala A — Edificio F', type: 'Keynote' },
    { time: '14:00', title: 'Taller: Introducción a LLMs para ingenieros', location: 'Lab de Cómputo 3', type: 'Taller' },
    { time: '16:30', title: 'Panel: Ética y responsabilidad en la IA industrial', location: 'Sala de Conferencias B', type: 'Panel' },
  ],
  mar: [
    { time: '09:00', title: 'Transición energética en América Latina: retos y oportunidades', location: 'Auditorio Principal', type: 'Keynote' },
    { time: '11:00', title: 'Hidrógeno verde como vector energético del futuro', location: 'Sala A — Edificio F', type: 'Plenaria' },
    { time: '14:00', title: 'Taller: Diseño de microrredes solares con MATLAB', location: 'Lab de Ingeniería Eléctrica', type: 'Taller' },
    { time: '16:00', title: 'Panel: Política energética y rol de la academia', location: 'Sala de Conferencias B', type: 'Panel' },
  ],
  mie: [
    { time: '09:00', title: 'Robótica colaborativa en manufactura 4.0', location: 'Auditorio Principal', type: 'Keynote' },
    { time: '11:00', title: 'Gemelos digitales: del concepto a la implementación', location: 'Sala A — Edificio F', type: 'Plenaria' },
    { time: '14:00', title: 'Taller: Programación de brazos robóticos con ROS2', location: 'Lab de Robótica', type: 'Taller' },
    { time: '16:30', title: 'Panel: El futuro del trabajo en la era de la automatización', location: 'Sala de Conferencias B', type: 'Panel' },
  ],
  jue: [
    { time: '09:00', title: 'Infraestructura sostenible y cambio climático', location: 'Auditorio Principal', type: 'Keynote' },
    { time: '11:00', title: 'BIM y digitalización de proyectos de construcción', location: 'Sala A — Edificio F', type: 'Plenaria' },
    { time: '14:00', title: 'Taller: Modelado estructural con Revit y análisis FEM', location: 'Lab de CAD', type: 'Taller' },
    { time: '16:00', title: 'Panel: Ingeniería para la resiliencia urbana', location: 'Sala de Conferencias B', type: 'Panel' },
  ],
  vie: [
    { time: '09:00', title: 'Ciberseguridad en infraestructuras críticas', location: 'Auditorio Principal', type: 'Keynote' },
    { time: '11:00', title: 'Bioingeniería: dispositivos médicos del futuro', location: 'Sala A — Edificio F', type: 'Plenaria' },
    { time: '14:00', title: 'Taller: Hacking ético y pentesting para ingenieros', location: 'Lab de Cómputo 2', type: 'Taller' },
    { time: '16:00', title: 'Ceremonia de clausura y premiación', location: 'Auditorio Principal', type: 'Plenaria' },
  ],
}

const badgeColors: Record<BadgeType, string> = {
  Plenaria: 'bg-cgr-purple/15 text-cgr-purple border border-cgr-purple/30',
  Keynote:  'bg-blue-500/15 text-blue-400 border border-blue-500/30',
  Taller:   'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30',
  Panel:    'bg-amber-500/15 text-amber-400 border border-amber-500/30',
}
</script>

<template>
  <section id="programa" class="bg-cgr-bg py-24 px-5 lg:px-20">
    <div class="max-w-7xl mx-auto">

      <!-- Encabezado -->
      <div class="text-center mb-12">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Programa académico</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-black text-white">
          5 días de conocimiento
        </h2>
        <p class="mt-4 text-cgr-muted max-w-xl mx-auto text-base leading-relaxed">
          Agenda completa del 13 al 17 de octubre 2025.
        </p>
      </div>

      <!-- Tabs de días -->
      <div class="flex gap-2 overflow-x-auto pb-2 mb-8 scrollbar-hide">
        <button
          v-for="day in days"
          :key="day.key"
          @click="activeDay = day.key"
          class="shrink-0 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all"
          :class="activeDay === day.key
            ? 'bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white shadow-lg shadow-cgr-purple/20'
            : 'border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple'"
        >
          {{ day.label }}
        </button>
      </div>

      <!-- Agenda del día activo -->
      <div class="flex flex-col gap-4">
        <div
          v-for="(item, i) in agenda[activeDay]"
          :key="i"
          class="bg-cgr-card border border-cgr-border rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center gap-4"
        >
          <!-- Hora -->
          <div class="shrink-0 w-16 text-cgr-purple font-bold text-lg">
            {{ item.time }}
          </div>

          <!-- Divider vertical (desktop) -->
          <div class="hidden sm:block w-px h-12 bg-gradient-to-b from-cgr-purple-dark to-cgr-purple opacity-40 shrink-0" />

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <h3 class="text-white font-semibold text-base mb-1 leading-snug">{{ item.title }}</h3>
            <div class="flex items-center gap-2 text-cgr-subtle text-xs">
              <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
              </svg>
              {{ item.location }}
            </div>
          </div>

          <!-- Badge de tipo -->
          <span class="shrink-0 text-xs font-semibold px-3 py-1 rounded-full" :class="badgeColors[item.type]">
            {{ item.type }}
          </span>
        </div>
      </div>
    </div>
  </section>
</template>
