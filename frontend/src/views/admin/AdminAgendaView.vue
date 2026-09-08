<script setup lang="ts">
import { ref, computed, onMounted, reactive, watch } from 'vue'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'

/**
 * Editor de la agenda pública. Admin/administrativo (Liney) puede agregar,
 * editar, reordenar y eliminar filas por jornada. Si un día queda sin filas,
 * la web pública cae al póster oficial de ese día.
 */

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

const dias = [
  { key: 'mie', label: 'Miércoles 14' },
  { key: 'jue', label: 'Jueves 15' },
  { key: 'vie', label: 'Viernes 16' },
  { key: 'sab', label: 'Sábado 17' },
]

const bloques = [
  { value: '', label: '— Sin bloque —' },
  { value: 'manana', label: 'Jornada de la mañana' },
  { value: 'tarde', label: 'Jornada de la tarde' },
  { value: 'noche', label: 'Jornada de la noche' },
]

const tipos = [
  { value: 'registro', label: 'Registro' },
  { value: 'apertura', label: 'Apertura / acto' },
  { value: 'conferencia', label: 'Conferencia central' },
  { value: 'ponencia', label: 'Ponencias / sesiones' },
  { value: 'foto', label: 'Foto oficial' },
  { value: 'refrigerio', label: 'Refrigerio' },
  { value: 'almuerzo', label: 'Almuerzo' },
  { value: 'cultural', label: 'Actividad cultural' },
  { value: 'otro', label: 'Otro' },
]

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

const defaultPosters: Record<string, string> = {
  mie: '/carteleras/miercoles-14.png',
  jue: '/carteleras/jueves-15.png',
  vie: '/carteleras/viernes-16.png',
  sab: '/carteleras/sabado-17.png',
}

const api = useFetchApi()
const items = ref<AgendaItem[]>([])
const diasMeta = ref<Record<string, DiaMeta>>({})
const loading = ref(true)
const error = ref('')
const savedMessage = ref('')

const activeDay = ref('mie')
const dayItems = computed(() =>
  items.value.filter(i => i.dia === activeDay.value).sort((a, b) => a.orden - b.orden || a.id - b.id),
)

const currentMeta = computed<DiaMeta | undefined>(() => diasMeta.value[activeDay.value])
const posterThumb = computed(() => currentMeta.value?.poster_url || defaultPosters[activeDay.value])
const posterIsCustom = computed(() => !!currentMeta.value?.poster_url)
const posterVisible = computed(() => currentMeta.value?.poster_visible !== false)
const tableVisible = computed(() => currentMeta.value?.table_visible !== false)

const posterBusy = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const metaDraft = reactive({ nombre: '', fecha: '', mes: '', titulo: '' })

function syncTituloDraft() {
  metaDraft.nombre = currentMeta.value?.nombre ?? ''
  metaDraft.fecha = currentMeta.value?.fecha ?? ''
  metaDraft.mes = currentMeta.value?.mes ?? ''
  metaDraft.titulo = currentMeta.value?.titulo ?? ''
}

async function togglePoster() {
  posterBusy.value = true
  const res = await api.put<DiaMeta>(`/admin/agenda/days/${activeDay.value}`, {
    poster_visible: !posterVisible.value,
  })
  posterBusy.value = false
  if (res) { diasMeta.value[res.dia] = res; flash(res.poster_visible ? 'Póster visible.' : 'Póster oculto.') }
  else error.value = api.error.value?.message ?? 'No se pudo actualizar el póster.'
}

async function toggleTable() {
  posterBusy.value = true
  const res = await api.put<DiaMeta>(`/admin/agenda/days/${activeDay.value}`, {
    table_visible: !tableVisible.value,
  })
  posterBusy.value = false
  if (res) { diasMeta.value[res.dia] = res; flash(res.table_visible ? 'Tabla visible.' : 'Tabla oculta.') }
  else error.value = api.error.value?.message ?? 'No se pudo actualizar la tabla.'
}

async function onPosterFile(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  posterBusy.value = true
  error.value = ''
  const fd = new FormData()
  fd.append('poster', file)
  const res = await api.postForm<DiaMeta>(`/admin/agenda/days/${activeDay.value}/poster`, fd)
  posterBusy.value = false
  if (fileInput.value) fileInput.value.value = ''
  if (res) { diasMeta.value[res.dia] = res; flash('Póster actualizado.') }
  else error.value = api.error.value?.message ?? 'No se pudo subir el póster.'
}

async function removePoster() {
  if (!confirm('¿Quitar el póster subido y volver al oficial?')) return
  posterBusy.value = true
  const res = await api.delete<DiaMeta>(`/admin/agenda/days/${activeDay.value}/poster`)
  posterBusy.value = false
  if (res) { diasMeta.value[res.dia] = res; flash('Se volvió al póster oficial.') }
  else error.value = api.error.value?.message ?? 'No se pudo quitar el póster.'
}

async function saveDayMeta() {
  const res = await api.put<DiaMeta>(`/admin/agenda/days/${activeDay.value}`, {
    nombre: metaDraft.nombre || null,
    fecha: metaDraft.fecha || null,
    mes: metaDraft.mes || null,
    titulo: metaDraft.titulo || null,
  })
  if (res) { diasMeta.value[res.dia] = res; flash('Jornada actualizada.') }
  else error.value = api.error.value?.message ?? 'No se pudo guardar la jornada.'
}

// ── Modal de edición/creación ──────────────────────────────────────────────
const modalOpen = ref(false)
const editingId = ref<number | null>(null)
const saving = ref(false)
const form = reactive({
  dia: 'mie',
  bloque: '',
  tipo: 'otro',
  hora: '',
  titulo: '',
  ponente: '',
  pais: '',
  etiqueta: '',
  descripcion: '',
  lugar: '',
})

function resetForm() {
  form.dia = activeDay.value
  form.bloque = ''
  form.tipo = 'otro'
  form.hora = ''
  form.titulo = ''
  form.ponente = ''
  form.pais = ''
  form.etiqueta = ''
  form.descripcion = ''
  form.lugar = ''
}

function openCreate() {
  editingId.value = null
  resetForm()
  modalOpen.value = true
}

function openEdit(item: AgendaItem) {
  editingId.value = item.id
  form.dia = item.dia
  form.bloque = item.bloque ?? ''
  form.tipo = item.tipo ?? 'otro'
  form.hora = item.hora ?? ''
  form.titulo = item.titulo
  form.ponente = item.ponente ?? ''
  form.pais = item.pais ?? ''
  form.etiqueta = item.etiqueta ?? ''
  form.descripcion = item.descripcion ?? ''
  form.lugar = item.lugar ?? ''
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
}

function flash(msg: string) {
  savedMessage.value = msg
  setTimeout(() => (savedMessage.value = ''), 2500)
}

async function load() {
  loading.value = true
  error.value = ''
  const data = await api.get<{ items: AgendaItem[]; dias: DiaMeta[] }>('/agenda')
  if (data?.items) {
    items.value = data.items
    if (data.dias) diasMeta.value = Object.fromEntries(data.dias.map(d => [d.dia, d]))
    syncTituloDraft()
  } else {
    error.value = 'No se pudo cargar la agenda.'
  }
  loading.value = false
}

async function submitForm() {
  if (!form.titulo.trim()) {
    error.value = 'La actividad (título) es obligatoria.'
    return
  }
  saving.value = true
  error.value = ''
  const payload = {
    dia: form.dia,
    bloque: form.bloque || null,
    tipo: form.tipo,
    hora: form.hora || null,
    titulo: form.titulo,
    ponente: form.ponente || null,
    pais: form.pais || null,
    etiqueta: form.etiqueta || null,
    descripcion: form.descripcion || null,
    lugar: form.lugar || null,
  }
  const result = editingId.value
    ? await api.put<AgendaItem>(`/admin/agenda/${editingId.value}`, payload)
    : await api.post<AgendaItem>('/admin/agenda', payload)
  saving.value = false
  if (result) {
    modalOpen.value = false
    activeDay.value = form.dia
    await load()
    flash(editingId.value ? 'Fila actualizada.' : 'Fila agregada.')
  } else {
    error.value = api.error.value?.message ?? 'No se pudo guardar la fila.'
  }
}

async function remove(item: AgendaItem) {
  if (!confirm(`¿Eliminar "${item.titulo}"?`)) return
  const result = await api.delete(`/admin/agenda/${item.id}`)
  if (result !== null || api.error.value === null) {
    await load()
    flash('Fila eliminada.')
  } else {
    error.value = api.error.value?.message ?? 'No se pudo eliminar.'
  }
}

async function move(item: AgendaItem, dir: -1 | 1) {
  const list = [...dayItems.value]
  const idx = list.findIndex(i => i.id === item.id)
  const target = idx + dir
  if (target < 0 || target >= list.length) return
  ;[list[idx], list[target]] = [list[target]!, list[idx]!]
  const ids = list.map(i => i.id)
  const result = await api.put('/admin/agenda/reorder', { ids })
  if (result !== null || api.error.value === null) await load()
}

watch(activeDay, syncTituloDraft)

onMounted(load)
</script>

<template>
  <div class="max-w-4xl space-y-6">
    <div class="flex items-start justify-between gap-4 flex-wrap">
      <div>
        <h1 class="text-2xl font-bold text-white">Agenda del congreso</h1>
        <p class="text-cgr-muted text-sm mt-1 max-w-2xl">
          Edita la programación por día. Si un día no tiene filas, en la web pública
          se muestra el póster oficial de ese día como respaldo.
        </p>
      </div>
      <button
        @click="openCreate"
        class="inline-flex items-center gap-2 bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white text-sm font-semibold px-4 py-2.5 rounded-lg hover:opacity-90 transition-opacity shrink-0"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Agregar fila
      </button>
    </div>

    <p v-if="error" class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">{{ error }}</p>
    <p v-if="savedMessage" class="text-sm text-green-400 bg-green-500/10 border border-green-500/20 rounded-lg px-3 py-2">{{ savedMessage }}</p>

    <!-- Tabs de días -->
    <div class="flex flex-wrap gap-2">
      <button
        v-for="d in dias"
        :key="d.key"
        @click="activeDay = d.key"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all"
        :class="activeDay === d.key
          ? 'bg-cgr-purple/20 text-cgr-purple border border-cgr-purple/40'
          : 'border border-cgr-border text-cgr-muted hover:text-white'"
      >
        {{ d.label }}
        <span class="ml-1 text-xs opacity-70">({{ items.filter(i => i.dia === d.key).length }})</span>
      </button>
    </div>

    <div v-if="loading" class="text-cgr-muted text-sm py-8 text-center">Cargando…</div>

    <template v-else>
      <!-- Póster de la jornada -->
      <UiCard class="p-5">
        <div class="flex flex-col sm:flex-row gap-5">
          <!-- Miniatura -->
          <div class="shrink-0 w-full sm:w-40">
            <div class="relative rounded-xl overflow-hidden border border-cgr-border bg-cgr-bg/40">
              <img :src="posterThumb" alt="Póster de la jornada" class="w-full h-auto block" :class="{ 'opacity-40': !posterVisible }" />
              <div v-if="!posterVisible" class="absolute inset-0 flex items-center justify-center">
                <span class="text-[10px] font-bold uppercase tracking-wide text-white bg-black/70 px-2 py-1 rounded-full">Oculto</span>
              </div>
            </div>
            <p class="text-cgr-subtle text-[11px] mt-1 text-center">
              {{ posterIsCustom ? 'Póster subido' : 'Póster oficial' }}
            </p>
          </div>

          <!-- Controles -->
          <div class="flex-1 min-w-0 space-y-4">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h2 class="text-white font-semibold text-sm">Póster de la jornada</h2>
                <p class="text-cgr-muted text-xs mt-1 leading-relaxed">
                  Sube la imagen de la agenda de este día o muéstrala/ocúltala en la web pública.
                </p>
              </div>
              <!-- Switch mostrar/ocultar -->
              <button
                type="button"
                role="switch"
                :aria-checked="posterVisible"
                :disabled="posterBusy"
                class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors disabled:opacity-50"
                :class="posterVisible ? 'bg-cgr-purple' : 'bg-cgr-border'"
                @click="togglePoster"
                :title="posterVisible ? 'Ocultar póster' : 'Mostrar póster'"
              >
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="posterVisible ? 'translate-x-6' : 'translate-x-1'" />
              </button>
            </div>

            <p class="text-xs font-medium" :class="posterVisible ? 'text-green-400' : 'text-amber-400'">
              {{ posterVisible ? 'Visible en la web pública' : 'Oculto — no se muestra en la web' }}
            </p>

            <div class="flex flex-wrap items-center gap-2">
              <input ref="fileInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="onPosterFile" />
              <button
                type="button"
                :disabled="posterBusy"
                @click="fileInput?.click()"
                class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white hover:opacity-90 disabled:opacity-50"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                {{ posterBusy ? 'Procesando…' : 'Subir póster' }}
              </button>
              <button
                v-if="posterIsCustom"
                type="button"
                :disabled="posterBusy"
                @click="removePoster"
                class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg border border-cgr-border text-cgr-muted hover:text-white disabled:opacity-50"
              >
                Volver al oficial
              </button>
            </div>

            <!-- Datos editables de la jornada -->
            <div class="pt-1 space-y-3">
              <div class="grid grid-cols-3 gap-2">
                <label class="block">
                  <span class="text-cgr-muted text-xs font-medium">Día</span>
                  <input v-model="metaDraft.nombre" type="text" placeholder="Miércoles" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none" />
                </label>
                <label class="block">
                  <span class="text-cgr-muted text-xs font-medium">Fecha</span>
                  <input v-model="metaDraft.fecha" type="text" placeholder="14" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none" />
                </label>
                <label class="block">
                  <span class="text-cgr-muted text-xs font-medium">Mes</span>
                  <input v-model="metaDraft.mes" type="text" placeholder="octubre" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none" />
                </label>
              </div>
              <label class="block">
                <span class="text-cgr-muted text-xs font-medium">Título / eje de la jornada</span>
                <input v-model="metaDraft.titulo" type="text" placeholder="Transformación Digital y Tecnología Humanocéntrica" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none" />
              </label>
              <button type="button" @click="saveDayMeta" class="px-4 py-2 rounded-lg text-sm font-semibold text-cgr-muted hover:text-white border border-cgr-border">Guardar datos de la jornada</button>
            </div>
          </div>
        </div>
      </UiCard>

      <!-- Visibilidad de la tabla en la web -->
      <UiCard class="p-5">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-white font-semibold text-sm">Tabla detallada en la web</h2>
            <p class="text-cgr-muted text-xs mt-1 leading-relaxed">
              Cuando está visible, la web pública muestra la tabla de actividades de este día (debajo del póster).
              Ocúltala si por ahora solo quieres mostrar el póster.
            </p>
            <p class="text-xs mt-2 font-medium" :class="tableVisible ? 'text-green-400' : 'text-amber-400'">
              {{ tableVisible ? 'Visible en la web pública' : 'Oculta — no se muestra en la web' }}
            </p>
          </div>
          <button
            type="button"
            role="switch"
            :aria-checked="tableVisible"
            :disabled="posterBusy"
            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors disabled:opacity-50"
            :class="tableVisible ? 'bg-cgr-purple' : 'bg-cgr-border'"
            @click="toggleTable"
            :title="tableVisible ? 'Ocultar tabla' : 'Mostrar tabla'"
          >
            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="tableVisible ? 'translate-x-6' : 'translate-x-1'" />
          </button>
        </div>
      </UiCard>

      <UiCard v-if="dayItems.length === 0" class="p-8 text-center">
        <p class="text-cgr-muted text-sm">
          Este día no tiene filas en la tabla.<br />
          En la web se mostrará el póster de arriba (si está visible). Agrega filas para publicar la tabla detallada.
        </p>
      </UiCard>

      <div v-else class="space-y-2">
        <UiCard
          v-for="(item, idx) in dayItems"
          :key="item.id"
          class="p-4 flex items-start gap-3"
        >
          <!-- Reordenar -->
          <div class="flex flex-col gap-1 shrink-0 pt-0.5">
            <button
              @click="move(item, -1)"
              :disabled="idx === 0"
              class="w-6 h-6 rounded flex items-center justify-center text-cgr-muted hover:text-white hover:bg-cgr-card disabled:opacity-30 disabled:cursor-not-allowed"
              title="Subir"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
            </button>
            <button
              @click="move(item, 1)"
              :disabled="idx === dayItems.length - 1"
              class="w-6 h-6 rounded flex items-center justify-center text-cgr-muted hover:text-white hover:bg-cgr-card disabled:opacity-30 disabled:cursor-not-allowed"
              title="Bajar"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
          </div>

          <!-- Datos -->
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span v-if="item.hora" class="text-cgr-purple font-bold text-xs">{{ item.hora }}</span>
              <span class="text-[10px] uppercase tracking-wide text-cgr-subtle bg-cgr-card border border-cgr-border px-2 py-0.5 rounded-full">{{ item.tipo }}</span>
              <span v-if="item.bloque" class="text-[10px] uppercase tracking-wide text-cgr-subtle">{{ bloques.find(b => b.value === item.bloque)?.label }}</span>
              <span v-if="item.etiqueta" class="text-[10px] font-bold uppercase text-cgr-purple bg-cgr-purple/10 border border-cgr-purple/20 px-2 py-0.5 rounded-full">{{ item.etiqueta }}</span>
            </div>
            <p class="text-white font-semibold text-sm mt-1">{{ item.titulo }}</p>
            <p v-if="item.ponente" class="text-cgr-muted text-sm">{{ item.ponente }}<span v-if="item.pais" class="text-cgr-subtle"> · {{ item.pais }}</span></p>
            <p v-if="item.descripcion" class="text-cgr-subtle text-xs mt-0.5">{{ item.descripcion }}</p>
            <p v-if="item.lugar" class="text-cgr-subtle text-xs mt-0.5">📍 {{ item.lugar }}</p>
          </div>

          <!-- Acciones -->
          <div class="flex items-center gap-1 shrink-0">
            <button @click="openEdit(item)" class="w-8 h-8 rounded-lg flex items-center justify-center text-cgr-muted hover:text-white hover:bg-cgr-card" title="Editar">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            <button @click="remove(item)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-400/70 hover:text-red-400 hover:bg-red-500/10" title="Eliminar">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </UiCard>
      </div>
    </template>

    <!-- Modal crear/editar -->
    <Transition name="fade">
      <div v-if="modalOpen" class="fixed inset-0 z-[100] bg-black/70 backdrop-blur-sm flex items-start sm:items-center justify-center p-4 overflow-y-auto" @click.self="closeModal">
        <div class="bg-cgr-section border border-cgr-border rounded-2xl w-full max-w-xl my-8 shadow-2xl">
          <div class="flex items-center justify-between p-5 border-b border-cgr-border">
            <h2 class="text-white font-bold">{{ editingId ? 'Editar fila' : 'Agregar fila' }}</h2>
            <button @click="closeModal" class="w-8 h-8 rounded-lg flex items-center justify-center text-cgr-muted hover:text-white hover:bg-cgr-card">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-3">
              <label class="block">
                <span class="text-cgr-muted text-xs font-medium">Día</span>
                <select v-model="form.dia" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none">
                  <option v-for="d in dias" :key="d.key" :value="d.key">{{ d.label }}</option>
                </select>
              </label>
              <label class="block">
                <span class="text-cgr-muted text-xs font-medium">Bloque</span>
                <select v-model="form.bloque" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none">
                  <option v-for="b in bloques" :key="b.value" :value="b.value">{{ b.label }}</option>
                </select>
              </label>
              <label class="block">
                <span class="text-cgr-muted text-xs font-medium">Tipo de actividad</span>
                <select v-model="form.tipo" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none">
                  <option v-for="t in tipos" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
              </label>
              <label class="block">
                <span class="text-cgr-muted text-xs font-medium">Hora <span class="text-cgr-subtle">(opcional)</span></span>
                <input v-model="form.hora" type="text" placeholder="8:00 – 9:00 a.m." class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none" />
              </label>
            </div>

            <label class="block">
              <span class="text-cgr-muted text-xs font-medium">Actividad <span class="text-red-400">*</span></span>
              <input v-model="form.titulo" type="text" placeholder="Registro general y entrega de kits" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none" />
            </label>

            <div class="grid grid-cols-2 gap-3">
              <label class="block">
                <span class="text-cgr-muted text-xs font-medium">Ponente <span class="text-cgr-subtle">(opcional)</span></span>
                <input v-model="form.ponente" type="text" placeholder="Dra. Sindey C. Bernal" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none" />
              </label>
              <label class="block">
                <span class="text-cgr-muted text-xs font-medium">País <span class="text-cgr-subtle">(opcional)</span></span>
                <input v-model="form.pais" type="text" placeholder="Colombia" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none" />
              </label>
              <label class="block">
                <span class="text-cgr-muted text-xs font-medium">Etiqueta <span class="text-cgr-subtle">(opcional)</span></span>
                <input v-model="form.etiqueta" type="text" placeholder="Conferencia Central" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none" />
              </label>
              <label class="block">
                <span class="text-cgr-muted text-xs font-medium">Lugar <span class="text-cgr-subtle">(opcional)</span></span>
                <input v-model="form.lugar" type="text" placeholder="Auditorio principal" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none" />
              </label>
            </div>

            <label class="block">
              <span class="text-cgr-muted text-xs font-medium">Descripción / título de la charla <span class="text-cgr-subtle">(opcional)</span></span>
              <textarea v-model="form.descripcion" rows="2" placeholder="La Revolución de la Tecnología al Servicio de la Humanidad" class="mt-1 w-full bg-cgr-card border border-cgr-border rounded-lg px-3 py-2 text-white text-sm focus:border-cgr-purple outline-none resize-none"></textarea>
            </label>
          </div>

          <div class="flex items-center justify-end gap-2 p-5 border-t border-cgr-border">
            <button @click="closeModal" class="px-4 py-2 rounded-lg text-sm font-semibold text-cgr-muted hover:text-white border border-cgr-border">Cancelar</button>
            <button
              @click="submitForm"
              :disabled="saving"
              class="px-4 py-2 rounded-lg text-sm font-semibold bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white hover:opacity-90 disabled:opacity-50"
            >
              {{ saving ? 'Guardando…' : (editingId ? 'Guardar cambios' : 'Agregar') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }
</style>
