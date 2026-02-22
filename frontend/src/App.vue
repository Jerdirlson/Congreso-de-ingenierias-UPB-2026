<script setup lang="ts">
import NavBar from './components/NavBar.vue'
import StatsBar from './components/StatsBar.vue'
import HeroSection from './components/HeroSection.vue'
import AboutSection from './components/AboutSection.vue'
import TracksSection from './components/TracksSection.vue'
import SpeakersSection from './components/SpeakersSection.vue'
import TimelineSection from './components/TimelineSection.vue'
import CtaSection from './components/CtaSection.vue'
import FooterSection from './components/FooterSection.vue'
</script>

<template>
  <div class="bg-cgr-bg font-sans text-white">
    <NavBar />
    <main>
      <HeroSection />
      <StatsBar />
      <AboutSection />
      <TracksSection />
      <SpeakersSection />
      <TimelineSection />
      <CtaSection />
    </main>
    <FooterSection />
  </div>
</template>
