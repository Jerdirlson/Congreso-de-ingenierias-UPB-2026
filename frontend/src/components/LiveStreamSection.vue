<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useFetchApi } from '../composables/useFetchApi'
import LiveStreamPlayer from './LiveStreamPlayer.vue'

interface Stream {
  id: number
  title: string
  slug: string
  description: string | null
  status: 'scheduled' | 'live' | 'ended' | 'cancelled'
  platform: string
  iframe_url: string | null
  playback_url: string | null
  hls_url: string | null
  platform_url: string | null
  scheduled_at: string
  started_at: string | null
  ended_at: string | null
  speaker?: { id: number; name: string; photo: string | null }
  event?: { id: number; title: string }
}

interface PaginatedResponse {
  data: Stream[]
  current_page: number
  last_page: number
  total: number
}

const { get, loading } = useFetchApi()

const streams     = ref<Stream[]>([])
const activeStream = ref<Stream | null>(null)
const hasLive     = computed(() => streams.value.some(s => s.status === 'live'))
let pollTimer: ReturnType<typeof setInterval> | null = null

async function fetchStreams() {
  const res = await get<PaginatedResponse>('/streams?status=live')
  const live = res?.data ?? []

  if (live.length > 0) {
    streams.value = live
    activeStream.value = live[0] ?? null
  } else {
    const scheduled = await get<PaginatedResponse>('/streams?status=scheduled')
    const upcoming = scheduled?.data ?? []
    streams.value = upcoming.slice(0, 3)
    activeStream.value = upcoming[0] ?? null
  }
}

function selectStream(stream: Stream) {
  activeStream.value = stream
}

function formatDate(dateStr: string): string {
  const d = new Date(dateStr)
  return d.toLocaleDateString('es-CO', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function statusLabel(status: string): string {
  const labels: Record<string, string> = {
    live: 'En vivo',
    scheduled: 'Programada',
    ended: 'Finalizada',
    cancelled: 'Cancelada',
  }
  return labels[status] ?? status
}

function statusClasses(status: string): string {
  const classes: Record<string, string> = {
    live: 'bg-red-600 text-white',
    scheduled: 'bg-cgr-purple-dark/30 text-cgr-accent',
    ended: 'bg-cgr-border text-cgr-muted',
    cancelled: 'bg-cgr-border text-cgr-subtle',
  }
  return classes[status] ?? 'bg-cgr-border text-cgr-muted'
}

onMounted(() => {
  fetchStreams()
  pollTimer = setInterval(fetchStreams, 30_000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>

<template>
  <section
    v-if="activeStream"
    id="envivo"
    class="py-20 bg-cgr-section"
  >
    <div class="max-w-7xl mx-auto px-5 lg:px-20">
      <!-- Header -->
      <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 border border-cgr-purple-dark rounded-full px-4 py-1.5 mb-4">
          <span
            class="w-2 h-2 rounded-full"
            :class="hasLive ? 'bg-red-500 animate-pulse' : 'bg-cgr-purple animate-pulse'"
          />
          <span class="text-cgr-accent text-xs font-semibold tracking-widest uppercase">
            {{ hasLive ? 'Transmisión en vivo' : 'Próximas transmisiones' }}
          </span>
        </div>
        <h2 class="text-3xl sm:text-4xl font-bold text-white">
          {{ hasLive ? 'En vivo ahora' : 'Transmisiones' }}
        </h2>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Player principal -->
        <div class="lg:col-span-2">
          <LiveStreamPlayer
            :stream-id="String(activeStream.id)"
            :iframe-url="activeStream.iframe_url"
            :hls-url="activeStream.hls_url"
            :platform-url="activeStream.platform_url"
            :platform="activeStream.platform"
            :title="activeStream.title"
            :status="activeStream.status"
          />

          <div class="mt-4">
            <h3 class="text-xl font-bold text-white">{{ activeStream.title }}</h3>
            <div class="flex items-center gap-3 mt-2">
              <span
                class="text-xs font-semibold px-2.5 py-0.5 rounded-full"
                :class="statusClasses(activeStream.status)"
              >
                {{ statusLabel(activeStream.status) }}
              </span>
              <span v-if="activeStream.speaker" class="text-cgr-muted text-sm">
                {{ activeStream.speaker.name }}
              </span>
              <span class="text-cgr-subtle text-sm">
                {{ formatDate(activeStream.scheduled_at) }}
              </span>
            </div>
            <p v-if="activeStream.description" class="text-cgr-muted text-sm mt-3 leading-relaxed">
              {{ activeStream.description }}
            </p>
          </div>
        </div>

        <!-- Lista lateral de streams -->
        <div class="space-y-3">
          <h4 class="text-sm font-semibold text-cgr-muted uppercase tracking-wider mb-4">
            {{ hasLive ? 'Otras transmisiones' : 'Programadas' }}
          </h4>

          <button
            v-for="stream in streams"
            :key="stream.id"
            class="w-full text-left rounded-lg p-4 border transition-all"
            :class="activeStream?.id === stream.id
              ? 'bg-cgr-card border-cgr-purple-dark'
              : 'bg-cgr-card/50 border-cgr-border hover:border-cgr-purple-dark/50'"
            @click="selectStream(stream)"
          >
            <div class="flex items-start gap-3">
              <span
                class="shrink-0 mt-0.5 text-xs font-semibold px-2 py-0.5 rounded-full"
                :class="statusClasses(stream.status)"
              >
                {{ statusLabel(stream.status) }}
              </span>
              <div class="min-w-0">
                <p class="text-white font-medium text-sm truncate">{{ stream.title }}</p>
                <p v-if="stream.speaker" class="text-cgr-subtle text-xs mt-0.5">
                  {{ stream.speaker.name }}
                </p>
                <p class="text-cgr-subtle text-xs mt-0.5">
                  {{ formatDate(stream.scheduled_at) }}
                </p>
              </div>
            </div>
          </button>

          <div v-if="streams.length === 0 && !loading" class="text-cgr-subtle text-sm text-center py-8">
            No hay transmisiones programadas por el momento.
          </div>

          <div v-if="loading && streams.length === 0" class="flex justify-center py-8">
            <div class="w-8 h-8 border-2 border-cgr-purple border-t-transparent rounded-full animate-spin" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
