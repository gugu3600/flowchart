# Application Architecture & Strategic Tier Matrix (Current MVP Stage)

> Last updated: 2026-06-09 14:00 UTC

## Core System Stack
- **Frontend:** Vue 3 (Composition API) + Vite 8 + Tailwind CSS v4 + PrimeVue 4 + axios.
- **Backend:** Laravel 13 running RESTful APIs, MySQL Database.
- **Authentication:** JWT-based authentication using tymon/jwt-auth. Token stored in HTTP-only Secure SameSite=Strict cookie via `JwtCookieMiddleware`.
- **Role-Based Access Control:** Spatie Laravel Permissions (free/silver/gold/platinum tiers + super-admin).
- **Caching:** Laravel's built-in caching system.
- **Design Patterns:** Repository Pattern, Service Pattern, Form Request validation, API Resources.
- **Database Schema:** 3NF normalized MySQL schema for `flows`, `flow_nodes`, `flow_edges`, `table_definitions`, `logic_definitions`.
- **Testing:** Playwright (E2E) for frontend, PHPUnit for backend API tests.

## Frontend Pages
| Route | Page | Purpose |
|-------|------|---------|
| `/help` | HelpGuide.vue | How-to guide and documentation for the web app |
| `/login` | Login.vue | JWT login form, links to register |
| `/register` | Register.vue | User registration with tier selection (Free/Silver/Gold/Platinum), pricing display, and payment method picker (KBZ Pay / AYA Pay / CB Pay / MMQR) for paid tiers |
| `/canvas` | Canvas.vue | Tabbed Vue Flow canvas (Flow mode + Schema mode), drag-drop, save/load, live definitions, edge/node deletion via Delete/Backspace |
| `/tables` | TableDesigner.vue | CRUD for database table schemas (columns, types, PK/FK/UQ) |
| `/logics` | LogicDesigner.vue | CRUD for logic/function definitions (structured name/type inputs, output) |

## Canvas Modes (Tab-Separated)

| Mode | Tab | Node Types | Connection Rule | Sidebar |
|------|-----|------------|-----------------|---------|
| **Flow** | `Flow` | Logic, Folder/File | logic↔logic, logic↔folderFile, folderFile↔folderFile | Sidebar.vue (Logic + Folder/File) |
| **Schema** | `Schema` | Table | table↔table only (FK relationships) | SchemaSidebar.vue (Table only) |

The `isValidConnection` prop on VueFlow enforces domain boundaries: table nodes cannot connect to logic/folder nodes and vice versa.

Additionally, when loading saved flow data, nodes are **filtered by mode**:
- Flow mode: only `logic` and `folderFile` nodes are loaded (table nodes are silently skipped)
- Schema mode: only `table` nodes are loaded (logic/folderFile nodes are silently skipped)

The `onDrop` handler also rejects dropped items whose type doesn't match the active mode. This ensures **absolute separation** — tables never appear in Flow canvas, logic/folders never appear in Schema canvas, even if previously saved.

## Frontend Component Architecture
```
src/
├── api/
│   ├── apiClient.js          — Axios instance (withCredentials, response unwrapper)
│   ├── flows.js              — Flows CRUD + save API
│   ├── tables.js             — Table definitions CRUD API
│   └── logics.js             — Logic definitions CRUD API
├── assets/
│   └── style.css             — Tailwind import, @theme tokens, reusable utility classes
├── components/
│   ├── AppButton.vue         — PrimeVue Button wrapper (btn-primary / btn-secondary variants)
│   ├── AppInput.vue          — PrimeVue InputText wrapper with label
│   ├── AppCard.vue           — PrimeVue Card wrapper with title/subtitle/slot
│   ├── AppNavbar.vue         — PrimeVue Menubar wrapper
│   ├── Sidebar.vue           — Drag-and-drop palette (Flow mode: Logic, Folder/File)
│   ├── SchemaSidebar.vue     — Drag-and-drop palette (Schema mode: Table only)
│   ├── index.js              — Barrel exports
│   └── nodes/
│       ├── BaseNode.vue       — Shared node wrapper (Handle ports, color themes, selected ring)
│       ├── TableNode.vue      — DB table schema node (columns, PK/FK/UQ badges)
│       ├── LogicNode.vue      — Logic/function node (inputs, outputs, description)
│       ├── FolderFileNode.vue — Folder/file mapping node (path, children tree preview)
│       └── index.js           — Barrel exports
├── views/
│   ├── Login.vue             — Login form using AppCard / AppInput / AppButton + apiClient
│   ├── Canvas.vue            — Tabbed Vue Flow (Flow/Schema), flow selector, sidebar, save/load, edge/node deletion
│   ├── HelpGuide.vue         — How-to guide with app usage instructions and keyboard shortcuts
│   ├── Register.vue          — Registration form (name, email, password, confirm)
│   ├── TableDesigner.vue     — Table schema CRUD with modal form, column builder
│   └── LogicDesigner.vue     — Logic definition CRUD with modal form, inputs/output builder
├── router/
│   └── index.js              — Vue Router (/login, /register, /canvas, /help, /tables, /logics routes)
├── App.vue                   — <router-view /> root
└── main.js                   — createApp + router + PrimeVue plugin
```

## Registration Flow
1. User navigates to `/register`
2. Selects a tier from 4 cards: Free (0 MMK), Silver (3,000 MMK), Gold (6,000 MMK), Platinum (7,500 MMK)
3. Fills in name, email, password, confirm password
4. For paid tiers (Silver/Gold/Platinum), selects a payment method: KBZ Pay, AYA Pay, CB Pay, or MMQR
5. POST to `/api/register` with `{name, email, password, password_confirmation, tier, payment_method?}`
6. Backend creates user, assigns selected tier role, returns JWT in HTTP-only cookie
7. Free tier users can register without selecting any payment method
8. Payment processing and mail system will be implemented later via Laravel Queue

## Pricing
| Tier | Price (MMK) |
|------|-------------|
| Free | 0 |
| Silver | 3,000 |
| Gold | 6,000 |
| Platinum | 7,500 |

## Definition-to-Node Flow
1. User creates Table definitions on `/tables` or Logic definitions on `/logics`
2. On `/canvas`, switch to **Flow** tab for logic/folder flowcharts or **Schema** tab for table relationship diagrams
3. Canvas loads the relevant definitions for the active mode and creates nodes (with `definitionId` tracking)
4. Existing saved nodes in the active mode keep their positions; new definitions appear in a grid
5. `isValidConnection` enforces domain boundaries — table nodes cannot connect to logic nodes
6. User can reposition, connect, save; clicking refresh (⟳) reloads definitions

## API Routes (30 total)

### Public (throttled 20/min)
| Method | Route | Handler |
|--------|-------|---------|
| POST | `/api/register` | AuthController@register (accepts tier + payment_method) |
| POST | `/api/login` | AuthController@login |

### Authenticated (auth:api)
| Method | Route | Handler |
|--------|-------|---------|
| GET | `/api/me` | AuthController@me |
| POST | `/api/logout` | AuthController@logout |
| GET/POST | `/api/flows` | FlowController@index/store |
| GET/PUT/DELETE | `/api/flows/{flow}` | FlowController@show/update/destroy |
| POST | `/api/flows/{flow}/nodes` | FlowController@saveNodes |
| POST | `/api/flows/{flow}/edges` | FlowController@saveEdges |
| POST | `/api/flows/{flow}/save` | FlowController@save (combined) |
| GET/POST | `/api/tables` | TableDefinitionController@index/store |
| GET/PUT/DELETE | `/api/tables/{table}` | TableDefinitionController@show/update/destroy |
| GET/POST | `/api/logics` | LogicDefinitionController@index/store |
| GET/PUT/DELETE | `/api/logics/{logic}` | LogicDefinitionController@show/update/destroy |

## Strategic Monetization Matrix (Value-Based Hierarchy)
1. **Free Tier:**
   - Access to basic drag-and-drop canvas layout building.
   - *Strict Restriction:* Cannot save workflows. Trigger 403 Paywall on Save.
2. **🥈 Silver Tier (Basic Premium):**
   - **Unlimited Save Slots:** Unlocks complete flowchart blueprint saving backend logic.
   - **Canvas Customization:** Authorization to dynamically change route/edge colors and node background colors via UI.
3. **🥇 Gold Tier (Intermediate Premium):**
   - Includes all **Silver Tier** features.
   - **Database Auto-Generation:** Compiles frontend canvas layout JSON into clean SQL DDL scripts and interactive relational diagrams.
4. **💎 Platinum Tier (The Ultimate Creator Feature - Highest Perceived Value):**
   - Includes all **Gold Tier** and **Silver Tier** features.
   - **Visual Folder Mapping Engine:** Unlocks special `Folder/File Nodes` on the canvas. Users can draw flowchart connections from Logic/Function Nodes into Folder/File Nodes to architect their workspace layout visually.

## Future MVP Scale-Up Roadmap
- **Trigger Condition:** Monitor application users' ratings and feedback on the Visual Folder Mapping Engine.
- **Execution Step:** Once a stable rating threshold is confirmed, implement the custom backend generator script to parse the visual folder JSON mappings and compile full functional repository zip packages (Auto Code Generation) exclusively for Platinum/VIP status.

## Reusable Components
| Component | Used In | Purpose |
|-----------|---------|---------|
| `FloatingInput` | Login.vue, Register.vue | Floating-label input with bottom-border underline (v-model, type, id, label, autocomplete props) |
| `TierSelector` | Register.vue | 2×2 tier card grid (v-model, tiers array) |
| `PaymentMethodPicker` | Register.vue | 2×2 payment method button grid (v-model, methods array) |
| `AppButton` | Multiple views | Styled action button with loading state |
| `AppCard` | Multiple views | Card container with optional title/subtitle |

## Middleware & Access Gatekeeping (Gatekeeper Bounds)
- **Silver Check:** Wraps `/flows/save` endpoints. Verify user membership in ('silver', 'gold', 'platinum').
- **Gold Check:** Wraps `/flows/generate-schema` endpoints. Verify user membership in ('gold', 'platinum').
- **Platinum Check:** Wraps `/flows/map-structure` endpoints. Strictly restrict to user membership == 'platinum'.
- All violations must return `403 Forbidden` with localized and global paywall instructions.
