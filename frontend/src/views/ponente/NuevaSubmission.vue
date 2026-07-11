<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFetchApi } from '../../composables/useFetchApi'
import { useSettingsStore } from '../../stores/settings'
import UiCard from '../../components/ui/UiCard.vue'
import UiButton from '../../components/ui/UiButton.vue'

interface ThematicAxis {
  id: number
  name: string
  description?: string
}

interface Author {
  name: string
  affiliation: string
  email: string
}

interface SubmissionResult {
  id: number
  status: string
}

const router = useRouter()
const api = useFetchApi()
const axisApi = useFetchApi()
const settings = useSettingsStore()

const submissionsClosed = computed(() => settings.loaded && !settings.submissionsOpen)

const errorMessage = ref('')
const axes = ref<ThematicAxis[]>([])
const selectedAxisId = ref<number | null>(null)

const title = ref('')
const authors = ref<Author[]>([{ name: '', affiliation: '', email: '' }])
const keywords = ref('')
const abstractFile = ref<File | null>(null)
const abstractFileError = ref('')

const keywordList = computed(() =>
  keywords.value.split(',').map(k => k.trim()).filter(Boolean)
)

const keywordsValid = computed(() =>
  keywordList.value.length >= 3 && keywordList.value.length <= 6
)

const isValid = computed(() => {
  if (!title.value.trim()) return false
  if (!selectedAxisId.value) return false
  if (!keywordsValid.value) return false
  if (!abstractFile.value) return false
  const first = authors.value[0]
  if (!first?.name.trim() || !first?.affiliation.trim() || !first?.email.trim()) return false
  for (let i = 1; i < authors.value.length; i++) {
    const co = authors.value[i]
    if (!co || !co.name.trim() || !co.affiliation.trim()) return false
  }
  return true
})

onMounted(async () => {
  await settings.fetch()
  if (submissionsClosed.value) return
  const data = await axisApi.get<{ data: ThematicAxis[] } | ThematicAxis[]>('/thematic-axes')
  if (data) {
    axes.value = Array.isArray(data) ? data : data.data
  }
})

function addAuthor() {
  authors.value.push({ name: '', affiliation: '', email: '' })
}

function removeAuthor(index: number) {
  authors.value.splice(index, 1)
}

function onFileChange(e: Event) {
  abstractFileError.value = ''
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  if (!file) { abstractFile.value = null; return }
  const lower = file.name.toLowerCase()
  if (!(lower.endsWith('.docx') || lower.endsWith('.pdf'))) {
    abstractFileError.value = 'Solo se permiten archivos .docx o .pdf'
    abstractFile.value = null
    return
  }
  if (file.size > 10 * 1024 * 1024) {
    abstractFileError.value = 'El archivo no debe superar 10 MB'
    abstractFile.value = null
    return
  }
  abstractFile.value = file
}

async function submit() {
  errorMessage.value = ''

  const formData = new FormData()
  formData.append('title', title.value.trim())
  formData.append('thematic_axis_id', String(selectedAxisId.value))
  formData.append('keywords', keywords.value.trim())

  authors.value.forEach((author, i) => {
    formData.append(`authors[${i}][name]`, author.name.trim())
    formData.append(`authors[${i}][affiliation]`, author.affiliation.trim())
    if (author.email.trim()) formData.append(`authors[${i}][email]`, author.email.trim())
  })

  if (abstractFile.value) {
    formData.append('abstract_file', abstractFile.value)
  }

  const data = await api.postForm<{ submission: SubmissionResult }>('/submissions', formData)

  if (data) {
    router.push({ name: 'ponente-detail', params: { id: data.submission.id } })
  } else {
    errorMessage.value = api.error.value?.message ?? 'Error al enviar la ponencia'
  }
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
        Completa el formulario con los datos de tu ponencia y selecciona el eje temático.
      </p>
    </div>

    <!-- Periodo de subida de ponencias cerrado -->
    <UiCard v-if="submissionsClosed" class="p-8 text-center">
      <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-amber-500/15 flex items-center justify-center">
        <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h2 class="text-lg font-bold text-white mb-2">Se acabó el tiempo para subir ponencias</h2>
      <p class="text-sm text-cgr-muted max-w-md mx-auto mb-6">
        El periodo para registrar nuevas ponencias ya finalizó, por lo que no es posible
        crear una ponencia nueva. Si ya tenías una ponencia en proceso, puedes continuar
        con ella desde "Mis ponencias".
      </p>
      <UiButton variant="secondary" @click="router.push({ name: 'ponente-home' })">
        Volver a mis ponencias
      </UiButton>
    </UiCard>

    <UiCard v-else class="p-6">
      <form @submit.prevent="submit" class="space-y-6">

        <!-- Título -->
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">
            Título de la ponencia <span class="text-red-400">*</span>
          </label>
          <input
            v-model="title"
            type="text"
            placeholder="Ej: Diseño de un sistema robótico colaborativo para entornos industriales humanocéntricos"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
          />
        </div>

        <!-- Eje temático -->
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-2">
            Eje temático <span class="text-red-400">*</span>
          </label>
          <div class="space-y-2">
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
                </span>
                <span v-if="axis.description" class="block text-xs text-cgr-subtle mt-0.5">{{ axis.description }}</span>
              </span>
            </label>
          </div>
        </div>

        <!-- Autores -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="text-xs font-medium text-cgr-muted">
              Autores <span class="text-red-400">*</span>
            </label>
            <button
              type="button"
              @click="addAuthor"
              class="text-xs text-cgr-purple hover:text-cgr-accent transition-colors flex items-center gap-1"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Agregar coautor
            </button>
          </div>

          <div class="space-y-3">
            <div
              v-for="(author, i) in authors"
              :key="i"
              class="bg-cgr-bg border border-cgr-border rounded-xl p-4 space-y-3"
            >
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-cgr-purple uppercase tracking-wider">
                  {{ i === 0 ? 'Autor principal' : `Coautor ${i}` }}
                </span>
                <button
                  v-if="i > 0"
                  type="button"
                  @click="removeAuthor(i)"
                  class="text-cgr-subtle hover:text-red-400 transition-colors"
                  aria-label="Eliminar coautor"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs text-cgr-subtle mb-1">
                    Nombre completo <span class="text-red-400">*</span>
                  </label>
                  <input
                    v-model="author.name"
                    type="text"
                    placeholder="Nombre y apellidos"
                    class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
                  />
                </div>
                <div>
                  <label class="block text-xs text-cgr-subtle mb-1">
                    Afiliación <span class="text-red-400">*</span>
                  </label>
                  <input
                    v-model="author.affiliation"
                    type="text"
                    placeholder="Universidad / Empresa / Institución"
                    class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
                  />
                </div>
              </div>

              <div>
                <label class="block text-xs text-cgr-subtle mb-1">
                  Correo electrónico
                  <span v-if="i === 0" class="text-red-400">*</span>
                  <span v-else class="text-cgr-subtle ml-1">(opcional)</span>
                </label>
                <input
                  v-model="author.email"
                  type="email"
                  placeholder="correo@ejemplo.com"
                  class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Palabras clave -->
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">
            Palabras clave <span class="text-red-400">*</span>
            <span class="ml-2 text-cgr-subtle font-normal">(mínimo 3, máximo 6, separadas por coma)</span>
          </label>
          <input
            v-model="keywords"
            type="text"
            placeholder="Ej: robótica colaborativa, cobots, manufactura avanzada"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2.5 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple transition-colors"
            :class="{ 'border-red-500/50': keywords.length > 0 && !keywordsValid }"
          />
          <div class="mt-1.5 flex items-start justify-between gap-2">
            <p v-if="keywords.length > 0 && !keywordsValid" class="text-xs text-red-400">
              {{ keywordList.length < 3 ? 'Ingresa al menos 3 palabras clave' : 'Máximo 6 palabras clave permitidas' }}
            </p>
            <p v-else-if="keywords.length > 0 && keywordsValid" class="text-xs text-green-400 shrink-0">
              {{ keywordList.length }} palabras clave
            </p>
            <div v-if="keywordList.length > 0" class="flex gap-1.5 flex-wrap justify-end ml-auto">
              <span
                v-for="kw in keywordList"
                :key="kw"
                class="text-[10px] bg-cgr-purple/10 text-cgr-purple border border-cgr-purple/20 rounded-full px-2 py-0.5"
              >
                {{ kw }}
              </span>
            </div>
          </div>
        </div>

        <!-- Archivo del resumen -->
        <div>
          <label class="block text-xs font-medium text-cgr-muted mb-1.5">
            Archivo del resumen (Word o PDF) <span class="text-red-400">*</span>
            <span class="ml-2 text-cgr-subtle font-normal">.docx o .pdf · máximo 10 MB</span>
          </label>
          <label
            class="flex items-center gap-3 w-full bg-cgr-section border rounded-lg px-3 py-2.5 cursor-pointer transition-colors"
            :class="abstractFileError
              ? 'border-red-500/50'
              : abstractFile
                ? 'border-cgr-purple/50 bg-cgr-purple/5 hover:border-cgr-purple'
                : 'border-cgr-border hover:border-cgr-purple'"
          >
            <svg class="w-5 h-5 shrink-0" :class="abstractFile ? 'text-cgr-purple' : 'text-cgr-muted'" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            <span class="text-sm flex-1 truncate" :class="abstractFile ? 'text-white' : 'text-cgr-subtle'">
              {{ abstractFile ? abstractFile.name : 'Seleccionar archivo .docx o .pdf…' }}
            </span>
            <span v-if="abstractFile" class="text-xs text-cgr-muted shrink-0">
              {{ (abstractFile.size / 1024 / 1024).toFixed(2) }} MB
            </span>
            <input type="file" accept=".docx,.pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf" class="hidden" @change="onFileChange" />
          </label>
          <p v-if="abstractFileError" class="mt-1 text-xs text-red-400">{{ abstractFileError }}</p>
          <p class="mt-1.5 text-xs text-cgr-subtle">
            El archivo debe seguir la
            <a href="/api/docs/Plantilla_Resumen.docx" class="text-cgr-purple hover:underline">plantilla oficial</a>
            (secciones Resumen, Palabras claves, Abstract, Key Words y Referencias).
            Si no conserva esa estructura, el sistema lo rechazará.
          </p>
        </div>

        <p v-if="errorMessage" class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
          {{ errorMessage }}
        </p>

        <div class="flex gap-3 pt-1">
          <UiButton type="submit" :disabled="!isValid" :loading="api.loading.value">
            Enviar ponencia
          </UiButton>
          <UiButton variant="secondary" type="button" @click="router.push({ name: 'ponente-home' })">
            Cancelar
          </UiButton>
        </div>
      </form>
    </UiCard>
  </div>
</template>
