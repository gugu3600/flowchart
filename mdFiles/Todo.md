# MVP Implementation Roadmap (Value-Tier Focus)

> Last updated: 2026-06-08 12:00 UTC

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
- [ ] Build layout state saving mechanisms triggered via `/flows/save` wrapped in Silver middleware checks.
- [ ] Implement reactive canvas customizations allowing real-time edge colors and node background modifications for Silver tier users.

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
