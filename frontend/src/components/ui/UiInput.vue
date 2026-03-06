<script setup lang="ts">
defineProps<{
  modelValue?: string | number
  type?: string
  label?: string
  error?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
}>()
</script>

<template>
  <div class="space-y-1.5">
    <label v-if="label" class="block text-xs font-medium text-cgr-muted">
      {{ label }}
      <span v-if="required" class="text-red-400">*</span>
    </label>
    <input
      :value="modelValue"
      :type="type ?? 'text'"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      class="w-full bg-cgr-section border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      :class="error ? 'border-red-500/50' : 'border-cgr-border'"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <p v-if="error" class="text-xs text-red-400">{{ error }}</p>
  </div>
</template>
