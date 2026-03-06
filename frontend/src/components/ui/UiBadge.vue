<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    variant?: 'default' | 'success' | 'warning' | 'danger' | 'info' | 'purple'
    size?: 'sm' | 'md'
  }>(),
  { variant: 'default', size: 'md' }
)

const variantClasses: Record<string, string> = {
  default: 'bg-cgr-card/50 text-cgr-muted border-cgr-border',
  success: 'bg-green-500/10 text-green-400 border-green-500/30',
  warning: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
  danger: 'bg-red-500/10 text-red-400 border-red-500/30',
  info: 'bg-blue-500/10 text-blue-400 border-blue-500/30',
  purple: 'bg-cgr-purple/10 text-cgr-purple border-cgr-purple/30',
}
</script>

<template>
  <span
    class="inline-flex items-center font-medium rounded-full border"
    :class="[
      variantClasses[variant],
      size === 'sm' && 'px-2 py-0.5 text-xs',
      size === 'md' && 'px-2.5 py-1 text-xs',
    ]"
  >
    <slot />
  </span>
</template>
