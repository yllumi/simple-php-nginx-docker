# PHP-FPM dengan ekstensi Redis
FROM php:8.3-fpm

# Install ekstensi Redis via PECL
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        pkg-config \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*
