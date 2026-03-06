<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiBadge from '../../components/ui/UiBadge.vue'

const router = useRouter()

interface ThematicAxis { id: number; name: string }
interface Review {
  id: number
  status: 'pending' | 'in_progress' | 'completed'
  decision: string | null
  assigned_at: string | null
  submission?: {
    id: number
    title: string
    user?: { name: string; email: string }
    thematic_axis?: { id: number; name: string }
  }
}

const reviews = ref<Review[]>([])
const axes = ref<ThematicAxis[]>([])
const loading = ref(true)

const filterStatus = ref('')
const filterAxis = ref('')

const statusLabels: Record<string, string> = {
  pending:     'Pendiente',
  in_progress: 'En progreso',
  completed:   'Completada',
}
const statusVariants: Record<string, 'warning' | 'info' | 'success'> = {
  pending:     'warning',
  in_progress: 'info',
  completed:   'success',
}
const decisionVariants: Record<string, 'success' | 'danger'> = {
  approved: 'success',
  rejected: 'danger',
}
const decisionLabels: Record<string, string> = {
  approved: 'Aprobada',
  rejected: 'Rechazada',
}

const stats = computed(() => ({
  pending:     reviews.value.filter(r => r.status === 'pending').length,
  in_progress: reviews.value.filter(r => r.status === 'in_progress').length,
  completed:   reviews.value.filter(r => r.status === 'completed').length,
}))

// Agrupar por submission: una card por ponencia con la revision mas reciente/activa
const groupedBySubmission = computed(() => {
  const map = new Map<number, { latest: Review; all: Review[] }>()
  for (const r of reviews.value) {
    const sid = r.submission?.id ?? r.id
    if (!map.has(sid)) {
      map.set(sid, { latest: r, all: [r] })
    } else {
      const entry = map.get(sid)!
      entry.all.push(r)
      // Priorizar: pending > in_progress > completed
      const priority = { pending: 0, in_progress: 1, completed: 2 }
      if ((priority[r.status as keyof typeof priority] ?? 9) < (priority[entry.latest.status as keyof typeof priority] ?? 9)) {
        entry.latest = r
      }
    }
  }
  return Array.from(map.values())
})

const filtered = computed(() => groupedBySubmission.value.filter(({ latest: r }) => {
  if (filterStatus.value && r.status !== filterStatus.value) return false
  if (filterAxis.value && r.submission?.thematic_axis?.id !== Number(filterAxis.value)) return false
  return true
}))

function formatDate(d: string | null) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function loadData() {
  loading.value = true
  const api1 = useFetchApi()
  const api2 = useFetchApi()
  const [reviewsData, axesData] = await Promise.all([
    api1.get<{ data: Review[] } | Review[]>('/reviews'),
    api2.get<ThematicAxis[]>('/thematic-axes'),
  ])
  if (reviewsData) {
    reviews.value = Array.isArray(reviewsData) ? reviewsData : (reviewsData as { data: Review[] }).data ?? []
  }
  if (axesData) axes.value = axesData
  loading.value = false
}

onMounted(loadData)
</script>

<template>
  <div class="max-w-4xl">
    <h1 class="text-2xl font-bold text-white mb-6">Mis revisiones</h1>

    <div v-if="loading" class="text-center py-12 text-cgr-muted">Cargando…</div>

    <template v-else>
      <!-- Stats -->
      <div class="grid grid-cols-3 gap-4 mb-8">
        <UiCard class="p-4 text-center">
          <p class="text-3xl font-bold text-yellow-400">{{ stats.pending }}</p>
          <p class="text-xs text-cgr-muted mt-1">Pendientes</p>
        </UiCard>
        <UiCard class="p-4 text-center">
          <p class="text-3xl font-bold text-blue-400">{{ stats.in_progress }}</p>
          <p class="text-xs text-cgr-muted mt-1">En progreso</p>
        </UiCard>
        <UiCard class="p-4 text-center">
          <p class="text-3xl font-bold text-green-400">{{ stats.completed }}</p>
          <p class="text-xs text-cgr-muted mt-1">Completadas</p>
        </UiCard>
      </div>

      <!-- Filtros -->
      <div class="flex flex-wrap gap-3 mb-6">
        <select
          v-model="filterStatus"
          class="bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-cgr-purple"
        >
          <option value="">Todos los estados</option>
          <option value="pending">Pendiente</option>
          <option value="in_progress">En progreso</option>
          <option value="completed">Completada</option>
        </select>

        <select
          v-model="filterAxis"
          class="bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-cgr-purple"
        >
          <option value="">Todos los ejes</option>
          <option v-for="a in axes" :key="a.id" :value="a.id">{{ a.name }}</option>
        </select>

        <button
          v-if="filterStatus || filterAxis"
          class="text-xs text-cgr-muted hover:text-white transition-colors px-3 py-2"
          @click="filterStatus = ''; filterAxis = ''"
        >
          Limpiar filtros
        </button>
      </div>

      <!-- Sin revisiones -->
      <div v-if="filtered.length === 0" class="text-center py-16 text-cgr-muted">
        <p class="text-lg mb-1">{{ reviews.length === 0 ? 'No tienes revisiones asignadas.' : 'Ninguna revisión coincide con los filtros.' }}</p>
        <p v-if="reviews.length > 0" class="text-sm text-cgr-subtle">Ajusta los filtros para ver más resultados.</p>
      </div>

      <!-- Lista -->
      <div v-else class="space-y-3">
        <UiCard
          v-for="{ latest: r, all } in filtered"
          :key="r.submission?.id ?? r.id"
          class="p-5 cursor-pointer hover:border-cgr-purple/50 transition-colors"
          @click="router.push({ name: 'revisor-detail', params: { id: r.id } })"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
              <h2 class="font-semibold text-white mb-1 line-clamp-2">
                {{ r.submission?.title ?? 'Sin título' }}
              </h2>
              <div class="flex flex-wrap items-center gap-2 mb-2">
                <span v-if="r.submission?.user" class="text-xs text-cgr-muted">
                  {{ r.submission.user.name }}
                </span>
                <span v-if="r.submission?.thematic_axis" class="text-xs text-cgr-purple">
                  · {{ r.submission.thematic_axis.name }}
                </span>
              </div>
              <div class="flex items-center gap-2">
                <UiBadge :variant="statusVariants[r.status]">
                  {{ statusLabels[r.status] }}
                </UiBadge>
                <UiBadge v-if="r.decision" :variant="decisionVariants[r.decision]">
                  {{ decisionLabels[r.decision] }}
                </UiBadge>
                <span v-if="all.length > 1" class="text-xs text-cgr-purple font-medium bg-cgr-purple/10 border border-cgr-purple/20 rounded-full px-2 py-0.5">
                  Revisión v{{ all.length }}
                </span>
              </div>
            </div>
            <div class="shrink-0 text-right">
              <span v-if="r.assigned_at" class="text-cgr-subtle text-xs block">
                {{ formatDate(r.assigned_at) }}
              </span>
            </div>
          </div>
        </UiCard>
      </div>
    </template>
  </div>
</template>
