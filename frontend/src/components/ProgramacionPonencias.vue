<script setup lang="ts">
import { ref, computed } from 'vue'
import { programacionPonencias } from '../data/programacionPonencias'

/**
 * Sección "Ponencias" de la agenda: tarjetas con los PDF de la programación de
 * ponencias internas (Drive público). Cada PDF se puede ver en línea (visor
 * embebido de Drive) o descargar. Si la lista está vacía, no se muestra nada.
 */

interface Documento {
  titulo: string
  descripcion?: string
  dia?: string
  /** Enlace para abrir en Drive (pestaña nueva). */
  abrir: string
  /** Visor embebible (solo archivos). */
  preview: string | null
  /** Descarga directa (solo archivos). */
  descarga: string | null
  esCarpeta: boolean
  destacado: boolean
}

function driveId(url: string, patron: RegExp): string | null {
  return url.match(patron)?.[1] ?? null
}

function toDocumento(d: (typeof programacionPonencias)[number]): Documento {
  const url = d.url.trim()
  const base = { titulo: d.titulo, descripcion: d.descripcion, dia: d.dia, destacado: !!d.destacado }

  const carpeta = driveId(url, /drive\.google\.com\/drive\/(?:u\/\d+\/)?folders\/([\w-]+)/)
  if (carpeta) {
    return { ...base, abrir: `https://drive.google.com/drive/folders/${carpeta}`, preview: null, descarga: null, esCarpeta: true }
  }

  const archivo = driveId(url, /drive\.google\.com\/file\/d\/([\w-]+)/) ?? driveId(url, /drive\.google\.com\/.*[?&]id=([\w-]+)/)
  if (archivo) {
    return {
      ...base,
      abrir: `https://drive.google.com/file/d/${archivo}/view`,
      preview: `https://drive.google.com/file/d/${archivo}/preview`,
      descarga: `https://drive.google.com/uc?export=download&id=${archivo}`,
      esCarpeta: false,
    }
  }

  // Otro enlace (SharePoint/OneDrive, PDF directo…): se abre en pestaña nueva;
  // descarga directa si se indicó, si no el mismo enlace.
  return { ...base, abrir: url, preview: null, descarga: d.descarga ?? url, esCarpeta: false }
}

const documentos = computed(() => programacionPonencias.map(toDocumento))

// Visor en modal
const abierto = ref<Documento | null>(null)
function verEnLinea(doc: Documento) {
  if (doc.preview) abierto.value = doc
  else window.open(doc.abrir, '_blank', 'noopener')
}
function cerrar() { abierto.value = null }
</script>

<template>
  <section v-if="documentos.length" id="ponencias" class="bg-cgr-bg py-20 px-5 lg:px-20 scroll-mt-16">
    <div class="max-w-5xl mx-auto">

      <div class="text-center mb-10">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Ponencias</span>
        <h2 class="mt-3 text-3xl font-black text-white">Programación de ponencias</h2>
        <p class="mt-3 text-cgr-muted text-sm leading-relaxed max-w-2xl mx-auto">
          Consulta en qué sala y a qué hora te corresponde presentar. Los documentos se
          actualizan constantemente: revísalos antes de tu ponencia.
        </p>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <article
          v-for="(doc, i) in documentos"
          :key="i"
                  :class="doc.destacado ? 'sm:col-span-2 border-cgr-purple/40' : 'border-cgr-border'"
          class="group flex flex-col gap-5 bg-cgr-card border rounded-2xl p-5 sm:p-6 transition-all hover:border-cgr-purple/60 hover:shadow-lg hover:shadow-cgr-purple/10"
        >
          <div class="flex items-start gap-4">
            <!-- Ícono -->
            <div class="shrink-0 w-12 h-12 rounded-xl bg-cgr-purple/10 border border-cgr-purple/20 flex items-center justify-center">
              <svg v-if="doc.esCarpeta" class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
              </svg>
              <svg v-else class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5M9 13h6M9 17h4" />
              </svg>
            </div>

            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <span
                  v-if="doc.dia"
                  class="text-[10px] font-bold uppercase tracking-wide text-cgr-purple bg-cgr-purple/10 border border-cgr-purple/20 px-2 py-0.5 rounded-full"
                >{{ doc.dia }}</span>
                <span class="text-[10px] font-bold uppercase tracking-wide text-cgr-subtle">
                  {{ doc.esCarpeta ? 'Carpeta' : 'PDF' }}
                </span>
              </div>
              <h3 class="mt-1.5 text-white font-bold leading-snug">{{ doc.titulo }}</h3>
              <p v-if="doc.descripcion" class="mt-1 text-cgr-muted text-sm leading-relaxed">{{ doc.descripcion }}</p>
            </div>
          </div>

          <!-- Acciones -->
          <div class="mt-auto flex flex-wrap gap-2">
            <button
              @click="verEnLinea(doc)"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white shadow-lg shadow-cgr-purple/20 transition-all hover:opacity-90"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
              {{ doc.esCarpeta ? 'Abrir carpeta' : 'Ver en línea' }}
            </button>
            <a
              v-if="doc.descarga"
              :href="doc.descarga"
              target="_blank"
              rel="noopener"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple transition-all"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5 5-5M12 15V3" />
              </svg>
              Descargar
            </a>
          </div>
        </article>
      </div>
    </div>

    <!-- Visor del PDF -->
    <Transition name="fade">
      <div
        v-if="abierto"
        @click="cerrar"
        class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-center justify-center p-3 sm:p-8"
      >
        <div @click.stop class="w-full max-w-5xl h-full flex flex-col bg-cgr-card border border-cgr-border rounded-2xl overflow-hidden shadow-2xl">
          <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3 border-b border-cgr-border">
            <p class="min-w-0 truncate text-white font-semibold text-sm">{{ abierto.titulo }}</p>
            <div class="flex items-center gap-2 shrink-0">
              <a
                v-if="abierto.descarga"
                :href="abierto.descarga"
                target="_blank"
                rel="noopener"
                class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple transition-all"
              >Descargar</a>
              <a
                :href="abierto.abrir"
                target="_blank"
                rel="noopener"
                class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple transition-all"
              >Abrir en Drive</a>
              <button
                @click="cerrar"
                class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center transition-colors"
                aria-label="Cerrar"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          <iframe
            v-if="abierto.preview"
            :src="abierto.preview"
            :title="abierto.titulo"
            class="flex-1 w-full bg-white"
            allow="autoplay"
          />
        </div>
      </div>
    </Transition>
  </section>
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
