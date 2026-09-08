<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import NavBar from '../../components/NavBar.vue'
import FooterSection from '../../components/FooterSection.vue'
import { useFetchApi } from '../../composables/useFetchApi'

/**
 * Agenda pública — tabla editable por día, con póster como respaldo.
 *
 * Cada jornada (miércoles 14 a sábado 17 de octubre) puede administrarse desde
 * el panel (admin/administrativo). Si un día tiene filas cargadas se muestra la
 * tabla; si no, se cae al póster oficial de Comunicaciones. Así Liney puede
 * publicar cambios de última hora sin depender de rehacer las piezas gráficas.
 */

interface Jornada {
  key: string
  fecha: string
  dia: string
  titulo: string
  imagen: string
}

interface AgendaItem {
  id: number
  dia: string
  bloque: string | null
  tipo: string
  hora: string | null
  titulo: string
  ponente: string | null
  pais: string | null
  etiqueta: string | null
  descripcion: string | null
  lugar: string | null
  orden: number
}

const jornadas: Jornada[] = [
  { key: 'mie', fecha: '14', dia: 'Miércoles', titulo: 'Transformación Digital y Tecnología Humanocéntrica', imagen: '/carteleras/miercoles-14.png' },
  { key: 'jue', fecha: '15', dia: 'Jueves', titulo: 'Tecnologías Emergentes y Sociedad', imagen: '/carteleras/jueves-15.png' },
  { key: 'vie', fecha: '16', dia: 'Viernes', titulo: 'Sostenibilidad, Inteligencia Avanzada y Redes del Futuro', imagen: '/carteleras/viernes-16.png' },
  { key: 'sab', fecha: '17', dia: 'Sábado', titulo: 'Integración y Salud', imagen: '/carteleras/sabado-17.png' },
]

const bloqueLabels: Record<string, string> = {
  manana: 'Jornada de la mañana',
  tarde: 'Jornada de la tarde',
  noche: 'Jornada de la noche',
}

interface DiaMeta {
  dia: string
  nombre: string | null
  fecha: string | null
  mes: string | null
  titulo: string | null
  poster_url: string | null
  poster_visible: boolean
  table_visible: boolean
}

const api = useFetchApi()
const porDia = ref<Record<string, AgendaItem[]>>({})
const diasMeta = ref<Record<string, DiaMeta>>({})
const loading = ref(true)

const activeDay = ref('mie')
const current = computed(() => jornadas.find(j => j.key === activeDay.value) ?? jornadas[0]!)
const currentItems = computed(() => porDia.value[activeDay.value] ?? [])
const currentMeta = computed<DiaMeta | undefined>(() => diasMeta.value[activeDay.value])
const currentTitulo = computed(() => currentMeta.value?.titulo || current.value.titulo)
const currentNombre = computed(() => currentMeta.value?.nombre || current.value.dia)
const currentFecha = computed(() => currentMeta.value?.fecha || current.value.fecha)
const currentMes = computed(() => currentMeta.value?.mes || 'octubre')
// Etiqueta del tab (nombre + fecha editables, con fallback al valor por defecto).
function tabLabel(j: Jornada) {
  const m = diasMeta.value[j.key]
  return `${m?.nombre || j.dia} ${m?.fecha || j.fecha}`
}
// Póster a mostrar: el subido desde el panel, o el oficial por defecto.
const posterSrc = computed(() => currentMeta.value?.poster_url || current.value.imagen)
// Visible salvo que el admin lo haya ocultado.
const showPoster = computed(() => currentMeta.value?.poster_visible !== false)
// La tabla se muestra si el admin no la ocultó y hay filas cargadas.
const showTable = computed(() => currentMeta.value?.table_visible !== false && currentItems.value.length > 0)

/** Filas del día activo agrupadas por bloque, respetando el orden. */
const currentGroups = computed(() => {
  const groups: { bloque: string | null; items: AgendaItem[] }[] = []
  for (const item of currentItems.value) {
    const last = groups[groups.length - 1]
    if (last && last.bloque === item.bloque) last.items.push(item)
    else groups.push({ bloque: item.bloque, items: [item] })
  }
  return groups
})

async function load() {
  loading.value = true
  const data = await api.get<{ por_dia: Record<string, AgendaItem[]>; dias: DiaMeta[] }>('/agenda')
  if (data?.por_dia) porDia.value = data.por_dia
  if (data?.dias) {
    diasMeta.value = Object.fromEntries(data.dias.map(d => [d.dia, d]))
  }
  loading.value = false
}

// Lightbox
const zoomed = ref(false)
function openZoom() { zoomed.value = true }
function closeZoom() { zoomed.value = false }

const tipoIcons: Record<string, string> = {
  registro: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
  apertura: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.247m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.247',
  conferencia: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
  ponencia: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
  foto: 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z',
  refrigerio: 'M3 3h12M3 3v10a4 4 0 004 4h4a4 4 0 004-4V3M15 6h3a2 2 0 012 2v1a2 2 0 01-2 2h-3',
  almuerzo: 'M3 3v18M8 3v6a3 3 0 01-3 3M8 3v18M21 3l-2 9m0 0v9m0-9h-4',
  cultural: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z',
  otro: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
}
function iconFor(tipo: string) { return tipoIcons[tipo] ?? tipoIcons.otro }

onMounted(load)
</script>

<template>
  <div class="min-h-screen bg-cgr-bg">
    <NavBar />

    <main class="pt-16 lg:pl-72">

      <!-- HERO -->
      <section class="hero-gradient relative py-28 px-5 lg:px-20 overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cgr-purple-deep/30 rounded-full blur-3xl pointer-events-none" />
        <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-cgr-purple-dark/20 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 max-w-3xl mx-auto text-center">
          <div class="inline-flex items-center gap-2 border border-cgr-purple-dark rounded-full px-4 py-1.5 mb-8">
            <span class="w-2 h-2 rounded-full bg-cgr-purple animate-pulse" />
            <span class="text-cgr-accent text-xs font-semibold tracking-widest uppercase">14 al 17 de octubre · 2026</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight mb-6">
            <span class="text-white">Agenda del</span><br />
            <span class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple bg-clip-text text-transparent">
              Congreso
            </span>
          </h1>

          <p class="text-cgr-muted text-lg leading-relaxed max-w-2xl mx-auto">
            Cuatro jornadas en el campus de la
            <span class="text-white font-semibold">UPB Bucaramanga</span>,
            cada una con un eje temático propio.
          </p>
        </div>
      </section>

      <!-- JORNADAS -->
      <section class="bg-cgr-section py-20 px-5 lg:px-20">
        <div class="max-w-5xl mx-auto">

          <div class="text-center mb-10">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Programación por día</span>
            <h2 class="mt-3 text-3xl font-black text-white">Cuatro días, cuatro enfoques</h2>
          </div>

          <!-- Aviso: la agenda todavía es preliminar -->
          <div class="mb-10 flex items-start gap-3 bg-cgr-card border border-cgr-purple/30 rounded-2xl px-5 py-4 max-w-3xl mx-auto">
            <svg class="w-5 h-5 text-cgr-purple shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <p class="text-sm text-cgr-muted leading-relaxed">
              Esta agenda es <strong class="text-white font-semibold">preliminar</strong> y puede cambiar.
              Selecciona un día para ver su programación completa.
            </p>
          </div>

          <!-- Tabs de días -->
          <div class="flex flex-wrap justify-center gap-2 sm:gap-3 mb-10">
            <button
              v-for="j in jornadas"
              :key="j.key"
              @click="activeDay = j.key"
              class="shrink-0 px-4 sm:px-5 py-2.5 rounded-xl text-sm font-semibold transition-all"
              :class="activeDay === j.key
                ? 'bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white shadow-lg shadow-cgr-purple/20'
                : 'border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple'"
            >
              {{ tabLabel(j) }}
            </button>
          </div>

          <!-- Card del día activo -->
          <div class="bg-cgr-card border border-cgr-border rounded-3xl overflow-hidden">
            <!-- Cabecera del día -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-6 sm:p-8 border-b border-cgr-border">
              <div class="min-w-0">
                <div class="flex items-baseline gap-2">
                  <span class="text-white font-black text-2xl sm:text-3xl">{{ currentNombre }}</span>
                  <span class="text-cgr-purple font-bold text-lg">{{ currentFecha }} de {{ currentMes }}</span>
                </div>
                <p class="mt-1 text-cgr-muted text-sm leading-snug">{{ currentTitulo }}</p>
              </div>
              <!-- Acciones del póster (solo cuando el póster está visible) -->
              <div v-if="!loading && showPoster" class="flex items-center gap-2 shrink-0">
                <button
                  @click="openZoom"
                  class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple transition-all"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 8v6M8 11h6M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0z"/>
                  </svg>
                  Ampliar
                </button>
                <a
                  :href="posterSrc"
                  :download="`agenda-${current.dia.toLowerCase()}-${current.fecha}.png`"
                  class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white shadow-lg shadow-cgr-purple/20 transition-all hover:opacity-90"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5 5-5M12 15V3"/>
                  </svg>
                  Descargar
                </a>
              </div>
            </div>

            <!-- Cargando -->
            <div v-if="loading" class="p-10 text-center text-cgr-muted text-sm">Cargando agenda…</div>

            <template v-else>
            <!-- Póster del día (si está visible) -->
            <div v-if="showPoster" class="p-4 sm:p-6 bg-cgr-bg/40 flex justify-center border-b border-cgr-border">
              <button
                @click="openZoom"
                class="group relative block w-full max-w-2xl rounded-2xl overflow-hidden cursor-zoom-in"
                aria-label="Ampliar agenda"
              >
                <img
                  :src="posterSrc"
                  :alt="`Agenda ${current.dia} ${current.fecha} de octubre — ${currentTitulo}`"
                  class="w-full h-auto block transition-transform duration-300 group-hover:scale-[1.01]"
                  loading="lazy"
                />
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors" />
              </button>
            </div>

            <!-- Estado vacío: sin póster y sin tabla -->
            <div v-if="!showPoster && !showTable" class="p-10 text-center text-cgr-muted text-sm">
              La programación de esta jornada se publicará pronto.
            </div>

            <!-- Tabla del día (si está visible y hay filas) -->
            <div v-if="showTable" class="p-4 sm:p-6">
              <div v-for="(group, gi) in currentGroups" :key="gi" class="mb-6 last:mb-0">
                <div v-if="group.bloque" class="flex items-center gap-2 mb-3 px-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-cgr-purple" />
                  <h3 class="text-cgr-accent text-xs font-bold tracking-widest uppercase">
                    {{ bloqueLabels[group.bloque] ?? group.bloque }}
                  </h3>
                </div>

                <div class="rounded-2xl border border-cgr-border overflow-hidden divide-y divide-cgr-border">
                  <div
                    v-for="item in group.items"
                    :key="item.id"
                    class="flex gap-3 sm:gap-4 p-4 bg-cgr-bg/40 hover:bg-cgr-bg/70 transition-colors"
                  >
                    <!-- Hora -->
                    <div v-if="item.hora" class="shrink-0 w-16 sm:w-20 pt-0.5">
                      <span class="text-cgr-purple font-bold text-xs sm:text-sm">{{ item.hora }}</span>
                    </div>
                    <!-- Ícono por tipo -->
                    <div class="shrink-0 w-9 h-9 rounded-lg bg-cgr-purple/10 border border-cgr-purple/20 flex items-center justify-center">
                      <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="iconFor(item.tipo)" />
                      </svg>
                    </div>
                    <!-- Contenido -->
                    <div class="min-w-0 flex-1">
                      <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="text-white font-semibold text-sm leading-snug">{{ item.titulo }}</span>
                        <span
                          v-if="item.etiqueta"
                          class="text-[10px] font-bold uppercase tracking-wide text-cgr-purple bg-cgr-purple/10 border border-cgr-purple/20 px-2 py-0.5 rounded-full"
                        >{{ item.etiqueta }}</span>
                      </div>
                      <p v-if="item.ponente" class="text-cgr-muted text-sm mt-0.5">
                        {{ item.ponente }}
                        <span v-if="item.pais" class="text-cgr-subtle">· {{ item.pais }}</span>
                      </p>
                      <p v-if="item.descripcion" class="text-cgr-subtle text-xs mt-1 leading-relaxed">{{ item.descripcion }}</p>
                      <p v-if="item.lugar" class="text-cgr-subtle text-xs mt-1 inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 9.2c0 7.3-8 11.8-8 11.8z"/>
                          <circle cx="12" cy="10" r="3"/>
                        </svg>
                        {{ item.lugar }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            </template>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="bg-cgr-bg py-20 px-5 lg:px-20">
        <div class="max-w-3xl mx-auto text-center">
          <h2 class="text-2xl sm:text-3xl font-black text-white mb-4">
            ¿Quieres participar como ponente?
          </h2>
          <p class="text-cgr-muted leading-relaxed mb-8">
            Consulta el llamado a ponencias, los ejes temáticos y las fechas clave del congreso.
          </p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <RouterLink
              to="/ponencias"
              class="inline-flex items-center justify-center bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white text-sm font-semibold px-6 py-3 rounded-lg hover:opacity-90 transition-opacity"
            >
              Llamado a Ponencias
            </RouterLink>
            <RouterLink
              to="/conferencistas"
              class="inline-flex items-center justify-center border border-cgr-purple/50 text-cgr-purple hover:bg-cgr-purple/10 text-sm font-semibold px-6 py-3 rounded-lg transition-colors"
            >
              Ver conferencistas
            </RouterLink>
          </div>
        </div>
      </section>

      <FooterSection />
    </main>

    <!-- Lightbox -->
    <Transition name="fade">
      <div
        v-if="zoomed"
        @click="closeZoom"
        class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 sm:p-8 cursor-zoom-out"
      >
        <button
          @click.stop="closeZoom"
          class="absolute top-4 right-4 sm:top-6 sm:right-6 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center transition-colors"
          aria-label="Cerrar"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <img
          :src="posterSrc"
          :alt="`Agenda ${current.dia} ${current.fecha} de octubre`"
          class="max-w-full max-h-full w-auto h-auto object-contain rounded-lg shadow-2xl"
          @click.stop
        />
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
