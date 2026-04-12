#!/usr/bin/env bash
set -euo pipefail

# =============================================================================
# CivicCrowd Test Runner
#
# Self-bootstrapping: installs PHP via Homebrew if absent, uses SQLite
# in-memory databases, requires zero external services (no Docker, no
# PostgreSQL, no network after initial setup).
# =============================================================================

ROOT="$(cd "$(dirname "$0")" && pwd)"
BACKEND="$ROOT/backend"
FRONTEND="$ROOT/frontend"
PASS=0
FAIL=0
SUITES=0

PHP=""
COMPOSER=""

# -------------------------------------------------------------------
# Detect or install PHP
# -------------------------------------------------------------------
find_php() {
    for candidate in php php8.4 php8.3; do
        local p
        p="$(command -v "$candidate" 2>/dev/null || true)"
        if [ -n "$p" ]; then
            local ver
            ver="$("$p" -r 'echo PHP_MAJOR_VERSION;' 2>/dev/null || echo 0)"
            if [ "$ver" -ge 8 ]; then
                PHP="$p"
                return 0
            fi
        fi
    done

    # Check Homebrew keg-only paths (php@8.x are keg-only)
    for keg in /opt/homebrew/opt/php@8.4/bin/php /opt/homebrew/opt/php@8.3/bin/php \
               /usr/local/opt/php@8.4/bin/php /usr/local/opt/php@8.3/bin/php \
               /opt/homebrew/opt/php/bin/php /usr/local/opt/php/bin/php; do
        if [ -x "$keg" ]; then
            PHP="$keg"
            return 0
        fi
    done

    return 1
}

install_php() {
    echo "[Bootstrap] PHP not found. Installing php@8.4 via Homebrew..."
    if ! command -v brew &>/dev/null; then
        echo "[FATAL] Homebrew is required to auto-install PHP."
        echo "        Install it from https://brew.sh then re-run this script."
        exit 1
    fi
    brew install php@8.4 2>&1 | tail -5
    find_php || {
        echo "[FATAL] PHP installation succeeded but binary not found."
        exit 1
    }
}

# -------------------------------------------------------------------
# Detect or install Composer
# -------------------------------------------------------------------
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

# -------------------------------------------------------------------
# Generate a minimal .env for a Laravel project directory.
# Everything is self-contained — no .env.example needed.
# phpunit.xml overrides DB, cache, etc. for testing anyway.
# -------------------------------------------------------------------
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

# -------------------------------------------------------------------
# Prepare a Laravel project directory for local testing
#   Usage: prepare_project <dir> <app_name>
# -------------------------------------------------------------------
prepare_project() {
    local dir="$1"
    local app_name="${2:-Laravel}"

    # 1. Ensure all required Laravel directories exist
    mkdir -p "$dir/bootstrap/cache" \
             "$dir/storage/logs" \
             "$dir/storage/framework/sessions" \
             "$dir/storage/framework/views" \
             "$dir/storage/framework/cache"

    # 2. Generate a clean .env inline — no dependency on .env.example.
    #    Tests override everything via phpunit.xml anyway.
    generate_env "$dir" "$app_name"

    # 3. Install Composer dependencies (don't swallow errors)
    echo "[Setup] Installing dependencies in $(basename "$dir")..."
    (cd "$dir" && $COMPOSER install --no-interaction 2>&1) || {
        echo "[FATAL] Composer install failed in $(basename "$dir"). See output above."
        exit 1
    }
}

# -------------------------------------------------------------------
# Bootstrap
# -------------------------------------------------------------------
echo "============================================"
echo "  CivicCrowd Test Runner"
echo "============================================"
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

# -------------------------------------------------------------------
# Prepare backend
# -------------------------------------------------------------------

# Symlink shared test directories into backend (mirrors Docker volume mounts).
# Remove whatever exists (symlink, directory, or file) and recreate the symlink.
for dir_name in API_tests unit_tests; do
    if [ -d "$ROOT/$dir_name" ]; then
        rm -rf "$BACKEND/$dir_name"
        ln -s "$ROOT/$dir_name" "$BACKEND/$dir_name"
    fi
done

prepare_project "$BACKEND" "CivicCrowd-Backend"
echo ""

# ===================================================================
#  Backend Unit Tests
# ===================================================================
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

# ===================================================================
#  Backend API Tests
# ===================================================================
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

# ===================================================================
#  Frontend Tests
# ===================================================================
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
