<script setup lang="ts">
import LogoUpb from '../components/LogoUpb.vue'
</script>

<template>
  <div
    class="min-h-screen bg-cgr-bg font-sans text-white relative flex items-center justify-center overflow-hidden"
    style="background: linear-gradient(135deg, #1a1035 0%, #0A0A1A 40%, #1a0a2e 100%);"
  >
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cgr-purple-deep/30 rounded-full blur-3xl pointer-events-none" />
    <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-cgr-purple-dark/20 rounded-full blur-3xl pointer-events-none" />

    <div class="relative z-10 w-full max-w-md px-5">
      <RouterLink to="/" class="flex items-center justify-center gap-3 mb-8">
        <LogoUpb class="h-8 w-auto" />
        <div class="border-l border-cgr-border pl-4 text-left">
          <p class="text-white font-semibold text-sm leading-tight">Congreso Internacional</p>
          <p class="text-cgr-purple text-xs font-normal">de Ingeniería · 2026</p>
        </div>
      </RouterLink>
      <div class="bg-cgr-card border border-cgr-border rounded-2xl p-8">
        <slot />
      </div>
    </div>
  </div>
</template>
