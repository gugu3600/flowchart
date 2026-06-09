# Git Commits

> Last updated: 2026-06-09 14:00 UTC

## 2026-06-09

- extracted FloatingInput, TierSelector, PaymentMethodPicker components from Register.vue
- refactored: Login.vue uses FloatingInput, Register.vue uses all 3 new components
- all component CSS now lives in style.css (global), not scoped
- updated Todo.md, Architecture.md (Reusable Components table), Commit.md, archive logs

- Moved route guard from router/index.js to router/routeGuard.js
- Created AdminController with users/show/update/updateRoles/upgrade/destroy endpoints
- Added admin management UI (AdminDashboard.vue): users table, role editing modal, delete confirmation, upgrade modal
- Added tier definitions section in admin dashboard (fetched from GET /api/admin/tiers)
- Added backend upgrade endpoint (PUT /api/admin/users/{user}/upgrade) with backend confirmation
- Permission middleware on all write endpoints (save-flows, generate-schema, map-structure)
- Rate limiting on login endpoint (20/min) with password complexity validation
- Login form redesigned: floating labels, border-bottom underline inputs
- Created Playwright admin tests (admin.spec.js): admin dashboard, tier definitions, upgrade flow, route guard
- All 14 Playwright tests passing
- Updated frontend/backend README.md with admin panel, route guard, permission docs
- Both dev and test branches pushed and merged

- Added edge/node deletion: `delete-key-code` prop + `@edges-delete` / `@nodes-delete` handlers in Canvas.vue
- Created HelpGuide.vue — how-to guide page at `/help` with usage instructions, feature overview, and keyboard shortcuts
- Added `/help` route to Vue Router and Help link in Canvas.vue header
- Updated all project documentation files (Architecture.md, Review.md, Skills.md, schema.md, Todo.md, Commit.md)
- Replaced stock frontend/backend README.md with project-specific descriptions

## 2026-06-08

- Built Table Designer page (`/tables`) — CRUD table schemas with column builder (name, type, PK/FK/UQ)
- Built Logic Designer page (`/logics`) — CRUD logic definitions with inputs/output builder
- Backend: `table_definitions` and `logic_definitions` migrations, models, repositories, services, controllers
- Combined save endpoint (`POST /api/flows/{flow}/save`) with node→edge ID mapping
- Sidebar.vue drag-and-drop palette (Logic, Folder/File node types)
- SchemaSidebar.vue drag-and-drop palette (Table only)
- Canvas.vue: two mode tabs (Flow/Schema) with separate node type registrations
- `isValidConnection` enforces domain boundaries — tables cannot connect to logics
- Canvas auto-loads relevant definitions based on active mode
- All 10 Playwright tests passing
- Fixed route model binding bug: TableDefinition & LogicDefinition controller params renamed to match route params ($table, $logic)
- Table designer now supports DATETIME, TIMESTAMP, DATE column types (free-text type field) with working create/update
- Logic inputs upgraded from flat strings to structured {name, type} objects (workflow-style CRUD)
- Backend Store/UpdateLogicDefinitionRequest validates inputs.*.name + inputs.*.type
- LogicNode.vue shows "name:type" badges, handles backward compat with old string inputs
- LogicDesigner.vue has name+type input fields per row (like table column builder)
- Full code review completed: fixed BaseController error() check, FlowController resource type, missing node-tag-slate CSS, try/catch on Canvas onDrop, added deleting states to CRUD pages, column type validation
- Created Register.vue view with /register route, linked from login page
- Security audit: ownership verified for all CRUD controllers; rate limiting, password complexity, JWT refresh flow identified as TODO items
- Strict cross-mode node filtering: saved nodes filtered by mode on load; onDrop rejects wrong-mode drops; tables never appear in Flow, logics/folders never appear in Schema

## 2026-06-04

- Initial project setup with backend (Laravel) and frontend (Vue + Vite)
- Installed spatie/laravel-permission for RBAC
- Installed tymon/jwt-auth for API authentication
- Created custom artisan commands: make:service, make:repository
- Configured API routes with spatie permission middleware
- Set up openspec change logging system
- Configured Playwright E2E test framework
- Created dev and test branches
- Switched DB to `flowchart`, designed 3NF schema (flows, flow_nodes, flow_edges)
- Created `mdFiles/schema.md` documenting full DB schema
- Created `tests/setup.spec.js` for Playwright smoke tests
- Created Eloquent models (Flow, FlowNode, FlowEdge) with relationships
- Created AuthController (JWT register/login/me/logout) + FlowController (CRUD)
- Created RoleAndPermissionSeeder with super-admin + free/silver/gold/platinum tier roles
- 3NF verification PASSED
- Refactored to Repository/Service pattern: Form Requests for validation, Repositories for DB, Services for logic
- RepositoryServiceProvider with interface→implementation bindings
- Removed Auth::guard('api') — default guard is already api
- API Resources (UserResource, FlowResource, FlowNodeResource, FlowEdgeResource)
- JWT stored in HTTP-only Secure cookie (XSS-safe), removed from response body
- JwtCookieMiddleware reads cookie and injects Authorization header
- Fixed BaseController response wrapper
- Removed mdFiles/, tests/, openspec/, root configs from main tracking; locked main branch
- Created backend/config/cors.php for frontend (localhost:3000) CORS with credentials
- Installed axios + vue-router in frontend, created apiClient.js with response interceptor
- Set up Vue Router with /login route
- Created Login.vue with login form + apiClient integration
- Installed Tailwind CSS v4 + PrimeVue 4 + primeicons
- Created reusable components: AppCard, AppButton, AppInput, AppNavbar (PrimeVue + Tailwind wrappers)
- Added @theme surface palette + reusable utility classes (.form-input, .btn-primary, .btn-secondary, .card, .error-msg) in style.css
- Playwright E2E login tests: valid credentials + invalid credentials (2 tests, passing)
- Installed @vue-flow/core and created reusable node components: BaseNode, TableNode, LogicNode, FolderFileNode
