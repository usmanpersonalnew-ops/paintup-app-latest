# Multi-stage Dockerfile for Laravel + Vue.js application
# Stage 1: Build - Frontend assets
# Stage 2: Build - PHP dependencies
# Stage 3: Build - Application preparation
# Stage 4: Upload - Optimized image for registry
# Stage 5: Deploy - Runtime image

# ============================================================================
# STAGE 1: Build - Frontend Assets
# ============================================================================
FROM node:20-alpine AS build-frontend

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install Node.js dependencies
RUN npm ci --only=production=false

# Copy source files needed for build
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Build assets
RUN npm run build

# ============================================================================
# STAGE 2: Build - PHP Dependencies
# ============================================================================
FROM php:8.3-fpm-alpine AS build-composer

WORKDIR /app

# Install system dependencies and build tools
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

# Install PHP extensions
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

# Clean up build dependencies
RUN apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer*.json ./

# Install PHP dependencies (no dev dependencies for production)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --optimize-autoloader

# ============================================================================
# STAGE 3: Build - Application Preparation
# ============================================================================
FROM php:8.3-fpm-alpine AS build-app

WORKDIR /app

# Install system dependencies and build tools
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

# Install PHP extensions
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

# Clean up build dependencies
RUN apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy installed Composer dependencies
COPY --from=build-composer /app/vendor ./vendor

# Copy built assets from frontend stage
COPY --from=build-frontend /app/public/build ./public/build

# Copy application files
COPY . .

# Generate Composer autoloader
RUN composer dump-autoload --optimize --no-dev --no-scripts

# Set proper permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 775 /app/storage \
    && chmod -R 775 /app/bootstrap/cache

# Create storage directories if they don't exist
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

# ============================================================================
# STAGE 4: Upload - Optimized Image for Registry
# ============================================================================
FROM php:8.3-fpm-alpine AS upload

WORKDIR /var/www/html

# Install runtime dependencies only
RUN apk add --no-cache \
    libpng \
    libzip \
    oniguruma \
    sqlite-libs \
    freetype \
    libjpeg-turbo \
    nginx \
    supervisor

# Install PHP extensions (runtime only, no build deps)
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

# Copy application from build stage
COPY --from=build-app /app /var/www/html

# Set proper ownership
RUN chown -R www-data:www-data /var/www/html

# ============================================================================
# STAGE 5: Deploy - Runtime Image
# ============================================================================
FROM upload AS deploy

WORKDIR /var/www/html

# Copy PHP-FPM configuration
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy nginx configuration (if needed)
COPY docker/nginx/conf.d/default.conf /etc/nginx/http.d/default.conf

# Create supervisor log directory
RUN mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data /var/log/supervisor

# Ensure proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
