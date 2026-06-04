# Application Architecture & Strategic Tier Matrix (Current MVP Stage)

## Core System Stack
- **Frontend:** Vue 3 (Composition API) + Tailwind CSS + `@vue-flow/core` for canvas interaction.
- **Backend:** Laravel 13 running RESTful APIs, MySQL Database, and Prisma ORM for schema operations.
- **Authentication:** JWT-based authentication using JWT tymon.
- **Role-Based Access Control:** Role-based access control using Spatie Laravel Permissions.
- **Caching:** Redis-based caching using Laravel's built-in caching system.
- **Design Patterns:** Repository Pattern, Service Pattern.
- **Database Schema:** use flowchart's 3nf normalization for database schema design use mongo db for storing workflow metadata.use MySQL for storing workflow data .

## Strategic Monetization Matrix (Value-Based Hierarchy)
1. **Free Tier:** 
   - Access to basic drag-and-drop canvas layout building.
   - *Strict Restriction:* Cannot save workflows. Trigger 403 Paywall on Save.
2. **🥈 Silver Tier (Basic Premium):**
   - **Unlimited Save Slots:** Unlocks complete flowchart blueprint saving backend logic (Highly liked by CS students for managing multiple project ideas).
   - **Canvas Customization:** Authorization to dynamically change route/edge colors and node background colors via UI.
3. **🥇 Gold Tier (Intermediate Premium):**
   - Includes all **Silver Tier** features.
   - **Database Auto-Generation:** Compiles frontend canvas layout JSON into clean Prisma Schema strings, raw SQL DDL scripts, and interactive relational diagrams. (AI-assisted alternatives exist, keeping this as a mid-tier feature).
4. **💎 Platinum Tier (The Ultimate Creator Feature - Highest Perceived Value):**
   - Includes all **Gold Tier** and **Silver Tier** features.
   - **Visual Folder Mapping Engine:** Unlocks special `Folder/File Nodes` on the canvas. Users can draw flowchart connection edges from Logic/Function Nodes directly into Folder/File Nodes to architecture their workspace layout visually (Highly liked by Vibe Coders for pure architecture planning).
   - *Current Stage Note:* Automatic code file generation is strictly excluded from the current rollout. This tier is focused entirely on visual workspace mapping to validate layout UX and monitor user ratings.

## Future MVP Scale-Up Roadmap
- **Trigger Condition:** Monitor application users' ratings and feedback on the Visual Folder Mapping Engine.
- **Execution Step:** Once a stable rating threshold is confirmed, implement the custom backend generator script to parse the visual folder JSON mappings and compile full functional repository zip packages (Auto Code Generation) exclusively for Platinum/VIP status.

## Middleware & Access Gatekeeping (Gatekeeper Bounds)
- **Silver Check:** Wraps `/flows/save` endpoints. Verify user membership in ('silver', 'gold', 'platinum').
- **Gold Check:** Wraps `/flows/generate-schema` endpoints. Verify user membership in ('gold', 'platinum').
- **Platinum Check:** Wraps `/flows/map-structure` endpoints. Strictly restrict to user membership == 'platinum'.
- All violations must return `403 Forbidden` with localized and global paywall instructions (Local MMK rates / Global 4.99 USD variants).
