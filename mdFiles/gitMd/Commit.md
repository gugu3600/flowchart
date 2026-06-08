# Git Commits

## 2026-06-08

- Built Table Designer page (`/tables`) — CRUD table schemas with column builder (name, type, PK/FK/UQ)
- Built Logic Designer page (`/logics`) — CRUD logic definitions with inputs/output builder
- Backend: `table_definitions` and `logic_definitions` migrations, models, repositories, services, controllers
- Combined save endpoint (`POST /api/flows/{flow}/save`) with node→edge ID mapping
- Sidebar.vue drag-and-drop palette (Table, Logic, Folder/File node types)
- Canvas.vue: flow selector, create/save flows, drag-drop from sidebar, markRaw for nodeTypes
- Canvas auto-loads table/logic definitions as nodes with definitionId tracking
- Added tailwind utility base `btn-sm` and reused across designer pages
- All 10 Playwright tests passing

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
