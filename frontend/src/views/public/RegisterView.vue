<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import GuestLayout from '../../layouts/GuestLayout.vue'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const auth = useAuthStore()
const isSubmitting = computed(() => auth.loading)

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
    name: name.value,
    email: email.value,
    password: password.value,
    password_confirmation: passwordConfirmation.value,
    registration_type: registrationType.value,
    phone: phone.value || undefined,
    document_type: documentType.value || undefined,
    document_number: documentNumber.value || undefined,
    institution: institution.value || undefined,
    country: country.value || undefined,
    city: city.value || undefined,
  }

  const result = await auth.register(body)

  if (result.ok) {
    const r = auth.role
    if (r === 'ponente') router.push({ name: 'ponente-home' })
    else if (r === 'participante') router.push({ name: 'participante-home' })
    else router.push({ name: 'landing' })
    return
  }

  errorMessage.value = result.message ?? 'Error al registrarse'
  validationErrors.value = result.errors ?? {}
}
</script>

<template>
  <GuestLayout>
    <h2 class="text-xl font-bold text-white mb-1">Crear cuenta</h2>
    <p class="text-sm text-cgr-muted mb-6">Inscríbete al Congreso Internacional de Ingeniería 2026</p>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Tipo de inscripción</label>
        <select
          v-model="registrationType"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-cgr-purple transition-colors"
        >
          <option value="participante">Solo asistencia (participante)</option>
          <option value="ponente">Presentar ponencia (ponente)</option>
        </select>
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
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Teléfono</label>
        <input
          v-model="phone"
          type="tel"
          placeholder="+57 300 123 4567"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Tipo de documento</label>
          <select
            v-model="documentType"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-cgr-purple transition-colors"
          >
            <option value="cedula">Cédula</option>
            <option value="pasaporte">Pasaporte</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Número de documento</label>
          <input
            v-model="documentNumber"
            type="text"
            placeholder="12345678"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
          />
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-cgr-muted mb-1.5">Institución</label>
        <input
          v-model="institution"
          type="text"
          placeholder="UPB"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
        />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">País</label>
          <input
            v-model="country"
            type="text"
            placeholder="Colombia"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">Ciudad</label>
          <input
            v-model="city"
            type="text"
            placeholder="Bucaramanga"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
          />
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
