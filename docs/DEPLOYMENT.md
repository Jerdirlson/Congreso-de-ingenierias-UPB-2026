# Guía de Despliegue

## Entornos

| Entorno | URL | Rama Git |
|---------|-----|----------|
| Desarrollo local | http://localhost:5173 | cualquiera |
| Producción | http://congreso2026.bucaramanga.upb.edu.co | `main` |

---

## Desarrollo local

### Requisitos
- Docker Desktop (corriendo)
- Git

### Pasos

```bash
git clone https://github.com/Jerdirlson/Congreso-de-ingenierias-UPB-2026.git
cd Congreso-de-ingenierias-UPB-2026
docker compose up --build
```

El primer arranque instala dependencias y ejecuta migraciones + seeders automáticamente.
Los reinicios posteriores son rápidos gracias a los marcadores `.docker-seeded` y `.docker-published`.

### Servicios disponibles

| Servicio | URL |
|----------|-----|
| Frontend (Vite dev server) | http://localhost:5173 |
| Backend API | http://localhost:8000 |
| phpMyAdmin | http://localhost:8080 |
| Health check | http://localhost:8000/api/health |

---

## Producción (VPS)

### Datos del servidor

| Campo | Valor |
|-------|-------|
| Proveedor | UPB — Laboratorio CCA |
| SO | Ubuntu Server 24.04.3 |
| IP pública | 207.248.81.83 |
| Dominio | congreso2026.bucaramanga.upb.edu.co |
| Usuario SSH | congresoing-admin |
| RAM | 4 GB |
| CPU | 2 cores |
| Disco | 100 GB |

### CI/CD (automático)

Cada `git push` a la rama `main` dispara el pipeline de GitHub Actions:

```
push a main
    │
    ├─► Job 1: Backend Tests (GitHub, ubuntu-latest)
    │       └── PHP 8.4 + SQLite en memoria
    │           Si fallan → pipeline se detiene, NO hay deploy
    │
    └─► Job 2: Deploy (self-hosted runner en el VPS)
            ├── git pull origin main
            ├── docker compose -f docker-compose.prod.yml up --build -d
            └── docker compose ps (verifica estado)
```

El runner de GitHub Actions está instalado en el VPS como servicio systemd:
```bash
sudo systemctl status actions.runner.*
```

### Despliegue manual

Si necesitas desplegar sin hacer push:

```bash
# Conectarse al VPS
ssh congresoing-admin@207.248.81.83

# Ir al proyecto
cd ~/congreso

# Desplegar
bash deploy.sh
```

### Primer despliegue en un servidor nuevo

```bash
# 1. Instalar Docker
sudo apt update && sudo apt install -y ca-certificates curl gnupg git
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg
echo "deb [arch=amd64 signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu noble stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update && sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
sudo usermod -aG docker $USER && newgrp docker

# 2. Clonar el repositorio
cd ~ && git clone https://github.com/Jerdirlson/Congreso-de-ingenierias-UPB-2026.git congreso && cd congreso

# 3. Crear el archivo .env con contraseñas reales
cp .env.prod.example .env
nano .env   # <-- editar con contraseñas seguras

# 4. Desplegar
bash deploy.sh
```

### Variables de entorno de producción

El archivo `.env` en la raíz del proyecto (nunca en Git) es leído por `docker-compose.prod.yml`:

| Variable | Descripción |
|----------|-------------|
| `APP_URL` | URL pública del sitio (ej: `http://207.248.81.83`) |
| `SANCTUM_STATEFUL_DOMAINS` | Dominio para sesiones Sanctum |
| `DB_DATABASE` | Nombre de la base de datos |
| `DB_USERNAME` | Usuario de MySQL |
| `DB_PASSWORD` | Contraseña de MySQL |
| `DB_ROOT_PASSWORD` | Contraseña root de MySQL |

### Comandos útiles en producción

```bash
# Ver estado de los contenedores
docker compose -f docker-compose.prod.yml ps

# Ver logs en tiempo real
docker compose -f docker-compose.prod.yml logs -f

# Ver logs solo del backend
docker compose -f docker-compose.prod.yml logs backend --tail=50

# Reiniciar un servicio
docker compose -f docker-compose.prod.yml restart backend

# Acceder al backend (artisan, etc.)
docker compose -f docker-compose.prod.yml exec backend php artisan <comando>

# Limpiar caché de producción manualmente
docker compose -f docker-compose.prod.yml exec backend php artisan optimize:clear

# Ver el runner de CI/CD
sudo systemctl status actions.runner.*
```

---

## HTTPS / SSL (pendiente)

El CTIC entregará un certificado wildcard institucional (`*.bucaramanga.upb.edu.co`).

Cuando se reciban los archivos:
1. Copiarlos al VPS en `/etc/ssl/congreso/`
2. Descomentar el volumen SSL en `docker-compose.prod.yml`
3. Actualizar `nginx.prod.conf` para escuchar en 443 con los certificados
4. Agregar redirección HTTP → HTTPS
5. Actualizar `APP_URL` a `https://congreso2026.bucaramanga.upb.edu.co`
