<script setup lang="ts">
import { ref, computed } from 'vue'
import NavBar from '../../components/NavBar.vue'
import FooterSection from '../../components/FooterSection.vue'

/**
 * Agenda pública — cartelera por jornada.
 *
 * Se publican los pósters oficiales de cada día (del miércoles 14 al sábado 17
 * de octubre) con la programación completa. El **martes 13 no se publica**
 * (confirmado por el comité: "solo hay que publicar a partir del miércoles").
 * Es la jornada "Futuros Ingenieros", dirigida a semilleros y colegios.
 *
 * Fuente: pósters oficiales del comité de Comunicaciones.
 */

interface Jornada {
  key: string
  fecha: string
  dia: string
  titulo: string
  imagen: string
}

const jornadas: Jornada[] = [
  {
    key: 'mie',
    fecha: '14',
    dia: 'Miércoles',
    titulo: 'Transformación Digital y Tecnología Humanocéntrica',
    imagen: '/agenda/miercoles-14.png',
  },
  {
    key: 'jue',
    fecha: '15',
    dia: 'Jueves',
    titulo: 'Tecnologías Emergentes y Sociedad',
    imagen: '/agenda/jueves-15.png',
  },
  {
    key: 'vie',
    fecha: '16',
    dia: 'Viernes',
    titulo: 'Sostenibilidad, Inteligencia Avanzada y Redes del Futuro',
    imagen: '/agenda/viernes-16.png',
  },
  {
    key: 'sab',
    fecha: '17',
    dia: 'Sábado',
    titulo: 'Integración y Salud',
    imagen: '/agenda/sabado-17.png',
  },
]

const activeDay = ref('mie')
const current = computed(() => jornadas.find(j => j.key === activeDay.value) ?? jornadas[0])

// Lightbox
const zoomed = ref(false)
function openZoom() { zoomed.value = true }
function closeZoom() { zoomed.value = false }
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
        <div class="max-w-5xl mx-auto">

          <div class="text-center mb-10">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Programación por día</span>
            <h2 class="mt-3 text-3xl font-black text-white">Cuatro días, cuatro enfoques</h2>
          </div>

          <!-- Aviso: la agenda todavía es preliminar -->
          <div class="mb-10 flex items-start gap-3 bg-cgr-card border border-cgr-purple/30 rounded-2xl px-5 py-4 max-w-3xl mx-auto">
            <svg class="w-5 h-5 text-cgr-purple shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <p class="text-sm text-cgr-muted leading-relaxed">
              Esta agenda es <strong class="text-white font-semibold">preliminar</strong> y puede cambiar.
              Selecciona un día para ver su cartelera completa.
            </p>
          </div>

          <!-- Tabs de días -->
          <div class="flex flex-wrap justify-center gap-2 sm:gap-3 mb-10">
            <button
              v-for="j in jornadas"
              :key="j.key"
              @click="activeDay = j.key"
              class="shrink-0 px-4 sm:px-5 py-2.5 rounded-xl text-sm font-semibold transition-all"
              :class="activeDay === j.key
                ? 'bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white shadow-lg shadow-cgr-purple/20'
                : 'border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple'"
            >
              {{ j.dia }} {{ j.fecha }}
            </button>
          </div>

          <!-- Card del día activo -->
          <div class="bg-cgr-card border border-cgr-border rounded-3xl overflow-hidden">
            <!-- Cabecera del día -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-6 sm:p-8 border-b border-cgr-border">
              <div class="min-w-0">
                <div class="flex items-baseline gap-2">
                  <span class="text-white font-black text-2xl sm:text-3xl">{{ current.dia }}</span>
                  <span class="text-cgr-purple font-bold text-lg">{{ current.fecha }} de octubre</span>
                </div>
                <p class="mt-1 text-cgr-muted text-sm leading-snug">{{ current.titulo }}</p>
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
                  :href="current.imagen"
                  :download="`agenda-${current.dia.toLowerCase()}-${current.fecha}.png`"
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
                  :src="current.imagen"
                  :alt="`Agenda ${current.dia} ${current.fecha} de octubre — ${current.titulo}`"
                  class="w-full h-auto block transition-transform duration-300 group-hover:scale-[1.01]"
                  loading="lazy"
                />
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors" />
              </button>
            </div>
          </div>
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
          :src="current.imagen"
          :alt="`Agenda ${current.dia} ${current.fecha} de octubre`"
          class="max-w-full max-h-full w-auto h-auto object-contain rounded-lg shadow-2xl"
          @click.stop
        />
      </div>
    </Transition>
  </div>
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
