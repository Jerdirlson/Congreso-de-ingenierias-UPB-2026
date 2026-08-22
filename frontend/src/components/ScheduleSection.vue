<script setup lang="ts">
import { ref, computed } from 'vue'

interface AgendaDay {
  key: string
  label: string
  weekday: string
  date: string
  theme: string
  image: string
}

const days: AgendaDay[] = [
  {
    key: 'mie',
    label: 'Mié 14',
    weekday: 'Miércoles',
    date: '14 de octubre',
    theme: 'Transformación digital y tecnología humanocéntrica',
    image: '/agenda/miercoles-14.png',
  },
  {
    key: 'jue',
    label: 'Jue 15',
    weekday: 'Jueves',
    date: '15 de octubre',
    theme: 'Tecnologías emergentes y sociedad',
    image: '/agenda/jueves-15.png',
  },
  {
    key: 'vie',
    label: 'Vie 16',
    weekday: 'Viernes',
    date: '16 de octubre',
    theme: 'Sostenibilidad, inteligencia avanzada y redes del futuro',
    image: '/agenda/viernes-16.png',
  },
  {
    key: 'sab',
    label: 'Sáb 17',
    weekday: 'Sábado',
    date: '17 de octubre',
    theme: 'Integración y salud',
    image: '/agenda/sabado-17.png',
  },
]

const activeDay = ref('mie')
const current = computed(() => days.find(d => d.key === activeDay.value) ?? days[0])

// Lightbox
const zoomed = ref(false)
function openZoom() { zoomed.value = true }
function closeZoom() { zoomed.value = false }
</script>

<template>
  <section id="agenda" class="bg-cgr-bg py-24 px-5 lg:px-20">
    <div class="max-w-6xl mx-auto">

      <!-- Encabezado -->
      <div class="text-center mb-12">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Programa académico</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-black text-white">
          Agenda del congreso
        </h2>
        <p class="mt-4 text-cgr-muted max-w-xl mx-auto text-base leading-relaxed">
          Programación completa del 14 al 17 de octubre 2026. Selecciona un día para ver su cartelera.
        </p>
      </div>

      <!-- Tabs de días -->
      <div class="flex flex-wrap justify-center gap-2 sm:gap-3 mb-10">
        <button
          v-for="day in days"
          :key="day.key"
          @click="activeDay = day.key"
          class="shrink-0 px-4 sm:px-5 py-2.5 rounded-xl text-sm font-semibold transition-all"
          :class="activeDay === day.key
            ? 'bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white shadow-lg shadow-cgr-purple/20'
            : 'border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple'"
        >
          {{ day.label }}
        </button>
      </div>

      <!-- Card del día activo -->
      <div class="bg-cgr-card border border-cgr-border rounded-3xl overflow-hidden">
        <!-- Cabecera del día -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-6 sm:p-8 border-b border-cgr-border">
          <div class="min-w-0">
            <div class="flex items-baseline gap-2">
              <span class="text-white font-black text-2xl sm:text-3xl">{{ current.weekday }}</span>
              <span class="text-cgr-purple font-bold text-lg">{{ current.date }}</span>
            </div>
            <p class="mt-1 text-cgr-muted text-sm leading-snug">{{ current.theme }}</p>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <button
              @click="openZoom"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple transition-all"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 8v6M8 11h6M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0z"/>
              </svg>
              Ampliar
            </button>
            <a
              :href="current.image"
              :download="`agenda-${current.key}.png`"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white shadow-lg shadow-cgr-purple/20 transition-all hover:opacity-90"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5 5-5M12 15V3"/>
              </svg>
              Descargar
            </a>
          </div>
        </div>

        <!-- Póster del día -->
        <div class="p-4 sm:p-6 bg-cgr-bg/40 flex justify-center">
          <button
            @click="openZoom"
            class="group relative block w-full max-w-2xl rounded-2xl overflow-hidden cursor-zoom-in"
            aria-label="Ampliar agenda"
          >
            <img
              :src="current.image"
              :alt="`Agenda ${current.weekday} ${current.date} — ${current.theme}`"
              class="w-full h-auto block transition-transform duration-300 group-hover:scale-[1.01]"
              loading="lazy"
            />
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors" />
          </button>
        </div>
      </div>
    </div>

    <!-- Lightbox -->
    <Transition name="fade">
      <div
        v-if="zoomed"
        @click="closeZoom"
        class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 sm:p-8 cursor-zoom-out"
      >
        <button
          @click.stop="closeZoom"
          class="absolute top-4 right-4 sm:top-6 sm:right-6 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center transition-colors"
          aria-label="Cerrar"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <img
          :src="current.image"
          :alt="`Agenda ${current.weekday} ${current.date}`"
          class="max-w-full max-h-full w-auto h-auto object-contain rounded-lg shadow-2xl"
          @click.stop
        />
      </div>
    </Transition>
  </section>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
