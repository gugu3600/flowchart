# Autonomous Code Review & Validation Checklist

> Last updated: 2026-06-10 11:30 UTC

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
- [x] Register.vue: tier selection, payment methods, password toggle, router-link
- [x] Login.vue: remember me checkbox, password toggle, register link
- [x] FloatingInput: showPasswordToggle, hasError props
- [x] Payment methods fetched from backend (GET /api/payment-methods), not hardcoded
- [x] Canvas.vue: free tier upgrade banner, silver+ slot bar, color toolbar
- [x] Backend: flow count limit (5 for silver, 999 for gold+), flow_count/max_slots in index
- [x] Backend: logic CRUD routes — removed permission::gate, all tiers can create
- [x] Backend: free tier logic limit (max 4) enforced in LogicDefinitionService
- [x] Backend: logic_count/max_slots returned in LogicDefinitionController@index
- [x] Backend: subscription_expires_at column on users table (migration added)
- [x] Backend: upgrade sets subscription durations (silver 33d, gold 37d, platinum 44d)
- [x] Frontend: LogicDesigner.vue free banner, logic slot counter, upgrade modal on limit
- [x] Frontend: API base URL moved to `.env` (VITE_API_BASE_URL), `||` fallback removed from apiClient.js, `.env.example` created
- [x] Frontend: ColumnBuilder.vue extracted as reusable component from TableDesigner.vue
- [x] Frontend: TableDesigner.vue permission-gated — create/edit/delete hidden for users without `generate-schema` (Gold+)
- [x] Store: added `canGenerateSchema` computed property
- [x] **Tested 2026-06-10**: Free registration works, logic limit 4/4 enforced (5th=403), table creation blocked for free (403 perms)
- [x] **Tested 2026-06-10**: Gold/platinum — node color, edge color, table CRUD, unlimited logics — 25 total tests passing
- [x] **Tested 2026-06-10**: Route guard refactored — return-only (no next()), login 409 for already-authenticated users, adminGuard simplified
- [x] **Tested 2026-06-10**: Color reactivity fix — setNodeColor/setEdgeColor mutate nodes.value/edges.value directly; backend-first save flow
- [x] **Tested 2026-06-10**: Insert bugfix — FlowNodeRepository/FlowEdgeRepository json_encode JSON columns for bulkCreate (bypasses Eloquent casts)
- [x] **Tested 2026-06-10**: Self-service subscribe — free user upgrades to silver via POST /api/subscribe, role + subscription_expires_at set
- [x] **Tested 2026-06-10**: Seeded tier demo users — freeuser, silveruser, golduser, platinumuser (all password: "password"), idempotent seeder
- [x] **Tested 2026-06-10**: Full suite 26/26 tests passing (14 setup/login/canvas/admin + 12 tier-colors/subscribe)
- [x] **Rate Limiting:** Login and register routes throttled 20/min.
- [x] **Password Complexity:** Registration requires uppercase+digit+special char regex.
- [x] **JWT Refresh Flow:** Cookie TTL synced to JWT_TTL; POST /api/refresh endpoint; 401 interceptor with queue-based retry.
- [x] **Input Sanitization:** strip_tags() on all node, edge, and flow labels before persist.
- [x] **Frontend Auth Guards:** requiresAuth meta + authGuard() on /subscribe, /canvas, /tables, /logics.
- [x] **Cross-type node deletion:** save()/saveNodes()/saveEdges() use type-aware deletes.
- [x] **DB Transactions:** subscribe() and flow create() wrapped with lockForUpdate.
- [x] **ModeTabs, ColorSwatchPalette, useFlowMapper:** extracted and tested (build passes).
- [x] **Color tools visual update:** setNodeColor/setEdgeColor now update node.style/edge.style immediately (not just config).
- [x] **Duplicate edge prevention:** isValidConnection rejects connections where source+target already have an edge.
- [x] **Definition loading without flow:** loadFlowData(null) called from onMounted when no flow selected — logics/tables visible to all users regardless of tier or saved-flow status.
- [ ] **Tier Middleware Verification:** Inspect `routes/api.php` and verify that all routes accessing or compiling outputs are strictly wrapped inside respective tier stacks.
- [ ] **Client-Side Separation:** Verify zero DB/SQL in frontend bundle.

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
- [x] Cross-mode node filtering: `loadFlowData` skips table nodes in Flow mode, logic/folder nodes in Schema mode.
- [x] Drop validation: `onDrop` rejects items whose type doesn't match the active mode.
- [x] Edge/node deletion: `delete-key-code` prop + `@edges-delete` / `@nodes-delete` handlers remove selected edges and nodes on Delete/Backspace.
- [x] Fixed route model binding: `$tableDefinition` → `$table`, `$logicDefinition` → `$logic` to match route params.
- [x] E2E tests for tier-gated features (gold/platinum colors, table CRUD, unlimited logics) — 11 tier-colors tests passing
- [ ] Write E2E tests for Table Designer and Logic Designer CRUD pages.

## [Phase 4: Infrastructure & Configuration]
- [x] `main` branch cleaned: docs/tests/root config removed from tracking.
- [x] `main` branch locked on GitHub: PR review required, force-push disabled.
- [x] `backend/config/cors.php` created — allows `localhost:3000` with credentials.
- [x] `frontend/src/api/apiClient.js` — axios with `withCredentials: true`, response unwrapper.
- [x] `frontend/src/views/Login.vue` — uses PrimeVue + Tailwind reusable components.
- [x] Frontend build passes.
