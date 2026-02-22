<script setup lang="ts">
const stats = [
  { value: '+50',   label: 'Ponentes Internacionales' },
  { value: '5',     label: 'Días de Congreso' },
  { value: '12',    label: 'Líneas Temáticas' },
  { value: '+1000', label: 'Asistentes Esperados' },
]
</script>

<template>
  <section class="bg-cgr-section border-y border-cgr-border">
    <div class="max-w-7xl mx-auto px-5 lg:px-20">
      <div class="grid grid-cols-2 lg:grid-cols-4">
        <div
          v-for="(stat, i) in stats"
          :key="stat.label"
          class="flex flex-col items-center justify-center py-8 px-6 text-center"
          :class="i < stats.length - 1 ? 'border-r border-cgr-border' : ''"
        >
          <span class="text-3xl lg:text-4xl font-black text-cgr-purple">{{ stat.value }}</span>
          <span class="mt-1 text-sm text-cgr-muted font-medium">{{ stat.label }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
