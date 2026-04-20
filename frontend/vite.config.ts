import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

// En Docker: nginx. En local: localhost:8000
const apiTarget = process.env.VITE_API_PROXY_TARGET ?? 'http://localhost:8000'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
  ],
  server: {
    allowedHosts: 'all',
    watch: {
      // Necesario en Docker sobre Windows: los eventos de inotify no se propagan
      usePolling: true,
      interval: 500,
    },
    proxy: {
      '/api': {
        target: apiTarget,
        changeOrigin: true,
        // Host esperado por Laravel (evita 500 por TrustHosts/proxy)
        headers: { Host: 'localhost:8000' },
      },
      // Archivos subidos: Nginx los sirve en /storage/, Vite los re-proxy al backend
      '/storage': {
        target: apiTarget,
        changeOrigin: true,
        headers: { Host: 'localhost:8000' },
      },
    },
  },
})
