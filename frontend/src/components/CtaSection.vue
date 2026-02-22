<script setup lang="ts">
const modalities = [
  {
    title: 'Presencial',
    description: 'Presentación oral en el recinto del evento en modalidad de ponencia o póster.',
    icon: 'building',
  },
  {
    title: 'Virtual',
    description: 'Envío de videoponencia grabada antes del 28 de septiembre de 2026 con requisitos técnicos específicos.',
    icon: 'video',
  },
  {
    title: 'Proyectos de Aula',
    description: 'Trabajos destacados de estudiantes de ingeniería UPB y experiencias exitosas de colegios seleccionados.',
    icon: 'academic',
  },
]
</script>

<template>
  <section
    id="inscripcion"
    class="relative py-24 px-5 lg:px-20 overflow-hidden"
    style="background: radial-gradient(ellipse at center, #2E1065 0%, #0A0A1A 70%);"
  >
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-cgr-purple/5 rounded-full blur-3xl pointer-events-none" />

    <div class="relative z-10 max-w-5xl mx-auto text-center">

      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-cgr-purple/20 border border-cgr-purple/30 mb-8">
        <svg class="w-8 h-8 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.63 2.17a14.98 14.98 0 0 0-5.84 7.38m11.8 4.82A6 6 0 0 1 2 16.37m13.59-2V12a6 6 0 0 0-6-6H8.37"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </div>

      <h2 class="text-3xl sm:text-5xl font-black text-white mb-4 leading-tight">
        Sé parte del congreso<br />
        <span class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple bg-clip-text text-transparent">
          más importante del año
        </span>
      </h2>
      <p class="text-cgr-muted text-lg mb-12 max-w-2xl mx-auto">
        Al menos un autor debe estar inscrito antes del 18 de septiembre para garantizar
        la publicación en el libro de resúmenes.
      </p>

      <!-- Modalidades -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-12">
        <div
          v-for="mod in modalities"
          :key="mod.title"
          class="bg-cgr-card border border-cgr-border rounded-2xl p-8 flex flex-col items-center gap-4 hover:-translate-y-1 transition-transform"
        >
          <!-- Building -->
          <div class="w-12 h-12 rounded-xl bg-cgr-purple/10 flex items-center justify-center">
            <svg v-if="mod.icon === 'building'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18H6zM6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
              <path stroke-linecap="round" d="M10 6h4M10 10h4M10 14h4M10 18h4"/>
            </svg>
            <svg v-else-if="mod.icon === 'video'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <svg v-else class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
            </svg>
          </div>

          <h3 class="text-white font-bold text-base">{{ mod.title }}</h3>
          <p class="text-cgr-muted text-xs leading-relaxed text-center">{{ mod.description }}</p>
        </div>
      </div>

      <!-- Certificaciones destacadas -->
      <div class="bg-cgr-card border border-cgr-border rounded-2xl p-8 mb-12 text-left max-w-3xl mx-auto">
        <h3 class="text-white font-bold text-base mb-4 text-center">Publicación y Certificaciones</h3>
        <ul class="flex flex-col gap-3">
          <li class="flex gap-3 items-start">
            <span class="w-1.5 h-1.5 rounded-full bg-cgr-purple mt-2 shrink-0" />
            <span class="text-cgr-muted text-sm">Libro de Memorias oficial con registro ISSN (100% de resúmenes aceptados)</span>
          </li>
          <li class="flex gap-3 items-start">
            <span class="w-1.5 h-1.5 rounded-full bg-cgr-purple mt-2 shrink-0" />
            <span class="text-cgr-muted text-sm">Edición especial ISSN para proyectos de aula destacados</span>
          </li>
          <li class="flex gap-3 items-start">
            <span class="w-1.5 h-1.5 rounded-full bg-cgr-purple mt-2 shrink-0" />
            <span class="text-cgr-muted text-sm">Artículos destacados enviados a la Revista Facultad de Ingeniería — Universidad de Antioquia</span>
          </li>
          <li class="flex gap-3 items-start">
            <span class="w-1.5 h-1.5 rounded-full bg-cgr-purple mt-2 shrink-0" />
            <span class="text-cgr-muted text-sm">Certificación digital de asistencia y de ponencia para todos los autores</span>
          </li>
        </ul>
      </div>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <a
          href="#"
          class="w-full sm:w-auto bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-10 py-3.5 rounded-xl hover:opacity-90 transition-opacity"
        >
          Enviar ponencia
        </a>
        <a
          href="#ejes"
          class="w-full sm:w-auto border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple font-semibold px-10 py-3.5 rounded-xl transition-colors"
        >
          Ver ejes temáticos
        </a>
      </div>
    </div>
  </section>
</template>
