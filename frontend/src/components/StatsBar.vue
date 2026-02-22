<script setup lang="ts">
import { useScrollReveal } from '../composables/useScrollReveal'

const { setRef } = useScrollReveal()

const stats = [
  { value: '5',     label: 'Días de Congreso' },
  { value: '8',     label: 'Ejes Temáticos' },
  { value: '6',     label: 'Facultades de Ingeniería' },
  { value: 'Híbrido', label: 'Presencial + Virtual' },
]
</script>

<template>
  <section class="bg-cgr-section border-y border-cgr-border">
    <div class="max-w-7xl mx-auto px-5 lg:px-20">
      <div class="grid grid-cols-2 lg:grid-cols-4">
        <div
          v-for="(stat, i) in stats"
          :key="stat.label"
          :ref="(el) => setRef(el as Element, i)"
          class="animate-reveal flex flex-col items-center justify-center py-8 px-6 text-center hover:scale-105 transition-transform duration-300"
          :class="i < stats.length - 1 ? 'border-r border-cgr-border' : ''"
        >
          <span class="text-3xl lg:text-4xl font-black text-cgr-purple">{{ stat.value }}</span>
          <span class="mt-1 text-sm text-cgr-muted font-medium">{{ stat.label }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
