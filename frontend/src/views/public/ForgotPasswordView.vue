<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import GuestLayout from '../../layouts/GuestLayout.vue'
import { useFetchApi } from '../../composables/useFetchApi'

const router = useRouter()
const api = useFetchApi()

// Pasos: 1 = correo, 2 = código, 3 = nueva contraseña
const step = ref<1 | 2 | 3>(1)

const email = ref('')
const code = ref('')
const password = ref('')
const passwordConfirmation = ref('')

const errorMessage = ref('')
const infoMessage = ref('')
const resendLoading = ref(false)
const resendMessage = ref('')

function maskEmail(value: string): string {
  const [user = '', domain = ''] = value.split('@')
  const visible = user.slice(0, 3)
  const hidden = '*'.repeat(Math.max(user.length - 3, 2))
  return `${visible}${hidden}@${domain}`
}

// Paso 1 → enviar código
async function sendCode() {
  errorMessage.value = ''
  if (!/^\S+@\S+\.\S+$/.test(email.value)) {
    errorMessage.value = 'Ingresa un correo electrónico válido.'
    return
  }

  const result = await api.post<{ message: string }>('/password/forgot', { email: email.value })
  if (result) {
    infoMessage.value = result.message
    step.value = 2
  } else {
    errorMessage.value = api.error.value?.message ?? 'No se pudo enviar el código. Intenta de nuevo.'
  }
}

// Paso 2 → verificar código
async function verifyCode() {
  errorMessage.value = ''
  if (!/^\d{6}$/.test(code.value)) {
    errorMessage.value = 'El código debe ser de 6 dígitos.'
    return
  }

  const result = await api.post<{ message: string }>('/password/verify-code', {
    email: email.value,
    code: code.value,
  })
  if (result) {
    step.value = 3
  } else {
    errorMessage.value = api.error.value?.message ?? 'Código incorrecto o expirado.'
  }
}

// Paso 3 → restablecer contraseña
async function resetPassword() {
  errorMessage.value = ''
  if (password.value.length < 8) {
    errorMessage.value = 'La contraseña debe tener al menos 8 caracteres.'
    return
  }
  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Las contraseñas no coinciden.'
    return
  }

  const result = await api.post<{ message: string }>('/password/reset', {
    email: email.value,
    code: code.value,
    password: password.value,
    password_confirmation: passwordConfirmation.value,
  })
  if (result) {
    router.replace({ name: 'login', query: { reset: '1' } })
  } else {
    errorMessage.value = api.error.value?.message ?? 'No se pudo restablecer la contraseña.'
  }
}

async function resend() {
  resendLoading.value = true
  resendMessage.value = ''
  const result = await api.post<{ message: string }>('/password/forgot', { email: email.value })
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
            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
        </svg>
      </div>
      <h2 class="text-xl font-bold text-white mb-2">Restablecer contraseña</h2>
      <p v-if="step === 1" class="text-sm text-cgr-muted">
        Ingresa el correo de tu cuenta y te enviaremos un código de 6 dígitos.
      </p>
      <p v-else-if="step === 2" class="text-sm text-cgr-muted">
        Ingresa el código de 6 dígitos que enviamos a<br>
        <span class="text-white font-medium">{{ maskEmail(email) }}</span>
      </p>
      <p v-else class="text-sm text-cgr-muted">
        Crea una nueva contraseña para tu cuenta.
      </p>
    </div>

    <!-- Paso 1: correo -->
    <form v-if="step === 1" @submit.prevent="sendCode" class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Correo electrónico</label>
        <input
          v-model="email"
          type="email"
          required
          placeholder="tu@correo.com"
          autofocus
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
      </div>
      <p v-if="errorMessage" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
        {{ errorMessage }}
      </p>
      <button
        type="submit"
        :disabled="api.loading.value"
        class="w-full bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold py-2.5 rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity text-sm"
      >
        {{ api.loading.value ? 'Enviando…' : 'Enviar código' }}
      </button>
    </form>

    <!-- Paso 2: código -->
    <form v-else-if="step === 2" @submit.prevent="verifyCode" class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5 text-center">Código de verificación</label>
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
          :class="{ 'border-red-500/60': errorMessage }"
        />
      </div>
      <p v-if="errorMessage" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2 text-center">
        {{ errorMessage }}
      </p>
      <button
        type="submit"
        :disabled="api.loading.value || code.length !== 6"
        class="w-full bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold py-2.5 rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity text-sm"
      >
        {{ api.loading.value ? 'Verificando…' : 'Verificar código' }}
      </button>

      <div class="text-center">
        <p class="text-xs text-cgr-subtle mb-2">¿No recibiste el correo? Revisa spam o</p>
        <button
          type="button"
          :disabled="resendLoading"
          class="text-cgr-purple hover:text-cgr-accent text-sm font-medium disabled:opacity-50 transition-colors"
          @click="resend"
        >
          {{ resendLoading ? 'Enviando…' : 'Reenviar código' }}
        </button>
        <p v-if="resendMessage" class="mt-2 text-xs text-cgr-muted">{{ resendMessage }}</p>
      </div>
    </form>

    <!-- Paso 3: nueva contraseña -->
    <form v-else @submit.prevent="resetPassword" class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Nueva contraseña</label>
        <input
          v-model="password"
          type="password"
          required
          placeholder="••••••••"
          autofocus
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
      </div>
      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Confirmar contraseña</label>
        <input
          v-model="passwordConfirmation"
          type="password"
          required
          placeholder="••••••••"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
      </div>
      <p v-if="errorMessage" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
        {{ errorMessage }}
      </p>
      <button
        type="submit"
        :disabled="api.loading.value"
        class="w-full bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold py-2.5 rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity text-sm"
      >
        {{ api.loading.value ? 'Guardando…' : 'Cambiar contraseña' }}
      </button>
    </form>

    <div class="mt-6 text-center">
      <RouterLink to="/login" class="text-xs text-cgr-subtle hover:text-cgr-muted transition-colors">
        Volver a iniciar sesión
      </RouterLink>
    </div>
  </GuestLayout>
</template>
