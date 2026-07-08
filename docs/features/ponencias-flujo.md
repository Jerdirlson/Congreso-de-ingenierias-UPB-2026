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
draft → abstract_submitted → abstract_approved → modality_selected →
  [virtual] video_pending → video_ready → payment_pending → confirmed
  [presencial] payment_pending → confirmed
abstract_submitted → abstract_rejected   (rechazo de resumen; permite reenvío)
```
> **Cambio jul-2026**: con el resumen aprobado se pasa **directo a modalidad** — el
> antiguo paso 2 obligatorio ("documento PDF") se eliminó. Los estados `under_review`,
> `revision_requested` y `document_approved` son **legados**: se conservan por
> compatibilidad con ponencias que quedaron a mitad de ese flujo (también permiten
> elegir modalidad).

## Artículo — publicación en revista científica (opcional, carril paralelo)
- Disponible **desde `abstract_approved` en adelante** (incluye estados legados).
- **Opt-in**: `POST/DELETE /submissions/{id}/journal-opt-in` marca `journal_opt_in_at`
  en la ponencia ("quiero que mi trabajo sea considerado para revista"). Subir artículo
  implica opt-in automático. El opt-out solo se permite si no ha subido artículo.
- **Subir artículo** `POST /submissions/{id}/articles`: **Word** (`.doc/.docx`, máx 10 MB),
  tabla `submission_articles` (versionado como los documentos). Descarga:
  `GET /submissions/{id}/articles/{article}/download`.
- **Revisión propia** (tipo `article`, ver revision.md): su dictamen **solo cambia el
  estado del artículo** (`pending_review → under_review → approved | revision_requested`),
  nunca el de la ponencia — modalidad, video y pago siguen su curso.
- Resubida tras "pedir ajustes": re-asigna automáticamente los mismos revisores del artículo.

## Funcionalidades / endpoints (rol `ponente`, correo verificado)
- **Crear ponencia** `POST /submissions`: título + eje + archivo de resumen. Extrae el texto
  del `.docx/.pdf` (no guarda el archivo, solo el texto en `submission_abstracts.content`),
  exige ≥100 palabras, crea ponencia + resumen **en una transacción**. Pasa a `abstract_submitted`.
- **Reenviar resumen** `POST /submissions/{id}/abstracts`: permitido en `draft`,
  `abstract_submitted`, `abstract_rejected` (también transaccional). Si es reenvío tras
  `abstract_rejected`, **re-asigna automáticamente** a los revisores que ya dictaminaron
  (nueva revisión `pending` sobre la nueva versión); las revisiones de resumen aún abiertas
  pasan a apuntar a la última versión.
- **Confirmar eje** `PATCH /submissions/{id}/axis`: el ponente fija el `thematic_axis_id`.
  Existe sugerencia por IA (`ClassifyAbstractJob` + `LlmClassificationService`) que rellena
  `llm_axis_id` para recomendar un eje.
- **Subir documento** `POST /submissions/{id}/documents` (**legado**, ya sin UI): PDF, solo
  en `abstract_approved` o `revision_requested`. La descarga sigue activa para históricos.
- **Elegir modalidad** `PATCH /submissions/{id}/modality`: desde `abstract_approved`
  (también estados legados `under_review`/`revision_requested`/`document_approved`).
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
