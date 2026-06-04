# Change Log Archive

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

