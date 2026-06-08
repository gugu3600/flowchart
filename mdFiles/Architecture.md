# Application Architecture & Strategic Tier Matrix (Current MVP Stage)

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
| `/login` | Login.vue | JWT login form |
| `/canvas` | Canvas.vue | Vue Flow canvas with drag-drop, save/load, live definitions |
| `/tables` | TableDesigner.vue | CRUD for database table schemas (columns, types, PK/FK/UQ) |
| `/logics` | LogicDesigner.vue | CRUD for logic/function definitions (inputs, output, description) |

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
│   ├── Sidebar.vue           — Drag-and-drop node palette
│   ├── index.js              — Barrel exports
│   └── nodes/
│       ├── BaseNode.vue       — Shared node wrapper (Handle ports, color themes, selected ring)
│       ├── TableNode.vue      — DB table schema node (columns, PK/FK/UQ badges)
│       ├── LogicNode.vue      — Logic/function node (inputs, outputs, description)
│       ├── FolderFileNode.vue — Folder/file mapping node (path, children tree preview)
│       └── index.js           — Barrel exports
├── views/
│   ├── Login.vue             — Login form using AppCard / AppInput / AppButton + apiClient
│   ├── Canvas.vue            — Vue Flow canvas with flow selector, sidebar, save/load
│   ├── TableDesigner.vue     — Table schema CRUD with modal form, column builder
│   └── LogicDesigner.vue     — Logic definition CRUD with modal form, inputs/output builder
├── router/
│   └── index.js              — Vue Router (/login, /canvas, /tables, /logics routes)
├── App.vue                   — <router-view /> root
└── main.js                   — createApp + router + PrimeVue plugin
```

## Definition-to-Node Flow
1. User creates a Table or Logic definition on `/tables` or `/logics`
2. Canvas loads all definitions and creates nodes automatically (with `definitionId` tracking)
3. Existing definition nodes in the flow keep their positions; new definitions appear in a grid
4. User can reposition, connect, save — definitions persist as flow nodes
5. Clicking the refresh (⟳) button reloads definitions without losing saved flow nodes
6. Editing definitions on the designer pages updates the next canvas load

## API Routes (22 total)

### Public
| Method | Route | Handler |
|--------|-------|---------|
| POST | `/api/register` | AuthController@register |
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

## Middleware & Access Gatekeeping (Gatekeeper Bounds)
- **Silver Check:** Wraps `/flows/save` endpoints. Verify user membership in ('silver', 'gold', 'platinum').
- **Gold Check:** Wraps `/flows/generate-schema` endpoints. Verify user membership in ('gold', 'platinum').
- **Platinum Check:** Wraps `/flows/map-structure` endpoints. Strictly restrict to user membership == 'platinum'.
- All violations must return `403 Forbidden` with localized and global paywall instructions.
