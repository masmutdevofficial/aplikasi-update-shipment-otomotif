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
    curl \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    intl \
    zip \
    gd \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
 && chown -R www-data:www-data /var/www/html \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
 && mkdir -p /run/nginx /var/log/supervisor

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]