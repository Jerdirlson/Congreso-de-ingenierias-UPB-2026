<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useFetchApi } from '../../composables/useFetchApi'
import { useSettingsStore } from '../../stores/settings'
import UiCard from '../../components/ui/UiCard.vue'

const api = useFetchApi()
const settingsStore = useSettingsStore()

interface Settings {
  ponente_registration_open: boolean
  submissions_open: boolean
  video_upload_open: boolean
}

const loading = ref(true)
const saving = ref(false)
const error = ref('')
const savedMessage = ref('')

const ponenteRegistrationOpen = ref(true)
const submissionsOpen = ref(true)
const videoUploadOpen = ref(true)

async function load() {
  loading.value = true
  error.value = ''
  const data = await api.get<Settings>('/admin/settings')
  if (data) {
    ponenteRegistrationOpen.value = data.ponente_registration_open
    submissionsOpen.value = data.submissions_open
    videoUploadOpen.value = data.video_upload_open
  } else {
    error.value = 'No se pudo cargar la configuración.'
  }
  loading.value = false
}

async function save() {
  saving.value = true
  error.value = ''
  savedMessage.value = ''
  const data = await api.put<Settings>('/admin/settings', {
    ponente_registration_open: ponenteRegistrationOpen.value,
    submissions_open: submissionsOpen.value,
    video_upload_open: videoUploadOpen.value,
  })
  saving.value = false
  if (data) {
    ponenteRegistrationOpen.value = data.ponente_registration_open
    submissionsOpen.value = data.submissions_open
    videoUploadOpen.value = data.video_upload_open
    // Mantener el store público sincronizado en esta sesión.
    settingsStore.ponenteRegistrationOpen = data.ponente_registration_open
    settingsStore.submissionsOpen = data.submissions_open
    settingsStore.videoUploadOpen = data.video_upload_open
    savedMessage.value = 'Cambios guardados.'
    setTimeout(() => (savedMessage.value = ''), 2500)
  } else {
    error.value = api.error.value?.message ?? 'No se pudieron guardar los cambios.'
    // Recargar el estado real ante un fallo.
    await load()
  }
}

onMounted(load)
</script>

<template>
  <div class="max-w-2xl space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-white">Configuración del congreso</h1>
      <p class="text-cgr-muted text-sm mt-1">
        Controla los cierres de inscripción, de subida de ponencias y de videoponencias.
      </p>
    </div>

    <div v-if="loading" class="text-cgr-muted text-sm py-8 text-center">Cargando…</div>

    <template v-else>
      <p v-if="error" class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
        {{ error }}
      </p>
      <p v-if="savedMessage" class="text-sm text-green-400 bg-green-500/10 border border-green-500/20 rounded-lg px-3 py-2">
        {{ savedMessage }}
      </p>

      <!-- Cierre de subida de ponencias -->
      <UiCard class="p-5">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-white font-semibold text-sm">Subida de ponencias</h2>
            <p class="text-cgr-muted text-xs mt-1 leading-relaxed">
              Cuando se desactiva, ningún ponente podrá crear ponencias nuevas
              (verán el aviso de "se acabó el tiempo"). Quienes ya subieron su resumen
              podrán seguir con su proceso (reenviar correcciones, subir documentos, etc.).
            </p>
          </div>
          <button
            type="button"
            role="switch"
            :aria-checked="submissionsOpen"
            :disabled="saving"
            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors disabled:opacity-50"
            :class="submissionsOpen ? 'bg-cgr-purple' : 'bg-cgr-border'"
            @click="submissionsOpen = !submissionsOpen; save()"
          >
            <span
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
              :class="submissionsOpen ? 'translate-x-6' : 'translate-x-1'"
            />
          </button>
        </div>
        <p class="text-xs mt-3 font-medium" :class="submissionsOpen ? 'text-green-400' : 'text-amber-400'">
          {{ submissionsOpen ? 'Abierta — se pueden subir ponencias nuevas' : 'Cerrada — no se aceptan ponencias nuevas' }}
        </p>
      </UiCard>

      <!-- Pausa del envío de videoponencias -->
      <UiCard class="p-5">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-white font-semibold text-sm">Envío de videoponencias (link de YouTube)</h2>
            <p class="text-cgr-muted text-xs mt-1 leading-relaxed">
              Cuando se desactiva, los ponentes virtuales verán un aviso de que estamos
              preparando las indicaciones del video y no podrán compartir su enlace todavía.
              Los enlaces ya recibidos no se ven afectados.
            </p>
          </div>
          <button
            type="button"
            role="switch"
            :aria-checked="videoUploadOpen"
            :disabled="saving"
            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors disabled:opacity-50"
            :class="videoUploadOpen ? 'bg-cgr-purple' : 'bg-cgr-border'"
            @click="videoUploadOpen = !videoUploadOpen; save()"
          >
            <span
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
              :class="videoUploadOpen ? 'translate-x-6' : 'translate-x-1'"
            />
          </button>
        </div>
        <p class="text-xs mt-3 font-medium" :class="videoUploadOpen ? 'text-green-400' : 'text-amber-400'">
          {{ videoUploadOpen ? 'Habilitado — los ponentes pueden compartir el link de su video' : 'En pausa — a la espera de publicar las indicaciones' }}
        </p>
      </UiCard>

      <!-- Cierre de registro de ponentes -->
      <UiCard class="p-5">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-white font-semibold text-sm">Registro de ponentes</h2>
            <p class="text-cgr-muted text-xs mt-1 leading-relaxed">
              Cuando se desactiva, ya no se podrán crear cuentas nuevas como ponente.
              El registro de participantes sigue disponible normalmente.
            </p>
          </div>
          <button
            type="button"
            role="switch"
            :aria-checked="ponenteRegistrationOpen"
            :disabled="saving"
            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors disabled:opacity-50"
            :class="ponenteRegistrationOpen ? 'bg-cgr-purple' : 'bg-cgr-border'"
            @click="ponenteRegistrationOpen = !ponenteRegistrationOpen; save()"
          >
            <span
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
              :class="ponenteRegistrationOpen ? 'translate-x-6' : 'translate-x-1'"
            />
          </button>
        </div>
        <p class="text-xs mt-3 font-medium" :class="ponenteRegistrationOpen ? 'text-green-400' : 'text-amber-400'">
          {{ ponenteRegistrationOpen ? 'Abierto — se pueden registrar ponentes' : 'Cerrado — solo registro de participantes' }}
        </p>
      </UiCard>
    </template>
  </div>
</template>
