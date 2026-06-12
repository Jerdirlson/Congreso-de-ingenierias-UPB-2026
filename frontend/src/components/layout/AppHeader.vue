<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import type { AuthUser, UserRole } from '../../stores/auth'
import { useAuthStore } from '../../stores/auth'
import ThemeControls from '../ThemeControls.vue'

const authStore = useAuthStore()
const router = useRouter()
const showRoleSwitcher = ref(false)

function stopImpersonation() {
  authStore.stopImpersonation()
  router.push({ name: 'admin-users' })
}

function switchToRole(r: UserRole) {
  authStore.setActiveRole(r)
  showRoleSwitcher.value = false
  if (r === 'revisor') router.push({ name: 'revisor-home' })
  else if (r === 'ponente') router.push({ name: 'ponente-home' })
  else if (r === 'participante') router.push({ name: 'participante-home' })
}

const props = defineProps<{
  user: AuthUser | null
  role: UserRole | null
  sidebarOpen: boolean
}>()

defineEmits<{ logout: []; toggleSidebar: [] }>()

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
  <!-- Impersonation banner -->
  <div
    v-if="authStore.impersonating"
    class="bg-orange-500/10 border-b border-orange-500/30 text-orange-300 px-4 py-2 flex items-center justify-between gap-3 text-sm"
  >
    <span>
      Estás viendo la plataforma como
      <strong>{{ authStore.user?.name }}</strong>
      · {{ authStore.user?.email }}
    </span>
    <button
      @click="stopImpersonation"
      class="px-3 py-1 bg-orange-500/20 border border-orange-500/40 text-orange-300 text-xs font-semibold rounded-lg hover:bg-orange-500/30 hover:border-orange-500/60 transition-colors shrink-0"
    >
      Salir de impersonación
    </button>
  </div>

  <header class="h-16 shrink-0 border-b border-cgr-border bg-cgr-bg/90 backdrop-blur-md px-4 flex items-center justify-between gap-3">

    <div class="flex items-center gap-3">
      <!-- Toggle sidebar -->
      <button
        @click="$emit('toggleSidebar')"
        class="p-1.5 rounded-lg border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple/50 transition-colors"
        :aria-label="sidebarOpen ? 'Colapsar menú' : 'Expandir menú'"
      >
        <!-- Barras → colapsar -->
        <svg v-if="sidebarOpen" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <!-- Flecha → expandir -->
        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <h1 class="text-sm font-semibold text-white hidden sm:block">Panel de gestión</h1>
    </div>

    <div class="flex items-center gap-3">
      <ThemeControls />
      <div class="text-right hidden sm:block">
        <p class="text-xs text-white font-medium">{{ user?.name }}</p>
        <p class="text-xs text-cgr-muted">{{ user?.email }}</p>
      </div>
      <span
        class="text-xs px-2.5 py-1 rounded-full font-medium border hidden sm:inline-flex"
        :class="roleBadgeClass"
      >
        {{ roleLabel }}
      </span>

      <!-- Cambiar rol (solo para usuarios con doble rol) -->
      <div v-if="authStore.hasDualRole" class="relative hidden sm:block">
        <button
          type="button"
          @click="showRoleSwitcher = !showRoleSwitcher"
          class="text-xs px-3 py-1.5 rounded-lg border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple/50 transition-colors flex items-center gap-1.5"
          title="Cambiar rol"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
          </svg>
          Cambiar rol
        </button>
        <div
          v-if="showRoleSwitcher"
          class="absolute right-0 top-full mt-1 z-50 w-48 bg-cgr-card border border-cgr-border rounded-xl shadow-xl overflow-hidden"
        >
          <button
            v-for="r in authStore.allRoles.filter(x => x !== 'admin' && x !== 'administrativo')"
            :key="r"
            @click="switchToRole(r as UserRole)"
            class="w-full text-left px-4 py-2.5 text-xs transition-colors"
            :class="r === authStore.role ? 'text-white bg-cgr-purple/20 font-semibold' : 'text-cgr-muted hover:text-white hover:bg-cgr-section'"
          >
            {{ r === 'revisor' ? '🔍 Revisor' : r === 'ponente' ? '🎤 Ponente' : '🎓 Participante' }}
            <span v-if="r === authStore.role" class="ml-1 text-cgr-purple">(activo)</span>
          </button>
        </div>
      </div>

      <RouterLink
        to="/"
        class="text-xs px-3 py-1.5 rounded-lg border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple transition-colors hidden sm:inline-flex"
      >
        Inicio
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
