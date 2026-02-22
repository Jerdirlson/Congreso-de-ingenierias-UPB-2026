<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

const API = '/api'

// ── Auth ────────────────────────────────────────────────────────────────────
interface AuthUser {
  id: number
  name: string
  email: string
  role: 'admin' | 'administrativo' | 'ponente' | 'viewer' | null
}

const token        = ref<string | null>(localStorage.getItem('cgr_token'))
const user         = ref<AuthUser | null>(null)
const email        = ref('')
const password     = ref('')
const loginError   = ref('')
const loginLoading = ref(false)

const isLoggedIn = computed(() => !!token.value && !!user.value)
const canManage  = computed(() => user.value?.role === 'admin' || user.value?.role === 'administrativo')
const isPonente  = computed(() => user.value?.role === 'ponente')

const roleLabel = computed(() => {
  const labels: Record<string, string> = {
    admin: 'Administrador', administrativo: 'Administrativo',
    ponente: 'Ponente', viewer: 'Asistente',
  }
  return labels[user.value?.role ?? ''] ?? 'Sin rol'
})

const roleBadgeColor = computed(() => {
  const colors: Record<string, string> = {
    admin:          'bg-red-500/10 text-red-400 border border-red-500/30',
    administrativo: 'bg-purple-500/10 text-purple-400 border border-purple-500/30',
    ponente:        'bg-blue-500/10 text-blue-400 border border-blue-500/30',
    viewer:         'bg-gray-500/10 text-gray-400 border border-gray-500/30',
  }
  return colors[user.value?.role ?? ''] ?? 'bg-gray-700 text-gray-400'
})

// ── API helper ───────────────────────────────────────────────────────────────
async function authFetch(url: string, options: RequestInit = {}) {
  return fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token.value}`,
      ...((options.headers as Record<string, string>) ?? {}),
    },
  })
}

// ── Login / Logout ───────────────────────────────────────────────────────────
async function login() {
  loginError.value = ''
  loginLoading.value = true
  try {
    const res  = await fetch(`${API}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value }),
    })
    const data = await res.json()
    if (!res.ok) {
      loginError.value = data.message || 'Credenciales incorrectas'
      return
    }
    token.value = data.token
    user.value  = data.user
    localStorage.setItem('cgr_token', data.token)
    if (canManage.value) await loadStreams()
  } catch {
    loginError.value = 'Error de red. Intenta de nuevo.'
  } finally {
    loginLoading.value = false
  }
}

async function logout() {
  await authFetch(`${API}/logout`, { method: 'POST' }).catch(() => {})
  token.value  = null
  user.value   = null
  streams.value = []
  localStorage.removeItem('cgr_token')
}

// ── Streams ──────────────────────────────────────────────────────────────────
interface IngestCredentials {
  rtmps_url: string
  rtmps_stream_key: string
  srt_url: string
  srt_passphrase: string
}

interface Stream {
  id: number
  title: string
  status: string
  scheduled_at: string
  platform: string
  cloudflare_uid: string | null
  iframe_url: string | null
  ingest_credentials?: IngestCredentials
}

const streams        = ref<Stream[]>([])
const streamsLoading = ref(false)
const newTitle       = ref('')
const newDate        = ref('')
const createLoading  = ref(false)
const createError    = ref('')
const justCreated    = ref<Stream | null>(null)
const actionLoading  = ref<number | null>(null)
const copied         = ref<string | null>(null)

async function loadStreams() {
  streamsLoading.value = true
  try {
    const res  = await authFetch(`${API}/streams?per_page=50`)
    const data = await res.json()
    streams.value = data.data ?? []
  } finally {
    streamsLoading.value = false
  }
}

async function createStream() {
  createError.value = ''
  createLoading.value = true
  justCreated.value = null
  try {
    const res  = await authFetch(`${API}/streams`, {
      method: 'POST',
      body: JSON.stringify({
        title: newTitle.value,
        scheduled_at: newDate.value,
        platform: 'cloudflare',
      }),
    })
    const data = await res.json()
    if (!res.ok) { createError.value = data.message ?? 'Error al crear el stream'; return }
    justCreated.value = data
    newTitle.value = ''
    newDate.value  = ''
    await loadStreams()
  } catch {
    createError.value = 'Error de red.'
  } finally {
    createLoading.value = false
  }
}

async function goLive(id: number) {
  actionLoading.value = id
  try { await authFetch(`${API}/streams/${id}/go-live`, { method: 'POST' }); await loadStreams() }
  finally { actionLoading.value = null }
}

async function endStream(id: number) {
  if (!confirm('¿Terminar el directo?')) return
  actionLoading.value = id
  try { await authFetch(`${API}/streams/${id}/end`, { method: 'POST' }); await loadStreams() }
  finally { actionLoading.value = null }
}

async function deleteStream(id: number) {
  if (!confirm('¿Eliminar este stream permanentemente?')) return
  actionLoading.value = id
  try {
    await authFetch(`${API}/streams/${id}`, { method: 'DELETE' })
    if (justCreated.value?.id === id) justCreated.value = null
    await loadStreams()
  } finally { actionLoading.value = null }
}

function copyToClipboard(text: string, key: string) {
  navigator.clipboard.writeText(text)
  copied.value = key
  setTimeout(() => (copied.value = null), 2000)
}

function statusColor(status: string) {
  return ({
    scheduled: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
    live:      'bg-red-500/10 text-red-400 border-red-500/30',
    ended:     'bg-gray-500/10 text-gray-400 border-gray-500/30',
    cancelled: 'bg-gray-700/10 text-gray-500 border-gray-700/30',
  } as Record<string, string>)[status] ?? 'bg-gray-500/10 text-gray-400'
}

function statusLabel(status: string) {
  return ({ scheduled: 'Programado', live: 'EN VIVO', ended: 'Terminado', cancelled: 'Cancelado' } as Record<string, string>)[status] ?? status
}

onMounted(async () => {
  if (token.value) {
    const res = await authFetch(`${API}/me`)
    if (res.ok) {
      user.value = await res.json()
      if (canManage.value) await loadStreams()
    } else {
      token.value = null
      localStorage.removeItem('cgr_token')
    }
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-950 text-gray-100 font-sans">

    <!-- Header -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center font-bold text-sm">▶</div>
        <div>
          <h1 class="font-bold text-white text-sm">Panel — Congreso de Ingenierías 2026</h1>
          <p class="text-xs text-gray-500">Área restringida</p>
        </div>
      </div>
      <div v-if="isLoggedIn" class="flex items-center gap-3">
        <div class="text-right hidden sm:block">
          <p class="text-xs text-white font-medium">{{ user?.name }}</p>
          <p class="text-xs text-gray-500">{{ user?.email }}</p>
        </div>
        <span :class="roleBadgeColor" class="text-xs px-2.5 py-1 rounded-full font-medium">
          {{ roleLabel }}
        </span>
        <button
          @click="logout"
          class="text-xs px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 transition-colors"
        >
          Salir
        </button>
      </div>
    </header>

    <!-- ── LOGIN ─────────────────────────────────────────────────────────── -->
    <div v-if="!isLoggedIn" class="flex items-center justify-center min-h-[calc(100vh-65px)] px-4">
      <div class="w-full max-w-sm">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
          <h2 class="text-xl font-bold text-white mb-1">Iniciar sesión</h2>
          <p class="text-sm text-gray-500 mb-6">Acceso restringido al personal del congreso</p>

          <form @submit.prevent="login" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-400 mb-1.5">Correo electrónico</label>
              <input
                v-model="email" type="email" required
                placeholder="tu@correo.com"
                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition-colors"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-400 mb-1.5">Contraseña</label>
              <input
                v-model="password" type="password" required
                placeholder="••••••••"
                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition-colors"
              />
            </div>
            <p v-if="loginError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
              {{ loginError }}
            </p>
            <button
              type="submit" :disabled="loginLoading"
              class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-medium rounded-lg py-2.5 text-sm transition-colors"
            >
              {{ loginLoading ? 'Entrando…' : 'Entrar' }}
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- ── DASHBOARD ─────────────────────────────────────────────────────── -->
    <main v-else class="max-w-4xl mx-auto px-6 py-8 space-y-8">

      <!-- Vista ponente / viewer: sin acceso a gestión -->
      <div v-if="!canManage" class="bg-gray-900 border border-gray-800 rounded-2xl p-10 text-center">
        <div class="text-4xl mb-4">{{ isPonente ? '🎤' : '👋' }}</div>
        <h2 class="text-lg font-semibold text-white mb-2">
          Hola, {{ user?.name }}
        </h2>
        <p class="text-sm text-gray-400 max-w-sm mx-auto">
          <template v-if="isPonente">
            Tu cuenta de ponente está activa. El equipo administrativo te enviará las instrucciones para tu presentación.
          </template>
          <template v-else>
            Tu cuenta de asistente está activa. Sigue el congreso en la página principal.
          </template>
        </p>
        <a
          href="/"
          class="mt-6 inline-block text-sm px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors"
        >
          Ir a la landing
        </a>
      </div>

      <!-- Vista admin / administrativo: gestión de streams -->
      <template v-if="canManage">

        <!-- Crear stream -->
        <section class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
          <h2 class="font-semibold text-white mb-4">Crear nuevo stream</h2>
          <form @submit.prevent="createStream" class="flex flex-col sm:flex-row gap-3">
            <input
              v-model="newTitle" required
              placeholder="Título de la conferencia"
              class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition-colors"
            />
            <input
              v-model="newDate" type="datetime-local" required
              class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition-colors"
            />
            <button
              type="submit" :disabled="createLoading"
              class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-medium rounded-lg px-5 py-2.5 text-sm transition-colors whitespace-nowrap"
            >
              {{ createLoading ? 'Creando…' : '+ Crear' }}
            </button>
          </form>
          <p v-if="createError" class="mt-3 text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
            {{ createError }}
          </p>

          <!-- Credenciales del stream recién creado -->
          <div v-if="justCreated" class="mt-5 bg-gray-800 border border-green-500/30 rounded-xl p-5 space-y-4">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
              <p class="text-sm font-semibold text-green-400">Stream creado — credenciales para OBS</p>
            </div>
            <div class="space-y-3">
              <div v-for="(field, key) in {
                rtmps_url: { label: 'Server (RTMPS)', value: justCreated.ingest_credentials?.rtmps_url },
                stream_key: { label: 'Stream Key', value: justCreated.ingest_credentials?.rtmps_stream_key },
                iframe: { label: 'URL iframe (embed)', value: justCreated.iframe_url },
              }" :key="key">
                <p class="text-xs text-gray-500 mb-1">{{ field.label }}</p>
                <div class="flex items-center gap-2">
                  <code class="flex-1 bg-gray-900 text-indigo-300 text-xs px-3 py-2 rounded-lg font-mono break-all">{{ field.value }}</code>
                  <button
                    @click="copyToClipboard(field.value!, key)"
                    class="shrink-0 text-xs px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors"
                  >{{ copied === key ? '✓' : 'Copiar' }}</button>
                </div>
              </div>
            </div>
            <p class="text-xs text-gray-500">
              Pega el <strong class="text-gray-300">Server</strong> y el <strong class="text-gray-300">Stream Key</strong> en
              OBS → Settings → Stream → Service: Custom. Luego haz click en <strong class="text-gray-300">Ir en vivo</strong>.
            </p>
          </div>
        </section>

        <!-- Lista de streams -->
        <section>
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-white">Streams</h2>
            <button @click="loadStreams" class="text-xs px-3 py-1.5 bg-gray-800 hover:bg-gray-700 rounded-lg text-gray-400 transition-colors">
              Actualizar
            </button>
          </div>

          <div v-if="streamsLoading" class="text-center py-12 text-gray-600 text-sm">Cargando…</div>

          <div v-else-if="streams.length === 0" class="text-center py-12 text-gray-600 text-sm bg-gray-900 border border-gray-800 rounded-2xl">
            No hay streams. Crea uno arriba.
          </div>

          <div v-else class="space-y-3">
            <div v-for="stream in streams" :key="stream.id" class="bg-gray-900 border border-gray-800 rounded-xl p-5">
              <div class="flex items-start justify-between gap-4 flex-wrap">
                <!-- Info -->
                <div class="min-w-0">
                  <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <h3 class="font-medium text-white text-sm">{{ stream.title }}</h3>
                    <span :class="statusColor(stream.status)" class="text-xs px-2 py-0.5 rounded-full border font-medium flex items-center gap-1.5">
                      {{ statusLabel(stream.status) }}
                      <span v-if="stream.status === 'live'" class="w-1.5 h-1.5 bg-red-400 rounded-full animate-ping"></span>
                    </span>
                  </div>
                  <p class="text-xs text-gray-500">
                    {{ new Date(stream.scheduled_at).toLocaleString('es-CO', { dateStyle: 'medium', timeStyle: 'short' }) }}
                    · #{{ stream.id }}
                    <span v-if="stream.platform === 'cloudflare'" class="text-orange-400 ml-1">· Cloudflare</span>
                  </p>
                </div>

                <!-- Acciones -->
                <div class="flex items-center gap-2 shrink-0">
                  <button
                    v-if="stream.status === 'scheduled'"
                    @click="goLive(stream.id)"
                    :disabled="actionLoading === stream.id"
                    class="flex items-center gap-1.5 bg-red-600 hover:bg-red-500 disabled:opacity-50 text-white text-xs font-medium px-4 py-2 rounded-lg transition-colors"
                  >
                    <span class="w-2 h-2 bg-white rounded-full"></span>
                    {{ actionLoading === stream.id ? '…' : 'Ir en vivo' }}
                  </button>

                  <button
                    v-if="stream.status === 'live'"
                    @click="endStream(stream.id)"
                    :disabled="actionLoading === stream.id"
                    class="bg-gray-700 hover:bg-gray-600 disabled:opacity-50 text-white text-xs font-medium px-4 py-2 rounded-lg transition-colors"
                  >
                    {{ actionLoading === stream.id ? '…' : 'Terminar directo' }}
                  </button>

                  <!-- Eliminar solo para admin -->
                  <button
                    v-if="stream.status !== 'live' && user?.role === 'admin'"
                    @click="deleteStream(stream.id)"
                    :disabled="actionLoading === stream.id"
                    class="text-gray-600 hover:text-red-400 disabled:opacity-50 text-xs px-2 py-2 rounded-lg transition-colors"
                    title="Eliminar"
                  >✕</button>
                </div>
              </div>

              <!-- URL del player si está en vivo -->
              <div v-if="stream.status === 'live' && stream.iframe_url" class="mt-4 pt-4 border-t border-gray-800">
                <p class="text-xs text-gray-500 mb-1.5">URL del player en vivo</p>
                <div class="flex items-center gap-2">
                  <code class="flex-1 bg-gray-800 text-indigo-300 text-xs px-3 py-2 rounded-lg font-mono break-all">{{ stream.iframe_url }}</code>
                  <button
                    @click="copyToClipboard(stream.iframe_url!, `live_${stream.id}`)"
                    class="shrink-0 text-xs px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors"
                  >{{ copied === `live_${stream.id}` ? '✓' : 'Copiar' }}</button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Solo admin: info de usuarios -->
        <section v-if="user?.role === 'admin'" class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
          <h2 class="font-semibold text-white mb-3">Usuarios del sistema</h2>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div v-for="rol in [
              { name: 'admin',          label: 'Admin',          color: 'text-red-400',    desc: 'Acceso total' },
              { name: 'administrativo', label: 'Administrativo', color: 'text-purple-400', desc: 'Gestiona streams' },
              { name: 'ponente',        label: 'Ponente',        color: 'text-blue-400',   desc: 'Sube su material' },
              { name: 'viewer',         label: 'Asistente',      color: 'text-gray-400',   desc: 'Solo lectura' },
            ]" :key="rol.name" class="bg-gray-800 rounded-xl p-4">
              <p :class="rol.color" class="text-xs font-semibold mb-0.5">{{ rol.label }}</p>
              <p class="text-xs text-gray-500">{{ rol.desc }}</p>
            </div>
          </div>
          <p class="text-xs text-gray-600 mt-4">
            Para crear usuarios con roles específicos, usa el panel de base de datos o la CLI:
            <code class="text-gray-400 bg-gray-800 px-1 rounded">docker exec cgr-backend php artisan tinker</code>
          </p>
        </section>

      </template>
    </main>
  </div>
</template>
