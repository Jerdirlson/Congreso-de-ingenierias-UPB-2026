<script setup lang="ts">
import { onMounted } from 'vue'
import { useTheme } from './composables/useTheme'

const { theme, fontSize, setTheme, setFontSize } = useTheme()

onMounted(() => {
  setTheme(theme.value)
  setFontSize(fontSize.value)
})
</script>

<template>
  <RouterView />
</template>
