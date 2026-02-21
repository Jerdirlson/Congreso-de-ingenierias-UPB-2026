#!/bin/sh
set -e

APP_DIR="/var/www/html"

# ── Esperar MySQL ─────────────────────────────────────────────────────────────
echo "⏳  Waiting for MySQL..."
RETRIES=30
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
  RETRIES=$((RETRIES - 1))
  [ $RETRIES -eq 0 ] && echo "❌  MySQL timeout." && exit 1
  echo "   ...retrying ($RETRIES left)"
  sleep 3
done
echo "✅  MySQL ready."

# ── .env ─────────────────────────────────────────────────────────────────────
if [ ! -f "$APP_DIR/.env" ]; then
  cp "$APP_DIR/.env.example" "$APP_DIR/.env"
fi

# Inyectar variables de entorno Docker en .env
sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|"                   "$APP_DIR/.env"
sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=${DB_CONNECTION}|" "$APP_DIR/.env"
sed -i "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|"                   "$APP_DIR/.env"
sed -i "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|"                   "$APP_DIR/.env"
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|"       "$APP_DIR/.env"
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|"       "$APP_DIR/.env"
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|"       "$APP_DIR/.env"
sed -i "s|^REDIS_HOST=.*|REDIS_HOST=${REDIS_HOST}|"         "$APP_DIR/.env"
sed -i "s|^REDIS_PORT=.*|REDIS_PORT=${REDIS_PORT}|"         "$APP_DIR/.env"
sed -i "s|^CACHE_STORE=.*|CACHE_STORE=${CACHE_DRIVER:-redis}|" "$APP_DIR/.env"
sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=${SESSION_DRIVER:-redis}|" "$APP_DIR/.env"
sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}|" "$APP_DIR/.env"
if [ -n "${SANCTUM_STATEFUL_DOMAINS}" ]; then
  sed -i "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS}|" "$APP_DIR/.env" 2>/dev/null || true
fi
if [ -n "${APP_ENV}" ]; then
  sed -i "s|^APP_ENV=.*|APP_ENV=${APP_ENV}|"     "$APP_DIR/.env"
  sed -i "s|^APP_DEBUG=.*|APP_DEBUG=${APP_DEBUG:-false}|" "$APP_DIR/.env"
fi

# ── App Key ───────────────────────────────────────────────────────────────────
if ! grep -q "APP_KEY=base64" "$APP_DIR/.env" 2>/dev/null; then
  php "$APP_DIR/artisan" key:generate --no-interaction
fi

# ── Vendor: el build instaló los paquetes en la imagen.
#    El named volume cgr-vendor los preserva sobre el bind mount.
#    Solo hacemos composer install si vendor está vacío (primera vez con nuevo volume).
if [ ! -f "$APP_DIR/vendor/autoload.php" ]; then
  echo "📦  vendor/ empty — running composer install..."
  cd "$APP_DIR" && composer install --no-scripts --no-interaction --prefer-dist
fi

# ── Publicar vendor assets (Sanctum, Spatie, etc.) ───────────────────────────
MARKER="$APP_DIR/.docker-published"
if [ ! -f "$MARKER" ]; then
  cd "$APP_DIR"
  php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --no-interaction 2>/dev/null || true
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --no-interaction 2>/dev/null || true
  php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag=medialibrary-migrations --no-interaction 2>/dev/null || true
  touch "$MARKER"
fi

# ── Directorios de storage (necesarios antes de config:cache) ─────────────────
mkdir -p "$APP_DIR/storage/framework/views" \
         "$APP_DIR/storage/framework/cache/data" \
         "$APP_DIR/storage/framework/sessions" \
         "$APP_DIR/storage/framework/testing" \
         "$APP_DIR/storage/logs" \
         "$APP_DIR/storage/app/public" \
         "$APP_DIR/bootstrap/cache"

# ── Storage link ──────────────────────────────────────────────────────────────
[ ! -L "$APP_DIR/public/storage" ] && php "$APP_DIR/artisan" storage:link --no-interaction 2>/dev/null || true

# ── Permisos ──────────────────────────────────────────────────────────────────
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

# ── Migraciones ───────────────────────────────────────────────────────────────
cd "$APP_DIR"
if [ "$APP_ENV" = "production" ]; then
  # Producción: solo migrar, nunca sembrar datos de prueba
  php artisan migrate --force --no-interaction 2>&1 || true
else
  # Desarrollo: sembrar solo en el primer arranque
  SEED_MARKER="$APP_DIR/.docker-seeded"
  if [ ! -f "$SEED_MARKER" ]; then
    php artisan migrate --force --seed --no-interaction
    touch "$SEED_MARKER"
  else
    php artisan migrate --force --no-interaction 2>&1 || true
  fi
fi

# ── Cache de configuración ────────────────────────────────────────────────────
if [ "$APP_ENV" = "production" ]; then
  # Producción: cachear config/rutas (sin view:cache, es API sin vistas Blade)
  php artisan config:cache
  php artisan route:cache
else
  # Desarrollo: limpiar cache para hot-reload
  php artisan config:clear
  php artisan route:clear
  php artisan cache:clear
fi

echo ""
echo "============================================"
echo "  Backend listo"
echo "  API   → http://localhost:8000"
echo "  Health→ GET /api/health"
echo "  Admin → http://localhost:8080 (phpMyAdmin)"
echo "============================================"
echo ""

exec "$@"
