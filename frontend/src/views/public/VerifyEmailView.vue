<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import GuestLayout from '../../layouts/GuestLayout.vue'
import { useAuthStore } from '../../stores/auth'
import { useFetchApi } from '../../composables/useFetchApi'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const api = useFetchApi()

const resendMessage = ref('')
const justVerified = computed(() => route.query.verified === '1')

onMounted(async () => {
  if (justVerified.value && auth.token) {
    await auth.fetchMe()
    const r = auth.role
    if (r === 'ponente') router.replace({ name: 'ponente-home' })
    else if (r === 'participante') router.replace({ name: 'participante-home' })
    else if (r === 'revisor') router.replace({ name: 'revisor-home' })
    else if (r === 'admin' || r === 'administrativo') router.replace({ name: 'admin-home' })
    else router.replace({ name: 'landing' })
  }
})

async function resendEmail() {
  const email = auth.user?.email
  if (!email) return
  resendMessage.value = ''
  const data = await api.post<{ message: string }>('/email/verification-notification', { email })
  if (data) resendMessage.value = data.message ?? 'Correo reenviado.'
  else resendMessage.value = api.error.value?.message ?? 'Error al reenviar.'
}
</script>

<template>
  <GuestLayout>
    <div class="text-center">
      <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-cgr-purple-deep/50 flex items-center justify-center">
        <svg class="w-8 h-8 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
      </div>
      <h2 class="text-xl font-bold text-white mb-2">Revisa tu correo</h2>
      <p class="text-sm text-cgr-muted mb-6 max-w-sm mx-auto">
        Te hemos enviado un enlace de verificación a tu correo electrónico.
        Haz clic en el enlace para activar tu cuenta y poder usar todas las funciones.
      </p>
      <p class="text-xs text-cgr-subtle mb-4">
        Si no recibes el correo en unos minutos, revisa tu carpeta de spam.
      </p>
      <div v-if="auth.user?.email" class="mb-6">
        <button
          type="button"
          :disabled="api.loading.value"
          class="text-cgr-purple hover:text-cgr-accent text-sm font-medium"
          @click="resendEmail"
        >
          {{ api.loading.value ? 'Enviando…' : 'Reenviar correo de verificación' }}
        </button>
        <p v-if="resendMessage" class="mt-2 text-xs" :class="api.error.value ? 'text-red-400' : 'text-green-400'">
          {{ resendMessage }}
        </p>
      </div>
      <RouterLink
        to="/login"
        class="inline-block bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-6 py-2.5 rounded-lg hover:opacity-90 transition-opacity text-sm"
      >
        Ir a iniciar sesión
      </RouterLink>
    </div>
  </GuestLayout>
</template>
