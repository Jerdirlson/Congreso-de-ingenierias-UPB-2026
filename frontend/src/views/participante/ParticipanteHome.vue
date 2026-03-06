<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiBadge from '../../components/ui/UiBadge.vue'
import UiButton from '../../components/ui/UiButton.vue'

const router = useRouter()
const api = useFetchApi()

interface CongressEvent {
  id: number
  name: string
  description: string | null
  location: string | null
  modality: 'presencial' | 'virtual' | 'hibrido'
  event_date: string
  start_time: string | null
  end_time: string | null
  speaker: string | null
  category: string | null
  capacity: number | null
  is_free: boolean
  price: number
  currency: string
  is_full: boolean
  registered_count: number
}

interface Registration {
  id: number
  ticket_code: string | null
  registration_type: string
  confirmed_at: string | null
  congress_event?: { id: number; name: string; event_date: string } | null
}

const events = ref<CongressEvent[]>([])
const registrations = ref<Registration[]>([])
const loadingEvents = ref(false)
const activeTab = ref<'events' | 'my-registrations'>('events')

// IDs de eventos ya inscritos
const registeredEventIds = computed(() =>
  registrations.value
    .filter(r => r.congress_event)
    .map(r => r.congress_event!.id)
)

function isRegistered(eventId: number) {
  return registeredEventIds.value.includes(eventId)
}

async function loadData() {
  loadingEvents.value = true
  const [eventsData, regsData] = await Promise.all([
    useFetchApi().get<CongressEvent[]>('/events'),
    api.get<Registration[]>('/registrations'),
  ])
  if (eventsData) events.value = eventsData
  if (regsData) registrations.value = Array.isArray(regsData) ? regsData : []
  loadingEvents.value = false
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

const modalityLabel: Record<string, string> = {
  presencial: 'Presencial', virtual: 'Virtual', hibrido: 'Híbrido',
}

const modalityColor: Record<string, string> = {
  presencial: 'text-blue-400 bg-blue-500/10 border-blue-500/20',
  virtual: 'text-purple-400 bg-purple-500/10 border-purple-500/20',
  hibrido: 'text-teal-400 bg-teal-500/10 border-teal-500/20',
}

function goToRegister(event: CongressEvent) {
  router.push({ name: 'participante-pago', query: { eventId: event.id, eventName: event.name } })
}

// Agrupar eventos por fecha
const eventsByDate = computed(() => {
  const map = new Map<string, CongressEvent[]>()
  for (const e of events.value) {
    const list = map.get(e.event_date) ?? []
    list.push(e)
    map.set(e.event_date, list)
  }
  return map
})

onMounted(loadData)
</script>

<template>
  <div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-white">Congreso Ingenierías 2026</h1>
      <div class="flex gap-2 bg-cgr-section border border-cgr-border rounded-lg p-1">
        <button
          :class="['px-3 py-1.5 rounded text-sm font-medium transition-colors', activeTab === 'events' ? 'bg-cgr-purple text-white' : 'text-cgr-muted hover:text-white']"
          @click="activeTab = 'events'"
        >
          Eventos disponibles
        </button>
        <button
          :class="['px-3 py-1.5 rounded text-sm font-medium transition-colors', activeTab === 'my-registrations' ? 'bg-cgr-purple text-white' : 'text-cgr-muted hover:text-white']"
          @click="activeTab = 'my-registrations'"
        >
          Mis inscripciones
          <span v-if="registrations.length" class="ml-1 bg-cgr-purple/30 text-cgr-purple text-xs px-1.5 py-0.5 rounded-full">
            {{ registrations.length }}
          </span>
        </button>
      </div>
    </div>

    <!-- TAB: Eventos disponibles -->
    <div v-if="activeTab === 'events'">
      <div v-if="loadingEvents" class="text-center py-12 text-cgr-muted">Cargando eventos…</div>

      <div v-else-if="events.length === 0" class="text-center py-12 text-cgr-muted">
        No hay eventos disponibles por el momento.
      </div>

      <div v-else>
        <div v-for="[date, dayEvents] in eventsByDate" :key="date" class="mb-8">
          <h2 class="text-sm font-semibold text-cgr-muted uppercase tracking-wider mb-3 flex items-center gap-2">
            <span class="h-px flex-1 bg-cgr-border"></span>
            {{ formatDate(date) }}
            <span class="h-px flex-1 bg-cgr-border"></span>
          </h2>

          <div class="space-y-3">
            <UiCard v-for="event in dayEvents" :key="event.id" class="p-5">
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                  <!-- Cabecera -->
                  <div class="flex items-center gap-2 flex-wrap mb-2">
                    <span v-if="event.category" class="text-xs font-medium text-cgr-purple bg-cgr-purple/10 border border-cgr-purple/20 px-2 py-0.5 rounded-full">
                      {{ event.category }}
                    </span>
                    <span :class="['text-xs font-medium border px-2 py-0.5 rounded-full', modalityColor[event.modality]]">
                      {{ modalityLabel[event.modality] }}
                    </span>
                    <span v-if="event.is_free" class="text-xs font-medium text-green-400 bg-green-500/10 border border-green-500/20 px-2 py-0.5 rounded-full">
                      Gratuito
                    </span>
                  </div>

                  <h3 class="text-base font-semibold text-white mb-1 leading-tight">{{ event.name }}</h3>

                  <p v-if="event.description" class="text-sm text-cgr-muted mb-3 line-clamp-2">
                    {{ event.description }}
                  </p>

                  <!-- Meta info -->
                  <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-xs text-cgr-subtle">
                    <span v-if="event.start_time" class="flex items-center gap-1">
                      🕐 {{ formatTime(event.start_time) }}{{ event.end_time ? ' – ' + formatTime(event.end_time) : '' }}
                    </span>
                    <span v-if="event.location" class="flex items-center gap-1">
                      📍 {{ event.location }}
                    </span>
                    <span v-if="event.speaker" class="flex items-center gap-1">
                      🎤 {{ event.speaker }}
                    </span>
                    <span v-if="event.capacity" class="flex items-center gap-1">
                      👥 {{ event.registered_count }}/{{ event.capacity }} inscritos
                    </span>
                  </div>
                </div>

                <!-- Acción -->
                <div class="flex flex-col items-end gap-2 shrink-0">
                  <p class="text-base font-bold text-white">
                    {{ event.is_free ? 'Gratis' : formatPrice(event.price, event.currency) }}
                  </p>

                  <UiBadge v-if="isRegistered(event.id)" variant="success">Inscrito ✓</UiBadge>
                  <UiBadge v-else-if="event.is_full" variant="warning">Cupos agotados</UiBadge>
                  <UiButton
                    v-else
                    size="sm"
                    @click="goToRegister(event)"
                  >
                    Inscribirse
                  </UiButton>
                </div>
              </div>
            </UiCard>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB: Mis inscripciones -->
    <div v-else-if="activeTab === 'my-registrations'">
      <div v-if="registrations.length === 0" class="text-center py-12">
        <p class="text-cgr-muted mb-4">Aún no tienes inscripciones.</p>
        <UiButton @click="activeTab = 'events'">Ver eventos</UiButton>
      </div>

      <div v-else class="space-y-4">
        <UiCard v-for="r in registrations" :key="r.id" class="p-5">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p v-if="r.congress_event" class="text-sm font-semibold text-white mb-1">
                {{ r.congress_event.name }}
              </p>
              <p class="text-xs text-cgr-muted mb-2">
                {{ r.registration_type === 'speaker' ? 'Ponente' : 'Participante' }}
                <span v-if="r.congress_event"> — {{ formatDate(r.congress_event.event_date) }}</span>
              </p>
              <p v-if="r.ticket_code" class="text-lg font-mono font-semibold text-cgr-purple">
                {{ r.ticket_code }}
              </p>
              <p v-else class="text-sm text-cgr-subtle">Pago pendiente</p>
              <p v-if="r.confirmed_at" class="text-xs text-cgr-subtle mt-1">
                Confirmado el {{ new Date(r.confirmed_at).toLocaleDateString('es-CO') }}
              </p>
            </div>
            <UiBadge v-if="r.ticket_code" variant="success">Confirmado</UiBadge>
            <UiBadge v-else variant="warning">Pendiente</UiBadge>
          </div>
        </UiCard>
      </div>
    </div>
  </div>
</template>
