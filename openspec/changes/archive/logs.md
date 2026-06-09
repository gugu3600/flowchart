# Change Log Archive

> Last updated: 2026-06-08 12:00 UTC

## Log-2026-06-04-001 — Project Scaffolding & RBAC/JWT Setup

### Summary
- Scaffolded Laravel 13 backend in `backend/` with MySQL
- Scaffolded Vue 3 + Vite frontend in `frontend/` (port 3000, open: true)
- Installed `spatie/laravel-permission` (v8.0.0) — RBAC middleware aliases registered in `bootstrap/app.php`
- Installed `tymon/jwt-auth` (v2.3.0) — `auth:api` guard configured, `JWT_SECRET` generated
- User model updated: implements `JWTSubject`, uses `HasRoles` trait, `$auth_guard = 'api'`
- Created custom artisan commands:
  - `make:service Folder/Name` → `app/Services/{folder}/{Name}Service.php`
  - `make:repository Name` → `app/Repositories/{name}/{Name}Repository.php` + Interface
- Registered Spatie middleware aliases (`role`, `permission`, `role_or_permission`) in `bootstrap/app.php`
- Created `routes/api.php` with `auth:api` middleware
- Initialized git repo, pushed to `main`, created `dev` and `test` branches
- Created `mdFiles/gitMd/Commit.md` for commit history tracking

### Added Files
| File | Purpose |
|------|---------|
| `backend/app/Console/Commands/MakeServiceCommand.php` | Artisan `make:service` command |
| `backend/app/Console/Commands/MakeRepositoryCommand.php` | Artisan `make:repository` command |
| `backend/routes/api.php` | API route definitions |
| `mdFiles/gitMd/Commit.md` | Commit history log |
| `openspec/changes/archive/logs.md` | This file |

### Modified Files
| File | Change |
|------|--------|
| `backend/app/Models/User.php` | Added `JWTSubject`, `HasRoles`, `$auth_guard = 'api'` |
| `backend/bootstrap/app.php` | Registered `api` routes + Spatie middleware aliases |
| `backend/config/auth.php` | Added `api` guard with `jwt` driver |
| `backend/.env` | Set `AUTH_GUARD=api`, `JWT_TTL`, `JWT_SECRET`, `DB_PASSWORD` |
| `frontend/vite.config.js` | Added `server.port=3000`, `server.open=true` |

### Deleted Code
*(None — initial scaffolding)*

---

## Log-2026-06-04-002 — Database Schema: `flowchart` DB + Domain Migrations

### Summary
- Switched database from `backend` to `flowchart` (`DB_DATABASE=flowchart`)
- Designed and implemented 3NF schema for the flowchart domain
- Created 3 migration files: `flows`, `flow_nodes`, `flow_edges`
- Documented full schema in `mdFiles/schema.md`
- Created `tests/setup.spec.js` for Playwright smoke tests
- Deleted `tests/tests.md` (replaced by `setup.spec.js`)

### Added Files
| File | Purpose |
|------|---------|
| `mdFiles/schema.md` | Full DB schema documentation (3NF, ER, column types, migration order) |
| `tests/setup.spec.js` | Playwright smoke test — app mount, title, console errors, / health |
| `database/migrations/..._create_flows_table.php` | `flows` table with user FK, name, description, config JSON |
| `database/migrations/..._create_flow_nodes_table.php` | `flow_nodes` table with flow FK, type, position, data/config JSON |
| `database/migrations/..._create_flow_edges_table.php` | `flow_edges` table with flow FK, source/target node FKs, config JSON |

### Modified Files
| File | Change |
|------|--------|
| `backend/.env` | `DB_DATABASE=backend` → `DB_DATABASE=flowchart` |
| `openspec/changes/archive/logs.md` | Appended this log entry |

### Deleted Code
| File | Reason |
|------|--------|
| `tests/tests.md` | Replaced by `tests/setup.spec.js` |

---

## Log-2026-06-04-003 — Models, Controllers, Role/Permission Seeders & 3NF Verification

### Summary
- Created Eloquent models: `Flow`, `FlowNode`, `FlowEdge` with all relationships (`belongsTo` / `hasMany`)
- Created model factories for all three models
- Created `AuthController` with JWT endpoints: register, login (with roles/permissions in response), me, logout
- Created `FlowController` with full CRUD + `saveNodes` / `saveEdges` bulk operations
- Updated `routes/api.php` with public + auth:api protected routes
- Ran 3NF verification (PASSED — documented in `mdFiles/schema.md`)
- Created `RoleAndPermissionSeeder`:
  - 5 roles: `super-admin` (all perms), `free`, `silver`, `gold`, `platinum`
  - 5 permissions: `save-flows`, `generate-schema`, `map-structure`, `manage-users`, `manage-roles`
  - Seeded Super Admin user (`admin@flowchart.dev` / `password`) with super-admin role
- Tested login endpoint — returns user + roles + permissions + JWT token

### Added Files
| File | Purpose |
|------|---------|
| `app/Models/Flow.php` | Flow model with user/nodes/edges relations |
| `app/Models/FlowNode.php` | FlowNode model with flow/sourceEdges/targetEdges relations |
| `app/Models/FlowEdge.php` | FlowEdge model with flow/sourceNode/targetNode relations |
| `database/factories/FlowFactory.php` | Factory for Flow |
| `database/factories/FlowNodeFactory.php` | Factory for FlowNode |
| `database/factories/FlowEdgeFactory.php` | Factory for FlowEdge |
| `app/Http/Controllers/api/AuthController.php` | JWT auth: register, login, me, logout |
| `app/Http/Controllers/api/FlowController.php` | CRUD + saveNodes/saveEdges |
| `database/seeders/RoleAndPermissionSeeder.php` | Roles, permissions, super-admin user seed |

### Modified Files
| File | Change |
|------|--------|
| `backend/routes/api.php` | Added public auth routes + protected Flow routes |
| `backend/database/seeders/DatabaseSeeder.php` | Calls `RoleAndPermissionSeeder` |
| `mdFiles/schema.md` | Added 3NF verification table + model documentation |
| `mdFiles/Todo.md` | Checked off models, controllers, seeders |
| `mdFiles/Review.md` | Marked auth/role setup as verified |

### Deleted Code
*(None)*

---

## Log-2026-06-04-005 — Auth Simplification, API Resources, Cookie-Based JWT

### Summary
- **Removed all explicit `Auth::guard('api')`** calls — default guard is already `api` (set via `AUTH_GUARD=api` in `.env`). Now uses plain `Auth::user()`, `Auth::id()`, `auth()->attempt()`.
- **Created API Resources:** `UserResource`, `FlowResource`, `FlowNodeResource`, `FlowEdgeResource` — clean, consistent JSON response shapes.
- **Cookie-based JWT:** Token stored in HTTP-only, Secure, SameSite=Strict cookie (`jwt_token`). Removed token from JSON response body for XSS protection.
- **Created `JwtCookieMiddleware`** — reads `jwt_token` cookie and injects it into `Authorization: Bearer` header, so `auth:api` middleware picks it up transparently.
- **Registered middleware** prepended to `api` group in `bootstrap/app.php`.
- **Fixed `BaseController::success`** — was returning `$data` instead of the wrapped `$response` array.

### Response Shape (before → after)
```
// BEFORE (buggy — no wrapper)
{ "user": {...}, "token": "..." }

// AFTER (proper wrapper, no token in body)
{
  "success": true,
  "status": 200,
  "message": "Login successful",
  "data": { "user": { "id": 1, "name": "...", "roles": [...], "permissions": [...] } }
}
// Token delivered via Set-Cookie: jwt_token=...; httponly; secure; samesite=strict
```

### Added Files
| File | Layer | Purpose |
|------|-------|---------|
| `app/Http/Middleware/JwtCookieMiddleware.php` | Middleware | Reads JWT from cookie → sets `Authorization` header |
| `app/Http/Resources/UserResource.php` | Resource | Clean user shape with roles/permissions |
| `app/Http/Resources/FlowResource.php` | Resource | Flow shape with lazy-loaded nodes/edges |
| `app/Http/Resources/FlowNodeResource.php` | Resource | Node shape |
| `app/Http/Resources/FlowEdgeResource.php` | Resource | Edge shape |

### Modified Files
| File | Change |
|------|--------|
| `backend/app/Http/Controllers/api/AuthController.php` | Uses `UserResource`, sets `jwt_token` cookie, token removed from body |
| `backend/app/Http/Controllers/api/FlowController.php` | Uses `FlowResource`/`FlowNodeResource`, all `Auth::guard('api')` → `Auth::` |
| `backend/app/Http/Controllers/api/BaseController.php` | Fixed `success()` → returns wrapped `$response` |
| `backend/app/Services/Auth/AuthService.php` | Removed all `->guard('api')` calls |
| `backend/app/Services/Auth/RegisterService.php` | Removed `->guard('api')` call |
| `backend/bootstrap/app.php` | Registered `JwtCookieMiddleware` prepended to `api` group |
| `openspec/changes/archive/logs.md` | Appended this log entry |

---

## Log-2026-06-04-004 — Refactor to Repository/Service Pattern + Form Requests

### Summary
- Refactored all controllers to **zero inline validation/business logic**:
  - **Form Requests** handle validation (6 classes)
  - **Repositories** handle DB queries (4 interfaces + 4 implementations)
  - **Services** handle business logic (3 classes)
- Created `RepositoryServiceProvider` to bind interfaces → implementations
- Fixed `MakeRepositoryCommand`: changed `Str::kebab()` → `Str::snake()` for valid PHP namespace identifiers
- All 11 API endpoints tested and working

### Added Files
| File | Layer | Purpose |
|------|-------|---------|
| `app/Http/Requests/Auth/RegisterRequest.php` | Request | Register validation |
| `app/Http/Requests/Auth/LoginRequest.php` | Request | Login validation |
| `app/Http/Requests/Flow/StoreFlowRequest.php` | Request | Store flow validation |
| `app/Http/Requests/Flow/UpdateFlowRequest.php` | Request | Update flow validation |
| `app/Http/Requests/Flow/SaveNodesRequest.php` | Request | Save nodes validation |
| `app/Http/Requests/Flow/SaveEdgesRequest.php` | Request | Save edges validation |
| `app/Repositories/user/UserRepositoryInterface.php` | Interface | User repository contract |
| `app/Repositories/user/UserRepository.php` | Repository | User DB queries |
| `app/Repositories/flow/FlowRepositoryInterface.php` | Interface | Flow repository contract |
| `app/Repositories/flow/FlowRepository.php` | Repository | Flow DB queries |
| `app/Repositories/flow_node/FlowNodeRepositoryInterface.php` | Interface | FlowNode repository contract |
| `app/Repositories/flow_node/FlowNodeRepository.php` | Repository | FlowNode DB bulk ops |
| `app/Repositories/flow_edge/FlowEdgeRepositoryInterface.php` | Interface | FlowEdge repository contract |
| `app/Repositories/flow_edge/FlowEdgeRepository.php` | Repository | FlowEdge DB bulk ops |
| `app/Services/Auth/RegisterService.php` | Service | User registration logic |
| `app/Services/Auth/AuthService.php` | Service | Login, me, logout logic |
| `app/Services/Flow/FlowService.php` | Service | Flow CRUD + saveNodes/saveEdges |
| `app/Providers/RepositoryServiceProvider.php` | Provider | DI bindings for all repository interfaces |

### Modified Files
| File | Change |
|------|--------|
| `backend/app/Http/Controllers/api/AuthController.php` | Removed all `Request`/`Validator`/`User` imports; uses `RegisterRequest`, `LoginRequest`, `RegisterService`, `AuthService` |
| `backend/app/Http/Controllers/api/FlowController.php` | Removed all `Request`/`Validator`/`Flow` imports; uses `StoreFlowRequest`/etc + `FlowService` |
| `backend/app/Console/Commands/MakeRepositoryCommand.php` | `Str::kebab()` → `Str::snake()` for valid PHP namespace identifiers |
| `openspec/changes/archive/logs.md` | Appended this log entry |

### Architecture Layer Diagram
```
HTTP Request
  → FormRequest (validation)
    → Controller (thin — wires request→service→response)
      → Service (business logic, orchestrates repositories)
        → RepositoryInterface ← bound via RepositoryServiceProvider
          → Repository (Eloquent queries)
            → Model
```

---

## Log-2026-06-04-006 — Frontend: Tailwind v4 + PrimeVue 4 + axios + Login Page + Playwright E2E

### Summary
- Installed `tailwindcss` + `@tailwindcss/vite` (Tailwind v4 plugin approach)
- Installed `primevue` + `primeicons` (PrimeVue 4 components)
- Installed `axios` + `vue-router@4`
- Created `frontend/src/api/apiClient.js` — axios instance with `withCredentials: true`, response interceptor unwraps `{success, status, message, data}`.
- Created `frontend/src/router/index.js` — Vue Router with `/login` route
- Created `frontend/src/views/Login.vue` — login form, POST `/api/login`, redirects to `/dashboard`
- Created reusable PrimeVue wrappers in `components/`:
  - `AppButton.vue` — `btn-primary` / `btn-secondary` variants
  - `AppInput.vue` — form input with label
  - `AppCard.vue` — card container with title/subtitle
  - `AppNavbar.vue` — navigation bar with PrimeVue Menubar
- Configured Tailwind v4 in `vite.config.js` (added `tailwindcss()` plugin)
- Rewrote `style.css` — `@import "tailwindcss"`, `@theme` surface palette, reusable utility classes (`.form-input`, `.btn-primary`, `.btn-secondary`, `.card`, `.error-msg`)
- Created `backend/config/cors.php` — allows `localhost:3000` with `supports_credentials: true`
- Updated `playwright.config.js` — array webServer (backend `php artisan serve` + frontend `npm run dev`), system Chrome channel
- Created `tests/login.spec.js` — 2 E2E tests (valid login + invalid credentials)
- All tests passing: `✓ 2 passed (4.1s)`
- Cleaned `main` branch (removed mdFiles, tests, openspec, root configs from tracking)
- Locked `main` branch on GitHub (branch protection: 1 review required, enforce admins, lock_branch, no force-push)

### Added Files
| File | Purpose |
|------|---------|
| `backend/config/cors.php` | CORS config: allows localhost:3000 with credentials |
| `frontend/src/api/apiClient.js` | Axios instance with response unwrapper interceptor |
| `frontend/src/router/index.js` | Vue Router with `/login` route |
| `frontend/src/views/Login.vue` | Login page using PrimeVue + Tailwind components |
| `frontend/src/components/AppButton.vue` | PrimeVue Button wrapper |
| `frontend/src/components/AppInput.vue` | PrimeVue InputText wrapper |
| `frontend/src/components/AppCard.vue` | PrimeVue Card wrapper |
| `frontend/src/components/AppNavbar.vue` | PrimeVue Menubar wrapper |
| `frontend/src/components/index.js` | Barrel exports |
| `tests/login.spec.js` | Playwright E2E: valid + invalid login |

### Modified Files
| File | Change |
|------|--------|
| `frontend/vite.config.js` | Added `tailwindcss()` plugin |
| `frontend/src/style.css` | Replaced Vite defaults with Tailwind import, @theme, utility classes |
| `frontend/src/main.js` | Added router + PrimeVue plugin |
| `frontend/src/App.vue` | Replaced HelloWorld with `<router-view />` |
| `frontend/package.json` | Added axios, vue-router, tailwindcss, @tailwindcss/vite, primevue, primeicons |
| `playwright.config.js` | Array webServer (back + front), system Chrome channel |
| `mdFiles/Architecture.md` | Added frontend component architecture section |
| `mdFiles/Todo.md` | Marked login, auth, PrimeVue, Tailwind items complete |
| `mdFiles/Review.md` | Marked Playwright tests, CORS, branch protection items |
| `mdFiles/Skills.md` | Added PrimeVue + component structure rules |
| `mdFiles/gitMd/Commit.md` | Added all today's commit entries |

### Deleted Code
| File | Reason |
|------|--------|
| `.github/workflows/playwright.yml` | Removed from main tracking |
| `frontend/src/components/HelloWorld.vue` | Replaced by login page + reusable components |

---

## Log-2026-06-04-007 — Reusable Flowchart Node Components

### Summary
- Installed `@vue-flow/core` for Vue Flow canvas integration
- Created `BaseNode.vue` — shared node wrapper with Handle ports (source/target), color theme variants (blue/green/purple/amber/slate), selected ring, label header with PrimeIcons
- Created `TableNode.vue` — database table schema node: renders column list with type, PK/FK/UQ badges
- Created `LogicNode.vue` — logic/function node: displays description, input list with purple badges, output with emerald badge
- Created `FolderFileNode.vue` — folder/file mapping node: shows path, children tree preview with folder/file icons, amber for folders / slate for files
- All node types support 5 color themes, drag preview, and connection handles via Vue Flow's Handle component

### Added Files
| File | Purpose |
|------|---------|
| `frontend/src/components/nodes/BaseNode.vue` | Shared node wrapper with handles, colors, selection ring |
| `frontend/src/components/nodes/TableNode.vue` | DB table schema node |
| `frontend/src/components/nodes/LogicNode.vue` | Logic/function node |
| `frontend/src/components/nodes/FolderFileNode.vue` | Folder/file mapping node |
| `frontend/src/components/nodes/index.js` | Barrel exports |

### Modified Files
| File | Change |
|------|--------|
| `mdFiles/Architecture.md` | Added nodes/ directory to frontend structure |
| `mdFiles/Todo.md` | Marked @vue-flow/core + node components complete |
| `mdFiles/Review.md` | Marked node components review item |
| `mdFiles/gitMd/Commit.md` | Added node components entry |

---

## Log-2026-06-08-001 — Table Designer, Logic Designer & Interactive Canvas

### Summary
- **Table Designer page** (`/tables`): Full CRUD for database table schemas with modal form, column builder (name, type, PK/FK/UQ checkboxes), card list with preview badges.
- **Logic Designer page** (`/logics`): Full CRUD for logic/function definitions with modal form, dynamic inputs list, output field, card list with input/output tag previews.
- **Backend**: Created `table_definitions` and `logic_definitions` migrations, Eloquent models, repositories (with interfaces), services, FormRequests, API Resources, and RESTful controllers.
- **Combined save endpoint** (`POST /api/flows/{flow}/save`): Accepts both nodes and edges in one request, recreates nodes with `bulkCreateWithReturn` (returns temp→DB ID map), remaps edge references, saves edges, returns updated flow + node ID map.
- **Sidebar.vue**: Drag-and-drop palette with 3 node types (Table blue, Logic purple, Folder/File amber) using native HTML5 drag API.
- **Canvas.vue**: Flow selector dropdown, "+ New Flow" inline creation, Save button, drag-drop from sidebar via `screenToFlowCoordinate`, `v-model:nodes/edges` for Vue Flow state, `markRaw` for nodeTypes, `onConnect` handler for edge creation.
- **Definition-to-Node bridge**: Canvas loads all user's table/logic definitions via `Promise.all`, creates nodes with `definitionId` tracking, skips definitions already saved as flow nodes (preserving positions).
- **Navigation**: Header links between Canvas, Tables, Logics pages; refresh (⟳) button to reload definitions.

### Added Files
| File | Purpose |
|------|---------|
| `backend/database/migrations/..._create_table_definitions_table.php` | `table_definitions` table |
| `backend/database/migrations/..._create_logic_definitions_table.php` | `logic_definitions` table |
| `backend/app/Models/TableDefinition.php` | TableDefinition Eloquent model |
| `backend/app/Models/LogicDefinition.php` | LogicDefinition Eloquent model |
| `backend/app/Repositories/table_definition/` | TableDefinition repo interface + implementation |
| `backend/app/Repositories/logic_definition/` | LogicDefinition repo interface + implementation |
| `backend/app/Services/TableDefinition/TableDefinitionService.php` | TableDefinition business logic |
| `backend/app/Services/LogicDefinition/LogicDefinitionService.php` | LogicDefinition business logic |
| `backend/app/Http/Requests/TableDefinition/` | Store + Update form requests |
| `backend/app/Http/Requests/LogicDefinition/` | Store + Update form requests |
| `backend/app/Http/Resources/TableDefinitionResource.php` | API resource |
| `backend/app/Http/Resources/LogicDefinitionResource.php` | API resource |
| `backend/app/Http/Controllers/api/TableDefinitionController.php` | RESTful controller |
| `backend/app/Http/Controllers/api/LogicDefinitionController.php` | RESTful controller |
| `frontend/src/api/tables.js` | Table definitions API layer |
| `frontend/src/api/logics.js` | Logic definitions API layer |
| `frontend/src/views/TableDesigner.vue` | Table CRUD page |
| `frontend/src/views/LogicDesigner.vue` | Logic CRUD page |
| `frontend/src/components/Sidebar.vue` | Drag-drop node palette |
| `frontend/src/styles/sidebar.css` | Sidebar utility classes (in style.css) |

### Modified Files
| File | Change |
|------|--------|
| `backend/routes/api.php` | Added `/api/tables` and `/api/logics` resource routes, `/api/flows/{flow}/save` |
| `backend/app/Providers/RepositoryServiceProvider.php` | Bound TableDefinition + LogicDefinition repos |
| `backend/app/Repositories/flow_node/FlowNodeRepository.php` | Added `bulkCreateWithReturn()` |
| `backend/app/Repositories/flow_node/FlowNodeRepositoryInterface.php` | Added `bulkCreateWithReturn()` contract |
| `backend/app/Http/Requests/Flow/SaveFlowRequest.php` | New combined save validation |
| `backend/app/Services/Flow/FlowService.php` | Added `save()` method with node→edge mapping |
| `backend/app/Http/Controllers/api/FlowController.php` | Added `save()` endpoint |
| `frontend/src/router/index.js` | Added `/tables` and `/logics` routes |
| `frontend/src/views/Canvas.vue` | Loads definitions, nav links, refresh, markRaw |
| `frontend/src/style.css` | Added sidebar, btn-sm, btn-danger, modal utility classes |
| `mdFiles/Architecture.md` | Added designer pages, definition flow, full route table |
| `mdFiles/Todo.md` | Marked designer pages + canvas features complete |
| `mdFiles/Review.md` | Marked canvas + definition tests |
| `mdFiles/schema.md` | Added `table_definitions` and `logic_definitions` tables + migration order |
| `mdFiles/gitMd/Commit.md` | Added 2026-06-08 commit entries |

### Deleted Code
*(None)*

---

## Log-2026-06-08-002 — Canvas Tab Modes (Flow/Schema) with Domain Boundary Enforcement

### Summary
- **Canvas tabs**: Added mode switcher (`Flow` / `Schema`) to the canvas header — each mode registers only its relevant node types (`flowNodeTypes` for Logic+FolderFile, `schemaNodeTypes` for Table).
- **Domain boundary enforcement**: `isValidConnection` prop on VueFlow prevents connecting across domains — Schema mode only allows table↔table edges (FK relationships), Flow mode only allows logic↔logic, logic↔folderFile, folderFile↔folderFile edges.
- **Dual sidebars**: `Sidebar.vue` (Flow mode) shows Logic + Folder/File drag items; `SchemaSidebar.vue` (Schema mode) shows Table drag items only.
- **Contextual loading**: Canvas loads only the relevant definitions for the active mode — logics for Flow, tables for Schema.
- **Navigation**: Header shows contextual links — "Logics" link in Flow mode, "Tables" link in Schema mode.

### Added Files
| File | Purpose |
|------|---------|
| `frontend/src/components/SchemaSidebar.vue` | Schema-mode drag palette (Table only) |

### Modified Files
| File | Change |
|------|--------|
| `frontend/src/views/Canvas.vue` | Added mode tabs, `isValidConnection`, conditional node types, contextual loading |
| `frontend/src/components/Sidebar.vue` | Removed Table item (now only in SchemaSidebar) |
| `tests/canvas.spec.js` | Updated assertions for new sidebar content + mode tab checks |
| `mdFiles/Architecture.md` | Added Canvas Modes table, updated component structure |
| `mdFiles/Todo.md` | Marked mode separation complete |
| `mdFiles/Review.md` | Marked connection validation item |
| `mdFiles/gitMd/Commit.md` | Added mode separation entry |

---

## Log-2026-06-08-003 — Fix Route Model Binding for TableDefinition & LogicDefinition

### Summary
- **Bug**: Table definition and logic definition `update` endpoints were broken with `Argument #1 ($id) must be of type int, null given` because Laravel's implicit route model binding requires the controller parameter name to match the route parameter name.
- **Root cause**: `Route::apiResource('tables', ...)` generates `{table}` param, but the controller used `TableDefinition $tableDefinition`. Same for `{logic}` vs `LogicDefinition $logicDefinition`.
- **Fix**: Renamed `$tableDefinition` → `$table` and `$logicDefinition` → `$logic` in all controller methods (`show`, `update`, `destroy`).
- **Impact**: Users can now update table definitions with any column type (including DATETIME, TIMESTAMP, DATE) and update logic definitions.

### Modified Files
| File | Change |
|------|--------|
| `backend/app/Http/Controllers/api/TableDefinitionController.php` | `$tableDefinition` → `$table` in show/update/destroy |
| `backend/app/Http/Controllers/api/LogicDefinitionController.php` | `$logicDefinition` → `$logic` in show/update/destroy |
| `mdFiles/Review.md` | Marked fix as complete |
| `mdFiles/gitMd/Commit.md` | Added fix entry |
| `openspec/changes/archive/logs.md` | Appended this log entry |

---

## Log-2026-06-08-004 — Logic Inputs Upgraded to Structured {name, type} Objects

### Summary
Upgraded logic definitions from flat string inputs (`["x", "y"]`) to structured `{name, type}` objects (`[{name: "payload", type: "array"}, ...]`) — making the logic designer a proper workflow CRUD system with typed inputs like table columns.

### Changes
- `StoreLogicDefinitionRequest`: changed `inputs.*` from `string|max:255` to `inputs.*.name + inputs.*.type` (both `required_with:inputs|string|max:255`)
- `UpdateLogicDefinitionRequest`: same validation update
- `LogicDesigner.vue`: inputs form changed from single text field per row to name+type dual fields (like column builder); `openEdit` handles backward compat with old string inputs; card preview shows `name:type`
- `LogicNode.vue`: displays `name:type` badges, supports both old string and new object formats
- `Sidebar.vue`: default logic node data uses structured `[{name: 'input', type: 'any'}]`
- `Canvas.vue`: passes `typeField` from logic definitions to node data

### Modified Files
| File | Change |
|------|--------|
| `backend/app/Http/Requests/LogicDefinition/StoreLogicDefinitionRequest.php` | `inputs.*` → `inputs.*.name + inputs.*.type` |
| `backend/app/Http/Requests/LogicDefinition/UpdateLogicDefinitionRequest.php` | Same validation update |
| `frontend/src/views/LogicDesigner.vue` | name+type inputs, backward compat, card preview |
| `frontend/src/components/nodes/LogicNode.vue` | `name:type` badges, typeField line, backward compat |
| `frontend/src/components/Sidebar.vue` | Default logic data → structured inputs |
| `frontend/src/views/Canvas.vue` | Passes `typeField` from logic defs |
| `mdFiles/gitMd/Commit.md` | Added upgrade entry |

---

## Log-2026-06-08-005 — Register Page, Code Review Fixes & Security Audit

### Summary
- **Register page**: Created `Register.vue` with name/email/password/confirm form, added `/register` route, linked from Login page. Uses existing `POST /api/register` backend endpoint.
- **Critical bug fixes**: `BaseController::error()` checked `$errorMsg` instead of `$error` (dead code). `FlowController::saveEdges()` returned `FlowNodeResource` instead of `FlowEdgeResource`.
- **Frontend fixes**: Added missing `.node-tag-slate` CSS class; wrapped `Canvas.vue` onDrop `JSON.parse` in try/catch; added `deleting` loading state to both designer pages; enforced column `type` validation in TableDesigner.
- **Security audit**: Verified ownership checks on all CRUD controllers. Documented 3 remaining items (rate limiting, password complexity, JWT refresh flow).

### Added Files
| File | Purpose |
|------|---------|
| `frontend/src/views/Register.vue` | User registration form |

### Modified Files
| File | Change |
|------|--------|
| `backend/app/Http/Controllers/api/BaseController.php` | `$errorMsg` → `$error` in empty check |
| `backend/app/Http/Controllers/api/FlowController.php` | Import+use `FlowEdgeResource` in saveEdges |
| `frontend/src/router/index.js` | Added `/register` → Register.vue route |
| `frontend/src/views/Login.vue` | Added register link |
| `frontend/src/views/TableDesigner.vue` | Added `deleting` state, null columns guard, type validation |
| `frontend/src/views/LogicDesigner.vue` | Added `deleting` state |
| `frontend/src/views/Canvas.vue` | try/catch on onDrop JSON.parse |
| `frontend/src/style.css` | Added `.node-tag-slate` class |
| `mdFiles/Todo.md` | Added security & code quality items |
| `mdFiles/Review.md` | Added audit findings, security TODOs |
| `mdFiles/Architecture.md` | Added register route |
| `mdFiles/gitMd/Commit.md` | Added full summary |
| `openspec/changes/archive/logs.md` | Appended this log entry |

---

## Log-2026-06-09-001 — Help Guide, Edge/Node Deletion & Project Documentation Update

### Summary
- **Help guide page**: Created `HelpGuide.vue` at `/help` with comprehensive how-to documentation — authentication, canvas modes (Flow/Schema), drag-drop nodes, connecting with edges, deleting edges/nodes, save/load flows, Table Designer, Logic Designer, and keyboard shortcuts.
- **Edge/node deletion**: Added `delete-key-code="['Delete', 'Backspace']"` prop to VueFlow, `@edge-click` selection handler, and `@edges-delete`/`@nodes-delete` event handlers that remove selected elements from the reactive arrays on Delete/Backspace keypress.
- **Project READMEs**: Replaced stock Vue/Laravel template READMEs with project-specific documentation covering tech stack, pages/routes, and development setup.
- **Documentation update**: Updated all `mdFiles/` (Architecture.md, Review.md, Skills.md, schema.md, Todo.md, Commit.md) with current timestamps and new feature entries.

### Added Files
| File | Purpose |
|------|---------|
| `frontend/src/views/HelpGuide.vue` | How-to guide page with app usage instructions and keyboard shortcuts |

### Modified Files
| File | Change |
|------|--------|
| `frontend/src/router/index.js` | Added `/help` route pointing to `HelpGuide.vue` |
| `frontend/src/views/Canvas.vue` | Added `delete-key-code`, edge/node delete handlers, Help nav link |
| `frontend/README.md` | Replaced stock Vue template with project-specific readme |
| `backend/README.md` | Replaced stock Laravel readme with project-specific readme |
| `mdFiles/Architecture.md` | Added `/help` route, HelpGuide.vue to component tree, updated timestamps |
| `mdFiles/Review.md` | Added edge deletion to verified items |
| `mdFiles/Skills.md` | Updated timestamp |
| `mdFiles/schema.md` | Updated timestamp |
| `mdFiles/Todo.md` | Added help guide + edge deletion as completed |
| `mdFiles/gitMd/Commit.md` | Added 2026-06-09 commit entries |

---

## Log-2026-06-08-006 — Strict Cross-Mode Node Filtering (Absolute Domain Separation)

### Summary
Enhanced the Canvas mode separation to ensure **absolute domain isolation** between Flow and Schema modes:

- **Load filtering**: `loadFlowData()` now filters saved nodes by the active mode. Flow mode skips `table` nodes; Schema mode skips `logic` and `folderFile` nodes. This prevents stale cross-mode nodes from appearing after mode switch.
- **Drop validation**: `onDrop()` validates the dropped item's type against the active mode via `isAllowedType()`. Logic/folderFile items dropped on Schema mode are silently ignored; table items dropped on Flow mode are silently ignored.
- Together with the existing `isValidConnection` guard (which blocks cross-domain edges), this provides **triple protection**: nodes can't be loaded, can't be dropped, and can't be connected across domains.

### Modified Files
| File | Change |
|------|--------|
| `frontend/src/views/Canvas.vue` | Added mode-based node filtering on load + `isAllowedType()` guard in onDrop |
| `mdFiles/Architecture.md` | Documented strict mode filtering |
| `mdFiles/Todo.md` | Marked cross-mode filtering complete |
| `mdFiles/Review.md` | Added cross-mode filtering to verified items |
| `mdFiles/gitMd/Commit.md` | Added entry |

---

## Log-2026-06-09-002 — Admin Management, Tier Upgrade, Route Guard, Permissions Enforcement

### Summary
- **Route guard extracted**: Moved from `router/index.js` to `router/routeGuard.js` — reusable `adminGuard` with async store import, keeps router config clean.
- **AdminController** created with 6 endpoints: `stats`, `users` (paginated), `show`, `update`, `updateRoles`, `upgrade`, `destroy`.
- **Admin dashboard** (`AdminDashboard.vue`): users table with role editing modal, tier upgrade modal (with backend confirmation dialog), user delete with confirmation, tier definitions section showing all 4 tiers and their permissions.
- **Tier upgrade endpoint** (`PUT /api/admin/users/{user}/upgrade`): validates target tier against valid Spatie roles, prevents super-admin downgrade, syncs role and returns `{user, confirmed: true}`.
- **Permission middleware** on all write API routes:
  - `permission:save-flows` on flow POST/PUT/DELETE/saveNodes/saveEdges/save
  - `permission:generate-schema` on table POST/PUT/DELETE
  - `permission:map-structure` on logic POST/PUT/DELETE
  - `role:super-admin` on all admin endpoints
  - All GET routes remain open to any authenticated user
- **Rate limiting**: Login endpoint throttled to 20 attempts/minute (up from 5) via `RateLimiter::for('login')` in `AppServiceProvider`.
- **Password complexity**: `RegisterRequest` validates uppercase + lowercase + digit + special character with custom error message.
- **Login form redesigned**: Floating labels, border-bottom underline inputs, dark card with centered layout.
- **Playwright tests** (`tests/admin.spec.js`): 4 tests covering admin dashboard rendering, tier definitions display (4 tiers), upgrade modal flow with confirmation, and non-admin route guard redirect.
- **All 14 tests passing**: admin (4), canvas (2), example (2), login (2), setup (4).
- **Git workflow**: Changes committed to `dev`, merged into `test`, both branches pushed to origin with full merge history.

### Added Files
| File | Layer | Purpose |
|------|-------|---------|
| `backend/app/Http/Controllers/api/AdminController.php` | Controller | Admin endpoints: stats, users CRUD, tiers, upgrade |
| `frontend/src/api/admin.js` | API | Admin API client (getUsers, updateUserRoles, upgradeUser, getTiers) |
| `frontend/src/router/routeGuard.js` | Router | Extracted adminGuard with async store import |
| `tests/admin.spec.js` | Test | Playwright E2E: admin dashboard, tiers, upgrade, route guard |

### Modified Files
| File | Change |
|------|--------|
| `backend/routes/api.php` | Permission middleware on write routes, admin group, upgrade route, login throttle |
| `backend/app/Providers/AppServiceProvider.php` | RateLimiter for login (20/min) |
| `backend/app/Http/Requests/Auth/RegisterRequest.php` | Password complexity regex + custom error message |
| `backend/app/Http/Controllers/api/AuthController.php` | Removed stats method (moved to AdminController) |
| `frontend/src/router/index.js` | Imports adminGuard from routeGuard.js |
| `frontend/src/views/AdminDashboard.vue` | Users table, role modal, upgrade modal, tier definitions section |
| `frontend/src/views/Login.vue` | Redesigned with floating labels, border-bottom inputs |
| `frontend/src/style.css` | Added login page CSS (floating label, border-bottom), admin table, upgrade success classes |
| `tests/login.spec.js` | Updated login assertion for new h1 "Welcome Back" |
| `tests/canvas.spec.js` | Minor selector updates |
| `frontend/README.md` | Updated with admin panel, route guard, testing section |
| `backend/README.md` | Updated with full 29-route table, permission guards, rate limiting docs |
| `mdFiles/gitMd/Commit.md` | Added 2026-06-09 entries |
| `openspec/changes/archive/logs.md` | Appended this log entry |

### Deleted Code
| File | Reason |
|------|--------|
| `frontend/src/router/index.js:58-74` | inline `router.beforeEach` with admin guard → moved to `routeGuard.js` |
| `backend/app/Http/Controllers/api/AuthController.php:54-69` | `stats()` method → moved to `AdminController` |

