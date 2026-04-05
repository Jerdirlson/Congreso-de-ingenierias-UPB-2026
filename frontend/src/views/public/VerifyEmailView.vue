<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import GuestLayout from '../../layouts/GuestLayout.vue'
import { useAuthStore } from '../../stores/auth'
import { useFetchApi } from '../../composables/useFetchApi'

const router = useRouter()
const auth = useAuthStore()
const api = useFetchApi()

const code = ref('')
const errorMessage = ref('')
const successMessage = ref('')
const resendMessage = ref('')
const resendLoading = ref(false)

onMounted(async () => {
  // Si tiene token pero no user cargado, recuperar perfil
  if (auth.token && !auth.user) {
    await auth.fetchMe()
  }
  // Si ya está verificado, redirigir al dashboard
  if (auth.isEmailVerified) {
    redirectToDashboard()
  }
})

function redirectToDashboard() {
  const r = auth.role
  if (r === 'ponente') router.replace({ name: 'ponente-home' })
  else if (r === 'participante') router.replace({ name: 'participante-home' })
  else if (r === 'revisor') router.replace({ name: 'revisor-home' })
  else if (r === 'admin' || r === 'administrativo') router.replace({ name: 'admin-home' })
  else router.replace({ name: 'landing' })
}

function maskEmail(email: string): string {
  const parts = email.split('@')
  const user = parts[0] ?? ''
  const domain = parts[1] ?? ''
  const visible = user.slice(0, 3)
  const hidden = '*'.repeat(Math.max(user.length - 3, 2))
  return `${visible}${hidden}@${domain}`
}

async function submitCode() {
  const email = auth.user?.email
  if (!email) {
    errorMessage.value = 'Sesión expirada. Inicia sesión nuevamente.'
    return
  }
  if (code.value.length !== 6 || !/^\d{6}$/.test(code.value)) {
    errorMessage.value = 'El código debe ser de 6 dígitos.'
    return
  }

  errorMessage.value = ''
  successMessage.value = ''

  const result = await api.post<{ message: string; email_verified?: boolean }>(
    '/email/verify-code',
    { email, code: code.value },
  )

  if (result) {
    successMessage.value = result.message
    await auth.fetchMe()
    setTimeout(redirectToDashboard, 800)
  } else {
    errorMessage.value = api.error.value?.message ?? 'Código incorrecto o expirado.'
    code.value = ''
  }
}

async function resendCode() {
  const email = auth.user?.email
  if (!email) return

  resendLoading.value = true
  resendMessage.value = ''

  const result = await api.post<{ message: string }>('/email/verification-notification', { email })

  resendLoading.value = false
  resendMessage.value = result?.message ?? api.error.value?.message ?? 'Error al reenviar.'
}
</script>

<template>
  <GuestLayout>
    <div class="text-center mb-8">
      <div class="w-16 h-16 mx-auto mb-5 rounded-full bg-cgr-purple-deep/50 flex items-center justify-center">
        <svg class="w-8 h-8 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
        </svg>
      </div>
      <h2 class="text-xl font-bold text-white mb-2">Verifica tu correo</h2>
      <p v-if="auth.user?.email" class="text-sm text-cgr-muted">
        Ingresa el código de 6 dígitos que enviamos a<br>
        <span class="text-white font-medium">{{ maskEmail(auth.user.email) }}</span>
      </p>
      <p v-else class="text-sm text-cgr-muted">
        Revisa tu correo electrónico para obtener el código de verificación.
      </p>
    </div>

    <form @submit.prevent="submitCode" class="space-y-4">
      <!-- Code input -->
      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5 text-center">
          Código de verificación
        </label>
        <input
          v-model="code"
          type="text"
          inputmode="numeric"
          pattern="[0-9]*"
          maxlength="6"
          autocomplete="one-time-code"
          placeholder="000000"
          autofocus
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-4 py-3 text-center text-2xl font-bold tracking-[0.5em] text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors font-mono"
          :class="{ 'border-red-500/60': errorMessage, 'border-green-500/60': successMessage }"
        />
      </div>

      <!-- Error -->
      <p
        v-if="errorMessage"
        class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2 text-center"
      >
        {{ errorMessage }}
      </p>

      <!-- Success -->
      <p
        v-if="successMessage"
        class="text-xs text-green-400 bg-green-500/10 border border-green-500/20 rounded-lg px-3 py-2 text-center"
      >
        {{ successMessage }} Redirigiendo…
      </p>

      <button
        type="submit"
        :disabled="api.loading.value || code.length !== 6"
        class="w-full bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold py-2.5 rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity text-sm"
      >
        {{ api.loading.value ? 'Verificando…' : 'Verificar cuenta' }}
      </button>
    </form>

    <!-- Resend -->
    <div class="mt-6 text-center">
      <p class="text-xs text-cgr-subtle mb-2">¿No recibiste el correo? Revisa la carpeta de spam o</p>
      <button
        type="button"
        :disabled="resendLoading"
        class="text-cgr-purple hover:text-cgr-accent text-sm font-medium disabled:opacity-50 transition-colors"
        @click="resendCode"
      >
        {{ resendLoading ? 'Enviando…' : 'Reenviar código' }}
      </button>
      <p v-if="resendMessage" class="mt-2 text-xs"
         :class="api.error.value ? 'text-red-400' : 'text-green-400'">
        {{ resendMessage }}
      </p>
    </div>

    <div class="mt-6 text-center">
      <RouterLink to="/login" class="text-xs text-cgr-subtle hover:text-cgr-muted transition-colors">
        Volver a iniciar sesión
      </RouterLink>
    </div>
  </GuestLayout>
</template>
