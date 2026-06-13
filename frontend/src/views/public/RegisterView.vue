<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import GuestLayout from '../../layouts/GuestLayout.vue'
import { useAuthStore } from '../../stores/auth'
import { useSettingsStore } from '../../stores/settings'

const router = useRouter()
const auth = useAuthStore()
const settings = useSettingsStore()
const isSubmitting = computed(() => auth.loading)

const ponenteRegistrationClosed = computed(() => settings.loaded && !settings.ponenteRegistrationOpen)

onMounted(async () => {
  await settings.fetch()
  // Si el registro de ponentes está cerrado, forzar participante.
  if (ponenteRegistrationClosed.value) {
    registrationType.value = 'participante'
  }
})

const registrationType = ref<'ponente' | 'participante'>('participante')
const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const phone = ref('')
const documentType = ref('cedula')
const documentNumber = ref('')
const institution = ref('')
const country = ref('')
const city = ref('')
const errorMessage = ref('')
const validationErrors = ref<Record<string, string[]>>({})

async function submit() {
  errorMessage.value = ''
  validationErrors.value = {}

  const body = {
    name: name.value.trim(),
    email: email.value.trim(),
    password: password.value,
    password_confirmation: passwordConfirmation.value,
    registration_type: registrationType.value,
    phone: phone.value.trim(),
    document_type: documentType.value,
    document_number: documentNumber.value.trim(),
    institution: institution.value.trim(),
    country: country.value.trim(),
    city: city.value.trim(),
  }

  const result = await auth.register(body)

  if (result.ok) {
    router.push({ name: 'verify-email' })
    return
  }

  errorMessage.value = result.message ?? 'Error al registrarse'
  validationErrors.value = result.errors ?? {}
}
</script>

<template>
  <GuestLayout>
    <h2 class="text-xl font-bold text-white mb-1">Crear cuenta</h2>
    <p class="text-sm text-cgr-muted mb-4">Inscríbete al Congreso Internacional de Ingeniería 2026</p>

    <div class="mb-6 flex gap-2 text-xs text-amber-200 bg-amber-500/10 border border-amber-500/30 rounded-lg px-3 py-2.5">
      <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span>
        <strong class="font-semibold">Todos los campos marcados con * son obligatorios.</strong>
        Los usaremos para emitir tu certificado al finalizar el congreso, así que asegúrate de
        que tu nombre, documento e institución estén correctos.
      </span>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Tipo de inscripción</label>
        <select
          v-model="registrationType"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-cgr-purple transition-colors"
        >
          <option value="participante">Solo asistencia (participante)</option>
          <option v-if="!ponenteRegistrationClosed" value="ponente">Presentar ponencia (ponente)</option>
        </select>
        <p v-if="ponenteRegistrationClosed" class="mt-1.5 text-xs text-amber-300/80">
          El registro de ponentes ya cerró. Puedes inscribirte como participante.
        </p>
      </div>

      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Nombre completo *</label>
        <input
          v-model="name"
          type="text"
          required
          placeholder="Juan Pérez"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
        <p v-if="validationErrors.name" class="mt-1 text-xs text-red-400">{{ validationErrors.name[0] }}</p>
      </div>

      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Correo electrónico *</label>
        <input
          v-model="email"
          type="email"
          required
          placeholder="tu@correo.com"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
        <p v-if="validationErrors.email" class="mt-1 text-xs text-red-400">{{ validationErrors.email[0] }}</p>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Contraseña *</label>
          <input
            v-model="password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
          />
          <p v-if="validationErrors.password" class="mt-1 text-xs text-red-400">{{ validationErrors.password[0] }}</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Confirmar contraseña *</label>
          <input
            v-model="passwordConfirmation"
            type="password"
            required
            placeholder="••••••••"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
          />
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Teléfono *</label>
        <input
          v-model="phone"
          type="tel"
          required
          placeholder="+57 300 123 4567"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
        <p v-if="validationErrors.phone" class="mt-1 text-xs text-red-400">{{ validationErrors.phone[0] }}</p>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Tipo de documento *</label>
          <select
            v-model="documentType"
            required
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-cgr-purple transition-colors"
          >
            <option value="cedula">Cédula</option>
            <option value="pasaporte">Pasaporte</option>
            <option value="cc_extranjera">Cédula extranjería</option>
          </select>
          <p v-if="validationErrors.document_type" class="mt-1 text-xs text-red-400">{{ validationErrors.document_type[0] }}</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Número de documento *</label>
          <input
            v-model="documentNumber"
            type="text"
            required
            placeholder="12345678"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
          />
          <p v-if="validationErrors.document_number" class="mt-1 text-xs text-red-400">{{ validationErrors.document_number[0] }}</p>
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Institución *</label>
        <input
          v-model="institution"
          type="text"
          required
          placeholder="UPB"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
        <p v-if="validationErrors.institution" class="mt-1 text-xs text-red-400">{{ validationErrors.institution[0] }}</p>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">País *</label>
          <input
            v-model="country"
            type="text"
            required
            placeholder="Colombia"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
          />
          <p v-if="validationErrors.country" class="mt-1 text-xs text-red-400">{{ validationErrors.country[0] }}</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Ciudad *</label>
          <input
            v-model="city"
            type="text"
            required
            placeholder="Bucaramanga"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
          />
          <p v-if="validationErrors.city" class="mt-1 text-xs text-red-400">{{ validationErrors.city[0] }}</p>
        </div>
      </div>

      <p
        v-if="errorMessage"
        class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2"
      >
        {{ errorMessage }}
      </p>

      <button
        type="submit"
        :disabled="isSubmitting"
        class="w-full bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold py-2.5 rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity text-sm"
      >
        {{ isSubmitting ? 'Registrando…' : 'Registrarme' }}
      </button>
    </form>

    <p class="mt-6 text-center text-sm text-cgr-muted">
      ¿Ya tienes cuenta?
      <RouterLink to="/login" class="text-cgr-purple hover:text-cgr-accent font-medium">
        Inicia sesión
      </RouterLink>
    </p>
  </GuestLayout>
</template>
