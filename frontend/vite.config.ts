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
    proxy: {
      '/api': {
        target: apiTarget,
        changeOrigin: true,
        // Host esperado por Laravel (evita 500 por TrustHosts/proxy)
        headers: { Host: 'localhost:8000' },
      },
    },
  },
})
