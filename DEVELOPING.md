# Developing on an eLearning platform

Start here if the repo layout feels confusing. Every platform repo has two layers:

| Layer | Where | What lives there |
|-------|--------|------------------|
| **Platform** (your repo) | `platform/` | Branding, config, native mobile projects |
| **Core** (git submodule) | `core/` | Shared Angular frontend, CakePHP backend, Capacitor |

Run core commands from the **platform repo root** as:

```bash
npm run core <command>
```

Or run the same scripts directly from `core/` (what the VS Code tasks do).

## Open the workspace (required for tasks and linting)

VS Code only loads tasks from workspace roots. Open the shared workspace file — do **not** open the platform folder alone if you want the **eLearning:** task menu:

**File → Open Workspace from File…** → `core/elearning-platform.code-workspace`

That workspace opens three roots:

| Root | Path | Why |
|------|------|-----|
| **platform** | platform repo root | Branding, config, native mobile projects |
| **core** | `core/` submodule | Backend, scripts, demo DB, VS Code tasks |
| **frontend** | `core/frontend/` | Angular app — separate root so ESLint, TypeScript, and Angular language services find `tsconfig.json`, `.eslintrc.json`, and `node_modules` |

The **frontend** folder is included because the linter and type checker expect `frontend/` to be the project root. With only `core` open, you often get false ESLint/TypeScript errors on Angular files.

Tasks and docs live in core so they stay in sync when you `npm run update-core`.

## First-time setup

1. Clone your platform repo and init the submodule: `npm run init`
2. Install dependencies: `npm run core install-dependencies`
3. Start MAMP (or your local PHP stack)
4. Set your web root: `export WWW_PATH='/Applications/MAMP/htdocs'` (add to `~/.bash_profile`)
5. Import `core/demo/elearning_demo_db.sql` into phpMyAdmin as `elearning_demo_db`
6. Run task **eLearning: First-time web setup** (or `copy-demo-assets` then `sync-local-backend`)
7. Create `platform/config/demo/.env` locally if you need backend secrets (gitignored)

Then **Terminal → Run Task…** → **eLearning: Start demo dev server**.

- Frontend: http://localhost:4200
- API: http://localhost/backend/api/

Use **demo**, not **local**, for everyday testing. The demo DB has lessons, exercises, and teacher-portal cases everyone can reproduce.

## Daily workflow (Run Task)

In Cursor/VS Code (with `elearning-platform.code-workspace` open): **Terminal → Run Task…**

| Task | When to use it |
|------|----------------|
| **Start demo dev server** | Normal web development (most days) |
| **Apply backend config changes (demo)** | You edited `app_local.php` or `platform/config/demo/.env` |
| **Sync backend to MAMP** | You changed PHP under `core/backend/` |
| **Build demo** | Verify a production-like frontend build |
| **Build staging / production** | Mobile or release builds (includes Capacitor sync) |
| **Sync mobile to platform (core → platform)** | After Android Studio or Xcode edits under `core/frontend/` — copies to `platform/` for commit |

**Default build task:** **eLearning: Start demo dev server** — **Terminal → Run Build Task** (often Cmd+Shift+B).

## When you change something…

| What you changed | What to run |
|------------------|-------------|
| `platform/assets/` or `platform/config/demo/app-config.json` | Start demo dev server |
| `platform/config/demo/app_local.php` or `.env` | Apply backend config changes (demo) |
| Code in `core/frontend/` | Dev server hot-reloads; restart if needed |
| Code in `core/backend/` | Sync backend to MAMP |
| Demo DB content | Re-import `demo/elearning_demo_db.sql`; commit SQL to **elearning-core** |
| Core submodule version | Update core submodule, then install dependencies if needed |

## Architecture cheat sheet

```
platform/assets/  ──copy──►  core/frontend/ + core/backend/webroot/
platform/config/  ──generate──►  environment.ts, backend config
core/frontend/    ──ng serve──►  :4200
core/backend/     ──sync──►      $WWW_PATH/backend/  (MAMP)
```

Mobile is an extra loop: native projects live in `platform/android/` and `platform/ios/`, get copied into `core/frontend/` for builds. See [README.md](README.md) and [.cursor/skills/bootstrap-new-platform/SKILL.md](.cursor/skills/bootstrap-new-platform/SKILL.md).

## Troubleshooting

- **No eLearning tasks in Run Task** — Open `core/elearning-platform.code-workspace`, not the platform folder alone.
- **ESLint/TypeScript errors on Angular files** — Reopen via `core/elearning-platform.code-workspace` so the **frontend** root is loaded (not `core/frontend` opened in isolation, and not the platform folder alone).
- **API 404 or wrong DB** — MAMP running? Demo DB imported? Run **Sync backend to MAMP**.
- **Config changes not showing** — Frontend config needs a dev-server restart; backend config needs **Apply backend config changes**, not just serve.
- **Command not found at repo root** — Use `npm run core <command>`, not bare `npm run serve:demo`.
- **Submodule empty** — `npm run init`

## More detail

- Core setup (detailed): [README.md](README.md)
- Social login setup (Facebook, Google, Apple): [frontend/SOCIAL_LOGIN_GUIDE.md](frontend/SOCIAL_LOGIN_GUIDE.md)
- Daily dev reference (for AI/agents): [.cursor/skills/elearning-daily-dev/SKILL.md](.cursor/skills/elearning-daily-dev/SKILL.md)
