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
COPY --from=vendor /app/vendor/tightenco/ziggy vendor/tightenco/ziggy
COPY vite.config.js tailwind.config.js* postcss.config.js* ./
RUN npm run build

# --- Development ---
FROM php:8.4-cli-alpine AS development

RUN apk add --no-cache \
        linux-headers \
        $PHPIZE_DEPS \
        openssl-dev \
        curl-dev \
        git \
    && docker-php-ext-install pdo_mysql opcache bcmath pcntl sockets \
    && git clone --depth 1 https://github.com/swoole/swoole-src.git /tmp/swoole \
    && cd /tmp/swoole && phpize && ./configure --enable-openssl --enable-swoole-curl && make -j$(nproc) && make install \
    && rm -rf /tmp/swoole \
    && cd / && git clone --depth 1 https://github.com/phpredis/phpredis.git /tmp/phpredis \
    && cd /tmp/phpredis && phpize && ./configure && make -j$(nproc) && make install \
    && rm -rf /tmp/phpredis \
    && cd / && git clone --depth 1 https://github.com/xdebug/xdebug.git /tmp/xdebug \
    && cd /tmp/xdebug && phpize && ./configure && make -j$(nproc) && make install \
    && rm -rf /tmp/xdebug \
    && docker-php-ext-enable swoole redis xdebug \
    && apk del $PHPIZE_DEPS git \
    && apk add --no-cache mysql-client nodejs npm unzip

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
RUN echo "opcache.jit=disable" >> /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/dev-entrypoint.sh /usr/local/bin/dev-entrypoint.sh
RUN chmod +x /usr/local/bin/dev-entrypoint.sh

WORKDIR /app
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

EXPOSE 8000 9003

ENTRYPOINT ["dev-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

