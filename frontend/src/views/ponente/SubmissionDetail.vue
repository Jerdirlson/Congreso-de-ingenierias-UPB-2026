<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useFetchApi, getApiToken } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiButton from '../../components/ui/UiButton.vue'
import UiBadge from '../../components/ui/UiBadge.vue'
import UiSteps from '../../components/ui/UiSteps.vue'
import UpbRegistrationOptions from '../../components/UpbRegistrationOptions.vue'

const route = useRoute()
const router = useRouter()
const api = useFetchApi()
const confirmAxisApi = useFetchApi()

const submission = ref<{
  id: number
  title: string
  status: string
  modality: string | null
  journal_opt_in_at?: string | null
  thematic_axis?: { id: number; name: string }
  abstracts?: { id: number; content: string; llm_status: string; llm_axis?: { id: number; name: string }; llm_justification?: string; llm_confidence_score?: number }[]
  documents?: { id: number; original_filename: string; version: number; status: string }[]
  articles?: { id: number; original_filename: string; version: number; status: string }[]
  video?: { id: number; status: string; error_message?: string | null; original_filename?: string | null } | null
  reviews?: { id: number; status: string; decision: string | null; comments: string | null; completed_at: string | null; type?: string; reviewer?: { name: string } }[]
} | null>(null)

const abstractFile = ref<File | null>(null)
const abstractFileError = ref('')
const articleFile = ref<File | null>(null)
const articleFileError = ref('')
const modalityChoice = ref<string>('')
const errorMessage = ref('')
const confirmDelete = ref(false)
const deleting = ref(false)
const videoFile = ref<File | null>(null)
const uploadProgress = ref(0)
const uploading = ref(false)
const videoValidationError = ref('')
let videoPolling: ReturnType<typeof setInterval> | null = null
const llmTimedOut = ref(false)
let llmPolling: ReturnType<typeof setInterval> | null = null
let llmPollCount = 0
const LLM_POLL_MAX = 15 // 15 × 3 s = 45 s antes de rendirse

const axes = ref<{ id: number; name: string; description?: string }[]>([])
const selectedAxisId = ref<number | null>(null)
const showResubmitForm = ref(false)

const deletableStatuses = ['draft', 'abstract_submitted']
const canDelete = computed(() => deletableStatuses.includes(submission.value?.status ?? ''))

// Flujo: Resumen → Modalidad → (Video si virtual) → Inscripción.
// El artículo para revista es un carril OPCIONAL paralelo (no es un paso).
const STEPS = [
  { key: 'abstract', label: 'Resumen' },
  { key: 'modality', label: 'Modalidad' },
  { key: 'payment', label: 'Inscripción' },
]

const MODALITIES = [
  { value: 'presencial_oral',   label: 'Ponencia Oral Presencial' },
  { value: 'presencial_poster', label: 'Ponencia Póster' },
  { value: 'virtual',           label: 'Ponencia Oral Virtual (requiere video)' },
]

const currentStepIndex = computed(() => {
  const s = submission.value?.status
  if (!s) return 0
  if (['draft', 'abstract_submitted', 'abstract_rejected'].includes(s)) return 0
  // Estados del antiguo paso "documento" cuentan como resumen aprobado → elegir modalidad
  if (['abstract_approved', 'under_review', 'revision_requested', 'document_approved'].includes(s)) return 1
  if (['modality_selected', 'video_pending', 'video_ready', 'payment_pending'].includes(s)) return 2
  if (s === 'confirmed') return 3
  return 0
})

const canPay = computed(() => submission.value?.status === 'payment_pending')

const canSubmitAbstract = computed(() => submission.value?.status === 'draft')
const canResubmitAbstract = computed(() => submission.value?.status === 'abstract_rejected')

const abstractRejectionReview = computed(() => {
  const reviews = submission.value?.reviews ?? []
  return reviews
    .filter(r => r.type === 'abstract' && r.status === 'completed' && r.decision === 'rejected')
    .sort((a, b) => new Date(b.completed_at ?? 0).getTime() - new Date(a.completed_at ?? 0).getTime())[0] ?? null
})

const abstractApprovalReview = computed(() => {
  const reviews = submission.value?.reviews ?? []
  return reviews
    .filter(r => r.type === 'abstract' && r.status === 'completed' && r.decision === 'approved' && !!r.comments)
    .sort((a, b) => new Date(b.completed_at ?? 0).getTime() - new Date(a.completed_at ?? 0).getTime())[0] ?? null
})
const canConfirmAxis = computed(() =>
  submission.value?.status === 'abstract_submitted' && !submission.value?.thematic_axis
)
const awaitingAdminApproval = computed(() =>
  submission.value?.status === 'abstract_submitted' && !!submission.value?.thematic_axis
)

// Con el resumen aprobado ya se elige modalidad; los estados del antiguo
// paso "documento" se aceptan por compatibilidad con ponencias a mitad de flujo.
const canSelectModality = computed(() =>
  ['abstract_approved', 'under_review', 'revision_requested', 'document_approved'].includes(submission.value?.status ?? '')
)

// ── Artículo (opcional, publicación en revista) ──
const ARTICLE_STATUSES = [
  'abstract_approved', 'under_review', 'revision_requested', 'document_approved',
  'modality_selected', 'video_pending', 'video_ready', 'payment_pending', 'confirmed',
]
const articleAvailable = computed(() => ARTICLE_STATUSES.includes(submission.value?.status ?? ''))
const journalOptIn = computed(() => !!submission.value?.journal_opt_in_at)

const latestArticle = computed(() => {
  const arts = submission.value?.articles
  return arts?.length ? arts[0] : null
})

const canUploadArticle = computed(() =>
  articleAvailable.value && journalOptIn.value
  && (!latestArticle.value || latestArticle.value.status === 'revision_requested')
)

const articleRevisionReview = computed(() => {
  const reviews = submission.value?.reviews ?? []
  return reviews
    .filter(r => r.type === 'article' && r.status === 'completed' && r.decision === 'rejected')
    .sort((a, b) => new Date(b.completed_at ?? 0).getTime() - new Date(a.completed_at ?? 0).getTime())[0] ?? null
})

const articleApprovalReview = computed(() => {
  const reviews = submission.value?.reviews ?? []
  return reviews
    .filter(r => r.type === 'article' && r.status === 'completed' && r.decision === 'approved' && !!r.comments)
    .sort((a, b) => new Date(b.completed_at ?? 0).getTime() - new Date(a.completed_at ?? 0).getTime())[0] ?? null
})

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
const showLlmSpinner = computed(() => llmClassifying.value && !llmTimedOut.value)

const revisionReview = computed(() => {
  const reviews = submission.value?.reviews ?? []
  return reviews
    .filter(r => r.type === 'document' && r.status === 'completed' && r.decision === 'rejected')
    .sort((a, b) => new Date(b.completed_at ?? 0).getTime() - new Date(a.completed_at ?? 0).getTime())[0] ?? null
})

const documentApprovalReview = computed(() => {
  const reviews = submission.value?.reviews ?? []
  return reviews
    .filter(r => r.type === 'document' && r.status === 'completed' && r.decision === 'approved' && !!r.comments)
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
  if (!abstractFile.value) return
  errorMessage.value = ''
  const form = new FormData()
  form.append('abstract_file', abstractFile.value)

  const data = await api.postForm<{ abstract: { llm_axis?: { id: number } } }>(
    `/submissions/${route.params.id}/abstracts`,
    form,
  )
  if (data) {
    abstractFile.value = null
    abstractFileError.value = ''
    showResubmitForm.value = false
    llmTimedOut.value = false
    const recommendedId = data.abstract?.llm_axis?.id
    if (recommendedId) selectedAxisId.value = recommendedId
    await loadSubmission()
    // Solo hacer polling si realmente sigue pendiente (la IA procesa async)
    // Si ya está rejected/approved, el selector aparece de inmediato
    if (llmClassifying.value) startLlmPolling()
  } else {
    errorMessage.value = api.error.value?.message ?? 'Error al enviar el archivo de resumen'
  }
}

function onAbstractFileChange(event: Event) {
  abstractFileError.value = ''
  const file = (event.target as HTMLInputElement).files?.[0] ?? null
  if (!file) {
    abstractFile.value = null
    return
  }

  const lower = file.name.toLowerCase()
  if (!(lower.endsWith('.docx') || lower.endsWith('.pdf'))) {
    abstractFile.value = null
    abstractFileError.value = 'Solo se permiten archivos .docx o .pdf.'
    return
  }

  if (file.size > 10 * 1024 * 1024) {
    abstractFile.value = null
    abstractFileError.value = 'El archivo no debe superar 10 MB.'
    return
  }

  abstractFile.value = file
}

async function confirmAxis() {
  if (!selectedAxisId.value) return
  errorMessage.value = ''
  const data = await confirmAxisApi.patch<unknown>(
    `/submissions/${route.params.id}/axis`,
    { thematic_axis_id: selectedAxisId.value }
  )
  if (data) {
    showResubmitForm.value = false
    await loadSubmission()
  } else {
    errorMessage.value = confirmAxisApi.error.value?.message ?? 'Error al confirmar el eje temático'
  }
}

async function optInJournal() {
  errorMessage.value = ''
  const data = await api.post<unknown>(`/submissions/${route.params.id}/journal-opt-in`, {})
  if (data) await loadSubmission()
  else errorMessage.value = api.error.value?.message ?? 'Error al registrar tu interés de publicación'
}

async function optOutJournal() {
  errorMessage.value = ''
  const data = await api.delete<unknown>(`/submissions/${route.params.id}/journal-opt-in`)
  if (data) await loadSubmission()
  else errorMessage.value = api.error.value?.message ?? 'Error al retirar la opción de publicación'
}

function onArticleFileChange(event: Event) {
  articleFileError.value = ''
  const file = (event.target as HTMLInputElement).files?.[0] ?? null
  if (!file) {
    articleFile.value = null
    return
  }
  const lower = file.name.toLowerCase()
  if (!(lower.endsWith('.doc') || lower.endsWith('.docx'))) {
    articleFile.value = null
    articleFileError.value = 'El artículo debe subirse en formato Word (.doc o .docx).'
    return
  }
  if (file.size > 10 * 1024 * 1024) {
    articleFile.value = null
    articleFileError.value = 'El archivo no debe superar 10 MB.'
    return
  }
  articleFile.value = file
}

async function submitArticle() {
  if (!articleFile.value) return
  errorMessage.value = ''
  const form = new FormData()
  form.append('file', articleFile.value)
  const data = await api.postForm<unknown>(`/submissions/${route.params.id}/articles`, form)
  if (data) {
    articleFile.value = null
    articleFileError.value = ''
    await loadSubmission()
  } else {
    errorMessage.value = api.error.value?.message ?? 'Error al subir el artículo'
  }
}

async function downloadArticle(articleId: number, filename: string) {
  const token = getApiToken()
  try {
    const res = await fetch(`/api/submissions/${route.params.id}/articles/${articleId}/download`, {
      headers: { Authorization: `Bearer ${token}` },
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

function startLlmPolling() {
  stopLlmPolling()
  llmPollCount = 0
  llmTimedOut.value = false
  llmPolling = setInterval(async () => {
    llmPollCount++
    if (llmPollCount >= LLM_POLL_MAX) {
      stopLlmPolling()
      llmTimedOut.value = true
      return
    }
    await loadSubmission()
    if (!llmClassifying.value) {
      stopLlmPolling()
      const axisId = submission.value?.abstracts?.[0]?.llm_axis?.id
      if (axisId) selectedAxisId.value = axisId
    }
  }, 3000)
}

function stopLlmPolling() {
  if (llmPolling) { clearInterval(llmPolling); llmPolling = null }
}

onMounted(async () => {
  const axisData = await useFetchApi().get<{ data: typeof axes.value } | typeof axes.value>('/thematic-axes')
  if (axisData) axes.value = Array.isArray(axisData) ? axisData : axisData.data
  await loadSubmission()
  if (submission.value?.status === 'abstract_submitted') {
    const axisId = submission.value?.abstracts?.[0]?.llm_axis?.id
    if (axisId) selectedAxisId.value = axisId
    if (llmClassifying.value) startLlmPolling()
  }
})
watch(() => route.params.id, () => {
  stopVideoPolling()
  stopLlmPolling()
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
        <UiBadge v-if="canConfirmAxis" variant="info">Pendiente de confirmación</UiBadge>
        <UiBadge v-else-if="latestAbstract?.llm_status === 'approved' && !canConfirmAxis" variant="success">Eje confirmado</UiBadge>
        <UiBadge v-else-if="showLlmSpinner" variant="info">Clasificando…</UiBadge>
      </h2>

      <!-- Formulario de envío inicial (draft) -->
      <div v-if="canSubmitAbstract">
        <input
          type="file"
          accept=".docx,.pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf"
          class="block w-full text-sm text-cgr-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cgr-purple file:text-white cursor-pointer"
          @change="onAbstractFileChange"
        />
        <p v-if="abstractFileError" class="mt-2 text-xs text-red-400">{{ abstractFileError }}</p>
        <p v-if="abstractFile" class="mt-2 text-xs text-cgr-subtle">
          Archivo seleccionado: {{ abstractFile.name }} ({{ (abstractFile.size / 1024 / 1024).toFixed(2) }} MB)
        </p>
        <UiButton class="mt-4" :loading="api.loading.value" :disabled="!abstractFile || !!abstractFileError" @click="submitAbstract">
          Enviar resumen
        </UiButton>
      </div>

      <!-- Pendiente de confirmación de eje (abstract_submitted) -->
      <div v-else-if="canConfirmAxis">

        <!-- Aviso de timeout: IA no respondió a tiempo -->
        <div v-if="llmTimedOut && !showResubmitForm" class="flex items-start gap-3 bg-amber-500/10 border border-amber-500/30 rounded-lg px-4 py-3 mb-4">
          <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          <p class="text-amber-300 text-sm">La IA tardó demasiado en responder. Puedes seleccionar el eje temático manualmente.</p>
        </div>

        <!-- Recomendación de la IA -->
        <div v-if="!showResubmitForm">
          <div v-if="latestAbstract?.llm_axis" :class="[
            'flex items-start gap-3 rounded-lg px-4 py-3 mb-4',
            latestAbstract.llm_status === 'approved'
              ? 'bg-green-500/10 border border-green-500/30'
              : 'bg-amber-500/10 border border-amber-500/30'
          ]">
            <svg v-if="latestAbstract.llm_status === 'approved'" class="w-4 h-4 text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg v-else class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div>
              <p :class="latestAbstract.llm_status === 'approved' ? 'text-green-400 text-sm font-semibold' : 'text-amber-400 text-sm font-semibold'">
                {{ latestAbstract.llm_status === 'approved' ? 'Alta confianza' : 'Confianza moderada' }} — eje sugerido:
              </p>
              <p class="text-white font-bold mt-0.5">{{ latestAbstract.llm_axis.name }}</p>
              <p v-if="latestAbstract.llm_justification" class="text-xs text-cgr-subtle italic mt-1">
                "{{ latestAbstract.llm_justification }}"
              </p>
            </div>
          </div>

          <div v-else class="flex items-start gap-3 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3 mb-4">
            <svg class="w-4 h-4 text-cgr-muted shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-cgr-muted text-sm">La IA no pudo clasificar tu resumen. Selecciona el eje temático manualmente para continuar.</p>
          </div>

          <!-- Selector de eje -->
          <p class="text-xs text-cgr-muted mb-2">Selecciona el eje temático de tu ponencia:</p>
          <div class="space-y-2 mb-4">
            <label
              v-for="axis in axes"
              :key="axis.id"
              :class="[
                'flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors',
                selectedAxisId === axis.id
                  ? 'border-cgr-purple bg-cgr-purple/10 text-white'
                  : 'border-cgr-border bg-cgr-section text-cgr-muted hover:border-cgr-purple/50'
              ]"
            >
              <input type="radio" :value="axis.id" v-model="selectedAxisId" class="hidden" />
              <span class="flex-1">
                <span class="block text-sm font-medium" :class="selectedAxisId === axis.id ? 'text-white' : 'text-cgr-muted'">
                  {{ axis.name }}
                  <span v-if="latestAbstract?.llm_axis?.id === axis.id" class="ml-2 text-xs font-normal text-cgr-purple">
                    ← IA recomienda
                  </span>
                </span>
                <span v-if="axis.description" class="block text-xs text-cgr-subtle mt-0.5">{{ axis.description }}</span>
              </span>
            </label>
          </div>

          <div class="flex gap-3 flex-wrap">
            <UiButton :disabled="!selectedAxisId" :loading="confirmAxisApi.loading.value" @click="confirmAxis">
              Confirmar eje temático
            </UiButton>
            <UiButton variant="secondary" @click="showResubmitForm = true">
              Cambiar archivo
            </UiButton>
          </div>
        </div>

        <!-- Formulario de reenvío de resumen -->
        <div v-else>
          <p class="text-xs text-cgr-muted mb-3">Sube un nuevo archivo para obtener una nueva recomendación de la IA.</p>
          <input
            type="file"
            accept=".docx,.pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf"
            class="block w-full text-sm text-cgr-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cgr-purple file:text-white cursor-pointer"
            @change="onAbstractFileChange"
          />
          <p v-if="abstractFileError" class="mt-2 text-xs text-red-400">{{ abstractFileError }}</p>
          <p v-if="abstractFile" class="mt-2 text-xs text-cgr-subtle">
            Archivo seleccionado: {{ abstractFile.name }} ({{ (abstractFile.size / 1024 / 1024).toFixed(2) }} MB)
          </p>
          <div class="flex gap-3 mt-4">
            <UiButton :loading="api.loading.value" :disabled="!abstractFile || !!abstractFileError" @click="submitAbstract">
              Enviar nuevo resumen
            </UiButton>
            <UiButton variant="secondary" @click="showResubmitForm = false">
              Cancelar
            </UiButton>
          </div>
        </div>
      </div>

      <!-- Eje confirmado, esperando revisión del comité (abstract_submitted + axis set) -->
      <div v-else-if="awaitingAdminApproval" class="flex items-start gap-3 bg-cgr-section border border-cgr-border rounded-lg px-4 py-4">
        <svg class="w-4 h-4 text-cgr-purple shrink-0 mt-0.5 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="text-sm space-y-1">
          <p class="text-white font-medium">Resumen enviado al comité para revisión</p>
          <p class="text-cgr-muted text-xs">
            Eje confirmado: <span class="text-cgr-purple font-medium">{{ submission?.thematic_axis?.name }}</span>
          </p>
          <p class="text-cgr-muted text-xs leading-relaxed">
            El comité revisará tu resumen. Una vez aprobado, podrás continuar eligiendo la modalidad de tu presentación.
          </p>
        </div>
      </div>

      <!-- Resumen pendiente de ajustes -->
      <div v-else-if="canResubmitAbstract">
        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl px-4 py-4 mb-4">
          <div class="flex gap-3 items-start mb-3">
            <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
              <p class="text-sm font-semibold text-yellow-300">El comité solicitó ajustes en tu resumen</p>
              <p class="text-xs text-yellow-200/70 mt-0.5">Revisa los comentarios y sube una nueva versión corregida.</p>
            </div>
          </div>
          <div v-if="abstractRejectionReview?.comments" class="bg-yellow-500/10 border border-yellow-400/20 rounded-lg px-3 py-3 text-sm text-yellow-100 whitespace-pre-wrap leading-relaxed">
            {{ abstractRejectionReview.comments }}
          </div>
          <p v-else class="text-xs text-yellow-200/60">Sin comentarios adicionales del revisor.</p>
        </div>

        <p class="text-xs text-cgr-muted mb-3">Sube una nueva versión del resumen:</p>
        <input
          type="file"
          accept=".docx,.pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf"
          class="block w-full text-sm text-cgr-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cgr-purple file:text-white cursor-pointer"
          @change="onAbstractFileChange"
        />
        <p v-if="abstractFileError" class="mt-2 text-xs text-red-400">{{ abstractFileError }}</p>
        <p v-if="abstractFile" class="mt-2 text-xs text-cgr-subtle">
          Archivo: {{ abstractFile.name }} ({{ (abstractFile.size / 1024 / 1024).toFixed(2) }} MB)
        </p>
        <UiButton class="mt-4" :loading="api.loading.value" :disabled="!abstractFile || !!abstractFileError" @click="submitAbstract">
          Reenviar resumen
        </UiButton>
      </div>

      <!-- Eje confirmado (abstract_approved y más allá) -->
      <div v-else-if="latestAbstract" class="space-y-3">
        <!-- Comentarios del revisor cuando el resumen fue aprobado -->
        <div v-if="abstractApprovalReview" class="bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-4">
          <div class="flex gap-3 items-start mb-3">
            <svg class="w-4 h-4 text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
              <p class="text-sm font-semibold text-green-300">Comentarios del comité científico</p>
              <p class="text-xs text-green-200/70 mt-0.5">
                Revisor: {{ abstractApprovalReview.reviewer?.name ?? 'Comité científico' }}
              </p>
            </div>
          </div>
          <div class="bg-green-500/10 border border-green-400/20 rounded-lg px-3 py-3 text-sm text-green-100 whitespace-pre-wrap leading-relaxed">
            {{ abstractApprovalReview.comments }}
          </div>
        </div>

        <div class="text-sm text-cgr-muted space-y-1">
          <p>Resumen enviado correctamente.</p>
          <p v-if="submission?.thematic_axis" class="text-cgr-purple">
            Eje temático: <strong>{{ submission.thematic_axis.name }}</strong>
          </p>
        </div>
      </div>
    </UiCard>

    <!-- ── Documento del flujo anterior (solo histórico/lectura) ── -->
    <UiCard v-if="latestDocument" class="p-6 mb-4">
      <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
        Documento enviado
        <UiBadge v-if="latestDocument.status === 'approved'" variant="success">Aprobado</UiBadge>
        <UiBadge v-else-if="latestDocument.status === 'revision_requested'" variant="warning">Pendiente de ajustes</UiBadge>
        <UiBadge v-else-if="latestDocument.status === 'under_review'" variant="info">En revisión</UiBadge>
      </h2>

      <div class="space-y-3">
        <!-- Ajustes solicitados sobre el documento del flujo anterior -->
        <div v-if="latestDocument.status === 'revision_requested'" class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg px-4 py-4">
          <div class="flex gap-3 items-start mb-3">
            <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
              <p class="text-sm font-semibold text-yellow-300">El comité solicitó ajustes en este documento</p>
              <p class="text-xs text-yellow-200/70 mt-0.5">
                Revisor: {{ revisionReview?.reviewer?.name ?? 'Comité científico' }}
              </p>
            </div>
          </div>
          <div v-if="revisionReview?.comments" class="bg-yellow-500/10 border border-yellow-400/20 rounded-lg px-3 py-3 text-sm text-yellow-100 whitespace-pre-wrap leading-relaxed">
            {{ revisionReview.comments }}
          </div>
          <p v-else class="text-xs text-yellow-200/60">Sin comentarios adicionales del revisor.</p>
          <p class="text-xs text-yellow-200/60 mt-3">
            Sube la versión corregida en la sección <strong class="text-yellow-100">Publicación en revista científica</strong> (más abajo).
          </p>
        </div>

        <!-- Comentarios del revisor cuando el documento fue aprobado -->
        <div v-if="latestDocument.status === 'approved' && documentApprovalReview" class="bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-4">
          <div class="flex gap-3 items-start mb-3">
            <svg class="w-4 h-4 text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
              <p class="text-sm font-semibold text-green-300">Comentarios del comité científico</p>
              <p class="text-xs text-green-200/70 mt-0.5">
                Revisor: {{ documentApprovalReview.reviewer?.name ?? 'Comité científico' }}
              </p>
            </div>
          </div>
          <div class="bg-green-500/10 border border-green-400/20 rounded-lg px-3 py-3 text-sm text-green-100 whitespace-pre-wrap leading-relaxed">
            {{ documentApprovalReview.comments }}
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
    </UiCard>

    <!-- ── Paso 2: Modalidad ── -->
    <UiCard class="p-6 mb-4">
      <h2 class="font-semibold text-white mb-4">2. Modalidad de presentación</h2>

      <div v-if="canSelectModality">
        <div class="flex items-start gap-2 bg-amber-500/10 border border-amber-500/30 rounded-lg px-4 py-3 mb-4 text-amber-300 text-sm">
          <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
          <span>La presentación en el congreso <strong>no implica publicación automática en la revista</strong>. La publicación es un proceso independiente y opcional.</span>
        </div>
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

    <!-- ── Artículo (opcional): publicación en revista científica ── -->
    <UiCard v-if="articleAvailable" class="p-6 mb-4 border-cgr-purple/40">
      <h2 class="font-semibold text-white mb-1 flex items-center gap-2 flex-wrap">
        <svg class="w-5 h-5 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        Publicación en revista científica
        <span class="text-[10px] font-medium text-cgr-muted border border-cgr-border bg-cgr-section rounded-full px-2 py-0.5">Opcional</span>
        <UiBadge v-if="latestArticle?.status === 'approved'" variant="success">Artículo aprobado</UiBadge>
        <UiBadge v-else-if="latestArticle?.status === 'revision_requested'" variant="warning">Pendiente de ajustes</UiBadge>
        <UiBadge v-else-if="latestArticle?.status === 'under_review'" variant="info">En revisión</UiBadge>
        <UiBadge v-else-if="latestArticle?.status === 'pending_review'" variant="info">Enviado</UiBadge>
      </h2>
      <p class="text-xs text-cgr-subtle mb-4">Este proceso es independiente: no afecta tu participación en el congreso.</p>

      <!-- Invitación (sin opt-in) -->
      <div v-if="!journalOptIn">
        <div class="flex gap-3 items-start bg-cgr-purple/10 border border-cgr-purple/30 rounded-lg px-4 py-4 mb-4">
          <svg class="w-5 h-5 text-cgr-purple shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
          <div>
            <p class="text-sm font-semibold text-white">¿Quieres que tu trabajo tenga la posibilidad de ser publicado en una revista científica?</p>
            <p class="text-xs text-cgr-muted mt-1 leading-relaxed">
              Si te interesa, podrás subir tu artículo completo en formato Word para que el comité lo revise y sea considerado para publicación.
            </p>
          </div>
        </div>
        <UiButton :loading="api.loading.value" @click="optInJournal">
          Sí, quiero que sea considerado para publicación
        </UiButton>
      </div>

      <!-- Con opt-in -->
      <div v-else class="space-y-4">
        <!-- Ajustes solicitados al artículo -->
        <div v-if="latestArticle?.status === 'revision_requested'" class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg px-4 py-4">
          <div class="flex gap-3 items-start mb-3">
            <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
              <p class="text-sm font-semibold text-yellow-300">El comité solicitó ajustes en tu artículo</p>
              <p class="text-xs text-yellow-200/70 mt-0.5">
                Revisor: {{ articleRevisionReview?.reviewer?.name ?? 'Comité científico' }}
              </p>
            </div>
          </div>
          <div v-if="articleRevisionReview?.comments" class="bg-yellow-500/10 border border-yellow-400/20 rounded-lg px-3 py-3 text-sm text-yellow-100 whitespace-pre-wrap leading-relaxed">
            {{ articleRevisionReview.comments }}
          </div>
          <p v-else class="text-xs text-yellow-200/60">Sin comentarios adicionales del revisor.</p>
          <p class="text-xs text-yellow-200/60 mt-3">Realiza los cambios indicados y sube la versión corregida.</p>
        </div>

        <!-- Comentarios cuando el artículo fue aprobado -->
        <div v-if="latestArticle?.status === 'approved' && articleApprovalReview" class="bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-4">
          <div class="flex gap-3 items-start mb-3">
            <svg class="w-4 h-4 text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
              <p class="text-sm font-semibold text-green-300">Comentarios del comité científico</p>
              <p class="text-xs text-green-200/70 mt-0.5">
                Revisor: {{ articleApprovalReview.reviewer?.name ?? 'Comité científico' }}
              </p>
            </div>
          </div>
          <div class="bg-green-500/10 border border-green-400/20 rounded-lg px-3 py-3 text-sm text-green-100 whitespace-pre-wrap leading-relaxed">
            {{ articleApprovalReview.comments }}
          </div>
        </div>

        <!-- En espera de revisión -->
        <div v-if="latestArticle && ['pending_review', 'under_review'].includes(latestArticle.status)" class="flex gap-3 items-start bg-blue-500/10 border border-blue-500/20 rounded-xl px-4 py-3">
          <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="text-sm font-semibold text-blue-300">
              {{ latestArticle.status === 'under_review' ? 'Artículo en revisión' : 'Artículo enviado — en espera de revisión' }}
            </p>
            <p class="text-xs text-blue-200/70 mt-1 leading-relaxed">El comité científico revisará tu artículo y te responderá con la aprobación o los ajustes necesarios.</p>
          </div>
        </div>

        <!-- Archivo actual -->
        <div v-if="latestArticle" class="flex items-center justify-between gap-4 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3">
          <div class="flex items-center gap-3 min-w-0">
            <svg class="w-5 h-5 text-blue-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/></svg>
            <div class="min-w-0">
              <p class="text-sm text-white truncate">{{ latestArticle.original_filename }}</p>
              <p class="text-xs text-cgr-subtle">Versión {{ latestArticle.version }}</p>
            </div>
          </div>
          <button
            class="flex items-center gap-1.5 text-xs font-medium text-cgr-purple hover:text-cgr-accent border border-cgr-purple/30 hover:border-cgr-purple/60 rounded-lg px-3 py-1.5 transition-colors shrink-0"
            @click="downloadArticle(latestArticle.id, latestArticle.original_filename)"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Descargar
          </button>
        </div>

        <!-- Subir / resubir artículo -->
        <div v-if="canUploadArticle">
          <p class="text-xs text-cgr-muted mb-2">
            {{ latestArticle ? 'Sube la versión corregida de tu artículo (Word .doc o .docx, máx. 10 MB):' : 'Sube tu artículo completo en formato Word (.doc o .docx, máx. 10 MB):' }}
          </p>
          <input
            type="file"
            accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            class="block w-full text-sm text-cgr-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cgr-purple file:text-white cursor-pointer"
            @change="onArticleFileChange"
          />
          <p v-if="articleFileError" class="mt-2 text-xs text-red-400">{{ articleFileError }}</p>
          <p v-if="articleFile" class="mt-2 text-xs text-cgr-subtle">
            Archivo: {{ articleFile.name }} ({{ (articleFile.size / 1024 / 1024).toFixed(2) }} MB)
          </p>
          <div class="flex items-center gap-4 mt-4 flex-wrap">
            <UiButton :disabled="!articleFile || !!articleFileError" :loading="api.loading.value" @click="submitArticle">
              {{ latestArticle ? 'Subir versión corregida' : 'Subir artículo' }}
            </UiButton>
            <button
              v-if="!latestArticle"
              class="text-xs text-cgr-muted hover:text-white transition-colors"
              @click="optOutJournal"
            >
              Ya no me interesa publicar
            </button>
          </div>
        </div>
      </div>
    </UiCard>

    <!-- ── Paso 3 (solo virtual): Video ── -->
    <UiCard v-if="isVirtual || canUploadVideo || submission?.video" class="p-6 mb-4">
      <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
        3. Videoponencia
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

    <!-- ── Paso 4: Inscripción y pago (portal UPB) ── -->
    <UiCard v-if="canPay || submission?.status === 'confirmed'" class="p-6 mb-4">
      <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
        4. Inscripción y pago
        <UiBadge v-if="submission?.status === 'confirmed'" variant="success">Completado</UiBadge>
        <UiBadge v-else variant="warning">Pendiente</UiBadge>
      </h2>

      <div v-if="canPay" class="flex items-start gap-3 bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-4 mb-5">
        <svg class="w-5 h-5 text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
          <p class="text-sm font-semibold text-green-300">Tu ponencia fue aprobada</p>
          <p class="text-xs text-green-200/80 mt-1">
            Solo falta completar la inscripción y el pago en el portal oficial de la UPB.
          </p>
        </div>
      </div>

      <UpbRegistrationOptions audience="ponente" @confirmed="loadSubmission" />
    </UiCard>
  </div>
</template>
