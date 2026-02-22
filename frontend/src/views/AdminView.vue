<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

const API = '/api'

// ── Auth ────────────────────────────────────────────────────────────────────
const token   = ref<string | null>(localStorage.getItem('cgr_token'))
const me      = ref<{ name: string; email: string } | null>(null)
const email   = ref('')
const password = ref('')
const loginError = ref('')
const loginLoading = ref(false)

const isLoggedIn = computed(() => !!token.value)

async function login() {
  loginError.value = ''
  loginLoading.value = true
  try {
    const res = await fetch(`${API}/login`, {
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
    me.value = data.user
    localStorage.setItem('cgr_token', data.token)
    await loadStreams()
  } catch {
    loginError.value = 'Error de red. Intenta de nuevo.'
  } finally {
    loginLoading.value = false
  }
}

async function logout() {
  await authFetch(`${API}/logout`, { method: 'POST' })
  token.value = null
  me.value = null
  streams.value = []
  localStorage.removeItem('cgr_token')
}

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
    const res = await authFetch(`${API}/streams`, {
      method: 'POST',
      body: JSON.stringify({
        title: newTitle.value,
        scheduled_at: newDate.value,
        platform: 'cloudflare',
      }),
    })
    const data = await res.json()
    if (!res.ok) {
      createError.value = data.message ?? 'Error al crear el stream'
      return
    }
    justCreated.value = data
    newTitle.value = ''
    newDate.value = ''
    await loadStreams()
  } catch {
    createError.value = 'Error de red.'
  } finally {
    createLoading.value = false
  }
}

async function goLive(id: number) {
  actionLoading.value = id
  try {
    await authFetch(`${API}/streams/${id}/go-live`, { method: 'POST' })
    await loadStreams()
  } finally {
    actionLoading.value = null
  }
}

async function endStream(id: number) {
  if (!confirm('¿Seguro que quieres terminar el directo?')) return
  actionLoading.value = id
  try {
    await authFetch(`${API}/streams/${id}/end`, { method: 'POST' })
    await loadStreams()
  } finally {
    actionLoading.value = null
  }
}

async function deleteStream(id: number) {
  if (!confirm('¿Eliminar este stream permanentemente?')) return
  actionLoading.value = id
  try {
    await authFetch(`${API}/streams/${id}`, { method: 'DELETE' })
    if (justCreated.value?.id === id) justCreated.value = null
    await loadStreams()
  } finally {
    actionLoading.value = null
  }
}

function copyToClipboard(text: string, key: string) {
  navigator.clipboard.writeText(text)
  copied.value = key
  setTimeout(() => (copied.value = null), 2000)
}

function statusColor(status: string) {
  return {
    scheduled: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
    live:      'bg-red-500/10 text-red-400 border-red-500/30',
    ended:     'bg-gray-500/10 text-gray-400 border-gray-500/30',
    cancelled: 'bg-gray-700/10 text-gray-500 border-gray-700/30',
  }[status] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/30'
}

function statusLabel(status: string) {
  return { scheduled: 'Programado', live: 'EN VIVO', ended: 'Terminado', cancelled: 'Cancelado' }[status] ?? status
}

onMounted(async () => {
  if (isLoggedIn.value) {
    const res = await authFetch(`${API}/me`)
    if (res.ok) {
      me.value = await res.json()
      await loadStreams()
    } else {
      // token expirado
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
        <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
          ▶
        </div>
        <div>
          <h1 class="font-bold text-white text-sm">Panel de Streaming</h1>
          <p class="text-xs text-gray-500">Congreso de Ingenierías 2026</p>
        </div>
      </div>
      <div v-if="isLoggedIn" class="flex items-center gap-3">
        <span class="text-xs text-gray-400">{{ me?.name }}</span>
        <button
          @click="logout"
          class="text-xs px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 transition-colors"
        >
          Cerrar sesión
        </button>
      </div>
    </header>

    <!-- Login -->
    <div v-if="!isLoggedIn" class="flex items-center justify-center min-h-[calc(100vh-65px)] px-4">
      <div class="w-full max-w-sm">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
          <h2 class="text-xl font-bold text-white mb-1">Iniciar sesión</h2>
          <p class="text-sm text-gray-500 mb-6">Acceso restringido a administradores</p>

          <form @submit.prevent="login" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-400 mb-1.5">Correo electrónico</label>
              <input
                v-model="email"
                type="email"
                required
                placeholder="admin@congreso.upb.edu.co"
                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition-colors"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-400 mb-1.5">Contraseña</label>
              <input
                v-model="password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition-colors"
              />
            </div>

            <p v-if="loginError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
              {{ loginError }}
            </p>

            <button
              type="submit"
              :disabled="loginLoading"
              class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg py-2.5 text-sm transition-colors"
            >
              {{ loginLoading ? 'Entrando…' : 'Entrar' }}
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Dashboard -->
    <main v-else class="max-w-4xl mx-auto px-6 py-8 space-y-8">

      <!-- Crear stream -->
      <section class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h2 class="font-semibold text-white mb-4">Crear nuevo stream</h2>
        <form @submit.prevent="createStream" class="flex flex-col sm:flex-row gap-3">
          <input
            v-model="newTitle"
            required
            placeholder="Título de la conferencia"
            class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition-colors"
          />
          <input
            v-model="newDate"
            type="datetime-local"
            required
            class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition-colors"
          />
          <button
            type="submit"
            :disabled="createLoading"
            class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-medium rounded-lg px-5 py-2.5 text-sm transition-colors whitespace-nowrap"
          >
            {{ createLoading ? 'Creando…' : '+ Crear' }}
          </button>
        </form>
        <p v-if="createError" class="mt-3 text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
          {{ createError }}
        </p>

        <!-- Credenciales del stream recién creado -->
        <div v-if="justCreated" class="mt-5 bg-gray-800 border border-indigo-500/30 rounded-xl p-5 space-y-4">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
            <p class="text-sm font-semibold text-green-400">Stream creado — credenciales para OBS</p>
          </div>

          <div class="space-y-3">
            <div>
              <p class="text-xs text-gray-500 mb-1">Server (RTMPS)</p>
              <div class="flex items-center gap-2">
                <code class="flex-1 bg-gray-900 text-indigo-300 text-xs px-3 py-2 rounded-lg font-mono break-all">
                  {{ justCreated.ingest_credentials?.rtmps_url }}
                </code>
                <button
                  @click="copyToClipboard(justCreated!.ingest_credentials!.rtmps_url, 'rtmps_url')"
                  class="shrink-0 text-xs px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors"
                >
                  {{ copied === 'rtmps_url' ? '✓' : 'Copiar' }}
                </button>
              </div>
            </div>

            <div>
              <p class="text-xs text-gray-500 mb-1">Stream Key</p>
              <div class="flex items-center gap-2">
                <code class="flex-1 bg-gray-900 text-indigo-300 text-xs px-3 py-2 rounded-lg font-mono break-all">
                  {{ justCreated.ingest_credentials?.rtmps_stream_key }}
                </code>
                <button
                  @click="copyToClipboard(justCreated!.ingest_credentials!.rtmps_stream_key, 'stream_key')"
                  class="shrink-0 text-xs px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors"
                >
                  {{ copied === 'stream_key' ? '✓' : 'Copiar' }}
                </button>
              </div>
            </div>

            <div>
              <p class="text-xs text-gray-500 mb-1">URL del iframe (para incrustar en la web)</p>
              <div class="flex items-center gap-2">
                <code class="flex-1 bg-gray-900 text-gray-400 text-xs px-3 py-2 rounded-lg font-mono break-all">
                  {{ justCreated.iframe_url }}
                </code>
                <button
                  @click="copyToClipboard(justCreated!.iframe_url!, 'iframe')"
                  class="shrink-0 text-xs px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors"
                >
                  {{ copied === 'iframe' ? '✓' : 'Copiar' }}
                </button>
              </div>
            </div>
          </div>

          <p class="text-xs text-gray-500">
            Pega el <strong class="text-gray-300">Server</strong> y el <strong class="text-gray-300">Stream Key</strong>
            en OBS → Settings → Stream → Service: Custom. Luego haz click en
            <strong class="text-gray-300">Ir en vivo</strong> abajo.
          </p>
        </div>
      </section>

      <!-- Lista de streams -->
      <section>
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-white">Streams</h2>
          <button
            @click="loadStreams"
            class="text-xs px-3 py-1.5 bg-gray-800 hover:bg-gray-700 rounded-lg text-gray-400 transition-colors"
          >
            Actualizar
          </button>
        </div>

        <div v-if="streamsLoading" class="text-center py-12 text-gray-600 text-sm">
          Cargando…
        </div>

        <div v-else-if="streams.length === 0" class="text-center py-12 text-gray-600 text-sm bg-gray-900 border border-gray-800 rounded-2xl">
          No hay streams todavía. Crea uno arriba.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="stream in streams"
            :key="stream.id"
            class="bg-gray-900 border border-gray-800 rounded-xl p-5"
          >
            <div class="flex items-start justify-between gap-4 flex-wrap">
              <!-- Info -->
              <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <h3 class="font-medium text-white text-sm">{{ stream.title }}</h3>
                  <span
                    :class="statusColor(stream.status)"
                    class="text-xs px-2 py-0.5 rounded-full border font-medium"
                  >
                    {{ statusLabel(stream.status) }}
                    <span v-if="stream.status === 'live'" class="inline-block w-1.5 h-1.5 bg-red-400 rounded-full animate-ping ml-1"></span>
                  </span>
                </div>
                <p class="text-xs text-gray-500">
                  {{ new Date(stream.scheduled_at).toLocaleString('es-CO', { dateStyle: 'medium', timeStyle: 'short' }) }}
                  · ID #{{ stream.id }}
                  <span v-if="stream.platform === 'cloudflare'" class="ml-1 text-orange-400">· Cloudflare</span>
                </p>
              </div>

              <!-- Acciones -->
              <div class="flex items-center gap-2 shrink-0">
                <!-- Ir en vivo -->
                <button
                  v-if="stream.status === 'scheduled'"
                  @click="goLive(stream.id)"
                  :disabled="actionLoading === stream.id"
                  class="flex items-center gap-1.5 bg-red-600 hover:bg-red-500 disabled:opacity-50 text-white text-xs font-medium px-4 py-2 rounded-lg transition-colors"
                >
                  <span class="w-2 h-2 bg-white rounded-full"></span>
                  {{ actionLoading === stream.id ? '…' : 'Ir en vivo' }}
                </button>

                <!-- Terminar -->
                <button
                  v-if="stream.status === 'live'"
                  @click="endStream(stream.id)"
                  :disabled="actionLoading === stream.id"
                  class="bg-gray-700 hover:bg-gray-600 disabled:opacity-50 text-white text-xs font-medium px-4 py-2 rounded-lg transition-colors"
                >
                  {{ actionLoading === stream.id ? '…' : 'Terminar directo' }}
                </button>

                <!-- Eliminar -->
                <button
                  v-if="stream.status !== 'live'"
                  @click="deleteStream(stream.id)"
                  :disabled="actionLoading === stream.id"
                  class="text-gray-600 hover:text-red-400 disabled:opacity-50 text-xs px-2 py-2 rounded-lg transition-colors"
                  title="Eliminar stream"
                >
                  ✕
                </button>
              </div>
            </div>

            <!-- iframe URL si está en vivo -->
            <div v-if="stream.status === 'live' && stream.iframe_url" class="mt-4 pt-4 border-t border-gray-800">
              <p class="text-xs text-gray-500 mb-1.5">URL del player en vivo</p>
              <div class="flex items-center gap-2">
                <code class="flex-1 bg-gray-800 text-indigo-300 text-xs px-3 py-2 rounded-lg font-mono break-all">
                  {{ stream.iframe_url }}
                </code>
                <button
                  @click="copyToClipboard(stream.iframe_url!, `iframe_${stream.id}`)"
                  class="shrink-0 text-xs px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors"
                >
                  {{ copied === `iframe_${stream.id}` ? '✓' : 'Copiar' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>
