<script setup lang="ts">
interface Props {
  streamId: string
  iframeUrl?: string | null
  hlsUrl?: string | null
  platformUrl?: string | null
  platform?: string
  title?: string
  status?: 'scheduled' | 'live' | 'ended' | 'cancelled'
}

const props = withDefaults(defineProps<Props>(), {
  platform: 'cloudflare',
  status: 'scheduled',
})

function getPlayerUrl(): string | null {
  if (props.iframeUrl) return props.iframeUrl
  if (props.hlsUrl) return props.hlsUrl
  if (props.platformUrl) return props.platformUrl
  return null
}

const playerUrl = getPlayerUrl()
</script>

<template>
  <div class="relative w-full overflow-hidden rounded-xl bg-black" style="aspect-ratio: 16/9;">
    <!-- Live badge -->
    <div
      v-if="status === 'live'"
      class="absolute top-3 left-3 z-20 flex items-center gap-1.5 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg"
    >
      <span class="w-2 h-2 rounded-full bg-white animate-pulse" />
      EN VIVO
    </div>

    <!-- Player iframe -->
    <iframe
      v-if="playerUrl && (status === 'live' || status === 'ended')"
      :src="playerUrl"
      :title="title ?? 'Transmisión en vivo'"
      class="absolute inset-0 w-full h-full"
      allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture"
      allowfullscreen
      style="border: none;"
    />

    <!-- Scheduled overlay -->
    <div
      v-else-if="status === 'scheduled'"
      class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-cgr-bg to-cgr-section"
    >
      <svg class="w-16 h-16 text-cgr-purple mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="text-white font-semibold text-lg">Transmisión programada</p>
      <p class="text-cgr-muted text-sm mt-1">La transmisión comenzará pronto</p>
    </div>

    <!-- Ended / No player overlay -->
    <div
      v-else-if="status === 'ended' && !playerUrl"
      class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-cgr-bg to-cgr-section"
    >
      <svg class="w-16 h-16 text-cgr-muted mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9.563C9 9.252 9.252 9 9.563 9h4.874c.311 0 .563.252.563.563v4.874c0 .311-.252.563-.563.563H9.564A.562.562 0 019 14.437V9.564z"/>
      </svg>
      <p class="text-white font-semibold text-lg">Transmisión finalizada</p>
      <p class="text-cgr-muted text-sm mt-1">La grabación estará disponible pronto</p>
    </div>

    <!-- Cancelled overlay -->
    <div
      v-else-if="status === 'cancelled'"
      class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-cgr-bg to-cgr-section"
    >
      <svg class="w-16 h-16 text-cgr-muted mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
      </svg>
      <p class="text-white font-semibold text-lg">Transmisión cancelada</p>
    </div>
  </div>
</template>
