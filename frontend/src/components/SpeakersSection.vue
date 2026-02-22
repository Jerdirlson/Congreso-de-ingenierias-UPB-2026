<script setup lang="ts">
const activities = [
  {
    title: 'Conferencias Centrales',
    description: 'Invitados nacionales e internacionales de alto nivel compartiendo su visión sobre la Industria 5.0.',
    icon: 'mic',
  },
  {
    title: 'Ponencias Académicas',
    description: 'Investigación científica, avances en progreso, revisiones del estado del arte, innovación educativa y casos de estudio.',
    icon: 'doc',
  },
  {
    title: 'Sesión de Pósters',
    description: 'Espacio de intercambio dinámico para diálogos directos y networking científico personalizado.',
    icon: 'poster',
  },
  {
    title: 'Workshops (Talleres)',
    description: 'Talleres prácticos para el fortalecimiento de competencias técnicas en tecnologías 5.0.',
    icon: 'tool',
  },
  {
    title: 'Panel Interdisciplinario',
    description: 'Participación de ponentes internacionales en un debate transdisciplinar sobre ingeniería y humanidad.',
    icon: 'users',
  },
  {
    title: 'Futuros Ingenieros por la Humanidad',
    description: 'Experiencias exitosas de colegios y trabajos destacados de estudiantes de ingeniería UPB.',
    icon: 'star',
  },
]
</script>

<template>
  <section id="actividades" class="bg-cgr-section py-24 px-5 lg:px-20">
    <div class="max-w-7xl mx-auto">

      <div class="text-center mb-16">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Metodología y Actividades</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-black text-white">
          Una experiencia integral
        </h2>
        <p class="mt-4 text-cgr-muted max-w-xl mx-auto text-base leading-relaxed">
          5 días de conferencias, talleres, paneles y encuentros diseñados para conectar la ingeniería con la humanidad.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="activity in activities"
          :key="activity.title"
          class="bg-cgr-card border border-cgr-border rounded-2xl p-8 hover:border-cgr-purple-dark transition-colors group"
        >
          <div class="w-12 h-12 rounded-xl bg-cgr-purple/10 flex items-center justify-center mb-6 group-hover:bg-cgr-purple/20 transition-colors">
            <!-- Mic -->
            <svg v-if="activity.icon === 'mic'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
            </svg>
            <!-- Doc -->
            <svg v-else-if="activity.icon === 'doc'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            <!-- Poster -->
            <svg v-else-if="activity.icon === 'poster'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25a2.25 2.25 0 01-2.25 2.25h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
            </svg>
            <!-- Tool -->
            <svg v-else-if="activity.icon === 'tool'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.03 5.03a1.5 1.5 0 01-2.12 0l-.88-.88a1.5 1.5 0 010-2.12l5.03-5.03M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.048.58.024 1.193-.14 1.743"/>
            </svg>
            <!-- Users -->
            <svg v-else-if="activity.icon === 'users'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
            </svg>
            <!-- Star -->
            <svg v-else class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
            </svg>
          </div>

          <h3 class="text-white font-bold text-lg mb-3">{{ activity.title }}</h3>
          <p class="text-cgr-muted text-sm leading-relaxed">{{ activity.description }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
