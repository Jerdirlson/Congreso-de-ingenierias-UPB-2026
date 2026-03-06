<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiButton from '../../components/ui/UiButton.vue'

type LlmStatus = 'approved' | 'rejected'

interface SubmissionAbstract {
  llm_status: LlmStatus
  llm_justification: string
  llm_axis?: { name: string }
  llm_confidence_score?: number
}

interface SubmissionResult {
  id: number
  status: string
  thematic_axis?: { id: number; name: string }
  abstracts?: SubmissionAbstract[]
}

const router = useRouter()
const api = useFetchApi()

const abstractContent = ref('')
const result = ref<SubmissionResult | null>(null)
const errorMessage = ref('')

const charCount = computed(() => abstractContent.value.length)
const isValid = computed(() => abstractContent.value.trim().length >= 100)

const latestAbstract = computed(() => {
  const abs = result.value?.abstracts
  return abs?.length ? abs[0] : null
})

const isApproved = computed(() => latestAbstract.value?.llm_status === 'approved')
const isRejected = computed(() => latestAbstract.value?.llm_status === 'rejected')

async function submit() {
  errorMessage.value = ''
  result.value = null

  const data = await api.post<{ submission: SubmissionResult }>('/submissions', {
    abstract: abstractContent.value,
  })

  if (data) {
    result.value = data.submission
  } else {
    errorMessage.value = api.error.value?.message ?? 'Error al enviar el resumen'
  }
}

function goToDetail() {
  router.push({ name: 'ponente-detail', params: { id: result.value!.id } })
}

function retry() {
  result.value = null
  abstractContent.value = ''
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
        Escribe el resumen de tu ponencia. Una IA lo analizará y lo clasificará dentro de los ejes temáticos del congreso.
      </p>
    </div>

    <!-- Resultado IA: aprobado -->
    <UiCard v-if="isApproved" class="p-6 border-green-500/30 bg-green-500/5 mb-4">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
          <p class="text-green-400 font-semibold mb-1">Resumen clasificado correctamente</p>
          <p class="text-sm text-cgr-muted mb-2">
            Tu ponencia ha sido asignada al eje temático:
          </p>
          <p class="text-white font-bold text-base mb-3">
            {{ latestAbstract?.llm_axis?.name ?? result?.thematic_axis?.name }}
          </p>
          <p v-if="latestAbstract?.llm_justification" class="text-xs text-cgr-subtle italic mb-4">
            "{{ latestAbstract.llm_justification }}"
          </p>
          <UiButton @click="goToDetail">
            Continuar con mi ponencia →
          </UiButton>
        </div>
      </div>
    </UiCard>

    <!-- Resultado IA: rechazado -->
    <UiCard v-else-if="isRejected" class="p-6 border-red-500/30 bg-red-500/5 mb-4">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
          <p class="text-red-400 font-semibold mb-1">Resumen no clasificado</p>
          <p class="text-sm text-cgr-muted mb-2">
            La IA no pudo asociar tu resumen con ninguno de los ejes temáticos del congreso.
          </p>
          <p v-if="latestAbstract?.llm_justification" class="text-sm text-cgr-muted bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 mb-4">
            <strong class="text-white">Motivo:</strong> {{ latestAbstract.llm_justification }}
          </p>
          <p class="text-xs text-cgr-subtle mb-4">
            Reescribe tu resumen enfocándolo en las áreas de ingeniería del congreso e inténtalo de nuevo.
          </p>
          <UiButton variant="secondary" @click="retry">
            Reescribir resumen
          </UiButton>
        </div>
      </div>
    </UiCard>

    <!-- Formulario -->
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
          Analizando y clasificando con IA… esto puede tomar unos segundos.
        </div>

        <div v-else class="flex gap-3 pt-1">
          <UiButton type="submit" :disabled="!isValid">
            Enviar y clasificar
          </UiButton>
          <UiButton variant="secondary" type="button" @click="router.push({ name: 'ponente-home' })">
            Cancelar
          </UiButton>
        </div>
      </form>
    </UiCard>
  </div>
</template>
