# Plan del Sistema — Congreso Ingenierías 2026

## Visión General

Plataforma web para gestionar el registro, envío, revisión y presentación de ponencias del Congreso de Ingenierías UPB 2026. El sistema soporta dos tipos de usuario: **participantes** (solo asistencia) y **ponentes** (presentan trabajos académicos), con un flujo de estados bien definido para cada uno.

---

## Tipos de Usuario / Roles

| Rol | Descripción |
|---|---|
| `participante` | Asiste al congreso sin presentar ponencia |
| `ponente` | Presenta una ponencia (presencial, virtual o proyecto de aula) |
| `revisor` | Revisa y dictamina documentos de ponentes |
| `administrativo` | Gestiona el congreso: asigna revisores, monitorea estados |
| `admin` | Acceso total al sistema |

---

## Modalidades de Participación

### 1. Participante (solo asistencia)
Asiste al congreso sin presentar trabajo académico.

### 2. Ponente — Modalidad Presencial
Presenta su trabajo en el recinto del evento. Dos sub-modalidades:
- **Oral**: exposición hablada con diapositivas
- **Poster**: presentación en formato cartel

### 3. Ponente — Modalidad Virtual
Envía una videoponencia grabada. El video debe cumplir requisitos de duración y formato especificados en la carta de aceptación. **Fecha límite de entrega: 28 de septiembre de 2026.**

### 4. Ponente — Proyectos de Aula
Modalidad especial para trabajos destacados de estudiantes de ingeniería UPB y experiencias exitosas de colegios seleccionados.

---

## Flujos de Usuario

### Flujo A — Participante (solo asistencia)

```
1. Registro (datos personales e institucionales)
      ↓
2. Bienvenida e introducción al congreso
      ↓
3. Pasarela de pago
      ↓
4. ✅ CONFIRMADO — acceso al congreso
```

---

### Flujo B — Ponente

```
1. Registro (datos personales, institucionales, título tentativo de ponencia)
      ↓
2. Subir resumen / abstract de la ponencia
      ↓
3. Clasificación por LLM
   ├── ✅ Coincide con un eje temático → eje asignado, avanza al paso 4
   └── ❌ No coincide → RECHAZADO con motivo
         └── Puede volver a subir un nuevo resumen (sin límite de intentos)
      ↓
4. Subir documento completo (paper/ponencia en PDF)
      ↓
5. Asignación a revisor (manual por administrador o automática)
      ↓
6. Revisión del documento
   ├── ✅ APROBADO → avanza al paso 7
   └── ❌ RECHAZADO con comentarios
         └── Ponente puede subir nueva versión del documento → vuelve al paso 5
      ↓
7. Selección de modalidad de presentación
   ├── Presencial (oral o poster) → pasa directamente al paso 9
   └── Virtual → pasa al paso 8
      ↓
8. [Solo virtual] Subir videoponencia (Cloudflare Stream)
      ↓
9. Pasarela de pago
      ↓
10. ✅ CONFIRMADO como ponente
```

---

## Estados de una Ponencia (Submission)

Los estados son secuenciales y representan exactamente en qué paso del flujo se encuentra:

```
draft                  → Ponente registrado, aún no envió resumen
abstract_submitted     → Resumen enviado, esperando procesamiento LLM
abstract_rejected      → LLM rechazó, puede reenviar resumen
abstract_approved      → LLM aprobó y asignó eje temático
document_pending       → Esperando que el ponente suba el documento
under_review           → Documento asignado a revisor, en revisión
revision_requested     → Revisor rechazó con comentarios, esperando nueva versión
document_approved      → Documento aprobado por revisor
modality_pending       → Ponente debe seleccionar modalidad de presentación
video_pending          → (Solo virtual) Esperando que el ponente suba video
video_ready            → Video subido y procesado por Cloudflare
payment_pending        → Esperando pago de inscripción
confirmed              → Proceso completo ✅
```

---

## Ejes Temáticos (Thematic Axes)

Los ejes temáticos son definidos por los organizadores del congreso. El LLM los recibe como contexto para clasificar cada resumen. Cada eje tiene:
- Nombre y descripción detallada
- Palabras clave de referencia
- Estado activo/inactivo

**Nota de diseño**: el LLM (Claude API) recibe el resumen + la lista de ejes con sus descripciones y debe devolver: eje asignado + score de confianza + justificación. Si el score está por debajo de un umbral configurable, se considera rechazo.

---

## Diseño de Base de Datos

### Entidades principales

#### `users`
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| name | varchar(255) | Nombre completo |
| email | varchar(255) unique | |
| password | varchar(255) | Hash bcrypt |
| phone | varchar(20) | Teléfono de contacto |
| document_type | enum(cedula, pasaporte, cc_extranjera) | Tipo de documento de identidad |
| document_number | varchar(50) | Número de documento |
| institution | varchar(255) | Institución/universidad de origen |
| country | varchar(100) | |
| city | varchar(100) | |
| email_verified_at | timestamp | Verificación de correo |
| created_at / updated_at | timestamps | |

> Los roles se gestionan con **Spatie Laravel Permission** (tabla `roles`, `model_has_roles`)

---

#### `thematic_axes` (Ejes temáticos)
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| name | varchar(255) | Ej: "Inteligencia Artificial e Industria 4.0" |
| description | text | Descripción detallada para el LLM |
| keywords | text | Palabras clave separadas por coma |
| is_active | boolean | Controla si el eje está disponible |
| created_at / updated_at | timestamps | |

---

#### `submissions` (Ponencias — entidad central del flujo)
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| user_id | FK users | Ponente dueño de la ponencia |
| title | varchar(500) | Título de la ponencia |
| status | enum | Estado actual (ver lista arriba) |
| modality | enum(presencial_oral, presencial_poster, virtual, proyecto_aula) | nullable hasta que el ponente elija |
| thematic_axis_id | FK thematic_axes | Asignado por el LLM, nullable |
| abstract_attempts | int default 0 | Cuántas veces intentó con el resumen |
| document_version | int default 0 | Versión actual del documento |
| created_at / updated_at | timestamps | |

---

#### `submission_abstracts` (Historial de resúmenes enviados)
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| submission_id | FK submissions | |
| content | text | Contenido del resumen |
| version | int | Número de intento (1, 2, 3...) |
| llm_status | enum(pending, approved, rejected) | Resultado del LLM |
| llm_axis_id | FK thematic_axes | nullable |
| llm_confidence_score | decimal(5,2) | Score 0-100 del LLM |
| llm_justification | text | Explicación del LLM |
| llm_raw_response | json | Respuesta completa del LLM (auditoría) |
| processed_at | timestamp | Cuándo procesó el LLM |
| created_at | timestamp | |

---

#### `submission_documents` (Versiones del documento completo)
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| submission_id | FK submissions | |
| version | int | Versión del documento (1, 2, 3...) |
| original_filename | varchar(255) | Nombre original del archivo |
| stored_path | varchar(500) | Ruta interna de almacenamiento |
| file_size | bigint | Tamaño en bytes |
| mime_type | varchar(100) | Siempre PDF |
| status | enum(pending_review, under_review, revision_requested, approved) | |
| submitted_at | timestamp | |

---

#### `reviews` (Revisiones de documentos)
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| submission_document_id | FK submission_documents | Documento específico revisado |
| submission_id | FK submissions | Para facilitar queries |
| reviewer_id | FK users | El revisor asignado |
| assigned_by | FK users | Admin que asignó |
| status | enum(pending, in_progress, completed) | Estado de la revisión |
| decision | enum(approved, rejected) | nullable hasta completar |
| comments | text | Comentarios/observaciones del revisor |
| assigned_at | timestamp | |
| started_at | timestamp | Cuándo abrió el documento |
| completed_at | timestamp | |

---

#### `submission_videos` (Videos de ponencias virtuales)
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| submission_id | FK submissions | |
| cloudflare_uid | varchar(255) | UID en Cloudflare Stream |
| cloudflare_playback_url | varchar(500) | URL de reproducción |
| cloudflare_thumbnail_url | varchar(500) | |
| duration_seconds | int | Duración en segundos |
| status | enum(pending, processing, ready, error) | Estado en Cloudflare |
| error_message | text | nullable, si hubo error |
| uploaded_at | timestamp | |
| ready_at | timestamp | Cuándo estuvo listo en Cloudflare |

---

#### `payments` (Pagos de inscripción)
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| user_id | FK users | |
| submission_id | FK submissions | nullable (null si es participante) |
| registration_type | enum(participant, speaker) | |
| amount | decimal(10,2) | |
| currency | varchar(3) default 'COP' | |
| status | enum(pending, completed, failed, refunded) | |
| payment_gateway | varchar(50) | PSE, tarjeta, PayU, Wompi, etc. |
| gateway_transaction_id | varchar(255) | ID de transacción en la pasarela |
| gateway_response | json | Respuesta completa de la pasarela |
| paid_at | timestamp | nullable |
| created_at / updated_at | timestamps | |

---

#### `registrations` (Inscripciones confirmadas)
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| user_id | FK users | |
| payment_id | FK payments | |
| submission_id | FK submissions | nullable (null si es participante) |
| registration_type | enum(participant, speaker) | |
| modality | enum(presencial, virtual, proyecto_aula) | Solo para ponentes |
| ticket_code | varchar(50) unique | Código único de acceso al evento |
| confirmed_at | timestamp | |
| attended | boolean default false | Control de asistencia en el evento |
| created_at | timestamp | |

---

## Diagrama de Relaciones (ERD simplificado)

```
users (1) ──────────── (N) submissions
users (1) ──────────── (N) payments
users (1) ──────────── (N) registrations
users (1) ──────────── (N) reviews [como revisor]

submissions (1) ─────── (N) submission_abstracts
submissions (1) ─────── (N) submission_documents
submissions (1) ─────── (1) submission_videos
submissions (1) ─────── (N) reviews
submissions (N) ─────── (1) thematic_axes

submission_documents (1) ── (N) reviews

payments (1) ─────────── (1) registrations
```

---

## Decisiones Tomadas

| Decisión | Resolución |
|---|---|
| Pasarela de pago | **Link externo** — se mockea por ahora. La tabla `payments` registra estado (pending/completed/failed) pero el redirect es a URL externa |
| Revisores por documento | **Uno o varios**, asignados manualmente por el admin |
| Proyectos de Aula | **Mismo flujo** que un ponente regular |
| Ejes temáticos | Se definen al llegar a ese punto (están en la landing del congreso) |

## Preguntas Pendientes (para cuando lleguemos a ese punto)

- **Umbral del LLM**: score mínimo para aceptar/rechazar un resumen
- **Límite de reintentos** del resumen y del documento (¿o sin límite?)
- **Notificaciones**: proveedor de email para alertas de cambio de estado
- **Precios** de inscripción: ¿diferenciados por tipo de participante?
- **Fecha de cierre** de envío de abstracts y documentos

---

## Próximos Pasos

1. Responder las preguntas abiertas
2. Confirmar ejes temáticos iniciales del congreso
3. Crear migraciones de Laravel en orden de dependencias
4. Crear seeders con datos de prueba
5. Definir API endpoints del nuevo flujo
6. Construir frontend paso a paso del flujo de ponente
