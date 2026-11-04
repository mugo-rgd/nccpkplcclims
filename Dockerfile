# ================================================================
# Stage 1: Frontend assets
# ================================================================

FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./

RUN node --version \
    && npm --version \
    && npm ci --no-audit --no-fund --loglevel verbose

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

RUN npm run build


# ================================================================
# Stage 2: PHP base with required extensions
# ================================================================

FROM php:8.4-fpm-bookworm AS php_base

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    libmagickwand-dev \
    libssl-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    soap \
    zip

RUN pecl install imagick \
    && docker-php-ext-enable imagick


# ================================================================
# Stage 3: Composer dependencies
# ================================================================

FROM php_base AS composer_dependencies

COPY --from=composer:2 \
    /usr/bin/composer \
    /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader


# ================================================================
# Stage 4: Production Laravel application
# ================================================================

FROM php_base AS app

WORKDIR /var/www/html

RUN mv "$PHP_INI_DIR/php.ini-production" \
    "$PHP_INI_DIR/php.ini"

COPY docker/php/production.ini \
    $PHP_INI_DIR/conf.d/99-meterlab.ini

COPY . /var/www/html

COPY --from=composer_dependencies \
    /app/vendor \
    /var/www/html/vendor

COPY --from=frontend \
    /app/public/build \
    /var/www/html/public/build

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache

RUN rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi

EXPOSE 9000

CMD ["php-fpm"]
