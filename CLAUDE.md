# Congreso Ingenierías 2026 — Contexto del proyecto

Plataforma del **II Congreso Internacional de Ingeniería 2026** (UPB Bucaramanga):
inscripciones, envío y revisión de ponencias, pagos, videoponencias y panel de administración.

> ⚠️ **App en producción.** Cambios de BD **solo por migraciones**; nada de breaking
> changes en la API. Trabajar directo en `main` está permitido, pero commitear/pushear
> solo cuando el usuario lo pida.

## Stack
- **Frontend** (`frontend/`): Vue 3 + TypeScript + Vite + Tailwind CSS v4 (`@tailwindcss/vite`, sin `tailwind.config.js`). Pinia para estado, Vue Router 4.
- **Backend** (`backend/`): PHP Laravel 12 + Sanctum (tokens) + Spatie Permission (roles) + Spatie Media Library.
- **Infra**: Docker Compose — MySQL 8, Redis 7, Nginx, Mailpit, phpMyAdmin, queue worker.
- PHP/Composer **solo dentro de Docker** (no hay PHP nativo en local ni en el servidor).

## Cómo correr
```bash
docker compose up --build
# Frontend http://localhost:5173 · API http://localhost:8000/api · phpMyAdmin http://localhost:8080
docker compose exec backend php artisan migrate --force   # en prod usar --force
docker compose exec backend php artisan tinker            # consola
```

## Estructura
- `frontend/src/` — `views/` (public, ponente, participante, revisor, admin, shared), `components/`, `stores/` (auth, settings), `composables/` (`useFetchApi`, …), `router/index.ts`.
- `backend/app/` — `Http/Controllers/Api/`, `Models/`, `Notifications/`, `Jobs/`, `Services/`, `Policies/`.
- `backend/routes/api.php` — todas las rutas (prefijo `/api`).
- `docs/` — documentación técnica (ver abajo).

## Roles
`admin`, `administrativo`, `revisor`, `ponente`, `participante`. **Roles múltiples** soportados
(p. ej. revisor + ponente). El rol "primario" prioriza el no-revisor. Ver
[docs/features/auth-y-roles.md](docs/features/auth-y-roles.md).

## Convenciones
- **Tailwind only** — sin CSS custom; usar las clases/tema (`cgr-*`).
- API REST bajo `/api`, auth con Sanctum (token en `localStorage` como `api_token`).
- Frontend habla con la API vía `composables/useFetchApi.ts` (`get/post/put/patch/delete/postForm`).
- Mensajes de UI y de error en **español**.
- Throttling por ruta; correos vía Notifications + plantillas Blade en `resources/views/emails/`.

## Funcionalidades core (docs por feature)
- [Autenticación y roles](docs/features/auth-y-roles.md) — registro, login, verificación y reset por código, cambio de contraseña, impersonación, doble rol.
- [Ponencias — flujo del ponente](docs/features/ponencias-flujo.md) — creación, resumen, eje, documento, modalidad, máquina de estados.
- [Revisión (revisor y comité)](docs/features/revision.md) — asignación y dictamen de resúmenes y documentos.
- [Inscripciones y pagos](docs/features/inscripciones-y-pagos.md) — pago (modo demo), tickets, inscripción externa UPB.
- [Video y streaming](docs/features/video-streaming.md) — videoponencias por link de YouTube + protocolo de grabación.
- [Agenda pública](docs/features/agenda-publica.md) — vista `/agenda` con el inicio de cada jornada.
- [Panel de administración](docs/features/admin.md) — usuarios, ponencias, revisores, métricas, correo masivo, ejes.
- [Configuración / cierre del congreso](docs/features/configuracion-cierre.md) — banderas para cerrar registro de ponentes y subida de ponencias.
- [Correos y notificaciones](docs/features/correos-y-notificaciones.md) — códigos, confirmaciones, correo masivo.

## Documentación técnica existente
`docs/ARCHITECTURE.md` · `docs/API.md` · `docs/DATABASE.md` (+ `database.dbml`) ·
`docs/DEPLOYMENT.md` · `docs/SECURITY.md` · `docs/CLOUDFLARE_STREAM.md` · `docs/PLAN.md`.
