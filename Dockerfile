FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git curl libpng-dev libxml2-dev zip unzip oniguruma-dev \
    icu-dev freetype-dev libjpeg-turbo-dev libzip-dev linux-headers $PHPIZE_DEPS

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 9000
CMD ["php-fpm"]
