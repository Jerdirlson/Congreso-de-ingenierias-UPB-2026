<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import AppSidebar from '../components/layout/AppSidebar.vue'
import AppHeader from '../components/layout/AppHeader.vue'

const auth = useAuthStore()
const sidebarOpen = ref(true)

async function handleLogout() {
  await auth.logout()
  window.location.href = '/'
}
</script>

<template>
  <div class="min-h-screen bg-cgr-bg font-sans text-white flex">

    <!-- Overlay móvil -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-20 bg-black/50 lg:hidden"
      @click="sidebarOpen = false"
    />

    <AppSidebar :role="auth.role" :open="sidebarOpen" @close="sidebarOpen = false" />

    <div class="flex-1 flex flex-col min-w-0">
      <AppHeader
        :user="auth.user"
        :role="auth.role"
        :sidebar-open="sidebarOpen"
        @toggle-sidebar="sidebarOpen = !sidebarOpen"
        @logout="handleLogout"
      />
      <main class="flex-1 p-6 overflow-auto">
        <RouterView />
      </main>
    </div>
  </div>
</template>
