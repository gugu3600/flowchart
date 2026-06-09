# Flowchart — Frontend

Vue 3 + Vite frontend for the Flowchart architecture diagramming tool.

## Tech Stack

- Vue 3 (Composition API, `<script setup>`)
- Vite
- Tailwind CSS v4
- PrimeVue 4
- @vue-flow/core (canvas node editor)
- axios (API client)
- Vue Router

## Pages

| Route | Page | Purpose |
|-------|------|---------|
| `/login` | Login.vue | JWT login (floating-label design) |
| `/register` | Register.vue | User registration (disabled) |
| `/canvas` | Canvas.vue | Flowchart canvas (Flow + Schema modes) |
| `/help` | HelpGuide.vue | How-to guide and documentation |
| `/tables` | TableDesigner.vue | DB table schema CRUD |
| `/logics` | LogicDesigner.vue | Logic/function definition CRUD |
| `/admin` | AdminDashboard.vue | Admin panel — users, tiers, stats |

## Architecture

- **Route guard** extracted to `router/routeGuard.js` (admin guard with async store import)
- **User store** (`stores/useUserStore.js`): singleton reactive store (no Pinia) with tier/role checks
- **API layer** (`api/`): per-resource modules (flows, tables, logics, admin) wrapping axios client
- **Components** (`components/`): reusable UI — AppHeader, UserProfile, UpgradeModal, StatCard, Sidebar, node components
- **Global CSS** (`style.css`): all styling (no scoped CSS in views)
- **Tier enforcement**: `canSave` computed prevents save button for users without `save-flows` permission; backend also enforces via Spatie middleware

## Development

```bash
npm install
npm run dev
```

The dev server starts at `http://localhost:3000` (configured in `vite.config.js`).

## Testing

```bash
npx playwright test
```

Tests cover login flow, canvas operations, admin dashboard, tier upgrades, and route guards. Backend and frontend servers are auto-started by Playwright webServer config.
