<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useFetchApi, getApiToken } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiButton from '../../components/ui/UiButton.vue'
import UiBadge from '../../components/ui/UiBadge.vue'
import UiSteps from '../../components/ui/UiSteps.vue'
import UiModal from '../../components/ui/UiModal.vue'
import UpbRegistrationOptions from '../../components/UpbRegistrationOptions.vue'
import { useAuthStore } from '../../stores/auth'
import { useSettingsStore } from '../../stores/settings'

const auth = useAuthStore()
const settings = useSettingsStore()
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
  abstracts?: { id: number; content: string; version?: number; llm_status: string; llm_axis?: { id: number; name: string }; llm_justification?: string; llm_confidence_score?: number }[]
  documents?: { id: number; original_filename: string; version: number; status: string }[]
  articles?: { id: number; original_filename: string; version: number; status: string }[]
  video?: { id: number; status: string; error_message?: string | null; original_filename?: string | null; youtube_url?: string | null } | null
  reviews?: { id: number; status: string; decision: string | null; comments: string | null; completed_at: string | null; type?: string; submission_abstract_id?: number | null; reviewer?: { name: string } }[]
} | null>(null)

const abstractFile = ref<File | null>(null)
const abstractFileError = ref('')
const articleFile = ref<File | null>(null)
const articleFileError = ref('')
const modalityChoice = ref<string>('')
const errorMessage = ref('')
const confirmDelete = ref(false)
const deleting = ref(false)
const youtubeUrl = ref('')
const sendingVideoLink = ref(false)
const videoLinkError = ref('')
// Infografía del comité con el protocolo de grabación (pieza de Comunicaciones).
const protocolOpen = ref(false)
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

// La ponencia queda confirmada al aprobarse; el último paso (inscripción en el
// portal UPB) se mide con el usuario, no con el estado de la ponencia.
// `payment_pending` solo aparece en ponencias antiguas del flujo anterior.
const registrationDone = computed(() => !!auth.user?.external_registration_at)
const isApproved = computed(() =>
  ['confirmed', 'payment_pending'].includes(submission.value?.status ?? '')
)

const currentStepIndex = computed(() => {
  const s = submission.value?.status
  if (!s) return 0
  if (['draft', 'abstract_submitted', 'abstract_rejected'].includes(s)) return 0
  // Estados del antiguo paso "documento" cuentan como resumen aprobado → elegir modalidad
  if (['abstract_approved', 'under_review', 'revision_requested', 'document_approved'].includes(s)) return 1
  if (['modality_selected', 'video_pending', 'video_ready'].includes(s)) return 2
  if (isApproved.value) return registrationDone.value ? 3 : 2
  return 0
})

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
// Dictámenes anteriores del resumen: todo dictamen completado distinto al que
// se muestra como vigente, para que el ponente no pierda de vista las
// correcciones pedidas sobre versiones previas.
const abstractReviewHistory = computed(() => {
  const currentId = abstractRejectionReview.value?.id ?? abstractApprovalReview.value?.id
  const reviews = submission.value?.reviews ?? []
  return reviews
    .filter(r => r.type === 'abstract' && r.status === 'completed' && r.id !== currentId)
    .sort((a, b) => new Date(b.completed_at ?? 0).getTime() - new Date(a.completed_at ?? 0).getTime())
})

function abstractVersionOf(review: { submission_abstract_id?: number | null }): number | null {
  const abs = submission.value?.abstracts?.find(a => a.id === review.submission_abstract_id)
  return abs?.version ?? null
}

function formatDate(value: string | null): string {
  if (!value) return ''
  return new Date(value).toLocaleDateString('es-CO', {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

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

// La ponencia ya llegó al paso del video y todavía no hay un enlace aceptado.
const videoStageReached = computed(() => {
  const s = submission.value?.status
  return (s === 'video_pending' || s === 'video_ready')
    && submission.value?.video?.status !== 'ready'
})

// El envío está en pausa mientras el comité prepara las indicaciones del video.
const videoUploadPaused = computed(() => videoStageReached.value && !settings.videoUploadOpen)
const canUploadVideo = computed(() => videoStageReached.value && settings.videoUploadOpen)

// Ya había subido el archivo antes de que el flujo cambiara al link de YouTube.
const hadUploadedFile = computed(() =>
  !!submission.value?.video?.original_filename && !submission.value?.video?.youtube_url
)

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
  if (!lower.endsWith('.pdf')) {
    abstractFile.value = null
    abstractFileError.value = 'El resumen debe subirse en formato PDF (exporta la plantilla diligenciada a PDF).'
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

// Formas válidas de enlace: watch, youtu.be, embed, live y shorts.
const YOUTUBE_PATTERNS = [
  /youtu\.be\/([A-Za-z0-9_-]{11})/,
  /youtube\.com\/watch\?(?:[^\s]*&)?v=([A-Za-z0-9_-]{11})/,
  /youtube(?:-nocookie)?\.com\/(?:embed|live|shorts|v)\/([A-Za-z0-9_-]{11})/,
]

function youtubeIdFrom(url: string | null | undefined): string | null {
  if (!url) return null
  for (const pattern of YOUTUBE_PATTERNS) {
    const match = pattern.exec(url.trim())
    if (match) return match[1] ?? null
  }
  return null
}

// Previsualización mientras escribe, para que confirme que es el video correcto.
const typedVideoId = computed(() => youtubeIdFrom(youtubeUrl.value))
const savedVideoId = computed(() => youtubeIdFrom(submission.value?.video?.youtube_url))

async function submitVideoLink() {
  videoLinkError.value = ''
  errorMessage.value = ''

  if (!typedVideoId.value) {
    videoLinkError.value = 'El enlace no parece ser de YouTube. Debe verse así: https://www.youtube.com/watch?v=…'
    return
  }

  sendingVideoLink.value = true
  const data = await api.post<unknown>(`/submissions/${route.params.id}/videos`, {
    youtube_url: youtubeUrl.value.trim(),
  })
  sendingVideoLink.value = false

  if (data) {
    youtubeUrl.value = ''
    await loadSubmission()
  } else {
    videoLinkError.value = api.error.value?.message ?? 'No se pudo guardar el enlace. Intenta de nuevo.'
  }
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
  await settings.fetch()
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
          accept=".pdf,application/pdf"
          class="block w-full text-sm text-cgr-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cgr-purple file:text-white cursor-pointer"
          @change="onAbstractFileChange"
        />
        <p v-if="abstractFileError" class="mt-2 text-xs text-red-400">{{ abstractFileError }}</p>
        <p v-if="abstractFile" class="mt-2 text-xs text-cgr-subtle">
          Archivo seleccionado: {{ abstractFile.name }} ({{ (abstractFile.size / 1024 / 1024).toFixed(2) }} MB)
        </p>
        <p class="mt-2 text-xs text-cgr-subtle">
          Descarga la
          <a href="/api/docs/Plantilla_Resumen.docx" class="text-cgr-purple hover:underline">plantilla oficial</a>,
          escribe tu contenido sobre ella (sin quitar el encabezado ni las secciones Resumen, Palabras claves,
          Abstract, Key Words y Referencias) y expórtala a <strong>PDF</strong>; de lo contrario será rechazado.
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
            accept=".pdf,application/pdf"
            class="block w-full text-sm text-cgr-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cgr-purple file:text-white cursor-pointer"
            @change="onAbstractFileChange"
          />
          <p v-if="abstractFileError" class="mt-2 text-xs text-red-400">{{ abstractFileError }}</p>
          <p v-if="abstractFile" class="mt-2 text-xs text-cgr-subtle">
            Archivo seleccionado: {{ abstractFile.name }} ({{ (abstractFile.size / 1024 / 1024).toFixed(2) }} MB)
          </p>
          <p class="mt-2 text-xs text-cgr-subtle">
            Descarga la
            <a href="/api/docs/Plantilla_Resumen.docx" class="text-cgr-purple hover:underline">plantilla oficial</a>,
            escribe tu contenido sobre ella (sin quitar el encabezado ni las secciones Resumen, Palabras claves,
            Abstract, Key Words y Referencias) y expórtala a <strong>PDF</strong>; de lo contrario será rechazado.
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
              <p v-if="abstractRejectionReview?.completed_at" class="text-xs text-yellow-200/60 mt-0.5">
                {{ formatDate(abstractRejectionReview.completed_at) }}
              </p>
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
          accept=".pdf,application/pdf"
          class="block w-full text-sm text-cgr-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cgr-purple file:text-white cursor-pointer"
          @change="onAbstractFileChange"
        />
        <p v-if="abstractFileError" class="mt-2 text-xs text-red-400">{{ abstractFileError }}</p>
        <p v-if="abstractFile" class="mt-2 text-xs text-cgr-subtle">
          Archivo: {{ abstractFile.name }} ({{ (abstractFile.size / 1024 / 1024).toFixed(2) }} MB)
        </p>
        <p class="mt-2 text-xs text-cgr-subtle">
          Descarga la
          <a href="/api/docs/Plantilla_Resumen.docx" class="text-cgr-purple hover:underline">plantilla oficial</a>,
          escribe tu contenido sobre ella (sin quitar el encabezado ni las secciones Resumen, Palabras claves,
          Abstract, Key Words y Referencias) y expórtala a <strong>PDF</strong>; de lo contrario será rechazado.
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

      <!-- Historial de dictámenes anteriores del resumen -->
      <div v-if="abstractReviewHistory.length" class="mt-5 pt-4 border-t border-cgr-border">
        <h3 class="text-xs font-semibold text-cgr-muted uppercase tracking-wide mb-3">Historial de dictámenes</h3>
        <div class="space-y-3">
          <div
            v-for="h in abstractReviewHistory"
            :key="h.id"
            class="bg-cgr-section border border-cgr-border rounded-lg px-4 py-3"
          >
            <div class="flex items-center justify-between gap-3 mb-2">
              <div class="flex items-center gap-2">
                <span class="text-xs text-cgr-subtle">Resumen{{ abstractVersionOf(h) ? ' v' + abstractVersionOf(h) : '' }}</span>
                <UiBadge :variant="h.decision === 'approved' ? 'success' : 'warning'">
                  {{ h.decision === 'approved' ? 'Aprobado' : 'Ajustes solicitados' }}
                </UiBadge>
              </div>
              <span class="text-xs text-cgr-subtle shrink-0">{{ formatDate(h.completed_at) }}</span>
            </div>
            <p v-if="h.comments" class="text-xs text-cgr-muted leading-relaxed whitespace-pre-wrap">
              {{ h.comments }}
            </p>
            <p v-else class="text-xs text-cgr-subtle italic">Sin comentarios.</p>
          </div>
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
              Si te interesa, podrás subir tu artículo completo para que el comité lo revise y sea considerado para publicación.
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
            {{ latestArticle ? 'Sube la versión corregida de tu artículo (cualquier formato, máx. 10 MB):' : 'Sube tu artículo completo (cualquier formato, máx. 10 MB):' }}
          </p>
          <input
            type="file"
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
    <UiCard v-if="isVirtual || canUploadVideo || videoUploadPaused || submission?.video" class="p-6 mb-4">
      <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
        3. Videoponencia
        <UiBadge v-if="submission?.video?.status === 'ready'" variant="success">Lista</UiBadge>
        <UiBadge v-else-if="videoUploadPaused" variant="info">Próximamente</UiBadge>
      </h2>

      <!-- Link recibido -->
      <div v-if="submission?.video?.status === 'ready'">
        <div class="flex items-center gap-3 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3">
          <svg class="w-5 h-5 text-green-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-white">Recibimos tu videoponencia</p>
            <a
              v-if="submission.video?.youtube_url"
              :href="submission.video.youtube_url"
              target="_blank"
              rel="noopener"
              class="text-xs text-cgr-purple hover:underline truncate block"
            >{{ submission.video.youtube_url }}</a>
            <p v-else-if="submission.video?.original_filename" class="text-xs text-cgr-subtle truncate">{{ submission.video.original_filename }}</p>
          </div>
          <span class="ml-auto text-xs text-green-400 shrink-0 font-medium">Listo</span>
        </div>

        <div v-if="savedVideoId" class="mt-4 rounded-lg overflow-hidden border border-cgr-border aspect-video">
          <iframe
            :src="`https://www.youtube.com/embed/${savedVideoId}`"
            class="w-full h-full"
            title="Videoponencia"
            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
          ></iframe>
        </div>

        <p class="text-xs text-cgr-subtle mt-3">
          No borres el video ni cambies su visibilidad hasta después del congreso —
          es el que se transmitirá el día de tu ponencia.
        </p>
      </div>

      <!-- Ya había subido el archivo: ahora necesitamos el link -->
      <div v-if="hadUploadedFile" class="mb-4">
        <div class="flex items-start gap-3 bg-cgr-purple/10 border border-cgr-purple/25 rounded-xl px-4 py-4">
          <svg class="w-5 h-5 text-cgr-purple shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="text-sm font-semibold text-white mb-1">Cambiamos la forma de entregar el video</p>
            <p class="text-sm text-cgr-muted leading-relaxed">
              Ya recibimos el archivo que subiste
              <span v-if="submission?.video?.original_filename" class="text-cgr-subtle">({{ submission.video.original_filename }})</span>,
              pero ahora la videoponencia se transmite desde YouTube. Sube ese mismo video a tu
              canal y comparte el enlace aquí abajo — no tienes que volver a grabar nada.
            </p>
          </div>
        </div>
      </div>

      <!-- Video rechazado por el comité -->
      <div v-if="submission?.video?.status === 'rejected'" class="mb-4">
        <div class="flex items-start gap-3 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-4">
          <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="text-sm font-semibold text-red-300 mb-1">El comité rechazó tu videoponencia</p>
            <p class="text-xs text-red-200/70 mt-0.5">Corrige el video y comparte el nuevo enlace.</p>
            <p v-if="submission.video.error_message" class="text-sm text-red-200/80 whitespace-pre-wrap mt-2">{{ submission.video.error_message }}</p>
          </div>
        </div>
      </div>

      <!-- En pausa: aún no publicamos las indicaciones del video -->
      <div v-if="videoUploadPaused">
        <div class="flex items-start gap-3 bg-cgr-purple/10 border border-cgr-purple/25 rounded-xl px-4 py-4">
          <svg class="w-5 h-5 text-cgr-purple shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="text-sm font-semibold text-white mb-1">Aún no habilitamos el envío del video</p>
            <p class="text-sm text-cgr-muted leading-relaxed">
              Estamos terminando de definir el formato de las videoponencias. En los próximos días
              te enviaremos por correo las <strong class="text-white font-medium">indicaciones</strong>
              de cómo grabar tu video y subirlo a YouTube.
            </p>
            <p class="text-xs text-cgr-subtle mt-2">
              No necesitas hacer nada por ahora — te avisaremos apenas esté disponible.
            </p>
          </div>
        </div>
      </div>

      <!-- Compartir el link de YouTube (también cuando fue rechazado) -->
      <div v-else-if="canUploadVideo">
        <p class="text-sm text-cgr-muted mb-3 leading-relaxed">
          Sube tu videoponencia a YouTube y pega aquí el enlace. Ese es el video que se
          transmitirá el día del congreso, así que no tienes que subir ningún archivo.
        </p>

        <!-- Protocolo de grabación (infografía del comité) -->
        <button
          type="button"
          class="w-full flex items-center gap-3 bg-cgr-purple/10 border border-cgr-purple/30 hover:border-cgr-purple/60 rounded-xl px-4 py-3 mb-4 text-left transition-colors"
          @click="protocolOpen = true"
        >
          <svg class="w-5 h-5 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h8.25a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H4.5A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
          </svg>
          <span class="min-w-0 flex-1">
            <span class="block text-sm font-semibold text-white">Cómo grabar tu videoponencia</span>
            <span class="block text-xs text-cgr-muted">Protocolo paso a paso y requisitos técnicos</span>
          </span>
          <svg class="w-4 h-4 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
          </svg>
        </button>

        <div class="bg-cgr-section border border-cgr-border rounded-xl px-4 py-3 mb-4">
          <p class="text-xs font-semibold text-white mb-2">Antes de pegar el enlace, revisa que:</p>
          <ul class="text-xs text-cgr-muted space-y-1.5 list-disc list-inside leading-relaxed">
            <li>
              La visibilidad del video sea <strong class="text-white font-medium">No listado (Unlisted)</strong>.
              Así solo quien tenga el enlace puede verlo, pero nosotros sí podemos reproducirlo y
              transmitirlo. Si lo dejas en <strong class="text-white font-medium">Privado</strong> no
              podremos verlo.
            </li>
            <li>Permita <strong class="text-white font-medium">insertarse en otras páginas</strong>, sin restricciones ni contraseña.</li>
            <li>Dure <strong class="text-white font-medium">máximo 10 minutos</strong>, en 720p o más (recomendado 1080p) y en proporción 16:9 horizontal.</li>
            <li>No lo borres ni le cambies la visibilidad hasta después del congreso.</li>
          </ul>
        </div>

        <label class="block text-sm text-cgr-muted mb-2" for="youtube-url">Enlace de YouTube</label>
        <input
          id="youtube-url"
          v-model="youtubeUrl"
          type="url"
          inputmode="url"
          placeholder="https://www.youtube.com/watch?v=..."
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white placeholder:text-cgr-subtle focus:outline-none focus:border-cgr-purple mb-3"
          @input="videoLinkError = ''"
        />

        <p v-if="videoLinkError" class="mb-3 text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
          {{ videoLinkError }}
        </p>

        <!-- Previsualización: que confirme que es el video correcto -->
        <div v-if="typedVideoId" class="mb-4">
          <p class="text-xs text-cgr-subtle mb-2">Revisa que sea el video correcto:</p>
          <div class="rounded-lg overflow-hidden border border-cgr-border aspect-video">
            <iframe
              :src="`https://www.youtube.com/embed/${typedVideoId}`"
              class="w-full h-full"
              title="Previsualización de la videoponencia"
              allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
            ></iframe>
          </div>
        </div>

        <UiButton :disabled="!youtubeUrl.trim()" :loading="sendingVideoLink" @click="submitVideoLink">
          Compartir enlace
        </UiButton>
      </div>

      <div v-else-if="!submission?.video" class="text-cgr-subtle text-sm">
        Disponible tras seleccionar modalidad virtual.
      </div>
    </UiCard>

    <!-- ── Paso 4: Inscripción y pago (portal UPB) ── -->
    <UiCard v-if="isApproved" class="p-6 mb-4">
      <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
        4. Inscripción y pago
        <UiBadge v-if="registrationDone" variant="success">Completado</UiBadge>
        <UiBadge v-else variant="warning">Pendiente</UiBadge>
      </h2>

      <div v-if="!registrationDone" class="flex items-start gap-3 bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-4 mb-5">
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

    <!-- Protocolo de grabación de la videoponencia -->
    <UiModal v-model="protocolOpen" size="wide" title="Cómo grabar tu videoponencia">
      <img
        src="/protocolo-video.png"
        alt="Protocolo para grabar la videoponencia: planifica, prepara tu entorno, graba, edita, sube el video a YouTube, configura la visibilidad como No listado y comparte el enlace. Requisitos técnicos: MP4 (también MOV o AVI), mínimo 1280x720 y recomendado 1920x1080, proporción 16:9 horizontal, duración máxima 10 minutos, códec de video H.264, audio AAC estéreo y 30 fotogramas por segundo."
        class="w-full h-auto rounded-lg"
      />
      <template #footer>
        <a
          href="/protocolo-video.png"
          target="_blank"
          rel="noopener"
          class="inline-flex items-center gap-2 border border-cgr-purple/50 text-cgr-purple hover:bg-cgr-purple/10 text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
          </svg>
          Abrir en tamaño completo
        </a>
        <UiButton @click="protocolOpen = false">Entendido</UiButton>
      </template>
    </UiModal>
  </div>
</template>
