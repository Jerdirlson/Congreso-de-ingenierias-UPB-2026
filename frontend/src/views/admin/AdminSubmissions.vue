<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiBadge from '../../components/ui/UiBadge.vue'

const router = useRouter()

interface Submission {
  id: number
  title: string
  status: string
  updated_at: string
  user?: { id: number; name: string; email: string }
  thematic_axis?: { id: number; name: string }
  reviews?: { id: number; reviewer?: { name: string }; status: string; decision: string | null }[]
  latest_article?: { id: number; status: string; version: number } | null
}
interface ThematicAxis { id: number; name: string }

const submissions = ref<Submission[]>([])
const axes = ref<ThematicAxis[]>([])
const loading = ref(true)
const filterStatus  = ref('')
const filterAxis    = ref('')
const filterArticle = ref('')
const search        = ref('')

const statusLabels: Record<string, string> = {
  draft: 'Borrador',
  abstract_submitted: 'Resumen enviado',
  abstract_rejected: 'Pendiente de ajustes',
  abstract_approved: 'Resumen aprobado',
  under_review: 'En revisión',
  revision_requested: 'Pendiente de ajustes',
  document_approved: 'Doc. aprobado',
  modality_selected: 'Modalidad elegida',
  video_pending: 'Video pendiente',
  video_ready: 'Video listo',
  payment_pending: 'Pago pendiente',
  confirmed: 'Confirmado',
}
const statusVariants: Record<string, 'default' | 'warning' | 'danger' | 'success' | 'info' | 'purple'> = {
  draft: 'default',
  abstract_submitted: 'info',
  abstract_rejected: 'warning',
  abstract_approved: 'success',
  under_review: 'info',
  revision_requested: 'warning',
  document_approved: 'success',
  modality_selected: 'purple',
  video_pending: 'warning',
  video_ready: 'success',
  payment_pending: 'warning',
  confirmed: 'success',
}

// El artículo de revista es un carril paralelo: no cambia el estado de la
// ponencia, por eso se filtra por el estado del propio artículo.
// pending_review = subido y sin revisor asignado (asignar lo pasa a under_review).
const ARTICLE_FILTERS = [
  { value: 'with',               label: 'Con artículo' },
  { value: 'pending_review',     label: 'Artículo por asignar revisor' },
  { value: 'under_review',       label: 'Artículo en revisión' },
  { value: 'revision_requested', label: 'Artículo con ajustes solicitados' },
  { value: 'approved',           label: 'Artículo aprobado' },
]
const articleStatusLabels: Record<string, string> = {
  pending_review: 'Por asignar',
  under_review: 'En revisión',
  revision_requested: 'Ajustes solicitados',
  approved: 'Aprobado',
}
const articleStatusVariants: Record<string, 'default' | 'warning' | 'danger' | 'success' | 'info' | 'purple'> = {
  pending_review: 'purple',
  under_review: 'info',
  revision_requested: 'warning',
  approved: 'success',
}

const stats = computed(() => {
  const list = submissions.value
  return {
    total:     list.length,
    toApprove: list.filter(s => s.status === 'abstract_submitted').length,
    inReview:  list.filter(s => s.status === 'under_review').length,
    confirmed: list.filter(s => s.status === 'confirmed').length,
  }
})

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  return submissions.value.filter(s => {
    if (filterStatus.value && s.status !== filterStatus.value) return false
    if (filterAxis.value && s.thematic_axis?.id !== Number(filterAxis.value)) return false
    if (filterArticle.value) {
      if (!s.latest_article) return false
      if (filterArticle.value !== 'with' && s.latest_article.status !== filterArticle.value) return false
    }
    if (q) {
      const inTitle  = s.title.toLowerCase().includes(q)
      const inAuthor = (s.user?.name ?? '').toLowerCase().includes(q)
      const inEmail  = (s.user?.email ?? '').toLowerCase().includes(q)
      if (!inTitle && !inAuthor && !inEmail) return false
    }
    return true
  })
})

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function loadData() {
  loading.value = true
  const api1 = useFetchApi()
  const api2 = useFetchApi()
  const [subsData, axesData] = await Promise.all([
    api1.get<Submission[]>('/admin/submissions'),
    api2.get<ThematicAxis[]>('/thematic-axes'),
  ])
  if (subsData) submissions.value = subsData as Submission[]
  if (axesData) axes.value = axesData
  loading.value = false
}

onMounted(loadData)
</script>

<template>
  <div class="max-w-6xl">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-white">Ponencias</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-cgr-muted">Cargando...</div>

    <template v-else>
      <!-- Stats -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <UiCard class="p-4 text-center">
          <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
          <p class="text-xs text-cgr-muted mt-1">Total</p>
        </UiCard>
        <UiCard class="p-4 text-center">
          <p class="text-2xl font-bold text-blue-400">{{ stats.toApprove }}</p>
          <p class="text-xs text-cgr-muted mt-1">Por aprobar</p>
        </UiCard>
        <UiCard class="p-4 text-center">
          <p class="text-2xl font-bold text-yellow-400">{{ stats.inReview }}</p>
          <p class="text-xs text-cgr-muted mt-1">En revisión</p>
        </UiCard>
        <UiCard class="p-4 text-center">
          <p class="text-2xl font-bold text-green-400">{{ stats.confirmed }}</p>
          <p class="text-xs text-cgr-muted mt-1">Confirmadas</p>
        </UiCard>
      </div>

      <!-- Filtros -->
      <div class="flex flex-wrap gap-3 mb-5">
        <!-- Buscador -->
        <div class="relative flex-1 min-w-48">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-cgr-subtle pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por título o autor…"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg pl-9 pr-4 py-2 text-sm text-white placeholder:text-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
          />
        </div>
        <select
          v-model="filterStatus"
          class="bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-cgr-purple"
        >
          <option value="">Todos los estados</option>
          <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
        </select>
        <select
          v-model="filterAxis"
          class="bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-cgr-purple"
        >
          <option value="">Todos los ejes</option>
          <option v-for="a in axes" :key="a.id" :value="a.id">{{ a.name }}</option>
        </select>
        <select
          v-model="filterArticle"
          class="bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-cgr-purple"
        >
          <option value="">Artículo (revista)</option>
          <option v-for="f in ARTICLE_FILTERS" :key="f.value" :value="f.value">{{ f.label }}</option>
        </select>
        <button
          v-if="search || filterStatus || filterAxis || filterArticle"
          class="text-xs text-cgr-muted hover:text-white transition-colors px-3 py-2"
          @click="search = ''; filterStatus = ''; filterAxis = ''; filterArticle = ''"
        >
          Limpiar
        </button>
        <span class="ml-auto text-xs text-cgr-subtle self-center">{{ filtered.length }} ponencias</span>
      </div>

      <!-- Tabla -->
      <UiCard class="overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-cgr-border text-left">
                <th class="px-5 py-3 text-xs font-semibold text-cgr-muted uppercase tracking-wide">#</th>
                <th class="px-5 py-3 text-xs font-semibold text-cgr-muted uppercase tracking-wide">Ponencia</th>
                <th class="px-5 py-3 text-xs font-semibold text-cgr-muted uppercase tracking-wide">Autor</th>
                <th class="px-5 py-3 text-xs font-semibold text-cgr-muted uppercase tracking-wide">Eje</th>
                <th class="px-5 py-3 text-xs font-semibold text-cgr-muted uppercase tracking-wide">Estado</th>
                <th class="px-5 py-3 text-xs font-semibold text-cgr-muted uppercase tracking-wide">Artículo</th>
                <th class="px-5 py-3 text-xs font-semibold text-cgr-muted uppercase tracking-wide">Revisores</th>
                <th class="px-5 py-3 text-xs font-semibold text-cgr-muted uppercase tracking-wide">Actualizado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-cgr-border">
              <tr
                v-for="s in filtered"
                :key="s.id"
                class="hover:bg-cgr-section/60 transition-colors cursor-pointer"
                @click="router.push({ name: 'admin-submission-detail', params: { id: s.id } })"
              >
                <td class="px-5 py-4 text-cgr-subtle">#{{ s.id }}</td>
                <td class="px-5 py-4 max-w-xs">
                  <p class="text-white font-medium truncate">{{ s.title }}</p>
                </td>
                <td class="px-5 py-4 text-cgr-muted">{{ s.user?.name ?? '—' }}</td>
                <td class="px-5 py-4">
                  <span v-if="s.thematic_axis" class="text-xs text-cgr-purple">{{ s.thematic_axis.name }}</span>
                  <span v-else class="text-cgr-subtle text-xs">—</span>
                </td>
                <td class="px-5 py-4">
                  <UiBadge :variant="statusVariants[s.status] ?? 'default'">
                    {{ statusLabels[s.status] ?? s.status }}
                  </UiBadge>
                </td>
                <td class="px-5 py-4">
                  <UiBadge v-if="s.latest_article" :variant="articleStatusVariants[s.latest_article.status] ?? 'default'">
                    {{ articleStatusLabels[s.latest_article.status] ?? s.latest_article.status }}
                  </UiBadge>
                  <span v-else class="text-xs text-cgr-subtle">—</span>
                </td>
                <td class="px-5 py-4 max-w-[180px]">
                  <div v-if="s.reviews?.length" class="flex flex-wrap gap-1">
                    <span
                      v-for="rev in s.reviews"
                      :key="rev.id"
                      :title="rev.reviewer?.name ?? '—'"
                      :class="[
                        'text-[10px] font-medium rounded-full px-2 py-0.5 border truncate max-w-[160px]',
                        rev.decision === 'approved'
                          ? 'text-green-300 border-green-500/30 bg-green-500/10'
                          : rev.decision === 'rejected'
                          ? 'text-amber-300 border-amber-500/30 bg-amber-500/10'
                          : 'text-cgr-muted border-cgr-border bg-cgr-section'
                      ]"
                    >
                      {{ rev.reviewer?.name ?? '—' }}
                    </span>
                  </div>
                  <span v-else class="text-xs text-cgr-subtle">—</span>
                </td>
                <td class="px-5 py-4 text-cgr-subtle text-xs">{{ formatDate(s.updated_at) }}</td>
              </tr>
              <tr v-if="filtered.length === 0">
                <td colspan="8" class="px-5 py-12 text-center text-cgr-muted">No hay ponencias con estos filtros.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </UiCard>
    </template>
  </div>
</template>