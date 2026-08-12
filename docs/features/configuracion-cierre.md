# Configuración / cierre del congreso

## Propósito
Banderas globales que el admin activa para **cerrar** el registro de ponentes, la subida
de ponencias y/o el envío de videoponencias, sin afectar a quienes ya están en proceso ni a
los participantes.

## Archivos clave
- Backend: modelo `AppSetting` (tabla `settings`, clave-valor, cacheado), `SettingsController`.
- Frontend: `stores/settings.ts`, `views/admin/AdminSettingsView.vue` (toggles),
  y consumo en `RegisterView`, `PonenteHome`, `NuevaSubmission`, `CallForAbstractView`,
  `SubmissionDetail`.

## Banderas (`AppSetting`)
- `ponente_registration_open` (default `true`): permite registrar nuevos ponentes.
- `submissions_open` (default `true`): permite crear ponencias nuevas.
- `video_upload_open` (default `true`): permite que los ponentes virtuales compartan el link
  de YouTube de su videoponencia. Quedó **desactivada** al migrar al flujo de link
  (agosto 2026); el comité la abre desde el panel cuando publique las indicaciones del video.

## Endpoints
- Público: `GET /settings` → `{ ponente_registration_open, submissions_open, video_upload_open }`.
- Admin: `GET /admin/settings`, `PUT /admin/settings`.

## Comportamiento al cerrar
- `submissions_open = false`:
  - `POST /submissions` → 422 ("El periodo para registrar nuevas ponencias está cerrado").
  - **NO** bloquea reenvío de resumen, documentos, video ni el resto del flujo de quienes ya
    subieron su resumen.
  - Front: oculta "Nueva ponencia" y muestra letrero "se acabó el tiempo para subir ponencias".
- `video_upload_open = false`:
  - `POST /submissions/{id}/videos` → 422 ("El envío de videoponencias está temporalmente en
    pausa…").
  - **NO** afecta los videos ya recibidos (`ready`) ni el resto del flujo
    (modalidad, pago, inscripción siguen igual).
  - Front: en `SubmissionDetail` el formulario del link se reemplaza por el aviso de que
    faltan publicar las indicaciones del video, con badge "Próximamente".
- `ponente_registration_open = false`:
  - `POST /register` con `registration_type=ponente` → 422 (sugiere registrarse como participante).
  - **Participantes siguen registrándose** sin cambios; el front oculta la opción de ponente.

## Reglas / notas
- El **backend es la barrera real** (422 aunque se fuerce la petición); el front solo refleja.
- Defaults abiertos: si las filas no existen en `settings`, ambas banderas se asumen `true`.
- Cambiar una bandera limpia su caché (`Cache::forget`).
