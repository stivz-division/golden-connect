#!/bin/sh
set -e

if [ ! -f /app/vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction
fi

if [ "${INSTALL_NODE_DEPS:-true}" = "true" ]; then
    if [ ! -f /app/node_modules/.package-lock.json ] || \
       ! diff -q /app/package-lock.json /app/node_modules/.package-lock.json > /dev/null 2>&1; then
        echo "Installing NPM dependencies..."
        npm install
    fi
fi

if grep -q "^APP_KEY=$" /app/.env 2>/dev/null; then
    echo "Generating APP_KEY..."
    php artisan key:generate
fi

exec "$@"