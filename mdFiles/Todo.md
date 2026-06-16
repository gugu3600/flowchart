# MVP Implementation Roadmap (Value-Tier Focus)

> Last updated: 2026-06-16 21:00 UTC

## Completed
- [x] **FEAT: Admin resource pages** — Separate `/admin/logics`, `/admin/tables`, `/admin/flows` pages with `AdminResourceTable` reusable component, navigation cards on dashboard
- [x] **BACKEND: Admin resource endpoints** — `GET /admin/logics`, `GET /admin/tables`, `GET /admin/flows` returning all records with owner info, gated by super-admin role
- [x] **REFACTOR: AdminService + FormRequest validation** — `AdminController` now delegates to `AdminService` (repos injected), validation in FormRequest classes, `all()` method added to Flow/Logic/Table repos
- [x] **FEAT: Admin nav cards** — Each admin resource view now has styled resource-card nav buttons matching the dashboard design, with active state indicator
- [x] **TEST: Admin resource pages** — Playwright test creating resources as gold user, verifying admin can see them with correct owners on all three pages

## [Phase 1: Core Canvas & Silver Tier Feature]
- [x] Initialize Vue 3 layout with `@vue-flow/core` integration and Tailwind styling elements.
- [x] Setup Laravel 13 backend structure with MySQL (JWT auth + Spatie RBAC).
- [x] Create custom artisan commands (`make:service`, `make:repository`) for repository/service pattern.
- [x] Configure openspec change logging (`openspec/changes/archive/logs.md`).
- [x] Set up Playwright E2E test framework for frontend UI testing.
- [x] Design and migrate `flowchart` database schema (3NF: flows, flow_nodes, flow_edges).
- [x] Document DB schema in `mdFiles/schema.md` (3NF verification PASSED).
- [x] Create Eloquent models: Flow, FlowNode, FlowEdge with all relationships.
- [x] Create API controllers: AuthController, FlowController.
- [x] Create RoleAndPermissionSeeder: super-admin (all perms), free, silver, gold, platinum tier roles.
- [x] Seed default Super Admin user (admin@flowchart.dev / password).
- [x] Refactor to Repository/Service pattern: Form Requests, Repositories, Services, DI bindings.
- [x] API Resources (UserResource, FlowResource, FlowNodeResource, FlowEdgeResource) for clean responses.
- [x] JWT moved to HTTP-only Secure SameSite=Strict cookie (XSS-safe). Removed `Auth::guard('api')` redundancy.
- [x] BaseController response wrapper fixed (`success`/`status`/`message`/`data` keys).
- [x] Install axios + vue-router + setup apiClient with response interceptor.
- [x] Install Tailwind CSS v4 + PrimeVue 4 with reusable components (AppCard, AppButton, AppInput, AppNavbar).
- [x] Create Login.vue page with reusable components.
- [x] Playwright E2E tests for login flow (valid + invalid credentials) — 2/2 passing.
- [x] Push main (clean), dev, test branches. Lock main branch with branch protection.
- [x] Install `@vue-flow/core` and create reusable flowchart node components (TableNode, LogicNode, FolderFileNode, BaseNode).
- [x] Build combined save endpoint (`POST /api/flows/{flow}/save`) with node→edge ID mapping.
- [x] Create Sidebar.vue drag-and-drop palette (Table, Logic, Folder/File node types).
- [x] Build Canvas.vue with flow selector, create/save flows, drag-drop, markRaw, onConnect.
- [x] Create Table Designer page (`/tables`) — CRUD table schemas with column builder.
- [x] Create Logic Designer page (`/logics`) — CRUD logic definitions with inputs/output builder.
- [x] Canvas auto-loads table/logic definitions as nodes with definitionId tracking.
- [x] Canvas has two modes (Flow/Schema tabs) — tables and logics cannot mix; `isValidConnection` enforces boundaries.
- [x] Cross-mode node filtering: saved nodes filtered by mode on load (tables skipped in Flow, logics/folders skipped in Schema).
- [x] Cross-mode definition isolation: definition nodes (logics for flow, tables for schema) only load in their respective mode via `loadFlowData` if/else branch.
- [x] Drop validation: `onDrop` rejects items whose type doesn't match the active mode.
- [x] Connection validation: `isValidConnection` enforces type boundaries + duplicate edge prevention.
- [x] Save response mode-filtering: `filterByMode()` strips non-current-mode nodes from save response.
- [x] Mode switch reload: `switchMode()` always calls `loadFlowData` to clear old nodes and load new mode's data.
- [x] Edge/node deletion via Delete/Backspace key with `delete-key-code` prop.
- [x] Help guide page (`/help`) with usage instructions and keyboard shortcuts.
- [x] Logic inputs upgraded to structured `{name, type}` objects (workflow-style CRUD like table columns).
- [x] Register.vue — user registration page with route, linked from login page.
- [x] Fixed critical: `saveEdges` returned `FlowNodeResource` instead of `FlowEdgeResource`.
- [x] Fixed critical: `BaseController::error()` checked `$errorMsg` instead of `$error`.
- [x] Added `node-tag-slate` class to style.css used by LogicNode.
- [x] Wrapped `Canvas.vue` onDrop `JSON.parse` in try/catch to prevent crash on invalid drops.
- [x] Added `deleting` loading state to both designer pages.
- [x] TableDesigner: validates both `c.name` AND `c.type` before submit; guards null columns.
- [x] **SECURITY: Add rate limiting** to `/api/login` and `/api/register` routes (throttle: 20 attempts/min).
- [x] **SECURITY: Enforce password complexity** — require uppercase+digit+special char in RegisterRequest.
- [x] Register.vue redesigned with tier selection (Free/Silver/Gold/Platinum) and pricing display
- [x] Payment method selection (KBZ Pay, AYA Pay, CB Pay, MMQR) for paid tiers during registration
- [x] Backend `/api/register` re-enabled — assigns selected tier role, accepts payment_method
- [x] Extracted reusable components from Register.vue: FloatingInput, TierSelector, PaymentMethodPicker
- [x] Login.vue also uses FloatingInput component
- [x] GET /api/payment-methods endpoint (mock) — payment methods fetched from backend, not hardcoded in frontend
- [x] FloatingInput: show/hide password toggle, error state (red border)
- [x] Login.vue: "Remember me" checkbox, show password toggle
- [x] Register.vue: show password toggle for both password fields, `<router-link>`
- [x] Login.vue: added "Don't have an account? Register" link
- [x] Free tier: save/create hidden, upgrade banner shown
- [x] Silver tier: 5-slot save limit, node background color picker, edge stroke color picker
- [x] Backend: flow count limit (5 for silver, unlimited gold+), max_slots/flow_count in index
- [x] Remove permission gate from logic CRUD routes — all tiers can create logics
- [x] Backend: logic count limit for free tier (max 4), logic_count/max_slots in index
- [x] Backend: migration — add subscription_expires_at column to users table
- [x] Backend: subscription durations (silver 33d, gold 37d, platinum 44d) set on upgrade
- [x] Frontend: LogicDesigner.vue free banner, logic slot counter, upgrade modal on limit
- [x] Frontend: API base URL moved to `.env` (VITE_API_BASE_URL), `||` fallback removed from apiClient.js, `.env.example` created
- [x] Frontend: ColumnBuilder.vue extracted from TableDesigner.vue
- [x] Frontend: TableDesigner.vue permission-gated — requires `generate-schema` permission (Gold+) for create/edit/delete
- [x] Store: added `canGenerateSchema` computed property
- [x] **Tested 2026-06-10**: Free register, logic limit (4 max), table 403 for free — all passing
- [x] **Tested 2026-06-10**: Gold/platinum — node color, edge color, table CRUD (generate-schema), unlimited logics — all 11 tier-colors tests passing (25 total)
- [x] **Tested 2026-06-10**: Subscribe backend endpoint — free user upgrades to silver via /api/subscribe
- [x] Upgrade modal — added Subscribe button per tier card (navigates to /subscribe?tier=)
- [x] Subscribe page (/subscribe) — tier cards, payment method picker, subscribe button, auto-redirect to canvas
- [x] Backend POST /api/subscribe — self-service tier upgrade with payment_method validation, sets subscription_expires_at
- [x] Seeded tier demo users: freeuser@mail.com, silveruser@mail.com, golduser@mail.com, platinumuser@mail.com (all password: "password")
- [x] Draggable node resize — NodeResizer from @vue-flow/node-resizer on all node types (min 160x60, visible on select)
- [x] **SECURITY: JWT refresh-token flow** — reduced TTL to 60min, added POST /api/refresh, cookie TTL synced, 401 interceptor with queue-based retry
- [x] **SECURITY: DB transactions** — subscribe() and flow create() wrapped with lockForUpdate
- [x] **SECURITY: Input sanitization** — strip_tags() on all node/edge/flow labels
- [x] **SECURITY: Frontend auth guards** — requiresAuth meta + authGuard() on all protected routes
- [x] **FIX: Cross-type node deletion bug** — save() only deletes nodes matching current mode's types
- [x] **REFACTOR: Extract ModeTabs.vue** — reusable mode-switch component from Canvas.vue
- [x] **REFACTOR: Extract ColorSwatchPalette.vue** — reusable color picker from Canvas.vue
- [x] **REFACTOR: Extract useFlowMapper.js** — composable for server↔client node/edge mapping
- [x] **BUGFIX: Load definitions for users without saved flows** — `loadFlowData(null)` called from `onMounted` when no flow is selected, so Free-tier users and new users see definition nodes on the canvas
- [x] **BUGFIX: Cross-mode definition leakage on mode switch** — `switchMode()` now always calls `loadFlowData`, clearing old mode's nodes and loading new mode's definitions even without a saved flow
- [ ] **SECURITY: Replace mock payment** with real gateway integration
- [ ] **SECURITY: Add downgrade protection** for subscription expiry
- [ ] **SECURITY: Exclude super-admin** from auto-downgrade
- [ ] **SECURITY: Add email verification** flow
- [ ] **SECURITY: Set cookie Secure flag** (requires HTTPS deployment)
- [ ] **SECURITY: Remove extra JSON keys** from API responses
- [ ] **CODE QUALITY: Extract duplicate modal CSS** (btn-sm, btn-danger, modal-*, field-label) from designer pages into global `style.css`.
- [ ] **CODE QUALITY: Extract duplicate CRUD pattern** from designer pages into a `useCrud` composable.
- [ ] **CODE QUALITY: Replace `<a href>` with `<router-link>`** in all views for subpath compatibility.
- [x] **FEAT: Backend connection validation** — added `POST /api/flows/{flow}/validate-connection` with tier, type, self-connection, duplicate checks
- [x] **FEAT: onConnect backend-first validation** — frontend validates with backend before adding edge, blocks transient edges for free-tier
- [x] **FIX: FK crash in save()** — filter out unmappable edges instead of `?? 0` fallback
- [x] **FIX: Duplicate-edge guard moved to onConnect** — removed from isValidConnection, added local check in onConnect + backend exists() check
- [x] **FIX: Prevent tier downgrade** — `TIER_RANK` constant in `AuthController::subscribe()` rejects if new tier ≤ current tier; Subscribe.vue and UpgradeModal.vue filter out non-upgrade tiers
- [x] **REFACTOR: JWT cookie config moved to env+config** — `config/jwt.php` `cookie` section with env-driven name, path, domain, secure, http_only, same_site; `jwtCookie()` helper extracted in AuthController
- [x] **TIER ACCESS AUDIT (2026-06-11)** — Tested all 4 tiers against every endpoint with correct payloads. No leaks found. All permissions correctly enforced.
- [ ] Build layout state saving mechanisms triggered via `/flows/save` wrapped in Silver middleware checks.
- [ ] Implement reactive canvas customizations allowing real-time edge colors and node background modifications for Silver tier users.

## [Phase 1.5: Payment & Mail System (Backend — Laravel Queue)]
- [ ] Implement payment gateway integration (KBZ Pay, AYA Pay, CB Pay, MMQR)
- [ ] Create payments table migration and model
- [ ] Build payment confirmation endpoint (webhook/callback)
- [ ] Implement mail system with Laravel Queue (welcome email, payment receipt)
- [ ] Upgrade user tier on successful payment confirmation
- [ ] Handle failed/cancelled payments and tier rollback

## [Phase 2: Gold Tier Schema Compiler & Diagram View]
- [ ] Develop database schema compiler translating custom table nodes into valid SQL DDL.
- [ ] Wire Laravel controllers to safely output SQL script dumps alongside compiled schema patterns.
- [ ] Add interactive diagram rendering support inside the frontend canvas for generated structures.

## [Phase 3: Platinum Visual Folder Architecture Layout]
- [ ] Build custom Canvas `FolderFileNode` to act as target drop boundaries for function logic nodes.
- [ ] Write backend controllers to safely save the visual mapping JSON schema representing which functions connect to which files/folders.
- [ ] Implement localized paywall middleware intercepts to trigger 403 notifications for unauthorized tiers.

## [Phase 4: Launch, User Ratings Validation & Expansion]
- [ ] Deploy the 3-Tier MVP to production bounds.
- [ ] Monitor user ratings specifically tracking the acceptance of the Visual Folder Mapping feature by Vibe Coders.
- [ ] Post-validation step: Once user rating threshold is verified, initiate development of the custom backend script for automatic repository code generation based on the visual mapping.

## Enforced Workflow Rule (from 2026-06-09)
- [ ] Every commit must also update `mdFiles/gitMd/Commit.md` (append commit hash + message) and `openspec/changes/archive/logs.md` (append a Log- entry describing the change).
- [ ] `mdFiles/gitMd/Commit.md` should contain every git commit in reverse-chronological order, organized by date.
- [ ] `openspec/changes/archive/logs.md` should contain structured summaries (Summary + Modified Files) for each feature/fix commit.
