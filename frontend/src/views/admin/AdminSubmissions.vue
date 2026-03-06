<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
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
  updated_at: string
  user?: { id: number; name: string; email: string }
  thematic_axis?: { id: number; name: string }
  reviews?: { id: number; reviewer?: { name: string }; status: string; decision: string | null }[]
}
interface ThematicAxis { id: number; name: string }

const submissions = ref<Submission[]>([])
const axes = ref<ThematicAxis[]>([])
const loading = ref(true)
const filterStatus = ref('')
const filterAxis = ref('')

const statusLabels: Record<string, string> = {
  draft: 'Borrador',
  abstract_submitted: 'Resumen enviado',
  abstract_rejected: 'Resumen rechazado',
  abstract_approved: 'Resumen aprobado',
  under_review: 'En revisión',
  revision_requested: 'Revisión solicitada',
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
  abstract_rejected: 'danger',
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

const stats = computed(() => {
  const list = submissions.value
  return {
    total: list.length,
    pending: list.filter(s => ['abstract_approved', 'under_review'].includes(s.status)).length,
    revision: list.filter(s => s.status === 'revision_requested').length,
    confirmed: list.filter(s => s.status === 'confirmed').length,
  }
})

const filtered = computed(() => submissions.value.filter(s => {
  if (filterStatus.value && s.status !== filterStatus.value) return false
  if (filterAxis.value && s.thematic_axis?.id !== Number(filterAxis.value)) return false
  return true
}))

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function loadData() {
  loading.value = true
  const api1 = useFetchApi()
  const api2 = useFetchApi()
  const [subsData, axesData] = await Promise.all([
    api1.get<{ data: Submission[] }>('/admin/submissions?per_page=100'),
    api2.get<ThematicAxis[]>('/thematic-axes'),
  ])
  if (subsData) submissions.value = (subsData as { data: Submission[] }).data ?? []
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
          <p class="text-2xl font-bold text-blue-400">{{ stats.pending }}</p>
          <p class="text-xs text-cgr-muted mt-1">En revisión</p>
        </UiCard>
        <UiCard class="p-4 text-center">
          <p class="text-2xl font-bold text-yellow-400">{{ stats.revision }}</p>
          <p class="text-xs text-cgr-muted mt-1">Con correcciones</p>
        </UiCard>
        <UiCard class="p-4 text-center">
          <p class="text-2xl font-bold text-green-400">{{ stats.confirmed }}</p>
          <p class="text-xs text-cgr-muted mt-1">Confirmadas</p>
        </UiCard>
      </div>

      <!-- Filtros -->
      <div class="flex flex-wrap gap-3 mb-5">
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
        <button
          v-if="filterStatus || filterAxis"
          class="text-xs text-cgr-muted hover:text-white transition-colors px-3 py-2"
          @click="filterStatus = ''; filterAxis = ''"
        >
          Limpiar filtros
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
                  <span v-if="s.reviews?.length" class="text-xs text-cgr-muted">
                    {{ s.reviews.length }} asignado{{ s.reviews.length > 1 ? 's' : '' }}
                  </span>
                  <span v-else class="text-xs text-cgr-subtle">—</span>
                </td>
                <td class="px-5 py-4 text-cgr-subtle text-xs">{{ formatDate(s.updated_at) }}</td>
              </tr>
              <tr v-if="filtered.length === 0">
                <td colspan="7" class="px-5 py-12 text-center text-cgr-muted">No hay ponencias con estos filtros.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </UiCard>
    </template>
  </div>
</template>