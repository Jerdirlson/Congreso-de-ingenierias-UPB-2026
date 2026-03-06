<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
import AppSidebar from '../components/layout/AppSidebar.vue'
import AppHeader from '../components/layout/AppHeader.vue'

const auth = useAuthStore()

async function handleLogout() {
  await auth.logout()
  window.location.href = '/'
}
</script>

<template>
  <div class="min-h-screen bg-cgr-bg font-sans text-white flex">
    <AppSidebar :role="auth.role" />
    <div class="flex-1 flex flex-col min-w-0">
      <AppHeader :user="auth.user" :role="auth.role" @logout="handleLogout" />
      <main class="flex-1 p-6 overflow-auto">
        <RouterView />
      </main>
    </div>
  </div>
</template>
