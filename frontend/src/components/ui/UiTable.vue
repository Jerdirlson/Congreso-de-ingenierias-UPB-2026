<script setup lang="ts">
</script>

<template>
  <div class="overflow-x-auto rounded-xl border border-cgr-border">
    <table class="w-full text-sm text-left">
      <thead class="bg-cgr-section text-cgr-muted uppercase">
        <slot name="header" />
      </thead>
      <tbody class="divide-y divide-cgr-border">
        <slot />
      </tbody>
    </table>
  </div>
</template>
