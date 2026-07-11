<script setup lang="ts">
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
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
interface Article { id: number; original_filename: string; version: number; status: string; submitted_at: string }
interface Review {
  id: number; status: string; decision: string | null; type?: string; comments?: string | null
  submission_article_id?: number | null
  reviewer?: { id: number; name: string }
  assignedBy?: { name: string }
  assigned_at: string | null; completed_at: string | null
}
interface Submission {
  id: number; title: string; status: string; updated_at: string
  journal_opt_in_at?: string | null
  user?: { id: number; name: string; email: string; institution?: string; country?: string }
  thematic_axis?: { id: number; name: string }
  abstracts?: { id: number; content: string; version: number; llm_status: string; original_filename?: string | null; stored_path?: string | null }[]
  documents?: Document[]
  articles?: Article[]
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

const assignAbstractModalOpen = ref(false)
const selectedAbstractReviewerId = ref<number | null>(null)
const assignAbstractError = ref('')
const assigningAbstract = ref(false)

const assignArticleModalOpen = ref(false)
const selectedArticleReviewerId = ref<number | null>(null)
const selectedArticleId = ref<number | null>(null)
const assignArticleError = ref('')
const assigningArticle = ref(false)
const downloadingArticle = ref<number | null>(null)
const downloading = ref<number | null>(null)
const downloadingVideo = ref(false)
const rejectingVideo = ref(false)
const showRejectVideoModal = ref(false)
const videoRejectReason = ref('')
const videoRejectError = ref('')

const removeReviewModalOpen = ref(false)
const reviewToRemove = ref<Review | null>(null)
const removingReview = ref(false)
const removeReviewError = ref('')

const statusLabels: Record<string, string> = {
  draft: 'Borrador', abstract_submitted: 'Resumen enviado', abstract_rejected: 'Pendiente de ajustes',
  abstract_approved: 'Resumen aprobado', under_review: 'En revisión', revision_requested: 'Pendiente de ajustes',
  document_approved: 'Documento aprobado', modality_selected: 'Modalidad elegida',
  video_pending: 'Video pendiente', video_ready: 'Video listo', payment_pending: 'Pago pendiente', confirmed: 'Confirmado',
}
const statusVariants: Record<string, 'default' | 'warning' | 'danger' | 'success' | 'info' | 'purple'> = {
  draft: 'default', abstract_submitted: 'info', abstract_rejected: 'warning', abstract_approved: 'success',
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

function slugify(text: string) {
  return text
    .normalize('NFD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-zA-Z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 80) || 'resumen'
}

const downloadingAbstractFile = ref(false)

/** Descarga el Word/PDF original del resumen (solo existe para resúmenes subidos desde jul 2026) */
async function downloadAbstractOriginal() {
  const abs = submission.value?.abstracts?.[0]
  if (!abs?.stored_path) return
  downloadingAbstractFile.value = true
  try {
    const blob = await fetchFileBlob(`/admin/submissions/${route.params.id}/abstracts/${abs.id}/download`)
    if (!blob) return
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url; a.download = abs.original_filename ?? 'resumen'
    document.body.appendChild(a); a.click()
    document.body.removeChild(a); URL.revokeObjectURL(url)
  } finally { downloadingAbstractFile.value = false }
}

function downloadAbstract() {
  const content = submission.value?.abstracts?.[0]?.content
  if (!content) return
  const title = submission.value?.title ?? 'resumen'
  const blob = new Blob([content], { type: 'text/plain;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `Resumen-${slugify(title)}.txt`
  document.body.appendChild(a); a.click()
  document.body.removeChild(a); URL.revokeObjectURL(url)
}

async function downloadDoc(doc: Document) {
  downloading.value = doc.id
  const token = getApiToken()
  try {
    const res = await fetch(`/api/admin/submissions/${route.params.id}/documents/${doc.id}/download`, {
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

// ── Vista previa (ponencia PDF / artículo Word) ──
const previewDocId = ref<number | null>(null)
const previewDocUrl = ref<string | null>(null)
const loadingPreviewDoc = ref<number | null>(null)

const previewArticleId = ref<number | null>(null)
const loadingPreviewArticle = ref<number | null>(null)
const articlePreviewContainer = ref<HTMLElement | null>(null)
const previewArticleError = ref('')

async function fetchFileBlob(path: string): Promise<Blob | null> {
  const token = getApiToken()
  const res = await fetch(`/api${path}`, { headers: { Authorization: `Bearer ${token}` } })
  if (!res.ok) return null
  return await res.blob()
}

function closeDocPreview() {
  if (previewDocUrl.value) URL.revokeObjectURL(previewDocUrl.value)
  previewDocUrl.value = null
  previewDocId.value = null
}

async function toggleDocPreview(doc: Document) {
  if (previewDocId.value === doc.id) { closeDocPreview(); return }
  closeDocPreview()
  loadingPreviewDoc.value = doc.id
  try {
    const blob = await fetchFileBlob(`/admin/submissions/${route.params.id}/documents/${doc.id}/download`)
    if (!blob) return
    previewDocUrl.value = URL.createObjectURL(new Blob([blob], { type: 'application/pdf' }))
    previewDocId.value = doc.id
  } finally { loadingPreviewDoc.value = null }
}

function closeArticlePreview() {
  previewArticleId.value = null
  previewArticleError.value = ''
  if (articlePreviewContainer.value) articlePreviewContainer.value.innerHTML = ''
}

async function toggleArticlePreview(art: Article) {
  if (previewArticleId.value === art.id) { closeArticlePreview(); return }
  closeArticlePreview()
  loadingPreviewArticle.value = art.id
  try {
    const blob = await fetchFileBlob(`/admin/submissions/${route.params.id}/articles/${art.id}/download`)
    if (!blob) { previewArticleError.value = 'No se pudo cargar el artículo.'; return }
    previewArticleId.value = art.id
    await nextTick()
    const { renderAsync } = await import('docx-preview')
    if (articlePreviewContainer.value) {
      articlePreviewContainer.value.innerHTML = ''
      await renderAsync(await blob.arrayBuffer(), articlePreviewContainer.value)
    }
  } catch {
    previewArticleError.value = 'No se pudo previsualizar este archivo (los .doc antiguos no se pueden renderizar). Descárgalo para verlo.'
    previewArticleId.value = null
  } finally { loadingPreviewArticle.value = null }
}

onUnmounted(closeDocPreview)

function isAlreadyAssignedToDoc(reviewerId: number) {
  return submission.value?.reviews?.some(r => r.reviewer?.id === reviewerId && r.type !== 'abstract' && r.type !== 'article') ?? false
}

function isAlreadyAssignedToArticle(reviewerId: number) {
  return submission.value?.reviews?.some(
    r => r.reviewer?.id === reviewerId && r.type === 'article' && r.submission_article_id === selectedArticleId.value
  ) ?? false
}

function openAssignArticleModal(articleId: number) {
  selectedArticleReviewerId.value = null
  selectedArticleId.value = articleId
  assignArticleError.value = ''
  assignArticleModalOpen.value = true
}

async function assignArticleReviewer() {
  if (!selectedArticleReviewerId.value || !selectedArticleId.value) {
    assignArticleError.value = 'Selecciona un revisor.'
    return
  }
  assigningArticle.value = true
  assignArticleError.value = ''
  const a = useFetchApi()
  const data = await a.post<unknown>(`/admin/submissions/${route.params.id}/assign-article-reviewer`, {
    reviewer_id: selectedArticleReviewerId.value,
    article_id: selectedArticleId.value,
  })
  assigningArticle.value = false
  if (data) {
    assignArticleModalOpen.value = false
    await load()
  } else {
    assignArticleError.value = a.error.value?.message ?? 'Error al asignar el revisor.'
  }
}

async function downloadArticleFile(art: Article) {
  downloadingArticle.value = art.id
  const token = getApiToken()
  try {
    const res = await fetch(`/api/admin/submissions/${route.params.id}/articles/${art.id}/download`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    if (!res.ok) { downloadingArticle.value = null; return }
    const blob = await res.blob()
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url; a.download = art.original_filename
    document.body.appendChild(a); a.click()
    document.body.removeChild(a); URL.revokeObjectURL(url)
  } finally { downloadingArticle.value = null }
}

function isAlreadyAssignedToAbstract(reviewerId: number) {
  return submission.value?.reviews?.some(r => r.reviewer?.id === reviewerId && r.type === 'abstract') ?? false
}

function openAssignAbstractModal() {
  selectedAbstractReviewerId.value = null
  assignAbstractError.value = ''
  assignAbstractModalOpen.value = true
}

async function assignAbstractReviewer() {
  if (!selectedAbstractReviewerId.value) {
    assignAbstractError.value = 'Selecciona un revisor.'
    return
  }
  assigningAbstract.value = true
  assignAbstractError.value = ''
  const a = useFetchApi()
  const data = await a.post<unknown>(`/admin/submissions/${route.params.id}/assign-abstract-reviewer`, {
    reviewer_id: selectedAbstractReviewerId.value,
  })
  assigningAbstract.value = false
  if (data) {
    assignAbstractModalOpen.value = false
    await load()
  } else {
    assignAbstractError.value = a.error.value?.message ?? 'Error al asignar el revisor.'
  }
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

function openRemoveReviewModal(rev: Review) {
  reviewToRemove.value = rev
  removeReviewError.value = ''
  removeReviewModalOpen.value = true
}

async function removeReview() {
  if (!reviewToRemove.value) return
  removingReview.value = true
  removeReviewError.value = ''
  const a = useFetchApi()
  const data = await a.delete<{ ok: boolean }>(
    `/admin/submissions/${route.params.id}/reviews/${reviewToRemove.value.id}`
  )
  removingReview.value = false
  if (data) {
    removeReviewModalOpen.value = false
    reviewToRemove.value = null
    await load()
  } else {
    removeReviewError.value = a.error.value?.message ?? 'Error al quitar el revisor.'
  }
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
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-xs font-semibold text-cgr-muted uppercase tracking-wide">Resumen</h2>
        <div class="flex items-center gap-2">
          <UiButton
            v-if="submission.abstracts[0]?.stored_path"
            size="sm"
            variant="secondary"
            :loading="downloadingAbstractFile"
            @click="downloadAbstractOriginal"
          >
            Descargar original
          </UiButton>
          <UiButton size="sm" variant="secondary" @click="downloadAbstract">
            {{ submission.abstracts[0]?.stored_path ? 'Descargar texto' : 'Descargar' }}
          </UiButton>
          <UiButton
            v-if="submission.status === 'abstract_submitted'"
            size="sm"
            @click="openAssignAbstractModal"
          >
            + Asignar revisor
          </UiButton>
        </div>
      </div>
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
            <UiButton size="sm" variant="secondary" :loading="loadingPreviewDoc === doc.id" @click="toggleDocPreview(doc)">
              {{ previewDocId === doc.id ? 'Ocultar' : 'Vista previa' }}
            </UiButton>
            <UiButton size="sm" variant="secondary" :loading="downloading === doc.id" @click="downloadDoc(doc)">
              Descargar
            </UiButton>
          </div>
        </div>
      </div>
      <div v-if="previewDocUrl" class="mt-3">
        <iframe
          :src="previewDocUrl"
          title="Vista previa del documento de la ponencia"
          class="w-full h-[75vh] rounded-lg border border-cgr-border bg-white"
        />
      </div>
    </UiCard>

    <!-- Artículo para revista científica -->
    <UiCard v-if="submission?.journal_opt_in_at || submission?.articles?.length" class="p-5 mb-4 border-cgr-purple/40">
      <div class="flex items-center justify-between mb-3 gap-2 flex-wrap">
        <h2 class="text-xs font-semibold text-cgr-muted uppercase tracking-wide">Artículo — revista científica</h2>
        <span class="text-[10px] font-medium text-cgr-purple border border-cgr-purple/30 bg-cgr-purple/10 rounded-full px-2 py-0.5">
          Quiere publicar{{ submission?.journal_opt_in_at ? ' · desde ' + formatDate(submission.journal_opt_in_at) : '' }}
        </span>
      </div>

      <div v-if="submission?.articles?.length" class="space-y-2">
        <div
          v-for="art in submission.articles"
          :key="art.id"
          class="flex items-center justify-between gap-4 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3"
        >
          <div class="flex items-center gap-3 min-w-0">
            <svg class="w-4 h-4 text-blue-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
            </svg>
            <div class="min-w-0">
              <p class="text-sm text-white truncate">{{ art.original_filename }}</p>
              <p class="text-xs text-cgr-subtle">Versión {{ art.version }} · Word · {{ formatDate(art.submitted_at) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3 shrink-0">
            <UiBadge :variant="art.status === 'approved' ? 'success' : art.status === 'revision_requested' ? 'warning' : 'info'">
              {{ docStatusLabels[art.status] ?? art.status }}
            </UiBadge>
            <UiButton size="sm" variant="secondary" :loading="loadingPreviewArticle === art.id" @click="toggleArticlePreview(art)">
              {{ previewArticleId === art.id ? 'Ocultar' : 'Vista previa' }}
            </UiButton>
            <UiButton size="sm" variant="secondary" :loading="downloadingArticle === art.id" @click="downloadArticleFile(art)">
              Descargar
            </UiButton>
            <UiButton
              v-if="['pending_review', 'under_review'].includes(art.status)"
              size="sm"
              @click="openAssignArticleModal(art.id)"
            >
              + Asignar revisor
            </UiButton>
          </div>
        </div>
      </div>
      <p v-else class="text-sm text-cgr-muted py-2">
        El ponente marcó que quiere publicar en revista, pero aún no ha subido su artículo.
      </p>
      <p v-if="previewArticleError" class="mt-2 text-xs text-red-400">{{ previewArticleError }}</p>
      <div v-show="previewArticleId !== null" class="mt-3">
        <div
          ref="articlePreviewContainer"
          class="w-full max-h-[75vh] overflow-auto rounded-lg border border-cgr-border bg-white"
        />
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
          class="flex items-start justify-between gap-4 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-7 h-7 rounded-full bg-cgr-purple/20 flex items-center justify-center shrink-0">
              <span class="text-xs font-bold text-cgr-purple">{{ rev.reviewer?.name?.charAt(0) ?? '?' }}</span>
            </div>
            <div>
              <div class="flex items-center gap-2 mb-0.5">
                <p class="text-sm text-white font-medium">{{ rev.reviewer?.name ?? 'Revisor #' + rev.id }}</p>
                <span
                  :class="['text-[10px] font-medium rounded-full px-2 py-0.5 border', rev.type === 'abstract' ? 'text-cgr-purple border-cgr-purple/30 bg-cgr-purple/10' : rev.type === 'article' ? 'text-blue-300 border-blue-500/30 bg-blue-500/10' : 'text-cgr-muted border-cgr-border bg-cgr-section']"
                >
                  {{ rev.type === 'abstract' ? 'Resumen' : rev.type === 'article' ? 'Artículo' : 'Documento' }}
                </span>
              </div>
              <p class="text-xs text-cgr-subtle">Asignado {{ formatDate(rev.assigned_at) }}</p>
              <p v-if="rev.comments && rev.decision === 'rejected'" class="text-xs text-amber-300/70 mt-1 line-clamp-2">
                "{{ rev.comments }}"
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <UiBadge :variant="reviewStatusVariants[rev.status]">
              {{ reviewStatusLabels[rev.status] }}
            </UiBadge>
            <UiBadge v-if="rev.decision" :variant="rev.decision === 'approved' ? 'success' : 'warning'">
              {{ rev.decision === 'approved' ? 'Aprobada' : 'Ajustes solicitados' }}
            </UiBadge>
            <button
              type="button"
              class="text-cgr-subtle hover:text-red-400 transition-colors p-1 rounded"
              title="Quitar revisor"
              @click="openRemoveReviewModal(rev)"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div v-else class="py-6 text-center">
        <p class="text-sm text-cgr-muted mb-1">No hay revisores asignados.</p>
        <p class="text-xs text-cgr-subtle">
          Asigna un revisor al resumen cuando el ponente lo haya enviado.
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

    <!-- Modal asignar revisor al documento -->
    <UiModal v-model="assignModalOpen" title="Asignar revisor al documento">
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
              :disabled="isAlreadyAssignedToDoc(r.id)"
            >
              {{ r.name }}{{ isAlreadyAssignedToDoc(r.id) ? ' (ya asignado)' : '' }}
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

    <!-- Modal confirmar quitar revisor -->
    <UiModal v-model="removeReviewModalOpen" title="Quitar revisor">
      <div class="space-y-3">
        <p class="text-sm text-cgr-muted">
          ¿Quitar a <span class="text-white font-medium">{{ reviewToRemove?.reviewer?.name ?? '—' }}</span>
          de la revisión {{ reviewToRemove?.type === 'abstract' ? 'del resumen' : reviewToRemove?.type === 'article' ? 'del artículo' : 'del documento' }}?
        </p>
        <p
          v-if="reviewToRemove?.status === 'completed'"
          class="text-xs text-amber-300 bg-amber-500/10 border border-amber-500/20 rounded-lg px-3 py-2"
        >
          ⚠ Esta revisión ya está completada
          ({{ reviewToRemove?.decision === 'approved' ? 'aprobó' : 'rechazó' }}).
          Si la quitas se perderá el dictamen y sus comentarios.
        </p>
        <p
          v-else-if="reviewToRemove?.status === 'in_progress'"
          class="text-xs text-amber-300 bg-amber-500/10 border border-amber-500/20 rounded-lg px-3 py-2"
        >
          ⚠ El revisor ya empezó a trabajar en esta revisión.
        </p>
        <p v-if="removeReviewError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
          {{ removeReviewError }}
        </p>
      </div>
      <template #footer>
        <UiButton variant="secondary" @click="removeReviewModalOpen = false">Cancelar</UiButton>
        <UiButton variant="danger" :loading="removingReview" @click="removeReview">Quitar</UiButton>
      </template>
    </UiModal>

    <!-- Modal asignar revisor al artículo -->
    <UiModal v-model="assignArticleModalOpen" title="Asignar revisor al artículo">
      <div class="space-y-4">
        <p class="text-xs text-cgr-muted">El revisor podrá descargar el artículo (Word) y emitir su dictamen: aprobarlo para publicación o solicitar ajustes con comentarios.</p>
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-2">Revisor</label>
          <select
            v-model="selectedArticleReviewerId"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-cgr-purple"
          >
            <option :value="null" disabled>Selecciona un revisor...</option>
            <option
              v-for="r in reviewers"
              :key="r.id"
              :value="r.id"
              :disabled="isAlreadyAssignedToArticle(r.id)"
            >
              {{ r.name }}{{ isAlreadyAssignedToArticle(r.id) ? ' (ya asignado)' : '' }}
            </option>
          </select>
        </div>
        <p v-if="assignArticleError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
          {{ assignArticleError }}
        </p>
      </div>
      <template #footer>
        <UiButton variant="secondary" @click="assignArticleModalOpen = false">Cancelar</UiButton>
        <UiButton :loading="assigningArticle" @click="assignArticleReviewer">Asignar</UiButton>
      </template>
    </UiModal>

    <!-- Modal asignar revisor al resumen -->
    <UiModal v-model="assignAbstractModalOpen" title="Asignar revisor al resumen">
      <div class="space-y-4">
        <p class="text-xs text-cgr-muted">El revisor podrá leer el texto del resumen y emitir su dictamen (aprobar o rechazar con comentarios).</p>
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-2">Revisor</label>
          <select
            v-model="selectedAbstractReviewerId"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-cgr-purple"
          >
            <option :value="null" disabled>Selecciona un revisor...</option>
            <option
              v-for="r in reviewers"
              :key="r.id"
              :value="r.id"
              :disabled="isAlreadyAssignedToAbstract(r.id)"
            >
              {{ r.name }}{{ isAlreadyAssignedToAbstract(r.id) ? ' (ya asignado)' : '' }}
            </option>
          </select>
        </div>
        <p v-if="assignAbstractError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
          {{ assignAbstractError }}
        </p>
      </div>
      <template #footer>
        <UiButton variant="secondary" @click="assignAbstractModalOpen = false">Cancelar</UiButton>
        <UiButton :loading="assigningAbstract" @click="assignAbstractReviewer">Asignar</UiButton>
      </template>
    </UiModal>
  </div>
</template>