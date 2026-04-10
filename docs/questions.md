## 1. Encryption at Rest

– Question:  
The prompt states that sensitive fields are "encrypted at rest" but does not specify the encryption algorithm, key management strategy, or scope of encryption.

– Assumption:  
Sensitive fields (e.g., phone numbers, mailing addresses, device fingerprints) should be protected using strong industry-standard encryption with secure key handling.

– Solution:  
Implemented field-level encryption using AES-256-GCM. Encryption keys are stored in environment variables with restricted access. A key rotation policy (e.g., every 90 days) is assumed. All backups are encrypted using the same standard.

## 2. Data in Transit Security

– Question:  
The prompt does not specify how data is secured during transmission between client and server.

– Assumption:  
All communication between client and server must be protected against interception and tampering.

– Solution:  
Enforced HTTPS using TLS 1.2+ for all connections. Secure cookies and HSTS headers are enabled to prevent downgrade and man-in-the-middle attacks.

## 3. Authentication Hardening

– Question:  
Authentication is limited to username/password without mention of MFA, password policies, or brute-force protection.

– Assumption:  
Basic authentication should be strengthened to prevent unauthorized access.

– Solution:  
Implemented password complexity rules (minimum length, entropy requirements), login rate limiting, and account lockout after repeated failed attempts. MFA (optional) is supported for staff roles.

## 4. Authorization Model

– Question:  
Roles (User, Creator, Moderator, Admin) are mentioned, but no permission structure is defined.

– Assumption:  
Each role should have clearly defined permissions governing access to resources and actions.

– Solution:  
Implemented Role-Based Access Control (RBAC) with a permissions matrix defining allowed actions per role (e.g., campaign approval restricted to moderators, refund approval to staff).

## 5. Audit Logging

– Question:  
Audit trails are mentioned but without details on scope, structure, or immutability.

– Assumption:  
All critical actions must be logged for traceability and dispute resolution.

– Solution:  
Implemented append-only audit logs capturing user actions (orders, refunds, approvals, disputes). Logs are immutable and stored with timestamps and actor IDs. Retention policy assumed (e.g., 1–3 years).

## 6. Backup & Disaster Recovery

– Question:  
No backup or recovery strategy is defined despite local-only storage.

– Assumption:  
System must remain recoverable in case of hardware failure or data corruption.

– Solution:  
Implemented periodic encrypted backups (daily incremental, weekly full). Recovery procedures are defined and tested. Backups are stored securely on local redundant storage.

## 7. File Upload Security

– Question:  
File validation is mentioned (type and checksum), but limits and storage protections are not defined.

– Assumption:  
Uploaded files may pose security risks and must be controlled.

– Solution:  
Enforced file size limits, MIME-type validation, and checksum verification. Files are stored in a non-executable directory. Optional local antivirus scanning is assumed.

## 8. Device Fingerprinting

– Question:  
Device fingerprinting is referenced but not defined in terms of generation, storage, or privacy.

– Assumption:  
Fingerprinting is used for fraud detection while minimizing privacy risks.

– Solution:  
Generated hashed device fingerprints using browser/device attributes. Stored in encrypted form. Designed to avoid storing raw identifiable data.

## 9. Concurrency Handling

– Question:  
Seat locking is described, but database-level concurrency control is not specified.

– Assumption:  
Simultaneous booking attempts must not cause overbooking or race conditions.

– Solution:  
Used database transactions with appropriate isolation level (e.g., READ COMMITTED or SERIALIZABLE where needed). Implemented row-level locking and retry logic for conflicts.

## 10. Offline Payment Auditing

– Question:  
Offline payments are supported, but reconciliation and fraud tracking are not defined.

– Assumption:  
Manual payments must still be auditable and verifiable.

– Solution:  
Recorded all offline payment entries with staff ID, timestamp, and method. Implemented reconciliation reports and anomaly detection for suspicious patterns.

## 11. Notification Retention & Limits

– Question:  
Notification Center is defined but lacks retention policy and storage constraints.

– Assumption:  
Notifications should not grow indefinitely and must remain performant.

– Solution:  
Implemented retention policy (e.g., 90 days or configurable). Notifications include read/unread status and pagination for scalability.

## 12. Localization Edge Cases

– Question:  
Language and timezone support are mentioned, but formatting rules are not defined.

– Assumption:  
Users expect culturally correct formatting.

– Solution:  
Implemented locale-aware formatting for dates, times, and currencies. System supports multiple languages and extensible translation dictionaries.

## 13. Campaign Outcome Handling

– Question:  
The behavior for failed crowdfunding campaigns is not defined.

– Assumption:  
Users should not lose funds if funding goals are not met.

– Solution:  
Defined rules: if target is not reached, campaign is marked as failed and orders are eligible for refund or cancellation per policy.

## 14. Testing Strategy

– Question:  
No testing or validation approach is described.

– Assumption:  
System reliability requires structured testing.

– Solution:  
Implemented unit tests, integration tests, and load testing for critical flows (e.g., seat booking, concurrency). Validation includes edge cases and failure scenarios.
