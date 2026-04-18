# syntax=docker/dockerfile:1.7

FROM php:8.3-fpm-alpine

ARG APP_DIR=/var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_CACHE_DIR=/tmp/composer/cache

WORKDIR ${APP_DIR}

# Runtime deps + build deps untuk compile extension PHP
RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        curl \
        git \
        unzip \
        icu-libs \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        oniguruma-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        bcmath \
        intl \
        zip \
        gd \
        opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/*

# Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy file dependency dulu agar layer composer tetap cacheable
COPY composer.json composer.lock ./

# Install dependency PHP.
# Layer ini hanya pecah kalau composer.json / composer.lock berubah.
RUN --mount=type=cache,target=/tmp/composer/cache \
    composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --optimize-autoloader

# Baru copy source code aplikasi
COPY . .

# Konfigurasi web/proses
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh \
    && mkdir -p \
        /run/nginx \
        /var/log/supervisor \
        ${APP_DIR}/storage \
        ${APP_DIR}/bootstrap/cache \
    && chown -R www-data:www-data ${APP_DIR} \
    && chmod -R ug+rwx ${APP_DIR}/storage ${APP_DIR}/bootstrap/cache \
    && composer dump-autoload --optimize

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]