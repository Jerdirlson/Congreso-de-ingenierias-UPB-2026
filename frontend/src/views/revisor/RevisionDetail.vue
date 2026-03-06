<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useFetchApi, getApiToken } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiButton from '../../components/ui/UiButton.vue'
import UiBadge from '../../components/ui/UiBadge.vue'

const route = useRoute()
const router = useRouter()
const api = useFetchApi()

interface ReviewDetail {
  id: number
  status: string
  decision: string | null
  comments: string | null
  assigned_at: string | null
  started_at: string | null
  completed_at: string | null
  submission?: {
    id: number
    title: string
    user?: { name: string; email: string; institution?: string; country?: string }
    thematic_axis?: { name: string }
    abstracts?: { content: string; version: number }[]
  }
  submission_document?: { id: number; original_filename: string; version: number } | null
  history?: {
    id: number; status: string; decision: string | null; comments: string | null
    completed_at: string | null
    submission_document?: { version: number; original_filename: string } | null
  }[]
}

const review = ref<ReviewDetail | null>(null)
const decision = ref<string>('approved')
const comments = ref('')
const errorMessage = ref('')
const downloading = ref(false)

const commentsRequired = computed(() => decision.value === 'rejected')

function formatDate(d: string | null) {
  if (!d) return '-'
  return new Date(d).toLocaleString('es-CO', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function loadReview() {
  const data = await api.get<ReviewDetail>(`/reviews/${route.params.id}`)
  if (data) review.value = data
  else router.push({ name: 'revisor-home' })
}

async function startReview() {
  errorMessage.value = ''
  const data = await api.patch<unknown>(`/reviews/${route.params.id}`, {})
  if (data) await loadReview()
  else errorMessage.value = api.error.value?.message ?? 'Error al iniciar la revision'
}

async function submitReview() {
  errorMessage.value = ''
  if (commentsRequired.value && !comments.value.trim()) {
    errorMessage.value = 'Los comentarios son obligatorios al rechazar una ponencia.'
    return
  }
  const data = await api.patch<unknown>(`/reviews/${route.params.id}`, {
    decision: decision.value,
    comments: comments.value.trim() || undefined,
  })
  if (data) router.push({ name: 'revisor-home' })
  else errorMessage.value = api.error.value?.message ?? 'Error al enviar el dictamen'
}

async function downloadDocument() {
  downloading.value = true
  const token = getApiToken()
  try {
    const res = await fetch(`/api/reviews/${route.params.id}/document`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    if (!res.ok) {
      const json = await res.json().catch(() => ({}))
      errorMessage.value = json.message ?? `Error ${res.status} al descargar`
      return
    }
    const blob = await res.blob()
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = review.value?.submission_document?.original_filename ?? 'documento.pdf'
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  } catch {
    errorMessage.value = 'No se pudo descargar el archivo.'
  } finally {
    downloading.value = false
  }
}

onMounted(loadReview)
watch(() => route.params.id, loadReview)
</script>

<template>
  <div class="max-w-3xl">
    <div class="mb-6">
      <RouterLink :to="{ name: 'revisor-home' }" class="text-sm text-cgr-muted hover:text-white mb-4 inline-block">
        &larr; Volver a mis revisiones
      </RouterLink>
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <h1 class="text-2xl font-bold text-white leading-snug">
            {{ review?.submission?.title ?? 'Cargando...' }}
          </h1>
          <p v-if="review?.submission?.thematic_axis" class="text-sm text-cgr-purple mt-1">
            {{ review.submission.thematic_axis.name }}
          </p>
        </div>
        <UiBadge
          v-if="review"
          :variant="review.status === 'completed' ? 'success' : review.status === 'in_progress' ? 'info' : 'warning'"
          class="shrink-0"
        >
          {{ review.status === 'pending' ? 'Pendiente' : review.status === 'in_progress' ? 'En progreso' : 'Completada' }}
        </UiBadge>
      </div>
    </div>

    <p v-if="errorMessage" class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-2">
      {{ errorMessage }}
    </p>

    <!-- Info del ponente -->
    <UiCard v-if="review?.submission?.user" class="p-5 mb-4">
      <h2 class="text-xs font-semibold text-cgr-muted uppercase tracking-wide mb-3">Ponente</h2>
      <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm">
        <div>
          <span class="text-cgr-subtle text-xs">Nombre</span>
          <p class="text-white font-medium">{{ review.submission.user.name }}</p>
        </div>
        <div v-if="review.submission.user.institution">
          <span class="text-cgr-subtle text-xs">Institucion</span>
          <p class="text-white">{{ review.submission.user.institution }}</p>
        </div>
        <div v-if="review.submission.user.country">
          <span class="text-cgr-subtle text-xs">Pais</span>
          <p class="text-white">{{ review.submission.user.country }}</p>
        </div>
      </div>
      <div class="flex flex-wrap gap-4 mt-3 pt-3 border-t border-cgr-border text-xs text-cgr-subtle">
        <span v-if="review.assigned_at">Asignada: {{ formatDate(review.assigned_at) }}</span>
        <span v-if="review.started_at">Iniciada: {{ formatDate(review.started_at) }}</span>
        <span v-if="review.completed_at">Completada: {{ formatDate(review.completed_at) }}</span>
      </div>
    </UiCard>

    <!-- Pendiente -->
    <UiCard v-if="review?.status === 'pending'" class="p-6 mb-4">
      <div class="flex items-start gap-4">
        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-500/15 shrink-0">
          <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div class="flex-1">
          <p class="font-medium text-white mb-1">Revision pendiente de iniciar</p>
          <p class="text-sm text-cgr-muted mb-4">Al iniciar, podras leer el resumen y el documento PDF del ponente para emitir tu dictamen.</p>
          <UiButton :loading="api.loading.value" @click="startReview">Iniciar revision</UiButton>
        </div>
      </div>
    </UiCard>

    <template v-if="review?.status === 'in_progress' || review?.status === 'completed'">
      <!-- Resumen -->
      <UiCard class="p-6 mb-4">
        <h2 class="font-semibold text-white mb-3">Resumen de la ponencia</h2>
        <div class="bg-cgr-section rounded-lg p-4 text-sm text-cgr-muted leading-relaxed whitespace-pre-wrap max-h-72 overflow-y-auto">
          {{ review?.submission?.abstracts?.[0]?.content ?? 'Sin resumen.' }}
        </div>
      </UiCard>

      <!-- Documento -->
      <UiCard class="p-6 mb-4">
        <h2 class="font-semibold text-white mb-3">Documento PDF</h2>
        <div v-if="review?.submission_document" class="flex items-center justify-between gap-4 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3">
          <div class="flex items-center gap-3 min-w-0">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
            </svg>
            <div class="min-w-0">
              <p class="text-sm text-white truncate">{{ review.submission_document.original_filename }}</p>
              <p class="text-xs text-cgr-subtle">Version {{ review.submission_document.version }}</p>
            </div>
          </div>
          <UiButton size="sm" variant="secondary" :loading="downloading" @click="downloadDocument">Descargar PDF</UiButton>
        </div>
        <p v-else class="text-cgr-subtle text-sm">No hay documento adjunto.</p>
      </UiCard>

      <!-- Dictamen (en progreso) -->
      <UiCard v-if="review?.status === 'in_progress'" class="p-6 mb-4">
        <h2 class="font-semibold text-white mb-4">Emitir dictamen</h2>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <label :class="['flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors', decision === 'approved' ? 'border-green-500/60 bg-green-500/10 text-white' : 'border-cgr-border bg-cgr-section text-cgr-muted hover:border-green-500/40']">
              <input type="radio" value="approved" v-model="decision" class="hidden" />
              <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span class="text-sm font-medium">Aprobar</span>
            </label>
            <label :class="['flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors', decision === 'rejected' ? 'border-red-500/60 bg-red-500/10 text-white' : 'border-cgr-border bg-cgr-section text-cgr-muted hover:border-red-500/40']">
              <input type="radio" value="rejected" v-model="decision" class="hidden" />
              <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span class="text-sm font-medium">Rechazar</span>
            </label>
          </div>
          <div>
            <label class="block text-xs font-medium text-cgr-muted mb-2">
              Comentarios
              <span v-if="commentsRequired" class="text-red-400 ml-1">* obligatorio al rechazar</span>
              <span v-else class="text-cgr-subtle ml-1">(opcional)</span>
            </label>
            <textarea
              v-model="comments"
              rows="5"
              placeholder="Escribe tus observaciones, correcciones o justificacion del dictamen..."
              :class="['w-full bg-cgr-section border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple resize-y', commentsRequired && !comments.trim() ? 'border-red-500/60' : 'border-cgr-border']"
            />
          </div>
          <UiButton :loading="api.loading.value" :variant="decision === 'rejected' ? 'danger' : 'primary'" @click="submitReview">
            {{ decision === 'approved' ? 'Aprobar ponencia' : 'Rechazar ponencia' }}
          </UiButton>
        </div>
      </UiCard>

      <!-- Dictamen completado -->
      <UiCard v-else-if="review?.status === 'completed'" class="p-6">
        <h2 class="font-semibold text-white mb-4">Dictamen emitido</h2>
        <div class="flex items-center gap-3 mb-4">
          <UiBadge :variant="review?.decision === 'approved' ? 'success' : 'danger'">
            {{ review?.decision === 'approved' ? 'Aprobada' : 'Rechazada' }}
          </UiBadge>
          <span class="text-xs text-cgr-subtle">{{ formatDate(review.completed_at) }}</span>
        </div>
        <div v-if="review?.comments" class="bg-cgr-section rounded-lg p-4 text-sm text-cgr-muted whitespace-pre-wrap">
          {{ review.comments }}
        </div>
        <p v-else class="text-sm text-cgr-subtle">Sin comentarios adicionales.</p>
      </UiCard>
    <!-- Historial de revisiones anteriores -->
    <UiCard v-if="review?.history?.length" class="p-5 mt-4">
      <h2 class="text-xs font-semibold text-cgr-muted uppercase tracking-wide mb-4">Historial de revisiones</h2>
      <div class="space-y-3">
        <div
          v-for="h in review.history"
          :key="h.id"
          class="bg-cgr-section border border-cgr-border rounded-lg px-4 py-3"
        >
          <div class="flex items-center justify-between gap-3 mb-2">
            <div class="flex items-center gap-2">
              <span class="text-xs text-cgr-subtle">
                {{ h.submission_document ? 'Doc v' + h.submission_document.version : 'Sin documento' }}
              </span>
              <UiBadge :variant="h.decision === 'approved' ? 'success' : h.decision === 'rejected' ? 'danger' : 'default'">
                {{ h.decision === 'approved' ? 'Aprobada' : h.decision === 'rejected' ? 'Cambios solicitados' : 'Sin dictamen' }}
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
    </UiCard>
    </template>
  </div>
</template>