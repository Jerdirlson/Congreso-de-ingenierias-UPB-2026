<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'

interface User {
  id: number
  name: string
  email: string
  role: string
  institution: string | null
  country: string | null
  city: string | null
  phone: string | null
  document_type: string | null
  document_number: string | null
  email_verified_at: string | null
  created_at: string
  submissions_count: number
  registrations_count: number
  payments_count: number
}

interface UserDetail extends User {
  submissions: { id: number; title: string; status: string; created_at: string }[]
  registrations: { id: number; registration_type: string; modality: string; confirmed_at: string | null }[]
  payments: { id: number; amount: number; currency: string; status: string; created_at: string }[]
}

interface Paginated {
  data: User[]
  current_page: number
  last_page: number
  total: number
  per_page: number
}

const users     = ref<User[]>([])
const total     = ref(0)
const lastPage  = ref(1)
const page      = ref(1)
const loading   = ref(true)
const search    = ref('')
const roleFilter = ref('')
const verifiedFilter = ref('')

const selected  = ref<UserDetail | null>(null)
const loadingDetail = ref(false)
const editingRole = ref(false)
const newRole   = ref('')
const savingRole = ref(false)

const roles = ['ponente', 'participante', 'revisor', 'admin', 'administrativo']

const roleColors: Record<string, string> = {
  ponente:        'bg-cgr-purple/20 text-cgr-purple border border-cgr-purple/30',
  participante:   'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30',
  revisor:        'bg-amber-500/15 text-amber-400 border border-amber-500/30',
  admin:          'bg-red-500/15 text-red-400 border border-red-500/30',
  administrativo: 'bg-red-500/15 text-red-400 border border-red-500/30',
}

let searchTimeout: ReturnType<typeof setTimeout>

watch([roleFilter, verifiedFilter], () => {
  page.value = 1
  load()
})

watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => { page.value = 1; load() }, 350)
})

async function load() {
  loading.value = true
  const api = useFetchApi()
  const params = new URLSearchParams({ page: String(page.value) })
  if (search.value)        params.set('search', search.value)
  if (roleFilter.value)    params.set('role', roleFilter.value)
  if (verifiedFilter.value) params.set('verified', verifiedFilter.value)

  const data = await api.get<Paginated>(`/admin/users?${params}`)
  if (data) {
    users.value   = data.data
    total.value   = data.total
    lastPage.value = data.last_page
  }
  loading.value = false
}

async function openDetail(user: User) {
  selected.value = null
  loadingDetail.value = true
  const api = useFetchApi()
  const data = await api.get<UserDetail>(`/admin/users/${user.id}`)
  if (data) {
    selected.value = data
    newRole.value  = data.role ?? ''
  }
  loadingDetail.value = false
  editingRole.value = false
}

async function saveRole() {
  if (!selected.value) return
  savingRole.value = true
  const api = useFetchApi()
  const res = await api.patch<{ id: number; role: string }>(`/admin/users/${selected.value.id}/role`, { role: newRole.value })
  if (res) {
    selected.value.role = res.role
    const idx = users.value.findIndex(u => u.id === selected.value!.id)
    if (idx !== -1) users.value[idx]!.role = res.role
  }
  savingRole.value  = false
  editingRole.value = false
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' })
}

const submissionStatusLabels: Record<string, string> = {
  draft: 'Borrador', abstract_submitted: 'Resumen enviado', abstract_approved: 'Resumen aprobado',
  under_review: 'En revisión', confirmed: 'Confirmado', payment_pending: 'Pago pendiente',
}

onMounted(load)
</script>

<template>
  <div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-white">Usuarios</h1>
        <p class="text-cgr-muted text-sm mt-0.5">{{ total }} usuarios registrados</p>
      </div>
    </div>

    <!-- Filtros -->
    <UiCard class="p-4">
      <div class="flex flex-col sm:flex-row gap-3">
        <!-- Búsqueda -->
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-cgr-subtle" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por nombre, email, institución…"
            class="w-full bg-cgr-bg border border-cgr-border rounded-lg pl-9 pr-4 py-2 text-sm text-white placeholder:text-cgr-subtle focus:outline-none focus:border-cgr-purple"
          />
        </div>
        <!-- Rol -->
        <select
          v-model="roleFilter"
          class="bg-cgr-bg border border-cgr-border rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-cgr-purple"
        >
          <option value="">Todos los roles</option>
          <option v-for="r in roles" :key="r" :value="r" class="capitalize">{{ r }}</option>
        </select>
        <!-- Verificado -->
        <select
          v-model="verifiedFilter"
          class="bg-cgr-bg border border-cgr-border rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-cgr-purple"
        >
          <option value="">Verificación: todos</option>
          <option value="yes">Verificados</option>
          <option value="no">Sin verificar</option>
        </select>
      </div>
    </UiCard>

    <!-- Tabla -->
    <UiCard class="overflow-hidden p-0">
      <div v-if="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-6 h-6 text-cgr-purple" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
      </div>

      <div v-else-if="users.length === 0" class="text-center py-16 text-cgr-subtle text-sm">
        No se encontraron usuarios.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-cgr-border text-cgr-subtle text-xs uppercase tracking-wide">
              <th class="text-left px-4 py-3">Usuario</th>
              <th class="text-left px-4 py-3">Rol</th>
              <th class="text-left px-4 py-3 hidden md:table-cell">Institución</th>
              <th class="text-left px-4 py-3 hidden lg:table-cell">País</th>
              <th class="text-center px-4 py-3 hidden sm:table-cell">Ponencias</th>
              <th class="text-center px-4 py-3 hidden sm:table-cell">Verificado</th>
              <th class="text-left px-4 py-3 hidden lg:table-cell">Registro</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-cgr-border">
            <tr
              v-for="u in users"
              :key="u.id"
              class="hover:bg-cgr-section/50 transition-colors cursor-pointer"
              @click="openDetail(u)"
            >
              <td class="px-4 py-3">
                <p class="text-white font-medium leading-tight">{{ u.name }}</p>
                <p class="text-cgr-subtle text-xs mt-0.5">{{ u.email }}</p>
              </td>
              <td class="px-4 py-3">
                <span
                  :class="roleColors[u.role] ?? 'bg-cgr-border text-cgr-muted'"
                  class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                >
                  {{ u.role ?? '—' }}
                </span>
              </td>
              <td class="px-4 py-3 hidden md:table-cell text-cgr-muted">
                {{ u.institution ?? '—' }}
              </td>
              <td class="px-4 py-3 hidden lg:table-cell text-cgr-muted">
                {{ u.country ?? '—' }}
              </td>
              <td class="px-4 py-3 hidden sm:table-cell text-center text-cgr-muted">
                {{ u.submissions_count }}
              </td>
              <td class="px-4 py-3 hidden sm:table-cell text-center">
                <span v-if="u.email_verified_at" class="text-emerald-400">
                  <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </span>
                <span v-else class="text-cgr-border">
                  <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </span>
              </td>
              <td class="px-4 py-3 hidden lg:table-cell text-cgr-subtle text-xs">
                {{ formatDate(u.created_at) }}
              </td>
              <td class="px-4 py-3 text-cgr-subtle">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="lastPage > 1" class="flex items-center justify-between px-4 py-3 border-t border-cgr-border">
        <p class="text-cgr-subtle text-xs">Página {{ page }} de {{ lastPage }}</p>
        <div class="flex gap-2">
          <button
            :disabled="page <= 1"
            class="px-3 py-1.5 text-xs rounded-lg border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
            @click="page--; load()"
          >Anterior</button>
          <button
            :disabled="page >= lastPage"
            class="px-3 py-1.5 text-xs rounded-lg border border-cgr-border text-cgr-muted hover:text-white hover:border-cgr-purple disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
            @click="page++; load()"
          >Siguiente</button>
        </div>
      </div>
    </UiCard>

  </div>

  <!-- ── Panel de detalle (drawer lateral) ── -->
  <Teleport to="body">
    <div
      v-if="selected || loadingDetail"
      class="fixed inset-0 z-50 flex justify-end"
    >
      <!-- Overlay -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="selected = null" />

      <!-- Drawer -->
      <aside class="relative z-10 w-full max-w-lg bg-cgr-section border-l border-cgr-border flex flex-col h-full overflow-y-auto">

        <!-- Loading -->
        <div v-if="loadingDetail" class="flex-1 flex items-center justify-center">
          <svg class="animate-spin w-6 h-6 text-cgr-purple" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </div>

        <template v-else-if="selected">
          <!-- Header del drawer -->
          <div class="sticky top-0 bg-cgr-section border-b border-cgr-border px-6 py-4 flex items-start justify-between gap-4 z-10">
            <div>
              <h2 class="text-white font-bold text-lg leading-tight">{{ selected.name }}</h2>
              <p class="text-cgr-subtle text-sm mt-0.5">{{ selected.email }}</p>
            </div>
            <button @click="selected = null" class="p-1.5 rounded-lg text-cgr-muted hover:text-white hover:bg-cgr-card transition-colors shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="flex-1 px-6 py-5 space-y-6">

            <!-- Badges -->
            <div class="flex flex-wrap gap-2">
              <span
                :class="roleColors[selected.role] ?? 'bg-cgr-border text-cgr-muted'"
                class="text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide"
              >{{ selected.role ?? '—' }}</span>
              <span
                :class="selected.email_verified_at ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-cgr-border/40 text-cgr-subtle border border-cgr-border'"
                class="text-xs font-semibold px-3 py-1 rounded-full"
              >{{ selected.email_verified_at ? 'Email verificado' : 'Sin verificar' }}</span>
            </div>

            <!-- Info personal -->
            <div class="grid grid-cols-2 gap-3">
              <div v-for="(val, label) in {
                'Teléfono': selected.phone,
                'Tipo doc.': selected.document_type,
                'Documento': selected.document_number,
                'Institución': selected.institution,
                'País': selected.country,
                'Ciudad': selected.city,
                'Registro': formatDate(selected.created_at),
              }" :key="label">
                <p class="text-cgr-subtle text-[10px] uppercase tracking-widest font-semibold mb-0.5">{{ label }}</p>
                <p class="text-white text-sm">{{ val ?? '—' }}</p>
              </div>
            </div>

            <!-- Cambiar rol -->
            <div class="bg-cgr-card border border-cgr-border rounded-xl p-4">
              <div class="flex items-center justify-between mb-3">
                <p class="text-cgr-subtle text-xs font-semibold uppercase tracking-widest">Rol</p>
                <button
                  v-if="!editingRole"
                  @click="editingRole = true"
                  class="text-cgr-purple text-xs font-semibold hover:underline"
                >Cambiar</button>
              </div>
              <div v-if="!editingRole">
                <span
                  :class="roleColors[selected.role] ?? 'bg-cgr-border text-cgr-muted'"
                  class="text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide"
                >{{ selected.role ?? '—' }}</span>
              </div>
              <div v-else class="flex gap-2">
                <select
                  v-model="newRole"
                  class="flex-1 bg-cgr-bg border border-cgr-border rounded-lg px-3 py-1.5 text-sm text-white focus:outline-none focus:border-cgr-purple"
                >
                  <option v-for="r in roles" :key="r" :value="r" class="capitalize">{{ r }}</option>
                </select>
                <button
                  @click="saveRole"
                  :disabled="savingRole"
                  class="px-4 py-1.5 bg-cgr-purple text-white text-sm font-semibold rounded-lg hover:bg-cgr-purple/90 disabled:opacity-50 transition-colors"
                >{{ savingRole ? '…' : 'Guardar' }}</button>
                <button @click="editingRole = false" class="px-3 py-1.5 border border-cgr-border text-cgr-muted text-sm rounded-lg hover:text-white transition-colors">✕</button>
              </div>
            </div>

            <!-- Ponencias -->
            <div v-if="selected.submissions.length">
              <p class="text-cgr-subtle text-xs font-semibold uppercase tracking-widest mb-3">Ponencias ({{ selected.submissions.length }})</p>
              <div class="space-y-2">
                <div
                  v-for="s in selected.submissions"
                  :key="s.id"
                  class="bg-cgr-card border border-cgr-border rounded-lg px-4 py-3"
                >
                  <p class="text-white text-sm font-medium leading-snug">{{ s.title }}</p>
                  <p class="text-cgr-subtle text-xs mt-1">{{ submissionStatusLabels[s.status] ?? s.status }} · {{ formatDate(s.created_at) }}</p>
                </div>
              </div>
            </div>

            <!-- Inscripciones -->
            <div v-if="selected.registrations.length">
              <p class="text-cgr-subtle text-xs font-semibold uppercase tracking-widest mb-3">Inscripciones ({{ selected.registrations.length }})</p>
              <div class="space-y-2">
                <div
                  v-for="r in selected.registrations"
                  :key="r.id"
                  class="bg-cgr-card border border-cgr-border rounded-lg px-4 py-3 flex items-center justify-between"
                >
                  <div>
                    <p class="text-white text-sm capitalize">{{ r.registration_type }} · {{ r.modality }}</p>
                  </div>
                  <span
                    :class="r.confirmed_at ? 'text-emerald-400' : 'text-amber-400'"
                    class="text-xs font-semibold"
                  >{{ r.confirmed_at ? 'Confirmado' : 'Pendiente' }}</span>
                </div>
              </div>
            </div>

            <!-- Pagos -->
            <div v-if="selected.payments.length">
              <p class="text-cgr-subtle text-xs font-semibold uppercase tracking-widest mb-3">Pagos ({{ selected.payments.length }})</p>
              <div class="space-y-2">
                <div
                  v-for="p in selected.payments"
                  :key="p.id"
                  class="bg-cgr-card border border-cgr-border rounded-lg px-4 py-3 flex items-center justify-between"
                >
                  <p class="text-white text-sm">{{ p.amount.toLocaleString('es-CO') }} {{ p.currency }}</p>
                  <span
                    :class="{
                      'text-emerald-400': p.status === 'completed',
                      'text-amber-400':   p.status === 'pending',
                      'text-red-400':     p.status === 'failed',
                    }"
                    class="text-xs font-semibold capitalize"
                  >{{ p.status }}</span>
                </div>
              </div>
            </div>

          </div>
        </template>
      </aside>
    </div>
  </Teleport>
</template>
