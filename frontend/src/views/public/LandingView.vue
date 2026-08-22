<script setup lang="ts">
import NavBar from '../../components/NavBar.vue'
import StatsBar from '../../components/StatsBar.vue'
import HeroSection from '../../components/HeroSection.vue'
import AboutSection from '../../components/AboutSection.vue'
import TracksSection from '../../components/TracksSection.vue'
import SpeakersSection from '../../components/SpeakersSection.vue'
import ScheduleSection from '../../components/ScheduleSection.vue'
import TimelineSection from '../../components/TimelineSection.vue'
import PricingSection from '../../components/PricingSection.vue'
import CtaSection from '../../components/CtaSection.vue'
import FooterSection from '../../components/FooterSection.vue'
</script>

<template>
  <div class="bg-cgr-bg font-sans text-white">
    <NavBar />
    <main class="lg:pl-72">
      <HeroSection />
      <StatsBar />
      <AboutSection />
      <TracksSection />
      <SpeakersSection />
      <ScheduleSection />
      <TimelineSection />
      <PricingSection />
      <CtaSection />
      <FooterSection />
    </main>
  </div>
</template>
