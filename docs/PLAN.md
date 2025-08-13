# PushAll Plan & Checklist

This is a living plan based on the PushAll rules (SaasyKit + Laravel 12 + FilamentPHP). Keep it updated as we progress.

## 0) Prereqs & Environment
- [ ] .env: set `APP_URL`, `APP_ENV`, `APP_KEY`, `SANCTUM_STATEFUL_DOMAINS`
- [ ] .env: Apple — `APPLE_PASS_CERT_PATH`, `APPLE_PASS_KEY_PATH`, `APPLE_PASS_PASSWORD`, `APPLE_TEAM_ID`, `APPLE_PASS_TYPE_ID`, `APPLE_WEB_SERVICE_URL`, `APPLE_WEB_SERVICE_AUTH`
- [ ] .env: Google — `GOOGLE_WALLET_CREDENTIALS`, `GOOGLE_WALLET_ISSUER_ID`, `GOOGLE_WALLET_ORIGINS`
- [ ] Queues: choose `database` or `redis`; configure driver and connection
- [ ] HTTPS enabled in local/test/prod (required for Wallet flows)

## 1) Data Model
- [ ] Generate model + migration for `WalletPass`
  - Columns (see rules): `id (uuid)`, `user_id`, `platform [apple|google]`, `type [generic|loyalty|offer|event]`,
    `serial_number`, `class_id`, `object_id`, `device_library_identifier`, `push_token`, `status [draft|active|revoked|expired]`, `meta json`, timestamps
- [ ] Migrate DB

Suggested commands:
```bash
php artisan make:model WalletPass -m
php artisan migrate
```

## 2) Admin (Filament)
- [ ] Scaffold `WalletPassResource` (forms, tables, filters)
- [ ] Table Actions: "Update Pass", "Revoke"
- [ ] Dashboard Widget: `PassStats` (stats-overview: active passes, installs, sends)

Suggested commands:
```bash
php artisan make:filament-resource WalletPass
php artisan make:filament-widget PassStats --stats-overview
```

## 3) Services (Business Logic)
- [ ] `app/Services/Wallet/ApplePassService.php` — methods: `create()`, `update()`, `notify()`
  - Build pass.json + assets; sign & package .pkpass; store under `storage/app/passes`
  - Include `webServiceURL` + `authenticationToken` inside pass
  - Push updates via APNs HTTP/2
- [ ] `app/Services/Wallet/GooglePassService.php` — methods: `create()`, `update()`, `notify()`
  - Create class/object (or JWT for "Add to Google Wallet")
  - Use REST v1 for updates; respect allowed `origins`

## 4) API Endpoints
- [ ] Sanctum-protected API for admin/app operations (standard CRUD as needed)
- [ ] Apple PassKit web service (no Sanctum; token header per spec) under base: `/wallet/apple`
  - [ ] GET  `/v1/devices/{deviceLibraryIdentifier}/registrations/{passTypeId}`
  - [ ] POST `/v1/devices/{deviceLibraryIdentifier}/registrations/{passTypeId}/{serial}`
  - [ ] GET  `/v1/passes/{passTypeId}/{serial}`
  - [ ] POST `/v1/log`
- [ ] Controllers thin; delegate to Services
- [ ] Form Requests for validation; uniform JSON: `{ data, meta, errors }`

## 5) Jobs & Queues
- [ ] `ApplePushJob` — queue APNs notifications with retry/backoff
- [ ] `GoogleUpdateJob` — queue object updates (REST v1)
- [ ] Configure queue worker + (prod) supervisor

Suggested commands:
```bash
php artisan make:job ApplePushJob
php artisan make:job GoogleUpdateJob
```

## 6) Assets & Storage
- [ ] Create `storage/app/passes` (ensure writable)
- [ ] Do NOT commit certificates/keys — keep under `config/certs/` or secure secret storage; reference via .env paths

## 7) Tests
- [ ] Feature tests: PassKit endpoints (mock auth + flows)
- [ ] Unit tests: ApplePassService/GooglePassService (mock external calls)
- [ ] Job tests: retry/backoff logic

## 8) Metrics & Logging
- [ ] Filament stats widget shows: active passes, installs, sends
- [ ] Log outbound calls (APNs / Google Wallet) with request/response IDs
- [ ] Add basic audit trail for admin actions (optional)

## 9) Acceptance Criteria
- [ ] Create pass from Filament and obtain:
  - Apple: downloadable `.pkpass` with valid `webServiceURL` + auth token
  - Google: working "Add to Google Wallet" link (valid JWT; allowed `origins`)
- [ ] iOS device registers via web service and receives update notification
- [ ] Android shows notification when object updates
- [ ] Failures logged; retries in place for jobs

---

## Execution Order (suggested)
1) Data Model → Migrate
2) Filament Resource + basic forms/tables
3) Services (Apple/Google) with create()
4) Apple PassKit web service endpoints
5) Jobs + notify() paths
6) Metrics widget and logging
7) Tests

## Notes
- Respect SaasyKit/Filament structure. Generate admin artifacts via Artisan (no manual placement).
- Keep controllers thin; push all wallet logic into Services.
- Enforce HTTPS and security headers.
