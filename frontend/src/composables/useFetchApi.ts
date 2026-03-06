/**
 * useFetchApi — Composable centralizado para todas las peticiones al backend.
 *
 * Todas las peticiones van a /api/* (URL relativa).
 * Vite proxy las redirige al backend (localhost:8000 en local, nginx en Docker).
 */

import { ref } from 'vue'

// ── Tipos ────────────────────────────────────────────────────────────────────

export interface ApiError {
  message: string
  status?: number
  errors?: Record<string, string[]>   // Errores de validación Laravel
}

export interface ApiResponse<T> {
  data: T | null
  error: ApiError | null
  loading: boolean
}

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

// ── Token de autenticación (Sanctum) ─────────────────────────────────────────

const _token = ref<string | null>(localStorage.getItem('api_token'))

export function setApiToken(token: string | null) {
  _token.value = token
  if (token) localStorage.setItem('api_token', token)
  else        localStorage.removeItem('api_token')
}

export function getApiToken(): string | null {
  return _token.value
}

// ── Core request ─────────────────────────────────────────────────────────────

async function request<T>(
  method: HttpMethod,
  path: string,
  body?: unknown,
  extraHeaders?: HeadersInit,
): Promise<{ data: T | null; error: ApiError | null }> {
  const headers: Record<string, string> = {
    'Accept':       'application/json',
    'Content-Type': 'application/json',
    ...(extraHeaders as Record<string, string>),
  }

  if (_token.value) {
    headers['Authorization'] = `Bearer ${_token.value}`
  }

  try {
    const res = await fetch(`/api${path}`, {
      method,
      headers,
      body: body !== undefined ? JSON.stringify(body) : undefined,
    })

    if (res.status === 204) {
      // No Content — operaciones DELETE exitosas
      return { data: null, error: null }
    }

    const json = await res.json()

    if (!res.ok) {
      return {
        data: null,
        error: {
          message: json.message ?? `Error ${res.status}`,
          status:  res.status,
          errors:  json.errors ?? undefined,
        },
      }
    }

    return { data: json as T, error: null }

  } catch (e) {
    // Error de red (backend caído, CORS, timeout, etc.)
    return {
      data:  null,
      error: {
        message: e instanceof Error ? e.message : 'No se pudo conectar con el servidor',
        status:  undefined,
      },
    }
  }
}

// ── Composable ────────────────────────────────────────────────────────────────

export function useFetchApi() {
  const loading = ref(false)
  const error   = ref<ApiError | null>(null)

  async function call<T>(
    method: HttpMethod,
    path: string,
    body?: unknown,
  ): Promise<T | null> {
    loading.value = true
    error.value   = null

    const { data, error: err } = await request<T>(method, path, body)

    error.value   = err
    loading.value = false
    return data
  }

  async function postForm<T>(path: string, formData: FormData): Promise<T | null> {
    loading.value = true
    error.value   = null

    const headers: Record<string, string> = { Accept: 'application/json' }
    if (_token.value) headers['Authorization'] = `Bearer ${_token.value}`

    try {
      const res = await fetch(`/api${path}`, {
        method: 'POST',
        headers,
        body: formData,
      })

      if (res.status === 204) return null as T
      const json = await res.json()

      if (!res.ok) {
        error.value = {
          message: json.message ?? `Error ${res.status}`,
          status:  res.status,
          errors:  json.errors ?? undefined,
        }
        return null
      }
      return json as T
    } catch (e) {
      error.value = {
        message: e instanceof Error ? e.message : 'No se pudo conectar con el servidor',
        status:  undefined,
      }
      return null
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,

    get:      <T>(path: string)                    => call<T>('GET',    path),
    post:     <T>(path: string, body: unknown)       => call<T>('POST',   path, body),
    postForm: <T>(path: string, formData: FormData) => postForm<T>(path, formData),
    put:      <T>(path: string, body: unknown)      => call<T>('PUT',    path, body),
    patch:    <T>(path: string, body: unknown)      => call<T>('PATCH',  path, body),
    delete:   <T>(path: string)                     => call<T>('DELETE', path),
  }
}
