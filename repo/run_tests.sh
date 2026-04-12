#!/usr/bin/env bash
set -euo pipefail

# =============================================================================
# CivicCrowd Test Runner
#
# Self-bootstrapping: installs PHP via Homebrew if absent, uses SQLite
# in-memory databases, requires zero external services (no Docker, no
# PostgreSQL, no network).
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
    # Check common locations
    for loc in /opt/homebrew/bin/composer /usr/local/bin/composer "$HOME/.composer/vendor/bin/composer"; do
        if [ -x "$loc" ]; then
            COMPOSER="$loc"
            return 0
        fi
    done
    # Use local phar if previously downloaded
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

# Symlink shared test directories into backend (mirrors Docker volume mounts)
# Replace empty placeholder dirs with symlinks to the real test directories
if [ -d "$ROOT/API_tests" ]; then
    if [ -d "$BACKEND/API_tests" ] && [ ! -L "$BACKEND/API_tests" ]; then
        rm -rf "$BACKEND/API_tests"
    fi
    if [ ! -e "$BACKEND/API_tests" ]; then
        ln -s "$ROOT/API_tests" "$BACKEND/API_tests"
    fi
fi
if [ -d "$ROOT/unit_tests" ]; then
    if [ -d "$BACKEND/unit_tests" ] && [ ! -L "$BACKEND/unit_tests" ]; then
        rm -rf "$BACKEND/unit_tests"
    fi
    if [ ! -e "$BACKEND/unit_tests" ]; then
        ln -s "$ROOT/unit_tests" "$BACKEND/unit_tests"
    fi
fi

# Ensure .env exists before composer install (package:discover needs it)
if [ ! -f "$BACKEND/.env" ]; then
    cp "$BACKEND/.env.example" "$BACKEND/.env"
fi
if ! grep -q '^APP_KEY=base64:' "$BACKEND/.env" 2>/dev/null; then
    local_key="base64:$(head -c 32 /dev/urandom | base64)"
    sed -i '' "s|^APP_KEY=.*|APP_KEY=${local_key}|" "$BACKEND/.env" 2>/dev/null || \
        sed -i "s|^APP_KEY=.*|APP_KEY=${local_key}|" "$BACKEND/.env"
fi

# Ensure Laravel storage/cache directories exist
mkdir -p "$BACKEND/bootstrap/cache" \
         "$BACKEND/storage/logs" \
         "$BACKEND/storage/framework/sessions" \
         "$BACKEND/storage/framework/views" \
         "$BACKEND/storage/framework/cache"

echo "[Setup] Installing backend dependencies..."
(cd "$BACKEND" && $COMPOSER install --no-interaction --quiet 2>&1 | tail -3)

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

# Ensure Laravel storage/cache directories exist
mkdir -p "$FRONTEND/bootstrap/cache" \
         "$FRONTEND/storage/logs" \
         "$FRONTEND/storage/framework/sessions" \
         "$FRONTEND/storage/framework/views" \
         "$FRONTEND/storage/framework/cache"

# Ensure .env exists with APP_KEY before composer install
if [ ! -f "$FRONTEND/.env" ]; then
    cp "$FRONTEND/.env.example" "$FRONTEND/.env"
fi
if ! grep -q '^APP_KEY=base64:' "$FRONTEND/.env" 2>/dev/null; then
    fe_key="base64:$(head -c 32 /dev/urandom | base64)"
    if ! grep -q '^APP_KEY=' "$FRONTEND/.env" 2>/dev/null; then
        echo "APP_KEY=${fe_key}" >> "$FRONTEND/.env"
    else
        sed -i '' "s|^APP_KEY=.*|APP_KEY=${fe_key}|" "$FRONTEND/.env" 2>/dev/null || \
            sed -i "s|^APP_KEY=.*|APP_KEY=${fe_key}|" "$FRONTEND/.env"
    fi
fi

echo "[Setup] Installing frontend dependencies..."
(cd "$FRONTEND" && $COMPOSER install --no-interaction --quiet 2>&1 | tail -3)

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
