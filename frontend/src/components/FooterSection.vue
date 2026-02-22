<script setup lang="ts">
import LogoUpb from './LogoUpb.vue'

const year = new Date().getFullYear()

const colCongreso = [
  { label: 'Inicio',           href: '#inicio' },
  { label: 'Acerca de',        href: '#acerca' },
  { label: 'Ejes temáticos',   href: '#ejes' },
  { label: 'Actividades',      href: '#actividades' },
  { label: 'Fechas',           href: '#fechas' },
  { label: 'Inscripción',      href: '#inscripcion' },
]

const colUniversidad = [
  { label: 'UPB Bucaramanga',        href: 'https://www.upb.edu.co/es/seccional-bucaramanga' },
  { label: 'Escuela de Ingeniería',  href: '#' },
  { label: 'Investigación',          href: '#' },
]

const colFacultades = [
  { label: 'Ing. Civil' },
  { label: 'Ing. Mecánica' },
  { label: 'Ing. Industrial' },
  { label: 'Ing. de Sistemas' },
  { label: 'Ing. Ambiental' },
  { label: 'Ing. Eléctrica y Electrónica' },
]
</script>

<template>
  <footer class="bg-cgr-footer border-t border-cgr-border">
    <div class="max-w-7xl mx-auto px-5 lg:px-20 py-16">

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">

        <div class="lg:col-span-1">
          <div class="flex flex-col gap-3 mb-4">
            <LogoUpb class="h-8 w-auto" />
            <p class="text-cgr-muted text-xs font-medium">II Congreso Internacional de Ingeniería · 2026</p>
          </div>
          <p class="text-cgr-subtle text-sm leading-relaxed">
            Donde la ingeniería se encuentra con la humanidad. Industria 5.0: la tecnología al servicio
            del bienestar, la sostenibilidad y la equidad.
          </p>
          <p class="text-cgr-subtle text-xs mt-4 italic">
            Lema institucional: "Sin Límites: Creer, imaginar y crear"
          </p>
        </div>

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

        <div>
          <h4 class="text-white font-semibold text-sm mb-5">Facultades participantes</h4>
          <ul class="flex flex-col gap-3">
            <li v-for="item in colFacultades" :key="item.label">
              <span class="text-cgr-subtle text-sm">{{ item.label }}</span>
            </li>
          </ul>
        </div>
      </div>

      <div class="border-t border-cgr-border pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-cgr-subtle text-xs">
          &copy; {{ year }} Universidad Pontificia Bolivariana — Seccional Bucaramanga. Todos los derechos reservados.
        </p>
        <p class="text-cgr-subtle text-xs">
          Modalidad Híbrida · 13 al 17 de octubre de 2026
        </p>
      </div>
    </div>
  </footer>
</template>
