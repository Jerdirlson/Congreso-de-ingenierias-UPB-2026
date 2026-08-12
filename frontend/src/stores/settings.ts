/**
 * Store de configuración del congreso (banderas públicas).
 * - ponenteRegistrationOpen: permite registrar nuevos ponentes
 * - submissionsOpen: permite crear nuevas ponencias
 * - videoUploadOpen: permite subir videoponencias
 */
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useFetchApi } from '../composables/useFetchApi'

interface PublicSettings {
  ponente_registration_open: boolean
  submissions_open: boolean
  video_upload_open: boolean
}

export const useSettingsStore = defineStore('settings', () => {
  const ponenteRegistrationOpen = ref(true)
  const submissionsOpen = ref(true)
  const videoUploadOpen = ref(true)
  const loaded = ref(false)

  async function fetch(): Promise<void> {
    const api = useFetchApi()
    const data = await api.get<PublicSettings>('/settings')
    if (data) {
      ponenteRegistrationOpen.value = data.ponente_registration_open
      submissionsOpen.value = data.submissions_open
      videoUploadOpen.value = data.video_upload_open
    }
    loaded.value = true
  }

  return { ponenteRegistrationOpen, submissionsOpen, videoUploadOpen, loaded, fetch }
})
