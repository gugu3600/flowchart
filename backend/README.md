# Flowchart — Backend

Laravel 13 RESTful API backend for the Flowchart architecture diagramming tool.

## Tech Stack

- Laravel 13
- MySQL 8.4
- tymon/jwt-auth (JWT authentication)
- spatie/laravel-permission (RBAC)
- Repository / Service / FormRequest pattern

## Key Features

- JWT authentication with HTTP-only Secure SameSite=Strict cookies
- Spatie RBAC with free/silver/gold/platinum tier roles
- Combined save endpoint (`POST /api/flows/{flow}/save`) with node→edge ID mapping
- Table and logic definition CRUD with column / input builders

## API Routes (22 total)

| Method | Route | Auth |
|--------|-------|------|
| POST | `/api/register` | Public |
| POST | `/api/login` | Public |
| GET | `/api/me` | Authenticated |
| POST | `/api/logout` | Authenticated |
| GET/POST | `/api/flows` | Authenticated |
| GET/PUT/DELETE | `/api/flows/{flow}` | Authenticated |
| POST | `/api/flows/{flow}/nodes` | Authenticated |
| POST | `/api/flows/{flow}/edges` | Authenticated |
| POST | `/api/flows/{flow}/save` | Authenticated |
| GET/POST | `/api/tables` | Authenticated |
| GET/PUT/DELETE | `/api/tables/{table}` | Authenticated |
| GET/POST | `/api/logics` | Authenticated |
| GET/PUT/DELETE | `/api/logics/{logic}` | Authenticated |

## Development

```bash
composer install
cp .env.example .env  # configure DB
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8000
```
