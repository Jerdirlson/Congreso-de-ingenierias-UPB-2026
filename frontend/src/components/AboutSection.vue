<script setup lang="ts">
const areas = [
  {
    title: 'Inteligencia Artificial',
    description: 'Machine learning, deep learning, visión computacional y procesamiento del lenguaje natural aplicados a la ingeniería.',
    icon: 'cpu',
  },
  {
    title: 'Energías Renovables',
    description: 'Solar, eólica, hidrógeno verde y sistemas de almacenamiento para la transición energética.',
    icon: 'zap',
  },
  {
    title: 'Robótica e Industria 4.0',
    description: 'Automatización industrial, robótica colaborativa, gemelos digitales e IoT en entornos de manufactura.',
    icon: 'cog',
  },
  {
    title: 'Ingeniería Civil y Construcción',
    description: 'Infraestructura sostenible, materiales avanzados, BIM y resiliencia ante el cambio climático.',
    icon: 'building',
  },
  {
    title: 'Bioingeniería',
    description: 'Dispositivos médicos, telemedicina, ingeniería de tejidos y biomecánica computacional.',
    icon: 'heart',
  },
  {
    title: 'Ciberseguridad',
    description: 'Seguridad en sistemas críticos, criptografía, análisis forense digital y protección de infraestructuras.',
    icon: 'shield',
  },
]
</script>

<template>
  <section id="talleres" class="bg-cgr-bg py-24 px-5 lg:px-20">
    <div class="max-w-7xl mx-auto">

      <!-- Encabezado -->
      <div class="text-center mb-16">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Áreas temáticas</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-black text-white">
          12 Líneas de investigación
        </h2>
        <p class="mt-4 text-cgr-muted max-w-xl mx-auto text-base leading-relaxed">
          Explora los ejes temáticos que guiarán las conferencias, talleres y paneles del congreso.
        </p>
      </div>

      <!-- Grid de cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="area in areas"
          :key="area.title"
          class="bg-cgr-card border border-cgr-border rounded-2xl p-8 hover:border-cgr-purple-dark transition-colors group"
        >
          <!-- Icono -->
          <div class="w-12 h-12 rounded-xl bg-cgr-purple/10 flex items-center justify-center mb-6 group-hover:bg-cgr-purple/20 transition-colors">

            <!-- CPU -->
            <svg v-if="area.icon === 'cpu'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <rect x="4" y="4" width="16" height="16" rx="2" />
              <rect x="9" y="9" width="6" height="6" />
              <path d="M15 2v2M9 2v2M2 15h2M2 9h2M15 22v-2M9 22v-2M22 15h-2M22 9h-2" stroke-linecap="round"/>
            </svg>

            <!-- Zap -->
            <svg v-else-if="area.icon === 'zap'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
            </svg>

            <!-- Cog -->
            <svg v-else-if="area.icon === 'cog'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="3"/>
              <path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
            </svg>

            <!-- Building -->
            <svg v-else-if="area.icon === 'building'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18H6zM6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
              <path stroke-linecap="round" d="M10 6h4M10 10h4M10 14h4M10 18h4"/>
            </svg>

            <!-- Heart pulse -->
            <svg v-else-if="area.icon === 'heart'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
              <path stroke-linecap="round" d="M3.22 12H9.5l.94-2.07 1.07 4.14 2.13-6.16L15 12h5.78"/>
            </svg>

            <!-- Shield check -->
            <svg v-else-if="area.icon === 'shield'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/>
            </svg>
          </div>

          <h3 class="text-white font-bold text-lg mb-3">{{ area.title }}</h3>
          <p class="text-cgr-muted text-sm leading-relaxed">{{ area.description }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
