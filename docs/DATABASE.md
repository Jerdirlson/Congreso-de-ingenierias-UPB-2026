# Diagrama de Base de Datos — Congreso Ingenierías 2026

## ERD Completo

```mermaid
erDiagram

    users {
        bigint id PK
        varchar name
        varchar email
        varchar password
        varchar phone
        enum document_type "cedula | pasaporte | cc_extranjera"
        varchar document_number
        varchar institution
        varchar country
        varchar city
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    thematic_axes {
        bigint id PK
        varchar name
        text description
        text keywords
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    submissions {
        bigint id PK
        bigint user_id FK
        bigint thematic_axis_id FK "nullable — asignado por LLM"
        varchar title
        enum status "draft | abstract_submitted | abstract_rejected | abstract_approved | under_review | revision_requested | document_approved | modality_selected | video_pending | video_ready | payment_pending | confirmed"
        enum modality "nullable | presencial_oral | presencial_poster | virtual | proyecto_aula"
        int abstract_attempts "veces que intentó con resumen"
        int document_version "versión actual del documento"
        timestamp created_at
        timestamp updated_at
    }

    submission_abstracts {
        bigint id PK
        bigint submission_id FK
        bigint llm_axis_id FK "nullable — eje sugerido por LLM"
        text content
        int version
        enum llm_status "pending | approved | rejected"
        decimal llm_confidence_score "0.00 a 100.00"
        text llm_justification
        json llm_raw_response
        timestamp processed_at
        timestamp created_at
    }

    submission_documents {
        bigint id PK
        bigint submission_id FK
        int version
        varchar original_filename
        varchar stored_path
        bigint file_size
        varchar mime_type
        enum status "pending_review | under_review | revision_requested | approved"
        timestamp submitted_at
    }

    reviews {
        bigint id PK
        bigint submission_document_id FK
        bigint submission_id FK
        bigint reviewer_id FK
        bigint assigned_by FK "admin que asignó"
        enum status "pending | in_progress | completed"
        enum decision "nullable | approved | rejected"
        text comments
        timestamp assigned_at
        timestamp started_at
        timestamp completed_at
    }

    submission_videos {
        bigint id PK
        bigint submission_id FK
        varchar cloudflare_uid
        varchar cloudflare_playback_url
        varchar cloudflare_thumbnail_url
        int duration_seconds
        enum status "pending | processing | ready | error"
        text error_message
        timestamp uploaded_at
        timestamp ready_at
    }

    payments {
        bigint id PK
        bigint user_id FK
        bigint submission_id FK "nullable — null si es participante"
        enum registration_type "participant | speaker"
        decimal amount
        varchar currency "default COP"
        enum status "pending | completed | failed | refunded"
        varchar external_payment_url "link externo de pasarela"
        varchar gateway_transaction_id "nullable"
        json gateway_response "nullable"
        timestamp paid_at
        timestamp created_at
        timestamp updated_at
    }

    registrations {
        bigint id PK
        bigint user_id FK
        bigint payment_id FK
        bigint submission_id FK "nullable — null si es participante"
        enum registration_type "participant | speaker"
        enum modality "nullable | presencial | virtual | proyecto_aula"
        varchar ticket_code "unique"
        timestamp confirmed_at
        boolean attended "control de asistencia en el evento"
        timestamp created_at
    }

    %% ─── Relaciones ───────────────────────────────────────────
    users           ||--o{ submissions         : "es ponente de"
    users           ||--o{ reviews             : "es revisor en (reviewer_id)"
    users           ||--o{ reviews             : "asignó (assigned_by)"
    users           ||--o{ payments            : "realizó"
    users           ||--o{ registrations       : "tiene"

    thematic_axes   ||--o{ submissions         : "clasifica"
    thematic_axes   ||--o{ submission_abstracts: "sugerido por LLM en"

    submissions     ||--o{ submission_abstracts : "tiene versiones de resumen"
    submissions     ||--o{ submission_documents : "tiene versiones de documento"
    submissions     ||--o| submission_videos    : "tiene video (solo virtual)"
    submissions     ||--o{ reviews             : "tiene revisiones"
    submissions     ||--o| payments            : "genera pago"
    submissions     ||--o| registrations       : "resulta en inscripción"

    submission_documents ||--o{ reviews        : "es revisado en"

    payments        ||--o| registrations       : "confirma"
```

---

## Flujo de Estados de `submissions.status`

```mermaid
stateDiagram-v2
    [*] --> draft : Ponente se registra

    draft --> abstract_submitted : Sube resumen

    abstract_submitted --> abstract_approved : LLM aprueba ✅
    abstract_submitted --> abstract_rejected : LLM rechaza ❌

    abstract_rejected --> abstract_submitted : Sube nuevo resumen

    abstract_approved --> under_review : Sube documento completo

    under_review --> revision_requested : Al menos un revisor rechaza ❌
    under_review --> document_approved  : TODOS los revisores aprueban ✅

    revision_requested --> under_review : Sube nueva versión del documento

    document_approved --> modality_selected : Elige modalidad

    modality_selected --> video_pending  : Eligió VIRTUAL
    modality_selected --> payment_pending : Eligió PRESENCIAL / PROYECTO AULA

    video_pending --> video_ready : Video procesado en Cloudflare ✅

    video_ready --> payment_pending : Avanza al pago

    payment_pending --> confirmed : Pago completado ✅
    payment_pending --> payment_pending : Pago fallido ❌ (reintenta)

    confirmed --> [*]
```

---

## Índices recomendados

```sql
-- submissions: búsquedas frecuentes por estado y por usuario
INDEX idx_submissions_status        (status)
INDEX idx_submissions_user_id       (user_id)
INDEX idx_submissions_axis_id       (thematic_axis_id)

-- reviews: buscar revisiones pendientes de un revisor
INDEX idx_reviews_reviewer_id       (reviewer_id)
INDEX idx_reviews_submission_id     (submission_id)
INDEX idx_reviews_document_id       (submission_document_id)
INDEX idx_reviews_status            (status)

-- payments: seguimiento de pagos por estado
INDEX idx_payments_user_id          (user_id)
INDEX idx_payments_status           (status)

-- registrations: ticket único
UNIQUE idx_registrations_ticket     (ticket_code)
INDEX  idx_registrations_user_id    (user_id)
```

---

## Notas de Integridad

| Regla | Descripción |
|---|---|
| `submissions.thematic_axis_id` | Nullable hasta que el LLM apruebe el resumen |
| `submissions.modality` | Nullable hasta que el ponente elija en `document_approved` |
| `reviews.decision` | Nullable mientras `status = pending` o `in_progress` |
| `payments.submission_id` | Nullable cuando `registration_type = participant` |
| `registrations.submission_id` | Nullable cuando `registration_type = participant` |
| `registrations.modality` | Nullable cuando `registration_type = participant` |
| Aprobación de documento | `status → document_approved` solo cuando **todos** los `reviews` con `submission_id = X` tienen `decision = approved` |
