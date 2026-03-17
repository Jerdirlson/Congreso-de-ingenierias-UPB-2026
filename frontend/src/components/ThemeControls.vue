<script setup lang="ts">
import type { FontSize } from '../composables/useTheme'
import { useTheme } from '../composables/useTheme'

const { theme, fontSize, toggleTheme, setFontSize } = useTheme()

const fontSizes: FontSize[] = ['small', 'normal', 'large']
const fontLabels: Record<FontSize, string> = { small: 'A−', normal: 'A', large: 'A+' }
</script>

<template>
  <div class="flex items-center gap-2" role="group" aria-label="Controles de accesibilidad">

    <!-- Tamaño de fuente -->
    <div class="flex items-center border border-cgr-border rounded-lg overflow-hidden" role="group" aria-label="Tamaño de fuente">
      <button
        v-for="s in fontSizes"
        :key="s"
        @click="setFontSize(s)"
        :class="[
          'px-2 py-1.5 text-xs leading-none transition-colors',
          fontSize === s
            ? 'bg-cgr-purple/20 text-cgr-purple font-semibold'
            : 'text-cgr-muted hover:text-cgr-purple hover:bg-cgr-card'
        ]"
        :aria-label="`Tamaño de fuente: ${s}`"
        :aria-pressed="fontSize === s"
      >{{ fontLabels[s] }}</button>
    </div>

    <!-- Modo claro / oscuro -->
    <button
      @click="toggleTheme"
      class="p-1.5 rounded-lg border border-cgr-border text-cgr-muted hover:text-cgr-purple hover:border-cgr-purple/50 transition-colors"
      :aria-label="theme === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
    >
      <!-- Sol: modo oscuro activo → clic cambia a claro -->
      <svg v-if="theme === 'dark'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="5"/>
        <path stroke-linecap="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
      </svg>
      <!-- Luna: modo claro activo → clic cambia a oscuro -->
      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
      </svg>
    </button>

  </div>
</template>
