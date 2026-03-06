/**
 * Store de autenticación — Pinia
 * Sincronizado con useFetchApi (api_token en localStorage)
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { setApiToken, getApiToken } from '../composables/useFetchApi'
import { useFetchApi } from '../composables/useFetchApi'

export type UserRole = 'admin' | 'administrativo' | 'revisor' | 'ponente' | 'participante'

export interface AuthUser {
  id: number
  name: string
  email: string
  email_verified_at?: string | null
  roles?: { name: string }[]
  role?: string
  phone?: string
  document_type?: string
  document_number?: string
  institution?: string
  country?: string
  city?: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const loading = ref(false)

  const token = computed(() => getApiToken())
  const isLoggedIn = computed(() => !!token.value && !!user.value)

  const role = computed((): UserRole | null => {
    if (!user.value) return null
    const r = user.value.role ?? user.value.roles?.[0]?.name
    return (r as UserRole) ?? null
  })

  const isEmailVerified = computed(() => !!user.value?.email_verified_at)

  const isPonente = computed(() => role.value === 'ponente')
  const isParticipante = computed(() => role.value === 'participante')
  const isRevisor = computed(() => role.value === 'revisor')
  const canManage = computed(() =>
    role.value === 'admin' || role.value === 'administrativo'
  )

  async function fetchMe(): Promise<boolean> {
    if (!getApiToken()) return false
    loading.value = true
    const api = useFetchApi()
    try {
      const data = await api.get<AuthUser>('/me')
      if (data) {
        user.value = data
        return true
      }
      setApiToken(null)
      user.value = null
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(body: Record<string, unknown>): Promise<{ ok: boolean; message?: string; errors?: Record<string, string[]> }> {
    loading.value = true
    const api = useFetchApi()
    try {
      const data = await api.post<{ token: string; user: AuthUser }>('/register', body)
      if (data) {
        setApiToken(data.token)
        user.value = data.user
        return { ok: true }
      }
      return {
        ok: false,
        message: api.error.value?.message ?? 'Error al registrarse',
        errors: api.error.value?.errors,
      }
    } finally {
      loading.value = false
    }
  }

  async function login(email: string, password: string): Promise<{ ok: boolean; message?: string }> {
    loading.value = true
    const api = useFetchApi()
    try {
      const data = await api.post<{ token: string; user: AuthUser }>('/login', { email, password })
      if (data) {
        setApiToken(data.token)
        user.value = data.user
        return { ok: true }
      }
      return { ok: false, message: api.error.value?.message ?? 'Credenciales incorrectas' }
    } finally {
      loading.value = false
    }
  }

  async function logout(): Promise<void> {
    const api = useFetchApi()
    await api.post('/logout', {}).catch(() => {})
    setApiToken(null)
    user.value = null
  }

  function setUser(u: AuthUser | null) {
    user.value = u
  }

  return {
    user,
    loading,
    token,
    isLoggedIn,
    isEmailVerified,
    role,
    isPonente,
    isParticipante,
    isRevisor,
    canManage,
    fetchMe,
    register,
    login,
    logout,
    setUser,
  }
})
