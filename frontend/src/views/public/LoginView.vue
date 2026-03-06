<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import GuestLayout from '../../layouts/GuestLayout.vue'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const errorMessage = ref('')

const redirectTo = computed(() => (route.query.redirect as string) ?? '/')

async function submit() {
  errorMessage.value = ''
  const result = await auth.login(email.value, password.value)
  if (result.ok) {
    const r = auth.role
    if (r === 'ponente') router.push({ name: 'ponente-home' })
    else if (r === 'participante') router.push({ name: 'participante-home' })
    else if (r === 'revisor') router.push({ name: 'revisor-home' })
    else if (r === 'admin' || r === 'administrativo') router.push({ name: 'admin-home' })
    else router.push(redirectTo.value)
  } else {
    errorMessage.value = result.message ?? 'Credenciales incorrectas'
  }
}
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
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Contraseña</label>
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
  </GuestLayout>
</template>
