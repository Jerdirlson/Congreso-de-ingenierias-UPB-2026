<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiButton from '../../components/ui/UiButton.vue'

interface ThematicAxis {
  id: number
  name: string
  description?: string
}

interface SubmissionAbstract {
  llm_status: 'approved' | 'rejected' | 'pending'
  llm_justification?: string
  llm_axis?: ThematicAxis
  llm_confidence_score?: number
}

interface SubmissionResult {
  id: number
  status: string
  thematic_axis?: ThematicAxis
  abstracts?: SubmissionAbstract[]
}

const router = useRouter()
const api = useFetchApi()
const axisApi = useFetchApi()
const confirmApi = useFetchApi()

const abstractContent = ref('')
const result = ref<SubmissionResult | null>(null)
const errorMessage = ref('')
const axes = ref<ThematicAxis[]>([])
const selectedAxisId = ref<number | null>(null)

const charCount = computed(() => abstractContent.value.length)
const isValid = computed(() => abstractContent.value.trim().length >= 100)

const latestAbstract = computed(() => {
  const abs = result.value?.abstracts
  return abs?.length ? abs[0] : null
})

const aiRecommendedAxis = computed(() =>
  latestAbstract.value?.llm_axis ?? null
)

const hasHighConfidence = computed(() =>
  latestAbstract.value?.llm_status === 'approved'
)

onMounted(async () => {
  const data = await axisApi.get<{ data: ThematicAxis[] } | ThematicAxis[]>('/thematic-axes')
  if (data) {
    axes.value = Array.isArray(data) ? data : data.data
  }
})

async function submit() {
  errorMessage.value = ''
  result.value = null

  const data = await api.post<{ submission: SubmissionResult }>('/submissions', {
    abstract: abstractContent.value,
  })

  if (data) {
    result.value = data.submission
    // Pre-seleccionar eje recomendado por la IA si existe
    const recommended = data.submission.abstracts?.[0]?.llm_axis
    if (recommended) {
      selectedAxisId.value = recommended.id ?? null
    }
  } else {
    errorMessage.value = api.error.value?.message ?? 'Error al enviar el resumen'
  }
}

async function confirmAxis() {
  if (!selectedAxisId.value || !result.value) return
  errorMessage.value = ''

  const data = await confirmApi.patch<SubmissionResult>(
    `/submissions/${result.value.id}/axis`,
    { thematic_axis_id: selectedAxisId.value }
  )

  if (data) {
    router.push({ name: 'ponente-detail', params: { id: result.value.id } })
  } else {
    errorMessage.value = confirmApi.error.value?.message ?? 'Error al confirmar el eje temático'
  }
}

function retry() {
  result.value = null
  abstractContent.value = ''
  selectedAxisId.value = null
  errorMessage.value = ''
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <RouterLink :to="{ name: 'ponente-home' }" class="text-sm text-cgr-muted hover:text-white mb-4 inline-block">
        ← Volver
      </RouterLink>
      <h1 class="text-2xl font-bold text-white">Nueva ponencia</h1>
      <p class="text-sm text-cgr-muted mt-1">
        Escribe el resumen de tu ponencia. La IA lo analizará y recomendará un eje temático; puedes aceptarlo o elegir otro.
      </p>
    </div>

    <!-- Paso 2: Selección de eje (después de clasificación IA) -->
    <template v-if="result">
      <!-- Recomendación de la IA -->
      <UiCard class="p-6 mb-4">
        <h2 class="font-semibold text-white mb-3">Recomendación de la IA</h2>

        <div v-if="aiRecommendedAxis" :class="[
          'flex items-start gap-3 rounded-lg px-4 py-3 mb-3',
          hasHighConfidence
            ? 'bg-green-500/10 border border-green-500/30'
            : 'bg-amber-500/10 border border-amber-500/30'
        ]">
          <svg v-if="hasHighConfidence" class="w-5 h-5 text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <svg v-else class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          <div>
            <p :class="hasHighConfidence ? 'text-green-400 font-semibold text-sm' : 'text-amber-400 font-semibold text-sm'">
              {{ hasHighConfidence ? 'Alta confianza' : 'Confianza moderada' }} — eje sugerido:
            </p>
            <p class="text-white font-bold mt-0.5">{{ aiRecommendedAxis.name }}</p>
            <p v-if="latestAbstract?.llm_justification" class="text-xs text-cgr-subtle italic mt-1">
              "{{ latestAbstract.llm_justification }}"
            </p>
          </div>
        </div>

        <div v-else class="flex items-start gap-3 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3 mb-3">
          <svg class="w-5 h-5 text-cgr-muted shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div>
            <p class="text-cgr-muted text-sm font-medium">La IA no pudo determinar un eje con certeza</p>
            <p class="text-xs text-cgr-subtle mt-0.5">Selecciona el eje temático que mejor corresponda a tu ponencia.</p>
          </div>
        </div>
      </UiCard>

      <!-- Selector de eje temático -->
      <UiCard class="p-6 mb-4">
        <h2 class="font-semibold text-white mb-1">Confirma el eje temático</h2>
        <p class="text-xs text-cgr-muted mb-4">Puedes aceptar la sugerencia o elegir el que mejor describa tu ponencia.</p>

        <div class="space-y-2 mb-5">
          <label
            v-for="axis in axes"
            :key="axis.id"
            :class="[
              'flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors',
              selectedAxisId === axis.id
                ? 'border-cgr-purple bg-cgr-purple/10 text-white'
                : 'border-cgr-border bg-cgr-section text-cgr-muted hover:border-cgr-purple/50'
            ]"
          >
            <input type="radio" :value="axis.id" v-model="selectedAxisId" class="hidden" />
            <span class="flex-1">
              <span class="block text-sm font-medium" :class="selectedAxisId === axis.id ? 'text-white' : 'text-cgr-muted'">
                {{ axis.name }}
                <span v-if="aiRecommendedAxis?.id === axis.id" class="ml-2 text-xs font-normal text-cgr-purple">
                  ← IA recomienda
                </span>
              </span>
              <span v-if="axis.description" class="block text-xs text-cgr-subtle mt-0.5">{{ axis.description }}</span>
            </span>
          </label>
        </div>

        <p v-if="errorMessage" class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2 mb-3">
          {{ errorMessage }}
        </p>

        <div class="flex gap-3">
          <UiButton :disabled="!selectedAxisId" :loading="confirmApi.loading.value" @click="confirmAxis">
            Confirmar eje temático
          </UiButton>
          <UiButton variant="secondary" @click="retry">
            Cambiar resumen
          </UiButton>
        </div>
      </UiCard>
    </template>

    <!-- Paso 1: Formulario de resumen -->
    <UiCard v-else class="p-6">
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">
            Resumen de la ponencia
            <span class="ml-2 text-cgr-subtle">({{ charCount }} / 10 000 caracteres · mínimo 100)</span>
          </label>
          <textarea
            v-model="abstractContent"
            rows="10"
            placeholder="Describe el contenido de tu ponencia: objetivos, metodología, resultados y conclusiones principales. Incluye palabras clave relacionadas con tu área de ingeniería..."
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple resize-y transition-colors"
            :class="{ 'border-red-500/50': charCount > 0 && !isValid }"
          />
          <p v-if="charCount > 0 && !isValid" class="mt-1 text-xs text-red-400">
            El resumen debe tener al menos 100 caracteres.
          </p>
        </div>

        <p v-if="errorMessage" class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
          {{ errorMessage }}
        </p>

        <!-- Loading con mensaje IA -->
        <div v-if="api.loading.value" class="flex items-center gap-3 text-cgr-muted text-sm py-2">
          <div class="w-4 h-4 border-2 border-cgr-purple border-t-transparent rounded-full animate-spin shrink-0"></div>
          Analizando con IA… esto puede tomar unos segundos.
        </div>

        <div v-else class="flex gap-3 pt-1">
          <UiButton type="submit" :disabled="!isValid">
            Analizar con IA
          </UiButton>
          <UiButton variant="secondary" type="button" @click="router.push({ name: 'ponente-home' })">
            Cancelar
          </UiButton>
        </div>
      </form>
    </UiCard>
  </div>
</template>
