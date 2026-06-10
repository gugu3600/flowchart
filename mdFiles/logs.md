# Change Log

> Last updated: 2026-06-10 11:30 UTC

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

### Remaining Items
- Fix 1.1: Replace mock payment with real gateway
- Fix 1.2: Add downgrade protection (subscription expiry)
- Fix 1.4: Exclude super-admin from auto-downgrade
- Fix 1.5: Add email verification flow
- Fix 3.4: Set cookie Secure flag (requires HTTPS)
- Fix 2.5: Remove extra JSON keys from API responses
