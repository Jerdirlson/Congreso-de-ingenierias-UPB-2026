<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'primary' | 'secondary' | 'ghost' | 'danger'
    type?: 'button' | 'submit' | 'reset'
    loading?: boolean
    disabled?: boolean
    size?: 'sm' | 'md' | 'lg'
  }>(),
  { variant: 'primary', type: 'button', loading: false, disabled: false, size: 'md' }
)
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center font-semibold rounded-lg transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
    :class="[
      size === 'sm' && 'px-3 py-1.5 text-xs',
      size === 'md' && 'px-5 py-2.5 text-sm',
      size === 'lg' && 'px-8 py-3 text-base',
      variant === 'primary' && 'bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white hover:opacity-90',
      variant === 'secondary' && 'border border-cgr-border text-white hover:border-cgr-purple bg-transparent',
      variant === 'ghost' && 'text-cgr-muted hover:text-white hover:bg-cgr-card',
      variant === 'danger' && 'bg-red-600/20 text-red-400 border border-red-500/30 hover:bg-red-600/30',
    ]"
  >
    <span v-if="loading" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
    <slot />
  </button>
</template>
