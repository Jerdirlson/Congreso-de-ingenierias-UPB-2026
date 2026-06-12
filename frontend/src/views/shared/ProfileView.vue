<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'

const auth = useAuthStore()
const api  = useFetchApi()

const currentPassword = ref('')
const newPassword     = ref('')
const confirmPassword = ref('')
const saving          = ref(false)
const successMsg      = ref('')
const errorMsg        = ref('')
const fieldErrors     = ref<Record<string, string>>({})

async function submit() {
  successMsg.value  = ''
  errorMsg.value    = ''
  fieldErrors.value = {}
  saving.value      = true

  const res = await api.patch<{ message: string }>('/me/password', {
    current_password:      currentPassword.value,
    password:              newPassword.value,
    password_confirmation: confirmPassword.value,
  })

  if (res) {
    successMsg.value  = 'Contraseña actualizada correctamente.'
    currentPassword.value = ''
    newPassword.value     = ''
    confirmPassword.value = ''
  } else {
    const err = api.error.value
    if (err?.errors) {
      fieldErrors.value = Object.fromEntries(
        Object.entries(err.errors).map(([k, v]) => [k, (v as string[])[0] ?? ''])
      )
    } else {
      errorMsg.value = err?.message ?? 'Ocurrió un error al cambiar la contraseña.'
    }
  }

  saving.value = false
}
</script>

<template>
  <div class="max-w-lg space-y-6">
    <div>
      <h1 class="text-2xl font-black text-white">Mi perfil</h1>
      <p class="text-cgr-muted text-sm mt-0.5">Gestiona tu información de cuenta</p>
    </div>

    <!-- Info del usuario -->
    <UiCard class="p-5 space-y-4">
      <p class="text-xs font-semibold text-cgr-subtle uppercase tracking-widest">Información</p>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-[10px] text-cgr-subtle uppercase tracking-widest mb-0.5">Nombre</p>
          <p class="text-white text-sm font-medium">{{ auth.user?.name }}</p>
        </div>
        <div>
          <p class="text-[10px] text-cgr-subtle uppercase tracking-widest mb-0.5">Correo</p>
          <p class="text-white text-sm">{{ auth.user?.email }}</p>
        </div>
        <div>
          <p class="text-[10px] text-cgr-subtle uppercase tracking-widest mb-0.5">Institución</p>
          <p class="text-white text-sm">{{ auth.user?.institution ?? '—' }}</p>
        </div>
        <div>
          <p class="text-[10px] text-cgr-subtle uppercase tracking-widest mb-0.5">País</p>
          <p class="text-white text-sm">{{ auth.user?.country ?? '—' }}</p>
        </div>
      </div>
    </UiCard>

    <!-- Cambiar contraseña -->
    <UiCard class="p-5">
      <p class="text-xs font-semibold text-cgr-subtle uppercase tracking-widest mb-4">Cambiar contraseña</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Contraseña actual</label>
          <input
            v-model="currentPassword"
            type="password"
            required
            placeholder="••••••••"
            class="w-full bg-cgr-bg border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none transition-colors"
            :class="fieldErrors.current_password ? 'border-red-500/60' : 'border-cgr-border focus:border-cgr-purple'"
          />
          <p v-if="fieldErrors.current_password" class="text-xs text-red-400 mt-1">{{ fieldErrors.current_password }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Nueva contraseña</label>
          <input
            v-model="newPassword"
            type="password"
            required
            placeholder="Mínimo 8 caracteres"
            class="w-full bg-cgr-bg border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none transition-colors"
            :class="fieldErrors.password ? 'border-red-500/60' : 'border-cgr-border focus:border-cgr-purple'"
          />
          <p v-if="fieldErrors.password" class="text-xs text-red-400 mt-1">{{ fieldErrors.password }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Confirmar nueva contraseña</label>
          <input
            v-model="confirmPassword"
            type="password"
            required
            placeholder="••••••••"
            class="w-full bg-cgr-bg border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none transition-colors"
            :class="fieldErrors.password_confirmation ? 'border-red-500/60' : 'border-cgr-border focus:border-cgr-purple'"
          />
          <p v-if="fieldErrors.password_confirmation" class="text-xs text-red-400 mt-1">{{ fieldErrors.password_confirmation }}</p>
        </div>

        <p v-if="errorMsg" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
          {{ errorMsg }}
        </p>
        <p v-if="successMsg" class="text-xs text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-lg px-3 py-2">
          {{ successMsg }}
        </p>

        <button
          type="submit"
          :disabled="saving"
          class="w-full bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold py-2.5 rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity text-sm"
        >
          {{ saving ? 'Guardando…' : 'Actualizar contraseña' }}
        </button>
      </form>
    </UiCard>
  </div>
</template>
