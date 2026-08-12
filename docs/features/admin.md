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
- **Export de ponentes**: `GET /ponentes/export` → CSV con los datos de contacto de todos los
  usuarios con rol `ponente` (una fila por ponente). Pedido por Decanatura en agosto de 2026.
  Botón "Descargar ponentes (CSV)" en la vista Usuarios; `?solo_con_ponencia=1` deja por fuera
  a quien se registró pero nunca envió nada. Ver notas más abajo.
- **Métricas / Analytics**: `GET /metrics` (dashboard), `GET /analytics` (Google Analytics).
- **Correo masivo**: `POST /mail/preview`, `POST /mail/send` (segmenta por roles).
- **Ejes temáticos**: `apiResource thematic-axes`.
- **Configuración**: `GET/PUT /admin/settings` — ver configuracion-cierre.md.

## Notas
- Analytics: Measurement ID `G-R11B8GNDCS`, Property ID `533195556`.
- No se puede impersonar a un admin/administrativo.

### Export de ponentes (`AdminPonenteExportController`)
Columnas: nombre, correo, celular, documento, institución, ciudad, país, n.º de ponencias,
ponencias en firme, estados, modalidades, títulos, inscripción UPB, correo verificado y fecha
de registro. Decisiones que conviene no deshacer sin pensarlo:
- **BOM UTF-8 + separador `;`** — sin el BOM, Excel en Windows rompe tildes y eñes; el `;` es
  el separador que espera Excel con configuración regional de Colombia.
- **`fputcsv` con el `escape` explícito** — en PHP 8.4 omitirlo está deprecado.
- **El celular va crudo**, sin prefijos invisibles: la Decanatura necesita poder copiar los
  números tal cual. Si Excel muestra un número largo en notación científica, se importa con
  *Datos > Desde texto* marcando la columna como Texto.
- **Anti-inyección de fórmulas**: los campos los escribe el usuario al registrarse, así que un
  valor que empiece por `=`, `@`, tab o CR se antepone con `'` para que Excel no lo ejecute.
  `+` y `-` **no** se tocan porque hay celulares que empiezan por `+`.
- Los títulos se aplanan a una línea (hay títulos guardados con saltos de línea).
- Cada descarga queda en el log (`Export de ponentes descargado`) con el id del admin.

⚠️ Es información personal de contacto: el endpoint está detrás de `role:admin|administrativo`
y no debe exponerse a otros roles.
