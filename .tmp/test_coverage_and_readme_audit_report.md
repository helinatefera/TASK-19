# Test Coverage Audit

Regenerated (static audit): 2026-04-16

## Project Type Detection
- README top now explicitly declares project type as fullstack.
- Evidence: README.md line with `Project type: Fullstack`.

## Scope and Method
- Static inspection only.
- No code/tests/scripts/containers/build tools executed as part of this audit.
- Endpoint inventory source: backend/routes/api.php with Laravel API prefix resolution from backend/bootstrap/app.php routing config.

## Backend Endpoint Inventory
- Normalized endpoint format: METHOD + /api + resolved route path.
- Total endpoints: 73.

## API Test Mapping Table
| Endpoint | Covered | Test Type | Test Files | Evidence |
|---|---|---|---|---|
| POST /api/auth/login | yes | true no-mock HTTP | API_tests/AuthTest.php | test('POST /api/auth/login with valid credentials returns 200 with token and user') |
| GET /api/campaigns | yes | true no-mock HTTP | API_tests/CampaignTest.php | test('GET /api/campaigns returns paginated list') |
| GET /api/campaigns/:campaign | yes | true no-mock HTTP | API_tests/CampaignTest.php | test('GET /api/campaigns/{id} returns campaign detail with reward tiers') |
| GET /api/campaigns/:campaign/reviews | yes | true no-mock HTTP | API_tests/ReviewTest.php | test('GET /api/campaigns/{id}/reviews returns only visible reviews') |
| GET /api/programs | yes | true no-mock HTTP | API_tests/VenueProgramTest.php | test('GET /api/programs returns paginated list') |
| GET /api/programs/:program | yes | true no-mock HTTP | API_tests/VenueProgramTest.php | test('GET /api/programs/{id} returns program detail') |
| POST /api/auth/logout | yes | true no-mock HTTP | API_tests/AuthTest.php | test('POST /api/auth/logout invalidates token') |
| GET /api/auth/me | yes | true no-mock HTTP | API_tests/AuthTest.php | test('GET /api/auth/me with valid token returns user data') |
| POST /api/campaigns | yes | true no-mock HTTP | API_tests/CampaignTest.php | test('POST /api/campaigns by creator creates campaign') |
| PUT /api/campaigns/:campaign | yes | true no-mock HTTP | API_tests/CampaignTest.php | test('PUT /api/campaigns/{id} by campaign owner updates it') |
| POST /api/campaigns/:campaign/submit | yes | true no-mock HTTP | API_tests/CampaignTest.php | test('POST /api/campaigns/{id}/submit changes status to pending_review') |
| POST /api/campaigns/:campaign/approve | yes | true no-mock HTTP | API_tests/CampaignTest.php | test('POST /api/campaigns/{id}/approve by moderator changes status to fundraising') |
| POST /api/campaigns/:campaign/reject | yes | true no-mock HTTP | API_tests/CampaignTest.php | test('POST /api/campaigns/{id}/reject by moderator changes status back to draft') |
| POST /api/campaigns/:campaign/visibility | yes | true no-mock HTTP | API_tests/CampaignTest.php | test('POST /api/campaigns/{id}/visibility toggles online/offline') |
| POST /api/campaigns/:campaign/close | yes | true no-mock HTTP | API_tests/CampaignTest.php | test('POST /api/campaigns/{id}/close by moderator transitions to closed') |
| POST /api/programs | yes | true no-mock HTTP | API_tests/VenueProgramTest.php | test('POST /api/programs by moderator creates program') |
| PUT /api/programs/:program | yes | true no-mock HTTP | API_tests/VenueProgramTest.php | test('PUT /api/programs/{id} updates program') |
| POST /api/programs/:program/submit | yes | true no-mock HTTP | API_tests/VenueProgramTest.php | test('POST /api/programs/{id}/submit changes status to pending_review') |
| POST /api/programs/:program/approve | yes | true no-mock HTTP | API_tests/VenueProgramTest.php | test('POST /api/programs/{id}/approve changes status to published') |
| POST /api/programs/:program/reject | yes | true no-mock HTTP | API_tests/VenueProgramTest.php | test('POST /api/programs/{id}/reject changes status back to draft') |
| POST /api/programs/:program/visibility | yes | true no-mock HTTP | API_tests/VenueProgramTest.php | test('POST /api/programs/{id}/visibility toggles online/offline') |
| GET /api/time-slots/:timeSlot | yes | true no-mock HTTP | API_tests/BookingTest.php | test('GET /api/time-slots/{id} returns availability info') |
| POST /api/time-slots/:timeSlot/lock | yes | true no-mock HTTP | API_tests/BookingTest.php | test('POST /api/time-slots/{id}/lock creates a seat lock') |
| DELETE /api/seat-locks/:seatLock | yes | true no-mock HTTP | API_tests/BookingTest.php | test('DELETE /api/seat-locks/{id} releases the lock') |
| POST /api/seat-locks/:seatLock/confirm | yes | true no-mock HTTP | API_tests/BookingTest.php | test('POST /api/seat-locks/{id}/confirm creates order with confirmation number') |
| GET /api/orders | yes | true no-mock HTTP | API_tests/OrderTest.php | test('GET /api/orders returns users orders') |
| GET /api/orders/:order | yes | true no-mock HTTP | API_tests/OrderTest.php | test('GET /api/orders/{id} returns order detail') |
| POST /api/orders | yes | true no-mock HTTP | API_tests/OrderTest.php | test('POST /api/orders creates contribution order') |
| POST /api/orders/:order/cancel | yes | true no-mock HTTP | API_tests/OrderTest.php | test('POST /api/orders/{id}/cancel cancels a confirmed order') |
| POST /api/orders/:order/fulfill | yes | true no-mock HTTP | API_tests/OrderTest.php | test('POST /api/orders/{id}/fulfill by staff fulfills order') |
| POST /api/orders/:order/attend | yes | true no-mock HTTP | API_tests/MilestoneTest.php | test('POST /api/orders/{id}/attend by staff marks attendance') |
| GET /api/orders/:order/milestones | yes | true no-mock HTTP | API_tests/MilestoneTest.php | test('GET /api/orders/{id}/milestones returns milestones list') |
| POST /api/orders/:order/milestones | yes | true no-mock HTTP | API_tests/MilestoneTest.php | test('POST /api/orders/{id}/milestones by staff creates milestone') |
| PUT /api/milestones/:milestone | yes | true no-mock HTTP | API_tests/MilestoneTest.php | test('PUT /api/milestones/{id} by staff updates milestone status and sets completed_at') |
| POST /api/orders/:order/payments | yes | true no-mock HTTP | API_tests/OrderTest.php | test('POST /api/orders/{id}/payments by staff records payment') |
| POST /api/orders/:order/refunds | yes | true no-mock HTTP | API_tests/RefundTest.php | test('POST /api/orders/{id}/refunds creates refund request') |
| POST /api/refund-requests/:refundRequest/approve | yes | true no-mock HTTP | API_tests/RefundTest.php | test('POST /api/refund-requests/{id}/approve by staff approves refund') |
| POST /api/refund-requests/:refundRequest/reject | yes | true no-mock HTTP | API_tests/RefundTest.php | test('POST /api/refund-requests/{id}/reject by staff rejects refund') |
| POST /api/orders/:order/after-sales | yes | true no-mock HTTP | API_tests/AuthorizationTest.php | test('after-sales submit with valid checksum succeeds') |
| POST /api/after-sales/:afterSalesRequest/review | yes | true no-mock HTTP | API_tests/AfterSalesReviewTest.php | test('POST /api/after-sales/{id}/review moves request to under_review') |
| POST /api/after-sales/:afterSalesRequest/resolve | yes | true no-mock HTTP | API_tests/AuthorizationTest.php | test('staff can resolve after-sales requests') |
| GET /api/vouchers | yes | true no-mock HTTP | API_tests/VoucherReadTest.php | test('GET /api/vouchers returns user voucher list') |
| GET /api/vouchers/:voucher | yes | true no-mock HTTP | API_tests/VoucherReadTest.php | test('GET /api/vouchers/{id} returns voucher detail') |
| POST /api/vouchers/:voucher/redeem | yes | true no-mock HTTP | API_tests/VoucherTest.php | test('POST /api/vouchers/{id}/redeem by staff redeems voucher') |
| POST /api/orders/:order/reviews | yes | true no-mock HTTP | API_tests/ReviewTest.php | test('POST /api/orders/{id}/reviews on fulfilled order creates review') |
| GET /api/notifications | yes | true no-mock HTTP | API_tests/NotificationTest.php | test('GET /api/notifications returns users notifications') |
| GET /api/notifications/unread-count | yes | true no-mock HTTP | API_tests/NotificationTest.php | test('GET /api/notifications/unread-count returns count') |
| POST /api/notifications/:notification/read | yes | true no-mock HTTP | API_tests/NotificationTest.php | test('POST /api/notifications/{id}/read marks as read') |
| POST /api/notifications/read-all | yes | true no-mock HTTP | API_tests/NotificationTest.php | test('POST /api/notifications/read-all marks all as read') |
| GET /api/disputes | yes | true no-mock HTTP | API_tests/DisputeTest.php | test('GET /api/disputes returns list for moderator') |
| GET /api/disputes/:dispute | yes | true no-mock HTTP | API_tests/DisputeTest.php | test('GET /api/disputes/{id} returns dispute detail') |
| POST /api/orders/:order/disputes | yes | true no-mock HTTP | API_tests/DisputeTest.php | test('POST /api/orders/{id}/disputes creates a dispute') |
| POST /api/disputes/:dispute/assign | yes | true no-mock HTTP | API_tests/DisputeTest.php | test('POST /api/disputes/{id}/assign assigns moderator') |
| POST /api/disputes/:dispute/decide | yes | true no-mock HTTP | API_tests/DisputeTest.php | test('POST /api/disputes/{id}/decide resolves dispute') |
| GET /api/risk/credit-scores | yes | true no-mock HTTP | API_tests/RiskAnomalyTest.php | test('GET /api/risk/credit-scores returns list for moderator') |
| GET /api/risk/credit-scores/:user | yes | true no-mock HTTP | API_tests/RiskControlTest.php | test('GET /api/risk/credit-scores/{user} returns score for moderator') |
| GET /api/risk/anomalies | yes | true no-mock HTTP | API_tests/RiskAnomalyTest.php | test('GET /api/risk/anomalies returns list for moderator') |
| POST /api/risk/anomalies/:anomalyFlag/resolve | yes | true no-mock HTTP | API_tests/RiskAnomalyTest.php | test('POST /api/risk/anomalies/{id}/resolve resolves anomaly') |
| POST /api/risk/chargebacks | yes | true no-mock HTTP | API_tests/RiskControlTest.php | test('POST /api/risk/chargebacks by staff records chargeback') |
| GET /api/admin/roles | yes | true no-mock HTTP | API_tests/AdminExtendedTest.php | test('GET /api/admin/roles lists available roles') |
| GET /api/admin/users | yes | true no-mock HTTP | API_tests/AdminTest.php | test('GET /api/admin/users by admin lists users') |
| POST /api/admin/users | yes | true no-mock HTTP | API_tests/AdminTest.php | test('POST /api/admin/users creates new user') |
| PUT /api/admin/users/:user | yes | true no-mock HTTP | API_tests/AdminExtendedTest.php | test('PUT /api/admin/users/{id} updates user roles') |
| GET /api/admin/business-parameters | yes | true no-mock HTTP | API_tests/AdminTest.php | test('GET /api/admin/business-parameters lists parameters') |
| PUT /api/admin/business-parameters/:key | yes | true no-mock HTTP | API_tests/AdminTest.php | test('PUT /api/admin/business-parameters/{key} updates a parameter') |
| GET /api/admin/audit-logs | yes | true no-mock HTTP | API_tests/AdminTest.php | test('GET /api/admin/audit-logs lists logs') |
| GET /api/admin/integration-stubs | yes | true no-mock HTTP | API_tests/AdminExtendedTest.php | test('GET /api/admin/integration-stubs returns list') |
| GET /api/admin/integration-stubs/:integrationStub | yes | true no-mock HTTP | API_tests/AdminExtendedTest.php | test('GET /api/admin/integration-stubs/{id} returns single stub') |
| PUT /api/admin/integration-stubs/:integrationStub | yes | true no-mock HTTP | API_tests/AdminExtendedTest.php | test('PUT /api/admin/integration-stubs/{id} toggles active state') |
| GET /api/admin/webhook-definitions | yes | true no-mock HTTP | API_tests/AdminExtendedTest.php | test('GET /api/admin/webhook-definitions returns list') |
| POST /api/admin/webhook-definitions | yes | true no-mock HTTP | API_tests/AdminExtendedTest.php | test('POST /api/admin/webhook-definitions creates definition') |
| PUT /api/admin/webhook-definitions/:webhookDefinition | yes | true no-mock HTTP | API_tests/AdminExtendedTest.php | test('PUT /api/admin/webhook-definitions/{id} updates name and events') |
| DELETE /api/admin/webhook-definitions/:webhookDefinition | yes | true no-mock HTTP | API_tests/AdminExtendedTest.php | test('DELETE /api/admin/webhook-definitions/{id} removes definition') |

## API Test Classification
1. True no-mock HTTP
- API_tests suite uses Laravel HTTP test client with seeded DB and app bootstrapping through API_tests/Pest.php.
- No transport/controller/service mocks detected in API_tests.

2. HTTP with Mocking
- None detected in API_tests.

3. Non-HTTP (unit/integration without HTTP)
- None detected in API_tests.

## Mock Detection Rules Outcome
- Backend API tests: no jest.mock/vi.mock/sinon-style stubbing patterns and no Laravel HTTP fake usage in API_tests.
- Frontend tests: mocked backend transport is extensive.
  - frontend/tests/Feature/AuthFlowTest.php uses Http::fake and withoutMiddleware.
  - frontend/tests/Feature/CampaignListTest.php, frontend/tests/Feature/OrderDetailTest.php, frontend/tests/Feature/NotificationBellTest.php use Http::fake.
  - frontend/tests/Unit/ApiClientTest.php uses Http::fake throughout.

## Coverage Summary
- Total endpoints: 73
- Endpoints with HTTP tests: 73
- Endpoints with TRUE no-mock tests: 73
- HTTP coverage: 100.0%
- True API coverage: 100.0%

## Unit Test Summary
### Backend Unit Tests
- Present: unit_tests/*.php (12 files).
- Covered modules:
  - Services: NotificationService, FieldEncryptionService.
  - Models/domain logic: Order, SeatLock, BusinessParameter, CreditScore, voucher/confirmation generation, immutability guards.
  - Security/config behavior: hashing and encryption behavior.
- Important backend modules not unit-tested directly:
  - Controllers (broadly), middleware/guards (EnforceRole, IdempotencyGuard, AuditRequest), and many policy classes.

### Frontend Unit Tests (Strict Detection)
- Frontend unit/component tests detected by direct file evidence:
  - frontend/tests/Unit/ApiClientTest.php
  - frontend/tests/Feature/AuthFlowTest.php
  - frontend/tests/Feature/CampaignListTest.php
  - frontend/tests/Feature/OrderDetailTest.php
  - frontend/tests/Feature/NotificationBellTest.php
- Framework/tools detected: Pest/PHPUnit + Laravel + Livewire testing.
- Covered frontend modules/components: ApiClient, CampaignList, OrderDetail, NotificationBell, login/logout flow pages.
- Important frontend components/modules not tested: CampaignDetail, CampaignForm, VenueProgramList, SeatMap, OrderList, VoucherList, VoucherDisplay, NotificationInbox, ReviewForm, moderation/admin Livewire views.
- Frontend unit tests: PRESENT.
- CRITICAL GAP: fullstack real FE<->BE no-mock E2E coverage is still missing.

### Cross-Layer Observation
- Backend API coverage is now broad and strong.
- Frontend is still transport-mocked and does not validate real frontend-backend boundary behavior.

## API Observability Check
- Generally strong: tests usually expose method/path, input payload, and response assertions.
- Remaining weakness: a noticeable subset of newly added tests are mostly status assertions with shallow payload validation (especially admin/risk/dispute/milestone additions).

## Tests Check
- Appropriate categories for this fullstack project:
  - API tests: present and now extensive.
  - Backend unit tests: present and meaningful.
  - Frontend component/unit tests: present but backend-mocked.
  - End-to-end FE<->BE tests: materially relevant and still missing.
- Sufficiency verdict:
  - Strong backend confidence for shipped API behaviors with full route-surface API coverage.
  - Still not fully confidence-complete at fullstack boundary due absent no-mock E2E coverage and frontend reliance on mocked transport.

## Test Coverage Score (0-100)
- 91/100

## Score Rationale
- Increased due newly added no-mock API tests now covering previously missing endpoint families, including milestones and admin integration/webhook item endpoints.
- Reduced from top-tier because:
  - Fullstack E2E across real frontend/backend is still absent.
  - Frontend tests still mock backend transport extensively.
  - Some newly added tests are shallow (status-heavy) rather than deep behavior assertions.

## Key Gaps
1. Missing fullstack no-mock FE<->BE E2E coverage (critical for final delivery confidence).
2. Frontend tests continue to rely on mocked HTTP transport, reducing real integration confidence.
3. Some API tests remain assertion-light (status-focused) and should validate richer payload/state semantics for stronger regressions detection.

## Confidence and Assumptions
- Confidence: high for endpoint inventory and test mapping from static evidence.
- Confidence: medium for runtime reliability, since static audit cannot prove execution paths without running tests.

---

# README Audit

## README Location
- Present at repo/README.md.

## Hard Gate Failures
- None detected in current README + run_tests.sh.

## High Priority Issues
1. Several newly added API tests are status-heavy and should assert richer response and state semantics for stronger confidence.

## Medium Priority Issues
1. Hardcoded test counts in README are likely to drift unless maintained continuously.

## Low Priority Issues
1. Verification section is good; adding one end-to-end user workflow example would improve operational clarity.

## README Verdict
- PASS

## README Final Notes
- Strict-mode gates now satisfied: explicit fullstack declaration, literal `docker-compose up` startup command, clear access/verification guidance, credentials matrix with role mapping, and Docker-contained test execution flow.

---

## Final Combined Verdicts
- Test Coverage Audit Verdict: PARTIAL PASS (strong backend/API coverage, but not full fullstack confidence).
- README Audit Verdict: PASS.
