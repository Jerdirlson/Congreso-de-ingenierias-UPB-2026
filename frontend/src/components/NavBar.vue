<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import LogoUpb from './LogoUpb.vue'
import ThemeControls from './ThemeControls.vue'
import { registrationOpen } from '../composables/useRegistration'

const menuOpen = ref(false)

const links: { label: string; href?: string; to?: string }[] = [
  { label: 'Inicio',               to: '/#inicio' },
  { label: 'Acerca de',            to: '/#acerca' },
  { label: 'Ejes Temáticos',       to: '/#ejes' },
  { label: 'En vivo',              to: '/#envivo' },
  { label: 'Actividades',          to: '/#actividades' },
  { label: 'Fechas',               to: '/#fechas' },
  { label: 'Precios',              to: '/#precios' },
  { label: 'Inscripción',          to: '/#inscripcion' },
  { label: 'Llamado a Ponencias',  to: '/ponencias' },
  { label: 'Sitios de Interés',    to: '/sitios-de-interes' },
]
</script>

<template>
  <!-- Overlay -->
  <div
    v-if="menuOpen"
    class="fixed inset-0 z-40 bg-black/50"
    @click="menuOpen = false"
  />

  <header class="fixed top-0 left-0 right-0 z-50 bg-cgr-bg border-b border-cgr-border">
    <div class="max-w-7xl mx-auto px-5 lg:px-20 h-16 flex items-center justify-between gap-4">

      <!-- Hamburger — siempre visible -->
      <button
        class="p-1.5 rounded-lg border border-cgr-border text-cgr-muted hover:text-cgr-purple hover:border-cgr-purple/50 transition-colors shrink-0"
        @click="menuOpen = !menuOpen"
        :aria-label="menuOpen ? 'Cerrar menú' : 'Abrir menú'"
        :aria-expanded="menuOpen"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Logo -->
      <RouterLink to="/" class="flex items-center gap-3 shrink-0">
        <LogoUpb class="h-7 w-auto" />
        <div class="hidden sm:block border-l border-cgr-border pl-4">
          <p class="text-white font-semibold text-xs leading-tight">Congreso Internacional</p>
          <p class="text-cgr-purple text-xs font-normal">de Ingeniería · 2026</p>
        </div>
      </RouterLink>

      <div class="flex-1" />

      <!-- Acciones -->
      <div class="flex items-center gap-3">
        <RouterLink
          to="/ponencias"
          class="hidden sm:inline-flex items-center gap-1.5 border border-cgr-purple/50 text-cgr-purple hover:bg-cgr-purple/10 text-xs font-semibold px-4 py-2 rounded-lg transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
          </svg>
          Llamado a Ponencias
        </RouterLink>
        <div class="flex flex-col items-center gap-0.5">
          <RouterLink
            v-if="registrationOpen"
            to="/login"
            class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white text-sm font-semibold px-5 py-2 rounded-lg hover:opacity-90 transition-opacity"
          >
            Inscríbete
          </RouterLink>
          <button
            v-else
            disabled
            class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white text-sm font-semibold px-5 py-2 rounded-lg opacity-50 cursor-not-allowed pointer-events-none"
          >
            Inscríbete
          </button>
          <span v-if="!registrationOpen" class="text-cgr-muted text-[10px] leading-tight text-center">Pronto serán activadas las inscripciones</span>
        </div>
      </div>
    </div>
  </header>

  <!-- Sidebar drawer -->
  <aside
    :class="[
      'fixed top-0 left-0 h-full w-72 z-50 bg-cgr-section border-r border-cgr-border flex flex-col transition-transform duration-300 ease-in-out',
      menuOpen ? 'translate-x-0' : '-translate-x-full',
    ]"
  >
    <!-- Header del drawer -->
    <div class="h-16 shrink-0 flex items-center justify-between border-b border-cgr-border px-5">
      <p class="text-white font-semibold text-sm">Navegación</p>
      <button
        @click="menuOpen = false"
        class="p-1.5 rounded-lg text-cgr-muted hover:text-white hover:bg-cgr-card transition-colors"
        aria-label="Cerrar menú"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Links de navegación -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <RouterLink
        v-for="link in links"
        :key="link.to"
        :to="link.to!"
        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-cgr-muted hover:text-white hover:bg-cgr-card transition-colors"
        @click="menuOpen = false"
      >
        {{ link.label }}
      </RouterLink>
    </nav>

    <!-- Footer del drawer -->
    <div class="border-t border-cgr-border px-5 py-4 space-y-4">
      <ThemeControls />
      <div class="flex flex-col items-center gap-1">
        <RouterLink
          v-if="registrationOpen"
          to="/login"
          class="block w-full text-center bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:opacity-90 transition-opacity"
          @click="menuOpen = false"
        >
          Inscríbete al congreso
        </RouterLink>
        <button
          v-else
          disabled
          class="block w-full text-center bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white text-sm font-semibold px-5 py-2.5 rounded-lg opacity-50 cursor-not-allowed pointer-events-none"
        >
          Inscríbete al congreso
        </button>
        <span v-if="!registrationOpen" class="text-cgr-muted text-[10px] leading-tight text-center">Pronto serán activadas las inscripciones</span>
      </div>
    </div>
  </aside>
</template>
