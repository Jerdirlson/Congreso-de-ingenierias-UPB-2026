<script setup lang="ts">
import { computed } from 'vue'
import type { AuthUser, UserRole } from '../../stores/auth'

const props = defineProps<{ user: AuthUser | null; role: UserRole | null }>()

const roleLabel = computed(() => {
  const labels: Record<string, string> = {
    admin: 'Administrador',
    administrativo: 'Administrativo',
    revisor: 'Revisor',
    ponente: 'Ponente',
    participante: 'Participante',
  }
  return labels[props.role ?? ''] ?? 'Sin rol'
})

const roleBadgeClass = computed(() => {
  const classes: Record<string, string> = {
    admin: 'bg-red-500/10 text-red-400 border-red-500/30',
    administrativo: 'bg-purple-500/10 text-purple-400 border-purple-500/30',
    revisor: 'bg-blue-500/10 text-blue-400 border-blue-500/30',
    ponente: 'bg-cgr-purple/10 text-cgr-purple border-cgr-purple/30',
    participante: 'bg-green-500/10 text-green-400 border-green-500/30',
  }
  return classes[props.role ?? ''] ?? 'bg-cgr-card text-cgr-muted border-cgr-border'
})
</script>

<template>
  <header class="h-16 shrink-0 border-b border-cgr-border bg-cgr-bg/90 backdrop-blur-md px-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
      <h1 class="text-sm font-semibold text-white">Panel de gestión</h1>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-right hidden sm:block">
        <p class="text-xs text-white font-medium">{{ user?.name }}</p>
        <p class="text-xs text-cgr-muted">{{ user?.email }}</p>
      </div>
      <span
        class="text-xs px-2.5 py-1 rounded-full font-medium border"
        :class="roleBadgeClass"
      >
        {{ roleLabel }}
      </span>
      <RouterLink
        to="/"
        class="text-xs px-3 py-1.5 rounded-lg border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple transition-colors"
      >
        Ir a inicio
      </RouterLink>
      <button
        type="button"
        @click="$emit('logout')"
        class="text-xs px-3 py-1.5 rounded-lg bg-cgr-card border border-cgr-border text-cgr-muted hover:text-white hover:border-red-500/50 transition-colors"
      >
        Salir
      </button>
    </div>
  </header>
</template>
