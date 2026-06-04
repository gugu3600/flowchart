# Git Commits

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
