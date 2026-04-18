FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    unzip \
    git \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    intl \
    zip \
    gd \
    opcache

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

RUN mkdir -p /run/nginx /var/log/supervisor \
 && chown -R www-data:www-data /var/www/html \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
