# Video y streaming (videoponencias)

## Propósito
Entrega de la videoponencia de la modalidad **virtual**. Desde agosto de 2026 el ponente
**no sube el archivo**: comparte el **link de YouTube** de su video, que es el que se embebe
en la página y se conecta al Webex para transmitirlo el día del congreso.

> El flujo anterior (subida del archivo → Cloudflare Stream) quedó descontinuado.
> El histórico técnico sigue en [../CLOUDFLARE_STREAM.md](../CLOUDFLARE_STREAM.md).

## Archivos clave
- Backend: `VideoController`, modelo `SubmissionVideo` (columna `youtube_url`).
- Frontend: `views/ponente/SubmissionDetail` (sección Videoponencia),
  `views/admin/AdminSubmissionDetail` (reproducir/rechazar video).

## Flujo
0. La bandera `video_upload_open` debe estar activa; si está en pausa el paso 2 responde 422 y
   el ponente ve el aviso de "aún no habilitamos el envío"
   (ver [configuracion-cierre.md](configuracion-cierre.md)).
1. Ponencia en `video_pending` (tras elegir modalidad virtual).
2. **Compartir link** `POST /submissions/{id}/videos` con `{ youtube_url }`:
   - Se extrae el ID de 11 caracteres (acepta `watch`, `youtu.be`, `embed`, `live`, `shorts`)
     y se guarda normalizado como `https://www.youtube.com/watch?v=<id>`.
   - Se verifica contra el **oEmbed público de YouTube** que el video exista y sea
     insertable; si es privado o no existe → 422. Si la verificación falla por red, se
     deja pasar (no se bloquea al ponente por un problema nuestro).
   - El video queda en `ready` y la ponencia pasa a `confirmed` + correo de confirmación.
3. Admin: reproduce el video embebido en el detalle de la ponencia y puede
   `PATCH /admin/submissions/{id}/video/reject` (con motivo → vuelve a `video_pending`).
   `PATCH .../video/approve` sigue existiendo, pero la confirmación ya es automática.
4. `GET /submissions/{id}/videos/status` devuelve estado y `youtube_url` (ya no se sondea
   desde el front: el link queda listo al instante).

## Indicaciones al ponente (UI)
El formulario le exige explícitamente que la visibilidad del video sea
**No listado (Unlisted)** —no *Privado*—, que permita insertarse en otras páginas, que dure
máximo 10 minutos en 16:9 y 720p o más, y que no lo borre ni le cambie la visibilidad hasta
después del congreso. El texto vive en la sección "3. Videoponencia" de
`views/ponente/SubmissionDetail.vue`.

> **Por qué "No listado" y no "Público":** así lo pide el protocolo del comité
> (Comunicaciones, agosto 2026). *No listado* no afecta la validación ni la reproducción —
> el oEmbed de YouTube resuelve estos videos igual que los públicos y se pueden embeber;
> lo único que cambia es que no aparecen en las búsquedas de YouTube. *Privado* sí falla.

Junto al formulario hay un botón **"Cómo grabar tu videoponencia"** que abre un modal con la
infografía oficial del protocolo (`frontend/public/protocolo-video.png`): 7 pasos y los
requisitos técnicos (MP4 preferido —también MOV/AVI—, mín. 1280×720 y recomendado 1920×1080,
16:9 horizontal, máx. 10 min, H.264 / AAC estéreo, 30 fps). Para reemplazar la pieza basta
con sobrescribir ese PNG.

## Notas
- Los archivos que se alcanzaron a subir con el flujo anterior **no se borran**: la fila
  conserva `stored_path` y `GET /admin/submissions/{id}/video/stream` los sigue sirviendo.
  Esas ponencias volvieron a `video_pending` para que compartan su link
  (migración `2026_08_05_000002_reset_uploaded_videos_to_youtube_link`).
- Trazabilidad: `video_link_compartido`, `video_link_actualizado` y `video_link_requerido`
  en `submission_events`.
- Ya no se usan la cola ni credenciales de Cloudflare para las videoponencias.
