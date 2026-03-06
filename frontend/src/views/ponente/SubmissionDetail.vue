<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useFetchApi, getApiToken } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiButton from '../../components/ui/UiButton.vue'
import UiBadge from '../../components/ui/UiBadge.vue'
import UiSteps from '../../components/ui/UiSteps.vue'

const route = useRoute()
const router = useRouter()
const api = useFetchApi()

const submission = ref<{
  id: number
  title: string
  status: string
  modality: string | null
  thematic_axis?: { id: number; name: string }
  abstracts?: { id: number; content: string; llm_status: string; llm_axis?: { name: string }; llm_justification?: string }[]
  documents?: { id: number; original_filename: string; version: number; status: string }[]
  video?: { id: number; status: string; error_message?: string | null; original_filename?: string | null } | null
  reviews?: { id: number; status: string; decision: string | null; comments: string | null; completed_at: string | null; reviewer?: { name: string } }[]
} | null>(null)

const abstractContent = ref('')
const documentFile = ref<File | null>(null)
const modalityChoice = ref<string>('')
const errorMessage = ref('')
const confirmDelete = ref(false)
const deleting = ref(false)
const videoFile = ref<File | null>(null)
const uploadProgress = ref(0)
const uploading = ref(false)
const videoValidationError = ref('')
let videoPolling: ReturnType<typeof setInterval> | null = null

const deletableStatuses = ['draft', 'abstract_rejected', 'abstract_submitted']
const canDelete = computed(() => deletableStatuses.includes(submission.value?.status ?? ''))

// Flujo: Resumen → Documento → Modalidad → (Video si virtual) → Confirmado
const STEPS = [
  { key: 'abstract', label: 'Resumen' },
  { key: 'document', label: 'Documento' },
  { key: 'modality', label: 'Modalidad' },
  { key: 'confirmed', label: 'Confirmado' },
]

const MODALITIES = [
  { value: 'presencial_oral',   label: 'Presencial oral' },
  { value: 'presencial_poster', label: 'Presencial póster' },
  { value: 'virtual',           label: 'Virtual (requiere video)' },
  { value: 'proyecto_aula',     label: 'Proyecto de aula' },
]

const currentStepIndex = computed(() => {
  const s = submission.value?.status
  if (!s) return 0
  if (['draft', 'abstract_submitted', 'abstract_rejected'].includes(s)) return 0
  if (['abstract_approved', 'under_review', 'revision_requested'].includes(s)) return 1
  if (s === 'document_approved') return 2
  if (['modality_selected', 'video_pending'].includes(s)) return 3
  if (s === 'video_ready') return 4
  if (s === 'confirmed') return 4
  return 0
})

const canSubmitAbstract = computed(() => {
  const s = submission.value?.status
  return s === 'draft' || s === 'abstract_rejected'
})

const canSubmitDocument = computed(() => {
  const s = submission.value?.status
  return s === 'abstract_approved' || s === 'revision_requested'
})

const canSelectModality = computed(() => submission.value?.status === 'document_approved')

const isVirtual = computed(() => submission.value?.modality === 'virtual')
const canUploadVideo = computed(() => {
  const s = submission.value?.status
  const vs = submission.value?.video?.status
  // Show upload form only when no ready/processing video exists
  return (s === 'video_pending' || s === 'video_ready')
    && vs !== 'ready'
    && vs !== 'processing'
})

const latestAbstract = computed(() => {
  const abs = submission.value?.abstracts
  return abs?.length ? abs[0] : null
})

const llmClassifying = computed(() => latestAbstract.value?.llm_status === 'pending')

const revisionReview = computed(() => {
  const reviews = submission.value?.reviews ?? []
  return reviews
    .filter(r => r.status === 'completed' && r.decision === 'rejected')
    .sort((a, b) => new Date(b.completed_at ?? 0).getTime() - new Date(a.completed_at ?? 0).getTime())[0] ?? null
})

const latestDocument = computed(() => {
  const docs = submission.value?.documents
  return docs?.length ? docs[0] : null
})

async function loadSubmission() {
  const id = route.params.id
  const data = await api.get<typeof submission.value>(`/submissions/${id}`)
  if (data) submission.value = data
  else router.push({ name: 'ponente-home' })
}

async function submitAbstract() {
  errorMessage.value = ''
  const data = await api.post<unknown>(`/submissions/${route.params.id}/abstracts`, { content: abstractContent.value })
  if (data) {
    abstractContent.value = ''
    await loadSubmission()
  } else {
    errorMessage.value = api.error.value?.message ?? 'Error al enviar el resumen'
  }
}

async function submitDocument() {
  if (!documentFile.value) return
  errorMessage.value = ''
  const form = new FormData()
  form.append('file', documentFile.value)
  const data = await api.postForm<unknown>(`/submissions/${route.params.id}/documents`, form)
  if (data) {
    documentFile.value = null
    await loadSubmission()
  } else {
    errorMessage.value = api.error.value?.message ?? 'Error al subir el documento'
  }
}

async function submitModality() {
  if (!modalityChoice.value) return
  errorMessage.value = ''
  const data = await api.patch<unknown>(`/submissions/${route.params.id}/modality`, { modality: modalityChoice.value })
  if (data) await loadSubmission()
  else errorMessage.value = api.error.value?.message ?? 'Error al guardar la modalidad'
}

async function downloadDocument(docId: number, filename: string) {
  const token = getApiToken()
  try {
    const res = await fetch(`/api/submissions/${route.params.id}/documents/${docId}/download`, {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/pdf' },
    })
    if (!res.ok) {
      const json = await res.json().catch(() => ({}))
      errorMessage.value = json.message ?? `Error ${res.status} al descargar el archivo`
      return
    }
    const blob = await res.blob()
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  } catch (e) {
    errorMessage.value = 'No se pudo descargar el archivo. Verifica tu conexión.'
  }
}

async function deleteSubmission() {
  deleting.value = true
  await api.delete(`/submissions/${route.params.id}`)
  deleting.value = false
  router.push({ name: 'ponente-home' })
}

const VIDEO_MAX_DURATION  = 600  // 10 min in seconds
const VIDEO_MIN_WIDTH     = 1280 // 720p minimum
const VIDEO_MIN_HEIGHT    = 720
const VIDEO_ASPECT_MIN    = 1.6  // allow ~16:10 and above
const VIDEO_ASPECT_MAX    = 2.0  // up to ~2:1

async function validateVideoFile(file: File): Promise<string> {
  return new Promise((resolve) => {
    const video = document.createElement('video')
    video.preload = 'metadata'
    const url = URL.createObjectURL(file)
    video.src = url

    const cleanup = () => { URL.revokeObjectURL(url); video.src = '' }

    video.onloadedmetadata = () => {
      const { duration, videoWidth, videoHeight } = video
      cleanup()

      if (duration > VIDEO_MAX_DURATION) {
        resolve(`La duración del video es ${Math.round(duration / 60)} min — el máximo permitido es 10 min.`)
        return
      }
      if (videoWidth < VIDEO_MIN_WIDTH || videoHeight < VIDEO_MIN_HEIGHT) {
        resolve(`La resolución mínima requerida es 1280×720 (720p). Tu video es ${videoWidth}×${videoHeight}.`)
        return
      }
      const ratio = videoWidth / videoHeight
      if (ratio < VIDEO_ASPECT_MIN || ratio > VIDEO_ASPECT_MAX) {
        resolve(`El video debe tener proporción 16:9. Tu video tiene proporción ${ratio.toFixed(2)}:1.`)
        return
      }
      resolve('')
    }

    video.onerror = () => {
      cleanup()
      resolve('No se pudo leer el archivo de video. Verifica que sea un archivo válido (mp4, mov, webm).')
    }

    // Timeout fallback if metadata never loads
    setTimeout(() => {
      cleanup()
      resolve('')  // allow upload if we can't read metadata (server will validate size/type)
    }, 8000)
  })
}

async function uploadVideo() {
  if (!videoFile.value) return
  errorMessage.value = ''
  videoValidationError.value = ''

  // Client-side validation
  const validationErr = await validateVideoFile(videoFile.value)
  if (validationErr) {
    videoValidationError.value = validationErr
    return
  }

  uploading.value = true
  uploadProgress.value = 0

  const token = getApiToken()
  const form = new FormData()
  form.append('file', videoFile.value)

  await new Promise<void>((resolve) => {
    const xhr = new XMLHttpRequest()
    xhr.open('POST', `/api/submissions/${route.params.id}/videos`)
    xhr.setRequestHeader('Authorization', `Bearer ${token}`)
    xhr.setRequestHeader('Accept', 'application/json')

    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable) uploadProgress.value = Math.round((e.loaded / e.total) * 100)
    }

    xhr.onload = async () => {
      uploading.value = false
      uploadProgress.value = 0
      videoFile.value = null
      if (xhr.status === 201) {
        await loadSubmission()
        startVideoPolling()
      } else {
        try {
          errorMessage.value = JSON.parse(xhr.responseText)?.message ?? `Error ${xhr.status}`
        } catch {
          errorMessage.value = `Error al subir el video (${xhr.status})`
        }
      }
      resolve()
    }

    xhr.onerror = () => {
      uploading.value = false
      errorMessage.value = 'Error de red al subir el video.'
      resolve()
    }

    xhr.send(form)
  })
}

function startVideoPolling() {
  stopVideoPolling()
  videoPolling = setInterval(async () => {
    const data = await useFetchApi().get<{ status: string }>(`/submissions/${route.params.id}/videos/status`)
    if (data?.status === 'ready' || data?.status === 'error') {
      stopVideoPolling()
      await loadSubmission()
    }
  }, 3000)
}

function stopVideoPolling() {
  if (videoPolling) { clearInterval(videoPolling); videoPolling = null }
}

onMounted(loadSubmission)
watch(() => route.params.id, () => {
  stopVideoPolling()
  loadSubmission()
})
</script>

<template>
  <div class="max-w-3xl">
    <div class="mb-6">
      <RouterLink :to="{ name: 'ponente-home' }" class="text-sm text-cgr-muted hover:text-white mb-4 inline-block">
        ← Volver a mis ponencias
      </RouterLink>
      <h1 class="text-2xl font-bold text-white">{{ submission?.title ?? 'Cargando…' }}</h1>
      <p v-if="submission?.thematic_axis" class="text-sm text-cgr-muted mt-1">
        Eje: {{ submission.thematic_axis.name }}
      </p>
      <div v-if="canDelete && !confirmDelete" class="mt-4">
        <button
          class="text-xs text-red-400 hover:text-red-300 border border-red-500/30 hover:border-red-400/60 rounded-lg px-3 py-1.5 transition-colors"
          @click="confirmDelete = true"
        >
          Eliminar ponencia
        </button>
      </div>
      <div v-if="confirmDelete" class="mt-4 flex items-center gap-3 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3">
        <p class="text-sm text-red-300 flex-1">¿Eliminar esta ponencia? Podrás crear una nueva después.</p>
        <UiButton size="sm" variant="danger" :loading="deleting" @click="deleteSubmission()">
          Sí, eliminar
        </UiButton>
        <button class="text-xs text-cgr-muted hover:text-white transition-colors" @click="confirmDelete = false">
          Cancelar
        </button>
      </div>
    </div>

    <UiSteps :steps="STEPS" :current="Math.min(currentStepIndex, 3)" class="mb-8" />

    <p v-if="errorMessage" class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-2">
      {{ errorMessage }}
    </p>

    <!-- ── Paso 1: Resumen ── -->
    <UiCard class="p-6 mb-4">
      <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
        1. Resumen
        <UiBadge v-if="latestAbstract?.llm_status === 'approved'" variant="success">Aprobado por IA</UiBadge>
        <UiBadge v-else-if="latestAbstract?.llm_status === 'rejected'" variant="danger">Rechazado por IA</UiBadge>
        <UiBadge v-else-if="llmClassifying" variant="info">Clasificando…</UiBadge>
      </h2>

      <!-- Puede enviar resumen -->
      <div v-if="canSubmitAbstract">
        <p v-if="latestAbstract?.llm_status === 'rejected'" class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2 mb-3">
          <strong>Motivo del rechazo:</strong> {{ latestAbstract?.llm_justification ?? 'Sin justificación disponible' }}
        </p>
        <textarea
          v-model="abstractContent"
          rows="6"
          placeholder="Escribe el resumen de tu ponencia (mínimo 150 palabras)…"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple resize-y"
        />
        <UiButton class="mt-4" :loading="api.loading.value" :disabled="!abstractContent.trim()" @click="submitAbstract">
          Enviar resumen
        </UiButton>
      </div>

      <!-- Clasificando con LLM -->
      <div v-else-if="llmClassifying" class="flex items-center gap-3 text-cgr-muted text-sm">
        <div class="w-4 h-4 border-2 border-cgr-purple border-t-transparent rounded-full animate-spin shrink-0"></div>
        Clasificando con IA… esto puede tomar unos segundos.
      </div>

      <!-- Resumen aprobado -->
      <div v-else-if="latestAbstract" class="text-sm text-cgr-muted space-y-1">
        <p>Resumen enviado correctamente.</p>
        <p v-if="latestAbstract.llm_axis" class="text-cgr-purple">
          Eje temático asignado: <strong>{{ latestAbstract.llm_axis.name }}</strong>
        </p>
      </div>
    </UiCard>

    <!-- ── Paso 2: Documento PDF ── -->
    <UiCard class="p-6 mb-4">
      <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
        2. Documento PDF
        <UiBadge v-if="latestDocument?.status === 'approved'" variant="success">Aprobado</UiBadge>
        <UiBadge v-else-if="latestDocument?.status === 'revision_requested'" variant="warning">Requiere revisión</UiBadge>
        <UiBadge v-else-if="latestDocument?.status === 'under_review'" variant="info">En revisión</UiBadge>
      </h2>

      <div v-if="canSubmitDocument">
        <div v-if="submission?.status === 'revision_requested'" class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg px-4 py-4 mb-4">
          <div class="flex gap-3 items-start mb-3">
            <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
              <p class="text-sm font-semibold text-yellow-300">El comité solicitó correcciones en tu documento</p>
              <p class="text-xs text-yellow-200/70 mt-0.5">
                Revisor: {{ revisionReview?.reviewer?.name ?? 'Comité científico' }}
              </p>
            </div>
          </div>
          <div v-if="revisionReview?.comments" class="bg-yellow-500/10 border border-yellow-400/20 rounded-lg px-3 py-3 text-sm text-yellow-100 whitespace-pre-wrap leading-relaxed">
            {{ revisionReview.comments }}
          </div>
          <p v-else class="text-xs text-yellow-200/60">Sin comentarios adicionales del revisor.</p>
          <p class="text-xs text-yellow-200/60 mt-3">Realiza los cambios indicados y sube una nueva versión del PDF.</p>
        </div>
        <input
          type="file"
          accept=".pdf"
          class="block w-full text-sm text-cgr-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cgr-purple file:text-white cursor-pointer"
          @change="documentFile = ($event.target as HTMLInputElement).files?.[0] ?? null"
        />
        <UiButton class="mt-4" :disabled="!documentFile" :loading="api.loading.value" @click="submitDocument">
          Subir documento
        </UiButton>
      </div>

      <!-- En revisión -->
      <div v-else-if="latestDocument && submission?.status === 'under_review'" class="space-y-3">
        <div class="flex gap-3 items-start bg-blue-500/10 border border-blue-500/20 rounded-xl px-4 py-3">
          <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="text-sm font-semibold text-blue-300">Documento enviado — en espera de revisión</p>
            <p class="text-xs text-blue-200/70 mt-1 leading-relaxed">Un miembro del comité científico revisará tu ponencia. Este proceso puede tardar varios días hábiles. Recibirás respuesta con la aprobación o correcciones necesarias.</p>
          </div>
        </div>
        <div class="flex items-center justify-between gap-4 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3">
          <div class="flex items-center gap-3 min-w-0">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/></svg>
            <div class="min-w-0">
              <p class="text-sm text-white truncate">{{ latestDocument.original_filename }}</p>
              <p class="text-xs text-cgr-subtle">Versión {{ latestDocument.version }}</p>
            </div>
          </div>
          <button
            class="flex items-center gap-1.5 text-xs font-medium text-cgr-purple hover:text-cgr-accent border border-cgr-purple/30 hover:border-cgr-purple/60 rounded-lg px-3 py-1.5 transition-colors shrink-0"
            @click="downloadDocument(latestDocument.id, latestDocument.original_filename)"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Descargar PDF
          </button>
        </div>
      </div>

      <!-- Documento aprobado u otro estado con archivo -->
      <div v-else-if="latestDocument" class="flex items-center justify-between gap-4 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3">
        <div class="flex items-center gap-3 min-w-0">
          <svg class="w-5 h-5 text-red-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/></svg>
          <div class="min-w-0">
            <p class="text-sm text-white truncate">{{ latestDocument.original_filename }}</p>
            <p class="text-xs text-cgr-subtle">Versión {{ latestDocument.version }}</p>
          </div>
        </div>
        <button
          class="flex items-center gap-1.5 text-xs font-medium text-cgr-purple hover:text-cgr-accent border border-cgr-purple/30 hover:border-cgr-purple/60 rounded-lg px-3 py-1.5 transition-colors shrink-0"
          @click="downloadDocument(latestDocument.id, latestDocument.original_filename)"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Descargar PDF
        </button>
      </div>

      <div v-else class="text-cgr-subtle text-sm">
        Completa el paso anterior primero.
      </div>
    </UiCard>

    <!-- ── Paso 3: Modalidad ── -->
    <UiCard class="p-6 mb-4">
      <h2 class="font-semibold text-white mb-4">3. Modalidad de presentación</h2>

      <div v-if="canSelectModality">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
          <label
            v-for="m in MODALITIES"
            :key="m.value"
            :class="[
              'flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors',
              modalityChoice === m.value
                ? 'border-cgr-purple bg-cgr-purple/10 text-white'
                : 'border-cgr-border bg-cgr-section text-cgr-muted hover:border-cgr-purple/50'
            ]"
          >
            <input type="radio" :value="m.value" v-model="modalityChoice" class="hidden" />
            <span class="text-sm font-medium">{{ m.label }}</span>
          </label>
        </div>
        <UiButton :disabled="!modalityChoice" :loading="api.loading.value" @click="submitModality">
          Confirmar modalidad
        </UiButton>
      </div>

      <div v-else-if="submission?.modality" class="text-sm text-cgr-muted">
        Modalidad seleccionada:
        <strong class="text-white ml-1">
          {{ MODALITIES.find(m => m.value === submission?.modality)?.label ?? submission?.modality }}
        </strong>
      </div>

      <div v-else class="text-cgr-subtle text-sm">
        Completa los pasos anteriores primero.
      </div>
    </UiCard>

    <!-- ── Paso 4 (solo virtual): Video ── -->
    <UiCard v-if="isVirtual || canUploadVideo || submission?.video" class="p-6 mb-4">
      <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
        4. Videoponencia
        <UiBadge v-if="submission?.video?.status === 'ready'" variant="success">Lista</UiBadge>
        <UiBadge v-else-if="submission?.video?.status === 'processing'" variant="info">Procesando…</UiBadge>
      </h2>

      <!-- Video listo -->
      <div v-if="submission?.video?.status === 'ready'">
        <div class="flex items-center gap-3 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3">
          <svg class="w-5 h-5 text-green-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-white">Video recibido correctamente</p>
            <p v-if="submission.video?.original_filename" class="text-xs text-cgr-subtle truncate">{{ submission.video.original_filename }}</p>
          </div>
          <span class="ml-auto text-xs text-green-400 shrink-0 font-medium">Listo</span>
        </div>
      </div>

      <!-- Procesando -->
      <div v-else-if="submission?.video?.status === 'processing'" class="flex items-center gap-3 text-sm text-cgr-muted">
        <div class="w-4 h-4 border-2 border-cgr-purple border-t-transparent rounded-full animate-spin shrink-0"></div>
        Procesando tu video, espera un momento…
      </div>

      <!-- Video rechazado por admin -->
      <div v-if="submission?.video?.status === 'rejected'" class="mb-4">
        <div class="flex items-start gap-3 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-4">
          <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="text-sm font-semibold text-red-300 mb-1">El comité rechazó tu videoponencia</p>
            <p class="text-xs text-red-200/70 mt-0.5">Sube una nueva versión corregida.</p>
            <p v-if="submission.video.error_message" class="text-sm text-red-200/80 whitespace-pre-wrap mt-2">{{ submission.video.error_message }}</p>
          </div>
        </div>
      </div>

      <!-- Error al procesar el video (fallo técnico) -->
      <div v-else-if="submission?.video?.status === 'error'" class="mb-4">
        <div class="flex items-start gap-3 bg-yellow-500/10 border border-yellow-500/20 rounded-xl px-4 py-4">
          <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="text-sm font-semibold text-yellow-300 mb-1">Hubo un problema al procesar el video</p>
            <p class="text-xs text-yellow-200/70">El archivo no se guardó correctamente. Por favor vuelve a subir el video.</p>
          </div>
        </div>
      </div>

      <!-- Subir video (también cuando fue rechazado) -->
      <div v-if="canUploadVideo">
        <p class="text-sm text-cgr-muted mb-4">
          Requisitos: MP4 / MOV / WebM · Máx. 2 GB · Máx. 10 min · Mínimo 720p (1280×720) · Proporción 16:9
        </p>
        <p v-if="videoValidationError" class="mb-3 text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
          {{ videoValidationError }}
        </p>

        <!-- Barra de progreso -->
        <div v-if="uploading" class="mb-4">
          <div class="flex justify-between text-xs text-cgr-muted mb-1">
            <span>Subiendo…</span>
            <span>{{ uploadProgress }}%</span>
          </div>
          <div class="w-full bg-cgr-section rounded-full h-2">
            <div
              class="bg-cgr-purple h-2 rounded-full transition-all duration-300"
              :style="{ width: uploadProgress + '%' }"
            ></div>
          </div>
          <p class="text-xs text-cgr-subtle mt-2">No cierres esta página hasta que termine la subida.</p>
        </div>

        <!-- Selector de archivo -->
        <div v-else>
          <input
            type="file"
            accept="video/mp4,video/quicktime,video/x-msvideo,video/webm"
            class="block w-full text-sm text-cgr-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cgr-purple file:text-white cursor-pointer mb-4"
            @change="videoFile = ($event.target as HTMLInputElement).files?.[0] ?? null; videoValidationError = ''"
          />
          <UiButton :disabled="!videoFile || !!videoValidationError" :loading="uploading" @click="uploadVideo">
            Subir videoponencia
          </UiButton>
          <p v-if="videoFile" class="text-xs text-cgr-subtle mt-2">
            Archivo: {{ videoFile.name }} ({{ (videoFile.size / 1024 / 1024).toFixed(1) }} MB)
          </p>
        </div>
      </div>

      <div v-else class="text-cgr-subtle text-sm">
        Disponible tras seleccionar modalidad virtual.
      </div>
    </UiCard>

    <!-- ── Confirmado ── -->
    <UiCard v-if="submission?.status === 'confirmed'" class="p-6 text-center">
      <div class="text-green-400 text-4xl mb-3">🎉</div>
      <p class="text-white font-semibold text-lg mb-1">¡Ponencia confirmada!</p>
      <p class="text-sm text-cgr-muted">
        Tu ponencia ha completado el proceso de revisión y está confirmada para el congreso.
      </p>
    </UiCard>
  </div>
</template>
