# Change Log

> Last updated: 2026-06-11 14:00 UTC

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
