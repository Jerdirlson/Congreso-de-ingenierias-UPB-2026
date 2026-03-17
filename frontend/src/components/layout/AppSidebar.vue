<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import LogoUpb from '../LogoUpb.vue'
import type { UserRole } from '../../stores/auth'

const props = defineProps<{ role: UserRole | null; open: boolean }>()
defineEmits<{ close: [] }>()

const route = useRoute()

const links = computed(() => {
  if (props.role === 'ponente') {
    return [
      { to: { name: 'ponente-home' }, label: 'Mis ponencias',   icon: 'list' },
      { to: { name: 'ponente-new' },  label: 'Nueva ponencia',  icon: 'plus' },
    ]
  }
  if (props.role === 'participante') {
    return [
      { to: { name: 'participante-home' }, label: 'Mis inscripciones', icon: 'ticket' },
      { to: { name: 'participante-pago' }, label: 'Realizar pago',     icon: 'card' },
    ]
  }
  if (props.role === 'revisor') {
    return [
      { to: { name: 'revisor-home' }, label: 'Mis revisiones', icon: 'pencil' },
    ]
  }
  if (props.role === 'admin' || props.role === 'administrativo') {
    return [
      { to: { name: 'admin-home' },        label: 'Dashboard',       icon: 'chart' },
      { to: { name: 'admin-submissions' }, label: 'Ponencias',       icon: 'list' },
      { to: { name: 'admin-axes' },        label: 'Ejes temáticos',  icon: 'tag' },
    ]
  }
  return []
})

function isActive(to: { name: string }) {
  return route.name === to.name
}
</script>

<template>
  <!-- Desktop: siempre en el flujo, colapsa a iconos -->
  <!-- Móvil: fixed overlay, se oculta con translateX -->
  <aside
    :class="[
      'z-30 bg-cgr-section border-r border-cgr-border flex flex-col transition-all duration-300 ease-in-out',
      // Desktop
      'hidden lg:flex',
      open ? 'w-64' : 'w-14',
    ]"
  >
    <!-- Logo -->
    <div class="h-16 shrink-0 flex items-center border-b border-cgr-border overflow-hidden"
         :class="open ? 'px-5 gap-3' : 'justify-center px-0'">
      <LogoUpb class="h-7 w-auto shrink-0" />
      <div v-if="open" class="border-l border-cgr-border pl-3 overflow-hidden whitespace-nowrap">
        <p class="text-white font-semibold text-xs leading-tight">Congreso</p>
        <p class="text-cgr-purple text-xs font-normal">Ingeniería 2026</p>
      </div>
    </div>

    <!-- Nav links -->
    <nav class="flex-1 p-2 space-y-1">
      <RouterLink
        v-for="link in links"
        :key="link.to.name"
        :to="link.to"
        :title="!open ? link.label : undefined"
        class="flex items-center gap-3 px-2.5 py-2.5 rounded-lg text-sm font-medium transition-colors overflow-hidden whitespace-nowrap"
        :class="isActive(link.to) ? 'bg-cgr-purple/20 text-cgr-purple' : 'text-cgr-muted hover:text-white hover:bg-cgr-card'"
      >
        <!-- Iconos SVG -->
        <span class="shrink-0 w-5 h-5 flex items-center justify-center">
          <!-- list -->
          <svg v-if="link.icon === 'list'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          <!-- plus -->
          <svg v-else-if="link.icon === 'plus'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          <!-- ticket -->
          <svg v-else-if="link.icon === 'ticket'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
          </svg>
          <!-- card -->
          <svg v-else-if="link.icon === 'card'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
          <!-- pencil -->
          <svg v-else-if="link.icon === 'pencil'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          <!-- chart -->
          <svg v-else-if="link.icon === 'chart'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          <!-- tag -->
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
          </svg>
        </span>
        <span v-if="open" class="truncate">{{ link.label }}</span>
      </RouterLink>
    </nav>
  </aside>

  <!-- Sidebar móvil (overlay) -->
  <aside
    :class="[
      'fixed top-0 left-0 h-full w-64 z-30 bg-cgr-section border-r border-cgr-border flex flex-col lg:hidden transition-transform duration-300 ease-in-out',
      open ? 'translate-x-0' : '-translate-x-full',
    ]"
  >
    <!-- Header con botón cerrar -->
    <div class="h-16 shrink-0 flex items-center justify-between border-b border-cgr-border px-5">
      <RouterLink to="/" class="flex items-center gap-3" @click="$emit('close')">
        <LogoUpb class="h-7 w-auto shrink-0" />
        <div class="border-l border-cgr-border pl-3">
          <p class="text-white font-semibold text-xs leading-tight">Congreso</p>
          <p class="text-cgr-purple text-xs font-normal">Ingeniería 2026</p>
        </div>
      </RouterLink>
      <button
        @click="$emit('close')"
        class="p-1.5 rounded-lg text-cgr-muted hover:text-white hover:bg-cgr-card transition-colors"
        aria-label="Cerrar menú"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <nav class="flex-1 p-2 space-y-1">
      <RouterLink
        v-for="link in links"
        :key="link.to.name"
        :to="link.to"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
        :class="isActive(link.to) ? 'bg-cgr-purple/20 text-cgr-purple' : 'text-cgr-muted hover:text-white hover:bg-cgr-card'"
        @click="$emit('close')"
      >
        {{ link.label }}
      </RouterLink>
    </nav>
  </aside>
</template>
