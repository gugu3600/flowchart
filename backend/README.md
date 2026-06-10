# Flowchart — Backend

Laravel 13 RESTful API backend for the Flowchart architecture diagramming tool.

## Tech Stack

- Laravel 13
- MySQL 8.4
- tymon/jwt-auth (JWT authentication via HTTP-only Secure SameSite=Strict cookies)
- spatie/laravel-permission (RBAC)
- Repository / Service / FormRequest pattern

## Key Features

- JWT cookie-based auth with `JwtCookieMiddleware` (extracts token from cookie into Authorization header)
- Spatie RBAC with 5 roles: `super-admin`, `free`, `silver`, `gold`, `platinum`
- Tier permissions: `save-flows`, `generate-schema`, `map-structure`, `manage-users`, `manage-roles`
- Permission middleware on all write endpoints (backend-enforced, not just frontend)
- Combined save endpoint (`POST /api/flows/{flow}/save`) with node→edge ID mapping
- Table and logic definition CRUD with column / input builders
- Admin management: user CRUD, role/tier assignment, upgrade endpoint
- Login rate limiting (20 attempts/minute) and password complexity validation
- Registration disabled for security (admin accounts via seeder only)

## API Routes (29 total)

| Method | Route | Auth / Guard |
|--------|-------|-------------|
| POST | `/api/login` | Public (throttled) |
| POST | `/api/register` | Disabled (commented out) |
| GET | `/api/me` | Authenticated |
| POST | `/api/logout` | Authenticated |
| POST | `/api/refresh` | Authenticated |
| GET | `/api/stats` | Authenticated |
| GET | `/api/flows` | Authenticated |
| POST | `/api/flows` | `permission:save-flows` |
| GET | `/api/flows/{flow}` | Authenticated |
| PUT | `/api/flows/{flow}` | `permission:save-flows` |
| DELETE | `/api/flows/{flow}` | `permission:save-flows` |
| POST | `/api/flows/{flow}/nodes` | `permission:save-flows` |
| POST | `/api/flows/{flow}/edges` | `permission:save-flows` |
| POST | `/api/flows/{flow}/save` | `permission:save-flows` |
| GET | `/api/tables` | Authenticated |
| POST | `/api/tables` | `permission:generate-schema` |
| GET | `/api/tables/{table}` | Authenticated |
| PUT | `/api/tables/{table}` | `permission:generate-schema` |
| DELETE | `/api/tables/{table}` | `permission:generate-schema` |
| GET | `/api/logics` | Authenticated |
| POST | `/api/logics` | `permission:map-structure` |
| GET | `/api/logics/{logic}` | Authenticated |
| PUT | `/api/logics/{logic}` | `permission:map-structure` |
| DELETE | `/api/logics/{logic}` | `permission:map-structure` |
| GET | `/api/admin/tiers` | `role:super-admin` |
| GET | `/api/admin/users` | `role:super-admin` |
| GET | `/api/admin/users/{user}` | `role:super-admin` |
| PUT | `/api/admin/users/{user}` | `role:super-admin` |
| PUT | `/api/admin/users/{user}/roles` | `role:super-admin` |
| PUT | `/api/admin/users/{user}/upgrade` | `role:super-admin` |
| DELETE | `/api/admin/users/{user}` | `role:super-admin` |

## Development

```bash
composer install
cp .env.example .env  # configure DB
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8000
```

Default admin: `admin@flowchart.dev` / `password` (created by `RoleAndPermissionSeeder`).

## Security

- **JWT TTL**: 60 minutes (configurable via `JWT_TTL` in `.env`). Automatic token refresh via `POST /api/refresh` extends the session transparently.
- **Password policy**: Minimum 8 characters with uppercase, lowercase, digit, and special character.
- **Rate limiting**: 20 attempts/minute on login and register.
- **RBAC**: Spatie permissions enforced via middleware on all write endpoints.
- **HTML sanitization**: All user-supplied labels (nodes, edges, flow names) are passed through `strip_tags()` before persisting.
- **Race condition prevention**: Subscription upgrades and flow creation use DB transactions with `lockForUpdate()` to serialize concurrent requests.
- **Cross-type node isolation**: Saving nodes in flow mode (logic/folderFile) preserves any existing schema mode (table) nodes and their edges, and vice versa. Each mode only touches its own node types.
