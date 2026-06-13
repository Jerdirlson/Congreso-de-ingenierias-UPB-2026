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
- `type`: `abstract` | `document`. Apunta a `submission_abstract_id` **o**
  `submission_document_id`.
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
- **Documento**: si se rechaza → `revision_requested` (doc `revision_requested`). Si **todos**
  los revisores del documento actual aprueban → `document_approved` (doc `approved`).
- Solo se evalúan las revisiones del resumen/documento **actual** (no versiones anteriores).

## Notas
- Roles con `revisor` + ponente: el revisor opera con su rol activo (ver auth-y-roles.md).
- Lista de revisores disponibles: `GET /admin/reviewers` (usuarios con rol `revisor`).
