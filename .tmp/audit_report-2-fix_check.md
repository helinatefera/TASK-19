# Issue Recheck Results (Static)

Source reviewed: .tmp/audit_report-2.md
Boundary: static-only, no runtime execution

## Summary
- Total issues rechecked: 5
- Fixed: 5
- Not fixed: 0
- Partially fixed: 0

## Detailed Results

### F-001
- Title: Idempotency key generation is per HTTP call, not per user action
- Status: Fixed
- Evidence:
  - frontend/app/Services/ApiClient.php:25
  - frontend/app/Services/ApiClient.php:47
  - frontend/app/Livewire/Booking/SeatMap.php:85
  - frontend/app/Livewire/Booking/SeatMap.php:97
  - frontend/app/Livewire/Booking/SeatMap.php:142
  - frontend/app/Livewire/Campaign/CampaignDetail.php:122
  - frontend/app/Livewire/Campaign/CampaignDetail.php:136
- Recheck note:
  - ApiClient no longer auto-generates an idempotency key per POST call.
  - Key creation/reuse is moved to action scopes in Livewire components (session-backed per logical action), and keys are only cleared after successful completion.

### F-002
- Title: Graylist restriction is modeled but not enforced in booking/order decision paths
- Status: Fixed
- Evidence:
  - backend/app/Http/Controllers/Api/Order/OrderController.php:84
  - backend/app/Http/Controllers/Api/Booking/BookingController.php:64
  - backend/app/Models/CreditScore.php:76
- Recheck note:
  - Booking and order entry points now reject users that require staff approval (`requiresStaffApproval()`), operationalizing graylist restriction behavior.

### F-003
- Title: Refund-frequency anomaly evaluation is wired only to RefundApproved events
- Status: Fixed
- Evidence:
  - backend/app/Providers/EventServiceProvider.php:100
  - backend/app/Providers/EventServiceProvider.php:105
  - backend/app/Listeners/RiskControl/EvaluateAnomaly.php:17
- Recheck note:
  - `EvaluateAnomaly` is now bound to both `RefundRequested` and `RefundApproved`, and the listener handles both event types.

### F-004
- Title: Frontend campaign duration validation remains wider than prompt/backend defaults
- Status: Fixed
- Evidence:
  - frontend/app/Livewire/Campaign/CampaignForm.php:31
  - frontend/app/Livewire/Campaign/CampaignForm.php:94
- Recheck note:
  - Frontend campaign duration validation now enforces `min:7|max:60` at both property and submit-time validation.

### F-005
- Title: Stack documentation can be clearer about backend vs frontend framework versions
- Status: Fixed
- Evidence:
  - README.md:99
  - README.md:108
  - frontend/composer.json:7
- Recheck note:
  - README now separates Backend Stack and Frontend Stack sections with explicit framework/version lines, and frontend versions align with composer constraints.

## Updated Overall Assessment for This Issue Set
- All five tracked findings from `.tmp/audit_report-2.md` are now statically verified as fixed.
- This specific issue set is fully closed under static-review boundaries.
