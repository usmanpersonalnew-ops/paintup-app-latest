# Multi-stage Dockerfile for Laravel + Vue.js application
# Stage 1: Build - PHP dependencies (must run before frontend for Ziggy)
# Stage 2: Build - Frontend assets
# Stage 3: Build - Application preparation
# Stage 4: Upload - Optimized image for registry
# Stage 5: Deploy - Runtime image

# ============================================================================
# STAGE 1: Build - PHP Dependencies
# ============================================================================
FROM php:8.3-fpm-alpine AS build-composer

WORKDIR /app

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    sqlite-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    && apk add --no-cache \
    libpng \
    libzip \
    oniguruma \
    sqlite-libs \
    freetype \
    libjpeg-turbo

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_sqlite \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

RUN apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-security-blocking

# ============================================================================
# STAGE 2: Build - Frontend Assets
# ============================================================================
FROM node:20-alpine AS build-frontend

WORKDIR /app

COPY package*.json ./

RUN npm ci --only=production=false

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
COPY --from=build-composer /app/vendor/tightenco/ziggy ./vendor/tightenco/ziggy

RUN npm run build

# ============================================================================
# STAGE 3: Build - Application Preparation
# ============================================================================
FROM php:8.3-fpm-alpine AS build-app

WORKDIR /app

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    sqlite-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    && apk add --no-cache \
    libpng \
    libzip \
    oniguruma \
    sqlite-libs \
    freetype \
    libjpeg-turbo

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_sqlite \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

RUN apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY --from=build-composer /app/vendor ./vendor

COPY --from=build-frontend /app/public/build ./public/build

COPY . .

RUN composer dump-autoload --optimize --no-dev --no-scripts

RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 775 /app/storage \
    && chmod -R 775 /app/bootstrap/cache

# ============================================================================
# STAGE 4: Upload - Optimized Image for Registry
# ============================================================================
FROM php:8.3-fpm-alpine AS upload

WORKDIR /var/www/html

RUN apk add --no-cache \
    libpng \
    libzip \
    oniguruma \
    sqlite-libs \
    freetype \
    libjpeg-turbo \
    nginx \
    supervisor

RUN apk add --no-cache --virtual .php-build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    sqlite-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_sqlite \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    && apk del .php-build-deps

COPY --from=build-app /app /var/www/html

RUN chown -R www-data:www-data /var/www/html

# ============================================================================
# STAGE 5: Deploy - Runtime Image
# ============================================================================
FROM upload AS deploy

WORKDIR /var/www/html

COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY docker/nginx/conf.d/default.conf /etc/nginx/http.d/default.conf

RUN mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data /var/log/supervisor

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
