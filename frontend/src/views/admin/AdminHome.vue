<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'

const api = useFetchApi()

const stats = ref({
  submissions: 0,
  pendingReview: 0,
  confirmed: 0,
})

async function loadStats() {
  const data = await api.get<{ data: { status: string }[]; total?: number }>('/admin/submissions?per_page=100')
  if (data && 'data' in data) {
    const list = (data as { data: { status: string }[] }).data
    const total = (data as { total?: number }).total ?? list.length
    stats.value = {
      submissions: total,
      pendingReview: list.filter((s: { status: string }) => ['under_review', 'abstract_approved'].includes(s.status)).length,
      confirmed: list.filter((s: { status: string }) => s.status === 'confirmed').length,
    }
  }
}

onMounted(loadStats)
</script>

<template>
  <div class="max-w-4xl">
    <h1 class="text-2xl font-bold text-white mb-8">Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <UiCard class="p-6">
        <p class="text-cgr-muted text-sm mb-1">Total ponencias</p>
        <p class="text-2xl font-bold text-white">{{ stats.submissions }}</p>
      </UiCard>
      <UiCard class="p-6">
        <p class="text-cgr-muted text-sm mb-1">En revisión</p>
        <p class="text-2xl font-bold text-cgr-purple">{{ stats.pendingReview }}</p>
      </UiCard>
      <UiCard class="p-6">
        <p class="text-cgr-muted text-sm mb-1">Confirmadas</p>
        <p class="text-2xl font-bold text-green-400">{{ stats.confirmed }}</p>
      </UiCard>
    </div>

    <div class="flex gap-3">
      <RouterLink :to="{ name: 'admin-submissions' }">
        <button class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-5 py-2.5 rounded-lg hover:opacity-90 transition-opacity text-sm">
          Ver ponencias
        </button>
      </RouterLink>
      <RouterLink :to="{ name: 'admin-axes' }">
        <button class="border border-cgr-border text-white font-semibold px-5 py-2.5 rounded-lg hover:border-cgr-purple transition-colors text-sm">
          Ejes temáticos
        </button>
      </RouterLink>
    </div>
  </div>
</template>
