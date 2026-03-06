<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiBadge from '../../components/ui/UiBadge.vue'
import UiButton from '../../components/ui/UiButton.vue'

const router = useRouter()

interface Registration {
  id: number
  registration_type: string
  ticket_code: string | null
  confirmed_at: string | null
  congress_event?: { id: number; name: string; event_date: string } | null
}

interface CongressEvent {
  id: number
  name: string
  description: string | null
  location: string | null
  modality: string
  event_date: string
  start_time: string | null
  end_time: string | null
  is_free: boolean
  price: number
  currency: string
}

interface Submission {
  id: number
  title: string
  status: string
  modality: string | null
  thematic_axis?: { id: number; name: string }
}

const registration = ref<Registration | null>(null)
const events = ref<CongressEvent[]>([])
const submissions = ref<Submission[]>([])
const loading = ref(true)
const paying = ref(false)
const payError = ref('')
const ticketCode = ref<string | null>(null)
const confirmDeleteId = ref<number | null>(null)
const deleting = ref(false)
const deleteError = ref('')

const speakerRegistered = computed(() => !!registration.value?.ticket_code)

const statusLabels: Record<string, string> = {
  draft: 'Borrador',
  abstract_submitted: 'Resumen enviado',
  abstract_rejected: 'Resumen rechazado',
  abstract_approved: 'Resumen aprobado',
  under_review: 'En revisión',
  revision_requested: 'Revisión solicitada',
  document_approved: 'Documento aprobado',
  modality_selected: 'Modalidad elegida',
  video_pending: 'Video pendiente',
  video_ready: 'Video listo',
  payment_pending: 'Pago pendiente',
  confirmed: 'Confirmado',
}

const statusVariants: Record<string, 'default' | 'warning' | 'danger' | 'success' | 'info' | 'purple'> = {
  draft: 'default',
  abstract_submitted: 'info',
  abstract_rejected: 'danger',
  abstract_approved: 'success',
  under_review: 'info',
  revision_requested: 'warning',
  document_approved: 'success',
  modality_selected: 'purple',
  video_pending: 'warning',
  video_ready: 'success',
  payment_pending: 'warning',
  confirmed: 'success',
}

function formatDate(dateStr: string) {
  return new Date(dateStr + 'T00:00:00').toLocaleDateString('es-CO', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  })
}

function formatTime(time: string | null) {
  if (!time) return ''
  return time.slice(0, 5)
}

function formatPrice(price: number, currency: string) {
  return new Intl.NumberFormat('es-CO', { style: 'currency', currency, maximumFractionDigits: 0 }).format(price)
}

async function loadData() {
  loading.value = true
  const api1 = useFetchApi()
  const api2 = useFetchApi()
  const api3 = useFetchApi()

  const [regsData, eventsData, subsData] = await Promise.all([
    api1.get<Registration[]>('/registrations'),
    api2.get<CongressEvent[]>('/events'),
    api3.get<{ data: Submission[] } | Submission[]>('/submissions'),
  ])

  if (regsData) {
    registration.value = (regsData as Registration[]).find(r => r.registration_type === 'speaker') ?? null
  }
  if (eventsData) events.value = eventsData
  if (subsData) {
    submissions.value = Array.isArray(subsData) ? subsData : (subsData as { data: Submission[] }).data ?? []
  }
  loading.value = false
}

async function confirmInscription(event: CongressEvent) {
  payError.value = ''
  paying.value = true
  const api = useFetchApi()
  const data = await api.post<{ ticket_code: string }>('/payments', {
    registration_type: 'speaker',
    congress_event_id: event.id,
  })
  paying.value = false
  if (data?.ticket_code) {
    ticketCode.value = data.ticket_code
    await loadData()
  } else {
    payError.value = api.error.value?.message ?? 'Error al procesar la inscripción'
  }
}

async function deleteSubmission(id: number) {
  deleting.value = true
  deleteError.value = ''
  const api = useFetchApi()
  await api.delete(`/submissions/${id}`)
  deleting.value = false
  if (api.error.value) {
    deleteError.value = api.error.value.message ?? 'No se pudo eliminar la ponencia.'
  } else {
    confirmDeleteId.value = null
    await loadData()
  }
}

onMounted(loadData)
</script>

<template>
  <div class="max-w-4xl">
    <div v-if="loading" class="text-center py-12 text-cgr-muted">Cargando…</div>

    <template v-else>

      <!-- ── PASO 0: Sin inscripción → seleccionar evento y pagar ── -->
      <div v-if="!speakerRegistered">
        <h1 class="text-2xl font-bold text-white mb-2">Inscripción como ponente</h1>
        <p class="text-sm text-cgr-muted mb-6">
          Para poder registrar tu ponencia primero debes inscribirte al congreso.
        </p>

        <!-- Banner provisional -->
        <div class="flex items-start gap-3 bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 mb-6">
          <span class="text-yellow-400 text-lg mt-0.5">⚠</span>
          <div>
            <p class="text-sm font-semibold text-yellow-300 mb-1">Módulo de pago provisional</p>
            <p class="text-xs text-yellow-200/70 leading-relaxed">
              La pasarela de pagos aún no está integrada. Al confirmar se genera un ticket sin cobro real.
              Cambiará cuando se integre Wompi / PayU.
            </p>
          </div>
        </div>

        <!-- Ticket generado -->
        <UiCard v-if="ticketCode" class="p-6 text-center mb-6">
          <div class="text-green-400 text-4xl mb-3">✓</div>
          <p class="text-white font-semibold mb-1">¡Inscripción confirmada!</p>
          <p class="text-xs text-cgr-subtle mb-4">Tu código de ticket como ponente:</p>
          <p class="text-2xl font-mono font-bold text-cgr-purple tracking-widest mb-6">
            {{ ticketCode }}
          </p>
          <p class="text-sm text-cgr-muted">Ya puedes registrar tu ponencia abajo.</p>
        </UiCard>

        <!-- Lista de eventos para inscribirse -->
        <div v-if="!ticketCode" class="space-y-4">
          <UiCard v-for="event in events" :key="event.id" class="p-5">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <p class="text-xs text-cgr-purple font-medium mb-1">{{ event.modality === 'hibrido' ? 'Híbrido' : event.modality }}</p>
                <h3 class="text-base font-semibold text-white mb-2">{{ event.name }}</h3>
                <p v-if="event.description" class="text-sm text-cgr-muted mb-3 line-clamp-2">
                  {{ event.description }}
                </p>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-cgr-subtle">
                  <span v-if="event.event_date">📅 {{ formatDate(event.event_date) }}</span>
                  <span v-if="event.start_time">🕐 {{ formatTime(event.start_time) }}</span>
                  <span v-if="event.location">📍 {{ event.location }}</span>
                </div>
              </div>
              <div class="flex flex-col items-end gap-2 shrink-0">
                <p class="text-lg font-bold text-white">
                  {{ event.is_free ? 'Gratis' : formatPrice(event.price, event.currency) }}
                </p>
                <UiButton size="sm" :loading="paying" @click="confirmInscription(event)">
                  Inscribirse (demo)
                </UiButton>
              </div>
            </div>
            <p v-if="payError" class="mt-3 text-xs text-red-400">{{ payError }}</p>
          </UiCard>

          <div v-if="events.length === 0" class="text-center py-8 text-cgr-muted">
            No hay eventos disponibles por el momento.
          </div>
        </div>
      </div>

      <!-- ── INSCRITO: mostrar ticket + pipeline de ponencias ── -->
      <div v-else>
        <!-- Cabecera con ticket -->
        <div class="flex items-start justify-between mb-8 gap-4">
          <div>
            <h1 class="text-2xl font-bold text-white">Mis ponencias</h1>
            <div class="flex items-center gap-2 mt-2">
              <span class="text-xs text-cgr-muted">Ticket ponente:</span>
              <span class="text-sm font-mono font-semibold text-cgr-purple">
                {{ registration?.ticket_code }}
              </span>
              <UiBadge variant="success">Inscrito</UiBadge>
            </div>
            <p v-if="registration?.congress_event" class="text-xs text-cgr-subtle mt-1">
              {{ registration.congress_event.name }}
            </p>
          </div>
          <UiButton
            v-if="submissions.length === 0"
            variant="primary"
            size="sm"
            @click="router.push({ name: 'ponente-new' })"
          >
            Nueva ponencia
          </UiButton>
        </div>

        <!-- Sin ponencias aún -->
        <div v-if="submissions.length === 0" class="text-center py-12">
          <p class="text-cgr-muted mb-2">Estás inscrito. Ahora puedes registrar tu ponencia.</p>
          <p class="text-xs text-cgr-subtle mb-6">Solo se permite una ponencia por ponente.</p>
          <UiButton variant="primary" @click="router.push({ name: 'ponente-new' })">
            Registrar mi ponencia
          </UiButton>
        </div>

        <!-- Lista de ponencias -->
        <div v-else class="space-y-4">
          <UiCard
            v-for="s in submissions"
            :key="s.id"
            class="p-5 cursor-pointer hover:border-cgr-purple/50 transition-colors"
            @click="router.push({ name: 'ponente-detail', params: { id: s.id } })"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <h2 class="font-semibold text-white mb-1">{{ s.title }}</h2>
                <p v-if="s.thematic_axis" class="text-xs text-cgr-muted mb-2">
                  {{ s.thematic_axis.name }}
                </p>
                <UiBadge :variant="statusVariants[s.status] ?? 'default'">
                  {{ statusLabels[s.status] ?? s.status }}
                </UiBadge>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <span class="text-cgr-subtle text-xs">#{{ s.id }}</span>
                <button
                  class="p-1.5 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors"
                  title="Eliminar ponencia"
                  @click.stop="confirmDeleteId = s.id"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </UiCard>
        </div>
      </div>

    </template>

    <!-- ── Modal confirmación eliminar ── -->
    <Teleport to="body">
      <div
        v-if="confirmDeleteId !== null"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="confirmDeleteId = null"
      >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="confirmDeleteId = null" />
        <div class="relative bg-cgr-card border border-cgr-border rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-500/15 shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </div>
            <div>
              <p class="font-semibold text-white">Eliminar ponencia</p>
              <p class="text-xs text-cgr-muted mt-0.5">Esta acción no se puede deshacer</p>
            </div>
          </div>
          <p class="text-sm text-cgr-muted mb-4">
            La ponencia será eliminada de tu lista. Podrás registrar una nueva ponencia después.
          </p>
          <p v-if="deleteError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2 mb-4">
            {{ deleteError }}
          </p>
          <div class="flex gap-3">
            <UiButton
              variant="danger"
              class="flex-1"
              :loading="deleting"
              @click="deleteSubmission(confirmDeleteId!)"
            >
              Sí, eliminar
            </UiButton>
            <UiButton
              variant="secondary"
              class="flex-1"
              :disabled="deleting"
              @click="confirmDeleteId = null; deleteError = ''"
            >
              Cancelar
            </UiButton>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
