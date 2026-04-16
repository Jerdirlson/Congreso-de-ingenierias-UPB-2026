<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'

const api = useFetchApi()
const loading = ref(true)
const error = ref(false)

interface ByRole { ponente?: number; participante?: number; revisor?: number; admin?: number; administrativo?: number }
interface ByStatus { [key: string]: number }
interface AxisEntry { name: string; count: number }
interface TrendEntry { date: string; count: number }

interface Metrics {
  users: {
    total: number
    by_role: ByRole
    new_last_7_days: number
    new_last_30_days: number
    verified: number
  }
  submissions: {
    total: number
    by_status: ByStatus
    by_modality: ByStatus
    by_axis: AxisEntry[]
    trend_last_30: TrendEntry[]
  }
  reviews: {
    total: number
    by_status: ByStatus
    approved: number
    approval_rate: number
    avg_days: number
  }
  registrations: {
    total: number
    by_type: ByStatus
    by_modality: ByStatus
    confirmed: number
    attended: number
  }
  payments: {
    total: number
    by_status: ByStatus
    revenue: number
    currency: string
  }
  generated_at: string
}

const metrics = ref<Metrics | null>(null)

async function loadMetrics() {
  loading.value = true
  error.value = false
  try {
    const data = await api.get<Metrics>('/admin/metrics')
    if (data) metrics.value = data
    else error.value = true
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}

onMounted(loadMetrics)

// ── Helpers ──────────────────────────────────────────────────────────────────

function pct(value: number, total: number) {
  if (!total) return 0
  return Math.round((value / total) * 100)
}

function fmt(n: number) {
  return n.toLocaleString('es-CO')
}

function fmtMoney(n: number, currency: string) {
  return new Intl.NumberFormat('es-CO', { style: 'currency', currency, maximumFractionDigits: 0 }).format(n)
}

// ── Status labels & colors ───────────────────────────────────────────────────

const statusLabel: Record<string, string> = {
  draft: 'Borrador',
  abstract_submitted: 'Abstract enviado',
  abstract_rejected: 'Abstract rechazado',
  abstract_approved: 'Abstract aprobado',
  under_review: 'En revisión',
  revision_requested: 'Revisión solicitada',
  document_approved: 'Documento aprobado',
  modality_selected: 'Modalidad seleccionada',
  video_pending: 'Video pendiente',
  video_ready: 'Video listo',
  payment_pending: 'Pago pendiente',
  confirmed: 'Confirmada',
}

const statusColor: Record<string, string> = {
  draft: 'bg-zinc-500',
  abstract_submitted: 'bg-blue-500',
  abstract_rejected: 'bg-red-500',
  abstract_approved: 'bg-sky-400',
  under_review: 'bg-yellow-400',
  revision_requested: 'bg-orange-400',
  document_approved: 'bg-cyan-400',
  modality_selected: 'bg-violet-400',
  video_pending: 'bg-pink-400',
  video_ready: 'bg-indigo-400',
  payment_pending: 'bg-amber-400',
  confirmed: 'bg-green-400',
}

// ── Computed stats ────────────────────────────────────────────────────────────

const presencialCount = computed(() => {
  if (!metrics.value) return 0
  const bm = metrics.value.registrations.by_modality
  return (bm['presencial'] ?? 0)
})

const confirmedSubmissions = computed(() => {
  if (!metrics.value) return 0
  return metrics.value.submissions.by_status['confirmed'] ?? 0
})

const submissionStatusEntries = computed(() => {
  if (!metrics.value) return []
  const bs = metrics.value.submissions.by_status
  const total = metrics.value.submissions.total
  return Object.entries(bs)
    .sort((a, b) => b[1] - a[1])
    .map(([key, count]) => ({ key, count, label: statusLabel[key] ?? key, color: statusColor[key] ?? 'bg-cgr-purple', pct: pct(count, total) }))
})

const maxAxisCount = computed(() => {
  if (!metrics.value) return 1
  return Math.max(1, ...metrics.value.submissions.by_axis.map(a => a.count))
})

const maxTrend = computed(() => {
  if (!metrics.value) return 1
  return Math.max(1, ...metrics.value.submissions.trend_last_30.map(t => t.count))
})

const trendTotal = computed(() => {
  if (!metrics.value) return 0
  return metrics.value.submissions.trend_last_30.reduce((s, t) => s + t.count, 0)
})

const roleEntries = computed(() => {
  if (!metrics.value) return []
  const br = metrics.value.users.by_role
  const total = metrics.value.users.total
  return [
    { label: 'Ponentes', key: 'ponente', count: br.ponente ?? 0, color: 'bg-cgr-purple' },
    { label: 'Participantes', key: 'participante', count: br.participante ?? 0, color: 'bg-blue-500' },
    { label: 'Revisores', key: 'revisor', count: br.revisor ?? 0, color: 'bg-yellow-400' },
    { label: 'Administradores', key: 'admin', count: (br.admin ?? 0) + (br.administrativo ?? 0), color: 'bg-green-400' },
  ].map(r => ({ ...r, pct: pct(r.count, total) }))
})

const reviewStatusColor: Record<string, string> = {
  pending: 'text-yellow-400',
  in_progress: 'text-blue-400',
  completed: 'text-green-400',
}

</script>

<template>
  <div class="max-w-7xl space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Panel de Administración</h1>
        <p v-if="metrics" class="text-cgr-muted text-xs mt-0.5">
          Actualizado: {{ new Date(metrics.generated_at).toLocaleString('es-CO') }}
        </p>
      </div>
      <button
        @click="loadMetrics"
        :disabled="loading"
        class="flex items-center gap-2 border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple transition-colors text-sm px-3 py-1.5 rounded-lg disabled:opacity-40"
      >
        <svg class="w-4 h-4" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Actualizar
      </button>
    </div>

    <!-- Error -->
    <div v-if="error" class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-red-400 text-sm">
      No se pudo cargar las métricas. Verifica que el servidor esté activo.
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading && !metrics" class="space-y-4">
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div v-for="i in 5" :key="i" class="bg-cgr-card border border-cgr-border rounded-2xl h-28 animate-pulse" />
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-cgr-card border border-cgr-border rounded-2xl h-64 animate-pulse" />
        <div class="bg-cgr-card border border-cgr-border rounded-2xl h-64 animate-pulse" />
      </div>
    </div>

    <template v-if="metrics">

      <!-- ── KPI Cards ─────────────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">

        <!-- Usuarios totales -->
        <UiCard class="p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-lg bg-cgr-purple/20 flex items-center justify-center">
              <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <span class="text-xs text-green-400 font-medium">+{{ metrics.users.new_last_7_days }} / 7d</span>
          </div>
          <p class="text-2xl font-bold text-white">{{ fmt(metrics.users.total) }}</p>
          <p class="text-cgr-muted text-xs mt-0.5">Usuarios registrados</p>
        </UiCard>

        <!-- Ponencias -->
        <UiCard class="p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-lg bg-blue-500/20 flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <span class="text-xs text-green-400 font-medium">{{ confirmedSubmissions }} conf.</span>
          </div>
          <p class="text-2xl font-bold text-white">{{ fmt(metrics.submissions.total) }}</p>
          <p class="text-cgr-muted text-xs mt-0.5">Ponencias enviadas</p>
        </UiCard>

        <!-- Inscritos -->
        <UiCard class="p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-lg bg-yellow-400/20 flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
              </svg>
            </div>
            <span class="text-xs text-yellow-400 font-medium">{{ metrics.registrations.confirmed }} conf.</span>
          </div>
          <p class="text-2xl font-bold text-white">{{ fmt(metrics.registrations.total) }}</p>
          <p class="text-cgr-muted text-xs mt-0.5">Inscritos totales</p>
        </UiCard>

        <!-- Asistentes presenciales -->
        <UiCard class="p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-lg bg-green-500/20 flex items-center justify-center">
              <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <span class="text-xs text-green-400 font-medium">{{ metrics.registrations.attended }} asistieron</span>
          </div>
          <p class="text-2xl font-bold text-white">{{ fmt(presencialCount) }}</p>
          <p class="text-cgr-muted text-xs mt-0.5">Asistentes presenciales</p>
        </UiCard>

        <!-- Ingresos -->
        <UiCard class="p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-500/20 flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <span class="text-xs text-cgr-muted font-medium">{{ metrics.payments.by_status['completed'] ?? 0 }} pagos</span>
          </div>
          <p class="text-xl font-bold text-white truncate">{{ fmtMoney(metrics.payments.revenue, metrics.payments.currency) }}</p>
          <p class="text-cgr-muted text-xs mt-0.5">Ingresos confirmados</p>
        </UiCard>
      </div>

      <!-- ── Row 2: Usuarios por rol + Estado de ponencias ──────────────────── -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Usuarios por rol -->
        <UiCard class="p-6">
          <h2 class="text-sm font-semibold text-white mb-1">Usuarios por rol</h2>
          <p class="text-cgr-muted text-xs mb-5">{{ fmt(metrics.users.new_last_30_days) }} nuevos en los últimos 30 días</p>
          <div class="space-y-3.5">
            <div v-for="r in roleEntries" :key="r.key" class="space-y-1">
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full shrink-0" :class="r.color" />
                  <span class="text-white">{{ r.label }}</span>
                </div>
                <span class="text-cgr-muted font-mono">{{ fmt(r.count) }} <span class="text-xs">({{ r.pct }}%)</span></span>
              </div>
              <div class="h-1.5 bg-cgr-section rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-700" :class="r.color" :style="`width: ${r.pct}%`" />
              </div>
            </div>
          </div>
          <div class="mt-5 pt-4 border-t border-cgr-border flex gap-6 text-xs">
            <div>
              <p class="text-cgr-muted">Verificados</p>
              <p class="text-white font-semibold">{{ fmt(metrics.users.verified) }}</p>
            </div>
            <div>
              <p class="text-cgr-muted">Sin verificar</p>
              <p class="text-white font-semibold">{{ fmt(metrics.users.total - metrics.users.verified) }}</p>
            </div>
            <div>
              <p class="text-cgr-muted">Nuevos (7d)</p>
              <p class="text-green-400 font-semibold">+{{ fmt(metrics.users.new_last_7_days) }}</p>
            </div>
          </div>
        </UiCard>

        <!-- Estado de ponencias -->
        <UiCard class="p-6">
          <h2 class="text-sm font-semibold text-white mb-1">Estado de ponencias</h2>
          <p class="text-cgr-muted text-xs mb-5">{{ fmt(metrics.submissions.total) }} ponencias en total</p>
          <div class="space-y-2">
            <div v-for="s in submissionStatusEntries" :key="s.key" class="flex items-center gap-3">
              <span class="w-2 h-2 rounded-full shrink-0" :class="s.color" />
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between text-xs mb-0.5">
                  <span class="text-white truncate">{{ s.label }}</span>
                  <span class="text-cgr-muted font-mono ml-2 shrink-0">{{ s.count }}</span>
                </div>
                <div class="h-1 bg-cgr-section rounded-full overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-700" :class="s.color" :style="`width: ${s.pct}%`" />
                </div>
              </div>
            </div>
            <p v-if="!submissionStatusEntries.length" class="text-cgr-muted text-sm text-center py-4">Sin datos aún</p>
          </div>
        </UiCard>
      </div>

      <!-- ── Row 3: Ejes temáticos + Revisiones ────────────────────────────── -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Ponencias por eje temático -->
        <UiCard class="p-6">
          <h2 class="text-sm font-semibold text-white mb-1">Ponencias por eje temático</h2>
          <p class="text-cgr-muted text-xs mb-5">Distribución entre los ejes activos</p>
          <div v-if="metrics.submissions.by_axis.length" class="space-y-3.5">
            <div v-for="(axis, i) in metrics.submissions.by_axis" :key="i" class="space-y-1">
              <div class="flex items-center justify-between text-sm">
                <span class="text-white truncate pr-3 text-xs">{{ axis.name }}</span>
                <span class="text-cgr-muted font-mono text-xs shrink-0">{{ axis.count }}</span>
              </div>
              <div class="h-2 bg-cgr-section rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full bg-gradient-to-r from-cgr-purple-dark to-cgr-purple transition-all duration-700"
                  :style="`width: ${pct(axis.count, maxAxisCount)}%`"
                />
              </div>
            </div>
          </div>
          <p v-else class="text-cgr-muted text-sm text-center py-8">
            Ninguna ponencia asignada a un eje aún
          </p>
        </UiCard>

        <!-- Revisiones -->
        <UiCard class="p-6">
          <h2 class="text-sm font-semibold text-white mb-1">Revisiones</h2>
          <p class="text-cgr-muted text-xs mb-5">{{ fmt(metrics.reviews.total) }} revisiones asignadas</p>

          <div class="grid grid-cols-3 gap-3 mb-5">
            <div
              v-for="[key, label] in [['pending','Pendientes'],['in_progress','En progreso'],['completed','Completadas']]"
              :key="key"
              class="bg-cgr-section rounded-xl p-3 text-center"
            >
              <p class="text-xl font-bold" :class="reviewStatusColor[key as string]">
                {{ fmt(metrics.reviews.by_status[key as string] ?? 0) }}
              </p>
              <p class="text-cgr-muted text-xs mt-0.5">{{ label }}</p>
            </div>
          </div>

          <div class="border-t border-cgr-border pt-4 grid grid-cols-2 gap-4">
            <div>
              <p class="text-cgr-muted text-xs mb-1">Tasa de aprobación</p>
              <div class="flex items-end gap-2">
                <p class="text-2xl font-bold text-green-400">{{ metrics.reviews.approval_rate }}%</p>
                <p class="text-cgr-muted text-xs mb-1">de completadas</p>
              </div>
              <div class="mt-2 h-2 bg-cgr-section rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full bg-green-400 transition-all duration-700"
                  :style="`width: ${metrics.reviews.approval_rate}%`"
                />
              </div>
            </div>
            <div>
              <p class="text-cgr-muted text-xs mb-1">Tiempo promedio</p>
              <div class="flex items-end gap-2">
                <p class="text-2xl font-bold text-blue-400">{{ metrics.reviews.avg_days }}</p>
                <p class="text-cgr-muted text-xs mb-1">días</p>
              </div>
              <p class="text-cgr-muted text-xs mt-2">Por revisión completada</p>
            </div>
          </div>
        </UiCard>
      </div>

      <!-- ── Row 4: Tendencia últimos 30 días ───────────────────────────────── -->
      <UiCard class="p-6">
        <div class="flex items-start justify-between mb-5">
          <div>
            <h2 class="text-sm font-semibold text-white">Tendencia de ponencias</h2>
            <p class="text-cgr-muted text-xs mt-0.5">Últimos 30 días — {{ fmt(trendTotal) }} ponencias en total</p>
          </div>
        </div>
        <div class="flex items-end gap-0.5 h-20">
          <div
            v-for="(t, i) in metrics.submissions.trend_last_30"
            :key="i"
            class="flex-1 rounded-t group relative"
            :class="t.count > 0 ? 'bg-cgr-purple/60 hover:bg-cgr-purple' : 'bg-cgr-section'"
            :style="`height: ${maxTrend > 0 ? Math.max(4, pct(t.count, maxTrend)) : 4}%`"
            :title="`${t.date}: ${t.count}`"
          >
            <!-- Tooltip -->
            <div v-if="t.count > 0" class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 bg-cgr-section border border-cgr-border rounded px-1.5 py-0.5 text-xs text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
              {{ t.count }}
            </div>
          </div>
        </div>
        <!-- Eje X: fechas inicio y fin -->
        <div class="flex justify-between mt-1.5 text-xs text-cgr-muted">
          <span>{{ metrics.submissions.trend_last_30[0]?.date }}</span>
          <span>{{ metrics.submissions.trend_last_30[29]?.date }}</span>
        </div>
      </UiCard>

      <!-- ── Row 5: Pagos + Modalidades + Ponentes ──────────────────────────── -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <!-- Pagos -->
        <UiCard class="p-6">
          <h2 class="text-sm font-semibold text-white mb-4">Pagos</h2>
          <div class="space-y-2.5">
            <div
              v-for="[key, label, color] in [
                ['completed','Completados','text-green-400'],
                ['pending','Pendientes','text-yellow-400'],
                ['failed','Fallidos','text-red-400'],
                ['refunded','Reembolsados','text-blue-400'],
              ]"
              :key="key"
              class="flex items-center justify-between text-sm"
            >
              <span :class="color">{{ label }}</span>
              <span class="text-white font-semibold">{{ fmt(metrics.payments.by_status[key as string] ?? 0) }}</span>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-cgr-border">
            <p class="text-cgr-muted text-xs">Ingresos totales confirmados</p>
            <p class="text-lg font-bold text-emerald-400 mt-0.5">
              {{ fmtMoney(metrics.payments.revenue, metrics.payments.currency) }}
            </p>
          </div>
        </UiCard>

        <!-- Modalidades de asistencia -->
        <UiCard class="p-6">
          <h2 class="text-sm font-semibold text-white mb-4">Modalidad de inscripciones</h2>
          <div class="space-y-3">
            <div
              v-for="[key, label, color] in [
                ['presencial','Presencial','bg-green-400'],
                ['virtual','Virtual','bg-blue-400'],
                ['proyecto_aula','Proyecto de aula','bg-violet-400'],
              ]"
              :key="key"
              class="space-y-1"
            >
              <div class="flex justify-between text-xs">
                <div class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full" :class="color" />
                  <span class="text-white">{{ label }}</span>
                </div>
                <span class="text-cgr-muted">{{ fmt(metrics.registrations.by_modality[key as string] ?? 0) }}</span>
              </div>
              <div class="h-1.5 bg-cgr-section rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-700" :class="color"
                  :style="`width: ${pct(metrics.registrations.by_modality[key as string] ?? 0, metrics.registrations.total)}%`"
                />
              </div>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-cgr-border grid grid-cols-2 gap-2 text-xs">
            <div>
              <p class="text-cgr-muted">Ponentes inscritos</p>
              <p class="text-white font-semibold">{{ fmt(metrics.registrations.by_type['speaker'] ?? 0) }}</p>
            </div>
            <div>
              <p class="text-cgr-muted">Participantes</p>
              <p class="text-white font-semibold">{{ fmt(metrics.registrations.by_type['participant'] ?? 0) }}</p>
            </div>
          </div>
        </UiCard>

        <!-- Ponencias por modalidad + accesos rápidos -->
        <UiCard class="p-6">
          <h2 class="text-sm font-semibold text-white mb-4">Modalidad de ponencias</h2>
          <div class="space-y-3">
            <div
              v-for="[key, label, color] in [
                ['presencial_oral','Presencial oral','bg-cgr-purple'],
                ['presencial_poster','Presencial póster','bg-pink-400'],
                ['virtual','Virtual','bg-blue-400'],
                ['proyecto_aula','Proyecto de aula','bg-amber-400'],
              ]"
              :key="key"
              class="space-y-1"
            >
              <div class="flex justify-between text-xs">
                <div class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full" :class="color" />
                  <span class="text-white">{{ label }}</span>
                </div>
                <span class="text-cgr-muted">{{ fmt(metrics.submissions.by_modality[key as string] ?? 0) }}</span>
              </div>
              <div class="h-1.5 bg-cgr-section rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-700" :class="color"
                  :style="`width: ${pct(metrics.submissions.by_modality[key as string] ?? 0, metrics.submissions.total)}%`"
                />
              </div>
            </div>
          </div>
        </UiCard>
      </div>

      <!-- ── Quick actions ──────────────────────────────────────────────────── -->
      <div class="flex flex-wrap gap-3 pt-2">
        <RouterLink :to="{ name: 'admin-submissions' }">
          <button class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-5 py-2.5 rounded-lg hover:opacity-90 transition-opacity text-sm">
            Ver ponencias
          </button>
        </RouterLink>
        <RouterLink :to="{ name: 'admin-axes' }">
          <button class="border border-cgr-border text-white font-semibold px-5 py-2.5 rounded-lg hover:border-cgr-purple transition-colors text-sm">
            Ejes temáticos
          </button>
        </RouterLink>
      </div>

    </template>
  </div>
</template>
