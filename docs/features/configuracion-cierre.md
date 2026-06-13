# Configuración / cierre del congreso

## Propósito
Banderas globales que el admin activa para **cerrar** el registro de ponentes y/o la subida
de ponencias cuando termina el periodo, sin afectar a quienes ya están en proceso ni a los
participantes.

## Archivos clave
- Backend: modelo `AppSetting` (tabla `settings`, clave-valor, cacheado), `SettingsController`.
- Frontend: `stores/settings.ts`, `views/admin/AdminSettingsView.vue` (toggles),
  y consumo en `RegisterView`, `PonenteHome`, `NuevaSubmission`, `CallForAbstractView`.

## Banderas (`AppSetting`)
- `ponente_registration_open` (default `true`): permite registrar nuevos ponentes.
- `submissions_open` (default `true`): permite crear ponencias nuevas.

## Endpoints
- Público: `GET /settings` → `{ ponente_registration_open, submissions_open }`.
- Admin: `GET /admin/settings`, `PUT /admin/settings`.

## Comportamiento al cerrar
- `submissions_open = false`:
  - `POST /submissions` → 422 ("El periodo para registrar nuevas ponencias está cerrado").
  - **NO** bloquea reenvío de resumen, documentos, video ni el resto del flujo de quienes ya
    subieron su resumen.
  - Front: oculta "Nueva ponencia" y muestra letrero "se acabó el tiempo para subir ponencias".
- `ponente_registration_open = false`:
  - `POST /register` con `registration_type=ponente` → 422 (sugiere registrarse como participante).
  - **Participantes siguen registrándose** sin cambios; el front oculta la opción de ponente.

## Reglas / notas
- El **backend es la barrera real** (422 aunque se fuerce la petición); el front solo refleja.
- Defaults abiertos: si las filas no existen en `settings`, ambas banderas se asumen `true`.
- Cambiar una bandera limpia su caché (`Cache::forget`).
