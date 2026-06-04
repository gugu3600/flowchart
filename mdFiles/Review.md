# Autonomous Code Review & Validation Checklist

## [Phase 1: Security & Route Protection]
- [ ] **Tier Middleware Verification:** Inspect `routes/api.php` and verify that all routes accessing or compiling outputs are strictly wrapped inside respective tier stacks (`role:silver|gold|platinum`, `role:gold|platinum`, `role:platinum`).
- [x] JWT auth routes configured (`/api/login`, `/api/register`, `/api/me`, `/api/logout`).
- [x] Super-admin role seeded with all permissions for development access.
- [x] Controllers refactored: validation → FormRequest, business logic → Service, DB → Repository.
- [x] RepositoryServiceProvider binds all interfaces → implementations.
- [ ] **Client-Side Separation:** Inspect frontend imports and build bundles. Ensure absolutely zero database connections or raw SQL queries are linked into the Vue client bundle to prevent client-side bypasses.
- [ ] **Input Sanitization:** Check Laravel controller validation arrays. Ensure all custom node identifiers, route configurations, folder names, and database layout parameters are fully validated and sanitized to protect against payload manipulation.

## [Phase 2: Architectural & System Integrity]
- [ ] **State Integrity & Memory Leaks:** Verify that deleting nodes or edges inside the Vue Flow UI completely unmounts reactive parameters without leaving unreferenced leakage inside memory arrays.
- [ ] **Edge Parsing Security:** Inspect the current visual mapping parser. Ensure that visual connections between logic nodes and folder/file nodes save exactly as an explicit JSON relational graph without executing runtime script code injection flaws.
- [ ] **Idempotency Protection:** Verify that rapid duplicate transaction payloads or sync triggers from mobile network disconnects (via CapacitorJS) do not execute duplicate entry breaks inside the backend database.

## [Phase 3: Playwright E2E Testing]
- [x] Playwright v1.60.0 installed with Chromium project configured.
- [x] `playwright.config.js` configured with `webServer` pointing to `localhost:3000`.
- [x] `tests/setup.spec.js` created — app mount, title check, console error tracking, health check.
- [ ] Write E2E tests for Canvas.vue (node drag-drop, edge connection, node deletion).
- [ ] Write E2E tests for authentication flow (login, token storage, protected routes).
- [ ] Write E2E tests for tier-gated features (403 paywall intercept).
