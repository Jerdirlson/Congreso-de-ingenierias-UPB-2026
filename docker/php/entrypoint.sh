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
if [ -n "${REDIS_PASSWORD}" ]; then
  sed -i "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=${REDIS_PASSWORD}|" "$APP_DIR/.env"
fi
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

# ── Mail SMTP (necesario antes de config:cache para que Laravel lo recoja) ───
# El .env.example apunta a mailpit:1025; sin esta inyección los correos no se
# envían en producción aunque docker-compose pase MAIL_* al contenedor.
if [ -n "${MAIL_HOST}" ]; then
  sed -i '/^MAIL_MAILER=/d;/^MAIL_HOST=/d;/^MAIL_PORT=/d;/^MAIL_USERNAME=/d;/^MAIL_PASSWORD=/d;/^MAIL_ENCRYPTION=/d;/^MAIL_FROM_ADDRESS=/d;/^MAIL_FROM_NAME=/d' "$APP_DIR/.env"
  {
    echo "MAIL_MAILER=\"${MAIL_MAILER:-smtp}\""
    echo "MAIL_HOST=\"${MAIL_HOST}\""
    echo "MAIL_PORT=\"${MAIL_PORT:-587}\""
    echo "MAIL_USERNAME=\"${MAIL_USERNAME}\""
    echo "MAIL_PASSWORD=\"${MAIL_PASSWORD}\""
    echo "MAIL_ENCRYPTION=\"${MAIL_ENCRYPTION:-tls}\""
    echo "MAIL_FROM_ADDRESS=\"${MAIL_FROM_ADDRESS}\""
    echo "MAIL_FROM_NAME=\"${MAIL_FROM_NAME:-Congreso Ingenierías 2026}\""
  } >> "$APP_DIR/.env"
fi

# ── App Key ───────────────────────────────────────────────────────────────────
if ! grep -q "APP_KEY=base64" "$APP_DIR/.env" 2>/dev/null; then
  php "$APP_DIR/artisan" key:generate --no-interaction
fi

# ── Vendor: el named volume cgr-vendor pisa el vendor del build de la imagen.
#    Reinstalar si: (a) vendor está vacío, o (b) composer.lock cambió vs lo instalado.
LOCK_FILE="$APP_DIR/composer.lock"
LOCK_MARKER="$APP_DIR/vendor/.composer-lock.sha256"
NEEDS_INSTALL=false
REASON=""
if [ ! -f "$APP_DIR/vendor/autoload.php" ]; then
  NEEDS_INSTALL=true
  REASON="vendor/ vacío"
elif [ -f "$LOCK_FILE" ]; then
  CURRENT_HASH=$(sha256sum "$LOCK_FILE" | awk '{print $1}')
  INSTALLED_HASH=$(cat "$LOCK_MARKER" 2>/dev/null || echo "")
  if [ "$CURRENT_HASH" != "$INSTALLED_HASH" ]; then
    NEEDS_INSTALL=true
    REASON="composer.lock cambió desde la última instalación"
  fi
fi
if [ "$NEEDS_INSTALL" = "true" ]; then
  echo "📦  ${REASON} — running composer install..."
  cd "$APP_DIR" && composer install --no-scripts --no-interaction --prefer-dist
  [ -f "$LOCK_FILE" ] && sha256sum "$LOCK_FILE" | awk '{print $1}' > "$LOCK_MARKER"
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

# ── Migraciones (solo si RUN_MIGRATIONS != false) ─────────────────────────────
# El queue-worker usa el mismo entrypoint pero no debe migrar (race condition).
cd "$APP_DIR"
if [ "${RUN_MIGRATIONS:-true}" = "false" ]; then
  echo "⏭️  Migraciones omitidas (RUN_MIGRATIONS=false)"
elif [ "$APP_ENV" = "production" ]; then
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
