<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useFetchApi, getApiToken } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiBadge from '../../components/ui/UiBadge.vue'
import UiButton from '../../components/ui/UiButton.vue'
import UiModal from '../../components/ui/UiModal.vue'

const route = useRoute()
const router = useRouter()
const api = useFetchApi()

interface Document { id: number; original_filename: string; version: number; status: string; submitted_at: string }
interface Review {
  id: number; status: string; decision: string | null
  reviewer?: { id: number; name: string }
  assignedBy?: { name: string }
  assigned_at: string | null; completed_at: string | null
}
interface Submission {
  id: number; title: string; status: string; updated_at: string
  user?: { id: number; name: string; email: string; institution?: string; country?: string }
  thematic_axis?: { id: number; name: string }
  abstracts?: { content: string; version: number; llm_status: string }[]
  documents?: Document[]
  reviews?: Review[]
  video?: { id: number; status: string; original_filename?: string; file_size?: number; uploaded_at?: string; error_message?: string | null } | null
}
interface Reviewer { id: number; name: string; email: string }

const submission = ref<Submission | null>(null)
const reviewers = ref<Reviewer[]>([])
const assignModalOpen = ref(false)
const selectedReviewerId = ref<number | null>(null)
const selectedDocumentId = ref<number | null>(null)
const assignError = ref('')
const assigning = ref(false)
const downloading = ref<number | null>(null)
const downloadingVideo = ref(false)
const rejectingVideo = ref(false)
const showRejectVideoModal = ref(false)
const videoRejectReason = ref('')
const videoRejectError = ref('')

const statusLabels: Record<string, string> = {
  draft: 'Borrador', abstract_submitted: 'Resumen enviado', abstract_rejected: 'Resumen rechazado',
  abstract_approved: 'Resumen aprobado', under_review: 'En revisión', revision_requested: 'Revisión solicitada',
  document_approved: 'Documento aprobado', modality_selected: 'Modalidad elegida',
  video_pending: 'Video pendiente', video_ready: 'Video listo', payment_pending: 'Pago pendiente', confirmed: 'Confirmado',
}
const statusVariants: Record<string, 'default' | 'warning' | 'danger' | 'success' | 'info' | 'purple'> = {
  draft: 'default', abstract_submitted: 'info', abstract_rejected: 'danger', abstract_approved: 'success',
  under_review: 'info', revision_requested: 'warning', document_approved: 'success',
  modality_selected: 'purple', video_pending: 'warning', video_ready: 'success', payment_pending: 'warning', confirmed: 'success',
}
const reviewStatusLabels: Record<string, string> = { pending: 'Pendiente', in_progress: 'En progreso', completed: 'Completada' }
const reviewStatusVariants: Record<string, 'warning' | 'info' | 'success'> = { pending: 'warning', in_progress: 'info', completed: 'success' }
const docStatusLabels: Record<string, string> = {
  pending_review: 'Pendiente', under_review: 'En revisión', revision_requested: 'Con correcciones', approved: 'Aprobado'
}

function formatDate(d: string | null) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function load() {
  const [subData, revData] = await Promise.all([
    api.get<Submission>(`/admin/submissions/${route.params.id}`),
    useFetchApi().get<Reviewer[]>('/admin/reviewers'),
  ])
  if (subData) submission.value = subData
  else router.push({ name: 'admin-submissions' })
  if (revData) reviewers.value = revData
}

function openAssignModal() {
  selectedReviewerId.value = null
  selectedDocumentId.value = submission.value?.documents?.[0]?.id ?? null
  assignError.value = ''
  assignModalOpen.value = true
}

async function assignReviewer() {
  if (!selectedReviewerId.value || !selectedDocumentId.value) {
    assignError.value = 'Selecciona un revisor y un documento.'
    return
  }
  assigning.value = true
  assignError.value = ''
  const a = useFetchApi()
  const data = await a.post<unknown>(`/admin/submissions/${route.params.id}/assign-reviewer`, {
    reviewer_id: selectedReviewerId.value,
    document_id: selectedDocumentId.value,
  })
  assigning.value = false
  if (data) {
    assignModalOpen.value = false
    await load()
  } else {
    assignError.value = a.error.value?.message ?? 'Error al asignar el revisor.'
  }
}

async function downloadDoc(doc: Document) {
  downloading.value = doc.id
  const token = getApiToken()
  try {
    const res = await fetch(`/api/submissions/${route.params.id}/documents/${doc.id}/download`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    if (!res.ok) { downloading.value = null; return }
    const blob = await res.blob()
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url; a.download = doc.original_filename
    document.body.appendChild(a); a.click()
    document.body.removeChild(a); URL.revokeObjectURL(url)
  } finally { downloading.value = null }
}

// Check if reviewer already assigned to avoid duplicates
function isAlreadyAssigned(reviewerId: number) {
  return submission.value?.reviews?.some(r => r.reviewer?.id === reviewerId) ?? false
}

function formatFileSize(bytes?: number) {
  if (!bytes) return ''
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB'
  return (bytes / 1024 / 1024).toFixed(1) + ' MB'
}

async function downloadVideo() {
  if (!submission.value?.video) return
  downloadingVideo.value = true
  const token = getApiToken()
  try {
    const res = await fetch(`/api/admin/submissions/${route.params.id}/video/stream`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    if (!res.ok) return
    const blob = await res.blob()
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = submission.value.video.original_filename ?? 'video.mp4'
    document.body.appendChild(a); a.click()
    document.body.removeChild(a); URL.revokeObjectURL(url)
  } finally { downloadingVideo.value = false }
}
async function rejectVideo() {
  if (!videoRejectReason.value.trim()) {
    videoRejectError.value = 'Indica el motivo del rechazo.'
    return
  }
  rejectingVideo.value = true
  videoRejectError.value = ''
  const a = useFetchApi()
  await a.patch(`/admin/submissions/${route.params.id}/video/reject`, { reason: videoRejectReason.value })
  rejectingVideo.value = false
  showRejectVideoModal.value = false
  videoRejectReason.value = ''
  await load()
}

onMounted(load)
</script>

<template>
  <div class="max-w-4xl">
    <!-- Header -->
    <div class="mb-6">
      <RouterLink :to="{ name: 'admin-submissions' }" class="text-sm text-cgr-muted hover:text-white mb-4 inline-block">
        &larr; Volver a ponencias
      </RouterLink>
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <h1 class="text-2xl font-bold text-white leading-snug">
            {{ submission?.title ?? 'Cargando...' }}
          </h1>
          <p v-if="submission?.thematic_axis" class="text-sm text-cgr-purple mt-1">
            {{ submission.thematic_axis.name }}
          </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
          <UiBadge v-if="submission" :variant="statusVariants[submission.status] ?? 'default'">
            {{ statusLabels[submission.status] ?? submission.status }}
          </UiBadge>
          <UiButton
            v-if="submission?.documents?.length"
            size="sm"
            @click="openAssignModal"
          >
            + Asignar revisor
          </UiButton>
        </div>
      </div>
    </div>

    <!-- Info ponente -->
    <UiCard v-if="submission?.user" class="p-5 mb-4">
      <h2 class="text-xs font-semibold text-cgr-muted uppercase tracking-wide mb-3">Ponente</h2>
      <div class="flex flex-wrap gap-x-8 gap-y-2 text-sm">
        <div>
          <p class="text-cgr-subtle text-xs">Nombre</p>
          <p class="text-white font-medium">{{ submission.user.name }}</p>
        </div>
        <div>
          <p class="text-cgr-subtle text-xs">Email</p>
          <p class="text-white">{{ submission.user.email }}</p>
        </div>
        <div v-if="submission.user.institution">
          <p class="text-cgr-subtle text-xs">Institución</p>
          <p class="text-white">{{ submission.user.institution }}</p>
        </div>
        <div v-if="submission.user.country">
          <p class="text-cgr-subtle text-xs">País</p>
          <p class="text-white">{{ submission.user.country }}</p>
        </div>
      </div>
    </UiCard>

    <!-- Resumen -->
    <UiCard v-if="submission?.abstracts?.length" class="p-5 mb-4">
      <h2 class="text-xs font-semibold text-cgr-muted uppercase tracking-wide mb-3">Resumen</h2>
      <div class="bg-cgr-section rounded-lg p-4 text-sm text-cgr-muted leading-relaxed whitespace-pre-wrap max-h-48 overflow-y-auto">
        {{ submission.abstracts[0]?.content }}
      </div>
    </UiCard>

    <!-- Documentos -->
    <UiCard v-if="submission?.documents?.length" class="p-5 mb-4">
      <h2 class="text-xs font-semibold text-cgr-muted uppercase tracking-wide mb-3">Documentos</h2>
      <div class="space-y-2">
        <div
          v-for="doc in submission.documents"
          :key="doc.id"
          class="flex items-center justify-between gap-4 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3"
        >
          <div class="flex items-center gap-3 min-w-0">
            <svg class="w-4 h-4 text-red-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
            </svg>
            <div class="min-w-0">
              <p class="text-sm text-white truncate">{{ doc.original_filename }}</p>
              <p class="text-xs text-cgr-subtle">Versión {{ doc.version }} · {{ formatDate(doc.submitted_at) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3 shrink-0">
            <UiBadge :variant="doc.status === 'approved' ? 'success' : doc.status === 'revision_requested' ? 'warning' : 'info'">
              {{ docStatusLabels[doc.status] ?? doc.status }}
            </UiBadge>
            <UiButton size="sm" variant="secondary" :loading="downloading === doc.id" @click="downloadDoc(doc)">
              Descargar
            </UiButton>
          </div>
        </div>
      </div>
    </UiCard>

    <!-- Revisores asignados -->
    <UiCard class="p-5 mb-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-xs font-semibold text-cgr-muted uppercase tracking-wide">Revisores asignados</h2>
        <span class="text-xs text-cgr-subtle">{{ submission?.reviews?.length ?? 0 }} revisor(es)</span>
      </div>

      <div v-if="submission?.reviews?.length" class="space-y-2">
        <div
          v-for="rev in submission.reviews"
          :key="rev.id"
          class="flex items-center justify-between gap-4 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-7 h-7 rounded-full bg-cgr-purple/20 flex items-center justify-center shrink-0">
              <span class="text-xs font-bold text-cgr-purple">{{ rev.reviewer?.name?.charAt(0) ?? '?' }}</span>
            </div>
            <div>
              <p class="text-sm text-white font-medium">{{ rev.reviewer?.name ?? 'Revisor #' + rev.id }}</p>
              <p class="text-xs text-cgr-subtle">Asignado {{ formatDate(rev.assigned_at) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <UiBadge :variant="reviewStatusVariants[rev.status]">
              {{ reviewStatusLabels[rev.status] }}
            </UiBadge>
            <UiBadge v-if="rev.decision" :variant="rev.decision === 'approved' ? 'success' : 'danger'">
              {{ rev.decision === 'approved' ? 'Aprobada' : 'Rechazada' }}
            </UiBadge>
          </div>
        </div>
      </div>

      <div v-else class="py-6 text-center">
        <p class="text-sm text-cgr-muted mb-1">No hay revisores asignados.</p>
        <p class="text-xs text-cgr-subtle">
          El ponente debe subir un documento antes de poder asignar revisores.
        </p>
      </div>
    </UiCard>

    <!-- Videoponencia -->
    <UiCard v-if="submission?.video" class="p-5 mb-4">
      <h2 class="text-xs font-semibold text-cgr-muted uppercase tracking-wide mb-3">Videoponencia</h2>
      <div class="flex items-center justify-between gap-4 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3 mb-3">
        <div class="flex items-center gap-3 min-w-0">
          <svg class="w-5 h-5 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div class="min-w-0">
            <p class="text-sm text-white truncate">{{ submission.video.original_filename ?? 'video' }}</p>
            <p class="text-xs text-cgr-subtle">
              {{ formatFileSize(submission.video.file_size) }}
              <span v-if="submission.video.uploaded_at"> · Subido {{ formatDate(submission.video.uploaded_at) }}</span>
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <UiBadge :variant="submission.video.status === 'ready' ? 'success' : submission.video.status === 'rejected' ? 'danger' : submission.video.status === 'processing' ? 'info' : 'warning'">
            {{ submission.video.status === 'ready' ? 'Listo' : submission.video.status === 'rejected' ? 'Rechazado' : submission.video.status === 'processing' ? 'Procesando' : submission.video.status === 'pending' ? 'Pendiente' : submission.video.status }}
          </UiBadge>
          <UiButton size="sm" variant="secondary" :loading="downloadingVideo" @click="downloadVideo">
            Descargar
          </UiButton>
        </div>
      </div>
      <p v-if="submission.video.status === 'rejected' && submission.video.error_message" class="text-xs text-red-300 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2 mb-3">
        Motivo del rechazo: {{ submission.video.error_message }}
      </p>
      <!-- La confirmación es automática al subir el video -->
      <p v-if="submission.video.status === 'ready'" class="text-xs text-green-400 mt-1">
        El ponente fue confirmado automáticamente al subir este video.
      </p>
    </UiCard>

    <!-- Modal rechazar video -->
    <UiModal v-model="showRejectVideoModal" title="Rechazar videoponencia">
      <div class="space-y-3">
        <p class="text-sm text-cgr-muted">El ponente deberá subir una nueva versión del video.</p>
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-2">Motivo del rechazo <span class="text-red-400">*</span></label>
          <textarea
            v-model="videoRejectReason"
            rows="3"
            placeholder="Ej: La resolución no cumple con el mínimo requerido (720p), el video supera los 10 minutos..."
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple resize-y"
          />
        </div>
        <p v-if="videoRejectError" class="text-xs text-red-400">{{ videoRejectError }}</p>
      </div>
      <template #footer>
        <UiButton variant="secondary" @click="showRejectVideoModal = false; videoRejectReason = ''; videoRejectError = ''">Cancelar</UiButton>
        <UiButton variant="danger" :loading="rejectingVideo" @click="rejectVideo">Rechazar</UiButton>
      </template>
    </UiModal>

    <!-- Modal asignar revisor -->
    <UiModal v-model="assignModalOpen" title="Asignar revisor">
      <div class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-2">Revisor</label>
          <select
            v-model="selectedReviewerId"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-cgr-purple"
          >
            <option :value="null" disabled>Selecciona un revisor...</option>
            <option
              v-for="r in reviewers"
              :key="r.id"
              :value="r.id"
              :disabled="isAlreadyAssigned(r.id)"
            >
              {{ r.name }}{{ isAlreadyAssigned(r.id) ? ' (ya asignado)' : '' }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-2">Documento a revisar</label>
          <select
            v-model="selectedDocumentId"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-cgr-purple"
          >
            <option v-for="d in submission?.documents" :key="d.id" :value="d.id">
              Versión {{ d.version }} — {{ d.original_filename }}
            </option>
          </select>
        </div>
        <p v-if="assignError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
          {{ assignError }}
        </p>
      </div>
      <template #footer>
        <UiButton variant="secondary" @click="assignModalOpen = false">Cancelar</UiButton>
        <UiButton :loading="assigning" @click="assignReviewer">Asignar</UiButton>
      </template>
    </UiModal>
  </div>
</template>