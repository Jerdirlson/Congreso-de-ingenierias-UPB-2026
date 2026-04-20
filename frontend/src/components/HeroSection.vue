<script setup lang="ts">
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
import { RouterLink } from 'vue-router'
import { registrationOpen } from '../composables/useRegistration'

type Slide = { type: 'video'; src: string; alt: string } | { type: 'image'; src: string; alt: string }

const slides: Slide[] = [
  { type: 'video', src: '/carousel/bucaramanga-1.mp4',   alt: 'Bucaramanga, Santander' },
  { type: 'video', src: '/carousel/bucaramanga-2.mp4',   alt: 'Bucaramanga, Santander' },
  { type: 'image', src: '/carousel/call-for-papers.jpg',  alt: 'Call for Papers — Congreso II' },
  { type: 'image', src: '/carousel/congreso-ii.jpg',      alt: 'Congreso de Ingeniería II' },
  { type: 'image', src: '/carousel/tarifas-congreso.jpg', alt: 'Tarifas — Congreso de Ingeniería II' },
  { type: 'image', src: '/carousel/congreso-1.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { type: 'image', src: '/carousel/congreso-2.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { type: 'image', src: '/carousel/congreso-3.webp',     alt: 'Congreso de Ingenierías 2026' },
  { type: 'image', src: '/carousel/congreso-4.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { type: 'image', src: '/carousel/congreso-5.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { type: 'image', src: '/carousel/congreso-6.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { type: 'image', src: '/carousel/congreso-7.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { type: 'image', src: '/carousel/congreso-8.webp',     alt: 'Congreso de Ingenierías 2026' },
]

const current = ref(0)
const isTransitioning = ref(false)
const videoRefs: HTMLVideoElement[] = []
let imageTimer: ReturnType<typeof setTimeout> | null = null

function setVideoRef(el: unknown, i: number) {
  if (el instanceof HTMLVideoElement) videoRefs[i] = el
}

function clearImageTimer() {
  if (imageTimer !== null) { clearTimeout(imageTimer); imageTimer = null }
}

function goTo(index: number) {
  if (isTransitioning.value) return
  clearImageTimer()

  const prev = slides[current.value]
  if (prev?.type === 'video') {
    const vid = videoRefs[current.value]
    if (vid) { vid.pause(); vid.currentTime = 0 }
  }

  isTransitioning.value = true
  current.value = (index + slides.length) % slides.length

  setTimeout(() => {
    isTransitioning.value = false
    activateSlide()
  }, 400)
}

function activateSlide() {
  const slide = slides[current.value]
  if (!slide) return
  if (slide.type === 'video') {
    nextTick(() => {
      const vid = videoRefs[current.value]
      if (vid) { vid.currentTime = 0; vid.play().catch(() => {}) }
    })
  } else {
    imageTimer = setTimeout(() => goTo(current.value + 1), 5000)
  }
}

function onVideoEnded() { goTo(current.value + 1) }

onMounted(() => activateSlide())
onUnmounted(() => clearImageTimer())
</script>

<template>
  <section id="inicio" class="relative flex flex-col lg:flex-row pt-16 min-h-[600px] lg:min-h-[700px]">

    <!-- ── IZQUIERDA: Carousel de medios ──────────────────────────── -->
    <div id="galeria" class="relative w-full lg:w-[58%] bg-black overflow-hidden min-h-[300px] sm:min-h-[440px] lg:min-h-0">

      <!-- Slides -->
      <transition-group name="fade">
        <div
          v-for="(slide, i) in slides"
          v-show="i === current"
          :key="slide.src"
          class="absolute inset-0"
        >
          <!-- Video -->
          <video
            v-if="slide.type === 'video'"
            :ref="(el) => setVideoRef(el, i)"
            :src="slide.src"
            class="w-full h-full object-cover"
            muted
            playsinline
            preload="none"
            @ended="onVideoEnded"
          />
          <!-- Imagen -->
          <template v-else>
            <img
              :src="slide.src"
              aria-hidden="true"
              class="absolute inset-0 w-full h-full object-cover scale-110 blur-2xl brightness-40"
            />
            <img
              :src="slide.src"
              :alt="slide.alt"
              class="relative w-full h-full object-contain z-10"
              loading="lazy"
            />
          </template>
        </div>
      </transition-group>

      <!-- Gradiente inferior -->
      <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/80 to-transparent pointer-events-none z-20" />

      <!-- Caption -->
      <p class="absolute bottom-10 inset-x-0 text-center text-white/60 text-xs z-30 px-8">
        {{ slides[current]?.alt }}
      </p>

      <!-- Flecha izquierda -->
      <button
        class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/40 hover:bg-cgr-purple/70 border border-white/10 flex items-center justify-center transition-colors z-30 backdrop-blur-sm"
        @click="goTo(current - 1)"
        aria-label="Anterior"
      >
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
      </button>

      <!-- Flecha derecha -->
      <button
        class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/40 hover:bg-cgr-purple/70 border border-white/10 flex items-center justify-center transition-colors z-30 backdrop-blur-sm"
        @click="goTo(current + 1)"
        aria-label="Siguiente"
      >
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
      </button>

      <!-- Indicadores -->
      <div class="absolute bottom-3 inset-x-0 flex items-center justify-center gap-1.5 z-30">
        <button
          v-for="(_, i) in slides"
          :key="i"
          class="h-1 rounded-full transition-all duration-300"
          :class="i === current ? 'w-6 bg-cgr-purple' : 'w-1.5 bg-white/30 hover:bg-white/60'"
          @click="goTo(i)"
          :aria-label="`Ir a slide ${i + 1}`"
        />
      </div>
    </div>

    <!-- ── DERECHA: Contenido estático ───────────────────────────── -->
    <div class="hero-gradient relative flex-1 flex items-center justify-center px-8 py-12 lg:py-0 overflow-hidden">

      <!-- Blobs de fondo -->
      <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-cgr-purple-deep/30 rounded-full blur-3xl pointer-events-none" />
      <div class="absolute bottom-1/4 right-1/4 w-48 h-48 bg-cgr-purple-dark/20 rounded-full blur-3xl pointer-events-none" />

      <div class="relative z-10 text-center lg:text-left max-w-md">

        <!-- Badge de fecha -->
        <div class="inline-flex items-center gap-2 border border-cgr-purple-dark rounded-full px-4 py-1.5 mb-6">
          <span class="w-2 h-2 rounded-full bg-cgr-purple animate-pulse" />
          <span class="text-cgr-accent text-xs font-semibold tracking-widest uppercase">
            13 — 17 de octubre 2026
          </span>
        </div>

        <!-- Título -->
        <h1 class="text-3xl sm:text-4xl lg:text-[2.6rem] font-black leading-tight mb-4">
          <span class="text-white">II Congreso Internacional</span><br />
          <span class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple bg-clip-text text-transparent">
            de Ingeniería
          </span>
        </h1>

        <p class="text-cgr-accent text-sm font-semibold mb-3 italic">
          "La ingeniería se encuentra con la humanidad"
        </p>

        <p class="text-cgr-muted text-sm max-w-sm mx-auto lg:mx-0 mb-8 leading-relaxed">
          Evolucionando de la eficiencia tecnológica al propósito humano.
          Industria 5.0: la tecnología al servicio del bienestar, la sostenibilidad y la equidad.
        </p>

        <!-- CTAs -->
        <div class="flex flex-col sm:flex-row items-center lg:items-start justify-center lg:justify-start gap-3 mb-8">
          <div class="flex flex-col items-center gap-1 w-full sm:w-auto">
            <RouterLink
              v-if="registrationOpen"
              to="/login"
              class="w-full sm:w-auto bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-7 py-3 rounded-xl hover:opacity-90 transition-opacity text-sm text-center"
            >
              Inscríbete ahora
            </RouterLink>
            <button
              v-else
              disabled
              class="w-full sm:w-auto bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-7 py-3 rounded-xl opacity-50 cursor-not-allowed pointer-events-none text-sm"
            >
              Inscríbete ahora
            </button>
            <span v-if="!registrationOpen" class="text-cgr-muted text-[10px] leading-tight text-center">
              Pronto serán activadas las inscripciones
            </span>
          </div>
          <a
            href="#fechas"
            class="w-full sm:w-auto border border-cgr-border text-white font-semibold px-7 py-3 rounded-xl hover:border-cgr-purple transition-colors text-sm text-center"
          >
            Ver programa
          </a>
        </div>

        <!-- Badges de ubicación -->
        <div class="flex flex-col sm:flex-row items-center lg:items-start justify-center lg:justify-start gap-4 text-cgr-subtle text-sm">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
            </svg>
            <span>UPB Bucaramanga, Colombia</span>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span>Modalidad Híbrida</span>
          </div>
        </div>

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
