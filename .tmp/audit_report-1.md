# CivicCrowd Static Audit Report (2026-04-12)

## 1. Verdict
- Overall conclusion: **Partial Pass**
- Rationale: Core architecture and many required flows are present, but multiple independent **High** severity issues materially impact prompt-fit delivery credibility and security/operational correctness.

## 2. Scope and Static Verification Boundary
- Reviewed:
  - Root documentation/config: [README.md](README.md#L1), [docker-compose.yml](docker-compose.yml#L1), [run_tests.sh](run_tests.sh#L1), [phpunit.xml](phpunit.xml#L1)
  - Backend routes/security/core modules: [backend/routes/api.php](backend/routes/api.php#L1), [backend/bootstrap/app.php](backend/bootstrap/app.php#L14), services/policies/controllers/migrations under [backend/app](backend/app) and [backend/database/migrations](backend/database/migrations)
  - Frontend flow wiring and Livewire components: [frontend/routes/web.php](frontend/routes/web.php#L1), components/views under [frontend/app/Livewire](frontend/app/Livewire) and [frontend/resources/views](frontend/resources/views)
  - Tests and test config: [API_tests](API_tests), [unit_tests](unit_tests), [frontend/tests](frontend/tests), [backend/phpunit.xml](backend/phpunit.xml#L1), [frontend/phpunit.xml](frontend/phpunit.xml#L1)
- Excluded from evidence source: `./.tmp/**` (per instruction)
- Not executed:
  - Project runtime, Docker, tests, external services, browser interactions
- Cannot Confirm Statistically:
  - Runtime correctness under load/concurrency, scheduler execution timing accuracy, actual container health, real browser rendering quality, real delivery outcomes
- Manual verification required for:
  - End-to-end runtime behavior, scheduler-triggered jobs in deployed environment, UX polish and visual quality

## 3. Repository / Requirement Mapping Summary
- Prompt core goals mapped:
  - Crowdfunding + reservation lifecycle, governance workflows, notification center, risk control, dispute arbitration, offline payment logging, voucher lifecycle, review anti-retaliation
- Main implementation areas mapped:
  - API workflow orchestration in [backend/routes/api.php](backend/routes/api.php#L52)
  - Business rules in services: booking/order/refund/campaign/risk/notification under [backend/app/Services](backend/app/Services)
  - Authorization in route middleware + policies under [backend/app/Http/Middleware](backend/app/Http/Middleware) and [backend/app/Policies](backend/app/Policies)
  - Data model coverage in migrations under [backend/database/migrations](backend/database/migrations)
  - Prompt-facing UI flows in Livewire routes/components under [frontend/routes/web.php](frontend/routes/web.php#L16)

## 4. Section-by-section Review

### 1. Hard Gates
- **1.1 Documentation and static verifiability**
  - Conclusion: **Partial Pass**
  - Rationale: Startup/test instructions and topology are present, but security/documentation consistency is materially flawed (default credential in compose conflicts with "required secret" and "no secrets" claims).
  - Evidence:
    - [README.md](README.md#L31), [README.md](README.md#L36), [README.md](README.md#L116)
    - [docker-compose.yml](docker-compose.yml#L8), [docker-compose.yml](docker-compose.yml#L44)
    - [run_tests.sh](run_tests.sh#L50)
  - Manual verification note: runtime startup outcomes not verified.
- **1.2 Material prompt deviation**
  - Conclusion: **Partial Pass**
  - Rationale: Most domain flows align, but prompt-critical governance behavior has at least one broken transition (venue program reject path) and reminder-template rendering mismatch undermines notification behavior.
  - Evidence:
    - [backend/app/Http/Controllers/Api/Campaign/VenueProgramController.php](backend/app/Http/Controllers/Api/Campaign/VenueProgramController.php#L139)
    - [backend/app/Policies/VenueProgramPolicy.php](backend/app/Policies/VenueProgramPolicy.php#L1)
    - [backend/database/seeders/NotificationTemplateSeeder.php](backend/database/seeders/NotificationTemplateSeeder.php#L95)
    - [backend/app/Console/Commands/SendBookingReminders.php](backend/app/Console/Commands/SendBookingReminders.php#L68)

### 2. Delivery Completeness
- **2.1 Core requirement coverage**
  - Conclusion: **Partial Pass**
  - Rationale: Broad feature set exists (orders/refunds/after-sales/reviews/risk/disputes/notifications), but key prompt-required behavior is partially broken or weakly evidenced (venue reject flow; timezone-aware reminder payload mismatch).
  - Evidence:
    - [backend/routes/api.php](backend/routes/api.php#L112), [backend/routes/api.php](backend/routes/api.php#L149), [backend/routes/api.php](backend/routes/api.php#L160), [backend/routes/api.php](backend/routes/api.php#L196)
    - [backend/app/Services/Review/ReviewService.php](backend/app/Services/Review/ReviewService.php#L14)
    - [backend/app/Services/Notification/NotificationService.php](backend/app/Services/Notification/NotificationService.php#L68)
- **2.2 End-to-end deliverable shape**
  - Conclusion: **Pass**
  - Rationale: Complete multi-module structure, docs, migrations, tests, and split frontend/backend delivery are present.
  - Evidence:
    - [README.md](README.md#L1)
    - [backend/routes/api.php](backend/routes/api.php#L1)
    - [frontend/routes/web.php](frontend/routes/web.php#L1)
    - [API_tests](API_tests), [unit_tests](unit_tests), [frontend/tests](frontend/tests)

### 3. Engineering and Architecture Quality
- **3.1 Structure and modularity**
  - Conclusion: **Pass**
  - Rationale: Reasonable decomposition into controllers/services/policies/middleware/listeners and migration-backed domain model.
  - Evidence:
    - [backend/app/Services](backend/app/Services)
    - [backend/app/Policies](backend/app/Policies)
    - [backend/app/Listeners](backend/app/Listeners)
- **3.2 Maintainability/extensibility**
  - Conclusion: **Partial Pass**
  - Rationale: Overall maintainable, but certain defects indicate brittle cross-layer coupling (template keys/variables and route-name wiring drift).
  - Evidence:
    - [backend/database/seeders/NotificationTemplateSeeder.php](backend/database/seeders/NotificationTemplateSeeder.php#L95)
    - [backend/app/Console/Commands/SendBookingReminders.php](backend/app/Console/Commands/SendBookingReminders.php#L68)
    - [frontend/resources/views/livewire/voucher/voucher-list.blade.php](frontend/resources/views/livewire/voucher/voucher-list.blade.php#L70)
    - [frontend/routes/web.php](frontend/routes/web.php#L38)

### 4. Engineering Details and Professionalism
- **4.1 Error handling/logging/validation/API quality**
  - Conclusion: **Partial Pass**
  - Rationale: Strong baseline exists (central API exception mapping, audit logging, policy checks), but material issues remain (secret default, broken governance path, reminder payload mismatch).
  - Evidence:
    - [backend/bootstrap/app.php](backend/bootstrap/app.php#L45)
    - [backend/app/Http/Middleware/AuditRequest.php](backend/app/Http/Middleware/AuditRequest.php#L21)
    - [backend/app/Models/AuditLog.php](backend/app/Models/AuditLog.php#L44)
- **4.2 Product-level credibility**
  - Conclusion: **Partial Pass**
  - Rationale: Product-like breadth and workflows are present, yet core-user navigation contains a static break in voucher list -> detail route.
  - Evidence:
    - [frontend/resources/views/livewire/voucher/voucher-list.blade.php](frontend/resources/views/livewire/voucher/voucher-list.blade.php#L70)
    - [frontend/routes/web.php](frontend/routes/web.php#L38)

### 5. Prompt Understanding and Requirement Fit
- **5.1 Business understanding and constraint fit**
  - Conclusion: **Partial Pass**
  - Rationale: Most constraints are represented (campaign duration 7-60, seat lock TTL 5, 72h review delay, 14-day refunds, risk controls), but implementation has key breaks/mismatches in moderation and reminder-template data mapping.
  - Evidence:
    - [backend/database/seeders/BusinessParametersSeeder.php](backend/database/seeders/BusinessParametersSeeder.php#L134)
    - [backend/database/seeders/BusinessParametersSeeder.php](backend/database/seeders/BusinessParametersSeeder.php#L26)
    - [backend/database/seeders/BusinessParametersSeeder.php](backend/database/seeders/BusinessParametersSeeder.php#L128)
    - [backend/app/Http/Controllers/Api/Campaign/VenueProgramController.php](backend/app/Http/Controllers/Api/Campaign/VenueProgramController.php#L139)

### 6. Aesthetics (frontend-only/full-stack)
- **6.1 Visual/interaction quality**
  - Conclusion: **Cannot Confirm Statistically**
  - Rationale: Static code shows loading/disabled/feedback patterns, but visual quality and interaction fidelity cannot be validated without runtime rendering.
  - Evidence:
    - [frontend/resources/views/livewire/booking/seat-map.blade.php](frontend/resources/views/livewire/booking/seat-map.blade.php#L152)
    - [frontend/resources/views/livewire/notification/notification-inbox.blade.php](frontend/resources/views/livewire/notification/notification-inbox.blade.php#L51)

## 5. Issues / Suggestions (Severity-Rated)

### High

- **H-01: Insecure default DB credential + docs/security contradiction**
  - Severity: **High**
  - Conclusion: **Fail**
  - Evidence:
    - [docker-compose.yml](docker-compose.yml#L8)
    - [docker-compose.yml](docker-compose.yml#L44)
    - [README.md](README.md#L36)
    - [README.md](README.md#L116)
  - Impact: If env vars are omitted, a predictable database password is used; this contradicts stated secret handling and can materially weaken deployment security.
  - Minimum actionable fix: Remove hardcoded fallback from compose and fail fast when secret env vars are missing; align README and compose behavior.
  - Minimal verification path: statically confirm `${POSTGRES_PASSWORD}` has no literal fallback and docs match.

- **H-02: Venue program reject transition is effectively blocked by missing policy method**
  - Severity: **High**
  - Conclusion: **Fail**
  - Evidence:
    - [backend/app/Http/Controllers/Api/Campaign/VenueProgramController.php](backend/app/Http/Controllers/Api/Campaign/VenueProgramController.php#L139)
    - [backend/app/Policies/VenueProgramPolicy.php](backend/app/Policies/VenueProgramPolicy.php#L1)
  - Impact: Moderator governance flow cannot reliably execute required reject transition for venue programs.
  - Minimum actionable fix: Implement `reject(User $user, VenueProgram $program): bool` in policy with correct status/permission guard and add API test.
  - Minimal verification path: static check that policy includes reject method and tests cover approve/reject parity.

- **H-03: Booking reminder notification payload does not satisfy template variable contract**
  - Severity: **High**
  - Conclusion: **Fail**
  - Evidence:
    - [backend/database/seeders/NotificationTemplateSeeder.php](backend/database/seeders/NotificationTemplateSeeder.php#L98)
    - [backend/app/Console/Commands/SendBookingReminders.php](backend/app/Console/Commands/SendBookingReminders.php#L68)
    - [backend/app/Services/Notification/NotificationService.php](backend/app/Services/Notification/NotificationService.php#L72)
  - Impact: Reminder content may render unresolved placeholders (`{{event_time}}`) or not render expected localized time formatting, undermining prompt-critical messaging quality.
  - Minimum actionable fix: Pass `event_time` with DateTime value (not pre-stringified) or align template placeholder to emitted key and add tests for rendered body correctness.
  - Minimal verification path: unit/integration test asserting reminder body contains resolved time text and no `{{...}}` leftovers.

- **H-04: Voucher list links to non-existent route name, breaking core user navigation**
  - Severity: **High**
  - Conclusion: **Fail**
  - Evidence:
    - [frontend/resources/views/livewire/voucher/voucher-list.blade.php](frontend/resources/views/livewire/voucher/voucher-list.blade.php#L70)
    - [frontend/routes/web.php](frontend/routes/web.php#L38)
  - Impact: Users can reach voucher list but cannot navigate to voucher detail through listed action, breaking a core prompt flow.
  - Minimum actionable fix: Use the registered route name (`vouchers.detail`) or rename route to match all call sites.
  - Minimal verification path: static grep for route name consistency and route helper resolution.

### Medium

- **M-01: Idempotency middleware allows pass-through when key header is absent on some guarded routes**
  - Conclusion: **Partial Fail**
  - Evidence:
    - [backend/app/Http/Middleware/IdempotencyGuard.php](backend/app/Http/Middleware/IdempotencyGuard.php#L21)
    - [backend/routes/api.php](backend/routes/api.php#L103)
  - Impact: Duplicate-trigger protection depends on client behavior; some idempotent routes may still process duplicates without explicit key.
  - Minimum actionable fix: Enforce required key on all mutation routes marked with idempotency middleware, or scope exceptions explicitly.

- **M-02: After-sales flow computes checksum but does not verify client-provided checksum contract**
  - Conclusion: **Partial Pass**
  - Evidence:
    - [backend/app/Http/Controllers/Api/Order/AfterSalesController.php](backend/app/Http/Controllers/Api/Order/AfterSalesController.php#L49)
  - Impact: Integrity metadata is stored but checksum validation semantics in prompt are only partially represented.
  - Minimum actionable fix: Accept and validate declared checksum against computed checksum (or explicitly document server-computed-only policy).

- **M-03: Frontend surfaces raw backend exception text in user flash messages**
  - Conclusion: **Partial Fail**
  - Evidence:
    - [frontend/app/Livewire/Campaign/CampaignDetail.php](frontend/app/Livewire/Campaign/CampaignDetail.php#L53)
    - [frontend/app/Livewire/Notification/NotificationInbox.php](frontend/app/Livewire/Notification/NotificationInbox.php#L44)
  - Impact: Internal error details may leak to end users and reduce UX/security hygiene.
  - Minimum actionable fix: Map exceptions to controlled user-safe messages and log detailed text server-side.

## 6. Security Review Summary

- **Authentication entry points**: **Pass**
  - Evidence: [backend/routes/api.php](backend/routes/api.php#L28), [backend/app/Http/Controllers/Api/Auth/AuthController.php](backend/app/Http/Controllers/Api/Auth/AuthController.php#L19), [backend/config/hashing.php](backend/config/hashing.php#L17)
  - Reasoning: Username/password login exists, Argon2id is configured, token issuance/logout paths are present.

- **Route-level authorization**: **Partial Pass**
  - Evidence: [backend/routes/api.php](backend/routes/api.php#L52), [backend/routes/api.php](backend/routes/api.php#L230), [backend/bootstrap/app.php](backend/bootstrap/app.php#L23)
  - Reasoning: Strong use of auth + role middleware, but one policy gap breaks a moderation transition.

- **Object-level authorization**: **Partial Pass**
  - Evidence: [backend/app/Http/Controllers/Api/Order/OrderController.php](backend/app/Http/Controllers/Api/Order/OrderController.php#L49), [backend/app/Policies/OrderPolicy.php](backend/app/Policies/OrderPolicy.php#L21), [backend/app/Http/Controllers/Api/Voucher/VoucherController.php](backend/app/Http/Controllers/Api/Voucher/VoucherController.php#L38)
  - Reasoning: Controllers frequently call policy authorization; key resources are protected.

- **Function-level authorization**: **Partial Pass**
  - Evidence: [backend/database/seeders/RolesAndPermissionsSeeder.php](backend/database/seeders/RolesAndPermissionsSeeder.php#L24), [backend/app/Models/User.php](backend/app/Models/User.php#L116)
  - Reasoning: Permission model exists and is used; some function contracts are inconsistent (venue reject policy method missing).

- **Tenant/user data isolation**: **Pass**
  - Evidence: [backend/app/Http/Controllers/Api/Notification/NotificationController.php](backend/app/Http/Controllers/Api/Notification/NotificationController.php#L23), [backend/app/Policies/NotificationPolicy.php](backend/app/Policies/NotificationPolicy.php#L21), [backend/app/Http/Controllers/Api/Order/OrderController.php](backend/app/Http/Controllers/Api/Order/OrderController.php#L29)
  - Reasoning: User-scoped queries and owner checks are consistently applied on major user resources.

- **Admin/internal/debug endpoint protection**: **Pass**
  - Evidence: [backend/routes/api.php](backend/routes/api.php#L230), [backend/routes/api.php](backend/routes/api.php#L247)
  - Reasoning: Admin/internal endpoints are under admin role middleware; webhook URLs are constrained to local/internal hosts.

## 7. Tests and Logging Review

- **Unit tests**: **Partial Pass**
  - Evidence: [unit_tests/NotificationServiceTest.php](unit_tests/NotificationServiceTest.php#L18), [unit_tests/OrderModelTest.php](unit_tests/OrderModelTest.php#L75), [unit_tests/DisputeDecisionImmutabilityTest.php](unit_tests/DisputeDecisionImmutabilityTest.php#L96)
  - Reasoning: Good coverage for key model/service rules, but gaps remain for some critical cross-layer integration defects.

- **API/integration tests**: **Partial Pass**
  - Evidence: [API_tests/AuthTest.php](API_tests/AuthTest.php#L9), [API_tests/BookingTest.php](API_tests/BookingTest.php#L87), [API_tests/AuthorizationTest.php](API_tests/AuthorizationTest.php#L27), [API_tests/RefundTest.php](API_tests/RefundTest.php#L208)
  - Reasoning: Covers many happy and failure paths (401/403/404/422, idempotency, authz), but does not catch discovered high defects (venue reject policy gap, reminder placeholder mismatch, frontend voucher route mismatch).

- **Logging categories / observability**: **Partial Pass**
  - Evidence: [backend/app/Http/Middleware/AuditRequest.php](backend/app/Http/Middleware/AuditRequest.php#L21), [backend/app/Models/AuditLog.php](backend/app/Models/AuditLog.php#L44), [backend/config/logging.php](backend/config/logging.php#L53)
  - Reasoning: Audit logs and centralized logging exist; request audit payload is minimal and does not include detailed domain deltas by default.

- **Sensitive-data leakage risk in logs/responses**: **Partial Pass**
  - Evidence: [backend/app/Models/User.php](backend/app/Models/User.php#L34), [frontend/app/Livewire/Campaign/CampaignDetail.php](frontend/app/Livewire/Campaign/CampaignDetail.php#L53)
  - Reasoning: Sensitive fields are hidden/cast-encrypted server-side, but frontend error flashing can expose backend exception text to users.

## 8. Test Coverage Assessment (Static Audit)

### 8.1 Test Overview
- Unit tests exist: **Yes**
  - Evidence: [unit_tests](unit_tests), [phpunit.xml](phpunit.xml#L14)
- API/integration tests exist: **Yes**
  - Evidence: [API_tests](API_tests), [phpunit.xml](phpunit.xml#L17)
- Frontend tests exist: **Yes** (feature + unit)
  - Evidence: [frontend/tests/Feature](frontend/tests/Feature), [frontend/tests/Unit](frontend/tests/Unit), [frontend/phpunit.xml](frontend/phpunit.xml#L7)
- Test entry points documented: **Yes**
  - Evidence: [README.md](README.md#L61), [run_tests.sh](run_tests.sh#L109)

### 8.2 Coverage Mapping Table
| Requirement / Risk Point | Mapped Test Case(s) | Key Assertion / Fixture / Mock | Coverage Assessment | Gap | Minimum Test Addition |
|---|---|---|---|---|---|
| Auth login/logout + 401 behavior | [API_tests/AuthTest.php](API_tests/AuthTest.php#L9), [API_tests/ErrorHandlingTest.php](API_tests/ErrorHandlingTest.php#L46) | status 200/401/422 assertions | sufficient | None major | Add lockout/rate-limit assertions |
| Route-level role authorization | [API_tests/AdminTest.php](API_tests/AdminTest.php#L27), [API_tests/RiskControlTest.php](API_tests/RiskControlTest.php#L31) | 403 for non-admin/non-moderator | basically covered | Venue reject policy gap not tested | Add explicit moderator reject program test |
| Booking confirm idempotency | [API_tests/BookingTest.php](API_tests/BookingTest.php#L87) | repeated confirm returns same order | sufficient | Lock endpoint idempotency header enforcement not covered | Add test for repeated lock without key behavior expectation |
| Refund window / fulfillment constraints | [API_tests/RefundTest.php](API_tests/RefundTest.php#L185), [API_tests/RefundTest.php](API_tests/RefundTest.php#L208) | 403 on non-fulfilled/expired window | sufficient | None major | Add boundary test on exact deadline day |
| Review 72h delayed visibility + alias exposure | [API_tests/ReviewTest.php](API_tests/ReviewTest.php#L90), [API_tests/ReviewTest.php](API_tests/ReviewTest.php#L154) | visible vs hidden filtering; alias checks | basically covered | No test of generated alias masking policy for all paths | Add service-level alias format/privacy test for generated records |
| Notification locale/timezone rendering | [unit_tests/NotificationServiceTest.php](unit_tests/NotificationServiceTest.php#L75) | timezone conversion and locale fallback assertions | basically covered | Missing integration test for real scheduled reminder template variables | Add command-to-template integration test for booking reminder body |
| Dispute immutability | [unit_tests/DisputeDecisionImmutabilityTest.php](unit_tests/DisputeDecisionImmutabilityTest.php#L96) | DB trigger blocks update/delete | sufficient | None major | Add API-level arbitration decision immutability follow-up test |
| Frontend voucher navigation | No direct test found for voucher list route helper target | N/A | missing | Static route mismatch slipped through | Add frontend feature test rendering voucher list and asserting generated detail URL is routable |

### 8.3 Security Coverage Audit
- Authentication: **sufficiently covered**
  - Evidence: [API_tests/AuthTest.php](API_tests/AuthTest.php#L9)
- Route authorization: **basically covered**
  - Evidence: [API_tests/AdminTest.php](API_tests/AdminTest.php#L27), [API_tests/RiskControlTest.php](API_tests/RiskControlTest.php#L31)
- Object-level authorization: **partially covered**
  - Evidence: [API_tests/AuthorizationTest.php](API_tests/AuthorizationTest.php#L27)
  - Gap: no targeted test for venue program reject authorization path regression
- Tenant/data isolation: **basically covered**
  - Evidence: [API_tests/AuthorizationTest.php](API_tests/AuthorizationTest.php#L41)
- Admin/internal protection: **covered**
  - Evidence: [API_tests/AdminTest.php](API_tests/AdminTest.php#L27)

### 8.4 Final Coverage Judgment
- **Partial Pass**
- Covered major risks: auth baseline, many authorization checks, idempotent booking confirm, refund constraints, immutability, error-shape consistency.
- Uncovered/high-risk gaps: tests did not detect a broken moderation transition path, notification template/payload mismatch, and frontend voucher route break; severe defects could remain while current suites pass.

## 9. Final Notes
- Review conclusions are strictly static and evidence-based.
- Runtime-success claims are intentionally avoided.
- Highest-priority remediation should target the four High findings first, then fill coverage gaps that failed to detect them.
