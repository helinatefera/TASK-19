#!/bin/sh
set -e

# Ensure required Laravel directories exist (bind mount may override Dockerfile-created dirs)
mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache

# Generate APP_KEY if not provided (required for encryption/sessions)
if [ -z "${APP_KEY}" ]; then
    export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    echo "Generated APP_KEY (no key was provided)."
fi

# Ensure .env exists so Laravel bootstrap does not emit file_get_contents warnings.
if [ ! -f .env ]; then
    echo "APP_KEY=${APP_KEY}" > .env
fi

if [ ! -d "vendor" ]; then
    composer install --no-interaction --no-dev --optimize-autoloader
fi

chown -R appuser:appuser storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

php artisan config:clear
php artisan view:clear

exec "$@"
