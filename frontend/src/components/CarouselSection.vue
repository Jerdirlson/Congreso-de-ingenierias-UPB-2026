<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const slides = [
  { src: '/carousel/call-for-papers.jpg', alt: 'Call for Papers — Congreso de Ingeniería II' },
  { src: '/carousel/congreso-ii.jpg',     alt: 'Congreso de Ingeniería II' },
  { src: '/carousel/congreso-1.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { src: '/carousel/congreso-2.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { src: '/carousel/congreso-3.webp',     alt: 'Congreso de Ingenierías 2026' },
  { src: '/carousel/congreso-4.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { src: '/carousel/congreso-5.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { src: '/carousel/congreso-6.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { src: '/carousel/congreso-7.jpg',      alt: 'Congreso de Ingenierías 2026' },
  { src: '/carousel/congreso-8.webp',     alt: 'Congreso de Ingenierías 2026' },
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
  autoplayTimer = setInterval(() => goTo(current.value + 1), 5000)
}

function stopAutoplay() {
  if (autoplayTimer) clearInterval(autoplayTimer)
}

onMounted(startAutoplay)
onUnmounted(stopAutoplay)
</script>

<template>
  <section class="bg-cgr-bg py-20 px-5 lg:px-20">
    <div class="max-w-5xl mx-auto">

      <!-- Header -->
      <div class="text-center mb-10">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Galería</span>
        <h2 class="mt-3 text-3xl font-black text-white">El Congreso en imágenes</h2>
        <p class="mt-3 text-cgr-muted text-sm max-w-xl mx-auto">
          Conoce la sede, la ciudad y los momentos más destacados del evento.
        </p>
      </div>

      <!-- Carousel -->
      <div
        class="relative rounded-2xl overflow-hidden border border-cgr-border bg-cgr-card"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
      >
        <!-- Slides -->
        <div class="relative h-[420px] sm:h-[520px]">
          <transition-group name="fade">
            <img
              v-for="(slide, i) in slides"
              v-show="i === current"
              :key="slide.src"
              :src="slide.src"
              :alt="slide.alt"
              class="absolute inset-0 w-full h-full object-cover"
              loading="lazy"
            />
          </transition-group>

          <!-- Gradient overlay bottom -->
          <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/60 to-transparent pointer-events-none" />

          <!-- Caption -->
          <p class="absolute bottom-4 left-5 right-16 text-white text-sm font-medium drop-shadow">
            {{ slides[current]?.alt }}
          </p>

          <!-- Flechas -->
          <button
            class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/50 hover:bg-cgr-purple/80 border border-white/10 flex items-center justify-center transition-colors"
            @click="prev"
            aria-label="Anterior"
          >
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
          </button>
          <button
            class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/50 hover:bg-cgr-purple/80 border border-white/10 flex items-center justify-center transition-colors"
            @click="next"
            aria-label="Siguiente"
          >
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>

        <!-- Indicadores -->
        <div class="flex items-center justify-center gap-1.5 py-4 bg-cgr-card">
          <button
            v-for="(_, i) in slides"
            :key="i"
            class="h-1.5 rounded-full transition-all duration-300"
            :class="i === current ? 'w-6 bg-cgr-purple' : 'w-1.5 bg-cgr-border hover:bg-cgr-muted'"
            @click="goTo(i)"
            :aria-label="`Ir a imagen ${i + 1}`"
          />
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
