# CivicCrowd - Reservation & Crowdfunding Management System

> **Project type:** Fullstack (Laravel backend API + Laravel Livewire frontend)

A Dockerized dual-project application for managing crowdfunding campaigns, venue program reservations, and internal governance workflows.

## Architecture

The system is split into two projects behind separate Nginx instances:

| Layer | Directory | Port | Description |
|-------|-----------|------|-------------|
| **Frontend** | `frontend/` | **8080** | Laravel Livewire application |
| **Backend API** | `backend/` | **8081** | Laravel REST API |
| **Database** | — | internal | PostgreSQL 16 (not exposed to host) |

The frontend communicates with the backend internally via `http://backend-nginx:80`.

### Docker Services

| Service | Container | Port |
|---------|-----------|------|
| `frontend-app` | civiccrowd_frontend_app | — |
| `frontend-nginx` | civiccrowd_frontend_nginx | **8080** |
| `backend-app` | civiccrowd_backend_app | — |
| `backend-nginx` | civiccrowd_backend_nginx | **8081** |
| `backend-queue` | civiccrowd_backend_queue | — |
| `backend-scheduler` | civiccrowd_backend_scheduler | — |
| `postgres` | civiccrowd_postgres | — |

## Setup

```bash
docker-compose up -d --build
```

All secrets have development defaults so the command above works immediately with zero configuration. To override for production:

```bash
export POSTGRES_PASSWORD="$(head -c 16 /dev/urandom | base64)"
export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
export FRONTEND_APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
docker-compose up -d --build
```

The frontend is accessible at **http://localhost:8080** and the backend API at **http://localhost:8081** after containers are healthy.

### Verification

After startup, verify the services are running:

```bash
# Frontend health
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080
# Expected: 200

# Backend API health
curl -s http://localhost:8081/api/campaigns | head -c 100
# Expected: JSON response with campaign data
```

## Testing

```bash
docker-compose up -d --build   # ensure containers are running
./run_tests.sh                 # runs all test suites inside containers
```

The test runner executes inside the Docker containers (PostgreSQL, PHP 8.4). No host-level PHP, Composer, or database is required — only Docker.

Test suites executed:

| Suite | Location |
|-------|----------|
| Backend unit tests | `unit_tests/` |
| Backend API tests | `API_tests/` |
| Frontend tests | `frontend/tests/` |

## Service URLs

| Service | URL |
|---------|-----|
| Frontend | http://localhost:8080 |
| Backend API | http://localhost:8081/api |

## Environment Configuration

Secrets are passed via environment variables. Development defaults are provided so `docker-compose up` works immediately — override for production:

| Variable | Description | Default |
|----------|-------------|---------|
| `POSTGRES_PASSWORD` | PostgreSQL password | `civiccrowd_dev_secret` — **override in production** |
| `APP_KEY` | Backend encryption key | Dev key provided in compose file |
| `FRONTEND_APP_KEY` | Frontend encryption key | Dev key provided in compose file |

## Test Credentials

Test users are created by the database seeder (`php artisan db:seed`), which runs automatically on first `docker-compose up`.

| Username | Password | Role | Description |
|----------|----------|------|-------------|
| `admin` | `Admin123!@#` | admin | Full system access |
| `staff1` | `Staff123!@#` | staff | Order fulfillment, payments, voucher redemption |
| `mod1` | `Mod123!@#` | moderator | Campaign/program approval, dispute resolution |
| `creator1` | `Creator123!@#` | creator | Campaign creation and management |
| `user1` | `User123!@#` | user | Browsing, ordering, reviews |
| `user2` | `User123!@#` | user | Second regular user (for isolation tests) |

Login via the API:

```bash
curl -s -X POST http://localhost:8081/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"Admin123!@#"}'
```

Or through the frontend at http://localhost:8080/login.

## Backend Stack

- **Framework**: Laravel 13.x with PHP 8.3+
- **Database**: PostgreSQL 16
- **Auth**: Laravel Sanctum (token-based)
- **Hashing**: Argon2id (hardcoded in `config/hashing.php`)
- **Encryption**: AES-256-CBC for sensitive field encryption
- **Queue/Cache**: Database drivers (no external dependencies)

## Frontend Stack

- **Framework**: Laravel 12.x with PHP 8.3+
- **UI**: Livewire 3.x

## Security

- **Password hashing**: Argon2id with memory=64MB, time=4, threads=1 (`backend/config/hashing.php`)
- **Session encryption**: Enabled (`SESSION_ENCRYPT=true`)
- **CSRF protection**: Active on all frontend forms
- **Role-based access**: `enforce.role` middleware on all privileged routes
- **Object-level authorization**: Laravel Policies on all resource operations
- **Audit logging**: All mutation requests logged via `AuditRequest` middleware
- **No production secrets in VCS**: All sensitive values use `${VAR}` environment variable references. Development defaults in `docker-compose.yml` are non-sensitive and intended for local use only — override via environment variables for production

## Campaign Lifecycle

Campaigns follow a strict status sequence:

```
draft → pending_review → fundraising → success/failure → closed
```

- **draft**: Creator edits campaign
- **pending_review**: Submitted for moderator review
- **fundraising**: Approved, accepting contributions (auto-transitions via scheduler)
- **success/failure**: Determined by whether target amount was met when campaign expires
- **closed**: Final state, set by moderator after success or failure
