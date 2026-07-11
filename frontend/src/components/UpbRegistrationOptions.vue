<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuthStore } from '../stores/auth'

type Audience = 'participante' | 'ponente'

interface NrcOption {
  nrc: string
  title: string
  description: string
  price: string
  url: string
  audiences: Audience[]
  unavailable?: boolean
}

const ALL_OPTIONS: NrcOption[] = [
  {
    nrc: '51882',
    title: 'Estudiantes UPB',
    description: 'Presencial, con o sin ponencia.',
    price: '$300.000 COP',
    url: 'https://micrositios.upb.edu.co/fcontinua/pages/index.php?nrc=51882&period=202650',
    audiences: ['ponente', 'participante'],
  },
  {
    nrc: '51883',
    title: 'Estudiantes externos',
    description: 'Estudiantes de otras instituciones, con o sin ponencia.',
    price: '$370.000 COP',
    url: 'https://micrositios.upb.edu.co/fcontinua/pages/index.php?nrc=51883&period=202650',
    audiences: ['ponente', 'participante'],
  },
  {
    nrc: '51884',
    title: 'Profesionales — Presencial',
    description: 'Profesionales con ponencia presencial.',
    price: '$670.000 COP',
    url: 'https://micrositios.upb.edu.co/fcontinua/pages/index.php?nrc=51884&period=202650',
    audiences: ['ponente', 'participante'],
  },
  {
    nrc: '51885',
    title: 'Profesionales — Virtual',
    description: 'Profesionales con ponencia virtual.',
    price: '$670.000 COP',
    url: 'https://micrositios.upb.edu.co/fcontinua/pages/index.php?nrc=51885&period=202650',
    audiences: ['ponente', 'participante'],
  },
  {
    nrc: '51888',
    title: 'Estudiantes UPB — Asistencia virtual',
    description: 'Asistencia virtual al congreso.',
    price: '$350.000 COP',
    url: 'https://micrositios.upb.edu.co/fcontinua/pages/index.php?nrc=51888&period=202650',
    audiences: ['participante'],
    unavailable: true,
  },
  {
    nrc: '51890',
    title: 'Egresados UPB',
    description: 'Evento Viernes - Solo tiene derecho a la conferencia de Egresado y Coctel de Cierre.',
    price: '$50.000 COP',
    url: 'https://micrositios.upb.edu.co/fcontinua/pages/index.php?nrc=51890&period=202650',
    audiences: ['participante'],
  },
]

const props = defineProps<{ audience: Audience }>()
const emit = defineEmits<{ confirmed: [] }>()

const auth = useAuthStore()
const selectedNrc = ref<string | null>(null)
const confirmLoading = ref(false)
const confirmError = ref('')

const options = computed(() => ALL_OPTIONS.filter(o => o.audiences.includes(props.audience)))
const selected = computed(() => options.value.find(o => o.nrc === selectedNrc.value) ?? null)
const alreadyConfirmed = computed(() => !!auth.user?.external_registration_at)
const alreadyPaid = computed(() => !!auth.user?.external_registration_paid_at)

async function confirmExternal() {
  confirmError.value = ''
  confirmLoading.value = true
  const ok = await auth.confirmExternalRegistration()
  confirmLoading.value = false
  if (ok) emit('confirmed')
  else confirmError.value = 'No se pudo registrar tu confirmación. Intenta de nuevo en unos segundos.'
}
</script>

<template>
  <!-- ── Estado: pago verificado ── -->
  <div v-if="alreadyPaid">
    <div class="flex items-start gap-3 bg-green-500/10 border border-green-500/30 rounded-xl px-5 py-5">
      <svg class="w-6 h-6 text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <p class="text-base font-semibold text-green-300 mb-1">Estás totalmente inscrito al Congreso</p>
        <p class="text-sm text-green-200/80 leading-relaxed">
          Confirmamos la recepción de tu pago. ¡Te esperamos en el congreso!
        </p>
      </div>
    </div>
  </div>

  <!-- ── Selector (visible mientras el pago no esté verificado) ── -->
  <div v-else>
    <div v-if="alreadyConfirmed" class="flex items-start gap-3 bg-cgr-purple/10 border border-cgr-purple/30 rounded-xl px-5 py-5 mb-5">
      <svg class="w-6 h-6 text-cgr-purple shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <p class="text-base font-semibold text-white mb-1">Registramos tu confirmación de inscripción</p>
        <p class="text-sm text-cgr-muted leading-relaxed">
          El pago se realiza <strong class="text-white">directamente en el portal de la UPB</strong>,
          no por correo. Si ya completaste el formulario y el pago allá, no debes hacer nada más:
          cuando verifiquemos tu pago verás aquí la confirmación. Si aún no lo has hecho,
          usa los enlaces de abajo para completarlo.
        </p>
      </div>
    </div>

    <div class="flex items-start gap-3 bg-cgr-section border border-cgr-border rounded-lg px-4 py-3 mb-5 text-sm">
      <svg class="w-4 h-4 text-cgr-purple shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="text-cgr-muted leading-relaxed">
        Selecciona la opción que corresponde a tu perfil. Al continuar se abrirá el
        portal oficial de la UPB para completar el formulario y el pago de tu inscripción.
        <strong class="text-white">Tarifas vigentes hasta el 10 de julio de 2026.</strong>
      </p>
    </div>

    <p class="text-xs text-cgr-muted mb-2">Tipo de inscripción:</p>
    <div class="space-y-2 mb-5">
      <label
        v-for="opt in options"
        :key="opt.nrc"
        :class="[
          'flex items-start gap-3 p-4 rounded-lg border cursor-pointer transition-colors',
          selectedNrc === opt.nrc
            ? 'border-cgr-purple bg-cgr-purple/10'
            : 'border-cgr-border bg-cgr-section hover:border-cgr-purple/50'
        ]"
      >
        <input type="radio" :value="opt.nrc" v-model="selectedNrc" class="mt-1 accent-cgr-purple" />
        <span class="flex-1 min-w-0">
          <span class="flex items-start justify-between gap-3 flex-wrap">
            <span class="text-sm font-semibold text-white">{{ opt.title }}</span>
            <span class="text-sm font-bold text-cgr-purple whitespace-nowrap">{{ opt.price }}</span>
          </span>
          <span class="block text-xs text-cgr-subtle mt-0.5">{{ opt.description }}</span>
          <span class="block text-[10px] text-cgr-subtle mt-1 font-mono uppercase tracking-wider">NRC {{ opt.nrc }}</span>
        </span>
      </label>
    </div>

    <div v-if="selected?.unavailable" class="flex items-start gap-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg px-4 py-3 text-sm">
      <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
      <p class="text-yellow-200/90 leading-relaxed">
        Esta modalidad se habilitará únicamente cuando se completen los cupos de inscripción externa. Pronto estará disponible.
      </p>
    </div>
    <a
      v-else-if="selected"
      :href="selected.url"
      target="_blank"
      rel="noopener noreferrer"
      class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg text-sm font-semibold bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white hover:opacity-90 transition-opacity"
    >
      Continuar al portal de inscripción UPB
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
      </svg>
    </a>
    <button
      v-else
      type="button"
      disabled
      class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg text-sm font-semibold bg-cgr-section text-cgr-subtle border border-cgr-border cursor-not-allowed"
    >
      Selecciona una opción para continuar
    </button>

    <!-- ── Confirmación de inscripción externa ── -->
    <div v-if="!alreadyConfirmed" class="mt-6 pt-5 border-t border-cgr-border">
      <p class="text-sm text-cgr-muted mb-3">
        ¿Ya terminaste la inscripción en la plataforma institucional?
      </p>
      <button
        type="button"
        :disabled="confirmLoading"
        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-lg text-sm font-semibold border border-cgr-purple text-cgr-purple hover:bg-cgr-purple/10 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        @click="confirmExternal"
      >
        <svg v-if="confirmLoading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 018-8v2a6 6 0 00-6 6H4z"/>
        </svg>
        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        {{ confirmLoading ? 'Procesando…' : 'Sí, ya terminé mi inscripción' }}
      </button>
      <p v-if="confirmError" class="mt-2 text-xs text-red-400">{{ confirmError }}</p>
    </div>
  </div>
</template>
