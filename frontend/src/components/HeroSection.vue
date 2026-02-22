<template>
  <section
    id="inicio"
    class="relative flex items-center justify-center overflow-hidden"
    style="height: 700px; background: linear-gradient(135deg, #1a1035 0%, #0A0A1A 40%, #1a0a2e 100%);"
  >
    <div class="absolute inset-0 bg-cgr-bg/80" />

    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cgr-purple-deep/30 rounded-full blur-3xl pointer-events-none" />
    <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-cgr-purple-dark/20 rounded-full blur-3xl pointer-events-none" />

    <div class="relative z-10 text-center px-5 max-w-4xl mx-auto">

      <div class="inline-flex items-center gap-2 border border-cgr-purple-dark rounded-full px-4 py-1.5 mb-8">
        <span class="w-2 h-2 rounded-full bg-cgr-purple animate-pulse" />
        <span class="text-cgr-accent text-xs font-semibold tracking-widest uppercase">
          13 — 17 de octubre 2026
        </span>
      </div>

      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight mb-4">
        <span class="text-white">II Congreso Internacional</span><br />
        <span class="bg-gradient-to-r from-cgr-purple-dark to-cgr-purple bg-clip-text text-transparent">
          de Ingeniería
        </span>
      </h1>

      <p class="text-cgr-accent text-base sm:text-lg font-semibold mb-4 italic">
        "Donde la ingeniería se encuentra con la humanidad"
      </p>

      <p class="text-cgr-muted text-base sm:text-lg max-w-2xl mx-auto mb-10 leading-relaxed">
        Evolucionando de la eficiencia tecnológica al propósito humano.
        Industria 5.0: la tecnología al servicio del bienestar, la sostenibilidad y la equidad.
      </p>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
        <a
          href="#inscripcion"
          class="w-full sm:w-auto bg-gradient-to-r from-cgr-purple-dark to-cgr-purple text-white font-semibold px-8 py-3.5 rounded-xl hover:opacity-90 transition-opacity text-sm"
        >
          Inscríbete ahora
        </a>
        <a
          href="#programa"
          class="w-full sm:w-auto border border-cgr-border text-white font-semibold px-8 py-3.5 rounded-xl hover:border-cgr-purple transition-colors text-sm"
        >
          Ver programa
        </a>
      </div>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-6 text-cgr-subtle text-sm">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
          </svg>
          <span>UPB Bucaramanga, Colombia</span>
        </div>
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
          <span>Modalidad Híbrida</span>
        </div>
      </div>
    </div>
  </section>
</template>
