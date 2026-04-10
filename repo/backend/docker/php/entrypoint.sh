#!/bin/sh
set -e

cd /var/www/html

# Generate APP_KEY if not provided (required for encryption/sessions)
if [ -z "${APP_KEY}" ]; then
    export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    echo "Generated APP_KEY (no key was provided)."
fi

# Ensure .env exists so Laravel bootstrap does not emit file_get_contents warnings.
# Actual configuration comes from Docker environment variables which take precedence.
if [ ! -f .env ]; then
    echo "APP_KEY=${APP_KEY}" > .env
fi

# Always ensure dependencies are installed and autoloader is fresh
if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --optimize-autoloader 2>&1
else
    echo "Regenerating autoloader..."
    composer dump-autoload --no-interaction --optimize 2>&1
fi

# Fix permissions
chown -R appuser:appgroup storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Wait for database if DB_HOST is set
if [ -n "${DB_HOST:-}" ]; then
    echo "Waiting for PostgreSQL at ${DB_HOST}..."
    MAX_TRIES=30
    TRIES=0
    until php -r "try { new PDO('pgsql:host=${DB_HOST};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-civiccrowd}', '${DB_USERNAME:-civiccrowd}', '${DB_PASSWORD:-civiccrowd_secret}'); echo 'ok'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; do
        TRIES=$((TRIES + 1))
        if [ "$TRIES" -ge "$MAX_TRIES" ]; then
            echo "WARNING: PostgreSQL did not become ready after $MAX_TRIES attempts."
            break
        fi
        sleep 2
    done
    echo "PostgreSQL ready."

    # Auto-migrate only in local/development mode. In production, migrations are
    # run explicitly as a deployment step. In testing, the test runner handles it.
    if [ "${APP_ENV:-production}" = "local" ]; then
        php artisan migrate --force 2>&1 || true
        php artisan db:seed --force 2>&1 || true
    fi
fi

# Clear any stale caches
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

echo "Application ready."
exec "$@"
