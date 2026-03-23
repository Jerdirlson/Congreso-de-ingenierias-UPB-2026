<script setup lang="ts">
import NavBar from '../../components/NavBar.vue'
import FooterSection from '../../components/FooterSection.vue'
import { useScrollReveal } from '../../composables/useScrollReveal'

const { setRef } = useScrollReveal()

const lugares = [
  {
    nombre: 'Cañón del Chicamocha',
    desc: 'Uno de los paisajes más representativos de Colombia, a pocos minutos del campus.',
    icon: 'mountain',
  },
  {
    nombre: 'Parque Nacional del Chicamocha',
    desc: 'Naturaleza e imponentes vistas del cañón con atracciones turísticas de talla mundial.',
    icon: 'park',
  },
  {
    nombre: 'Centro Histórico de Girón',
    desc: 'Patrimonio nacional de arquitectura colonial, una de las joyas del Área Metropolitana.',
    icon: 'building',
  },
  {
    nombre: 'Parque del Agua',
    desc: 'Espacio verde icónico en el corazón de Bucaramanga, ideal para descansar.',
    icon: 'park',
  },
  {
    nombre: 'Parque García Rovira',
    desc: 'Tradicional plaza en el centro histórico de Bucaramanga con arquitectura republicana.',
    icon: 'building',
  },
  {
    nombre: 'Parque Cerro del Santísimo',
    desc: 'Vista panorámica incomparable del valle desde las alturas de Floridablanca.',
    icon: 'mountain',
  },
]

const hoteles = [
  {
    nombre: 'Hotel Ventura',
    ubicacion: 'Floridablanca — a 100 m de la UPB',
    desc: 'Hotel 4 estrellas con piscina, parking gratuito y centro de fitness. La opción más cercana al campus.',
    estrellas: 4,
    link: 'https://hotelventura.com.co/es/hotel',
  },
  {
    nombre: 'Hotel Club Campestre',
    ubicacion: 'Cra. 21 #30-02, Cañaveral, Floridablanca',
    desc: 'Jardín, pista de tenis, squash, campo de golf y Wi-Fi gratuita en un entorno natural privilegiado.',
    estrellas: 4,
    link: 'https://campestrebucaramanga.com/hotel-4/',
  },
  {
    nombre: 'Hampton by Hilton',
    ubicacion: 'Cra. 33 #46-07, Bucaramanga',
    desc: 'Centro de fitness, bañera de hidromasaje, Sky lobby en piso 13 y Wi-Fi gratuita en la zona urbana.',
    estrellas: 4,
    link: 'https://www.hilton.com/es/hotels/bgahxhx-hampton-bucaramanga/',
  },
]
</script>

<template>
  <div class="bg-cgr-bg font-sans text-white">
    <NavBar />
    <main class="pt-16 lg:pl-72">

      <!-- ── 1. HERO ──────────────────────────────────────────────────── -->
      <section class="hero-gradient relative py-28 px-5 lg:px-20 overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cgr-purple-deep/30 rounded-full blur-3xl pointer-events-none" />
        <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-cgr-purple-dark/20 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 max-w-3xl mx-auto text-center">
          <div class="inline-flex items-center gap-2 border border-cgr-purple-dark rounded-full px-4 py-1.5 mb-8">
            <span class="w-2 h-2 rounded-full bg-cgr-purple animate-pulse" />
            <span class="text-cgr-accent text-xs font-semibold tracking-widest uppercase">Bucaramanga · 2026</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight mb-6">
            <span class="text-white">Sitios de</span><br />
            <span class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple bg-clip-text text-transparent">
              Interés
            </span>
          </h1>

          <p class="text-cgr-muted text-lg leading-relaxed max-w-2xl mx-auto">
            Todo lo que necesitas saber para aprovechar tu visita al
            <span class="text-white font-semibold">II Congreso Internacional de Ingeniería 2026</span>
            en Bucaramanga.
          </p>
        </div>
      </section>

      <!-- ── 2. SEDE DEL CONGRESO ────────────────────────────────────── -->
      <section class="bg-cgr-section py-20 px-5 lg:px-20">
        <div class="max-w-4xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Sede oficial</span>
            <h2 class="mt-3 text-3xl font-black text-white">Sede del Congreso</h2>
          </div>

          <div
            :ref="(el) => setRef(el as Element, 0)"
            class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-8 flex flex-col sm:flex-row gap-8 items-start"
          >
            <div class="w-14 h-14 rounded-2xl bg-cgr-purple/10 border border-cgr-purple/20 flex items-center justify-center shrink-0">
              <svg class="w-7 h-7 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
              </svg>
            </div>

            <div class="flex-1">
              <h3 class="text-white font-bold text-xl mb-1">Universidad Pontificia Bolivariana</h3>
              <p class="text-cgr-purple text-sm font-semibold mb-3">Seccional Bucaramanga</p>
              <div class="flex items-start gap-2 mb-4">
                <svg class="w-4 h-4 text-cgr-muted shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <span class="text-cgr-muted text-sm">Autopista Piedecuesta Kilómetro 7, Floridablanca, Santander</span>
              </div>
              <p class="text-cgr-muted text-sm leading-relaxed mb-6">
                Campus moderno rodeado de naturaleza, propicio para la investigación e intercambio académico.
                Un entorno privilegiado que combina la excelencia académica con la riqueza natural de Santander.
              </p>
              <a
                href="https://maps.google.com/?q=7.03859,-73.07112"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 border border-cgr-purple/50 text-cgr-purple hover:bg-cgr-purple/10 text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                Ver en Google Maps
              </a>
            </div>
          </div>

          <!-- Mapa -->
          <div class="mt-6 rounded-2xl overflow-hidden border border-cgr-border">
            <iframe
              src="https://www.openstreetmap.org/export/embed.html?bbox=-73.0911%2C7.0186%2C-73.0511%2C7.0586&layer=mapnik&marker=7.03859%2C-73.07112"
              width="100%"
              height="340"
              style="border:0; display:block;"
              loading="lazy"
              title="Ubicación UPB Bucaramanga"
            />
            <div class="bg-cgr-card px-5 py-3 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-cgr-purple shrink-0" />
              <span class="text-cgr-muted text-xs">Autopista Piedecuesta Kilómetro 7, Floridablanca, Santander</span>
            </div>
          </div>
        </div>
      </section>

      <!-- ── 3. SOBRE BUCARAMANGA ────────────────────────────────────── -->
      <section class="bg-cgr-bg py-20 px-5 lg:px-20">
        <div class="max-w-4xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">La ciudad</span>
            <h2 class="mt-3 text-3xl font-black text-white">Sobre Bucaramanga</h2>
          </div>

          <div
            :ref="(el) => setRef(el as Element, 1)"
            class="animate-reveal grid grid-cols-1 sm:grid-cols-3 gap-6"
          >
            <div class="bg-cgr-card border border-cgr-border rounded-2xl p-6 flex flex-col gap-3">
              <div class="w-10 h-10 rounded-xl bg-cgr-purple/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
                </svg>
              </div>
              <h3 class="text-white font-bold text-sm">La Ciudad Bonita</h3>
              <p class="text-cgr-muted text-xs leading-relaxed">Capital de Santander, conocida como "la Ciudad Bonita". Junto con Floridablanca, Girón y Piedecuesta forman el Área Metropolitana con más de un millón de habitantes.</p>
            </div>

            <div class="bg-cgr-card border border-cgr-border rounded-2xl p-6 flex flex-col gap-3">
              <div class="w-10 h-10 rounded-xl bg-cgr-purple/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.055 4.024.165C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-1.5-.75V14.25M12 12.75h.008v.008H12v-.008z"/>
                </svg>
              </div>
              <h3 class="text-white font-bold text-sm">Gastronomía única</h3>
              <p class="text-cgr-muted text-xs leading-relaxed">Destaca por su gastronomía exótica: hormigas culonas, cabro, pepitoria y dulces regionales únicos en el mundo que sorprenden a cada visitante.</p>
            </div>

            <div class="bg-cgr-card border border-cgr-border rounded-2xl p-6 flex flex-col gap-3">
              <div class="w-10 h-10 rounded-xl bg-cgr-purple/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.115 5.19l.319 1.913A6 6 0 008.11 10.36L9.75 12l-.387.775c-.217.433-.132.956.21 1.298l1.348 1.348c.21.21.329.497.329.795v1.089c0 .426.24.815.622 1.006l.153.076c.433.217.956.132 1.298-.21l.723-.723a8.7 8.7 0 002.288-4.042 1.087 1.087 0 00-.358-1.099l-1.33-1.108c-.251-.21-.582-.299-.905-.245l-1.17.195a1.125 1.125 0 01-.98-.314l-.295-.295a1.125 1.125 0 010-1.591l.13-.132a1.125 1.125 0 011.3-.21l.603.302a.809.809 0 001.086-1.086L14.25 7.5l1.256-.837a4.5 4.5 0 001.528-1.732l.146-.292M6.115 5.19A9 9 0 1017.18 4.64M6.115 5.19A8.965 8.965 0 0112 3c1.929 0 3.716.607 5.18 1.64"/>
                </svg>
              </div>
              <h3 class="text-white font-bold text-sm">Turismo de aventura</h3>
              <p class="text-cgr-muted text-xs leading-relaxed">Posición estratégica para el turismo de aventura con el impresionante Cañón del Chicamocha, parapente, senderismo y deportes extremos al alcance.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- ── 4. LUGARES PARA VISITAR ─────────────────────────────────── -->
      <section class="bg-cgr-section py-20 px-5 lg:px-20">
        <div class="max-w-5xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Turismo</span>
            <h2 class="mt-3 text-3xl font-black text-white">Lugares para visitar</h2>
            <p class="mt-3 text-cgr-muted text-sm max-w-xl mx-auto">Aprovecha tu visita al congreso para descubrir algunos de los rincones más hermosos de Santander.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
              v-for="(lugar, i) in lugares"
              :key="i"
              :ref="(el) => setRef(el as Element, i + 10)"
              class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-6 flex gap-4 items-start hover:border-cgr-purple-dark hover:-translate-y-1 hover:shadow-lg hover:shadow-cgr-purple/10 transition-all duration-300"
            >
              <div class="w-10 h-10 rounded-xl bg-cgr-purple/10 flex items-center justify-center shrink-0">
                <!-- mountain -->
                <svg v-if="lugar.icon === 'mountain'" class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                </svg>
                <!-- park -->
                <svg v-else-if="lugar.icon === 'park'" class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                </svg>
                <!-- building -->
                <svg v-else class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-white font-bold text-sm mb-1">{{ lugar.nombre }}</h3>
                <p class="text-cgr-muted text-xs leading-relaxed">{{ lugar.desc }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── 5. ALOJAMIENTO ──────────────────────────────────────────── -->
      <section class="bg-cgr-bg py-20 px-5 lg:px-20">
        <div class="max-w-5xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Hospedaje</span>
            <h2 class="mt-3 text-3xl font-black text-white">Alojamiento recomendado</h2>
            <p class="mt-3 text-cgr-muted text-sm max-w-xl mx-auto">Opciones cercanas al campus y a la ciudad, seleccionadas por comodidad y accesibilidad.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div
              v-for="(hotel, i) in hoteles"
              :key="i"
              :ref="(el) => setRef(el as Element, i + 20)"
              class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-7 flex flex-col gap-4 hover:border-cgr-purple-dark hover:-translate-y-1 hover:shadow-lg hover:shadow-cgr-purple/10 transition-all duration-300"
            >
              <!-- Estrellas -->
              <div class="flex gap-0.5">
                <svg
                  v-for="s in hotel.estrellas"
                  :key="s"
                  class="w-3.5 h-3.5 text-amber-400"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              </div>

              <div>
                <h3 class="text-white font-bold text-base leading-snug">{{ hotel.nombre }}</h3>
                <div class="flex items-center gap-1.5 mt-1">
                  <svg class="w-3.5 h-3.5 text-cgr-muted shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                  </svg>
                  <span class="text-cgr-muted text-xs">{{ hotel.ubicacion }}</span>
                </div>
              </div>

              <p class="text-cgr-muted text-xs leading-relaxed flex-1">{{ hotel.desc }}</p>

              <a
                :href="hotel.link"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center gap-2 border border-cgr-border hover:border-cgr-purple text-cgr-muted hover:text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-colors"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
                Ver hotel
              </a>
            </div>
          </div>
        </div>
      </section>

      <!-- ── 6. DESCARGA GUÍA ────────────────────────────────────────── -->
      <section class="cta-gradient relative py-20 px-5 lg:px-20 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-cgr-purple/5 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 max-w-2xl mx-auto text-center">
          <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-cgr-purple/20 border border-cgr-purple/30 mb-6">
            <svg class="w-7 h-7 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
          </div>

          <h2 class="text-2xl sm:text-3xl font-black text-white mb-3">Guía de sitios de interés</h2>
          <p class="text-cgr-muted text-base mb-8 leading-relaxed">
            ¿Quieres tener toda esta información a mano durante tu visita?
            Descarga la guía oficial con mapas, direcciones y recomendaciones.
          </p>

          <a
            href="/storage/docs/Location.docx"
            download="Location.docx"
            class="inline-flex items-center gap-2 bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-8 py-3.5 rounded-xl hover:opacity-90 transition-opacity text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Descargar guía de sitios de interés
          </a>
        </div>
      </section>

    </main>
    <FooterSection />
  </div>
</template>
