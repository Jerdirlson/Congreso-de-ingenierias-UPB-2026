<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiBadge from '../../components/ui/UiBadge.vue'
import UiButton from '../../components/ui/UiButton.vue'

const router = useRouter()

interface Submission {
  id: number
  title: string
  status: string
  modality: string | null
  thematic_axis?: { id: number; name: string }
}

const submissions    = ref<Submission[]>([])
const loading        = ref(true)
const confirmDeleteId = ref<number | null>(null)
const deleting       = ref(false)
const deleteError    = ref('')

const statusLabels: Record<string, string> = {
  draft:               'Borrador',
  abstract_submitted:  'Resumen enviado',
  abstract_rejected:   'Resumen rechazado',
  abstract_approved:   'Resumen aprobado',
  under_review:        'En revisión',
  revision_requested:  'Revisión solicitada',
  document_approved:   'Documento aprobado',
  modality_selected:   'Modalidad elegida',
  video_pending:       'Video pendiente',
  video_ready:         'Video listo',
  payment_pending:     'Pago pendiente',
  confirmed:           'Confirmado',
}

const statusVariants: Record<string, 'default' | 'warning' | 'danger' | 'success' | 'info' | 'purple'> = {
  draft:               'default',
  abstract_submitted:  'info',
  abstract_rejected:   'danger',
  abstract_approved:   'success',
  under_review:        'info',
  revision_requested:  'warning',
  document_approved:   'success',
  modality_selected:   'purple',
  video_pending:       'warning',
  video_ready:         'success',
  payment_pending:     'warning',
  confirmed:           'success',
}

async function loadData() {
  loading.value = true
  const api = useFetchApi()
  const subsData = await api.get<{ data: Submission[] } | Submission[]>('/submissions')
  if (subsData) {
    submissions.value = Array.isArray(subsData) ? subsData : (subsData as { data: Submission[] }).data ?? []
  }
  loading.value = false
}

async function deleteSubmission(id: number) {
  deleting.value = true
  deleteError.value = ''
  const api = useFetchApi()
  await api.delete(`/submissions/${id}`)
  deleting.value = false
  if (api.error.value) {
    deleteError.value = api.error.value.message ?? 'No se pudo eliminar la ponencia.'
  } else {
    confirmDeleteId.value = null
    await loadData()
  }
}

onMounted(loadData)
</script>

<template>
  <div class="max-w-4xl">
    <div v-if="loading" class="text-center py-12 text-cgr-muted">Cargando…</div>

    <template v-else>
      <!-- Encabezado -->
      <div class="flex items-start justify-between mb-8 gap-4">
        <div>
          <h1 class="text-2xl font-bold text-white">Mis ponencias</h1>
          <p class="text-sm text-cgr-muted mt-1">
            Registra y gestiona tu ponencia para el Congreso de Ingenierías 2026.
          </p>
        </div>
        <UiButton
          v-if="submissions.length === 0"
          variant="primary"
          size="sm"
          @click="router.push({ name: 'ponente-new' })"
        >
          Nueva ponencia
        </UiButton>
      </div>

      <!-- Sin ponencias -->
      <div v-if="submissions.length === 0" class="text-center py-12">
        <p class="text-cgr-muted mb-2">Aún no tienes ponencias registradas.</p>
        <p class="text-xs text-cgr-subtle mb-6">Solo se permite una ponencia por ponente.</p>
        <UiButton variant="primary" @click="router.push({ name: 'ponente-new' })">
          Registrar mi ponencia
        </UiButton>
      </div>

      <!-- Lista de ponencias -->
      <div v-else class="space-y-4">
        <UiCard
          v-for="s in submissions"
          :key="s.id"
          class="p-5 cursor-pointer hover:border-cgr-purple/50 transition-colors"
          @click="router.push({ name: 'ponente-detail', params: { id: s.id } })"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <h2 class="font-semibold text-white mb-1">{{ s.title }}</h2>
              <p v-if="s.thematic_axis" class="text-xs text-cgr-muted mb-2">
                {{ s.thematic_axis.name }}
              </p>
              <div class="flex items-center gap-2 flex-wrap">
                <UiBadge :variant="statusVariants[s.status] ?? 'default'">
                  {{ statusLabels[s.status] ?? s.status }}
                </UiBadge>
                <!-- Alerta de pago pendiente -->
                <span
                  v-if="s.status === 'payment_pending'"
                  class="text-[10px] font-semibold text-amber-400 bg-amber-500/10 border border-amber-500/30 px-2 py-0.5 rounded-full"
                >
                  Acción requerida: completar pago
                </span>
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <span class="text-cgr-subtle text-xs">#{{ s.id }}</span>
              <button
                class="p-1.5 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors"
                title="Eliminar ponencia"
                @click.stop="confirmDeleteId = s.id"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </UiCard>
      </div>
    </template>

    <!-- Modal confirmar eliminar -->
    <Teleport to="body">
      <div
        v-if="confirmDeleteId !== null"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="confirmDeleteId = null"
      >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="confirmDeleteId = null" />
        <div class="relative bg-cgr-card border border-cgr-border rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-500/15 shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </div>
            <div>
              <p class="font-semibold text-white">Eliminar ponencia</p>
              <p class="text-xs text-cgr-muted mt-0.5">Esta acción no se puede deshacer</p>
            </div>
          </div>
          <p class="text-sm text-cgr-muted mb-4">
            La ponencia será eliminada. Podrás registrar una nueva ponencia después.
          </p>
          <p v-if="deleteError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2 mb-4">
            {{ deleteError }}
          </p>
          <div class="flex gap-3">
            <UiButton variant="danger" class="flex-1" :loading="deleting" @click="deleteSubmission(confirmDeleteId!)">
              Sí, eliminar
            </UiButton>
            <UiButton variant="secondary" class="flex-1" :disabled="deleting" @click="confirmDeleteId = null; deleteError = ''">
              Cancelar
            </UiButton>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
