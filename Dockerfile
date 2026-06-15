# ============================================================
# IceFrame Inventory - Parte 1 - Dockerfile de Laravel
# Imagen PHP 8.4 con extensiones PostgreSQL (pdo_pgsql, pgsql).
# PHP 8.4 porque Laravel 13.3+ arrastra Symfony 8, que exige PHP >= 8.4.1.
# Sirve la app con el servidor embebido de PHP en el puerto 8000.
# OPcache activado para acelerar el arranque del framework en cada petición.
# ============================================================
FROM php:8.4-cli

# Dependencias del sistema y de las extensiones PHP (incluye opcache).
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libzip-dev \
        libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip mbstring bcmath opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# OPcache: cachea el bytecode PHP. enable_cli=1 porque el servidor embebido
# (artisan serve) corre bajo el SAPI cli; sin esto OPcache no actuaría.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=1'; \
        echo 'opcache.revalidate_freq=60'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Composer (copiado desde la imagen oficial).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Instalar dependencias primero (mejor cache de capas).
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist || true

# Copiar el resto del proyecto.
COPY . .

# Completar autoload y permisos de storage.
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

# El .env real se inyecta en runtime (no se copia al build).
# 1) Genera APP_KEY si no existe.
# 2) Cachea config/rutas/vistas AQUÍ (no en build) para que lean el .env de runtime
#    —incluida la IP Tailscale de la BD— y cada petición arranque más rápido.
#    Se usa ';' para que, si un paso falla, el servidor igual se levante.
# 3) Levanta el servidor accesible por la red Tailscale.
CMD ["sh", "-c", "php artisan key:generate --force --no-interaction 2>/dev/null; php artisan config:cache; php artisan route:cache; php artisan view:cache; php artisan serve --host=0.0.0.0 --port=8000"]