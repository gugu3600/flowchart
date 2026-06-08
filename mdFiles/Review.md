# Autonomous Code Review & Validation Checklist

> Last updated: 2026-06-08 12:00 UTC

## [Phase 1: Security & Route Protection]
- [x] Ownership verified: all CRUD controllers (`Flow`, `TableDefinition`, `LogicDefinition`) check `user_id` via `findForUser()`.
- [x] Register endpoint: `RegisterRequest` validates name/email/password; `RegisterService` hashes password + issues JWT.
- [x] `BaseController::error()` bug fixed — was checking `$errorMsg` instead of `$error` (always included empty `error` key).
- [x] `FlowController::saveEdges()` fixed — was returning `FlowNodeResource` instead of `FlowEdgeResource`.
- [x] `FlowService::saveEdges()` verified — correctly calls `$this->edgeRepository->deleteByFlowId()` (not node repo).
- [x] All models use `$fillable` whitelists (mass-assignment protection).
- [x] All JSON fields have `array` cast in models.
- [x] JWT cookie: `HttpOnly=true`, `Secure=true`, `SameSite=Strict`; logout blacklists token.
- [x] Password stored with `'hashed'` cast + `Hash::make()` (no double-hashing).
- [ ] **Tier Middleware Verification:** Inspect `routes/api.php` and verify that all routes accessing or compiling outputs are strictly wrapped inside respective tier stacks.
- [ ] **Rate Limiting:** Login and register routes are unthrottled — add `throttle:5,1` middleware.
- [ ] **Password Complexity:** Registration only requires `min:8` — add regex for uppercase+digit+special.
- [ ] **JWT Refresh Flow:** Cookie TTL (43200 min) vs JWT TTL (60 min) mismatch — add refresh-token mechanism.
- [ ] **Client-Side Separation:** Verify zero DB/SQL in frontend bundle.
- [ ] **Input Sanitization:** Ensure all custom node identifiers and parameters are validated.

## [Phase 2: Architectural & System Integrity]
- [ ] **State Integrity & Memory Leaks:** Verify that deleting nodes or edges inside the Vue Flow UI completely unmounts reactive parameters without leaving unreferenced leakage inside memory arrays.
- [ ] **Edge Parsing Security:** Inspect the current visual mapping parser. Ensure that visual connections between logic nodes and folder/file nodes save exactly as an explicit JSON relational graph without executing runtime script code injection flaws.
- [ ] **Idempotency Protection:** Verify that rapid duplicate transaction payloads or sync triggers from mobile network disconnects (via CapacitorJS) do not execute duplicate entry breaks inside the backend database.

## [Phase 3: Playwright E2E Testing]
- [x] Playwright v1.60.0 installed with Chromium project configured.
- [x] `playwright.config.js` configured with webServer (array: backend + frontend servers).
- [x] `tests/setup.spec.js` created — app mount, title check, console error tracking, health check.
- [x] `tests/login.spec.js` created — valid login + invalid credentials (2 tests, passing).
- [x] Reusable flowchart node components created: BaseNode, TableNode, LogicNode, FolderFileNode.
- [x] Canvas tests: flow selector, sidebar, create flow, save flow (2 tests, passing).
- [x] Canvas modes (Flow/Schema tabs) with `isValidConnection` domain boundary enforcement.
- [x] Fixed route model binding: `$tableDefinition` → `$table`, `$logicDefinition` → `$logic` to match route params.
- [ ] Write E2E tests for tier-gated features (403 paywall intercept).
- [ ] Write E2E tests for Table Designer and Logic Designer CRUD pages.

## [Phase 4: Infrastructure & Configuration]
- [x] `main` branch cleaned: docs/tests/root config removed from tracking.
- [x] `main` branch locked on GitHub: PR review required, force-push disabled.
- [x] `backend/config/cors.php` created — allows `localhost:3000` with credentials.
- [x] `frontend/src/api/apiClient.js` — axios with `withCredentials: true`, response unwrapper.
- [x] `frontend/src/views/Login.vue` — uses PrimeVue + Tailwind reusable components.
- [x] Frontend build passes.
