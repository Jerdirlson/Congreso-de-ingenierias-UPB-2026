<script setup lang="ts">
import { ref, onMounted } from 'vue'

const STORAGE_KEY = 'cgr_looker_studio_url'

const embedUrl = ref('')
const inputUrl = ref('')
const editing  = ref(false)

onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved) embedUrl.value = saved
  else editing.value = true
})

function save() {
  const trimmed = inputUrl.value.trim()
  if (!trimmed) return
  localStorage.setItem(STORAGE_KEY, trimmed)
  embedUrl.value = trimmed
  editing.value  = false
}

function reset() {
  editing.value  = true
  inputUrl.value = embedUrl.value
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-white">Analytics del sitio</h1>
        <p class="text-cgr-muted text-sm mt-0.5">Reporte de visitas vía Google Analytics / Looker Studio</p>
      </div>
      <button
        v-if="embedUrl && !editing"
        @click="reset"
        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium text-cgr-muted hover:text-white hover:bg-cgr-card border border-cgr-border transition-colors"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Cambiar reporte
      </button>
    </div>

    <!-- Configuración (cuando no hay URL o se edita) -->
    <div
      v-if="editing"
      class="bg-cgr-card border border-cgr-border rounded-xl p-6 space-y-5"
    >
      <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-lg bg-cgr-purple/20 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-white font-semibold text-sm">Conectar Looker Studio</h2>
          <p class="text-cgr-muted text-sm mt-1">
            Crea un reporte en Looker Studio con tus datos de GA4 y pega el enlace de inserción aquí.
          </p>
        </div>
      </div>

      <!-- Pasos -->
      <ol class="space-y-2.5 text-sm text-cgr-muted">
        <li class="flex items-start gap-2.5">
          <span class="w-5 h-5 rounded-full bg-cgr-purple/30 text-cgr-purple text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
          <span>Ve a <span class="text-white font-medium">lookerstudio.google.com</span> e inicia sesión con tu cuenta de Google.</span>
        </li>
        <li class="flex items-start gap-2.5">
          <span class="w-5 h-5 rounded-full bg-cgr-purple/30 text-cgr-purple text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
          <span>Crea un reporte nuevo → conecta como fuente de datos <span class="text-white font-medium">Google Analytics</span> → selecciona la propiedad <span class="text-white font-medium">Congreso UPB 2026</span>.</span>
        </li>
        <li class="flex items-start gap-2.5">
          <span class="w-5 h-5 rounded-full bg-cgr-purple/30 text-cgr-purple text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
          <span>Una vez diseñado, haz clic en <span class="text-white font-medium">Archivo → Configuración de inserción del informe</span>.</span>
        </li>
        <li class="flex items-start gap-2.5">
          <span class="w-5 h-5 rounded-full bg-cgr-purple/30 text-cgr-purple text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">4</span>
          <span>Activa "Habilitar inserción" y copia la URL del atributo <span class="text-white font-medium">src</span> del iframe (empieza por <span class="text-white font-medium">https://lookerstudio.google.com/embed/...</span>).</span>
        </li>
        <li class="flex items-start gap-2.5">
          <span class="w-5 h-5 rounded-full bg-cgr-purple/30 text-cgr-purple text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">5</span>
          <span>Pégala aquí abajo y guarda.</span>
        </li>
      </ol>

      <!-- Input -->
      <div class="flex gap-2">
        <input
          v-model="inputUrl"
          type="url"
          placeholder="https://lookerstudio.google.com/embed/reporting/..."
          class="flex-1 bg-cgr-section border border-cgr-border rounded-lg px-3 py-2 text-sm text-white placeholder:text-cgr-muted focus:outline-none focus:border-cgr-purple transition-colors"
          @keyup.enter="save"
        />
        <button
          @click="save"
          :disabled="!inputUrl.trim()"
          class="px-4 py-2 rounded-lg text-sm font-semibold bg-cgr-purple text-white hover:bg-cgr-purple/90 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        >
          Guardar
        </button>
      </div>
    </div>

    <!-- Iframe del reporte -->
    <div
      v-if="embedUrl && !editing"
      class="bg-cgr-card border border-cgr-border rounded-xl overflow-hidden"
    >
      <iframe
        :src="embedUrl"
        class="w-full"
        style="height: 80vh; min-height: 500px;"
        frameborder="0"
        allowfullscreen
        sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox"
      />
    </div>
  </div>
</template>
