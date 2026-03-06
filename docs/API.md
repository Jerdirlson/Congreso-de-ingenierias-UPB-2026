# API Reference

**Base URL:** `http://congreso2026.bucaramanga.upb.edu.co/api`  
**Autenticación:** Laravel Sanctum (Bearer token)  
**Formato:** JSON

---

## Health

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/health` | No | Estado del sistema |

---

## Autenticación

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| POST | `/register` | No | Registro (ponente o participante) |
| POST | `/login` | No | Iniciar sesión |
| POST | `/logout` | Sí | Cerrar sesión |
| GET | `/me` | Sí | Usuario actual |

**Registro (POST /register):**
```json
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "secret123",
  "password_confirmation": "secret123",
  "registration_type": "ponente",
  "phone": "+57 300 123 4567",
  "document_type": "cedula",
  "document_number": "12345678",
  "institution": "UPB",
  "country": "Colombia",
  "city": "Bucaramanga"
}
```

---

## Ejes temáticos (público)

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/thematic-axes` | No | Listar ejes temáticos activos |

---

## Ponencia (rol: ponente)

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/submissions` | Sí | Mis ponencias |
| POST | `/submissions` | Sí | Crear ponencia |
| GET | `/submissions/{id}` | Sí | Ver ponencia |
| PATCH | `/submissions/{id}` | Sí | Actualizar título (solo draft) |
| POST | `/submissions/{id}/abstracts` | Sí | Subir resumen (dispara clasificación LLM) |
| POST | `/submissions/{id}/documents` | Sí | Subir documento PDF |
| PATCH | `/submissions/{id}/modality` | Sí | Elegir modalidad |
| POST | `/submissions/{id}/videos` | Sí | Iniciar subida de videoponencia |

---

## Revisión (rol: revisor)

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/reviews` | Sí | Revisiones asignadas |
| GET | `/reviews/{id}` | Sí | Ver revisión |
| PATCH | `/reviews/{id}` | Sí | Iniciar o completar revisión (decision, comments) |

---

## Admin (rol: admin | administrativo)

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/admin/submissions` | Sí | Listar todas las ponencias |
| GET | `/admin/submissions/{id}` | Sí | Ver ponencia |
| POST | `/admin/submissions/{id}/assign-reviewer` | Sí | Asignar revisor |
| GET | `/admin/thematic-axes` | Sí | Listar ejes |
| POST | `/admin/thematic-axes` | Sí | Crear eje |
| GET | `/admin/thematic-axes/{id}` | Sí | Ver eje |
| PUT | `/admin/thematic-axes/{id}` | Sí | Actualizar eje |
| DELETE | `/admin/thematic-axes/{id}` | Sí | Eliminar eje |

---

## Pagos e inscripciones (rol: ponente | participante)

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| POST | `/payments` | Sí | Iniciar pago (retorna checkout_url) |
| GET | `/registrations` | Sí | Mis inscripciones (incluye ticket_code si ya pagó) |

**Body para pagos:** `{ "registration_type": "participant" }` (solo asistencia) o `{ "registration_type": "speaker", "submission_id": 1 }` (ponente)

---

## Webhooks

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| POST | `/webhooks/cloudflare-video` | No | Callback cuando video está listo |
| POST | `/webhooks/payment` | No | Callback de pasarela de pago |

---

## Rate limiting

| Tipo | Límite |
|------|--------|
| Auth (login/register) | 10 req/min |
| Rutas públicas | 120 req/min por IP |
| Rutas autenticadas | 60 req/min por usuario |

---

## Header de autenticación

```
Authorization: Bearer {token}
```
