# Application Architecture & Strategic Tier Matrix (Current MVP Stage)

## Core System Stack
- **Frontend:** Vue 3 (Composition API) + Vite 8 + Tailwind CSS v4 + PrimeVue 4 + axios.
- **Backend:** Laravel 13 running RESTful APIs, MySQL Database.
- **Authentication:** JWT-based authentication using tymon/jwt-auth. Token stored in HTTP-only Secure SameSite=Strict cookie via `JwtCookieMiddleware`.
- **Role-Based Access Control:** Spatie Laravel Permissions (free/silver/gold/platinum tiers + super-admin).
- **Caching:** Laravel's built-in caching system.
- **Design Patterns:** Repository Pattern, Service Pattern, Form Request validation, API Resources.
- **Database Schema:** 3NF normalized MySQL schema for `flows`, `flow_nodes`, `flow_edges`.
- **Testing:** Playwright (E2E) for frontend, PHPUnit for backend API tests.

## Frontend Component Architecture
```
src/
├── api/
│   └── apiClient.js          — Axios instance (withCredentials, response unwrapper)
├── assets/
│   └── style.css             — Tailwind import, @theme tokens, reusable utility classes
├── components/
│   ├── AppButton.vue         — PrimeVue Button wrapper (btn-primary / btn-secondary variants)
│   ├── AppInput.vue          — PrimeVue InputText wrapper with label
│   ├── AppCard.vue           — PrimeVue Card wrapper with title/subtitle/slot
│   ├── AppNavbar.vue         — PrimeVue Menubar wrapper
│   ├── index.js              — Barrel exports
│   └── nodes/
│       ├── BaseNode.vue       — Shared node wrapper (Handle ports, color themes, selected ring)
│       ├── TableNode.vue      — DB table schema node (columns, PK/FK/UQ badges)
│       ├── LogicNode.vue      — Logic/function node (inputs, outputs, description)
│       ├── FolderFileNode.vue — Folder/file mapping node (path, children tree preview)
│       └── index.js           — Barrel exports
├── views/
│   └── Login.vue             — Login form using AppCard / AppInput / AppButton + apiClient
├── router/
│   └── index.js              — Vue Router (/login route)
├── App.vue                   — <router-view /> root
└── main.js                   — createApp + router + PrimeVue plugin
```

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
