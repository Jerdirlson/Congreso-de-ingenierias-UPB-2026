# Video y streaming (videoponencias)

## Propósito
Subida y procesamiento de videoponencias para la modalidad **virtual**, usando
**Cloudflare Stream**. Documento técnico detallado: [../CLOUDFLARE_STREAM.md](../CLOUDFLARE_STREAM.md).

## Archivos clave
- Backend: `VideoController`, `CloudflareVideoWebhookController`,
  `Services/CloudflareStreamService`, `Jobs/ProcessVideoUploadJob`, modelo `SubmissionVideo`.
- Frontend: `views/ponente/SubmissionDetail` (sección Videoponencia),
  `views/admin/AdminSubmissionDetail` (revisar/aprobar/rechazar video).

## Flujo
1. Ponencia en `video_pending` (tras elegir modalidad virtual).
2. **Subir video** `POST /submissions/{id}/videos` → `ProcessVideoUploadJob` (cola) sube a
   Cloudflare Stream; estado del video: `pending` → `processing` → `ready` (o `error`).
3. `GET /submissions/{id}/videos/status` para sondear el estado.
4. Webhook `POST /webhooks/cloudflare-video` actualiza el estado cuando termina el procesado.
5. Admin: `GET /admin/submissions/{id}/video/stream` (descarga),
   `PATCH .../video/approve` (→ `payment_pending`) y `.../video/reject` (con motivo → `video_pending`).

## Notas
- La cola requiere el contenedor `queue-worker` activo.
- Requiere credenciales de Cloudflare Stream (ver CLOUDFLARE_STREAM.md / `.env`).
