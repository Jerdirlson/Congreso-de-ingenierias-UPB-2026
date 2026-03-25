<script setup lang="ts">
import NavBar from '../../components/NavBar.vue'
import FooterSection from '../../components/FooterSection.vue'

const cientifico: { nombre: string; pais: string; institucion: string }[] = [
  { nombre: 'Jackeline Martínez Gómez',          pais: 'Alemania',        institucion: 'Universidad de Ciencias Aplicadas de Trier' },
  { nombre: 'Paalo Andrea Moreno Yañez',          pais: 'Canadá',          institucion: 'Université de Sherbrooke' },
  { nombre: 'Jairo Viola',                        pais: 'EE.UU',           institucion: 'UCMerced' },
  { nombre: 'Roberto Alonso González Lezcano',    pais: 'España',          institucion: 'Universidad CEU San Pablo' },
  { nombre: 'Jorge Cerna Cortéz',                 pais: 'México',          institucion: 'Benemérita Universidad Autónoma de Puebla' },
  { nombre: 'Jorge Guillermo Díaz Rodríguez',     pais: 'México',          institucion: 'Campus Guadalajara - Tecnológico de Monterrey' },
  { nombre: 'Dora Luz González Bañales',          pais: 'México',          institucion: 'Instituto Tecnológico de Durango' },
  { nombre: 'Natalia Andrea Cano Londoño',        pais: 'Países Bajos',    institucion: 'University of Twente' },
  { nombre: 'Carlos Renzo Rivera',                pais: 'Perú',            institucion: 'Universidad Católica San Pablo' },
  { nombre: 'Mirza Cequea',                       pais: 'Chile',           institucion: 'Universidad Católica del Norte' },
  { nombre: 'Fredy Huamán',                       pais: 'Perú',            institucion: 'Universidad Católica San Pablo de Arequipa' },
  { nombre: 'Juan Felipe Miranda',                pais: 'Perú',            institucion: 'Universidad San Pablo' },
  { nombre: 'Jorge Arenas Bermudez',              pais: 'Chile',           institucion: 'Universidad Austral de Chile' },
  { nombre: 'Diego Méndez Chavez',                pais: 'Colombia',        institucion: 'Universidad Pontificia Javeriana' },
  { nombre: 'Carlos Eduardo García Sánchez',      pais: 'Colombia',        institucion: 'CDT del Gas' },
  { nombre: 'Elena Losik',                        pais: 'Colombia',        institucion: 'Instituto Colombiano del Petróleo ECOPETROL - ICEPET' },
  { nombre: 'Sebastian Alberto Pelaez',           pais: 'Colombia',        institucion: 'Politécnico Grancolombiano' },
  { nombre: 'Johan Leandro Téllez Garzón',        pais: 'Colombia',        institucion: 'Unidades Tecnológicas de Santander' },
  { nombre: 'Hernan Gonzalez Acuña',              pais: 'Colombia',        institucion: 'Universidad Autónoma de Bucaramanga' },
  { nombre: 'Marcela Villa Marulanda',            pais: 'Colombia',        institucion: 'Galápago Agroconsultores' },
  { nombre: 'Neila Mantilla Barbosa',             pais: 'Colombia',        institucion: 'Universidad de la Costa' },
  { nombre: 'Marianela Luzardo',                  pais: 'Colombia',        institucion: 'Universidad de Medellín' },
  { nombre: 'Yanyn Aurora Rincón Quintero',       pais: 'Colombia',        institucion: 'Universidad del Atlántico' },
  { nombre: 'Gustavo Emilio Ramirez Caballero',   pais: 'Colombia',        institucion: 'Universidad Industrial de Santander' },
  { nombre: 'Jorge Luis Chacón Velasco',          pais: 'Colombia',        institucion: 'Universidad Industrial de Santander' },
  { nombre: 'Yesid Javier Rueda Ordoñez',         pais: 'Colombia',        institucion: 'Universidad Industrial de Santander' },
  { nombre: 'César Augusto Sierra Avila',         pais: 'Colombia',        institucion: 'Universidad Nacional de Colombia' },
  { nombre: 'Miguel David Rojas López',           pais: 'Colombia',        institucion: 'Universidad Nacional de Colombia' },
  { nombre: 'Alba Soraya Aguilar Jiménez',        pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Alexandra Cerón Vivas',              pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Alfonso Santos Jaimes',              pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Claudia Patricia Retamoso Llamas',   pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Diego Alejandro Guzmán Arias',       pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Edwin Córdoba',                      pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Elsa Beatriz Gutiérrez Navas',       pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Emil Hernandez Arroyo',              pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Gabriel Fernando García Sánchez',    pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Gloria Patricia Fernández Morales',  pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Jairo Núñez Rodriguez',              pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Juan Carlos Forero Sarmiento',       pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Juan Carlos Mantilla Saavedra',      pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Lenin Javier Serrano Gil',           pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Ludym Jaimes Carrillo',              pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Luis Angel Silva',                   pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Marco Villamizar',                   pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Margareth Viecco Márquez',           pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Maryory Patricia Villamizar Leon',   pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Mónica Rocío Ordóñez Rodríguez',     pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Norma Cristina Solarte Vanegas',     pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Omar Pinzon Ardila',                 pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Rolando Guzman Lopez',               pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Sergio Andrés Gómez',                pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Olga Lucía Duarte Duarte Bolivar',   pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
  { nombre: 'Claudia Sofía Quintero',             pais: 'Colombia',        institucion: 'Universidad Pontificia Bolivariana' },
]

const organizador: { nombre: string; facultad: string }[] = [
  { nombre: 'Jacqueline Santamaría Valbuena',    facultad: 'Ingeniería Industrial' },
  { nombre: 'Alba Soraya Aguilar Jiménez',       facultad: 'Ingeniería Industrial' },
  { nombre: 'Mónica Rocío Ordóñez Rodríguez',    facultad: 'Ingeniería Eléctrica y Electrónica' },
  { nombre: 'Luis Angel Silva',                  facultad: 'Ingeniería Eléctrica y Electrónica' },
  { nombre: 'Juan Carlos Mantilla Saavedra',     facultad: 'Ingeniería Eléctrica y Electrónica' },
  { nombre: 'Rolando Guzman Lopez',              facultad: 'Ingeniería Mecánica' },
  { nombre: 'Gabriel Fernando García Sánchez',   facultad: 'Ingeniería Mecánica' },
  { nombre: 'Claudia Patricia Retamoso Llamas',  facultad: 'Ingeniería Civil' },
  { nombre: 'María Ximena García',               facultad: 'Ingeniería Civil' },
  { nombre: 'Francisco Jesús Molina Bovea',      facultad: 'Ingeniería de Sistemas e Informática' },
  { nombre: 'Diego Javier Parada',               facultad: 'Ingeniería de Sistemas e Informática' },
  { nombre: 'Omar Pinzon Ardila',                facultad: 'Ingeniería de Sistemas e Informática' },
  { nombre: 'Alexandra Cerón Vivas',             facultad: 'Ingeniería Ambiental' },
  { nombre: 'Angélica Muskus',                   facultad: 'Ingeniería Ambiental' },
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
