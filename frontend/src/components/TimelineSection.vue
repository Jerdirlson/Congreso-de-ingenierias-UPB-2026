<script setup lang="ts">
import { useScrollReveal } from '../composables/useScrollReveal'

const { setRef } = useScrollReveal()

const milestones = [
  { date: '16 de marzo', label: 'Apertura de recepción de resúmenes', status: 'active' },
  { date: '22 de mayo', label: 'Cierre de recepción de resúmenes', status: 'upcoming' },
  { date: '26 de junio', label: 'Notificación de resúmenes aceptados (ISSN)', status: 'upcoming' },
  { date: '1 — 30 de julio', label: 'Envío de artículos completos y versiones finales', status: 'upcoming' },
  { date: '1 — 30 de agosto', label: 'Contribuciones de estudiantes UPB y colegios', status: 'upcoming' },
  { date: '28 de septiembre', label: 'Cierre de videoponencias y notificación editorial', status: 'upcoming' },
  { date: '13 — 17 de octubre', label: 'Celebración del Congreso', status: 'upcoming' },
]
</script>

<template>
  <section id="fechas" class="bg-cgr-bg py-24 px-5 lg:px-20">
    <div class="max-w-4xl mx-auto">

      <div class="text-center mb-16">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Fechas importantes</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-black text-white">
          Cronograma 2026
        </h2>
        <p class="mt-4 text-cgr-muted max-w-xl mx-auto text-base leading-relaxed">
          Fechas clave para la postulación de trabajos y participación en el congreso.
        </p>
      </div>

      <div class="relative">
        <!-- Línea vertical -->
        <div class="absolute left-6 top-0 bottom-0 w-px bg-gradient-to-b from-cgr-purple-dark via-cgr-purple to-cgr-purple-dark/20" />

        <div class="flex flex-col gap-8">
          <div
            v-for="(milestone, i) in milestones"
            :key="i"
            class="relative flex gap-6 items-start"
          >
            <!-- Dot -->
            <div class="relative z-10 shrink-0 w-12 flex justify-center">
              <div
                class="w-4 h-4 rounded-full border-2 mt-1"
                :class="milestone.status === 'active'
                  ? 'bg-cgr-purple border-cgr-purple shadow-lg shadow-cgr-purple/40'
                  : 'bg-cgr-card border-cgr-border'"
              />
            </div>

            <!-- Content -->
            <div
              :ref="(el) => setRef(el as Element, i)"
              class="animate-reveal bg-cgr-card border border-cgr-border rounded-xl p-5 flex-1 hover:border-cgr-purple-dark hover:scale-[1.01] hover:shadow-md hover:shadow-cgr-purple/5 transition-all duration-300"
            >
              <span class="text-cgr-purple text-xs font-bold uppercase tracking-wider">{{ milestone.date }}</span>
              <p class="text-white text-sm font-semibold mt-1">{{ milestone.label }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
