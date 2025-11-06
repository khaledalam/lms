FROM php:8.3-fpm-alpine

# System & build deps
RUN apk add --no-cache \
    git curl bash shadow \
    icu-dev zlib-dev libzip-dev oniguruma-dev \
    sqlite-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    $PHPIZE_DEPS \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" \
    intl mbstring zip gd \
    pdo_sqlite

# enable PCNTL (needed by Horizon)
RUN docker-php-ext-install pcntl

# install + enable Redis ext (Horizon uses Redis queues)
RUN apk add --no-cache $PHPIZE_DEPS && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Cache composer layer
COPY composer.json composer.lock* ./
RUN composer install --no-scripts --no-dev --prefer-dist --no-interaction || true

# App files & perms
COPY . .
RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]