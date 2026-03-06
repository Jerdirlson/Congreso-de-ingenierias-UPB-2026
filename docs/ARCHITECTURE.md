# Arquitectura del Sistema

## Diagrama general

```
Internet
    │
    ▼
┌─────────────────────────────────────┐
│         VPS (207.248.81.83)         │
│      Ubuntu Server 24.04.3          │
│  2 cores · 4 GB RAM · 100 GB disco  │
│                                     │
│  ┌──────────────────────────────┐   │
│  │   Nginx (puerto 80 / 443)    │   │
│  │   Reverse proxy + SSL        │   │
│  └───────────┬──────────────────┘   │
│              │                      │
│     ┌────────┴────────┐             │
│     ▼                 ▼             │
│  ┌──────────┐   ┌──────────────┐   │
│  │ Frontend │   │ Backend      │   │
│  │ Vue SPA  │   │ Laravel 12   │   │
│  │ (static) │   │ PHP-FPM 8.4  │   │
│  └──────────┘   └──────┬───────┘   │
│                         │           │
│              ┌──────────┴───────┐   │
│              │                  │   │
│         ┌────▼─────┐     ┌─────▼──┐│
│         │ MySQL 8.0│     │ Redis 7 ││
│         │ (datos)  │     │ (caché) ││
│         └──────────┘     └─────────┘│
└─────────────────────────────────────┘
```

## Componentes

### Nginx (reverse proxy)
- Sirve el frontend como archivos estáticos
- Redirige `/api/*` y `/sanctum/*` al backend via FastCGI (PHP-FPM)
- Sirve archivos de media desde `/storage/`
- Aplica rate limiting y cabeceras de seguridad

### Frontend — Vue 3
- SPA (Single Page Application)
- Compilado con Vite en producción (`npm run build` → `dist/`)
- Tailwind CSS v4 para estilos (sin tailwind.config.js)
- Se comunica con el backend via `/api/*` (mismo dominio, sin CORS)

### Backend — Laravel 12
- API REST pura (sin vistas Blade en uso)
- Autenticación con Laravel Sanctum (tokens)
- Permisos con Spatie Laravel Permission
- Manejo de archivos con Spatie Media Library
- PHP-FPM en puerto 9000 (solo accesible internamente)

### MySQL 8.0
- Base de datos principal
- Solo accesible dentro de la red Docker (no expuesta al exterior)
- Datos persistidos en volumen Docker `cgr-mysql`

### Redis 7
- Caché de la aplicación
- Almacenamiento de sesiones
- Cola de trabajos
- Solo accesible dentro de la red Docker

## Modelo de datos

```
users
 ├── submissions (1:N)
 ├── reviews (1:N, reviewer_id)
 ├── payments (1:N)
 └── registrations (1:N)

thematic_axes
 ├── submissions (1:N)
 └── submission_abstracts (1:N, llm_axis_id)

submissions
 ├── submission_abstracts (1:N)
 ├── submission_documents (1:N)
 ├── submission_videos (1:1)
 ├── reviews (1:N)
 ├── payments (1:N)
 └── registrations (1:N)

submission_documents
 └── reviews (1:N)

payments
 └── registrations (1:1)
```

## Estructura del repositorio

```
congreso-ingenierias-2026/
├── frontend/               # Vue 3 + TypeScript + Vite
│   ├── src/
│   ├── public/
│   ├── .env.production
│   └── package.json
├── backend/                # Laravel 12
│   ├── app/
│   ├── routes/api.php
│   ├── database/
│   └── .env.example
├── docker/
│   ├── nginx/
│   │   ├── default.conf        # Nginx desarrollo
│   │   ├── nginx.prod.conf     # Nginx producción
│   │   └── Dockerfile.prod     # Build multi-stage frontend + nginx
│   └── php/
│       ├── Dockerfile
│       ├── entrypoint.sh
│       └── php.ini
├── .github/
│   └── workflows/
│       └── deploy.yml          # CI/CD con GitHub Actions
├── docker-compose.yml          # Entorno de desarrollo
├── docker-compose.prod.yml     # Entorno de producción
├── deploy.sh                   # Script de despliegue manual
├── .env.prod.example           # Plantilla de variables de producción
└── docs/                       # Esta documentación
```

## Red Docker

Todos los servicios se comunican en la red interna `cgr-network`. Solo Nginx tiene puertos expuestos al host (80 y 443). MySQL y Redis no tienen puertos expuestos en producción.

## Volúmenes Docker

| Volumen | Contenido |
|---------|-----------|
| `cgr-mysql` | Datos de MySQL (persistentes) |
| `cgr-redis` | Datos de Redis (persistentes) |
| `cgr-storage` | Archivos subidos por usuarios |
| `cgr-vendor` | Dependencias PHP de Composer |
