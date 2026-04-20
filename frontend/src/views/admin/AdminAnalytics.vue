<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useFetchApi } from '../../composables/useFetchApi'

const api     = useFetchApi()
const loading = ref(true)
const error   = ref(false)
const notConfigured = ref(false)

interface Summary {
  active_users: number
  sessions: number
  page_views: number
  bounce_rate: number
  avg_session_duration: number
}
interface TrendEntry   { date: string; sessions: number; active_users: number }
interface PageEntry    { path: string; views: number }
interface DeviceEntry  { device: string; sessions: number }
interface CountryEntry { country: string; sessions: number }

interface AnalyticsData {
  configured: boolean
  summary:   Summary
  trend:     TrendEntry[]
  top_pages: PageEntry[]
  devices:   DeviceEntry[]
  countries: CountryEntry[]
}

const data = ref<AnalyticsData | null>(null)

async function load() {
  loading.value = true
  error.value   = false
  const result  = await api.get<AnalyticsData>('/admin/analytics')
  if (!result) {
    error.value = true
  } else if (!result.configured) {
    notConfigured.value = true
  } else {
    data.value = result
  }
  loading.value = false
}

onMounted(load)

// ── Helpers ───────────────────────────────────────────────────────────────────

function fmt(n: number) { return n.toLocaleString('es-CO') }

function fmtDuration(secs: number) {
  const m = Math.floor(secs / 60)
  const s = secs % 60
  return m > 0 ? `${m}m ${s}s` : `${s}s`
}

function fmtDate(raw: string) {
  // raw = "20250410"
  const y = raw.slice(0, 4), m = raw.slice(4, 6), d = raw.slice(6, 8)
  return new Date(`${y}-${m}-${d}`).toLocaleDateString('es-CO', { weekday: 'short', day: 'numeric' })
}

function pct(value: number, total: number) {
  if (!total) return 0
  return Math.round((value / total) * 100)
}

const maxTrend = computed(() =>
  Math.max(1, ...(data.value?.trend ?? []).map(t => t.sessions))
)

const maxPages = computed(() =>
  Math.max(1, ...(data.value?.top_pages ?? []).map(p => p.views))
)

const totalDeviceSessions = computed(() =>
  (data.value?.devices ?? []).reduce((s, d) => s + d.sessions, 0)
)

const deviceColors: Record<string, string> = {
  desktop: 'bg-cgr-purple',
  mobile:  'bg-blue-500',
  tablet:  'bg-yellow-400',
}

const deviceIcons: Record<string, string> = {
  desktop: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
  mobile:  'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
  tablet:  'M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
}
</script>

<template>
  <div class="max-w-7xl space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-white">Analytics del sitio</h1>
        <p class="text-cgr-muted text-sm mt-0.5">Últimos 7 días · Google Analytics 4</p>
      </div>
      <button
        v-if="!notConfigured"
        @click="load"
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
      No se pudo cargar el reporte. Verifica que el backend esté corriendo.
    </div>

    <!-- No configurado -->
    <div v-else-if="notConfigured" class="bg-cgr-card border border-cgr-border rounded-xl p-8 text-center space-y-4">
      <div class="w-14 h-14 rounded-full bg-cgr-purple/20 flex items-center justify-center mx-auto">
        <svg class="w-7 h-7 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      </div>
      <div>
        <p class="text-white font-semibold">Analytics no configurado</p>
        <p class="text-cgr-muted text-sm mt-1">Agrega <code class="text-cgr-purple bg-cgr-section px-1 rounded">GA4_PROPERTY_ID</code> y <code class="text-cgr-purple bg-cgr-section px-1 rounded">GA4_SERVICE_ACCOUNT_JSON</code> en el <code class="text-white">.env</code> del backend.</p>
      </div>
    </div>

    <!-- Skeleton loading -->
    <template v-else-if="loading">
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div v-for="i in 5" :key="i" class="bg-cgr-card border border-cgr-border rounded-xl p-4 animate-pulse h-24"/>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-cgr-card border border-cgr-border rounded-xl p-4 animate-pulse h-48"/>
        <div class="bg-cgr-card border border-cgr-border rounded-xl p-4 animate-pulse h-48"/>
      </div>
    </template>

    <!-- Dashboard -->
    <template v-else-if="data">

      <!-- KPI Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

        <div class="bg-cgr-card border border-cgr-border rounded-xl p-4 space-y-1">
          <p class="text-cgr-muted text-xs">Usuarios activos</p>
          <p class="text-2xl font-bold text-white">{{ fmt(data.summary.active_users) }}</p>
          <p class="text-cgr-muted text-xs">últimos 7 días</p>
        </div>

        <div class="bg-cgr-card border border-cgr-border rounded-xl p-4 space-y-1">
          <p class="text-cgr-muted text-xs">Sesiones</p>
          <p class="text-2xl font-bold text-white">{{ fmt(data.summary.sessions) }}</p>
          <p class="text-cgr-muted text-xs">últimos 7 días</p>
        </div>

        <div class="bg-cgr-card border border-cgr-border rounded-xl p-4 space-y-1">
          <p class="text-cgr-muted text-xs">Vistas de página</p>
          <p class="text-2xl font-bold text-white">{{ fmt(data.summary.page_views) }}</p>
          <p class="text-cgr-muted text-xs">últimos 7 días</p>
        </div>

        <div class="bg-cgr-card border border-cgr-border rounded-xl p-4 space-y-1">
          <p class="text-cgr-muted text-xs">Tasa de rebote</p>
          <p class="text-2xl font-bold" :class="data.summary.bounce_rate > 60 ? 'text-red-400' : 'text-green-400'">
            {{ data.summary.bounce_rate }}%
          </p>
          <p class="text-cgr-muted text-xs">últimos 7 días</p>
        </div>

        <div class="bg-cgr-card border border-cgr-border rounded-xl p-4 space-y-1">
          <p class="text-cgr-muted text-xs">Duración media</p>
          <p class="text-2xl font-bold text-white">{{ fmtDuration(data.summary.avg_session_duration) }}</p>
          <p class="text-cgr-muted text-xs">por sesión</p>
        </div>

      </div>

      <!-- Tendencia + Páginas -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Tendencia diaria -->
        <div class="bg-cgr-card border border-cgr-border rounded-xl p-5 space-y-4">
          <h2 class="text-white font-semibold text-sm">Sesiones últimos 7 días</h2>
          <div class="flex items-end gap-1.5 h-32">
            <div
              v-for="entry in data.trend"
              :key="entry.date"
              class="flex-1 flex flex-col items-center gap-1 group"
            >
              <div class="relative w-full flex items-end justify-center" style="height: 100px;">
                <div
                  class="w-full bg-cgr-purple/70 hover:bg-cgr-purple rounded-sm transition-colors cursor-default"
                  :style="{ height: Math.max(4, pct(entry.sessions, maxTrend)) + '%' }"
                  :title="`${fmtDate(entry.date)}: ${entry.sessions} sesiones`"
                />
              </div>
              <span class="text-cgr-muted text-[10px] truncate w-full text-center">
                {{ fmtDate(entry.date).split(' ')[0] }}
              </span>
            </div>
          </div>
          <div class="flex items-center gap-4 text-xs text-cgr-muted border-t border-cgr-border pt-3">
            <span>Total: <span class="text-white font-medium">{{ fmt(data.trend.reduce((s,t) => s + t.sessions, 0)) }}</span> sesiones</span>
            <span>Usuarios: <span class="text-white font-medium">{{ fmt(data.trend.reduce((s,t) => s + t.active_users, 0)) }}</span></span>
          </div>
        </div>

        <!-- Páginas más vistas -->
        <div class="bg-cgr-card border border-cgr-border rounded-xl p-5 space-y-3">
          <h2 class="text-white font-semibold text-sm">Páginas más visitadas</h2>
          <div class="space-y-2.5">
            <div
              v-for="page in data.top_pages"
              :key="page.path"
              class="space-y-1"
            >
              <div class="flex items-center justify-between text-xs">
                <span class="text-white truncate max-w-[70%]" :title="page.path">{{ page.path }}</span>
                <span class="text-cgr-muted shrink-0">{{ fmt(page.views) }}</span>
              </div>
              <div class="h-1.5 bg-cgr-section rounded-full overflow-hidden">
                <div
                  class="h-full bg-cgr-purple rounded-full transition-all"
                  :style="{ width: pct(page.views, maxPages) + '%' }"
                />
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Dispositivos + Países -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Dispositivos -->
        <div class="bg-cgr-card border border-cgr-border rounded-xl p-5 space-y-4">
          <h2 class="text-white font-semibold text-sm">Dispositivos</h2>
          <div class="space-y-3">
            <div
              v-for="d in data.devices"
              :key="d.device"
              class="flex items-center gap-3"
            >
              <div class="w-8 h-8 rounded-lg bg-cgr-section flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-cgr-muted" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="deviceIcons[d.device] ?? deviceIcons.desktop"/>
                </svg>
              </div>
              <div class="flex-1 space-y-1">
                <div class="flex justify-between text-xs">
                  <span class="text-white capitalize">{{ d.device }}</span>
                  <span class="text-cgr-muted">{{ pct(d.sessions, totalDeviceSessions) }}%</span>
                </div>
                <div class="h-1.5 bg-cgr-section rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all"
                    :class="deviceColors[d.device] ?? 'bg-cgr-purple'"
                    :style="{ width: pct(d.sessions, totalDeviceSessions) + '%' }"
                  />
                </div>
              </div>
              <span class="text-cgr-muted text-xs w-10 text-right shrink-0">{{ fmt(d.sessions) }}</span>
            </div>
          </div>
        </div>

        <!-- Países -->
        <div class="bg-cgr-card border border-cgr-border rounded-xl p-5 space-y-3">
          <h2 class="text-white font-semibold text-sm">Top países</h2>
          <div class="space-y-2.5">
            <div
              v-for="(c, i) in data.countries"
              :key="c.country"
              class="flex items-center gap-3"
            >
              <span class="text-cgr-muted text-xs w-4 text-right shrink-0">{{ i + 1 }}</span>
              <span class="flex-1 text-white text-sm truncate">{{ c.country }}</span>
              <div class="w-24 h-1.5 bg-cgr-section rounded-full overflow-hidden shrink-0">
                <div
                  class="h-full bg-blue-500 rounded-full"
                  :style="{ width: pct(c.sessions, data.countries[0]?.sessions ?? 1) + '%' }"
                />
              </div>
              <span class="text-cgr-muted text-xs w-10 text-right shrink-0">{{ fmt(c.sessions) }}</span>
            </div>
          </div>
        </div>

      </div>

    </template>
  </div>
</template>
