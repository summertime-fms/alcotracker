FROM php:8.2-fpm

# Устанавливаем зависимости
RUN apt-get update && apt-get install -y \
    git zip unzip curl libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP-FPM: ограничение воркеров под 2 GB RAM
COPY www.conf /usr/local/etc/php-fpm.d/zz-custom.conf

WORKDIR /var/www/html
