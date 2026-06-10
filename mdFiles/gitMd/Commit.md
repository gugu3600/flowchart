# Git Commits

> Last updated: 2026-06-10 05:00 UTC

## 2026-06-10

- `19d4b88` fix: route guard return-only, login 409 if authed, color reactivity & insert bugfixes
- `7220011` feat: tier-gated logic CRUD, subscription durations, ColumnBuilder component, env security, free-tier tests
- `5484d97` docs: fix commit hash in Commit.md after amend
- `2b07240` Merge dev into test
- `9cc4e16` docs: update Commit.md

- `28c6bd4` feat: add register link to login, update all .md timestamps
- `156efc9` docs: update Commit.md with form UX commit
- `7353460` feat: password toggle, remember me, error states on login/register forms
- `1b970fe` Merge dev into test
- `472a0f9` feat: fetch payment methods from backend (mock)
- `464adad` Merge dev into test
- `8421040` refactor: move CSS to style.css, update docs
- `0464f37` Merge dev into test
- `36ae10f` refactor: extract FloatingInput, TierSelector, PaymentMethodPicker components
- `a06f5a1` Merge dev into test
- `cbbb048` docs: update Commit.md and archive logs with registration session
- `1350b1a` feat: register with tier selection, pricing, payment methods
- `cc1a195` docs: update Commit.md and archive logs with admin/permissions session
- `51c56d8` Merge dev into test
- `7168678` docs: update README with admin panel, route guard, permission middleware
- `32054f5` Merge dev into test
- `15da48c` feat: admin management, tier upgrade, route guard, rate limiting, password validation
- `7758e4e` merge dev into main: help guide, edge deletion, designer pages, doc updates
- `a9f993e` chore: update openspec archive log for help guide & edge deletion
- `58e8a52` feat: help guide page, edge/node deletion, project readme updates

## 2026-06-08

- `887c8f0` fix: strict cross-mode node filtering (absolute domain separation)
- `4786215` feat: register page, code review fixes, security audit
- `fe1e5ef` feat: structured {name,type} inputs for logic definitions (workflow CRUD)
- `926148c` fix: route model binding for table/logic definition updates
- `75d9949` feat: canvas tab modes (Flow/Schema) with isValidConnection domain boundaries
- `f2f8f01` Add last-updated timestamps to all .md files
- `df927b9` Table Designer, Logic Designer & interactive canvas with definition-to-node bridge

## 2026-06-04

- `c4c3d66` Merge dev: node tailwind refactor to style.css
- `fc34bba` refactor: extract all node tailwind classes into assets/style.css
- `4a42975` Merge dev: reusable flowchart node components
- `6a38be9` feat: reusable flowchart node components (BaseNode, TableNode, LogicNode, FolderFileNode)
- `a684ae4` Merge dev: frontend login page, tailwind v4 + primevue 4, axios apiClient, playwright e2e
- `b763587` feat: frontend login page, tailwind v4 + primevue 4, axios apiClient, playwright e2e
- `4641100` Sync test with dev: .gitignore update
- `f467c4d` chore: update .gitignore (exclude docs/tests/root config)
- `feed6dc` chore: remove docs, tests, and root config from main tracking
- `f0e9e11` Sync test branch with main: full backend scaffold, schema, controllers, services, resources
- `2f9c915` Merge main into dev: scaffold, schema, controllers, services, resources, cookie auth
- `6873c56` feat: api resources, cookie-based jwt, remove redundant auth guard
- `13b03b0` refactor: repository/service pattern with form requests
- `2368f7d` feat: create models, controllers, role/permission seeders with super-admin
- `e95032c` feat(database): create flowchart DB schema with 3NF migrations
- `209ea19` docs: add Commit.md to track project history
- `9cae740` dev: first commit

---

> **Note from 2026-06-09:** Going forward, every commit must also update `mdFiles/gitMd/Commit.md` (append the commit hash and message) and `openspec/changes/archive/logs.md` (append a Log- entry describing the change). See `mdFiles/Todo.md` for the enforced workflow rule.
