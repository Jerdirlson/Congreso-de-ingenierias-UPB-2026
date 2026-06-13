<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import GuestLayout from '../../layouts/GuestLayout.vue'
import { useAuthStore } from '../../stores/auth'
import type { UserRole } from '../../stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const showRoleModal = ref(false)

const redirectTo = computed(() => (route.query.redirect as string) ?? '/')
const justReset = computed(() => route.query.reset === '1')

function redirectByRole(r: UserRole | null) {
  if ((r === 'ponente' || r === 'participante') && !auth.isEmailVerified) {
    router.push({ name: 'verify-email' })
  } else if (r === 'ponente') router.push({ name: 'ponente-home' })
  else if (r === 'participante') router.push({ name: 'participante-home' })
  else if (r === 'revisor') router.push({ name: 'revisor-home' })
  else if (r === 'admin' || r === 'administrativo') router.push({ name: 'admin-home' })
  else router.push(redirectTo.value)
}

async function submit() {
  errorMessage.value = ''
  const result = await auth.login(email.value, password.value)
  if (result.ok) {
    if (auth.needsRoleSelection) {
      showRoleModal.value = true
    } else {
      redirectByRole(auth.role)
    }
  } else {
    errorMessage.value = result.message ?? 'Credenciales incorrectas'
  }
}

function selectRole(r: UserRole) {
  auth.setActiveRole(r)
  showRoleModal.value = false
  redirectByRole(r)
}

const roleOptions = computed(() => {
  const labels: Record<string, { label: string; desc: string; icon: string; color: string }> = {
    participante: {
      label: 'Participante',
      desc: 'Accede a la inscripción y seguimiento del congreso',
      icon: '🎓',
      color: 'border-green-500/40 hover:border-green-500 hover:bg-green-500/10',
    },
    ponente: {
      label: 'Ponente',
      desc: 'Gestiona tus ponencias y envíos',
      icon: '🎤',
      color: 'border-cgr-purple/40 hover:border-cgr-purple hover:bg-cgr-purple/10',
    },
    revisor: {
      label: 'Revisor',
      desc: 'Revisa y evalúa ponencias asignadas',
      icon: '🔍',
      color: 'border-blue-500/40 hover:border-blue-500 hover:bg-blue-500/10',
    },
  }
  return auth.allRoles
    .filter(r => r !== 'admin' && r !== 'administrativo')
    .map(r => ({ role: r as UserRole, ...(labels[r] ?? { label: r, desc: '', icon: '👤', color: '' }) }))
})
</script>

<template>
  <GuestLayout>
    <!-- Banner de registro prominente -->
    <div class="mb-6 rounded-xl border border-cgr-purple/30 bg-cgr-purple/10 px-4 py-4 flex flex-col gap-2">
      <p class="text-sm font-semibold text-white">¿Es tu primera vez en el congreso?</p>
      <p class="text-xs text-cgr-muted leading-relaxed">
        Si aún no tienes cuenta, crea una gratis para inscribirte como participante o ponente.
      </p>
      <RouterLink
        to="/register"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-cgr-purple hover:text-cgr-accent transition-colors mt-0.5"
      >
        Crear cuenta nueva
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
      </RouterLink>
    </div>

    <h2 class="text-xl font-bold text-white mb-1">Iniciar sesión</h2>
    <p class="text-sm text-cgr-muted mb-6">¿Ya tienes cuenta? Accede aquí</p>

    <p
      v-if="justReset"
      class="mb-4 text-xs text-green-400 bg-green-500/10 border border-green-500/20 rounded-lg px-3 py-2"
    >
      Tu contraseña fue actualizada. Inicia sesión con tu nueva contraseña.
    </p>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Correo electrónico</label>
        <input
          v-model="email"
          type="email"
          required
          placeholder="tu@correo.com"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
      </div>
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label class="block text-xs font-medium text-cgr-muted">Contraseña</label>
          <RouterLink
            to="/forgot-password"
            class="text-xs font-medium text-cgr-purple hover:text-cgr-accent transition-colors"
          >
            ¿Olvidaste tu contraseña?
          </RouterLink>
        </div>
        <input
          v-model="password"
          type="password"
          required
          placeholder="••••••••"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
      </div>
      <p
        v-if="errorMessage"
        class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2"
      >
        {{ errorMessage }}
      </p>
      <button
        type="submit"
        :disabled="auth.loading"
        class="w-full bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold py-2.5 rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity text-sm"
      >
        {{ auth.loading ? 'Entrando…' : 'Entrar' }}
      </button>
    </form>

    <!-- Modal de selección de rol -->
    <Teleport to="body">
      <div
        v-if="showRoleModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm px-4"
      >
        <div class="bg-cgr-card border border-cgr-border rounded-2xl p-6 w-full max-w-sm shadow-2xl">
          <h3 class="text-lg font-bold text-white mb-1">¿Cómo deseas ingresar?</h3>
          <p class="text-xs text-cgr-muted mb-5">
            Tu cuenta tiene múltiples roles. Elige con cuál quieres trabajar ahora.
          </p>

          <div class="flex flex-col gap-3">
            <button
              v-for="opt in roleOptions"
              :key="opt.role"
              @click="selectRole(opt.role)"
              class="flex items-center gap-4 w-full text-left border rounded-xl px-4 py-3.5 transition-all duration-150"
              :class="opt.color"
            >
              <span class="text-2xl">{{ opt.icon }}</span>
              <div>
                <p class="text-sm font-semibold text-white">{{ opt.label }}</p>
                <p class="text-xs text-cgr-muted mt-0.5">{{ opt.desc }}</p>
              </div>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </GuestLayout>
</template>
