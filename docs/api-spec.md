# CivicCrowd Reservation & Crowdfunding Management System API Specification
## Runtime Reality

- This API is served by the Eaglepoint backend implementation.
- The document describes the actual HTTP contract implemented
	by the codebase and should be considered the source of runtime
	behavior for clients.

## Source of Truth

- Route wiring: `backend/routes/*.php`
- Controllers/handlers: `backend/app/Http/Controllers/*`
- Request validation rules: `backend/app/Http/Requests/*`
- Models / response shapes: `backend/app/Models/*`
- Error contract and middleware: `backend/app/Http/Middleware/*`

## Contract Conventions

- Base URL: `http://localhost:8000` (env-configurable)
- API prefix: `/api/v1`
- Default content type: `application/json` for request and response
- IDs: UUID strings where applicable
- Timestamps: RFC3339 (e.g. `2026-04-10T12:34:56Z`)

### Global Headers

- `Authorization: Bearer <token>` — required for authenticated endpoints
- `X-Request-Id` — optional client-provided id; returned on responses
- `Accept-Language` — optional; used to select localized messages

## Error Envelope

- All error responses follow this envelope:

```json
{
	"code": 400,
	"message": "validation failed",
	"details": { /* optional field-level errors */ }
}
```

Common HTTP-to-code mapping:

- `400` — Bad Request / validation failure
- `401` — Unauthorized / invalid token / session expired
- `403` — Forbidden
- `404` — Not Found
- `409` — Conflict / duplicate
- `422` — Unprocessable Entity (semantic validation)
- `500` — Internal Server Error

## Authentication & Session

- Public endpoints (no token):
	- `POST /api/v1/auth/register`
	- `POST /api/v1/auth/login`
	- `POST /api/v1/auth/recover`
	- `GET /health`

- Authenticated endpoints expect `Authorization` header.
- Sessions may support refresh tokens — see `POST /api/v1/auth/refresh`.

## Shared Query Patterns

- Pagination: `limit` (default 50), `offset` (default 0)
- Filtering and sorting parameters are endpoint-specific; use query keys
	described per endpoint.

## Endpoint Catalog

### Health

#### GET /health

- Auth: none
- Response `200`:

```json
{"status":"ok"}
```

---

### Authentication

#### POST /api/v1/auth/register

- Auth: none
- Body:

```json
{
	"email": "user@example.com",
	"password": "StrongPass123!",
	"display_name": "User Name"
}
```

- Response `201`:

```json
{ "id": "<uuid>", "email": "user@example.com", "display_name": "User Name" }
```

#### POST /api/v1/auth/login

- Auth: none
- Body:

```json
{ "email": "user@example.com", "password": "StrongPass123!" }
```

- Response `200`:

```json
{ "token": "<bearer>", "expires_at": "2026-04-17T12:00:00Z" }
```

#### POST /api/v1/auth/logout

- Auth: bearer
- Response `204`

#### POST /api/v1/auth/refresh

- Auth: bearer
- Body: `{ "device_id": "optional" }`
- Response `200`: refreshed token payload.

---

### Users

#### GET /api/v1/users

- Auth: administrator (permission `user.read`)
- Query: `limit`, `offset`
- Response `200`: `User[]`

#### GET /api/v1/users/me

- Auth: bearer
- Response `200`: `User` (current user)

#### GET /api/v1/users/:id

- Auth: bearer + permission checks (self-or-admin)
- Response `200`: `User`

#### PUT /api/v1/users/:id

- Auth: bearer + self-or-admin
- Body: partial user update
- Response `200`: updated `User`

---

### Bookings

Endpoints related to booking resources used by clients.

#### POST /api/v1/bookings

- Auth: bearer
- Body: `CreateBookingRequest` (see models section)
- Response `201`: `Booking`

#### GET /api/v1/bookings

- Auth: bearer
- Query: `limit`, `offset`, `user_id` (admin)
- Response `200`: `Booking[]`

#### GET /api/v1/bookings/:id

- Auth: bearer
- Response `200`: `Booking`

---

### Orders

#### POST /api/v1/orders

- Auth: bearer
- Body: `CreateOrderRequest`
- Response `201`: `OrderSnapshot`

#### GET /api/v1/orders

- Auth: bearer
- Query: `limit`, `offset`, `status`
- Response `200`: `OrderSnapshot[]`

#### GET /api/v1/orders/:id

- Auth: bearer
- Response `200`: `OrderSnapshot`

---

### Payments

#### POST /api/v1/payments

- Auth: bearer
- Body: payment capture / payment method token
- Response `201`: payment resource or redirect payload

---

### Notifications

#### GET /api/v1/notifications/inbox

- Auth: bearer
- Query: `limit`, `offset`
- Response `200`: `Message[]`

#### POST /api/v1/notifications/inbox/:id/read

- Auth: bearer
- Response `200`: `{ "status": "read" }`

---

### Reviews

#### POST /api/v1/reviews

- Auth: bearer
- Body: `CreateReviewRequest`
- Response `201`: `Review`

---

### Vouchers

#### POST /api/v1/vouchers/redeem

- Auth: bearer
- Body: `{ "code": "ABC123" }`
- Response `200`: `VoucherRedemption` or `400/404` on failure

---

### Admin

Admin endpoints require elevated permissions.

#### GET /api/v1/admin/audit-logs

- Auth: administrator + `admin.audit`
- Query: `limit`, `offset`, `entity_type`, `entity_id`
- Response `200`: `AuditLog[]`

#### GET /api/v1/admin/config

- Auth: administrator + `admin.config`
- Response `200`: `AppConfig[]`

---

## Models (Representative)

### Error

```json
{
	"code": 400,
	"message": "bad request",
	"details": { "field": ["error message"] }
}
```

### User

```json
{
	"id": "uuid",
	"email": "user@example.com",
	"display_name": "Name",
	"role": "user",
	"created_at": "2026-04-10T12:00:00Z"
}
```

### OrderSnapshot (brief)

```json
{
	"id": "uuid",
	"user_id": "uuid",
	"total": "12.34",
	"currency": "USD",
	"created_at": "2026-04-10T12:00:00Z"
}
```

## Auditing

- Mutating requests for audited endpoints are recorded. Audit entries include actor id,
	action, entity id/type, and old/new values when available.

## Masking Rules

- Non-admin responses mask sensitive fields (tax ids, addresses) as implemented
	by services and transformers.

## Notes on Contract Completeness

- This document is intended to mirror the implemented behavior in the repository.
- For authoritative schema and business rules, consult controllers, request validators,
	and models under `backend/`.

---

If you'd like, I can:

- generate an OpenAPI (Swagger) draft from this spec,
- expand any endpoint with full request/response schema examples, or
- produce curl examples for the most-used endpoints.

