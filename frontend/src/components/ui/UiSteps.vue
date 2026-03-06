<script setup lang="ts">
defineProps<{
  steps: { label: string; key: string }[]
  current: number
}>()
</script>

<template>
  <nav class="flex items-center justify-between mb-8" aria-label="Progreso">
    <ol class="flex items-center w-full">
      <li
        v-for="(step, index) in steps"
        :key="step.key"
        class="flex items-center"
        :class="index < steps.length - 1 ? 'flex-1' : ''"
      >
        <div class="flex flex-col items-center flex-1">
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm border-2 transition-colors"
            :class="
              index < current
                ? 'bg-cgr-purple border-cgr-purple text-white'
                : index === current
                  ? 'border-cgr-purple text-cgr-purple bg-transparent'
                  : 'border-cgr-border text-cgr-subtle bg-transparent'
            "
          >
            {{ index < current ? '✓' : index + 1 }}
          </div>
          <span
            class="mt-2 text-xs font-medium"
            :class="index <= current ? 'text-white' : 'text-cgr-subtle'"
          >
            {{ step.label }}
          </span>
        </div>
        <div
          v-if="index < steps.length - 1"
          class="flex-1 h-0.5 mx-2"
          :class="index < current ? 'bg-cgr-purple' : 'bg-cgr-border'"
        />
      </li>
    </ol>
  </nav>
</template>
