# Revisión (revisor y comité)

## Propósito
Asignación y dictamen de revisiones, tanto del **resumen** (abstract) como del **documento**
completo. El admin/comité asigna revisores; el revisor emite decisión (aprobar/rechazar).

## Archivos clave
- Backend: `ReviewController` (revisor), `AdminSubmissionController` (asignación), modelo
  `Review`, `Policies/` (autorización).
- Frontend: `views/revisor/RevisorHome`, `views/revisor/RevisionDetail`,
  `views/admin/AdminSubmissionDetail` (asignar/quitar revisores).

## Modelo `Review`
- `type`: `abstract` | `document` | `article`. Apunta a `submission_abstract_id`,
  `submission_document_id` **o** `submission_article_id`.
- `status`: `pending` → `in_progress` → `completed`. `decision`: `approved` | `rejected`.
- `comments` obligatorio al **rechazar**.

## Funcionalidades / endpoints
- **Asignar revisor de resumen** `POST /admin/submissions/{id}/assign-abstract-reviewer`
  (solo en `abstract_submitted`).
- **Asignar revisor de documento** `POST /admin/submissions/{id}/assign-reviewer`
  (requiere `document_id`; marca el doc `under_review`).
- **Quitar revisor** `DELETE /admin/submissions/{id}/reviews/{review}`.
- **Override admin del resumen**: `PATCH /admin/submissions/{id}/abstract/approve|reject`.
- **Revisor**: `GET /reviews` (con filtros estado/eje), `GET /reviews/{id}`,
  `PATCH /reviews/{id}` (1ª llamada inicia → `in_progress`; 2ª con `decision` → `completed`),
  `GET /reviews/{id}/document` (descarga PDF).

## Avance automático de estado (al completar una revisión)
- **Resumen**: si se rechaza → `abstract_rejected`. Si **todos** los revisores del resumen
  actual aprueban → `abstract_approved`.
- **Documento** (flujo legado): si se rechaza → `revision_requested`. Si **todos** aprueban
  → `document_approved` (doc `approved`).
- **Artículo** (revista): el dictamen **solo cambia el estado del artículo**
  (`revision_requested` o `approved`), nunca el de la ponencia.
  Asignación: `POST /admin/submissions/{id}/assign-article-reviewer` (`article_id`).
- Solo se evalúan las revisiones del resumen/documento/artículo **actual** (no versiones anteriores).

## Notas
- **Resumen corregido**: cuando el ponente reenvía el resumen tras un rechazo, se crea
  automáticamente una nueva revisión `pending` para los mismos revisores (ver
  ponencias-flujo.md). El panel del revisor marca estas versiones como
  "Corregido por el autor" y muestra el historial de dictámenes anteriores.
- Roles con `revisor` + ponente: el revisor opera con su rol activo (ver auth-y-roles.md).
- Lista de revisores disponibles: `GET /admin/reviewers` (usuarios con rol `revisor`).
