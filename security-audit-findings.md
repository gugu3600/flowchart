# Security Audit Findings — Flowchart App

## Methodology
- Full source review of frontend (Vue 3 + Pinia) and backend (Laravel 13 + JWT)
- 4 vectors: Authentication, Input Validation, Session Management, Infrastructure
- Cross-referenced between frontend routes/stores and backend routes/controllers/services

---

## Vector 1: Authentication & Authorization Flaws

### 1.1 Subscription endpoint has no payment processing (mock payment)

**File:** `backend/app/Http/Controllers/api/AuthController.php:74-111`

The `POST /api/subscribe` endpoint accepts `tier` and `payment_method`, validates them via `Rule::in([...])`, but **never processes the payment**. The `payment_method` is discarded after validation. Any authenticated user can call this endpoint repeatedly to:
- Upgrade to any tier without paying
- Extend their subscription expiry indefinitely by calling subscribe again before expiry

```php
$user->syncRoles([$tierRole]);  // Lines line 88
// payment_method validated but never used — no actual charge
```

**Fix:** Integrate a real payment gateway before granting tier upgrades, or at minimum gate subscription changes behind admin action in production.

### 1.2 No downgrade protection / tier downgrade attack

**File:** `backend/app/Http/Controllers/api/AuthController.php:74-111`

A user on the Platinum tier can call `POST /api/subscribe` with `tier: silver` and instantly lose Platinum features with no confirmation or admin approval. The `syncRoles()` call replaces all roles unconditionally.

**Fix:** Only allow upgrades (compare current tier rank vs requested tier), or require admin approval for downgrades.

### 1.3 Subscription expiry race condition

**File:** `backend/app/Http/Controllers/api/AuthController.php:96-103`

Two concurrent subscribe requests from the same user can race:
1. Request A reads `subscription_expires_at` (null / past date)
2. Request B reads `subscription_expires_at` (same)
3. Both set `$base` to `now()` and add days
4. Result: only one extension applied instead of two

```php
$currentExpiry = $user->subscription_expires_at;
$base = ($currentExpiry && $currentExpiry->isFuture()) ? $currentExpiry : $now;
$user->subscription_expires_at = $base->copy()->addDays($days);
$user->save();
```

**Fix:** Use a database transaction with `lockForUpdate()` or an atomic increment.

### 1.4 Super-admin role can be assigned via admin upgrade

**File:** `backend/app/Http/Controllers/api/AdminController.php:119-151`

The `upgrade()` method permits any role *except* super-admin. However, `updateRoles()` at line 76 validates against **all** API-guard roles, which could include super-admin if it exists:

```php
'roles.*' => ['string', Rule::in(Role::where('guard_name', 'api')->pluck('name'))],
```

If the super-admin role exists in the DB, the Rule::in check passes. This would let a compromised super-admin grant super-admin to another user. This is limited by the `role:super-admin` middleware on the route, so only an existing super-admin can reach this code — but it lowers the bar for lateral movement.

**Fix:** Explicitly exclude super-admin from the allowed roles list in `updateRoles()`.

### 1.5 No email verification

Users can register and immediately access all authenticated features with an unverified email. There is no `email_verified_at` check in any controller or middleware.

**Fix:** Implement email verification and gate sensitive operations (subscribe, admin access) behind verified emails.

### 1.6 Frontend route guard only checks on navigation

**File:** `frontend/src/router/index.js:65-86`

The `beforeEach` guard calls `fetchUser()` which hits `GET /api/me`. If it fails, `user` is set to `null` and the guard effectively blocks protected routes via redirect. However, the `/canvas`, `/subscribe`, `/tables`, `/logics` routes have **no `meta: { requiresAuth: true }`** marker. The guard only checks `to.meta.requiresAdmin` (line 79). This means:
- Unauthenticated users can technically **navigate** to `/canvas`, `/subscribe`, etc. (they'll see loading state, empty data, or errors from failed API calls)
- The backend's `auth:api` middleware is the real gate, so no data is actually leaked

**Severity:** Low — backend enforces auth, but UX is confusing.

### 1.7 Rate limiting on login/register

**File:** `backend/app/Providers/AppServiceProvider.php:19-21`

Rate limiter is set to 20 req/min keyed by email or IP. This is **adequate** against brute force.

---

## Vector 2: Input Validation, Injection, & XSS

### 2.1 Strong password policy

**File:** `backend/app/Http/Requests/Auth/RegisterRequest.php:19`

```php
'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
```

Requires uppercase, lowercase, digit, and special character with min 8 chars. **Good.**

### 2.2 SQL injection prevented by Eloquent ORM

All database queries use Eloquent's parameterized queries. No raw SQL or `DB::raw()` found in any controller or service. **Safe.**

### 2.3 Stored XSS via node/edge labels

**File:** `frontend/src/views/Canvas.vue:108-115, 249-258`

User-controlled `label` values from saved flows are passed directly into VueFlow node data:

```javascript
data: { label: n.label, ...(n.data || {}) },
```

Vue 3's template rendering auto-escapes HTML in text interpolation (`{{ }}`), so basic `<script>` injection is blocked. However, if a custom node component uses `v-html` anywhere (see Vector 2.6), this becomes exploitable. Currently no `v-html` usage was found in `BaseNode.vue` / `TableNode.vue` / `LogicNode.vue`.

**Severity:** Low (Vue auto-escapes, no v-html found).

### 2.4 No HTML sanitization on labels (defense in depth)

Even though Vue auto-escapes, stored labels with malicious content (e.g., CSS injection, extremely long strings for DoS) are stored as-is. A future change that adds `v-html` would immediately create an XSS vector. Labels should be sanitized on write (backend) and read (frontend).

**Fix:** Strip or encode HTML entities in labels on the backend before persisting.

### 2.5 Mass assignment protection on JSON columns

**File:** `backend/app/Models/TableDefinition.php:16-20`
**File:** `backend/app/Http/Requests/TableDefinition/StoreTableDefinitionRequest.php:18-24`

The `columns` field is validated for `name`, `type`, `pk`, `fk`, `unique` keys, but the JSON `columns` array can contain arbitrary extra keys which will be stored via the `array` cast. Not exploitable for privilege escalation but allows storing junk data.

**Severity:** Low.

### 2.6 Node components using v-html (potential XSS sink)

Need to check `BaseNode.vue`, `TableNode.vue`, `LogicNode.vue`, `FolderFileNode.vue` for `v-html` usage. These were read by the subagent but the contents were the same as previously read. None of them use `v-html`. **Safe currently.**

### 2.7 JWT cookie auth via middleware

**File:** `backend/app/Http/Middleware/JwtCookieMiddleware.php:13-14`

```php
if ($token = $request->cookie('jwt_token')) {
    $request->headers->set('Authorization', "Bearer $token");
}
```

The middleware reads the JWT from the cookie and sets it as a Bearer token for the `auth:api` guard. This is a clean pattern that prevents XSS-based token theft (HttpOnly cookie).

### 2.8 No path traversal or file upload vectors

No file upload, storage, download, or path manipulation endpoints exist. No `Storage::disk()` or file operations were found in any controller.

---

## Vector 3: Session Management Weaknesses

### 3.1 Excessively long JWT TTL (30 days)

**File:** `backend/.env:70`
```
JWT_TTL=43200  # 43,200 minutes = 30 days
```

A 30-day token lifetime means:
- If a user's cookie is stolen (via physical access, malware, or proxy), the attacker has 30 days of access
- Password change does not invalidate existing JWTs (no mechanism for this)
- No refresh token rotation: the long-lived JWT serves as both access and refresh token

**Fix:** Reduce to 15–60 minutes and implement refresh token rotation.

### 3.2 JWT blacklist grace period = 0

**File:** `backend/config/jwt.php:235`
```php
'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),
```

This means logout immediately invalidates the token. **Good.**

### 3.3 No refresh token mechanism

When a JWT expires after 30 days, the user is simply logged out and must re-authenticate. This is fine given the 30-day TTL, but if the TTL is reduced (as recommended), a refresh token flow is needed.

### 3.4 Cookie secure flag depends on env

**File:** `backend/app/Http/Controllers/api/AuthController.php:25`
```php
$secure = config('app.env') === 'production';
```

The cookie's `Secure` flag is only set when running in production. This is correct behavior (allows HTTP for local development), but `.env` has `APP_ENV=local` committed, meaning in any deployed environment, the operator must remember to set `APP_ENV=production`. If forgotten, the JWT cookie is sent over unencrypted HTTP.

**Severity:** Medium (configuration-dependent).

### 3.5 SameSite=Strict prevents CSRF

The cookie is set with `samesite='Strict'`, preventing CSRF attacks via cross-site form submissions. The `withCredentials: true` in the frontend axios client works correctly with this.

---

## Vector 4: Infrastructure & Configuration Risks

### 4.1 [CRITICAL] .env committed to repository with secrets

**File:** `backend/.env`

```
DB_PASSWORD=Jukonjukon1@
JWT_SECRET=DfyYjM6Vm6I7kpyKK3JzY00Oj65lC8ddzvgXCO04RI0MxJBbnjTYeaOO3wdpomfA
APP_KEY=base64:aEansDnSjruy1s/51j6uALwNSFsPSwC6WQ86shI8dBI=
```

**Risk:** Anyone with repo access has the database root password and JWT signing secret. This allows:
- Direct database connection (if exposed)
- Forging valid JWTs as any user
- Reading all user data, flow data, credentials

**Fix:** Remove `.env` from git tracking (`git rm --cached`), rotate all secrets, add `.env` to `.gitignore`.

### 4.2 [HIGH] APP_DEBUG=true leaks stack traces

**File:** `backend/.env:4`
```
APP_DEBUG=true
```

In production, this leaks full stack traces, file paths, query parameters, and environment variables on errors. This aids attackers in reconnaissance.

**Fix:** Set to `false` in production and ensure `.env` is not in the repo.

### 4.3 [HIGH] DB root password exposed

**File:** `backend/.env:28`
```
DB_PASSWORD=Jukonjukon1@
```

MySQL root password stored in plaintext in version control.

**Fix:** Remove from version control, use environment variables or a secrets manager in production.

### 4.4 CORS configuration is reasonable

**File:** `backend/config/cors.php`
- Single allowed origin (FRONTEND_URL)
- Credentials enabled
- All methods/headers allowed (acceptable for API)

**Safe** as long as `FRONTEND_URL` is set to a controlled domain in production.

### 4.5 No concurrency protection on flow creation

**File:** `backend/app/Services/Flow/FlowService.php:112-125`

`checkFlowLimit()` reads the flow count and compares it against the limit, but does not use a database transaction or lock. Two simultaneous requests from a Silver user at the limit (5 flows) could both pass the check before either insert completes, resulting in 6+ flows.

```php
$count = $this->flowRepository->countForUser($userId);  // Both requests see 5
if ($count >= self::SILVER_MAX_FLOWS) {                  // Both pass
    abort(403, ...);
}
```

**Fix:** Use a database unique constraint on (user_id, something) or a transaction with `sharedLock`/`lockForUpdate`.

### 4.6 SQLite transaction mode is DEFERRED

**File:** `backend/config/database.php:44`
```
'transaction_mode' => 'DEFERRED',
```

This means transactions only acquire locks when the first write statement executes. Combined with the lack of explicit locking in flow creation (4.5), this increases the race window.

---

## Severity Summary

| # | Finding | Severity | Category |
|---|---------|----------|----------|
| 4.1 | .env with secrets committed to repo | **CRITICAL** | Infrastructure |
| 1.1 | Subscribe endpoint mock/no payment | **HIGH** | Auth |
| 4.2 | APP_DEBUG=true leaks stack traces | **HIGH** | Infrastructure |
| 4.3 | DB root password in source control | **HIGH** | Infrastructure |
| 3.1 | 30-day JWT TTL | **MEDIUM** | Session Mgmt |
| 1.3 | Subscribe race condition | **MEDIUM** | Auth |
| 1.4 | Super-admin role not excluded from allowed list | **MEDIUM** | Auth |
| 4.5 | Flow creation race condition | **MEDIUM** | Infrastructure |
| 3.4 | Cookie Secure flag env-dependent | **MEDIUM** | Session Mgmt |
| 1.5 | No email verification | **MEDIUM** | Auth |
| 1.2 | No downgrade protection | **LOW** | Auth |
| 1.6 | Missing frontend route auth guards | **LOW** | Auth |
| 2.3 | Stored XSS (Vue auto-escapes currently) | **LOW** | Input Validation |
| 2.5 | Extra keys in JSON columns | **LOW** | Input Validation |

---

## Fix Status

| # | Finding | Severity | Status |
|---|---------|----------|--------|
| 4.1 | .env with secrets committed to repo | **CRITICAL** | ✅ **False positive** — `.env` is gitignored, never tracked |
| 1.1 | Subscribe endpoint mock/no payment | **HIGH** | ❌ Not yet fixed (requires payment gateway integration) |
| 4.2 | APP_DEBUG=true leaks stack traces | **HIGH** | ✅ `APP_DEBUG=false` in `.env.example` |
| 4.3 | DB root password in source control | **HIGH** | ✅ **False positive** — not committed (gitignored) |
| 3.1 | 30-day JWT TTL | **MEDIUM** | ✅ Reduced to 60 min, refresh endpoint added |
| 1.3 | Subscribe race condition | **MEDIUM** | ✅ Wrapped in `DB::transaction` with `lockForUpdate()` |
| 1.4 | Super-admin role not excluded from allowed list | **MEDIUM** | ❌ Not yet fixed |
| 4.5 | Flow creation race condition | **MEDIUM** | ✅ Wrapped in `DB::transaction` with `lockForUpdate()` |
| 3.4 | Cookie Secure flag env-dependent | **MEDIUM** | ❌ Requires production deployment config |
| 1.5 | No email verification | **MEDIUM** | ❌ Requires full email verification flow |
| 1.2 | No downgrade protection | **LOW** | ❌ Not yet fixed |
| 1.6 | Missing frontend route auth guards | **LOW** | ✅ `requiresAuth` meta added to all protected routes |
| 2.3 | Stored XSS (Vue auto-escapes currently) | **LOW** | ✅ `strip_tags()` on all labels in backend before persisting |
| 2.5 | Extra keys in JSON columns | **LOW** | ❌ Not yet fixed |

### Additional fix: Cross-type node deletion in save

**Root cause:** `FlowService::save()` called `$this->nodeRepository->deleteByFlowId($flowId)` which deleted ALL nodes for the flow regardless of type. Saving in flow mode (logic/folderFile) would destroy any existing schema-mode (table) nodes and vice versa.

**Files changed:** `backend/app/Services/Flow/FlowService.php`, `backend/app/Repositories/flow_node/FlowNodeRepository.php`, `backend/app/Repositories/flow_edge/FlowEdgeRepository.php`, `frontend/src/views/Canvas.vue`

**Fix:** Save operations now use `deleteByFlowIdAndTypes()` which only deletes nodes matching the payload's node types. Edge deletion uses `deleteByNodeIds()` to only remove edges referencing the deleted nodes. Frontend filter node response by current mode after save.

| # | Finding | Severity | Status |
|---|---------|----------|--------|
| — | Cross-type node deletion on save | **HIGH** | ✅ Type-aware deletion implemented in save/saveNodes/saveEdges; frontend filters response by mode |

## Remaining Fixes

1. **High:** Implement real payment gateway or gate subscribe endpoint to admin approval
2. **Medium:** Exclude super-admin from allowed roles in `updateRoles()` validation
3. **Medium:** Require `APP_ENV=production` check for Secure cookie flag
4. **Medium:** Add email verification requirement for authenticated features
5. **Low:** Prevent downgrade via subscribe endpoint (only allow upgrades)
6. **Low:** Validate extra keys in JSON columns (table columns, etc.)
