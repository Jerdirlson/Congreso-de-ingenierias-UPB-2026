<script setup lang="ts">
import { useScrollReveal } from '../composables/useScrollReveal'

const { setRef } = useScrollReveal()
</script>

<template>
  <section id="acerca" class="bg-cgr-bg py-24 px-5 lg:px-20">
    <div class="max-w-7xl mx-auto">

      <div class="text-center mb-16">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Acerca del congreso</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-black text-white">
          De la Inteligencia Artificial al Bienestar Humano
        </h2>
        <p class="mt-4 text-cgr-muted max-w-3xl mx-auto text-base leading-relaxed">
          En 2024, pusimos nuestra mirada en la frontera del conocimiento con el primer congreso sobre
          "Avances en Inteligencia Artificial y Tecnologías Emergentes". Para 2026, proponemos una visión
          más ambiciosa: la Quinta Revolución Industrial (Industria 5.0), donde la tecnología deja de ser
          un fin en sí misma para convertirse en la herramienta definitiva al servicio del bienestar,
          la sostenibilidad y la equidad.
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

        <div
          :ref="(el) => setRef(el as Element, 0)"
          class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-8 self-center hover:scale-[1.01] hover:shadow-lg hover:shadow-cgr-purple/5 transition-all duration-300"
        >
          <div class="w-12 h-12 rounded-xl bg-cgr-purple/10 flex items-center justify-center mb-6">
            <svg class="w-6 h-6 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
            </svg>
          </div>
          <h3 class="text-white font-bold text-lg mb-3">Objetivo General</h3>
          <p class="text-cgr-muted text-sm leading-relaxed">
            Consolidar un espacio de intercambio internacional que permita analizar cómo la ingeniería
            trasciende los límites técnicos para integrarse con la dimensión humana, promoviendo el
            bienestar social a través de la innovación, la sostenibilidad y las tecnologías habilitadoras 5.0.
          </p>
        </div>

        <div class="flex flex-col gap-6">
          <div
            :ref="(el) => setRef(el as Element, 1)"
            class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-6 flex gap-5 hover:border-cgr-purple-dark/50 hover:scale-[1.01] transition-all duration-300"
          >
            <div class="w-10 h-10 rounded-xl bg-cgr-purple/10 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
              </svg>
            </div>
            <div>
              <h4 class="text-white font-semibold text-sm mb-1">Para la Academia</h4>
              <p class="text-cgr-muted text-xs leading-relaxed">
                Publicar en un entorno de alto impacto que valora la intersección entre tecnología y sociedad.
                Libro de Memorias con ISSN y posibilidad de publicación en revista indexada.
              </p>
            </div>
          </div>

          <div
            :ref="(el) => setRef(el as Element, 2)"
            class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-6 flex gap-5 hover:border-cgr-purple-dark/50 hover:scale-[1.01] transition-all duration-300"
          >
            <div class="w-10 h-10 rounded-xl bg-cgr-purple/10 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>
              </svg>
            </div>
            <div>
              <h4 class="text-white font-semibold text-sm mb-1">Para la Industria</h4>
              <p class="text-cgr-muted text-xs leading-relaxed">
                Descubrir cómo adaptar procesos a la visión 5.0, aumentando la competitividad
                mediante el valor humano y la innovación tecnológica.
              </p>
            </div>
          </div>

          <div
            :ref="(el) => setRef(el as Element, 3)"
            class="animate-reveal bg-cgr-card border border-cgr-border rounded-2xl p-6 flex gap-5 hover:border-cgr-purple-dark/50 hover:scale-[1.01] transition-all duration-300"
          >
            <div class="w-10 h-10 rounded-xl bg-cgr-purple/10 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-cgr-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
              </svg>
            </div>
            <div>
              <h4 class="text-white font-semibold text-sm mb-1">Para las Ciencias Humanas y Sociales</h4>
              <p class="text-cgr-muted text-xs leading-relaxed">
                Un punto de encuentro entre la tecnología y las ciencias sociales y humanas.
                Un llamado a la colaboración radical y transdisciplinar.
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-16 text-center">
        <blockquote class="text-cgr-accent text-lg sm:text-xl font-semibold italic max-w-2xl mx-auto">
          "La ingeniería diseña el cómo, pero la humanidad define el para qué."
        </blockquote>
      </div>
    </div>
  </section>
</template>
