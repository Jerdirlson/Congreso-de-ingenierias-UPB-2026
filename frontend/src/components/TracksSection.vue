<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useScrollReveal } from '../composables/useScrollReveal'
import { useFetchApi } from '../composables/useFetchApi'

const { setRef } = useScrollReveal()
const api = useFetchApi()

interface ThematicAxis {
  id: number
  name: string
  description: string | null
  keywords: string | null
  is_active: boolean
}

const axesFromApi = ref<ThematicAxis[]>([])

const defaultTracks = [
  { number: '01', title: 'Ingeniería Aplicada con Propósito Humano', description: 'Diseño centrado en el usuario, ergonomía cognitiva, biomecánica, tecnologías de asistencia e inclusión.' },
  { number: '02', title: 'Tecnologías 5.0', description: 'IA, Robótica colaborativa, Gemelos Digitales, IoT, Edge Computing, IA Explicable y Sistemas Ciber-Físico-Sociales.' },
  { number: '03', title: 'Sostenibilidad y Logística Verde', description: 'Energías limpias, economía circular, descarbonización, transición energética y movilidad inteligente.' },
  { number: '04', title: 'Innovación y Transformación Digital', description: 'Nuevos modelos de negocio, manufactura avanzada, Servitización, gestión del ciclo de vida del producto.' },
  { number: '05', title: 'Gobernanza y Ciberseguridad', description: 'Ética de datos, protección de infraestructuras críticas, soberanía digital y Privacidad por Diseño.' },
  { number: '06', title: 'Retos Regionales y Sector Productivo', description: 'Transformación local, cierre de brechas tecnológicas e innovación social de base tecnológica.' },
  { number: '07', title: 'Desarrollo Tecnológico, Sociedad y Educación', description: 'Impacto de tecnologías emergentes, estrategias pedagógicas, EdTech y apropiación social del conocimiento.' },
  { number: '08', title: 'Ingeniería de Software Inteligente', description: 'DevOps/DevSecOps, MLOps, AIOps, arquitecturas cloud-native y Platform Engineering.' },
]

const tracks = computed(() => {
  const active = axesFromApi.value.filter((a) => a.is_active)
  if (active.length === 0) return defaultTracks
  return active.map((a, i) => ({
    number: String(i + 1).padStart(2, '0'),
    title: a.name,
    description: a.description ?? '',
  }))
})

onMounted(async () => {
  const data = await api.get<ThematicAxis[]>('/thematic-axes')
  if (data && Array.isArray(data)) axesFromApi.value = data
})
</script>

<template>
  <section id="ejes" class="bg-cgr-section py-24 px-5 lg:px-20">
    <div class="max-w-7xl mx-auto">

      <div class="text-center mb-16">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Ejes temáticos</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-black text-white">
          {{ tracks.length }} Tracks de Investigación
        </h2>
        <p class="mt-4 text-cgr-muted max-w-xl mx-auto text-base leading-relaxed">
          Nuestra agenda científica se articula en torno a ocho pilares fundamentales que guiarán las ponencias y mesas de trabajo.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="(track, i) in tracks"
          :key="track.number"
          :ref="(el) => setRef(el as Element, i)"
          class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-7 hover:border-cgr-purple-dark hover:scale-[1.02] hover:shadow-lg hover:shadow-cgr-purple/10 transition-all duration-300 group"
        >
          <span class="text-cgr-purple text-3xl font-black opacity-30 group-hover:opacity-60 transition-opacity">
            {{ track.number }}
          </span>
          <h3 class="text-white font-bold text-base mt-3 mb-3 leading-snug">{{ track.title }}</h3>
          <p class="text-cgr-muted text-xs leading-relaxed">{{ track.description }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
