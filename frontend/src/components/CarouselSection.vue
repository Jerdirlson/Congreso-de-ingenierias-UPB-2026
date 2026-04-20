<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const slides = [
  { src: '/carousel/bucaramanga-1.mp4',   alt: 'Bucaramanga' },
  { src: '/carousel/bucaramanga-2.mp4',   alt: 'Bucaramanga' },
  { src: '/carousel/call-for-papers.jpg', alt: 'Call for Papers — Congreso de Ingeniería II' },
  { src: '/carousel/congreso-ii.jpg',     alt: 'Congreso de Ingeniería II' },
  { src: '/carousel/congreso-1.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { src: '/carousel/congreso-2.jpg',      alt: 'La 27, Bucaramanga' },
  { src: '/carousel/congreso-3.webp',     alt: 'Parroquia Histórica San Laureano' },
  { src: '/carousel/congreso-4.jpg',      alt: 'Puente La Novena, Bucaramanga' },
  { src: '/carousel/congreso-5.jpg',      alt: 'Basílica menor Señor de los Milagros (Parroquia San Juan Bautista)' },
  { src: '/carousel/congreso-6.jpg',      alt: 'El Santísimo' },
  { src: '/carousel/congreso-7.jpg',      alt: 'Parque Nacional del Chicamocha' },
  { src: '/carousel/congreso-8.webp',     alt: 'Congreso de Ingenierías 2026' },
  { src: '/carousel/tarifas-congreso.jpg', alt: 'Tarifas — Congreso de Ingenierías 2026' },
]

const current = ref(0)
const isTransitioning = ref(false)
let autoplayTimer: ReturnType<typeof setInterval> | null = null

function goTo(index: number) {
  if (isTransitioning.value) return
  isTransitioning.value = true
  current.value = (index + slides.length) % slides.length
  setTimeout(() => { isTransitioning.value = false }, 400)
}

function prev() { goTo(current.value - 1) }
function next() { goTo(current.value + 1) }

function startAutoplay() {
  clearInterval(autoplayTimer ?? undefined)
  autoplayTimer = setInterval(() => {
    if (!isTransitioning.value) goTo(current.value + 1)
  }, 5000)
}

function stopAutoplay() {
  clearInterval(autoplayTimer ?? undefined)
}

onMounted(startAutoplay)
onUnmounted(stopAutoplay)
</script>

<template>
  <section id="galeria" class="relative bg-cgr-bg py-14">

    <!-- Separador con etiqueta centrada -->
    <div class="flex items-center gap-4 px-5 lg:px-20 mb-8 max-w-6xl mx-auto">
      <div class="h-px flex-1 bg-cgr-border" />
      <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase shrink-0">Galería · Congreso 2026</span>
      <div class="h-px flex-1 bg-cgr-border" />
    </div>

    <!-- Carousel full-width -->
    <div
      class="carousel-dark relative overflow-hidden"
      @mouseenter="stopAutoplay"
      @mouseleave="startAutoplay"
    >
      <!-- Slides -->
      <div class="relative h-[300px] sm:h-[420px] lg:h-[500px]">
        <transition-group name="fade">
          <div
            v-for="(slide, i) in slides"
            v-show="i === current"
            :key="slide.src"
            class="absolute inset-0"
          >
            <!-- Fondo desenfocado -->
            <video
              v-if="slide.src.endsWith('.mp4')"
              aria-hidden="true"
              class="absolute inset-0 w-full h-full object-cover scale-110 blur-2xl brightness-40"
              muted loop autoplay playsinline
            >
              <source :src="slide.src" type="video/mp4" />
            </video>
            <img
              v-else
              :src="slide.src"
              :alt="''"
              aria-hidden="true"
              class="absolute inset-0 w-full h-full object-cover scale-110 blur-2xl brightness-40"
            />
            <!-- Contenido principal centrado -->
            <video
              v-if="slide.src.endsWith('.mp4')"
              class="relative w-full h-full object-contain z-10"
              muted loop autoplay playsinline
            >
              <source :src="slide.src" type="video/mp4" />
            </video>
            <img
              v-else
              :src="slide.src"
              :alt="slide.alt"
              class="relative w-full h-full object-contain z-10"
              loading="lazy"
            />
          </div>
        </transition-group>

        <!-- Fade lateral izquierdo -->
        <div class="absolute inset-y-0 left-0 w-20 sm:w-36 lg:w-52 bg-gradient-to-r from-cgr-bg to-transparent pointer-events-none z-20" />
        <!-- Fade lateral derecho -->
        <div class="absolute inset-y-0 right-0 w-20 sm:w-36 lg:w-52 bg-gradient-to-l from-cgr-bg to-transparent pointer-events-none z-20" />

        <!-- Gradient inferior -->
        <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/70 to-transparent pointer-events-none z-20" />

        <!-- Caption -->
        <p class="absolute bottom-4 inset-x-0 text-center text-white/80 text-xs font-medium drop-shadow z-30 px-40">
          {{ slides[current]?.alt }}
        </p>

        <!-- Flecha izquierda -->
        <button
          class="absolute left-4 sm:left-8 lg:left-14 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/40 hover:bg-cgr-purple/70 border border-white/10 flex items-center justify-center transition-colors z-30 backdrop-blur-sm"
          @click="prev"
          aria-label="Anterior"
        >
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
          </svg>
        </button>

        <!-- Flecha derecha -->
        <button
          class="absolute right-4 sm:right-8 lg:right-14 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/40 hover:bg-cgr-purple/70 border border-white/10 flex items-center justify-center transition-colors z-30 backdrop-blur-sm"
          @click="next"
          aria-label="Siguiente"
        >
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </button>
      </div>

      <!-- Indicadores -->
      <div class="flex items-center justify-center gap-1.5 pt-4 pb-2">
        <button
          v-for="(_, i) in slides"
          :key="i"
          class="h-1 rounded-full transition-all duration-300"
          :class="i === current ? 'w-6 bg-cgr-purple' : 'w-1.5 bg-cgr-border hover:bg-cgr-muted'"
          @click="goTo(i)"
          :aria-label="`Ir a imagen ${i + 1}`"
        />
      </div>
    </div>

  </section>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
