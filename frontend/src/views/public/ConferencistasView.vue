<script setup lang="ts">
import NavBar from '../../components/NavBar.vue'
import FooterSection from '../../components/FooterSection.vue'

type Conferencista = {
  nombre: string
  titulo: string
  conferencia: string
  bio?: string
  institucion: string
  pais: string
  tipo: 'Internacional' | 'Nacional'
  modalidad?: 'Presencial' | 'Virtual' | 'Híbrida'
  lineas?: string[]
  cvUrl?: string
}

const conferencistas: Conferencista[] = [
  {
    nombre: 'Néstor Guillermo Escalona Burgos',
    titulo: 'Dr.',
    conferencia: 'Cadena de valor del H₂ verde: Tecnologías y desafíos para su implementación',
    bio: 'Profesor Titular en la Pontificia Universidad Católica de Chile, Doctor en Química con destacada trayectoria en investigación y formación de capital humano avanzado en ingeniería química y catálisis. Su trabajo se centra en el desarrollo de procesos catalíticos aplicados a la descontaminación ambiental, la producción de energía y la valorización de biomasa, incluyendo hidrotratamiento, gasificación, producción de hidrógeno y biocombustibles sostenibles (SAF) en el contexto de biorrefinerías. Ha liderado numerosos proyectos nacionales e internacionales, cuenta con amplia producción científica en revistas de alto impacto y ha sido reconocido con premios por excelencia académica.',
    institucion: 'Pontificia Universidad Católica de Chile',
    pais: 'Chile',
    tipo: 'Internacional',
    lineas: ['Catálisis', 'Hidrógeno verde', 'Biocombustibles sostenibles (SAF)', 'Biorrefinerías'],
  },
  {
    nombre: 'Jhon Alexander Narváez Salazar',
    titulo: 'Mg.',
    conferencia: 'Del dato al valor: Cómo la transformación digital del mantenimiento impacta la confiabilidad industrial y el bienestar',
    institucion: 'Ecopetrol',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    lineas: ['Iniciativas de mejora continua', 'Gestión de activos y mantenimiento', 'Transformación digital para mantenimiento'],
  },
]
</script>

<template>
  <NavBar />

  <main class="min-h-screen bg-cgr-bg pt-16 lg:pl-72">

    <!-- Hero -->
    <section class="bg-cgr-section border-b border-cgr-border py-14 px-6 lg:px-16">
      <div class="max-w-4xl mx-auto">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Congreso de Ingenierías 2026</span>
        <h1 class="text-3xl sm:text-4xl font-black text-white mt-3 mb-4">Conferencistas</h1>
        <p class="text-cgr-muted text-sm max-w-2xl leading-relaxed">
          Conoce a los expertos nacionales e internacionales que compartirán su conocimiento y experiencia en el II Congreso Internacional de Ingeniería.
        </p>
      </div>
    </section>

    <!-- Cards -->
    <section class="py-12 px-6 lg:px-16">
      <div class="max-w-4xl mx-auto space-y-8">

        <article
          v-for="c in conferencistas"
          :key="c.nombre"
          class="bg-cgr-card border border-cgr-border rounded-2xl overflow-hidden"
        >
          <!-- Cabecera coloreada -->
          <div class="bg-cgr-section border-b border-cgr-border px-6 py-5 flex flex-col sm:flex-row sm:items-start gap-4">
            <!-- Avatar -->
            <div class="shrink-0 w-14 h-14 rounded-full bg-cgr-purple/20 border border-cgr-purple/30 flex items-center justify-center">
              <span class="text-cgr-purple font-bold text-lg">{{ c.titulo }}</span>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <h2 class="text-white font-bold text-lg leading-snug">{{ c.nombre }}</h2>
                <!-- Tipo badge -->
                <span
                  :class="c.tipo === 'Internacional'
                    ? 'bg-cgr-purple/20 text-cgr-purple border border-cgr-purple/30'
                    : 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30'"
                  class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full uppercase tracking-wide shrink-0"
                >
                  {{ c.tipo }}
                </span>
                <span
                  v-if="c.modalidad"
                  class="bg-cgr-border/60 text-cgr-subtle text-[10px] font-semibold px-2.5 py-0.5 rounded-full uppercase tracking-wide shrink-0"
                >
                  {{ c.modalidad }}
                </span>
              </div>
              <p class="text-cgr-muted text-sm">{{ c.institucion }} · {{ c.pais }}</p>
            </div>
          </div>

          <!-- Cuerpo -->
          <div class="px-6 py-5 space-y-4">
            <!-- Conferencia -->
            <div>
              <p class="text-cgr-purple text-[10px] font-semibold uppercase tracking-widest mb-1">Conferencia</p>
              <p class="text-white font-semibold text-base leading-snug">"{{ c.conferencia }}"</p>
            </div>

            <!-- Bio -->
            <p v-if="c.bio" class="text-cgr-muted text-sm leading-relaxed">{{ c.bio }}</p>

            <!-- Líneas de experiencia -->
            <div v-if="c.lineas?.length">
              <p class="text-cgr-subtle text-[10px] font-semibold uppercase tracking-widest mb-2">Líneas de experiencia</p>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="linea in c.lineas"
                  :key="linea"
                  class="bg-cgr-section border border-cgr-border text-cgr-muted text-xs px-3 py-1 rounded-full"
                >
                  {{ linea }}
                </span>
              </div>
            </div>

            <!-- CV -->
            <div v-if="c.cvUrl" class="pt-1">
              <a
                :href="c.cvUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 border border-cgr-purple/50 text-cgr-purple hover:bg-cgr-purple/10 text-xs font-semibold px-4 py-2 rounded-lg transition-colors"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
                Ver CV
              </a>
            </div>
          </div>
        </article>

      </div>
    </section>

  </main>

  <FooterSection class="lg:pl-72" />
</template>
