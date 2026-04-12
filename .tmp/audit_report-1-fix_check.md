# Audit Report #1 Fix Verification (2026-04-12 03:06)

Source issues: `.tmp/audit_report-1.md`
Method: Static-only re-verification (no runtime/test/docker execution)

## Summary
- Fixed: 7/7
- Not fixed: 0/7
- Current verdict against prior issue list: **All previously listed issues appear fixed (static evidence)**

## Re-check Results

### H-01 Insecure default DB credential fallback
- Status: **Fixed**
- Evidence:
  - `docker-compose.yml:8` -> `POSTGRES_PASSWORD: ${POSTGRES_PASSWORD}`
  - `docker-compose.yml:44` -> `DB_PASSWORD: ${POSTGRES_PASSWORD}`
  - `docker-compose.yml:93` -> `DB_PASSWORD: ${POSTGRES_PASSWORD}`
  - `docker-compose.yml:124` -> `DB_PASSWORD: ${POSTGRES_PASSWORD}`
  - `README.md:87` states password is required and has no default
  - `README.md:91` table now says `POSTGRES_PASSWORD` is required with no default

### H-02 Venue program reject policy gap
- Status: **Fixed**
- Evidence:
  - `backend/app/Policies/VenueProgramPolicy.php:64` now defines `public function reject(User $user, VenueProgram $program): bool`
  - `backend/app/Http/Controllers/Api/Campaign/VenueProgramController.php:141` still authorizes `reject` ability, now backed by policy method

### H-03 Booking reminder template/payload mismatch
- Status: **Fixed**
- Evidence:
  - `backend/database/seeders/NotificationTemplateSeeder.php:98` now uses `{{starts_at}}`
  - `backend/database/seeders/NotificationTemplateSeeder.php:99` now uses `{{starts_at}}`
  - `backend/app/Console/Commands/SendBookingReminders.php:71` sends `starts_at`

### H-04 Voucher list route mismatch
- Status: **Fixed**
- Evidence:
  - `frontend/resources/views/livewire/voucher/voucher-list.blade.php:70` now uses `route('vouchers.detail', ['voucherId' => $voucher['id']])`
  - `frontend/routes/web.php:38` defines route name `vouchers.detail` for `/vouchers/{voucherId}`

### M-01 Idempotency key missing -> pass-through
- Status: **Fixed**
- Evidence:
  - `backend/app/Http/Middleware/IdempotencyGuard.php:21-25` now returns a `422` JSON response when `X-Idempotency-Key` is missing, instead of pass-through

### M-02 After-sales checksum contract incomplete
- Status: **Fixed**
- Evidence:
  - `backend/app/Http/Controllers/Api/Order/AfterSalesController.php:33` now validates `client_checksum` as required
  - `backend/app/Http/Controllers/Api/Order/AfterSalesController.php:46-49` computes server checksum and rejects mismatch with `422`
  - `backend/app/Http/Controllers/Api/Order/AfterSalesController.php:56` persists verified checksum

### M-03 Raw backend exception messages surfaced to users
- Status: **Fixed**
- Evidence:
  - `frontend/app/Livewire/Campaign/CampaignDetail.php:39` (and related catch blocks) now flash a generic safe message
  - `frontend/app/Livewire/Notification/NotificationInbox.php:46` (and related catch blocks) now flash a generic safe message
  - No remaining `session()->flash('error', $e->getMessage())` matches found in these files

## Notes
- No code edits were made by this verification step.
- This verification only checks whether the specific previously reported issues are fixed in current files.
- Runtime behavior and tests were not executed.
