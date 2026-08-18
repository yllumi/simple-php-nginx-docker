# PHP-FPM dengan ekstensi Redis & PDO MySQL
FROM php:8.3-fpm

# Install ekstensi Redis via PECL dan PDO MySQL untuk MariaDB
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        pkg-config \
    && docker-php-ext-install pdo_mysql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*
