<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiButton from '../../components/ui/UiButton.vue'

const router = useRouter()
const route = useRoute()
const api = useFetchApi()
const isSubmitting = computed(() => api.loading.value)

const errorMessage = ref('')
const ticketCode = ref<string | null>(null)

const eventId = computed(() => route.query.eventId ? Number(route.query.eventId) : null)
const eventName = computed(() => (route.query.eventName as string) ?? 'Evento del congreso')

interface CongressEvent {
  id: number
  name: string
  description: string | null
  location: string | null
  modality: string
  event_date: string
  start_time: string | null
  end_time: string | null
  speaker: string | null
  category: string | null
  is_free: boolean
  price: number
  currency: string
}

const event = ref<CongressEvent | null>(null)

async function loadEvent() {
  if (!eventId.value) return
  const events = await useFetchApi().get<CongressEvent[]>('/events')
  if (events) event.value = events.find(e => e.id === eventId.value) ?? null
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

async function confirmDemo() {
  errorMessage.value = ''
  const data = await api.post<{ demo: boolean; ticket_code: string }>('/payments', {
    registration_type: 'participant',
    congress_event_id: eventId.value,
  })
  if (data?.ticket_code) {
    ticketCode.value = data.ticket_code
  } else {
    errorMessage.value = api.error.value?.message ?? 'Error al procesar la inscripción'
  }
}

onMounted(() => {
  loadEvent()
})
</script>

<template>
  <div class="max-w-xl">
    <h1 class="text-2xl font-bold text-white mb-6">Confirmar inscripción</h1>

    <!-- Sin evento seleccionado -->
    <UiCard v-if="!eventId" class="p-8 text-center">
      <div class="text-cgr-muted text-4xl mb-4">💳</div>
      <p class="text-white font-semibold mb-2">No tienes ningún pago pendiente</p>
      <p class="text-sm text-cgr-muted mb-6">
        Para inscribirte, primero selecciona un evento desde el listado.
      </p>
      <UiButton @click="router.push({ name: 'participante-home' })">
        Ver eventos disponibles
      </UiButton>
    </UiCard>

    <template v-else>
      <!-- Banner provisional -->
      <div class="flex items-start gap-3 bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 mb-6">
        <span class="text-yellow-400 text-lg mt-0.5">⚠</span>
        <div>
          <p class="text-sm font-semibold text-yellow-300 mb-1">Módulo de pago provisional</p>
          <p class="text-xs text-yellow-200/70 leading-relaxed">
            La pasarela de pagos aún no está integrada. En esta versión de desarrollo,
            al confirmar se genera un ticket directamente sin cobro real.
            Este comportamiento cambiará cuando se integre la pasarela (Wompi / PayU).
          </p>
        </div>
      </div>

      <!-- Ticket generado -->
      <UiCard v-if="ticketCode" class="p-6 text-center">
        <div class="text-green-400 text-4xl mb-3">✓</div>
        <p class="text-white font-semibold mb-1">¡Inscripción confirmada!</p>
        <p class="text-sm text-cgr-muted mb-1">{{ eventName }}</p>
        <p class="text-xs text-cgr-subtle mb-4">Tu código de ticket:</p>
        <p class="text-2xl font-mono font-bold text-cgr-purple tracking-widest mb-6">
          {{ ticketCode }}
        </p>
        <UiButton variant="secondary" @click="router.push({ name: 'participante-home' })">
          Ver mis inscripciones
        </UiButton>
      </UiCard>

      <!-- Detalle del evento + confirmar -->
      <UiCard v-else class="p-6">
        <!-- Cargando -->
        <div v-if="!event" class="text-center py-6">
          <div class="w-6 h-6 border-2 border-cgr-purple border-t-transparent rounded-full animate-spin mx-auto"></div>
        </div>

        <template v-else>
          <!-- Info del evento -->
          <div class="mb-6">
            <p class="text-xs text-cgr-muted mb-1 uppercase tracking-wide font-medium">Evento seleccionado</p>
            <p class="text-white font-semibold mb-1">{{ event.name }}</p>
            <div class="flex flex-col gap-1 text-xs text-cgr-subtle">
              <span v-if="event.event_date">📅 {{ formatDate(event.event_date) }}</span>
              <span v-if="event.start_time">🕐 {{ formatTime(event.start_time) }}{{ event.end_time ? ' – ' + formatTime(event.end_time) : '' }}</span>
              <span v-if="event.location">📍 {{ event.location }}</span>
              <span v-if="event.speaker">🎤 {{ event.speaker }}</span>
            </div>
          </div>

          <!-- Resumen de pago -->
          <div class="bg-cgr-section rounded-lg px-4 py-3 mb-6 space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-cgr-muted">Tipo</span>
              <span class="text-white">Participante asistente</span>
            </div>
            <div class="flex justify-between border-t border-cgr-border pt-2">
              <span class="text-cgr-muted">Valor</span>
              <span class="text-white font-semibold">
                {{ event.is_free ? 'Gratis' : formatPrice(event.price, event.currency) }}
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-cgr-muted">Estado pago</span>
              <span class="text-yellow-400 font-medium">Demo — sin cobro real</span>
            </div>
          </div>

          <p v-if="errorMessage" class="text-sm text-red-400 mb-4 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
            {{ errorMessage }}
          </p>

          <div class="flex gap-3">
            <UiButton :loading="isSubmitting" @click="confirmDemo">
              Confirmar inscripción (demo)
            </UiButton>
            <UiButton variant="secondary" @click="router.push({ name: 'participante-home' })">
              Volver
            </UiButton>
          </div>
        </template>
      </UiCard>
    </template>
  </div>
</template>
