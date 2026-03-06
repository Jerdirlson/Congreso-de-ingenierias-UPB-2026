<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import LogoUpb from '../LogoUpb.vue'
import type { UserRole } from '../../stores/auth'

const props = defineProps<{ role: UserRole | null }>()
const route = useRoute()

const links = computed(() => {
  if (props.role === 'ponente') {
    return [
      { to: { name: 'ponente-home' }, label: 'Mis ponencias', icon: '📋' },
      { to: { name: 'ponente-new' }, label: 'Nueva ponencia', icon: '➕' },
    ]
  }
  if (props.role === 'participante') {
    return [
      { to: { name: 'participante-home' }, label: 'Mis inscripciones', icon: '🎫' },
      { to: { name: 'participante-pago' }, label: 'Realizar pago', icon: '💳' },
    ]
  }
  if (props.role === 'revisor') {
    return [
      { to: { name: 'revisor-home' }, label: 'Mis revisiones', icon: '📝' },
    ]
  }
  if (props.role === 'admin' || props.role === 'administrativo') {
    return [
      { to: { name: 'admin-home' }, label: 'Dashboard', icon: '📊' },
      { to: { name: 'admin-submissions' }, label: 'Ponencias', icon: '📋' },
      { to: { name: 'admin-axes' }, label: 'Ejes temáticos', icon: '📑' },
    ]
  }
  return []
})

function isActive(to: { name: string }) {
  return route.name === to.name
}
</script>

<template>
  <aside class="w-64 shrink-0 bg-cgr-section border-r border-cgr-border flex flex-col">
    <div class="p-5 border-b border-cgr-border">
      <RouterLink to="/" class="flex items-center gap-3">
        <LogoUpb class="h-7 w-auto" />
        <div class="border-l border-cgr-border pl-3">
          <p class="text-white font-semibold text-xs leading-tight">Congreso</p>
          <p class="text-cgr-purple text-xs font-normal">Ingeniería 2026</p>
        </div>
      </RouterLink>
    </div>
    <nav class="flex-1 p-4 space-y-1">
      <RouterLink
        v-for="link in links"
        :key="link.to.name"
        :to="link.to"
        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors"
        :class="isActive(link.to) ? 'bg-cgr-purple/20 text-cgr-purple' : 'text-cgr-muted hover:text-white hover:bg-cgr-card'"
      >
        <span>{{ link.icon }}</span>
        {{ link.label }}
      </RouterLink>
    </nav>
  </aside>
</template>
