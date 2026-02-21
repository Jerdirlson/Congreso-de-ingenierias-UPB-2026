#!/bin/bash
# ══════════════════════════════════════════════════════════════════════════════
#  Congreso Ingenierías 2026 — Script de despliegue
#  Ejecutar en el VPS: bash deploy.sh
# ══════════════════════════════════════════════════════════════════════════════
set -e

echo ""
echo "========================================================="
echo "  Congreso Ingenierías 2026 — Despliegue en producción"
echo "========================================================="
echo ""

# Verificar que Docker está instalado y corriendo
command -v docker >/dev/null 2>&1 || { echo "❌ Docker no está instalado. Ver: https://docs.docker.com/engine/install/ubuntu/"; exit 1; }
docker info >/dev/null 2>&1       || { echo "❌ Docker no está corriendo. Ejecuta: sudo systemctl start docker"; exit 1; }
command -v git    >/dev/null 2>&1 || { echo "❌ Git no está instalado. Ejecuta: sudo apt install git"; exit 1; }

# Ir al directorio donde está este script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"
echo "📁 Directorio: $SCRIPT_DIR"

# Verificar que existe el .env con los secretos
if [ ! -f .env ]; then
    echo ""
    echo "❌ No se encontró el archivo .env"
    echo "   Crea el archivo con tus contraseñas:"
    echo ""
    echo "   cp .env.prod.example .env"
    echo "   nano .env   # <-- edita y cambia las contraseñas"
    echo ""
    exit 1
fi

# Actualizar código desde Git
echo "📦 Actualizando código (git pull)..."
git pull origin main

# Construir imágenes y levantar servicios
echo "🔨 Construyendo imágenes y levantando servicios..."
docker compose -f docker-compose.prod.yml up --build -d

echo ""
echo "⏳ Esperando que los servicios inicien (15 seg)..."
sleep 15

# Verificar estado
echo ""
echo "📊 Estado de los servicios:"
docker compose -f docker-compose.prod.yml ps

echo ""
echo "========================================================="
echo "  ✅ Despliegue completado!"
echo ""
echo "  Sitio:  http://207.248.81.83"
echo "  Health: http://207.248.81.83/api/health"
echo ""
echo "  Comandos útiles:"
echo "  Ver logs:    docker compose -f docker-compose.prod.yml logs -f"
echo "  Ver estado:  docker compose -f docker-compose.prod.yml ps"
echo "  Reiniciar:   docker compose -f docker-compose.prod.yml restart"
echo "  Detener:     docker compose -f docker-compose.prod.yml down"
echo "========================================================="
echo ""
