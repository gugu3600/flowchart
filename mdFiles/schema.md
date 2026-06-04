# Database Schema — `flowchart`

## Overview

- **Engine:** MySQL 8.4
- **Database:** `flowchart`
- **Design:** 3NF (Third Normal Form)
- **Auth Guard:** `api` (JWT stateless)

## Entity Relationship

```
users (1) ──< flows (1) ──< flow_nodes
                      └──< flow_edges (source_node_id ──> flow_nodes)
                                       (target_node_id ──> flow_nodes)
```

---

## Core Tables

### `users` (Laravel default + Spatie RBAC)

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | Auto-increment |
| name | VARCHAR(255) | |
| email | VARCHAR(255) | UNIQUE |
| email_verified_at | TIMESTAMP | Nullable |
| password | VARCHAR(255) | Hashed |
| remember_token | VARCHAR(100) | Nullable |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

- **Relations:** Has many `flows`. Has roles/permissions via Spatie `model_has_roles`, `model_has_permissions`.

---

### `flows`

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | Auto-increment |
| user_id | BIGINT FK | → `users.id` ON DELETE CASCADE |
| name | VARCHAR(255) | Flowchart blueprint name |
| description | TEXT | Nullable |
| config | JSON | Nullable — canvas settings (edge colors, node styles) |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

- **Relations:** Belongs to `user`. Has many `flow_nodes` and `flow_edges`.

---

### `flow_nodes`

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | Auto-increment |
| flow_id | BIGINT FK | → `flows.id` ON DELETE CASCADE |
| type | VARCHAR(255) | Node type: `table`, `logic`, `folder_file` |
| label | VARCHAR(255) | Display label on canvas |
| position_x | FLOAT | Canvas X coordinate |
| position_y | FLOAT | Canvas Y coordinate |
| data | JSON | Nullable — node-specific payload |
| config | JSON | Nullable — styling overrides (background color, etc.) |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

- **Relations:** Belongs to `flow`. Has edges as source/target via `flow_edges`.

---

### `flow_edges`

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | Auto-increment |
| flow_id | BIGINT FK | → `flows.id` ON DELETE CASCADE |
| source_node_id | BIGINT FK | → `flow_nodes.id` ON DELETE CASCADE |
| target_node_id | BIGINT FK | → `flow_nodes.id` ON DELETE CASCADE |
| label | VARCHAR(255) | Nullable — edge label |
| config | JSON | Nullable — color/style overrides |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

- **Relations:** Belongs to `flow`. Belongs to `source_node` and `target_node` (both `flow_nodes`).

---

## Spatie Permission Tables (auto-managed)

| Table | Purpose |
|-------|---------|
| `permissions` | All registered permissions (e.g. `save-flows`, `generate-schema`, `map-structure`) |
| `roles` | Roles: `free`, `silver`, `gold`, `platinum` |
| `model_has_roles` | User ↔ Role assignment |
| `model_has_permissions` | Direct user ↔ permission (rare, prefer roles) |
| `role_has_permissions` | Role ↔ permission mapping |

---

## 3NF Justification

1. **No repeating groups:** Each node/edge is a separate row, no JSON arrays of children.
2. **Full PK dependency:** Every non-key column depends on the full primary key.
3. **No transitive dependencies:** Node config does not depend on flow config; they are separate JSON columns because their schemas differ.

---

## Migration Order

| # | Migration | Purpose |
|---|-----------|---------|
| 1 | `create_users_table` | Laravel auth base |
| 2 | `create_cache_table` | Cache store |
| 3 | `create_jobs_table` | Queue jobs |
| 4 | `create_permission_tables` | Spatie RBAC |
| 5 | `create_flows_table` | Flowchart projects |
| 6 | `create_flow_nodes_table` | Canvas nodes |
| 7 | `create_flow_edges_table` | Canvas connections |
