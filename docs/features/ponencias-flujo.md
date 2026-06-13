# Ponencias — flujo del ponente

## Propósito
Ciclo de vida de una ponencia: creación con resumen, confirmación de eje, revisión del
resumen, documento completo, revisión del documento, modalidad, video/pago y confirmación.

## Archivos clave
- Backend: `SubmissionController`, `AbstractController`, `AxisConfirmationController`,
  `DocumentSubmissionController`, `ModalityController`, `VideoController`; modelos
  `Submission`, `SubmissionAbstract`, `SubmissionDocument`, `SubmissionVideo`;
  `Services/AbstractFileExtractorService`, `Jobs/ClassifyAbstractJob`.
- Frontend: `views/ponente/PonenteHome`, `NuevaSubmission`, `SubmissionDetail`, `PonenteInscripcion`.

## Máquina de estados (`Submission::STATUS_*`)
```
draft → abstract_submitted → abstract_approved → under_review →
  (revision_requested ⇄ under_review) → document_approved → modality_selected →
  [virtual] video_pending → video_ready → payment_pending → confirmed
  [presencial] payment_pending → confirmed
abstract_submitted → abstract_rejected   (rechazo de resumen; permite reenvío)
```

## Funcionalidades / endpoints (rol `ponente`, correo verificado)
- **Crear ponencia** `POST /submissions`: título + eje + archivo de resumen. Extrae el texto
  del `.docx/.pdf` (no guarda el archivo, solo el texto en `submission_abstracts.content`),
  exige ≥100 palabras, crea ponencia + resumen **en una transacción**. Pasa a `abstract_submitted`.
- **Reenviar resumen** `POST /submissions/{id}/abstracts`: permitido en `draft`,
  `abstract_submitted`, `abstract_rejected` (también transaccional).
- **Confirmar eje** `PATCH /submissions/{id}/axis`: el ponente fija el `thematic_axis_id`.
  Existe sugerencia por IA (`ClassifyAbstractJob` + `LlmClassificationService`) que rellena
  `llm_axis_id` para recomendar un eje.
- **Subir documento** `POST /submissions/{id}/documents`: PDF, solo en `abstract_approved` o
  `revision_requested`. Pasa a `under_review`. Si es resubida tras correcciones, **re-asigna
  automáticamente** los mismos revisores.
- **Elegir modalidad** `PATCH /submissions/{id}/modality`: solo en `document_approved`.
  `virtual` → `video_pending`; presencial (oral/póster) → `payment_pending`.
- **Video** `POST /submissions/{id}/videos` (+ `/videos/status`): ver video-streaming.md.
- Gestión: `GET /submissions`, `GET/PATCH/DELETE /submissions/{id}` (editar título solo en
  `draft`; borrar = soft delete, solo en estados iniciales).

## Reglas de negocio
- **Máximo 2 ponencias por ponente** (las soft-deleted no cuentan).
- **Cierre de subida**: si la bandera `submissions_open` está en false, `POST /submissions`
  devuelve 422 ("se acabó el tiempo"); los que ya subieron resumen **siguen su proceso**.
- `content` del resumen es `LONGTEXT` (se amplió desde `TEXT` por documentos largos).
- El pago del ponente ocurre **al final** (ver inscripciones-y-pagos.md).
