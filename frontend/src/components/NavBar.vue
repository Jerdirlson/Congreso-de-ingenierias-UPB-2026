<script setup lang="ts">
import { ref } from 'vue'
import LogoUpb from './LogoUpb.vue'

const menuOpen = ref(false)

const links = [
  { label: 'Inicio',       href: '#inicio' },
  { label: 'Acerca de',    href: '#acerca' },
  { label: 'Ejes Temáticos', href: '#ejes' },
  { label: 'En vivo',      href: '#envivo' },
  { label: 'Actividades',  href: '#actividades' },
  { label: 'Fechas',       href: '#fechas' },
  { label: 'Inscripción',  href: '#inscripcion' },
]
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50 bg-cgr-bg/90 backdrop-blur-md border-b border-cgr-border">
    <div class="max-w-7xl mx-auto px-5 lg:px-20 h-16 flex items-center justify-between">

      <a href="#inicio" class="flex items-center gap-4 shrink-0">
        <LogoUpb class="h-7 w-auto" />
        <div class="hidden sm:block border-l border-cgr-border pl-4">
          <p class="text-white font-semibold text-xs leading-tight">Congreso Internacional</p>
          <p class="text-cgr-purple text-xs font-normal">de Ingeniería · 2026</p>
        </div>
      </a>

      <nav class="hidden lg:flex items-center gap-8">
        <a
          v-for="link in links"
          :key="link.href"
          :href="link.href"
          class="text-cgr-muted hover:text-white text-sm font-medium transition-colors"
        >
          {{ link.label }}
        </a>
      </nav>

      <div class="flex items-center gap-3">
        <a
          href="#inscripcion"
          class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white text-sm font-semibold px-5 py-2 rounded-lg hover:opacity-90 transition-opacity"
        >
          Inscríbete
        </a>

        <button
          class="lg:hidden text-cgr-muted hover:text-white p-1"
          @click="menuOpen = !menuOpen"
          aria-label="Menú"
        >
          <svg v-if="!menuOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <div v-if="menuOpen" class="lg:hidden border-t border-cgr-border bg-cgr-bg px-5 py-4 flex flex-col gap-4">
      <a
        v-for="link in links"
        :key="link.href"
        :href="link.href"
        class="text-cgr-muted hover:text-white text-sm font-medium transition-colors"
        @click="menuOpen = false"
      >
        {{ link.label }}
      </a>
    </div>
  </header>
</template>
