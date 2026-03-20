<script setup lang="ts">
import { useScrollReveal } from '../composables/useScrollReveal'

const { setRef } = useScrollReveal()

const tracks = [
  {
    number: '01',
    title: 'Ingeniería Aplicada con propósito humano y bienestar',
    description: 'Diseño centrado en el usuario, ergonomía cognitiva, ética en el diseño y tecnologías para la salud. Biomecánica, diseño de máquinas humanocéntricas. Tecnologías de asistencia e inclusión, ingeniería para el cuidado, sistemas de seguridad proactiva y confort ambiental.',
  },
  {
    number: '02',
    title: 'Tecnologías 5.0',
    description: 'Evolución de la IA, Robótica colaborativa (Cobots), Gemelos Digitales, IoT e hiper-personalización industrial. Automatización y sistemas inteligentes, Redes Inteligentes y Autónomas, Transformación Digital, Industria 4.0, Ciencia de Datos, Sistemas Ciber-Físico-Sociales (CPSS), Edge Computing, IA Explicable (XAI).',
  },
  {
    number: '03',
    title: 'Sostenibilidad y Logística Verde',
    description: 'Energías limpias, economía circular, descarbonización y cadenas de suministro resilientes. Materiales para Ingeniería, Movilidad Inteligente, Transición Energética, economía circular de plásticos, tecnologías energéticas sostenibles, pilas de combustible, textiles inteligentes.',
  },
  {
    number: '04',
    title: 'Innovación y Transformación Digital',
    description: 'Nuevos modelos de negocio, agilidad organizacional, digitalización en la productividad, sistemas mecánicos, manufactura avanzada, gestión de activos, Servitización (MaaS), PLM y Ecosistemas de Innovación Abierta.',
  },
  {
    number: '05',
    title: 'Gobernanza y Ciberseguridad',
    description: 'Ética de los datos, protección de infraestructuras críticas, legislación tecnológica, soberanía digital, derecho digital, ciberseguridad industrial, Resiliencia Ciber-Física, Privacidad por Diseño (PbD) e Identidad Digital Segura.',
  },
  {
    number: '06',
    title: 'Retos Regionales y Sector Productivo',
    description: 'Transformación local, cierre de brechas tecnológicas, políticas públicas, gestión de activos, transferencia tecnológica rural/urbana, encadenamientos productivos regionales e innovación social de base tecnológica.',
  },
  {
    number: '07',
    title: 'Desarrollo tecnológico, sociedad y educación',
    description: 'Impacto de tecnologías emergentes en la sociedad, estrategias pedagógicas en ingeniería, ecosistemas educativos digitales, formación en Soft Skills, EdTech y apropiación social del conocimiento.',
  },
  {
    number: '08',
    title: 'Ingeniería de Software Inteligente y Automatizada',
    description: 'DevOps, DevSecOps y GitOps; MLOps; AIOps; Infraestructura como Código; Arquitecturas cloud-native y microservicios; Ingeniería de requisitos asistida por IA; Testing automatizado; Generación de código con IA; Gobierno de modelos; Platform Engineering. Modelado y Simulación.',
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
