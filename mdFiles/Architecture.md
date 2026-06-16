# Application Architecture & Strategic Tier Matrix (Current MVP Stage)

> Last updated: 2026-06-16 20:00 UTC

## Core System Stack
- **Frontend:** Vue 3 (Composition API) + Vite 8 + Tailwind CSS v4 + PrimeVue 4 + axios.
- **API:** axios client configured via `VITE_API_BASE_URL` in `frontend/.env` (no fallback — must be set). Base URL points to Laravel backend (`http://localhost:8000/api`).
- **Backend:** Laravel 13 running RESTful APIs, MySQL Database.
- **Authentication:** JWT-based authentication using tymon/jwt-auth. Token stored in HTTP-only Secure SameSite=Strict cookie via `JwtCookieMiddleware`. Cookie params (name, path, domain, secure, http_only, same_site) are env-driven via `config('jwt.cookie.*')`.
- **Role-Based Access Control:** Spatie Laravel Permissions (free/silver/gold/platinum tiers + super-admin).
- **Caching:** Laravel's built-in caching system.
- **Design Patterns:** Repository Pattern, Service Pattern, Form Request validation, API Resources.
- **Database Schema:** 3NF normalized MySQL schema for `flows`, `flow_nodes`, `flow_edges`, `table_definitions`, `logic_definitions`.
- **Testing:** Playwright (E2E) for frontend — 15 tests passing (12 tier-gated color/permission + subscribe tests, 1 color-ui, 1 admin-resources, 1 debug-cleaned-up), PHPUnit for backend API tests.

## Frontend Pages
| Route | Page | Purpose |
|-------|------|---------|
| `/help` | HelpGuide.vue | How-to guide and documentation for the web app |
| `/login` | Login.vue | JWT login form with floating-label inputs, show/hide password toggle, "Remember me" checkbox, link to register |
| `/register` | Register.vue | User registration with tier selection (Free/Silver/Gold/Platinum), pricing display, floating-label inputs with password toggle, payment method picker (KBZ Pay / AYA Pay / CB Pay / MMQR) for paid tiers |
| `/canvas` | Canvas.vue | Tabbed Vue Flow canvas (Flow mode + Schema mode), drag-drop, save/load, live definitions, edge/node deletion via Delete/Backspace. Free tier: sandbox only (no save). Silver+: color picker for node backgrounds and edge strokes, save up to 5 flows. Gold+: unlimited flows. |
| `/subscribe` | Subscribe.vue | Self-service subscription page — tier cards, payment method picker, subscribe button. Authenticated users only. |
| `/tables` | TableDesigner.vue | CRUD for database table schemas (columns, types, PK/FK/UQ). Gold+ (`generate-schema` permission) can create/edit/delete. Free/Silver read-only. ColumnBuilder component for column rows. |
| `/logics` | LogicDesigner.vue | CRUD for logic/function definitions (structured name/type inputs, output). Free: max 4 logics with upgrade modal. Silver+: unlimited. |
| `/admin` | AdminDashboard.vue | Admin overview with stats, user management (roles, upgrade, delete), tier definitions. Super-admin only. |
| `/admin/logics` | AdminLogics.vue | All logic definitions across all users with owner info. Super-admin only. |
| `/admin/tables` | AdminTables.vue | All table definitions across all users with owner info. Super-admin only. |
| `/admin/flows` | AdminFlows.vue | All flows across all users with owner info. Super-admin only. |

## Canvas Modes (Tab-Separated)

| Mode | Tab | Node Types | Connection Rule | Sidebar |
|------|-----|------------|-----------------|---------|
| **Flow** | `Flow` | Logic, Folder/File | logic↔logic, logic↔folderFile, folderFile↔folderFile | Sidebar.vue (Logic + Folder/File) |
| **Schema** | `Schema` | Table | table↔table only (FK relationships) | SchemaSidebar.vue (Table only) |

### Mode Separation Rules (Enforced)

1. **Definition loading:** `loadFlowData` only loads definitions matching the current mode — logics for flow, tables for schema. The `if/else` branch in `loadFlowData` ensures only the correct API is called.
2. **Saved node filtering:** When loading saved flow data, nodes are filtered by `n.type` — flow mode keeps only `logic`/`folderFile`, schema mode keeps only `table`. All others are silently dropped.
3. **Drop validation:** `onDrop` calls `isAllowedType()` which rejects items whose type doesn't match the active mode. A logic item cannot be dropped on schema canvas and vice versa.
4. **Connection validation:** `isValidConnection` checks type compatibility (table↔table, logic↔logic/folderFile). Backend `POST /api/flows/{flow}/validate-connection` (gated by `permission:save-flows`) additionally checks tier eligibility, self-connection, type compatibility, and duplicate edges (when nodes have DB IDs). `onConnect` validates with backend before adding edge async.
5. **Save response filtering:** `handleSave` applies `filterByMode()` to the server response, so only current-mode nodes survive the round-trip. Nodes of other modes exist on the server but are filtered out client-side.
6. **Mode switch reload:** `switchMode` always calls `loadFlowData` (with or without a flowId), clearing the old mode's nodes and loading the new mode's definitions/saved nodes.
7. **Edge isolation:** Edges referencing nodes filtered out by mode are also excluded from display since their source/target IDs won't match any displayed node.
8. **Edge save safety:** `FlowService::save()` filters out edges whose source/target node IDs are not in the node ID map (unmappable edges) instead of defaulting to 0, preventing FK constraint violations.

## Frontend Component Architecture
```
src/
├── api/
│   ├── apiClient.js          — Axios instance (withCredentials, response unwrapper, baseURL from VITE_API_BASE_URL); 401 interceptor with queue-based token refresh
│   ├── flows.js              — Flows CRUD + save API
│   ├── tables.js             — Table definitions CRUD API
│   └── logics.js             — Logic definitions CRUD API
├── assets/
│   └── style.css             — Tailwind import, @theme tokens, reusable utility classes
├── components/
│   ├── AppButton.vue         — PrimeVue Button wrapper (btn-primary / btn-secondary variants)
│   ├── AppInput.vue          — PrimeVue InputText wrapper with label
│   ├── AppCard.vue           — PrimeVue Card wrapper with title/subtitle/slot
│   ├── AppNavbar.vue         — PrimeVue Menubar wrapper
│   ├── ColumnBuilder.vue     — v-model column row builder (name, type, PK/FK/UQ checkboxes, add/remove)
│   ├── FloatingInput.vue     — Floating-label input with bottom-border underline, password toggle, error state
│   ├── TierSelector.vue      — 2×2 tier card grid for registration
│   ├── PaymentMethodPicker.vue — 2×2 payment method grid with loading state
│   ├── Sidebar.vue           — Drag-and-drop palette (Flow mode: Logic, Folder/File)
│   ├── SchemaSidebar.vue     — Drag-and-drop palette (Schema mode: Table only)
│   ├── ModeTabs.vue          — Flow/Schema mode toggle buttons (props: mode, emits: update:mode)
│   ├── ColorSwatchPalette.vue — Color picker swatches (props: label, colors, selectedColor, isActive fn)
│   ├── AdminResourceTable.vue — Reusable admin table with owner badges, date/count formatting
│   ├── index.js              — Barrel exports
│   └── nodes/
│       ├── BaseNode.vue       — Shared node wrapper (Handle ports, color themes, selected ring)
│       ├── TableNode.vue      — DB table schema node (columns, PK/FK/UQ badges)
│       ├── LogicNode.vue      — Logic/function node (inputs, outputs, description)
│       ├── FolderFileNode.vue — Folder/file mapping node (path, children tree preview)
│       └── index.js           — Barrel exports
├── composables/
│   └── useFlowMapper.js       — Server↔client mapping: toClientNode, toClientEdge, toServerNode, toServerEdge, filterByMode
├── views/
│   ├── Login.vue             — Login form using AppCard / AppInput / AppButton + apiClient
│   ├── Canvas.vue            — Tabbed Vue Flow (Flow/Schema), flow selector, sidebar, save/load, edge/node deletion; uses ModeTabs, ColorSwatchPalette, useFlowMapper
│   ├── HelpGuide.vue         — How-to guide with app usage instructions and keyboard shortcuts
│   ├── Register.vue          — Registration form (name, email, password, confirm)
│   ├── Subscribe.vue         — Self-service subscription with tier cards, payment method picker
│   ├── TableDesigner.vue     — Table schema CRUD with modal form, column builder
│   ├── LogicDesigner.vue     — Logic definition CRUD with modal form, inputs/output builder
│   ├── AdminDashboard.vue    — Admin overview with stats, user management, resource nav cards
│   ├── AdminLogics.vue       — All logics across all users (super-admin only)
│   ├── AdminTables.vue       — All tables across all users (super-admin only)
│   ├── AdminFlows.vue        — All flows across all users (super-admin only)
├── router/
│   ├── index.js              — Vue Router (/login, /register, /subscribe, /canvas, /help, /tables, /logics, /admin, /admin/logics, /admin/tables, /admin/flows routes) with requiresAuth/requiresAdmin meta
│   └── routeGuard.js          — authGuard() + adminGuard() route navigation guards
├── stores/
│   └── useUserStore.js        — Singleton reactive store (no Pinia) with tier/role/computed permissions
├── App.vue                   — <router-view /> root
└── main.js                   — createApp + router + PrimeVue plugin
```

## Registration Flow
1. User navigates to `/register`
2. Selects a tier from 4 cards: Free (0 MMK), Silver (3,000 MMK), Gold (6,000 MMK), Platinum (7,500 MMK)
3. Fills in name, email, password, confirm password
4. For paid tiers (Silver/Gold/Platinum), the frontend fetches available payment methods from `GET /api/payment-methods` (mock data, no payment logic implemented yet)
5. Selects a payment method: KBZ Pay, AYA Pay, CB Pay, or MMQR
6. POST to `/api/register` with `{name, email, password, password_confirmation, tier, payment_method?}`
7. Backend creates user, assigns selected tier role, returns JWT in HTTP-only cookie
8. Free tier users can register without selecting any payment method
9. Payment processing and mail system will be implemented later via Laravel Queue

## Subscribe / Upgrade Flow
1. User navigates to `/subscribe` (or via UpgradeModal from `/canvas` or `/logics`)
2. Subscribe.vue filters available tiers to only those strictly higher than the user's current tier
3. User selects a tier and payment method
4. POST to `/api/subscribe` with `{tier, payment_method}`
5. Backend checks `TIER_RANK` (free=0, silver=1, gold=2, platinum=3) — rejects if new tier rank ≤ current tier rank with 422
6. If valid, wraps in `DB::transaction` with `lockForUpdate()`, calls `syncRoles()`, extends `subscription_expires_at`
7. Frontend refreshes user store and redirects to `/canvas`

## Subscription Durations
| Tier | Duration |
|------|----------|
| Silver | 33 days |
| Gold | 37 days |
| Platinum | 44 days |

Paid tiers have `subscription_expires_at` set on upgrade; auto-downgraded to free via `subscription:expire` command.

## Pricing
| Tier | Price (MMK) |
|------|-------------|
| Free | 0 |
| Silver | 3,000 |
| Gold | 6,000 |
| Platinum | 7,500 |

## Definition-to-Node Flow
1. User creates Table definitions on `/tables` or Logic definitions on `/logics`
2. On `/canvas`, switch to **Flow** tab for logic/folder flowcharts or **Schema** tab for table relationship diagrams
3. Canvas loads the relevant definitions for the active mode and creates nodes (with `definitionId` tracking)
4. Existing saved nodes in the active mode keep their positions; new definitions appear in a grid
5. `isValidConnection` enforces domain boundaries — table nodes cannot connect to logic nodes
6. User can reposition, connect, save; clicking refresh (⟳) reloads definitions

## API Routes (42 total)

### Public (throttled 20/min)
| Method | Route | Handler |
|--------|-------|---------|
| POST | `/api/register` | AuthController@register (accepts tier + payment_method) |
| POST | `/api/login` | AuthController@login |
| GET | `/api/payment-methods` | AuthController@paymentMethods (mock data, no auth required) |

### Authenticated (auth:api)
| Method | Route | Handler |
|--------|-------|---------|
| GET | `/api/me` | AuthController@me |
| POST | `/api/logout` | AuthController@logout |
| POST | `/api/subscribe` | AuthController@subscribe (self-service tier upgrade) |
| GET/POST | `/api/flows` | FlowController@index/store |
| GET/PUT/DELETE | `/api/flows/{flow}` | FlowController@show/update/destroy |
| POST | `/api/flows/{flow}/nodes` | FlowController@saveNodes |
| POST | `/api/flows/{flow}/edges` | FlowController@saveEdges |
| POST | `/api/flows/{flow}/save` | FlowController@save (combined) |
| POST | `/api/flows/{flow}/validate-connection` | FlowController@validateConnection (tier+type+duplicate check) |
| GET/POST | `/api/tables` | TableDefinitionController@index/store |
| GET/PUT/DELETE | `/api/tables/{table}` | TableDefinitionController@show/update/destroy |
| GET/POST | `/api/logics` | LogicDefinitionController@index/store (free: max 4) |
| GET/PUT/DELETE | `/api/logics/{logic}` | LogicDefinitionController@show/update/destroy |
| GET | `/api/admin/tiers` | AdminController@tiers (super-admin, list tier roles/permissions) |
| GET | `/api/admin/users` | AdminController@users (super-admin, paginated user list with roles) |
| GET | `/api/admin/users/{user}` | AdminController@show (super-admin, single user with available roles) |
| PUT | `/api/admin/users/{user}` | AdminController@update (super-admin, update name/email/password) |
| PUT | `/api/admin/users/{user}/roles` | AdminController@updateRoles (super-admin, sync user roles) |
| PUT | `/api/admin/users/{user}/upgrade` | AdminController@upgrade (super-admin, upgrade tier with subscription duration) |
| DELETE | `/api/admin/users/{user}` | AdminController@destroy (super-admin, delete user) |
| GET | `/api/admin/logics` | AdminController@logics (super-admin, all users' logic definitions) |
| GET | `/api/admin/tables` | AdminController@tables (super-admin, all users' table definitions) |
| GET | `/api/admin/flows` | AdminController@flows (super-admin, all users' flows) |

## Strategic Monetization Matrix (Value-Based Hierarchy)
1. **Free Tier:**
   - Access to basic drag-and-drop canvas layout building.
   - *Strict Restriction:* Cannot save workflows. Trigger 403 Paywall on Save.
2. **🥈 Silver Tier (Basic Premium):**
   - **Unlimited Save Slots:** Unlocks complete flowchart blueprint saving backend logic.
   - **Canvas Customization:** Authorization to dynamically change route/edge colors and node background colors via UI.
3. **🥇 Gold Tier (Intermediate Premium):**
   - Includes all **Silver Tier** features.
   - **Database Auto-Generation:** Compiles frontend canvas layout JSON into clean SQL DDL scripts and interactive relational diagrams.
4. **💎 Platinum Tier (The Ultimate Creator Feature - Highest Perceived Value):**
   - Includes all **Gold Tier** and **Silver Tier** features.
   - **Visual Folder Mapping Engine:** Unlocks special `Folder/File Nodes` on the canvas. Users can draw flowchart connections from Logic/Function Nodes into Folder/File Nodes to architect their workspace layout visually.

## Future MVP Scale-Up Roadmap
- **Trigger Condition:** Monitor application users' ratings and feedback on the Visual Folder Mapping Engine.
- **Execution Step:** Once a stable rating threshold is confirmed, implement the custom backend generator script to parse the visual folder JSON mappings and compile full functional repository zip packages (Auto Code Generation) exclusively for Platinum/VIP status.

## Reusable Components
| Component | Used In | Purpose |
|-----------|---------|---------|
| `ColumnBuilder` | TableDesigner.vue | v-model column row builder with name/type/PK/FK/UQ fields, add/remove buttons |
| `FloatingInput` | Login.vue, Register.vue | Floating-label input with bottom-border underline, password show/hide toggle, error state (v-model, type, id, label, autocomplete, showPasswordToggle, hasError props) |
| `TierSelector` | Register.vue | 2×2 tier card grid (v-model, tiers array) |
| `PaymentMethodPicker` | Register.vue | 2×2 payment method button grid with loading state (v-model, methods, loading props) |
| `ModeTabs` | Canvas.vue | Flow/Schema mode toggle buttons (props: mode, emits: update:mode) |
| `ColorSwatchPalette` | Canvas.vue | Color picker swatches for node background / edge stroke (props: label, colors, selectedColor, isActive fn) |
| `AdminResourceTable` | AdminLogics.vue, AdminTables.vue, AdminFlows.vue | Reusable admin table with owner badges, date/count formatting (props: items, columns, loading) |
| `AppButton` | Multiple views | Styled action button with loading state |
| `AppCard` | Multiple views | Card container with optional title/subtitle |

## Composables
| Composable | Used In | Purpose |
|-----------|---------|---------|
| `useFlowMapper` | Canvas.vue | Server↔client node/edge mapping: `toClientNode`, `toClientEdge`, `toServerNode`, `toServerEdge`, `filterByMode` |

## Middleware & Access Gatekeeping (Gatekeeper Bounds)
- **Save Flows:** Wraps `/flows/*` write endpoints. Verify user has `save-flows` permission (silver+).
- **Generate Schema:** Wraps `/tables/*` write endpoints. Verify user has `generate-schema` permission (gold+).
- **Map Structure:** Reserved for future platinum folder-mapping feature.
- **Logic CRUD:** Open to all tiers. Free tier limited to 4 logics by service layer check.
- All violations return `403 Forbidden` with localized and global paywall instructions.
