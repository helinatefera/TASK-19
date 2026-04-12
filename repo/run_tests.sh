#!/usr/bin/env bash
set -euo pipefail

# =============================================================================
# CivicCrowd Test Runner
#
# Two execution modes:
#   1. Docker mode – if the civiccrowd containers are already running, tests
#      execute inside them (PHP 8.4, no host dependencies beyond Docker).
#   2. Host mode – runs directly on the host with a local PHP >= 8.3.
#      Self-bootstrapping: installs PHP via Homebrew if absent, uses SQLite
#      in-memory databases, requires zero external services.
#
# The script auto-detects which mode to use.
# =============================================================================

ROOT="$(cd "$(dirname "$0")" && pwd)"
BACKEND="$ROOT/backend"
FRONTEND="$ROOT/frontend"
PASS=0
FAIL=0
SUITES=0

BACKEND_CONTAINER="civiccrowd_backend_app"
FRONTEND_CONTAINER="civiccrowd_frontend_app"

# ===================================================================
# Docker mode helpers
# ===================================================================
docker_containers_running() {
    command -v docker &>/dev/null || return 1
    docker inspect -f '{{.State.Running}}' "$BACKEND_CONTAINER" 2>/dev/null | grep -q true || return 1
    docker inspect -f '{{.State.Running}}' "$FRONTEND_CONTAINER" 2>/dev/null | grep -q true || return 1
    return 0
}

wait_for_container() {
    local ctr="$1"
    local max=300
    local elapsed=0
    echo "[Setup] Waiting for $ctr to be ready (entrypoint + composer install)..."
    while [ $elapsed -lt $max ]; do
        # Check that the app can actually bootstrap – this means the
        # entrypoint has finished composer install, migrations, etc.
        if docker exec "$ctr" php artisan --version &>/dev/null; then
            echo "[Setup] $ctr is ready."
            return 0
        fi
        sleep 5
        elapsed=$((elapsed + 5))
    done
    echo "[WARN] Timed out after ${max}s waiting for $ctr."
}

run_tests_docker() {
    echo "[Mode] Docker – running tests inside containers"
    echo ""

    # Stop queue and scheduler containers – their entrypoints re-seed the
    # database on restart, which conflicts with the test suite's
    # migrate:fresh.  They are not needed during testing.
    echo "[Setup] Stopping background workers to avoid DB conflicts..."
    docker stop civiccrowd_backend_queue civiccrowd_backend_scheduler 2>/dev/null || true
    echo ""

    wait_for_container "$BACKEND_CONTAINER"

    # ---- Backend Unit Tests ----
    echo "============================================"
    echo "  Running Backend Unit Tests"
    echo "============================================"
    echo ""
    SUITES=$((SUITES + 1))

    if docker exec "$BACKEND_CONTAINER" php artisan test --testsuite=unit_tests 2>&1; then
        echo ""
        echo "[PASS] Backend unit tests passed."
        PASS=$((PASS + 1))
    else
        echo ""
        echo "[FAIL] Backend unit tests failed."
        FAIL=$((FAIL + 1))
    fi
    echo ""

    # ---- Backend API Tests ----
    echo "============================================"
    echo "  Running Backend API Tests"
    echo "============================================"
    echo ""
    SUITES=$((SUITES + 1))

    if docker exec "$BACKEND_CONTAINER" php artisan test --testsuite=api_tests 2>&1; then
        echo ""
        echo "[PASS] Backend API tests passed."
        PASS=$((PASS + 1))
    else
        echo ""
        echo "[FAIL] Backend API tests failed."
        FAIL=$((FAIL + 1))
    fi
    echo ""

    # ---- Frontend Tests ----
    echo "============================================"
    echo "  Running Frontend Tests"
    echo "============================================"
    echo ""
    SUITES=$((SUITES + 1))

    wait_for_container "$FRONTEND_CONTAINER"

    if docker exec "$FRONTEND_CONTAINER" php artisan test 2>&1; then
        echo ""
        echo "[PASS] Frontend tests passed."
        PASS=$((PASS + 1))
    else
        echo ""
        echo "[FAIL] Frontend tests failed."
        FAIL=$((FAIL + 1))
    fi
    echo ""
}

# ===================================================================
# Host mode helpers
# ===================================================================
PHP=""
COMPOSER=""

find_php() {
    for candidate in php8.4 php8.3 php; do
        local p
        p="$(command -v "$candidate" 2>/dev/null || true)"
        if [ -n "$p" ]; then
            local major minor
            major="$("$p" -r 'echo PHP_MAJOR_VERSION;' 2>/dev/null || echo 0)"
            minor="$("$p" -r 'echo PHP_MINOR_VERSION;' 2>/dev/null || echo 0)"
            if [ "$major" -gt 8 ] || { [ "$major" -eq 8 ] && [ "$minor" -ge 3 ]; }; then
                PHP="$p"
                return 0
            fi
        fi
    done

    # Check Homebrew keg-only paths
    for keg in /opt/homebrew/opt/php@8.4/bin/php /opt/homebrew/opt/php@8.3/bin/php \
               /usr/local/opt/php@8.4/bin/php /usr/local/opt/php@8.3/bin/php \
               /opt/homebrew/opt/php/bin/php /usr/local/opt/php/bin/php; do
        if [ -x "$keg" ]; then
            local major minor
            major="$("$keg" -r 'echo PHP_MAJOR_VERSION;' 2>/dev/null || echo 0)"
            minor="$("$keg" -r 'echo PHP_MINOR_VERSION;' 2>/dev/null || echo 0)"
            if [ "$major" -gt 8 ] || { [ "$major" -eq 8 ] && [ "$minor" -ge 3 ]; }; then
                PHP="$keg"
                return 0
            fi
        fi
    done

    return 1
}

install_php() {
    echo "[Bootstrap] PHP >= 8.3 not found. Attempting to install php@8.4 via Homebrew..."
    if ! command -v brew &>/dev/null; then
        echo "[FATAL] PHP >= 8.3 is required but not found, and Homebrew is not"
        echo "        available to auto-install it."
        exit 1
    fi
    brew install php@8.4 2>&1 | tail -5
    find_php || {
        echo "[FATAL] PHP installation succeeded but a suitable binary (>= 8.3) was not found."
        exit 1
    }
}

find_composer() {
    if command -v composer &>/dev/null; then
        COMPOSER="composer"
        return 0
    fi
    for loc in /opt/homebrew/bin/composer /usr/local/bin/composer "$HOME/.composer/vendor/bin/composer"; do
        if [ -x "$loc" ]; then
            COMPOSER="$loc"
            return 0
        fi
    done
    if [ -f "$ROOT/.composer.phar" ]; then
        COMPOSER="$PHP $ROOT/.composer.phar"
        return 0
    fi
    return 1
}

install_composer() {
    echo "[Bootstrap] Composer not found. Downloading composer.phar..."
    "$PHP" -r "copy('https://getcomposer.org/installer', '$ROOT/.composer-setup.php');"
    "$PHP" "$ROOT/.composer-setup.php" --install-dir="$ROOT" --filename=".composer.phar" --quiet
    rm -f "$ROOT/.composer-setup.php"
    COMPOSER="$PHP $ROOT/.composer.phar"
}

generate_env() {
    local dir="$1"
    local app_name="$2"
    local key="base64:$(head -c 32 /dev/urandom | base64)"

    cat > "$dir/.env" <<ENVEOF
APP_NAME=${app_name}
APP_ENV=testing
APP_KEY=${key}
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
LOG_CHANNEL=stderr
LOG_LEVEL=debug
BROADCAST_CONNECTION=null
ENVEOF
}

prepare_project() {
    local dir="$1"
    local app_name="${2:-Laravel}"

    mkdir -p "$dir/bootstrap/cache" \
             "$dir/storage/logs" \
             "$dir/storage/framework/sessions" \
             "$dir/storage/framework/views" \
             "$dir/storage/framework/cache"

    generate_env "$dir" "$app_name"

    echo "[Setup] Installing dependencies in $(basename "$dir")..."
    (cd "$dir" && $COMPOSER install --no-interaction --ignore-platform-reqs 2>&1) || {
        echo "[FATAL] Composer install failed in $(basename "$dir"). See output above."
        exit 1
    }
}

run_tests_host() {
    echo "[Mode] Host – running tests with local PHP"
    echo ""

    if ! find_php; then
        install_php
    fi
    echo "[Setup] Using PHP: $PHP ($($PHP -r 'echo phpversion();'))"

    if ! find_composer; then
        install_composer
    fi
    echo "[Setup] Using Composer: $COMPOSER"
    echo ""

    # Symlink shared test directories into backend
    for dir_name in API_tests unit_tests; do
        if [ -d "$ROOT/$dir_name" ]; then
            rm -rf "$BACKEND/$dir_name"
            ln -s "$ROOT/$dir_name" "$BACKEND/$dir_name"
        fi
    done

    prepare_project "$BACKEND" "CivicCrowd-Backend"
    echo ""

    # ---- Backend Unit Tests ----
    echo "============================================"
    echo "  Running Backend Unit Tests"
    echo "============================================"
    echo ""
    SUITES=$((SUITES + 1))

    if (cd "$BACKEND" && $PHP artisan test --testsuite=unit_tests 2>&1); then
        echo ""
        echo "[PASS] Backend unit tests passed."
        PASS=$((PASS + 1))
    else
        echo ""
        echo "[FAIL] Backend unit tests failed."
        FAIL=$((FAIL + 1))
    fi
    echo ""

    # ---- Backend API Tests ----
    echo "============================================"
    echo "  Running Backend API Tests"
    echo "============================================"
    echo ""
    SUITES=$((SUITES + 1))

    if (cd "$BACKEND" && $PHP artisan test --testsuite=api_tests 2>&1); then
        echo ""
        echo "[PASS] Backend API tests passed."
        PASS=$((PASS + 1))
    else
        echo ""
        echo "[FAIL] Backend API tests failed."
        FAIL=$((FAIL + 1))
    fi
    echo ""

    # ---- Frontend Tests ----
    echo "============================================"
    echo "  Running Frontend Tests"
    echo "============================================"
    echo ""
    SUITES=$((SUITES + 1))

    prepare_project "$FRONTEND" "CivicCrowd"

    if (cd "$FRONTEND" && $PHP artisan test 2>&1); then
        echo ""
        echo "[PASS] Frontend tests passed."
        PASS=$((PASS + 1))
    else
        echo ""
        echo "[FAIL] Frontend tests failed."
        FAIL=$((FAIL + 1))
    fi
    echo ""
}

# ===================================================================
#  Main
# ===================================================================
echo "============================================"
echo "  CivicCrowd Test Runner"
echo "============================================"
echo ""

if docker_containers_running; then
    run_tests_docker
elif find_php; then
    run_tests_host
elif docker_containers_running; then
    # Shouldn't reach here but just in case
    run_tests_docker
else
    # No suitable PHP and no Docker – try installing
    install_php
    run_tests_host
fi

# ===================================================================
#  Summary
# ===================================================================
echo "============================================"
echo "  Test Summary"
echo "============================================"
echo "  Suites run:     $SUITES"
echo "  Suites passed:  $PASS"
echo "  Suites failed:  $FAIL"
echo "============================================"

if [ "$FAIL" -gt 0 ]; then
    echo ""
    echo "RESULT: FAIL"
    exit 1
else
    echo ""
    echo "RESULT: PASS"
    exit 0
fi
