# API Reference

**Base URL:** `http://congreso2026.bucaramanga.upb.edu.co/api`
**Autenticación:** Laravel Sanctum (Bearer token)
**Formato:** JSON

---

## Health

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/health` | No | Estado del sistema |

**Respuesta exitosa:**
```json
{
  "status": "ok",
  "service": "congreso-ingenierias-2026-api",
  "timestamp": "2026-02-21T18:00:00.000Z",
  "checks": {
    "database": { "status": "ok" },
    "redis": { "status": "ok" }
  }
}
```

---

## Eventos

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/events` | No | Listar todos los eventos |
| GET | `/events/{id}` | No | Ver un evento |
| POST | `/events` | Sí | Crear evento |
| PUT | `/events/{id}` | Sí | Actualizar evento |
| DELETE | `/events/{id}` | Sí | Eliminar evento |

---

## Ponentes

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/speakers` | No | Listar ponentes |
| GET | `/speakers/{id}` | No | Ver un ponente |
| POST | `/speakers` | Sí | Crear ponente |
| PUT | `/speakers/{id}` | Sí | Actualizar ponente |
| DELETE | `/speakers/{id}` | Sí | Eliminar ponente |

---

## Documentos

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/documents` | No | Listar documentos |
| GET | `/documents/{id}` | No | Ver un documento |
| GET | `/documents/{id}/download` | No | Descargar documento |
| POST | `/documents` | Sí | Subir documento |
| PUT | `/documents/{id}` | Sí | Actualizar documento |
| DELETE | `/documents/{id}` | Sí | Eliminar documento |

**Tipos de archivo permitidos:** `pdf`, `docx`, `pptx`, `xlsx`, `zip`
**Tamaño máximo:** 100 MB

---

## Transmisiones (Streams)

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/streams` | No | Listar transmisiones |
| GET | `/streams/{id}` | No | Ver una transmisión |
| POST | `/streams` | Sí | Crear transmisión |
| PUT | `/streams/{id}` | Sí | Actualizar transmisión |
| DELETE | `/streams/{id}` | Sí | Eliminar transmisión |
| POST | `/streams/{id}/go-live` | Sí | Iniciar transmisión en vivo |
| POST | `/streams/{id}/end` | Sí | Terminar transmisión |

---

## Rate Limiting

| Tipo de ruta | Límite |
|-------------|--------|
| Rutas públicas (GET) | 120 requests / minuto por IP |
| Rutas autenticadas | 60 requests / minuto por usuario |
| Nginx (todas) | 30 req/s por IP (burst: 60) |

Cuando se supera el límite se devuelve `HTTP 429 Too Many Requests`.

---

## Autenticación

Las rutas protegidas requieren un Bearer token en el header:

```
Authorization: Bearer {token}
```

El token se obtiene al iniciar sesión. Consultar la documentación de Laravel Sanctum para el flujo completo de autenticación SPA.
