<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useFetchApi } from './composables/useFetchApi'

interface HealthResponse {
  status: string
  service: string
  version: string
  timestamp: string
  php: string
  laravel: string
  checks: Record<string, { status: string; message?: string }>
}

const { get, loading, error } = useFetchApi()

const health = ref<HealthResponse | null>(null)
const apiStatus = ref<'loading' | 'ok' | 'error' | 'degraded'>('loading')

onMounted(async () => {
  const data = await get<HealthResponse>('/health')
  if (data) {
    health.value = data
    apiStatus.value = data.status as typeof apiStatus.value
  } else {
    apiStatus.value = 'error'
  }
})

const statusLabel: Record<string, string> = {
  loading:  'Verificando...',
  ok:       'Operativo',
  degraded: 'Degradado',
  error:    'Sin conexión',
}

const statusClass: Record<string, string> = {
  loading:  'bg-blue-900/50 text-blue-300',
  ok:       'bg-green-900/50 text-green-300',
  degraded: 'bg-yellow-900/50 text-yellow-300',
  error:    'bg-red-900/50 text-red-400',
}
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-slate-200 font-sans">
    <main class="max-w-4xl mx-auto px-6 py-10">

      <!-- Header -->
      <header class="mb-10 flex items-center gap-4">
        <span class="text-5xl">🎓</span>
        <div>
          <h1 class="text-2xl font-bold text-white tracking-tight">
            Sistema de Gestión Documental
          </h1>
          <p class="text-slate-400 text-sm mt-1">Congreso de Ingenierías 2026</p>
        </div>
      </header>

      <!-- Status card -->
      <section class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-8">
        <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">
          Estado del sistema
        </h2>

        <!-- Frontend -->
        <div class="flex items-center justify-between py-3 border-b border-slate-700/60">
          <span class="text-sm text-slate-300">Frontend (Vue 3 + TypeScript)</span>
          <span class="px-3 py-0.5 rounded-full text-xs font-semibold bg-green-900/50 text-green-300">
            Operativo
          </span>
        </div>

        <!-- Backend general -->
        <div class="flex items-center justify-between py-3"
             :class="health ? 'border-b border-slate-700/60' : ''">
          <span class="text-sm text-slate-300">Backend API (Laravel)</span>
          <span :class="['px-3 py-0.5 rounded-full text-xs font-semibold', statusClass[apiStatus]]">
            {{ loading ? 'Verificando...' : statusLabel[apiStatus] }}
          </span>
        </div>

        <!-- Sub-checks (DB, Redis) -->
        <template v-if="health?.checks">
          <div v-for="(check, name) in health.checks" :key="name"
               class="flex items-center justify-between py-2 pl-4">
            <span class="text-xs text-slate-400 capitalize">{{ name }}</span>
            <span :class="['px-2 py-0.5 rounded-full text-xs font-medium',
              check.status === 'ok' ? 'bg-green-900/40 text-green-400' : 'bg-red-900/40 text-red-400']">
              {{ check.status === 'ok' ? 'ok' : check.message ?? 'error' }}
            </span>
          </div>
        </template>

        <!-- Error detalle -->
        <p v-if="error && !loading" class="mt-3 text-xs text-red-400 bg-red-950/30 rounded-lg px-3 py-2">
          {{ error.message }}
        </p>
      </section>

      <!-- Modules grid -->
      <section>
        <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">
          Módulos del sistema
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 hover:border-indigo-500 transition-colors">
            <span class="text-3xl block mb-3">📄</span>
            <h3 class="font-semibold text-white mb-1">Gestión Documental</h3>
            <p class="text-slate-400 text-sm leading-relaxed">
              Carga, organización y control de versiones de documentos universitarios.
            </p>
          </div>
          <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 hover:border-indigo-500 transition-colors">
            <span class="text-3xl block mb-3">🎥</span>
            <h3 class="font-semibold text-white mb-1">Streaming</h3>
            <p class="text-slate-400 text-sm leading-relaxed">
              Transmisión en vivo y reproducción de conferencias del congreso.
            </p>
          </div>
          <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 hover:border-indigo-500 transition-colors">
            <span class="text-3xl block mb-3">👤</span>
            <h3 class="font-semibold text-white mb-1">Usuarios y Roles</h3>
            <p class="text-slate-400 text-sm leading-relaxed">
              Gestión de participantes, ponentes y administradores.
            </p>
          </div>
        </div>
      </section>

    </main>
  </div>
</template>
