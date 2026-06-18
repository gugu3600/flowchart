# Change Log

> Last updated: 2026-06-18 16:00 UTC

## 2026-06-16 — Admin Resource Pages & Nav Links
- **FEAT: Admin resource endpoints** — Added `GET /admin/logics`, `GET /admin/tables`, `GET /admin/flows` endpoints to `AdminController`, each returning all records with owner name/email. Routes gated by `role:super-admin`.
- **FEAT: AdminResourceTable component** — Reusable `AdminResourceTable.vue` accepts `items`, `columns`, `loading` props, renders owner badge and date/count formatting.
- **FEAT: Admin resource pages** — Separate `AdminLogics.vue`, `AdminTables.vue`, `AdminFlows.vue` pages at `/admin/logics`, `/admin/tables`, `/admin/flows`, each with header nav back to dashboard, canvas, and sibling admin resource pages.
- **FEAT: Admin dashboard resource nav** — `AdminDashboard.vue` now has navigation cards (Logics, Tables, Flows) linking to each resource page, replacing inline resource tables.
- **FIX: Login 409 conflict** — Admin test requires logout before switching users to avoid JWT cookie conflict.
- **TEST: Admin resource pages** — Playwright test (`admin-resources.spec.js`) creates resources as a gold user, then logs in as admin and verifies all three resource pages show the data with correct owner names.
- **FEAT: AdminService + FormRequest refactor** — Extracted business logic into `AdminService` (injects Flow/Logic/Table repos) and validation into FormRequest classes (`UpdateUserRequest`, `UpdateRolesRequest`, `UpgradeUserRequest`). `AdminController` no longer calls models directly. Added `all()` (unscoped with user eager-loading) to `FlowRepository`, `LogicDefinitionRepository`, `TableDefinitionRepository`.

- **FEAT: Admin nav cards** — Admin resource views (Logics/Tables/Flows) now have styled resource cards matching the dashboard style, with icons and active-state highlighting.

### Files Modified (session 3)
|------|--------|
| `backend/app/Services/Admin/AdminService.php` | **NEW** — injects all three repos, provides `getAllLogics/Tables/Flows`, `getStats`, `getUsers`, `updateUser/Roles`, `deleteUser`, `getTiers`, `upgradeUser` |
| `backend/app/Http/Requests/Admin/UpdateUserRequest.php` | **NEW** — validates name/email/password on admin user update |
| `backend/app/Http/Requests/Admin/UpdateRolesRequest.php` | **NEW** — validates roles array against available API roles |
| `backend/app/Http/Requests/Admin/UpgradeUserRequest.php` | **NEW** — validates tier against non-super-admin roles |
| `backend/app/Http/Controllers/api/AdminController.php` | Refactored: injects `AdminService`, uses FormRequest classes, no direct model calls |
| `backend/app/Repositories/flow/FlowRepository.php` | Added `all()` — returns all flows with `user:id,name,email`, ordered by `created_at desc` |
| `backend/app/Repositories/logic_definition/LogicDefinitionRepository.php` | Added `all()` — returns all logics with user, ordered by `created_at desc` |
| `backend/app/Repositories/table_definition/TableDefinitionRepository.php` | Added `all()` — returns all tables with user, ordered by `created_at desc` |
| `backend/app/Repositories/flow/FlowRepositoryInterface.php` | Added `all()` method signature |
| `backend/app/Repositories/logic_definition/LogicDefinitionRepositoryInterface.php` | Added `all()` method signature |
| `backend/app/Repositories/table_definition/TableDefinitionRepositoryInterface.php` | Added `all()` method signature |

## 2026-06-18 — Docker Setup, ColumnBuilder Dropdown, Canvas Scoping
- **FEAT: Docker Compose setup** — 4 services (mysql:3308, backend:8000, frontend:3000, phpmyadmin:8080). Fixes: MySQL 3307→3308 (system conflict), PHP 8.3→8.4-fpm (composer >=8.4.1), node:20-alpine→node:20 (rolldown glibc/musl binding mismatch). Migrations run, seeder seeded.
- **FEAT: ColumnBuilder data type dropdown** — Type field changed from text `<input>` to grouped `<select>` with 5 groups (Numeric, String, Date/Time, JSON, Spatial) covering all common MySQL types including ENUM and SET. Custom text input fallback for non-standard types.
- **FIX: Canvas per-flow node isolation** — Removed auto-population of definition nodes in `loadFlowData()`. Each flow now only shows nodes explicitly saved to it, fixing cross-flow table/logic leaking.

### Files Modified (session 4)
| File | Change |
|------|--------|
| `docker-compose.yml` | **NEW** — 4 services: mysql, backend (PHP-FPM + Nginx), frontend (Node 20), phpmyadmin |
| `docker/php/Dockerfile` | **NEW** — PHP 8.4-FPM with extensions, Nginx, Composer |
| `docker/php/nginx.conf` | **NEW** — Nginx config for Laravel |
| `docker/php/www.conf` | **NEW** — PHP-FPM pool config |
| `docker/frontend/Dockerfile` | **NEW** — Node 20 build + serve |
| `docker/frontend/nginx.conf` | **NEW** — Nginx config for Vite SPA |
| `frontend/src/components/ColumnBuilder.vue` | Type field changed from text input to grouped `<select>` with `DATA_TYPE_GROUPS` (5 groups, all MySQL types), custom text fallback |
| `frontend/src/views/Canvas.vue` | Removed `getTables`/`getLogics` imports and auto-population block; `loadFlowData` now only uses `savedNodes`/`savedEdges` |

## 2026-06-18 — Login Redirect, Per-Flow Definitions, Delete Persistence
- **FIX: Login redirect for authenticated users** — Router now fetches user on `/login`/`/register` routes; if already authenticated, redirects to `/canvas` which auto-selects the latest flow (flows now ordered `created_at desc`).
- **FEAT: Per-flow definition scoping** — Added `flow_id` FK to `table_definitions` and `logic_definitions`. Backend scopes CRUD by `flow_id` query param. Frontend TableDesigner/LogicDesigner have flow selectors. Canvas sidebar shows definitions for the current flow as draggable items.
- **FIX: Delete persistence** — `onEdgesDelete` and `onNodesDelete` now call `handleSave()` to persist deletions to backend immediately.

### Files Modified (session 5)
| File | Change |
|------|--------|
| `backend/database/migrations/2026_06_18_070406_add_flow_id_to_table_and_logic_definitions.php` | **NEW** — adds nullable `flow_id` FK to both `table_definitions` and `logic_definitions` |
| `backend/app/Models/TableDefinition.php` | Added `flow_id` to fillable, added `flow()` BelongsTo relationship |
| `backend/app/Models/LogicDefinition.php` | Added `flow_id` to fillable, added `flow()` BelongsTo relationship |
| `backend/app/Repositories/table_definition/TableDefinitionRepository.php` | Added `allForUserAndFlow()` |
| `backend/app/Repositories/table_definition/TableDefinitionRepositoryInterface.php` | Added `allForUserAndFlow()` signature |
| `backend/app/Repositories/logic_definition/LogicDefinitionRepository.php` | Added `allForUserAndFlow()`, `countForUserAndFlow()` |
| `backend/app/Repositories/logic_definition/LogicDefinitionRepositoryInterface.php` | Added `allForUserAndFlow()`, `countForUserAndFlow()` signatures |
| `backend/app/Repositories/flow/FlowRepository.php` | Added `orderBy('created_at', 'desc')` to `allForUser()` |
| `backend/app/Services/TableDefinition/TableDefinitionService.php` | Added `allForUserAndFlow()`, passes `flow_id` on create |
| `backend/app/Services/LogicDefinition/LogicDefinitionService.php` | Added `allForUserAndFlow()`, passes `flow_id` on create, limit check scoped by flow |
| `backend/app/Http/Controllers/api/TableDefinitionController.php` | `index()` accepts `?flow_id` query param |
| `backend/app/Http/Controllers/api/LogicDefinitionController.php` | `index()` accepts `?flow_id` query param |
| `backend/app/Http/Requests/TableDefinition/StoreTableDefinitionRequest.php` | Added `flow_id` validation rule |
| `backend/app/Http/Requests/LogicDefinition/StoreLogicDefinitionRequest.php` | Added `flow_id` validation rule |
| `frontend/src/router/index.js` | Fetches user on `/login`/`/register` to detect existing session |
| `frontend/src/api/tables.js` | `getTables(flowId)` passes optional query param |
| `frontend/src/api/logics.js` | `getLogics(flowId)` passes optional query param |
| `frontend/src/components/Sidebar.vue` | Accepts `definitions` prop, shows per-flow logics as draggable items |
| `frontend/src/components/SchemaSidebar.vue` | Accepts `definitions` prop, shows per-flow tables as draggable items |
| `frontend/src/views/Canvas.vue` | Restored `getTables`/`getLogics` imports scoped by `currentFlowId`; calls `loadFlowDefinitions` on flow select; passes definitions to sidebars; `onEdgesDelete`/`onNodesDelete` call `handleSave()` |
| `frontend/src/views/TableDesigner.vue` | Added flow selector dropdown, `loadTables(flowId)`, passes `flow_id` on create |
| `frontend/src/views/LogicDesigner.vue` | Added flow selector dropdown, `loadLogics(flowId)`, passes `flow_id` on create |

## 2026-06-10 — Security Fixes & Canvas Refactoring

### Security Fixes (from audit findings)
- **Fix 4 (JWT Refresh):** `JWT_TTL` reduced from 43200 min → 60 min; added `POST /api/refresh` endpoint with cookie TTL synced to `config('jwt.ttl')`; frontend `apiClient.js` 401 interceptor with queue-based concurrent retry prevents infinite loops.
- **Fix 5 (DB Transactions):** `subscribe()` wrapped in `DB::transaction` with `lockForUpdate()` on user row; `FlowService::create()` wrapped in `DB::transaction` with `lockForUpdate()` in `checkFlowLimit()`.
- **Fix 7 (Input Sanitization):** `strip_tags()` applied to all labels (nodes, edges, flow names) before persisting in `FlowService::save()`, `saveNodes()`, `saveEdges()`, `create()`.
- **Fix 8 (Frontend Auth Guards):** `authGuard()` added to `routeGuard.js`; `meta: { requiresAuth: true }` set on `/subscribe`, `/canvas`, `/tables`, `/logics`.

- **Cross-type node deletion bug fixed:** `FlowService::save()`/`saveNodes()`/`saveEdges()` now use `deleteByFlowIdAndTypes()` (deletes only nodes matching payload types) and `deleteByNodeIds()` (cleans edges referencing deleted nodes); frontend save response filters by mode.

- **False positive corrected:** `.env` is gitignored, never tracked — audit finding 4.1 requires no action.

### Canvas Refactoring
- **ModeTabs.vue** extracted: Flow/Schema toggle buttons (props: mode, emits: update:mode).
- **ColorSwatchPalette.vue** extracted: Color picker swatches (props: label, colors, selectedColor, isActive fn).
- **useFlowMapper.js** extracted: `toClientNode`, `toClientEdge`, `toServerNode`, `toServerEdge`, `filterByMode` — server↔client node/edge mapping.
- `Canvas.vue` refactored to use all three: 565→506 lines (-10%).

### Bugfixes (2026-06-10)
- **Color tools not visually updating:** `setNodeColor()` and `setEdgeColor()` were not updating the `style` property on the node/edge object, so color changes only applied after save+reload. Fixed by setting `node.style` and `edge.style` directly before saving.
- **Duplicate edges allowed in all modes:** `isValidConnection()` did not check for existing edges between the same source/target pair. Added `edges.value.some()` check to prevent duplicate connections.
- **Logics not appearing in flow canvas for users without saved flows:** `loadFlowData()` was only called when a flow was selected (requires `canSave` + existing flows). Free-tier users or new users without flows never loaded definitions. Fixed by calling `loadFlowData(null)` from `onMounted` when no flow is selected, which loads definitions without saved flow data.
- **Cross-mode definition leakage on mode switch:** `switchMode()` guarded `loadFlowData` behind `if (currentFlowId.value)`, so switching modes without a selected flow left the old mode's definition nodes on screen and never loaded the new mode's definitions. Fixed by removing the guard — `loadFlowData` now always runs on mode switch and handles null flowId gracefully.

### Files Modified
| File | Change |
|------|--------|
| `backend/app/Http/Controllers/api/AdminController.php` | Added `logics()`, `tables()`, `flows()` methods with user eager-loading and mapping |
| `backend/routes/api.php` | Added `GET /admin/logics`, `GET /admin/tables`, `GET /admin/flows` in super-admin group |
| `frontend/src/api/admin.js` | Added `getAdminLogics()`, `getAdminTables()`, `getAdminFlows()` |
| `frontend/src/components/AdminResourceTable.vue` | **NEW** — reusable admin resource table component |
| `frontend/src/views/AdminLogics.vue` | **NEW** — admin logics page; header nav links to Dashboard, Tables, Flows, Canvas |
| `frontend/src/views/AdminTables.vue` | **NEW** — admin tables page; header nav links to Dashboard, Logics, Flows, Canvas |
| `frontend/src/views/AdminFlows.vue` | **NEW** — admin flows page; header nav links to Dashboard, Logics, Tables, Canvas |
| `frontend/src/views/AdminDashboard.vue` | Removed inline resource tables, added resource nav cards |
| `frontend/src/router/index.js` | Added `/admin/logics`, `/admin/tables`, `/admin/flows` routes |
| `frontend/src/style.css` | Added `.admin-resource-nav`, `.admin-resource-card` styles |
| `tests/admin-resources.spec.js` | **NEW** — Playwright test for admin resource pages |

## 2026-06-10 — Security Fixes & Canvas Refactoring

### Security Fixes (from audit findings)

### Files Modified
| File | Change |
| `backend/.env.example` | Added `APP_DEBUG=false`, `JWT_TTL=60`, `FRONTEND_URL` |
| `backend/README.md` | Updated route table, security section |
| `backend/app/Http/Controllers/api/AuthController.php` | Cookie TTL dynamic; DB::transaction + lockForUpdate in subscribe() |
| `backend/app/Services/Flow/FlowService.php` | Type-aware save/saveNodes/saveEdges; strip_tags() on all labels |
| `backend/app/Repositories/flow_node/FlowNodeRepository.php` | Added `deleteByFlowIdAndTypes()` |
| `backend/app/Repositories/flow_node/FlowNodeRepositoryInterface.php` | Interface update |
| `backend/app/Repositories/flow_edge/FlowEdgeRepository.php` | Added `deleteByNodeIds()` |
| `backend/app/Repositories/flow_edge/FlowEdgeRepositoryInterface.php` | Interface update |
| `backend/routes/api.php` | Added `POST /api/refresh` |
| `frontend/src/api/apiClient.js` | 401 interceptor with queue-based refresh retry |
| `frontend/src/router/index.js` | `requiresAuth` meta on all protected routes |
| `frontend/src/router/routeGuard.js` | `authGuard()` exported |
| `frontend/src/components/ModeTabs.vue` | NEW — reusable mode-switch component |
| `frontend/src/components/ColorSwatchPalette.vue` | NEW — reusable color palette |
| `frontend/src/composables/useFlowMapper.js` | NEW — server↔client node/edge mapping |
| `frontend/src/views/Canvas.vue` | Refactored to use ModeTabs, ColorSwatchPalette, useFlowMapper |
| `frontend/README.md` | Added composables section, updated components list |
| `security-audit-findings.md` | Fix status table updated with completed fixes |

### Backend Connection Validation & Save Fix (2026-06-10)
- **Backend connection validation endpoint:** Added `POST /api/flows/{flow}/validate-connection` (gated by `permission:save-flows`) that checks tier eligibility, self-connection, type compatibility (table↔table for schema, logic↔folderFile for flow), and duplicate edges (when nodes have DB IDs).
- **`onConnect` now validates with backend before adding edge:** Calls `validateConnection()` API first; only adds edge to UI if backend returns success. Free-tier users are rejected at the validation step, preventing misleading transient edges.
- **FK constraint crash in `FlowService::save()` fixed:** The `$nodeIdMap[$e['source']] ?? 0` fallback inserted `source_node_id=0` which violated the FK constraint to `flow_nodes.id`, causing a silent 500 error. Fixed by filtering out edges with unmappable source/target instead of defaulting to 0.
- **Duplicate-edge guard moved to `onConnect`:** Prevents adding duplicate edges locally. Backend also protects against duplicates for saved nodes via `FlowEdgeRepository::exists()`.
- **`FlowEdgeRepositoryInterface` / `FlowEdgeRepository`:** Added `exists(int $flowId, int $sourceNodeId, int $targetNodeId): bool` method for duplicate detection.

### Tier Downgrade Protection (2026-06-11)
- **`AuthController::subscribe()`** now rejects requests where the requested tier's rank is ≤ current tier's rank via `TIER_RANK` constant (free=0, silver=1, gold=2, platinum=3). Returns 422 with message "You are already on the {tier} tier. Please choose a higher tier to upgrade."
- **Subscribe.vue** filters `allTiers` via `validTiers` computed — only shows tiers strictly higher than the user's current tier. If no upgrade is available (already platinum), shows a "highest available plan" message.
- **UpgradeModal.vue** uses `isUpgrade()` function to only render Subscribe buttons for tiers with rank > current rank.

### JWT Cookie Config Refactor (2026-06-11)
- **`config/jwt.php`** added `cookie` section with env-driven keys: `name` (`JWT_COOKIE_NAME`), `path` (`JWT_COOKIE_PATH`), `domain` (`JWT_COOKIE_DOMAIN`), `secure` (`JWT_COOKIE_SECURE`), `http_only` (`JWT_COOKIE_HTTP_ONLY`), `same_site` (`JWT_COOKIE_SAME_SITE`), `raw` (false).
- **`AuthController.php`** extracted `jwtCookie()` private helper — eliminates 4 duplicated `->cookie('jwt_token', ...)` calls with hardcoded args. Cookie reads updated to `config('jwt.cookie.name')`.
- **`JwtCookieMiddleware.php`** updated to read cookie name from config instead of hardcoded `'jwt_token'`.
- **`.env.example`** added `JWT_COOKIE_*` vars.

### Tier Access Audit (2026-06-11)
Tested all 4 tier accounts (free, silver, gold, platinum) against every protected endpoint with correct payloads. **No leaks found.**

| Test | free | silver | gold | platinum |
|------|------|--------|------|----------|
| GET /flows (read) | 200 ✅ | 200 ✅ | 200 ✅ | 200 ✅ |
| POST /flows (create) | 403 ✅ | 201 ✅ | 201 ✅ | 201 ✅ |
| GET /tables (read) | 200 ✅ | 200 ✅ | 200 ✅ | 200 ✅ |
| POST /tables (create) | 403 ✅ | 403 ✅ | 201 ✅ | 201 ✅ |
| POST /logics (create) | 201 ✅ | 201 ✅ | 201 ✅ | 201 ✅ |
| POST /subscribe (downgrade) | 200 (upgrade ✅) | 422 ✅ | 422 ✅ | 422 ✅ |
| GET /admin/tiers | 403 ✅ | 403 ✅ | 403 ✅ | 403 ✅ |

### Logic Node UI
Confirmed node shows title = logic name, body = description + inputs + output — matches requirements.

### Remaining Items
- Fix 1.1: Replace mock payment with real gateway
- Fix 1.2: Add downgrade protection (subscription expiry)
- Fix 1.4: Exclude super-admin from auto-downgrade
- Fix 1.5: Add email verification flow
- Fix 3.4: Set cookie Secure flag (requires HTTPS)
- Fix 2.5: Remove extra JSON keys from API responses
