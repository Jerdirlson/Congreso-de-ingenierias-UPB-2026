<script setup lang="ts">
import { ref, computed } from 'vue'

type Audience = 'participante' | 'ponente'

interface NrcOption {
  nrc: string
  title: string
  description: string
  price: string
  url: string
  audiences: Audience[]
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
    audiences: ['ponente'],
  },
  {
    nrc: '51885',
    title: 'Profesionales — Virtual',
    description: 'Profesionales con ponencia virtual.',
    price: '$670.000 COP',
    url: 'https://micrositios.upb.edu.co/fcontinua/pages/index.php?nrc=51885&period=202650',
    audiences: ['ponente'],
  },
  {
    nrc: '51888',
    title: 'Estudiantes UPB — Asistencia virtual',
    description: 'Asistencia virtual al congreso.',
    price: '$350.000 COP',
    url: 'https://micrositios.upb.edu.co/fcontinua/pages/index.php?nrc=51888&period=202650',
    audiences: ['participante'],
  },
  {
    nrc: '51890',
    title: 'Egresados UPB',
    description: 'Evento del viernes.',
    price: '$50.000 COP',
    url: 'https://micrositios.upb.edu.co/fcontinua/pages/index.php?nrc=51890&period=202650',
    audiences: ['participante'],
  },
]

const props = defineProps<{ audience: Audience }>()

const selectedNrc = ref<string | null>(null)

const options = computed(() => ALL_OPTIONS.filter(o => o.audiences.includes(props.audience)))
const selected = computed(() => options.value.find(o => o.nrc === selectedNrc.value) ?? null)
</script>

<template>
  <div>
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

    <a
      v-if="selected"
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
  </div>
</template>
