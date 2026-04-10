#!/usr/bin/env bash
set -euo pipefail

# =============================================================================
# CivicCrowd Test Runner
#
# Fully self-contained: builds, starts, tests, and tears down all services
# inside Docker. Zero dependency on host-installed runtimes, packages, or
# environment files.
# =============================================================================

COMPOSE="docker compose"
BACKEND="backend-app"
FRONTEND="frontend-app"
PASS=0
FAIL=0
SUITES=0

# -------------------------------------------------------------------
# Generate and export secrets so all services share consistent keys.
# These are ephemeral test-run values, never persisted to files.
# -------------------------------------------------------------------
export POSTGRES_PASSWORD="test_$(head -c 8 /dev/urandom | od -An -tx1 | tr -d ' \n')"
export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
export FRONTEND_APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"

cleanup() {
    echo ""
    echo "[Teardown] Stopping services and removing volumes..."
    $COMPOSE down --remove-orphans --volumes 2>/dev/null || true
}

# Always clean up on exit (success or failure)
trap cleanup EXIT

echo "============================================"
echo "  CivicCrowd Test Runner"
echo "============================================"
echo ""

# -------------------------------------------------------------------
# Teardown: ensure clean slate
# -------------------------------------------------------------------
echo "[Setup] Cleaning up previous containers and volumes..."
$COMPOSE down --remove-orphans --volumes 2>/dev/null || true

# -------------------------------------------------------------------
# Build and start services
# -------------------------------------------------------------------
echo "[Setup] Building and starting services..."
$COMPOSE up -d --build postgres $BACKEND $FRONTEND 2>&1

# -------------------------------------------------------------------
# Wait for PostgreSQL readiness
# -------------------------------------------------------------------
echo "[Setup] Waiting for PostgreSQL to be ready..."
for i in $(seq 1 30); do
    if $COMPOSE exec -T postgres pg_isready -U civiccrowd -d civiccrowd >/dev/null 2>&1; then
        echo "[Setup] PostgreSQL is ready."
        break
    fi
    if [ "$i" -eq 30 ]; then
        echo "[FAIL] PostgreSQL did not become ready in time."
        $COMPOSE logs postgres 2>&1 | tail -20
        exit 1
    fi
    sleep 2
done

# -------------------------------------------------------------------
# Wait for backend application readiness
# -------------------------------------------------------------------
echo "[Setup] Waiting for backend application to be ready..."
for i in $(seq 1 60); do
    if $COMPOSE exec -T $BACKEND php artisan --version >/dev/null 2>&1; then
        echo "[Setup] Backend application is ready."
        break
    fi
    if [ "$i" -eq 60 ]; then
        echo "[FAIL] Backend application did not become ready in time."
        $COMPOSE logs $BACKEND 2>&1 | tail -40
        exit 1
    fi
    sleep 3
done

# -------------------------------------------------------------------
# Install backend dev dependencies (for pest/phpunit)
# -------------------------------------------------------------------
echo "[Setup] Ensuring backend test dependencies are available..."
$COMPOSE exec -T $BACKEND composer install --no-interaction 2>&1 | tail -5

# -------------------------------------------------------------------
# Prepare test database
# -------------------------------------------------------------------
echo "[Setup] Running migrations and seeding test database..."
$COMPOSE exec -T $BACKEND php artisan migrate:fresh --seed --force 2>&1 | tail -5
echo ""

# ===================================================================
#  Backend Unit Tests
# ===================================================================
echo "============================================"
echo "  Running Backend Unit Tests"
echo "============================================"
echo ""
SUITES=$((SUITES + 1))

if $COMPOSE exec -T $BACKEND php artisan test --testsuite=unit_tests 2>&1; then
    echo ""
    echo "[PASS] Backend unit tests passed."
    PASS=$((PASS + 1))
else
    echo ""
    echo "[FAIL] Backend unit tests failed."
    FAIL=$((FAIL + 1))
fi

echo ""

# -------------------------------------------------------------------
# Re-seed for API tests (clean state)
# -------------------------------------------------------------------
echo "[Setup] Re-seeding database for API tests..."
$COMPOSE exec -T $BACKEND php artisan migrate:fresh --seed --force 2>&1 | tail -3
echo ""

# ===================================================================
#  Backend API Tests
# ===================================================================
echo "============================================"
echo "  Running Backend API Tests"
echo "============================================"
echo ""
SUITES=$((SUITES + 1))

if $COMPOSE exec -T $BACKEND php artisan test --testsuite=api_tests 2>&1; then
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

# Wait for frontend application readiness
echo "[Setup] Waiting for frontend application to be ready..."
for i in $(seq 1 60); do
    if $COMPOSE exec -T $FRONTEND php artisan --version >/dev/null 2>&1; then
        echo "[Setup] Frontend application is ready."
        break
    fi
    if [ "$i" -eq 60 ]; then
        echo "[FAIL] Frontend application did not become ready in time."
        $COMPOSE logs $FRONTEND 2>&1 | tail -20
        FAIL=$((FAIL + 1))
        break
    fi
    sleep 3
done

# Install dev dependencies for testing (entrypoint installs --no-dev)
$COMPOSE exec -T $FRONTEND composer install --no-interaction 2>&1 | tail -5

if $COMPOSE exec -T $FRONTEND php artisan test 2>&1; then
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

# Teardown is handled by the EXIT trap

if [ "$FAIL" -gt 0 ]; then
    echo ""
    echo "RESULT: FAIL"
    exit 1
else
    echo ""
    echo "RESULT: PASS"
    exit 0
fi
