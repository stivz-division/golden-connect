# syntax=docker/dockerfile:1

# --- Vendor (production dependencies) ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader --no-interaction --ignore-platform-reqs

# --- Node build (frontend assets) ---
FROM node:22-alpine AS node-builder
WORKDIR /app

ARG VITE_APP_NAME=GoldenConnect
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT=8080
ARG VITE_REVERB_SCHEME=https

ENV VITE_APP_NAME=${VITE_APP_NAME} \
    VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY} \
    VITE_REVERB_HOST=${VITE_REVERB_HOST} \
    VITE_REVERB_PORT=${VITE_REVERB_PORT} \
    VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME}

COPY package.json package-lock.json* ./
RUN --mount=type=cache,target=/root/.npm \
    npm ci
COPY resources/ resources/
COPY vite.config.js tailwind.config.js* postcss.config.js* ./
RUN npm run build

# --- Development ---
FROM php:8.4-cli-alpine AS development

RUN apk add --no-cache \
        linux-headers \
        $PHPIZE_DEPS \
        openssl-dev \
        curl-dev \
        mysql-client \
        nodejs \
        npm \
        unzip \
    && docker-php-ext-install pdo_mysql opcache bcmath pcntl sockets \
    && pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="no" enable-mysqlnd="no" enable-swoole-curl="yes" enable-cares="no" enable-brotli="no"' swoole \
    && pecl install redis xdebug \
    && docker-php-ext-enable swoole redis xdebug \
    && apk del $PHPIZE_DEPS

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
RUN echo "opcache.jit=disable" >> /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/dev-entrypoint.sh /usr/local/bin/dev-entrypoint.sh
RUN chmod +x /usr/local/bin/dev-entrypoint.sh

WORKDIR /app
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

EXPOSE 8000 9003

ENTRYPOINT ["dev-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# --- Production ---
FROM php:8.4-cli-alpine AS production

RUN apk add --no-cache linux-headers $PHPIZE_DEPS openssl-dev curl-dev \
    && docker-php-ext-install pdo_mysql opcache bcmath pcntl sockets \
    && pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="no" enable-mysqlnd="no" enable-swoole-curl="yes" enable-cares="no" enable-brotli="no"' swoole \
    && pecl install redis \
    && docker-php-ext-enable swoole redis \
    && apk del $PHPIZE_DEPS linux-headers

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php-production.ini /usr/local/etc/php/conf.d/php-production.ini

WORKDIR /app

RUN addgroup -g 1001 -S appuser && \
    adduser -S -u 1001 -G appuser appuser

COPY --from=vendor --chown=appuser:appuser /app/vendor ./vendor
COPY --chown=appuser:appuser . .
COPY --from=node-builder --chown=appuser:appuser /app/public/build ./public/build
COPY docker/php/prod-entrypoint.sh /usr/local/bin/prod-entrypoint.sh
RUN chmod +x /usr/local/bin/prod-entrypoint.sh

RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

USER appuser

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD php artisan octane:status || exit 1

ENTRYPOINT ["prod-entrypoint.sh"]
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]

# --- Nginx ---
FROM nginx:1.27-alpine AS nginx

COPY --from=node-builder /app/public/build /app/public/build
