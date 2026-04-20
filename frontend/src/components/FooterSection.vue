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
  { label: 'UPB Bucaramanga',                href: 'https://www.upb.edu.co/es/seccional-bucaramanga' },
  { label: 'Escuela de Ingeniería',          href: '#' },
  { label: 'Departamento de Ciencias Básicas', href: '#' },
  { label: 'Investigación',                  href: '#' },
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
            La ingeniería se encuentra con la humanidad. Industria 5.0: la tecnología al servicio
            del bienestar, la sostenibilidad y la equidad.
          </p>
          <p class="text-cgr-subtle text-xs mt-4 italic">
            Lema institucional: "Sin Límites: Creer, imaginar y crear"
          </p>
          <a
            href="mailto:congreso.ingenieria.bga@upb.edu.co"
            class="inline-flex items-center gap-1.5 mt-4 text-xs text-cgr-muted hover:text-cgr-purple transition-colors"
          >
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
            </svg>
            congreso.ingenieria.bga@upb.edu.co
          </a>
          <a
            href="https://www.instagram.com/iicongresoingupbbga?igsh=MWJjeWpvdmh4bjMyaQ=="
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-1.5 mt-3 text-xs text-cgr-muted hover:text-cgr-purple transition-colors"
          >
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.334 3.608 1.308.974.975 1.246 2.242 1.308 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.062 1.366-.334 2.633-1.308 3.608-.975.974-2.242 1.246-3.608 1.308-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.334-3.608-1.308-.974-.975-1.246-2.242-1.308-3.608C2.175 15.584 2.163 15.204 2.163 12s.012-3.584.07-4.85c.062-1.366.334-2.633 1.308-3.608C4.516 2.497 5.783 2.225 7.15 2.163 8.416 2.105 8.796 2.163 12 2.163zm0-2.163c-3.259 0-3.667.014-4.947.072-1.615.074-3.067.372-4.204 1.508C1.712 2.717 1.414 4.169 1.34 5.784 1.282 7.064 1.268 7.472 1.268 12c0 4.528.014 4.936.072 6.216.074 1.615.372 3.067 1.508 4.204 1.137 1.136 2.589 1.434 4.204 1.508C8.333 23.986 8.741 24 12 24s3.667-.014 4.947-.072c1.615-.074 3.067-.372 4.204-1.508 1.136-1.137 1.434-2.589 1.508-4.204.058-1.28.072-1.688.072-6.216 0-4.528-.014-4.936-.072-6.216-.074-1.615-.372-3.067-1.508-4.204C19.014.386 17.562.088 15.947.014 14.667-.044 14.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zm0 10.162a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
            </svg>
            @iicongresoingupbbga
          </a>
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
