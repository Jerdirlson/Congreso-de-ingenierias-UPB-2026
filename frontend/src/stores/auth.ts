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
  roles?: string[]
  role?: string
  phone?: string
  document_type?: string
  document_number?: string
  institution?: string
  country?: string
  city?: string
  external_registration_at?: string | null
  external_registration_paid_at?: string | null
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const loading = ref(false)
  const impersonating = ref(!!localStorage.getItem('cgr_admin_token'))
  const originalAdminUser = ref<AuthUser | null>(
    (() => { try { return JSON.parse(localStorage.getItem('cgr_admin_user') ?? '') } catch { return null } })()
  )

  // Rol activo seleccionado por el usuario (persiste en sessionStorage)
  const activeRole = ref<UserRole | null>(
    sessionStorage.getItem('cgr_active_role') as UserRole | null
  )

  const token = computed(() => getApiToken())
  const isLoggedIn = computed(() => !!token.value && !!user.value)

  // Todos los roles del usuario
  const allRoles = computed((): UserRole[] => {
    if (!user.value) return []
    return (user.value.roles ?? []) as UserRole[]
  })

  // Tiene rol revisor + al menos otro rol (necesita selección)
  const hasDualRole = computed(() =>
    allRoles.value.includes('revisor') && allRoles.value.length > 1
  )

  // Debe elegir con qué rol entrar (dual role y aún no ha elegido)
  const needsRoleSelection = computed(() => hasDualRole.value && !activeRole.value)

  // Rol activo: si el usuario eligió uno, se usa ese; si no, el rol primario del backend
  const role = computed((): UserRole | null => {
    if (!user.value) return null
    if (activeRole.value) return activeRole.value
    const r = user.value.role
    return (r as UserRole) ?? null
  })

  const isEmailVerified = computed(() => !!user.value?.email_verified_at)

  const isPonente     = computed(() => role.value === 'ponente')
  const isParticipante = computed(() => role.value === 'participante')
  const isRevisor     = computed(() => role.value === 'revisor')
  const canManage     = computed(() => role.value === 'admin' || role.value === 'administrativo')

  function setActiveRole(r: UserRole | null) {
    activeRole.value = r
    if (r) sessionStorage.setItem('cgr_active_role', r)
    else sessionStorage.removeItem('cgr_active_role')
  }

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
        // Limpiar activeRole previo al hacer login nuevo
        setActiveRole(null)
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
    setActiveRole(null)
  }

  function setUser(u: AuthUser | null) {
    user.value = u
  }

  async function confirmExternalRegistration(): Promise<boolean> {
    const api = useFetchApi()
    const data = await api.post<AuthUser>('/me/confirm-external-registration', {})
    if (data) {
      user.value = data
      return true
    }
    return false
  }

  async function startImpersonation(userId: number): Promise<boolean> {
    const api = useFetchApi()
    const data = await api.post<{ token: string; user: AuthUser }>(`/admin/users/${userId}/impersonate`, {})
    if (!data) return false
    localStorage.setItem('cgr_admin_token', getApiToken() ?? '')
    localStorage.setItem('cgr_admin_user', JSON.stringify(user.value))
    originalAdminUser.value = user.value
    setApiToken(data.token)
    user.value = data.user
    setActiveRole(null)
    impersonating.value = true
    return true
  }

  function stopImpersonation(): void {
    const savedToken = localStorage.getItem('cgr_admin_token')
    const savedUser = localStorage.getItem('cgr_admin_user')
    if (savedToken) setApiToken(savedToken)
    try { user.value = savedUser ? JSON.parse(savedUser) : null } catch { user.value = null }
    localStorage.removeItem('cgr_admin_token')
    localStorage.removeItem('cgr_admin_user')
    setActiveRole(null)
    impersonating.value = false
    originalAdminUser.value = null
  }

  return {
    user,
    loading,
    impersonating,
    originalAdminUser,
    activeRole,
    token,
    isLoggedIn,
    isEmailVerified,
    allRoles,
    hasDualRole,
    needsRoleSelection,
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
    setActiveRole,
    confirmExternalRegistration,
    startImpersonation,
    stopImpersonation,
  }
})
