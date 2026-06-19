FROM php:8.2-cli

# Extensions the app needs at runtime:
#   intl   - CodeIgniter requirement
#   mysqli - database.default.DBDriver = MySQLi
#   gd     - webcam check-in/out photo handling
#   zip    - PhpSpreadsheet (.xlsx export)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" intl mysqli gd zip \
    && rm -rf /var/lib/apt/lists/*

# Composer (copied from the official image) for in-container dependency management
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Raise PHP upload limits so phone-camera photos (2-15 MB) are accepted
COPY docker/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /app
EXPOSE 8080

# CodeIgniter dev server, reachable from the host
CMD ["php", "spark", "serve", "--host", "0.0.0.0", "--port", "8080"]
