<script setup lang="ts">
import { useScrollReveal } from '../composables/useScrollReveal'

const { setRef } = useScrollReveal()

const tracks = [
  {
    number: '01',
    title: 'Ingeniería Aplicada con Propósito Humano',
    description: 'Diseño centrado en el usuario, ergonomía cognitiva, biomecánica, tecnologías de asistencia e inclusión, y sistemas de seguridad proactiva.',
  },
  {
    number: '02',
    title: 'Tecnologías 5.0',
    description: 'IA, Robótica colaborativa (Cobots), Gemelos Digitales, IoT, Edge Computing, IA Explicable (XAI) y Sistemas Ciber-Físico-Sociales.',
  },
  {
    number: '03',
    title: 'Sostenibilidad y Logística Verde',
    description: 'Energías limpias, economía circular, descarbonización, transición energética, movilidad inteligente y materiales funcionales sostenibles.',
  },
  {
    number: '04',
    title: 'Innovación y Transformación Digital',
    description: 'Nuevos modelos de negocio, manufactura avanzada, Servitización (MaaS), gestión del ciclo de vida del producto (PLM) e innovación abierta.',
  },
  {
    number: '05',
    title: 'Gobernanza y Ciberseguridad',
    description: 'Ética de datos, protección de infraestructuras críticas, soberanía digital, Resiliencia Ciber-Física y Privacidad por Diseño (PbD).',
  },
  {
    number: '06',
    title: 'Retos Regionales y Sector Productivo',
    description: 'Transformación local, cierre de brechas tecnológicas, transferencia tecnológica rural/urbana e innovación social de base tecnológica.',
  },
  {
    number: '07',
    title: 'Desarrollo Tecnológico, Sociedad y Educación',
    description: 'Impacto de tecnologías emergentes en la sociedad, estrategias pedagógicas, EdTech, competencias humanas (Soft Skills) y apropiación social del conocimiento.',
  },
  {
    number: '08',
    title: 'Ingeniería de Software Inteligente',
    description: 'DevOps/DevSecOps, MLOps, AIOps, arquitecturas cloud-native, testing automatizado inteligente, generación de código con IA y Platform Engineering.',
  },
]
</script>

<template>
  <section id="ejes" class="bg-cgr-section py-24 px-5 lg:px-20">
    <div class="max-w-7xl mx-auto">

      <div class="text-center mb-16">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Ejes temáticos</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-black text-white">
          8 Tracks de Investigación
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
