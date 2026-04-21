<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiButton from '../../components/ui/UiButton.vue'

const ROLES = [
  { value: 'ponente',        label: 'Ponentes' },
  { value: 'participante',   label: 'Participantes' },
  { value: 'revisor',        label: 'Revisores' },
  { value: 'admin',          label: 'Administradores' },
  { value: 'administrativo', label: 'Administrativos' },
]

const TEMPLATES = [
  { label: 'Invitación a enviar ponencia',
    subject: 'Invitación — Congreso de Ingenierías 2026',
    body: `Te invitamos a participar como ponente en el Congreso de Ingenierías 2026, organizado por la Universidad Pontificia Bolivariana, Seccional Bucaramanga.

Este espacio reúne a investigadores, docentes y profesionales de la ingeniería para compartir avances, experiencias y propuestas en torno a los retos tecnológicos actuales.

Para registrar tu ponencia ingresa a: https://congreso2026.bucaramanga.upb.edu.co

Fecha límite de envío de resúmenes: [FECHA LÍMITE]
Fecha del congreso: [FECHA CONGRESO]

¡Esperamos contar con tu participación!` },
  { label: 'Recordatorio de envío de ponencia',
    subject: 'Recordatorio — Fecha límite para envío de ponencias',
    body: `Te recordamos que la fecha límite para enviar tu ponencia al Congreso de Ingenierías 2026 se aproxima.

Si aún no has registrado tu resumen, te invitamos a hacerlo a la brevedad posible ingresando a nuestra plataforma.

Fecha límite: [FECHA LÍMITE]

Para cualquier duda escríbenos a: congreso.ingenierias@upb.edu.co` },
  { label: 'Inicio del congreso',
    subject: '¡El Congreso de Ingenierías 2026 comienza pronto!',
    body: `Nos complace informarte que el Congreso de Ingenierías 2026 dará inicio el próximo [FECHA CONGRESO].

Adjunto encontrarás el programa oficial con los horarios y actividades del evento.

Recuerda llevar tu código de inscripción para el registro en el evento.

Lugar: [LUGAR DEL EVENTO]
Hora de inicio: [HORA]

¡Te esperamos!` },
]

type RecipientType = 'all' | 'by_role' | 'external' | 'combined'

const subject = ref('')
const body = ref('')
const recipientType = ref<RecipientType>('all')
const selectedRoles = ref<string[]>([])
const externalEmails = ref('')

const previewResult = ref<{ internal_count: number; external_count: number; total: number } | null>(null)
const previewing = ref(false)
const sending = ref(false)
const sentResult = ref<{ sent: number; failed: number; errors: string[] } | null>(null)
const errorMsg = ref('')
const confirmSend = ref(false)

const showRoles = computed(() => recipientType.value === 'by_role' || recipientType.value === 'combined')
const showExternal = computed(() => recipientType.value === 'external' || recipientType.value === 'combined')

watch(recipientType, () => { previewResult.value = null; sentResult.value = null })

function applyTemplate(t: typeof TEMPLATES[0]) {
  subject.value = t.subject
  body.value = t.body
}

function toggleRole(role: string) {
  const idx = selectedRoles.value.indexOf(role)
  if (idx === -1) selectedRoles.value.push(role)
  else selectedRoles.value.splice(idx, 1)
}

function buildPayload() {
  return {
    subject: subject.value,
    body: body.value,
    recipient_type: recipientType.value,
    roles: selectedRoles.value,
    external_emails: externalEmails.value,
  }
}

async function doPreview() {
  previewing.value = true
  errorMsg.value = ''
  previewResult.value = null
  const api = useFetchApi()
  const data = await api.post<typeof previewResult.value>('/admin/mail/preview', buildPayload())
  previewing.value = false
  if (data) previewResult.value = data
  else errorMsg.value = api.error.value?.message ?? 'No se pudo obtener la previsualización.'
}

async function doSend() {
  confirmSend.value = false
  sending.value = true
  errorMsg.value = ''
  sentResult.value = null
  const api = useFetchApi()
  const data = await api.post<{ sent: number; failed: number; errors: string[] }>('/admin/mail/send', buildPayload())
  sending.value = false
  if (data) sentResult.value = data
  else errorMsg.value = api.error.value?.message ?? 'Error al enviar los correos.'
}

const canSend = computed(() => subject.value.trim() && body.value.trim())
</script>

<template>
  <div class="max-w-3xl">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-white">Correo masivo</h1>
      <p class="text-sm text-cgr-muted mt-1">Envía invitaciones y recordatorios a usuarios registrados o correos externos.</p>
    </div>

    <!-- Plantillas rápidas -->
    <UiCard class="p-5 mb-5">
      <p class="text-xs font-semibold text-cgr-muted uppercase tracking-wide mb-3">Plantillas rápidas</p>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="t in TEMPLATES"
          :key="t.label"
          class="text-xs px-3 py-1.5 rounded-lg border border-cgr-border text-cgr-muted hover:border-cgr-purple hover:text-cgr-purple transition-colors"
          @click="applyTemplate(t)"
        >
          {{ t.label }}
        </button>
      </div>
    </UiCard>

    <!-- Asunto y cuerpo -->
    <UiCard class="p-6 mb-5">
      <h2 class="font-semibold text-white mb-4">Mensaje</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-xs text-cgr-muted mb-1">Asunto <span class="text-red-400">*</span></label>
          <input
            v-model="subject"
            type="text"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple"
            placeholder="Ej: Invitación al Congreso de Ingenierías 2026"
          />
        </div>
        <div>
          <label class="block text-xs text-cgr-muted mb-1">Cuerpo del mensaje <span class="text-red-400">*</span></label>
          <textarea
            v-model="body"
            rows="10"
            class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple resize-y font-mono"
            placeholder="Escribe aquí el contenido del correo…"
          />
          <p class="text-xs text-cgr-subtle mt-1">Texto plano — los saltos de línea se respetan en el correo.</p>
        </div>
      </div>
    </UiCard>

    <!-- Destinatarios -->
    <UiCard class="p-6 mb-5">
      <h2 class="font-semibold text-white mb-4">Destinatarios</h2>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-5">
        <button
          v-for="opt in [
            { value: 'all',      label: 'Todos los usuarios' },
            { value: 'by_role',  label: 'Por rol' },
            { value: 'external', label: 'Externos' },
            { value: 'combined', label: 'Rol + externos' },
          ]"
          :key="opt.value"
          :class="[
            'px-3 py-2 rounded-lg border text-sm font-medium transition-colors text-center',
            recipientType === opt.value
              ? 'border-cgr-purple bg-cgr-purple/10 text-cgr-purple'
              : 'border-cgr-border text-cgr-muted hover:border-cgr-purple/50'
          ]"
          @click="recipientType = opt.value as RecipientType"
        >
          {{ opt.label }}
        </button>
      </div>

      <!-- Selector de roles -->
      <div v-if="showRoles" class="mb-4">
        <p class="text-xs text-cgr-muted mb-2">Selecciona los roles:</p>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="r in ROLES"
            :key="r.value"
            :class="[
              'px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors',
              selectedRoles.includes(r.value)
                ? 'border-cgr-purple bg-cgr-purple/10 text-cgr-purple'
                : 'border-cgr-border text-cgr-muted hover:border-cgr-purple/50'
            ]"
            @click="toggleRole(r.value)"
          >
            {{ r.label }}
          </button>
        </div>
      </div>

      <!-- Correos externos -->
      <div v-if="showExternal">
        <label class="block text-xs text-cgr-muted mb-1">Correos externos</label>
        <textarea
          v-model="externalEmails"
          rows="4"
          class="w-full bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white placeholder-cgr-subtle focus:outline-none focus:border-cgr-purple resize-none font-mono"
          placeholder="uno@ejemplo.com, dos@ejemplo.com&#10;tres@ejemplo.com"
        />
        <p class="text-xs text-cgr-subtle mt-1">Separados por comas, punto y coma o saltos de línea.</p>
      </div>

      <!-- Preview de destinatarios -->
      <div class="mt-4 flex items-center gap-3 flex-wrap">
        <UiButton variant="secondary" size="sm" :loading="previewing" @click="doPreview">
          Verificar destinatarios
        </UiButton>
        <div v-if="previewResult" class="text-sm text-cgr-muted">
          <span class="text-white font-semibold">{{ previewResult.total }}</span> destinatarios
          <span v-if="previewResult.internal_count">({{ previewResult.internal_count }} internos</span>
          <span v-if="previewResult.external_count">, {{ previewResult.external_count }} externos</span>
          <span v-if="previewResult.internal_count || previewResult.external_count">)</span>
        </div>
      </div>
    </UiCard>

    <!-- Error / resultado -->
    <p v-if="errorMsg" class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-2">
      {{ errorMsg }}
    </p>

    <div v-if="sentResult" class="mb-5 rounded-xl border px-5 py-4"
      :class="sentResult.failed === 0 ? 'bg-green-500/10 border-green-500/20' : 'bg-amber-500/10 border-amber-500/20'">
      <p class="font-semibold" :class="sentResult.failed === 0 ? 'text-green-300' : 'text-amber-300'">
        {{ sentResult.sent }} correo(s) enviado(s) correctamente
        <span v-if="sentResult.failed"> · {{ sentResult.failed }} fallido(s)</span>
      </p>
      <ul v-if="sentResult.errors.length" class="mt-2 text-xs text-red-300 space-y-0.5">
        <li v-for="e in sentResult.errors" :key="e">✕ {{ e }}</li>
      </ul>
    </div>

    <!-- Botón enviar -->
    <div class="flex gap-3">
      <UiButton
        variant="primary"
        :disabled="!canSend || sending"
        @click="confirmSend = true"
      >
        Enviar correos
      </UiButton>
    </div>

    <!-- Modal confirmar envío -->
    <Teleport to="body">
      <div
        v-if="confirmSend"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="confirmSend = false"
      >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="confirmSend = false" />
        <div class="relative bg-cgr-card border border-cgr-border rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <p class="font-semibold text-white mb-2">Confirmar envío</p>
          <p class="text-sm text-cgr-muted mb-1">
            Asunto: <strong class="text-white">{{ subject }}</strong>
          </p>
          <p v-if="previewResult" class="text-sm text-cgr-muted mb-5">
            Se enviará a <strong class="text-white">{{ previewResult.total }}</strong> destinatarios.
          </p>
          <p v-else class="text-sm text-amber-400 mb-5">Verifica los destinatarios antes de enviar.</p>
          <div class="flex gap-3">
            <UiButton variant="primary" class="flex-1" :loading="sending" @click="doSend">
              Sí, enviar
            </UiButton>
            <UiButton variant="secondary" class="flex-1" :disabled="sending" @click="confirmSend = false">
              Cancelar
            </UiButton>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
