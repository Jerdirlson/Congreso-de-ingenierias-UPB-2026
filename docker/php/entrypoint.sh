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

# ── Storage link ──────────────────────────────────────────────────────────────
[ ! -L "$APP_DIR/public/storage" ] && php "$APP_DIR/artisan" storage:link --no-interaction 2>/dev/null || true

# ── Permisos ──────────────────────────────────────────────────────────────────
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

# ── Migraciones ───────────────────────────────────────────────────────────────
cd "$APP_DIR"
SEED_MARKER="$APP_DIR/.docker-seeded"
if [ ! -f "$SEED_MARKER" ]; then
  php artisan migrate --force --seed --no-interaction
  touch "$SEED_MARKER"
else
  php artisan migrate --force --no-interaction 2>&1 || true
fi

# ── Limpiar cache ─────────────────────────────────────────────────────────────
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo ""
echo "============================================"
echo "  Backend listo"
echo "  API   → http://localhost:8000"
echo "  Health→ GET /api/health"
echo "  Admin → http://localhost:8080 (phpMyAdmin)"
echo "============================================"
echo ""

exec "$@"
