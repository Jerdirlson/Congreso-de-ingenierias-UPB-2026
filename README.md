# Congreso Ingenierías 2026 — UPB Bucaramanga

Sistema web para la gestión del Congreso de Ingenierías 2026 de la Universidad Pontificia Bolivariana, sede Bucaramanga.

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Frontend | Vue 3 + TypeScript + Vite + Tailwind CSS v4 |
| Backend | PHP 8.4 + Laravel 12 |
| Base de datos | MySQL 8.0 |
| Caché / Sesiones | Redis 7 |
| Autenticación | Laravel Sanctum |
| Permisos | Spatie Permission |
| Media | Spatie Media Library |
| Contenedores | Docker + Docker Compose |
| Servidor web | Nginx |

## Documentación

| Documento | Descripción |
|-----------|-------------|
| [Arquitectura](docs/ARCHITECTURE.md) | Diagrama del sistema, componentes y modelo de datos |
| [Despliegue](docs/DEPLOYMENT.md) | Cómo desplegar en producción y localmente |
| [Seguridad](docs/SECURITY.md) | Medidas de seguridad implementadas |
| [API](docs/API.md) | Endpoints disponibles de la API REST |

## Levantar en local (desarrollo)

**Requisitos:** Docker Desktop, Git

```bash
git clone https://github.com/Jerdirlson/Congreso-de-ingenierias-UPB-2026.git
cd Congreso-de-ingenierias-UPB-2026
docker compose up --build
```

| Servicio | URL |
|----------|-----|
| Frontend (Vue) | http://localhost:5173 |
| Backend (API) | http://localhost:8000 |
| phpMyAdmin | http://localhost:8080 |
| Health check | http://localhost:8000/api/health |

## Producción

| Servicio | URL |
|----------|-----|
| Sitio | http://congreso2026.bucaramanga.upb.edu.co |
| Health check | http://congreso2026.bucaramanga.upb.edu.co/api/health |
| VPS | 207.248.81.83 — Ubuntu Server 24.04.3 |

Ver [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) para instrucciones completas.
