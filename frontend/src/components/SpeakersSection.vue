<script setup lang="ts">
const speakers = [
  {
    name: 'Dra. María González',
    role: 'IA & Machine Learning',
    bio: 'Investigadora principal en el MIT Media Lab. Pionera en modelos de IA aplicados a sistemas de ingeniería críticos.',
    gradient: 'from-violet-600 to-purple-800',
    initials: 'MG',
  },
  {
    name: 'Dr. Carlos Ramírez',
    role: 'Energías Renovables',
    bio: 'Director del Centro de Transición Energética de la Universidad de Chile. Asesor del Ministerio de Energía.',
    gradient: 'from-blue-600 to-violet-700',
    initials: 'CR',
  },
  {
    name: 'Ing. Sofía Herrera',
    role: 'Robótica Industrial',
    bio: 'Líder de innovación en Boston Dynamics LATAM. Especialista en robots colaborativos para manufactura.',
    gradient: 'from-purple-700 to-pink-700',
    initials: 'SH',
  },
  {
    name: 'Dr. Andrés Mora',
    role: 'Ciberseguridad',
    bio: 'Ex-director de seguridad en el Banco Interamericano de Desarrollo. Experto en infraestructuras críticas.',
    gradient: 'from-indigo-600 to-purple-700',
    initials: 'AM',
  },
]
</script>

<template>
  <section id="ponentes" class="bg-cgr-section py-24 px-5 lg:px-20">
    <div class="max-w-7xl mx-auto">

      <!-- Encabezado -->
      <div class="text-center mb-16">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Ponentes confirmados</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-black text-white">
          Expertos internacionales
        </h2>
        <p class="mt-4 text-cgr-muted max-w-xl mx-auto text-base leading-relaxed">
          Líderes de la industria y la academia que darán forma al debate de la ingeniería del futuro.
        </p>
      </div>

      <!-- Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="speaker in speakers"
          :key="speaker.name"
          class="bg-cgr-card border border-cgr-border rounded-2xl overflow-hidden hover:border-cgr-purple-dark transition-colors group"
        >
          <!-- Avatar con gradiente -->
          <div
            class="h-[200px] flex items-center justify-center bg-gradient-to-br"
            :class="speaker.gradient"
          >
            <span class="text-5xl font-black text-white/90">{{ speaker.initials }}</span>
          </div>

          <!-- Info -->
          <div class="p-6">
            <h3 class="text-white font-bold text-base mb-1">{{ speaker.name }}</h3>
            <p class="text-cgr-accent text-sm font-medium mb-3">{{ speaker.role }}</p>
            <p class="text-cgr-muted text-xs leading-relaxed">{{ speaker.bio }}</p>
          </div>
        </div>
      </div>

      <!-- Ver todos -->
      <div class="text-center mt-12">
        <a
          href="#"
          class="inline-flex items-center gap-2 border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple transition-colors text-sm font-medium px-6 py-3 rounded-xl"
        >
          Ver todos los ponentes
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>
  </section>
</template>
