# Panel de administración

## Propósito
Gestión integral por `admin` / `administrativo`: usuarios y roles, ponencias y revisores,
métricas, correo masivo, ejes temáticos y configuración del congreso.

## Archivos clave
- Backend: `AdminUserController`, `AdminSubmissionController`, `AdminImpersonateController`,
  `AdminMetricsController`, `AdminAnalyticsController`, `AdminMailController`,
  `ThematicAxisController`, `SettingsController`.
- Frontend: `views/admin/` (`AdminHome`, `AdminUsersView`, `AdminSubmissions`,
  `AdminSubmissionDetail`, `AdminAxes`, `AdminAnalytics`, `AdminMailView`,
  `AdminSettingsView`), `components/layout/AppSidebar.vue`.

## Funcionalidades (todo bajo `/admin`, rol `admin|administrativo`)
- **Usuarios**: `GET /users`, `GET /users/{id}`, `PATCH /users/{id}/role`,
  `POST /users/{id}/assign-reviewer`, `DELETE /users/{id}/remove-reviewer`,
  `POST /users/{id}/impersonate`. La vista permite buscar y gestionar roles, alternar el rol
  revisor e impersonar (ver auth-y-roles.md).
- **Ponencias**: `GET /submissions` (filtros estado/eje), `GET /submissions/{id}`,
  asignar/quitar revisores, override de resumen, descarga de documentos y video. La lista
  tiene **buscador por título y autor**.
- **Métricas / Analytics**: `GET /metrics` (dashboard), `GET /analytics` (Google Analytics).
- **Correo masivo**: `POST /mail/preview`, `POST /mail/send` (segmenta por roles).
- **Ejes temáticos**: `apiResource thematic-axes`.
- **Configuración**: `GET/PUT /admin/settings` — ver configuracion-cierre.md.

## Notas
- Analytics: Measurement ID `G-R11B8GNDCS`, Property ID `533195556`.
- No se puede impersonar a un admin/administrativo.
