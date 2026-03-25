<script setup lang="ts">
import NavBar from '../../components/NavBar.vue'
import FooterSection from '../../components/FooterSection.vue'
import { useScrollReveal } from '../../composables/useScrollReveal'
import { registrationOpen } from '../../composables/useRegistration'

const { setRef } = useScrollReveal()

const profiles = [
  {
    icon: 'academic',
    title: 'Investigadores y académicos',
    desc: 'Docentes, investigadores y grupos de investigación de universidades e institutos de Colombia y el mundo.',
  },
  {
    icon: 'briefcase',
    title: 'Profesionales del sector',
    desc: 'Ingenieros y profesionales en ejercicio con proyectos, innovaciones o experiencias destacadas en la industria.',
  },
  {
    icon: 'users',
    title: 'Estudiantes de ingeniería',
    desc: 'Estudiantes de pregrado y posgrado de UPB y otras instituciones; también colegios seleccionados con proyectos destacados.',
  },
]

const modalities = [
  {
    icon: 'building',
    title: 'Presencial',
    badge: 'Oral / Póster',
    desc: 'Presentación oral o póster en el campus UPB Bucaramanga, 13 al 17 de octubre de 2026.',
  },
  {
    icon: 'video',
    title: 'Virtual',
    badge: 'Videoponencia',
    desc: 'Envío de videoponencia grabada. Fecha límite: 28 de septiembre de 2026. Aplican requisitos técnicos específicos.',
  },
  {
    icon: 'academic',
    title: 'Proyectos Internos',
    badge: 'UPB / Colegios',
    desc: 'Para trabajos destacados de estudiantes UPB y experiencias exitosas de colegios seleccionados.',
  },
]

const tracks = [
  { number: '01', title: 'Ingeniería Aplicada con propósito humano y bienestar', short: 'Diseño centrado en el usuario, ergonomía cognitiva, tecnologías de asistencia e inclusión.' },
  { number: '02', title: 'Tecnologías 5.0', short: 'IA, robótica colaborativa, gemelos digitales e IoT al servicio de la industria.' },
  { number: '03', title: 'Sostenibilidad y Logística Verde', short: 'Energías limpias, economía circular y cadenas de suministro resilientes.' },
  { number: '04', title: 'Innovación y Transformación Digital', short: 'Nuevos modelos de negocio, manufactura avanzada y ecosistemas de innovación.' },
  { number: '05', title: 'Gobernanza y Ciberseguridad', short: 'Ética de datos, protección de infraestructuras críticas y soberanía digital.' },
  { number: '06', title: 'Retos Regionales y Sector Productivo', short: 'Transformación local, transferencia tecnológica e innovación social de base tecnológica.' },
  { number: '07', title: 'Desarrollo tecnológico, sociedad y educación', short: 'Tecnologías emergentes, estrategias pedagógicas y ecosistemas educativos digitales.' },
  { number: '08', title: 'Ingeniería de Software Inteligente y Automatizada', short: 'DevOps, MLOps, cloud-native, IA generativa y Platform Engineering.' },
]

const benefits = [
  'Publicación en Libro de Resúmenes oficial con registro ISSN',
  'Opción de publicación en Revista Facultad de Ingeniería — Universidad de Antioquia',
  'Opción de publicación en Revista Memoria Investigaciones en Ingeniería (Uruguay)',
  'Certificación digital de ponencia con nombre de todos los autores',
  'Edición especial ISSN para proyectos estudiantiles UPB y colegios',
]

const dates = [
  { date: '20 de marzo',         label: 'Apertura recepción de resúmenes' },
  { date: '31 de mayo',          label: 'Cierre recepción de resúmenes' },
  { date: '26 de junio',         label: 'Notificación de resúmenes aceptados (ISSN)' },
  { date: '1 — 30 de julio',     label: 'Envío de versiones finales' },
  { date: '28 de septiembre',    label: 'Cierre de videoponencias virtuales' },
  { date: '13 — 17 de octubre',  label: 'Celebración del Congreso' },
]

const steps = [
  {
    n: '01',
    title: 'Prepara tu resumen analítico',
    desc: 'Redacta entre 300 y 500 palabras siguiendo la plantilla oficial (disponible en el documento completo de la convocatoria).',
  },
  {
    n: '02',
    title: 'Completa el formulario',
    desc: 'Ingresa título, autores, afiliaciones y palabras clave (mínimo 3, máximo 6) en el formulario de envío.',
  },
  {
    n: '03',
    title: 'Adjunta el archivo .docx',
    desc: 'Sube tu resumen en formato Word (.docx). Tamaño máximo: 5 MB.',
  },
  {
    n: '04',
    title: 'Verifica la inscripción',
    desc: 'Al menos un autor debe estar inscrito al congreso antes del 18 de septiembre para garantizar la publicación en el libro de memorias.',
  },
]
</script>

<template>
  <div class="bg-cgr-bg font-sans text-white">
    <NavBar />
    <main class="pt-16 lg:pl-72">

      <!-- ── 1. HERO ──────────────────────────────────────────────────────── -->
      <section class="hero-gradient relative py-28 px-5 lg:px-20 overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cgr-purple-deep/30 rounded-full blur-3xl pointer-events-none" />
        <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-cgr-purple-dark/20 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 max-w-3xl mx-auto text-center">
          <div class="inline-flex items-center gap-2 border border-cgr-purple-dark rounded-full px-4 py-1.5 mb-8">
            <span class="w-2 h-2 rounded-full bg-cgr-purple animate-pulse" />
            <span class="text-cgr-accent text-xs font-semibold tracking-widest uppercase">Call for Abstract · 2026</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight mb-6">
            <span class="text-white">Llamado a</span><br />
            <span class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple bg-clip-text text-transparent">
              Ponencias
            </span>
          </h1>

          <p class="text-cgr-muted text-lg leading-relaxed max-w-2xl mx-auto mb-10">
            ¿Tienes una investigación, proyecto o experiencia que vale la pena compartir?
            Te invitamos a participar en el
            <span class="text-white font-semibold">II Congreso Internacional de Ingeniería 2026</span>.
          </p>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-4 flex-wrap">
            <div class="flex flex-col items-center gap-1">
              <RouterLink
                v-if="registrationOpen"
                to="/login"
                class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-8 py-3.5 rounded-xl hover:opacity-90 transition-opacity text-sm"
              >
                Enviar mi ponencia
              </RouterLink>
              <button
                v-else
                disabled
                class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-8 py-3.5 rounded-xl opacity-50 cursor-not-allowed pointer-events-none text-sm"
              >
                Enviar mi ponencia
              </button>
              <span v-if="!registrationOpen" class="text-cgr-muted text-[10px]">Pronto serán activadas las inscripciones</span>
            </div>

            <a
              href="/api/docs/Call_for_papers_V2.docx"
              download
              class="border border-cgr-border text-white font-semibold px-8 py-3.5 rounded-xl hover:border-cgr-purple transition-colors text-sm flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Descargar convocatoria
            </a>

            <a
              href="/api/docs/Plantilla_Resumen.docx"
              download
              class="border border-cgr-border text-cgr-muted font-semibold px-8 py-3.5 rounded-xl hover:border-cgr-purple hover:text-white transition-colors text-sm flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
              </svg>
              Descargar plantilla
            </a>
          </div>
        </div>
      </section>

      <!-- ── 2. QUIÉNES PUEDEN PARTICIPAR ──────────────────────────────── -->
      <section class="bg-cgr-section py-20 px-5 lg:px-20">
        <div class="max-w-5xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Participantes</span>
            <h2 class="mt-3 text-3xl font-black text-white">¿Quiénes pueden participar?</h2>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div
              v-for="(p, i) in profiles"
              :key="i"
              :ref="(el) => setRef(el as Element, i)"
              class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-7 flex flex-col items-center text-center gap-4 hover:-translate-y-1 hover:border-cgr-purple-dark/50 hover:shadow-lg hover:shadow-cgr-purple/10 transition-all duration-300"
            >
              <div class="w-12 h-12 rounded-xl bg-cgr-purple/10 flex items-center justify-center">
                <svg v-if="p.icon === 'academic'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
                </svg>
                <svg v-else-if="p.icon === 'briefcase'" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073c0 1.035-.84 1.875-1.875 1.875H5.625A1.875 1.875 0 013.75 18.22V14.15M16.5 6H15V5.25A2.25 2.25 0 0012.75 3h-1.5A2.25 2.25 0 009 5.25V6H7.5m9 0H7.5m9 0a2.25 2.25 0 012.25 2.25v1.5a2.25 2.25 0 01-2.25 2.25H7.5A2.25 2.25 0 015.25 9.9v-1.5A2.25 2.25 0 017.5 6"/>
                </svg>
                <svg v-else class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </div>
              <h3 class="text-white font-bold text-base">{{ p.title }}</h3>
              <p class="text-cgr-muted text-xs leading-relaxed">{{ p.desc }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- ── 3. MODALIDADES ─────────────────────────────────────────────── -->
      <section class="bg-cgr-bg py-20 px-5 lg:px-20">
        <div class="max-w-5xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Participación</span>
            <h2 class="mt-3 text-3xl font-black text-white">Modalidades de participación</h2>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div
              v-for="(mod, i) in modalities"
              :key="i"
              :ref="(el) => setRef(el as Element, i + 10)"
              class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-7 hover:border-cgr-purple-dark/50 hover:-translate-y-1 hover:shadow-lg hover:shadow-cgr-purple/10 transition-all duration-300"
            >
              <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-cgr-purple/10 flex items-center justify-center">
                  <svg v-if="mod.icon === 'building'" class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 22V4a2 2 0 012-2h8a2 2 0 012 2v18H6zM6 12H4a2 2 0 00-2 2v6a2 2 0 002 2h2M18 9h2a2 2 0 012 2v9a2 2 0 01-2 2h-2"/>
                  </svg>
                  <svg v-else-if="mod.icon === 'video'" class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                  </svg>
                  <svg v-else class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
                  </svg>
                </div>
                <span class="text-xs bg-cgr-purple/10 text-cgr-purple border border-cgr-purple/20 rounded-full px-2.5 py-0.5 font-medium">
                  {{ mod.badge }}
                </span>
              </div>
              <h3 class="text-white font-bold text-base mb-2">{{ mod.title }}</h3>
              <p class="text-cgr-muted text-xs leading-relaxed">{{ mod.desc }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- ── 4. EJES TEMÁTICOS ───────────────────────────────────────────── -->
      <section class="bg-cgr-section py-20 px-5 lg:px-20">
        <div class="max-w-5xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Ejes temáticos</span>
            <h2 class="mt-3 text-3xl font-black text-white">8 Tracks de Investigación</h2>
            <p class="mt-3 text-cgr-muted max-w-xl mx-auto text-sm leading-relaxed">
              Selecciona el eje que mejor se alinee con tu trabajo. La IA también puede ayudarte a identificarlo.
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="(track, i) in tracks"
              :key="i"
              :ref="(el) => setRef(el as Element, i + 20)"
              class="animate-reveal flex gap-4 items-start bg-cgr-card border border-cgr-border rounded-xl p-5 hover:border-cgr-purple-dark hover:scale-[1.01] hover:shadow-md hover:shadow-cgr-purple/5 transition-all duration-300"
            >
              <span class="text-cgr-purple text-xl font-black opacity-40 shrink-0 leading-none mt-0.5">{{ track.number }}</span>
              <div>
                <h3 class="text-white text-sm font-semibold leading-snug mb-1">{{ track.title }}</h3>
                <p class="text-cgr-muted text-xs leading-relaxed">{{ track.short }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── 5. BENEFICIOS ──────────────────────────────────────────────── -->
      <section class="bg-cgr-bg py-20 px-5 lg:px-20">
        <div class="max-w-3xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Publicaciones y reconocimientos</span>
            <h2 class="mt-3 text-3xl font-black text-white">¿Qué ganas al participar?</h2>
          </div>

          <div
            :ref="(el) => setRef(el as Element, 30)"
            class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-8"
          >
            <ul class="flex flex-col gap-5">
              <li v-for="(b, i) in benefits" :key="i" class="flex gap-4 items-start">
                <div class="w-6 h-6 rounded-full bg-cgr-purple/20 border border-cgr-purple/30 flex items-center justify-center shrink-0 mt-0.5">
                  <svg class="w-3.5 h-3.5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                  </svg>
                </div>
                <span class="text-cgr-muted text-sm leading-relaxed">{{ b }}</span>
              </li>
            </ul>
          </div>
        </div>
      </section>

      <!-- ── 6. FECHAS CLAVE ────────────────────────────────────────────── -->
      <section class="bg-cgr-section py-20 px-5 lg:px-20">
        <div class="max-w-3xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Calendario</span>
            <h2 class="mt-3 text-3xl font-black text-white">Fechas clave</h2>
          </div>

          <div class="relative">
            <div class="absolute left-6 top-0 bottom-0 w-px bg-gradient-to-b from-cgr-purple-dark via-cgr-purple to-cgr-purple-dark/20" />
            <div class="flex flex-col gap-4">
              <div
                v-for="(d, i) in dates"
                :key="i"
                :ref="(el) => setRef(el as Element, i + 40)"
                class="animate-reveal relative flex gap-6 items-start"
              >
                <div class="relative z-10 shrink-0 w-12 flex justify-center">
                  <div class="w-3.5 h-3.5 rounded-full border-2 mt-1.5 bg-cgr-card border-cgr-purple" />
                </div>
                <div class="bg-cgr-card border border-cgr-border rounded-xl px-5 py-3.5 flex-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 hover:border-cgr-purple/30 transition-colors">
                  <p class="text-white text-sm font-semibold">{{ d.label }}</p>
                  <span class="text-cgr-purple text-xs font-bold uppercase tracking-wider shrink-0">{{ d.date }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── 7. CÓMO ENVIAR ─────────────────────────────────────────────── -->
      <section class="bg-cgr-bg py-20 px-5 lg:px-20">
        <div class="max-w-3xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Proceso</span>
            <h2 class="mt-3 text-3xl font-black text-white">¿Cómo enviar tu resumen?</h2>
          </div>

          <div class="flex flex-col gap-5">
            <div
              v-for="(step, i) in steps"
              :key="i"
              :ref="(el) => setRef(el as Element, i + 50)"
              class="animate-reveal flex gap-5 items-start"
            >
              <div class="w-12 h-12 rounded-2xl bg-cgr-purple/10 border border-cgr-purple/20 flex items-center justify-center shrink-0">
                <span class="text-cgr-purple font-black text-sm">{{ step.n }}</span>
              </div>
              <div class="bg-cgr-card border border-cgr-border rounded-xl p-5 flex-1">
                <h3 class="text-white font-bold text-sm mb-1">{{ step.title }}</h3>
                <p class="text-cgr-muted text-xs leading-relaxed">{{ step.desc }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── 8. DOCUMENTO COMPLETO ──────────────────────────────────────── -->
      <section class="cta-gradient relative py-20 px-5 lg:px-20 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-cgr-purple/5 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 max-w-2xl mx-auto text-center">
          <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-cgr-purple/20 border border-cgr-purple/30 mb-6">
            <svg class="w-7 h-7 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
          </div>

          <h2 class="text-2xl sm:text-3xl font-black text-white mb-3">Documentos oficiales</h2>
          <p class="text-cgr-muted text-base mb-8 leading-relaxed">
            Descarga la convocatoria completa con todos los detalles, subtemas y condiciones,
            y la plantilla oficial para preparar tu resumen.
          </p>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a
              href="/api/docs/Call_for_papers_V2.docx"
              download
              class="inline-flex items-center gap-2 bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-8 py-3.5 rounded-xl hover:opacity-90 transition-opacity text-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Descargar convocatoria
            </a>
            <a
              href="/api/docs/Plantilla_Resumen.docx"
              download
              class="inline-flex items-center gap-2 border border-white/20 text-white font-semibold px-8 py-3.5 rounded-xl hover:border-white/50 transition-colors text-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
              </svg>
              Descargar plantilla (Word)
            </a>
          </div>
        </div>
      </section>

      <!-- ── 9. REVISTAS INDEXADAS ─────────────────────────────────────── -->
      <section class="bg-cgr-section py-20 px-5 lg:px-20">
        <div class="max-w-5xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Publicaciones científicas</span>
            <h2 class="mt-3 text-3xl font-black text-white">Revistas indexadas asociadas</h2>
            <p class="mt-3 text-cgr-muted text-sm max-w-2xl mx-auto leading-relaxed">
              Los mejores trabajos del congreso tendrán la oportunidad de ser publicados en las siguientes revistas científicas internacionales.
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <!-- Revista UdeA -->
            <div class="bg-cgr-card border border-cgr-border rounded-2xl p-7 flex flex-col gap-5 hover:border-cgr-purple-dark/50 hover:shadow-lg hover:shadow-cgr-purple/10 transition-all duration-300">
              <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-cgr-purple/10 flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                  </svg>
                </div>
                <div>
                  <p class="text-cgr-purple text-xs font-semibold uppercase tracking-wider mb-1">Colombia · UdeA</p>
                  <h3 class="text-white font-bold text-sm leading-snug">Revista Facultad de Ingeniería — Universidad de Antioquia</h3>
                </div>
              </div>
              <p class="text-cgr-muted text-xs leading-relaxed flex-1">
                Los trabajos con las valoraciones más altas serán seleccionados por el comité para enviar su artículo completo. La selección de su artículo no garantiza la publicación, dado que los trabajos se someterán al proceso editorial de la Revista de la Facultad de Ingeniería de la Universidad de Antioquia para un número especial.
              </p>
              <a
                href="https://revistas.udea.edu.co/index.php/ingenieria/about/submissions"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 text-cgr-purple hover:text-cgr-accent text-xs font-semibold transition-colors"
              >
                Ver revista
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
              </a>
            </div>

            <!-- Revista Uruguay -->
            <div class="bg-cgr-card border border-cgr-border rounded-2xl p-7 flex flex-col gap-5 hover:border-cgr-purple-dark/50 hover:shadow-lg hover:shadow-cgr-purple/10 transition-all duration-300">
              <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-cgr-purple/10 flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                  </svg>
                </div>
                <div>
                  <p class="text-cgr-purple text-xs font-semibold uppercase tracking-wider mb-1">Uruguay · UM</p>
                  <h3 class="text-white font-bold text-sm leading-snug">Revista Memoria Investigaciones en Ingeniería — Universidad de Montevideo</h3>
                </div>
              </div>
              <p class="text-cgr-muted text-xs leading-relaxed flex-1">
                Los trabajos con las valoraciones más altas serán seleccionados por el comité para enviar su artículo completo. La selección de su artículo no garantiza la publicación, dado que los trabajos se someterán al proceso editorial de la Revista Memoria Investigaciones en Ingeniería para un número especial.
              </p>
              <a
                href="https://revistas.um.edu.uy/index.php/ingenieria/about"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 text-cgr-purple hover:text-cgr-accent text-xs font-semibold transition-colors"
              >
                Ver revista
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
              </a>
            </div>

          </div>
        </div>
      </section>

      <!-- ── 10. CTA FINAL ──────────────────────────────────────────────── -->
      <section class="bg-cgr-section py-16 px-5 lg:px-20">
        <div class="max-w-2xl mx-auto text-center">
          <h2 class="text-2xl font-black text-white mb-3">¿Listo para participar?</h2>
          <p class="text-cgr-muted text-sm mb-8 leading-relaxed">
            Comparte tu conocimiento con la comunidad de ingeniería de la región.
          </p>

          <div class="flex flex-col items-center gap-1.5 mb-8">
            <RouterLink
              v-if="registrationOpen"
              to="/login"
              class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-10 py-3.5 rounded-xl hover:opacity-90 transition-opacity text-sm"
            >
              Enviar mi ponencia
            </RouterLink>
            <button
              v-else
              disabled
              class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-10 py-3.5 rounded-xl opacity-50 cursor-not-allowed pointer-events-none text-sm"
            >
              Enviar mi ponencia
            </button>
            <span v-if="!registrationOpen" class="text-cgr-muted text-xs">
              Pronto serán activadas las inscripciones
            </span>
          </div>

          <p class="text-cgr-subtle text-xs">
            ¿Dudas? Escríbenos al correo del Comité Científico:
            <a href="mailto:congreso.ingenieria.bga@upb.edu.co" class="text-cgr-purple hover:text-cgr-accent transition-colors font-medium">
              congreso.ingenieria.bga@upb.edu.co
            </a>
          </p>
        </div>
      </section>

    </main>
    <FooterSection />
  </div>
</template>
