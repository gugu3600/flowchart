# Database Schema — `flowchart`

> Last updated: 2026-06-10 11:30 UTC

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

## Definition Tables (User-Managed Node Templates)

### `table_definitions`

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | Auto-increment |
| user_id | BIGINT FK | → `users.id` ON DELETE CASCADE |
| name | VARCHAR(255) | Table name |
| columns | JSON | Array of `{name, type, pk, fk, unique}` |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

- **Relations:** Belongs to `user`.
- **Canvas Integration:** Each definition auto-creates a `table`-type node on the canvas (tracked via `data.definitionId`).

---

### `logic_definitions`

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | Auto-increment |
| user_id | BIGINT FK | → `users.id` ON DELETE CASCADE |
| name | VARCHAR(255) | Logic/function name |
| description | TEXT | Nullable |
| inputs | JSON | Array of input parameter names |
| output | VARCHAR(255) | Nullable return type |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

- **Relations:** Belongs to `user`.
- **Canvas Integration:** Each definition auto-creates a `logic`-type node on the canvas (tracked via `data.definitionId`).

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
| subscription_expires_at | TIMESTAMP | Nullable — set on tier upgrade; auto-downgraded via `subscription:expire` command |
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

## 3NF Verification (PASSED)

| Form | Rule | Status |
|------|------|--------|
| **1NF** | Atomic columns, no repeating groups | ✅ PASS — each row is a single entity, JSON columns store single documents |
| **2NF** | Non-key columns depend on the full PK | ✅ PASS — all tables have single-column PKs |
| **3NF** | No transitive dependencies on non-key columns | ✅ PASS — `flow_nodes.config` and `flow_edges.config` describe their own rows, not inherited from `flows.config` |

### Key 3NF Design Decisions
- **`flows.config`** (JSON): stores *canvas-level* settings (background theme, default edge colors) — directly describes the flow.
- **`flow_nodes.config`** (JSON): stores *node-level* overrides (background color, border style) — directly describes the node.
- **`flow_edges.config`** (JSON): stores *edge-level* overrides (line color, dash pattern) — directly describes the edge.

These are independent because one flow can have multiple nodes each with different styling, which is the definition of **no transitive dependency**.

### Models
| Model | Table | Fillable | Relations |
|-------|-------|----------|-----------|
| `App\Models\Flow` | `flows` | user_id, name, description, config | belongsTo User, hasMany FlowNode, hasMany FlowEdge |
| `App\Models\FlowNode` | `flow_nodes` | flow_id, type, label, position_x, position_y, data, config | belongsTo Flow |
| `App\Models\FlowEdge` | `flow_edges` | flow_id, source_node_id, target_node_id, label, config | belongsTo Flow, belongsTo sourceNode/targetNode |

### Architecture Layers

```
┌─────────────┐
│  FormRequest │  ← validation (app/Http/Requests/)
├─────────────┤
│  Controller  │  ← thin — wires request → service → response
├─────────────┤
│   Service    │  ← business logic (app/Services/)
├─────────────┤
│  Repository  │  ← DB queries (app/Repositories/)
├─────────────┤
│    Model     │  ← Eloquent (app/Models/)
└─────────────┘
```

All repository interfaces are bound to implementations in `App\Providers\RepositoryServiceProvider`.

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
| 8 | `create_table_definitions_table` | User-managed table schemas |
| 9 | `create_logic_definitions_table` | User-managed logic definitions |
