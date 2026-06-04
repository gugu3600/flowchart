# MVP Implementation Roadmap (Value-Tier Focus)

## [Phase 1: Core Canvas & Silver Tier Feature]
- [x] Initialize Vue 3 layout with `@vue-flow/core` integration and Tailwind styling elements.
- [x] Setup Laravel 13 backend structure with MySQL (JWT auth + Spatie RBAC).
- [x] Create custom artisan commands (`make:service`, `make:repository`) for repository/service pattern.
- [x] Configure openspec change logging (`openspec/changes/archive/logs.md`).
- [x] Set up Playwright E2E test framework for frontend UI testing.
- [x] Design and migrate `flowchart` database schema (3NF: flows, flow_nodes, flow_edges).
- [x] Document DB schema in `mdFiles/schema.md` (3NF verification PASSED).
- [x] Create Eloquent models: Flow, FlowNode, FlowEdge with all relationships.
- [x] Create API controllers: AuthController (register/login/me/logout), FlowController (CRUD + saveNodes/saveEdges).
- [x] Create RoleAndPermissionSeeder: super-admin (all perms), free, silver, gold, platinum tier roles.
- [x] Seed default Super Admin user (admin@flowchart.dev / password).
- [ ] Install `@vue-flow/core` and build Canvas.vue with Tailwind.
- [ ] Build layout state saving mechanisms triggered via `/flows/save` wrapped in Silver middleware checks.
- [ ] Implement reactive canvas customizations allowing real-time edge colors and node background modifications for Silver tier users.

## [Phase 2: Gold Tier Schema Compiler & Diagram View]
- [ ] Develop database schema compiler translating custom table nodes into valid Prisma configurations.
- [ ] Wire Laravel controllers to safely output SQL script dumps alongside compiled Prisma text patterns.
- [ ] Add interactive diagram rendering support inside the frontend canvas for generated structures.

## [Phase 3: Platinum Visual Folder Architecture Layout]
- [ ] Build custom Canvas `FolderFileNode` to act as target drop boundaries for function logic nodes.
- [ ] Write backend controllers to safely save the visual mapping JSON schema representing which functions connect to which files/folders.
- [ ] Implement localized paywall middleware intercepts to trigger 403 notifications for unauthorized tiers.

## [Phase 4: Launch, User Ratings Validation & Expansion]
- [ ] Deploy the 3-Tier MVP to production bounds.
- [ ] Monitor user ratings specifically tracking the acceptance of the Visual Folder Mapping feature by Vibe Coders.
- [ ] Post-validation step: Once user rating threshold is verified, initiate development of the custom backend script for automatic repository code generation based on the visual mapping.
