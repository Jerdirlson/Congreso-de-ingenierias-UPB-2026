<script setup lang="ts">
import NavBar from '../../components/NavBar.vue'
import FooterSection from '../../components/FooterSection.vue'

const cientifico: { nombre: string; pais: string; institucion: string }[] = [
  // Agrega los miembros aquí
]

const organizador: { nombre: string; facultad: string }[] = [
  // Agrega los miembros aquí
]
</script>

<template>
  <div class="bg-cgr-bg font-sans text-white">
    <NavBar />
    <main class="pt-16 lg:pl-72">

      <!-- HERO -->
      <section class="hero-gradient relative py-28 px-5 lg:px-20 overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cgr-purple-deep/30 rounded-full blur-3xl pointer-events-none" />
        <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-cgr-purple-dark/20 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 max-w-3xl mx-auto text-center">
          <div class="inline-flex items-center gap-2 border border-cgr-purple-dark rounded-full px-4 py-1.5 mb-8">
            <span class="w-2 h-2 rounded-full bg-cgr-purple animate-pulse" />
            <span class="text-cgr-accent text-xs font-semibold tracking-widest uppercase">II Congreso · 2026</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight mb-6">
            <span class="text-white">Nuestros</span><br />
            <span class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple bg-clip-text text-transparent">
              Comités
            </span>
          </h1>

          <p class="text-cgr-muted text-lg leading-relaxed max-w-2xl mx-auto">
            Conoce a las personas que hacen posible el
            <span class="text-white font-semibold">II Congreso Internacional de Ingeniería 2026</span>.
          </p>
        </div>
      </section>

      <!-- COMITÉ CIENTÍFICO -->
      <section class="bg-cgr-section py-20 px-5 lg:px-20">
        <div class="max-w-5xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Revisión y validación académica</span>
            <h2 class="mt-3 text-3xl font-black text-white">Comité Científico</h2>
          </div>

          <div class="overflow-hidden rounded-2xl border border-cgr-border">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-cgr-card border-b border-cgr-border">
                  <th class="text-left px-6 py-4 text-cgr-purple font-semibold text-xs uppercase tracking-wider">Nombre</th>
                  <th class="text-left px-6 py-4 text-cgr-purple font-semibold text-xs uppercase tracking-wider">País</th>
                  <th class="text-left px-6 py-4 text-cgr-purple font-semibold text-xs uppercase tracking-wider">Institución</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(miembro, i) in cientifico"
                  :key="i"
                  class="border-b border-cgr-border last:border-0 hover:bg-cgr-card/50 transition-colors"
                >
                  <td class="px-6 py-4 text-white font-medium">{{ miembro.nombre }}</td>
                  <td class="px-6 py-4 text-cgr-muted">{{ miembro.pais }}</td>
                  <td class="px-6 py-4 text-cgr-muted">{{ miembro.institucion }}</td>
                </tr>
                <tr v-if="cientifico.length === 0">
                  <td colspan="3" class="px-6 py-12 text-center text-cgr-muted text-sm">
                    Próximamente se publicará el listado del comité científico.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- COMITÉ ORGANIZADOR -->
      <section class="bg-cgr-bg py-20 px-5 lg:px-20">
        <div class="max-w-5xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Organización del evento</span>
            <h2 class="mt-3 text-3xl font-black text-white">Comité Organizador</h2>
          </div>

          <div class="overflow-hidden rounded-2xl border border-cgr-border">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-cgr-card border-b border-cgr-border">
                  <th class="text-left px-6 py-4 text-cgr-purple font-semibold text-xs uppercase tracking-wider">Nombre</th>
                  <th class="text-left px-6 py-4 text-cgr-purple font-semibold text-xs uppercase tracking-wider">Facultad</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(miembro, i) in organizador"
                  :key="i"
                  class="border-b border-cgr-border last:border-0 hover:bg-cgr-card/50 transition-colors"
                >
                  <td class="px-6 py-4 text-white font-medium">{{ miembro.nombre }}</td>
                  <td class="px-6 py-4 text-cgr-muted">{{ miembro.facultad }}</td>
                </tr>
                <tr v-if="organizador.length === 0">
                  <td colspan="2" class="px-6 py-12 text-center text-cgr-muted text-sm">
                    Próximamente se publicará el listado del comité organizador.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

    </main>
    <FooterSection />
  </div>
</template>
