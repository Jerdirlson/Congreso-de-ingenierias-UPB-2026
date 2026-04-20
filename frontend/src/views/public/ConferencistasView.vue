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
  foto?: string
  cvUrl?: string
}

function initials(nombre: string): string {
  return nombre.split(' ').filter((_, i) => i === 0 || i === 2).map(w => w[0]).join('').toUpperCase().slice(0, 2)
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
    foto: '/conferencistas/nestor-escalona.jpg',
  },
  {
    nombre: 'Carlos Eduardo García Sánchez',
    titulo: 'Dr.',
    conferencia: 'De la teoría al impacto: el rol de la investigación aplicada en el desarrollo tecnológico del país',
    bio: 'Investigador Senior reconocido por Minciencias, con actividad simultánea en la industria y la academia. Doctor en Ingeniería Química con amplia experiencia en proyectos con el sector productivo y entidades de gobierno, enfocado en el desarrollo tecnológico aplicado a la industria energética nacional.',
    institucion: 'CDT del Gas · Universidad Industrial de Santander (UIS)',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    lineas: [
      'Hidrógeno y transición energética',
      'Gases combustibles y gas natural',
      'Metrología y medición de gases',
      'Modelado y simulación de procesos',
      'Sensores, IoT y gemelos digitales',
      'Energía y biogás',
      'Calidad del aire urbano',
    ],
  },
  {
    nombre: 'Sindey Carolina Bernal Villamarín',
    titulo: 'Dra.',
    conferencia: 'La Revolución de la Tecnología al Servicio de la Diversidad',
    bio: 'Doctora en Educación Inclusiva y en Formación en Diversidad (Summa Cum Laude). Directora de la Maestría en Educación Inclusiva en la Universidad El Bosque, ExViceministra de Transformación Digital en el Ministerio TIC e investigadora en la Consejería para la Reconciliación Nacional. Combina visión de Estado, de docente y de investigadora para diseñar soluciones tecnológicas que cierran brechas de comunicación y aprendizaje.',
    institucion: 'Universidad El Bosque',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    lineas: ['Ingeniería Humanocéntrica', 'Innovación Educativa y STEM inclusivo', 'Gamificación', 'Impacto regional con tecnología'],
  },
  {
    nombre: 'Neila Mantilla Barbosa',
    titulo: 'Dra.',
    conferencia: 'Construyendo ecosistemas de innovación sostenible: experiencias en bioenergía y transferencia tecnológica',
    bio: 'Doctora en Ingeniería Química e investigadora con 2 patentes desarrolladas en Colombia. Realizó su postdoctorado en Cenipalma, lo que le otorga un enfoque altamente aplicado en el sector energético. Comprometida con la articulación entre investigación, industria y transferencia tecnológica.',
    institucion: 'Universidad de la Costa – CUC',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    lineas: ['Bioenergía y procesos sostenibles', 'Valorización de biomasa y tecnologías de pretratamiento', 'Innovación sostenible y transferencia tecnológica'],
    cvUrl: 'https://www.linkedin.com/in/neila-mantilla-543924140/',
    foto: '/conferencistas/neila-mantilla.jpg',
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
      <div class="max-w-5xl mx-auto">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Congreso de Ingenierías 2026</span>
        <h1 class="text-3xl sm:text-4xl font-black text-white mt-3 mb-4">Conferencistas</h1>
        <p class="text-cgr-muted text-sm max-w-2xl leading-relaxed">
          Conoce a los expertos nacionales e internacionales que compartirán su conocimiento y experiencia en el II Congreso Internacional de Ingeniería.
        </p>
      </div>
    </section>

    <!-- Cards -->
    <section class="py-12 px-6 lg:px-16">
      <div class="max-w-5xl mx-auto space-y-8">

        <article
          v-for="c in conferencistas"
          :key="c.nombre"
          class="bg-cgr-card border border-cgr-border rounded-2xl overflow-hidden flex flex-col md:flex-row"
        >
          <!-- ── Imagen / Placeholder ── -->
          <div class="relative md:w-56 lg:w-64 h-60 md:h-auto shrink-0">
            <img
              v-if="c.foto"
              :src="c.foto"
              :alt="c.nombre"
              class="absolute inset-0 w-full h-full object-cover object-top"
            />
            <!-- Placeholder elegante cuando no hay foto -->
            <div
              v-else
              class="absolute inset-0 flex flex-col items-center justify-center"
              :class="c.tipo === 'Internacional'
                ? 'bg-gradient-to-br from-cgr-purple-deep/60 via-cgr-purple/30 to-black/60'
                : 'bg-gradient-to-br from-slate-700/60 via-slate-600/30 to-black/60'"
            >
              <span class="text-7xl font-black text-white/10 select-none leading-none mb-2">{{ initials(c.nombre) }}</span>
              <span class="text-white/40 text-xs font-semibold tracking-widest uppercase">{{ c.titulo }}</span>
            </div>

            <!-- Badges sobre la imagen -->
            <div class="absolute bottom-3 left-3 flex flex-col gap-1.5 z-10">
              <span
                :class="c.tipo === 'Internacional'
                  ? 'bg-cgr-purple/80 text-white border border-cgr-purple/50'
                  : 'bg-emerald-600/80 text-white border border-emerald-500/50'"
                class="text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide backdrop-blur-sm w-fit"
              >
                {{ c.tipo }}
              </span>
              <span
                v-if="c.modalidad"
                class="bg-black/60 text-white/80 border border-white/10 text-[10px] font-semibold px-2.5 py-1 rounded-full uppercase tracking-wide backdrop-blur-sm w-fit"
              >
                {{ c.modalidad }}
              </span>
            </div>
          </div>

          <!-- ── Contenido ── -->
          <div class="flex-1 flex flex-col p-6 gap-5 min-w-0">

            <!-- Nombre e institución -->
            <div>
              <h2 class="text-white font-black text-xl leading-tight mb-1">
                {{ c.titulo }} {{ c.nombre }}
              </h2>
              <div class="flex items-center gap-1.5 text-cgr-muted text-sm flex-wrap">
                <svg class="w-3.5 h-3.5 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 9.2c0 7.3-8 11.8-8 11.8z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
                <span>{{ c.institucion }}</span>
                <span class="text-cgr-border">·</span>
                <span>{{ c.pais }}</span>
              </div>
            </div>

            <!-- Separador -->
            <div class="h-px bg-cgr-border" />

            <!-- Conferencia -->
            <div class="border-l-2 border-cgr-purple pl-4">
              <p class="text-cgr-purple text-[10px] font-semibold uppercase tracking-widest mb-2">Conferencia</p>
              <p class="text-white font-semibold text-base leading-snug">{{ c.conferencia }}</p>
            </div>

            <!-- Bio -->
            <p v-if="c.bio" class="text-cgr-muted text-sm leading-relaxed line-clamp-4">{{ c.bio }}</p>

            <!-- Líneas de experiencia -->
            <div v-if="c.lineas?.length">
              <p class="text-cgr-subtle text-[10px] font-semibold uppercase tracking-widest mb-2">Líneas de experiencia</p>
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="linea in c.lineas"
                  :key="linea"
                  class="bg-cgr-section border border-cgr-border text-cgr-muted text-xs px-3 py-1 rounded-full"
                >
                  {{ linea }}
                </span>
              </div>
            </div>

            <!-- Botón CV -->
            <div v-if="c.cvUrl" class="mt-auto pt-1">
              <a
                :href="c.cvUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 border border-cgr-purple/50 text-cgr-purple hover:bg-cgr-purple/10 text-xs font-semibold px-4 py-2 rounded-lg transition-colors"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
                Ver perfil
              </a>
            </div>

          </div>
        </article>

      </div>
    </section>

  </main>

  <FooterSection class="lg:pl-72" />
</template>
