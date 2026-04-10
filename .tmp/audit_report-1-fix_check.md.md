# Issue Reverification Report (Static-Only, Rerun)

Source issue list reviewed:
- [.tmp/delivery_acceptance_architecture_audit_2026-04-10_static_v2.md](.tmp/delivery_acceptance_architecture_audit_2026-04-10_static_v2.md)

## Summary
- Total prior issues rechecked: 3
- Fixed: 3
- Not fixed: 0
- Partially fixed: 0

## Issue-by-Issue Status

### F-001
- Prior issue: Payment method validation too permissive; could leak into server-error path.
- Status: Fixed
- Evidence:
- Controller now validates method via enum rule: [backend/app/Http/Controllers/Api/Order/PaymentController.php](backend/app/Http/Controllers/Api/Order/PaymentController.php#L25)
- Service enum conversion remains in place after validated input: [backend/app/Services/Order/PaymentService.php](backend/app/Services/Order/PaymentService.php#L26)
- Negative API test exists and expects 422 for invalid method: [API_tests/OrderTest.php](API_tests/OrderTest.php#L169)

### F-002
- Prior issue: Refund anomaly threshold semantics stricter than prompt wording (>= vs “more than 3”).
- Status: Fixed
- Evidence:
- Comparator now uses strict greater-than: [backend/app/Services/RiskControl/AnomalyDetectionService.php](backend/app/Services/RiskControl/AnomalyDetectionService.php#L28)
- Threshold parameter remains configured as 3, matching “more than 3” semantics with the `>` comparator: [backend/app/Services/RiskControl/AnomalyDetectionService.php](backend/app/Services/RiskControl/AnomalyDetectionService.php#L21)

### F-003
- Prior issue: Missing locale/timezone rendering assertions for notifications.
- Status: Fixed
- Evidence:
- Locale selection/fallback assertions: [unit_tests/NotificationServiceTest.php](unit_tests/NotificationServiceTest.php#L18), [unit_tests/NotificationServiceTest.php](unit_tests/NotificationServiceTest.php#L47)
- Timezone rendering assertions: [unit_tests/NotificationServiceTest.php](unit_tests/NotificationServiceTest.php#L75), [unit_tests/NotificationServiceTest.php](unit_tests/NotificationServiceTest.php#L100), [unit_tests/NotificationServiceTest.php](unit_tests/NotificationServiceTest.php#L117)

## Final Note
- Reverification was static-only (no project start, no Docker, no test execution).
- All issues from the referenced prior issue list are now statically marked fixed.
