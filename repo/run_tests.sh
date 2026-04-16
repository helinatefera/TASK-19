#!/usr/bin/env bash
set -euo pipefail

# =============================================================================
# CivicCrowd Test Runner
#
# Runs all test suites inside Docker containers. No host-level PHP, database,
# or runtime dependencies are required — only Docker.
#
# Usage:
#   ./run_tests.sh          # starts containers if needed, runs tests, reports
# =============================================================================

ROOT="$(cd "$(dirname "$0")" && pwd)"
PASS=0
FAIL=0
SUITES=0

COMPOSE="docker-compose"

# Prefer `docker compose` (plugin) if `docker-compose` (standalone) is missing
if ! command -v docker-compose &>/dev/null; then
    if docker compose version &>/dev/null 2>&1; then
        COMPOSE="docker compose"
    fi
fi

# -------------------------------------------------------------------
# Helpers — use service names via compose exec, not container names
# -------------------------------------------------------------------
compose_exec() {
    $COMPOSE exec -T "$@"
}

containers_running() {
    command -v docker &>/dev/null || return 1
    compose_exec backend-app php artisan --version &>/dev/null 2>&1 || return 1
    return 0
}

wait_for_service() {
    local svc="$1"
    local max=300
    local elapsed=0
    echo "[Setup] Waiting for $svc to be ready..."
    while [ $elapsed -lt $max ]; do
        if compose_exec "$svc" php artisan --version &>/dev/null; then
            echo "[Setup] $svc is ready."
            return 0
        fi
        sleep 5
        elapsed=$((elapsed + 5))
    done
    echo "[FATAL] Timed out after ${max}s waiting for $svc."
    exit 1
}

ensure_containers() {
    if containers_running; then
        echo "[Setup] Containers already running."
        return 0
    fi

    echo "[Setup] Starting containers via $COMPOSE..."
    $COMPOSE up -d --build 2>&1
    local up_exit=$?
    if [ $up_exit -ne 0 ]; then
        echo "[FATAL] $COMPOSE up failed (exit code $up_exit)."
        exit 1
    fi

    # Wait for postgres health via compose
    echo "[Setup] Waiting for PostgreSQL..."
    for i in $(seq 1 60); do
        if compose_exec postgres pg_isready -U civiccrowd &>/dev/null; then
            echo "[Setup] PostgreSQL is ready."
            break
        fi
        if [ "$i" -eq 60 ]; then
            echo "[FATAL] PostgreSQL did not become ready."
            exit 1
        fi
        sleep 2
    done
}

# -------------------------------------------------------------------
# Main
# -------------------------------------------------------------------
echo "============================================"
echo "  CivicCrowd Test Runner"
echo "============================================"
echo ""

cd "$ROOT"
ensure_containers

# Stop queue and scheduler to avoid DB conflicts during migrate:fresh
echo "[Setup] Stopping background workers..."
$COMPOSE stop backend-queue backend-scheduler 2>/dev/null || true
echo ""

wait_for_service backend-app

# ===================================================================
#  Backend Unit Tests
# ===================================================================
echo "============================================"
echo "  Running Backend Unit Tests"
echo "============================================"
echo ""
SUITES=$((SUITES + 1))

if compose_exec backend-app php artisan test --testsuite=unit_tests 2>&1; then
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

if compose_exec backend-app php artisan test --testsuite=api_tests 2>&1; then
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

wait_for_service frontend-app

if compose_exec frontend-app php artisan test 2>&1; then
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
