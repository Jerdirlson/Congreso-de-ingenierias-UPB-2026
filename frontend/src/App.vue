<script setup lang="ts">
import { computed } from 'vue'
import AdminView from './views/AdminView.vue'
import NavBar from './components/NavBar.vue'
import StatsBar from './components/StatsBar.vue'
import HeroSection from './components/HeroSection.vue'
import AboutSection from './components/AboutSection.vue'
import TracksSection from './components/TracksSection.vue'
import LiveStreamSection from './components/LiveStreamSection.vue'
import SpeakersSection from './components/SpeakersSection.vue'
import TimelineSection from './components/TimelineSection.vue'
import CtaSection from './components/CtaSection.vue'
import FooterSection from './components/FooterSection.vue'

const isAdmin = computed(() => window.location.pathname === '/admin')
</script>

<template>
  <AdminView v-if="isAdmin" />
  <div v-else class="bg-cgr-bg font-sans text-white">
    <NavBar />
    <main>
      <HeroSection />
      <StatsBar />
      <AboutSection />
      <TracksSection />
      <LiveStreamSection />
      <SpeakersSection />
      <TimelineSection />
      <CtaSection />
    </main>
    <FooterSection />
  </div>
</template>
