<script setup lang="ts">
import UiCard from '../../components/ui/UiCard.vue'
import UpbRegistrationOptions from '../../components/UpbRegistrationOptions.vue'
</script>

<template>
  <div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-white mb-2">Inscripción al congreso</h1>
    <p class="text-sm text-cgr-muted mb-6">
      Elige el tipo de inscripción que corresponde a tu perfil para continuar al portal oficial de la UPB.
    </p>

    <UiCard class="p-6">
      <UpbRegistrationOptions audience="participante" />
    </UiCard>
  </div>
</template>
