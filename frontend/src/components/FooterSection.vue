<script setup lang="ts">
import LogoUpb from './LogoUpb.vue'

const year = new Date().getFullYear()

const colCongreso = [
  { label: 'Inicio',         href: '#inicio' },
  { label: 'Ponentes',       href: '#ponentes' },
  { label: 'Programa',       href: '#programa' },
  { label: 'Áreas temáticas',href: '#talleres' },
  { label: 'Inscripción',    href: '#inscripcion' },
]

const colUniversidad = [
  { label: 'UPB Bucaramanga',      href: '#' },
  { label: 'Facultad de Ingeniería',href: '#' },
  { label: 'Investigación',         href: '#' },
  { label: 'Extensión',             href: '#' },
]

const colRedes = [
  { label: 'Instagram', href: '#' },
  { label: 'LinkedIn',  href: '#' },
  { label: 'Twitter / X',href: '#' },
  { label: 'YouTube',   href: '#' },
]
</script>

<template>
  <footer class="bg-cgr-footer border-t border-cgr-border">
    <div class="max-w-7xl mx-auto px-5 lg:px-20 py-16">

      <!-- Grid principal -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">

        <!-- Logo UPB (colores oficiales) + descripción -->
        <div class="lg:col-span-1">
          <div class="flex flex-col gap-3 mb-4">
            <LogoUpb class="h-8 w-auto" />
            <p class="text-cgr-muted text-xs font-medium">Congreso Internacional de Ingeniería · 2025</p>
          </div>
          <p class="text-cgr-subtle text-sm leading-relaxed">
            El espacio donde la academia y la industria se encuentran para construir el futuro de la ingeniería latinoamericana.
          </p>
        </div>

        <!-- Columna Congreso -->
        <div>
          <h4 class="text-white font-semibold text-sm mb-5">Congreso</h4>
          <ul class="flex flex-col gap-3">
            <li v-for="item in colCongreso" :key="item.label">
              <a :href="item.href" class="text-cgr-subtle hover:text-white text-sm transition-colors">
                {{ item.label }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Columna Universidad -->
        <div>
          <h4 class="text-white font-semibold text-sm mb-5">Universidad</h4>
          <ul class="flex flex-col gap-3">
            <li v-for="item in colUniversidad" :key="item.label">
              <a :href="item.href" class="text-cgr-subtle hover:text-white text-sm transition-colors">
                {{ item.label }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Columna Redes -->
        <div>
          <h4 class="text-white font-semibold text-sm mb-5">Síguenos</h4>
          <ul class="flex flex-col gap-3">
            <li v-for="item in colRedes" :key="item.label">
              <a :href="item.href" class="text-cgr-subtle hover:text-white text-sm transition-colors">
                {{ item.label }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Divider -->
      <div class="border-t border-cgr-border pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-cgr-subtle text-xs">
          &copy; {{ year }} Universidad Pontificia Bolivariana — Bucaramanga. Todos los derechos reservados.
        </p>
        <p class="text-cgr-subtle text-xs">
          congreso2026.bucaramanga.upb.edu.co
        </p>
      </div>
    </div>
  </footer>
</template>
