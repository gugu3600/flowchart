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
| `/login` | Login.vue | JWT login |
| `/register` | Register.vue | User registration |
| `/canvas` | Canvas.vue | Flowchart canvas (Flow + Schema modes) |
| `/help` | HelpGuide.vue | How-to guide and documentation |
| `/tables` | TableDesigner.vue | DB table schema CRUD |
| `/logics` | LogicDesigner.vue | Logic/function definition CRUD |

## Development

```bash
npm install
npm run dev
```

The dev server starts at `http://localhost:3000` (configured in `vite.config.js`).
