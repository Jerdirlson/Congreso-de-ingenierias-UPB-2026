FROM php:8.3-fpm

# ── System dependencies ───────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    zip unzip libzip-dev libpq-dev \
    && docker-php-ext-install \
        pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── Redis PHP extension ───────────────────────────────────────────────────────
RUN pecl install redis && docker-php-ext-enable redis

# ── Composer 2 ────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── PHP config ────────────────────────────────────────────────────────────────
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# ── Pre-install PHP dependencies durante el BUILD ─────────────────────────────
# Build context = raíz del proyecto (ver docker-compose.yml)
# Solo se copian composer.json/lock para aprovechar el cache de capas.
# El vendor/ resultante vive en una named volume (cgr-vendor) que persiste
# cuando ./backend se monta como volume de desarrollo.
WORKDIR /var/www/html

COPY backend/composer.json ./composer.json
# composer.lock puede no existir aún en repos nuevos → ignorar si falta
COPY backend/composer.loc[k] ./

RUN composer install \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
    || composer update \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader

# ── Entrypoint ────────────────────────────────────────────────────────────────
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
