# CivicCrowd Reservation & Crowdfunding Management System

## 1. Overview

CivicCrowd is an offline-first web platform for managing crowdfunding campaigns, venue reservations, order fulfillment, governance review, and operational messaging inside a local environment. It combines patron-facing booking and contribution flows with internal controls for moderation, disputes, auditability, and risk enforcement.

The platform serves multiple roles:
- Regular Users who browse campaigns and venue programs, place reservations or contributions, manage orders, view vouchers, track logistics, request refunds or after-sales adjustments, and receive in-app notifications
- Project Creators who draft campaigns, define reward tiers, add disclosures, and submit campaigns for review
- Moderators who review campaigns, enforce workflow rules, manage visibility, and oversee governance actions
- Staff and Administrators who log offline payments, manage seating and fulfillment operations, configure business rules, and review risk/dispute queues

The frontend is built with Laravel Livewire for responsive booking and order interactions without page reloads. The backend uses Laravel for workflow enforcement, validation, state management, notifications, and auditing. PostgreSQL is the system of record and all storage is local to support operation without internet dependency.

---

## 2. Goals

### 2.1 Primary Goals

- Allow users to browse locally published campaigns and venue programs
- Support reward-tier contributions and time-slot-based seating reservations
- Show real-time seat availability and temporary seat holds
- Prevent duplicate submissions through client-generated idempotency keys
- Support offline payment logging and complete order lifecycle management
- Provide in-app confirmations, receipts, reminders, and status notifications through a Notification Center
- Support creator campaign drafting, review, and controlled publishing transitions
- Enable two-way post-fulfillment reviews with anti-retaliation protections
- Enforce risk scoring, blacklisting, anomaly detection, and dispute handling
- Maintain full auditability, strong local security, and offline operation

### 2.2 Non-Goals

- Online payment gateway integration
- Email, SMS, or push notification delivery
- External fraud, scanning, or identity services
- Cloud storage or cloud-only infrastructure
- Public internet dependency for core operations

---

## 3. User Roles

### 3.1 Regular User

Regular Users can:
- Register and log in locally
- Browse campaigns and venue programs
- Choose reward tiers
- Reserve event seating by time slot
- Receive confirmations and voucher displays
- View and manage orders from one screen
- Cancel within allowed policy windows
- Request refunds or after-sales adjustments
- Track fulfillment milestones
- Leave reviews after fulfillment
- Receive in-app notifications

### 3.2 Project Creator

Project Creators can:
- Draft campaigns
- Set target amounts and durations
- Add risk disclosures
- Define reward tiers
- Submit campaigns for moderation review
- Monitor campaign lifecycle and visibility
- Leave reviews after fulfillment where applicable

### 3.3 Moderator

Moderators can:
- Review submitted campaigns
- Approve or reject publication readiness
- Control online/offline visibility
- Enforce campaign status transitions
- Review flagged disputes and governance actions
- Access immutable decision logs where authorized

### 3.4 Staff

Staff can:
- Log offline tender payments
- Manage operational order steps
- Record after-sales notes
- Validate vouchers on-site
- Process fulfillment milestones
- Assist with refund and adjustment workflows

### 3.5 Administrator

Administrators can:
- Manage business parameters
- Configure template dictionaries and reminders
- Enable or disable local integration stubs
- Oversee risk rules, restrictions, and operational settings
- Review audit logs and immutable governance records
- Manage access to sensitive data and role permissions

---

## 4. System Context

The system operates entirely on local infrastructure and consists of:

- A Laravel application
- Livewire components for interactive UI
- PostgreSQL as the system of record
- Local file storage for attachments and generated voucher artifacts
- Scheduled background jobs for seat lock expiry, reminders, and operational workflows
- An internal Notification Center instead of external messaging channels

All core workflows must remain functional without internet access.

---

## 5. High-Level Architecture

## 5.1 Component View

```text
+------------------------------------------------------+
| Browser UI                                           |
| - Laravel Blade + Livewire Components                |
| - Booking Flow                                       |
| - Campaign Contribution Flow                         |
| - Order Management Screen                            |
| - Notification Center                                |
| - Review Screens                                     |
| - Staff/Admin Dashboards                             |
+-----------------------------+------------------------+
                              |
                              | HTTPS
                              v
+------------------------------------------------------+
| Laravel Application                                  |
| - Auth & Session Management                          |
| - RBAC / Policy Checks                               |
| - Campaign Service                                   |
| - Reward Tier Service                                |
| - Program / Time Slot / Seat Service                 |
| - Order Service                                      |
| - Offline Tender Logging Service                     |
| - Voucher Service                                    |
| - Notification Rule Engine                           |
| - Review Service                                     |
| - Risk Control Service                               |
| - Dispute / Arbitration Service                      |
| - Attachment Validation Service                      |
| - Idempotency Service                                |
| - Audit Log Service                                  |
| - Config / Dictionary Service                        |
+-----------------------------+------------------------+
                              |
                              v
+------------------------------------------------------+
| PostgreSQL                                           |
| - Users / Roles                                      |
| - Campaigns / Reward Tiers                           |
| - Venue Programs / Time Slots / Seat Inventory       |
| - Orders / Order Items / Payments                    |
| - Seat Locks / Vouchers                              |
| - Reviews / Disputes / Arbitration Logs              |
| - Notifications / Templates / Dictionaries           |
| - Risk Events / Scores / Restrictions                |
| - Audit Logs / Configurations                        |
+------------------------------------------------------+

+------------------------------------------------------+
| Local File Storage                                   |
| - After-sales attachments                            |
| - Dispute evidence                                   |
| - Generated voucher assets                           |
| - Local exports / operational files                  |
+------------------------------------------------------+
````

## 5.2 Architectural Style

The platform uses a layered monolith architecture:

* Presentation layer: Blade + Livewire UI
* Application layer: controllers, actions, policies
* Domain/service layer: workflow and business rules
* Persistence layer: PostgreSQL and local file storage
* Job layer: scheduled and queued background tasks

This structure fits a local-first deployment while keeping workflows enforceable and auditable.

---

## 6. Design Principles

### 6.1 Offline-First

All critical business flows must operate on local infrastructure only, including notifications, risk analysis, disputes, and receipt generation.

### 6.2 Strong Workflow Enforcement

State machines govern campaigns, orders, refunds, reviews, and disputes to prevent illegal transitions.

### 6.3 Real-Time UX Without SPA Complexity

Livewire is used to provide responsive booking and contribution flows with server-backed validation and state.

### 6.4 Local Security and Privacy

Sensitive data is encrypted at rest, masked in UI by role, protected against CSRF, and kept within the local environment.

### 6.5 Deterministic Booking Integrity

Seat inventory, seat locks, and booking idempotency are enforced through consistent server-side rules and conflict checks.

### 6.6 Immutable Governance

Arbitration and audit records are append-only from the application perspective to preserve trustworthy history.

---

## 7. Functional Design

## 7.1 Authentication and Session Management

Users authenticate with local username/password credentials only. Passwords are stored using Argon2id. The system supports either JWT or server sessions, with session rotation and inactivity timeouts.

### Timeouts

* Staff inactivity timeout: 30 minutes
* User inactivity timeout: 2 hours

### Responsibilities

* Registration and login
* Password verification and hashing
* Session issuance and rotation
* Inactivity timeout enforcement
* Role lookup and authorization
* CSRF protection for browser clients
* Audit logging of sensitive auth events

### Notes

The final implementation should choose one session strategy and use it consistently across all browser flows.

---

## 7.2 Campaign Management

Project Creators draft campaigns with:

* Target amount
* Fixed duration between 7 and 60 days
* Risk disclosures
* Reward tiers

Campaigns move through controlled lifecycle states.

### Campaign States

* Draft
* Pending Review
* Fundraising
* Success
* Failure
* Closed

### Responsibilities

* Create and edit campaign drafts
* Validate target amount and duration
* Manage risk disclosure content
* Submit for moderation
* Enforce state transitions
* Control publication visibility

Moderators decide whether a campaign can move into public fundraising visibility inside the local system.

---

## 7.3 Venue Programs and Seat Reservations

Users can reserve seats for on-site events by time slot. Availability is calculated using seat inventory, confirmed bookings, and temporary seat holds.

### Responsibilities

* Show available seats by time slot
* Create temporary seat holds
* Detect booking conflicts
* Release expired holds automatically
* Confirm reservation after successful order creation
* Generate a final confirmation number

### Seat Lock Rules

* Seat lock TTL: 5 minutes
* Locks expire automatically if not converted to an order
* Availability calculations exclude active locks and confirmed reservations

### UI Behavior

The booking UI uses Livewire to:

* Refresh availability without page reloads
* Clearly show when seats are temporarily held
* Show visible “processing” state during booking submission
* Prevent duplicate submission and refresh races

---

## 7.4 Order Lifecycle Management

Users manage orders from a unified screen. Orders may be created for:

* Crowdfunding contributions with reward tiers
* Event reservations with time-slot seating
* Combined fulfillment and voucher-based flows where applicable

### Supported Lifecycle Actions

* Create order
* Mark as paid via staff-logged offline tender
* Cancel within policy
* Request refund
* Request after-sales adjustment
* Track fulfillment milestones
* View voucher and confirmation data

### Responsibilities

* Maintain a canonical order record
* Enforce valid state transitions
* Present order progress clearly to users and staff
* Associate tenders, vouchers, logistics milestones, and requests

### Example Order States

* Draft
* Pending Payment
* Paid
* Reserved
* Fulfillment In Progress
* Fulfilled
* Cancelled
* Refund Requested
* Refunded
* Adjustment Requested
* Closed

---

## 7.5 Offline Tender Logging

There is no online payment integration. Payments are recorded by staff as offline tenders, such as cash or card-on-file.

### Responsibilities

* Record tender type
* Record payment amount and timestamp
* Associate staff identity with payment logging
* Link payment records to orders
* Issue in-app receipts and confirmations

Payment logging must be auditable and should not allow silent overwrites.

---

## 7.6 Cancellation, Refunds, and After-Sales Adjustments

The platform enforces explicit order policy windows.

### Policy Rules

* Cancellation allowed until 2 hours before scheduled start
* Refund requests allowed within 14 days of fulfillment
* After-sales requests require staff notes and an attachment

### Responsibilities

* Validate request timing
* Enforce policy restrictions
* Require necessary evidence and staff actions
* Maintain resolution history
* Update Notification Center with outcome changes

### Attachment Validation

After-sales attachments are validated by:

* Allowed file type
* Checksum
* Local validation only
* No cloud scanning dependency

---

## 7.7 Vouchers and On-Site Validation

Electronic vouchers are generated for eligible orders and displayed in the user interface. They can be scanned on-site.

### Responsibilities

* Generate voucher identifiers
* Link voucher to order and event context
* Support voucher display in the user account area
* Support scan/validate workflow for staff
* Prevent duplicate redemption

### Voucher States

* Issued
* Redeemed
* Expired
* Cancelled

---

## 7.8 Notification Center

The Notification Center is the platform’s internal messaging channel. It includes inbox items and on-screen alerts.

### Notification Sources

* Campaign review decisions
* Order state changes
* Receipts
* Voucher issuance
* Reminders
* Refund or adjustment outcomes
* Arbitration decisions
* Operational status updates relevant to the user

### Responsibilities

* Generate notifications from event subscriptions
* Render language-aware templates
* Render time-zone aware dates and times
* Track read/unread status
* Support inbox and immediate alert display

### Localization

Templates support language and time-zone aware rendering, such as:

* English receipts
* Spanish receipts
* Localized event times in user-preferred zone

No SMS, email, or push delivery is required.

---

## 7.9 Reviews and Anti-Retaliation Protections

Both Creators and Users may leave two-way reviews after fulfillment.

### Review Data

* Star dimensions
* Tags
* Text

### Anti-Retaliation Rules

* Public display delayed for 72 hours
* Public identities masked
* Internal views may show more detail to authorized staff

### Responsibilities

* Ensure reviews are only allowed after fulfillment
* Validate structured rating dimensions
* Delay public display automatically
* Mask identities in public-facing contexts
* Support moderation or abuse reporting if needed

---

## 7.10 Moderation and Visibility Control

Moderators control:

* Campaign approval
* Campaign visibility
* Workflow transitions
* Governance-sensitive operational changes where authorized

### Responsibilities

* Review pending campaigns
* Approve or reject visibility
* Enforce status rules
* Prevent unauthorized publication
* Track moderation notes and audit trail

Visibility should support local published visibility and internal-only or offline-hidden states as needed by operations.

---

## 7.11 Risk Control and Restrictions

The system calculates a credit or risk score using:

* No-shows
* Chargebacks and refunds
* Policy violations

It also flags anomalies such as:

* More than 3 refund requests in 30 days
* Multiple accounts on the same device fingerprint

### Restriction Types

* Gray restriction
* Black restriction

Example:

* Blacklisted users cannot place new orders for 90 days

### Responsibilities

* Record risk events
* Compute risk score
* Apply restrictions automatically where policy allows
* Surface warnings or blocks in booking/order flow
* Allow staff/admin review of flagged cases

---

## 7.12 Disputes and Arbitration Queue

Evidence-based disputes are routed into an arbitration queue. Decisions are stored with immutable logs.

### Responsibilities

* Open dispute records
* Attach evidence
* Route to authorized reviewers
* Record arbitration outcome
* Preserve append-only decision history
* Notify involved parties of results

Arbitration records should not be editable after finalization except through explicit superseding records.

---

## 7.13 Business Parameters and Integration Stubs

Administrators can configure:

* Cancellation windows
* Lock TTL
* Reminder lead times
* Dictionaries
* Template parameters
* Integration stub enable/disable flags

Webhook definitions may exist for future local connectivity, but are not required for core features.

### Responsibilities

* Manage business rule values without redeploy
* Version important operational settings
* Audit configuration changes
* Keep stub integrations local-only and disabled by default unless needed

---

## 8. Data Design

## 8.1 Core Entities

### User

Stores account and identity data.

Key fields:

* id
* username
* passwordHash
* role
* phoneEncrypted
* addressEncrypted
* timezone
* language
* status
* createdAt
* updatedAt

### Campaign

Represents a crowdfunding campaign draft or published fundraising record.

Key fields:

* id
* creatorId
* title
* description
* targetAmount
* durationDays
* riskDisclosure
* status
* visibility
* startsAt
* endsAt

### RewardTier

Represents contribution options within a campaign.

Key fields:

* id
* campaignId
* title
* description
* contributionAmount
* fulfillmentType
* quantityLimit
* status

### VenueProgram

Represents a reservable event or venue program.

Key fields:

* id
* title
* description
* location
* status

### TimeSlot

Represents a reservable event time range.

Key fields:

* id
* venueProgramId
* startsAt
* endsAt
* seatCapacity
* status

### SeatInventory

Tracks seat usage for a time slot.

Key fields:

* id
* timeSlotId
* totalSeats
* reservedSeats
* lockedSeats
* availableSeatsCached

### SeatLock

Represents temporary held seats.

Key fields:

* id
* userId
* timeSlotId
* quantity
* requestKey
* expiresAt
* status

### Order

Represents a user order across contribution/reservation workflows.

Key fields:

* id
* userId
* orderType
* status
* confirmationNumber
* totalAmount
* createdAt
* fulfilledAt
* cancelledAt

### OrderItem

Stores line items for reward tiers or seat reservations.

Key fields:

* id
* orderId
* itemType
* itemId
* quantity
* unitAmount
* metadata

### OfflineTender

Stores staff-recorded payment details.

Key fields:

* id
* orderId
* tenderType
* amount
* recordedBy
* recordedAt
* referenceNote

### Voucher

Represents an electronic voucher.

Key fields:

* id
* orderId
* code
* status
* issuedAt
* redeemedAt
* expiresAt

### LogisticsMilestone

Tracks local fulfillment progress.

Key fields:

* id
* orderId
* milestoneType
* status
* occurredAt
* notes

### RefundRequest

Represents a refund workflow.

Key fields:

* id
* orderId
* requestedBy
* reason
* status
* requestedAt
* resolvedAt

### AfterSalesRequest

Represents an adjustment or service request after sale.

Key fields:

* id
* orderId
* requestedBy
* status
* staffNotes
* attachmentPath
* attachmentChecksum
* createdAt

### Review

Represents a post-fulfillment review.

Key fields:

* id
* orderId
* authorId
* targetUserId
* ratingDimensions
* tags
* text
* publicVisibleAt
* publicIdentityMasked
* status

### Notification

Represents an inbox or alert entry.

Key fields:

* id
* userId
* templateKey
* payload
* channelType
* readAt
* createdAt

### NotificationTemplate

Stores template rules and localized variants.

Key fields:

* id
* key
* language
* subjectTemplate
* bodyTemplate
* isActive

### RiskEvent

Stores signals contributing to risk analysis.

Key fields:

* id
* userId
* eventType
* severity
* evidenceRef
* createdAt

### RiskRestriction

Stores applied user restrictions.

Key fields:

* id
* userId
* restrictionType
* startsAt
* endsAt
* reason
* status

### DeviceFingerprint

Stores local device association data.

Key fields:

* id
* userId
* fingerprintHash
* createdAt
* lastSeenAt

### Dispute

Represents an arbitration case.

Key fields:

* id
* orderId
* raisedBy
* reason
* status
* createdAt
* resolvedAt

### ArbitrationDecisionLog

Stores immutable decisions for dispute handling.

Key fields:

* id
* disputeId
* decidedBy
* outcome
* notes
* createdAt

### AuditLog

Stores security and workflow-sensitive records.

Key fields:

* id
* actorId
* action
* resourceType
* resourceId
* maskedDetails
* createdAt

### BusinessParameter

Stores editable configuration values.

Key fields:

* id
* key
* value
* valueType
* updatedBy
* updatedAt

### WebhookDefinition

Stores local-only integration stub configuration.

Key fields:

* id
* name
* endpoint
* enabled
* eventTypes
* updatedAt

### IdempotencyKey

Stores request-key deduplication data.

Key fields:

* id
* requestKey
* requestType
* requestHash
* responseSnapshot
* expiresAt

---

## 9. Workflow and State Design

## 9.1 Campaign State Machine

```text
Draft -> Pending Review -> Fundraising -> Success/Failure -> Closed
```

Rules:

* Only Draft campaigns may be edited freely by creators
* Only Pending Review campaigns may be approved by moderators
* Fundraising starts only after approval
* Success or Failure depends on campaign outcome policy
* Closed is terminal

## 9.2 Order State Machine

```text
Draft -> Pending Payment -> Paid -> Reserved/Fulfillment In Progress -> Fulfilled -> Closed
                          \-> Cancelled
                          \-> Refund Requested -> Refunded
                          \-> Adjustment Requested
```

Rules:

* Illegal transitions are rejected
* Cancellation only allowed until 2 hours before scheduled start
* Refund requests only allowed within 14 days of fulfillment
* After-sales requests require validated attachment and staff notes

## 9.3 Seat Lock Lifecycle

```text
Created -> Active -> Converted to Order
                 \-> Expired
                 \-> Released
```

Rules:

* Active locks reduce visible availability
* Locks expire after 5 minutes if not converted
* Expired locks return seats to inventory automatically

## 9.4 Voucher Lifecycle

```text
Issued -> Redeemed
      \-> Expired
      \-> Cancelled
```

## 9.5 Review Lifecycle

```text
Draft -> Submitted -> Held for Delay Window -> Publicly Visible
                              \-> Hidden/Flagged
```

Rules:

* Public display delayed 72 hours
* Public identity masking enabled by default

---

## 10. Booking Integrity and Availability

Availability is computed per time slot using:

* total seats
* confirmed reservations
* active seat locks

### Conflict Detection

On booking attempt:

1. Validate active time slot
2. Check requested quantity against current available seats
3. Create or refresh seat lock in a transaction
4. Convert to order if submission completes successfully
5. Release or expire lock if abandoned

### Idempotency

Booking submissions use a client-generated request key.
If a duplicate submission arrives:

* the original result is returned
* no second hold or duplicate order is created

This prevents duplicate reservations after refresh or repeated clicks.

---

## 11. Security Design

## 11.1 Authentication Security

* Local-only username/password login
* Argon2id password hashing
* Session rotation
* Inactivity timeouts by role
* CSRF protection for browser flows

## 11.2 Authorization

* Role-based access controls
* Sensitive data access based on explicit policy
* Masked UI rendering for unauthorized roles

## 11.3 Data Protection

Sensitive fields such as:

* phone numbers
* mailing addresses

are encrypted at rest and masked in UI unless the role is authorized.

## 11.4 Attachment Security

* File type validation
* Checksum validation
* Local storage only
* No cloud scanning
* Restricted access to evidence files

## 11.5 Device and Abuse Controls

* Local device fingerprint association
* Multiple-account anomaly detection
* Restriction enforcement for gray/black statuses

## 11.6 Auditability

All important actions must be logged, including:

* campaign submissions and approvals
* order and payment updates
* refund and adjustment decisions
* risk restriction changes
* dispute decisions
* business parameter changes

---

## 12. Notification and Localization Design

Notifications are generated by:

* template rules
* subscribed events
* business parameter timing

### Rendering Inputs

* user language
* user time zone
* event-specific payload
* template dictionary entries

### Example Uses

* receipt confirmation in English or Spanish
* localized event reminder times
* order status changes
* moderation outcomes

Notifications are persisted and shown both:

* in Notification Center inbox
* as on-screen alerts where appropriate

---

## 13. Reliability and Background Jobs

The system uses scheduled local jobs for:

* expiring seat locks
* sending reminder notifications
* promoting delayed reviews to public visibility
* recalculating risk scores if needed
* cleaning expired idempotency records
* operational maintenance tasks

All jobs run on local infrastructure and should be observable through internal operations tooling.

---

## 14. API and Interaction Design Overview

Although the UI is server-rendered with Livewire, the backend still exposes structured Laravel endpoints and internal service boundaries for:

* authentication
* campaigns
* reward tiers
* venue programs
* time slot availability
* seat locks
* orders
* tender logging
* vouchers
* refunds and after-sales adjustments
* reviews
* notifications
* risk restrictions
* disputes and arbitration
* admin configuration

### Interaction Characteristics

* CSRF-protected browser requests
* Livewire-driven validation and partial updates
* idempotent booking submission
* consistent error responses
* policy-driven authorization

---

## 15. Error Handling

Errors are grouped into:

* validation failures
* workflow/state transition violations
* authorization failures
* booking conflicts
* expired lock issues
* attachment validation failures
* restriction/risk blocks
* dispute submission errors
* configuration errors

### Principles

* show actionable user-facing messages
* avoid leaking sensitive internal details
* audit security-relevant failures
* distinguish retryable vs non-retryable outcomes

---

## 16. Key Design Decisions

### 16.1 Laravel + Livewire Instead of SPA

Chosen because:

* interactive flows are needed
* server-driven UI reduces frontend complexity
* validation and business logic remain centralized

Tradeoff:

* careful component state handling is required for complex booking flows

### 16.2 PostgreSQL as System of Record

Chosen because:

* strong transactional consistency
* suitable for state machines and audit-heavy workflows
* good support for local deployment and relational integrity

Tradeoff:

* careful indexing and lock strategy are needed for seat availability workloads

### 16.3 In-App Notification Center Instead of External Channels

Chosen because:

* offline-first operation is required
* multilingual templating is still possible without external delivery

Tradeoff:

* users must log into the system to receive updates

### 16.4 Client-Generated Request Keys for Idempotency

Chosen because:

* duplicate booking/hold prevention is critical
* refresh and retry scenarios must be safe

Tradeoff:

* server must retain short-lived request history and response snapshots

### 16.5 Immutable Arbitration Logs

Chosen because:

* governance decisions must be trusted
* later disputes require reliable historical evidence

Tradeoff:

* correction requires superseding records, not edits

---

## 17. Risks

### 17.1 Product Risks

* order lifecycle UI may become complex if too many workflows appear on one screen
* delayed public reviews may confuse users unless clearly explained
* creators and users may dispute masked-public review display if expectations are unclear

### 17.2 Technical Risks

* seat lock race conditions may occur without careful transaction and lock design
* Livewire component state may drift in long booking sessions if not refreshed properly
* local device fingerprint logic may create false positives in shared-device environments

### 17.3 Operational Risks

* staff accuracy is critical for offline tender logging
* improper local certificate management could weaken transport security
* arbitration queue volume may grow if restriction thresholds are too aggressive

---

## 18. Future Enhancements

Potential future additions include:

* richer venue seating maps
* more advanced reward fulfillment workflows
* optional on-prem webhook integrations
* expanded dispute analytics
* configurable review dimension templates
* internal reporting exports for finance and operations

---

## 19. Open Assumptions

This design assumes:

* staff and administrators are separate operational roles, even if implemented through permission bundles
* request-key idempotency is used for booking submissions and can be extended to other sensitive actions
* review public masking applies only to public views, not all internal staff screens
* local webhook definitions are configuration-only unless explicitly enabled later
* order confirmation numbers and voucher codes are distinct identifiers
* success/failure determination for campaigns follows local business rules defined by administrators