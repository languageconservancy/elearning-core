# Developing on an eLearning platform

Start here if the repo layout feels confusing. Every language app repo has three layers:

| Layer | Where | What lives there |
|-------|--------|------------------|
| **Language repo** (your fork) | `elearning-<app-name>/` | Repo root — `package.json`, run `npm run core` from here |
| **Platform-specific** | `platform/` | Branding, config, native mobile projects version-controlled here |
| **Core** (git submodule) | `core/` | Shared Angular frontend, CakePHP backend, Capacitor |

Example: in `elearning-nuuwayga/`, platform assets live in `platform/assets/` and shared code in `core/`.

Run core commands from the **language repo root** (`elearning-<app-name>/`) as:

```bash
npm run core <command>
```

Or run the same scripts directly from `core/` (what the VS Code tasks do).

## Open the workspace (required for tasks and linting)

VS Code only loads tasks from workspace roots. Open the shared workspace file — do **not** open `elearning-<app-name>/` or `core/` alone if you want the **eLearning:** task menu:

**File → Open Workspace from File…** → `core/elearning-platform.code-workspace`

That workspace opens three roots:

| Root | Path | Why |
|------|------|-----|
| **`elearning-<app-name>`** | language repo root | `platform/`, top-level `package.json`, run `npm run core` here |
| **core** | `core/` submodule | Backend, scripts, demo DB, VS Code tasks |
| **frontend** | `core/frontend/` | Angular app — separate root so ESLint, TypeScript, and Angular language services find `tsconfig.json`, `.eslintrc.json`, and `node_modules` |

The **frontend** folder is included because the linter and type checker expect `frontend/` to be the project root. With only `core` open, you often get false ESLint/TypeScript errors on Angular files.

Tasks and docs live in core so they stay in sync when you `npm run update-core`.

## First-time setup

1. Clone your language repo (`elearning-<app-name>/`) and init the submodule: `npm run init`
2. Install dependencies: `npm run core install-dependencies`
3. Create language repo var files if missing: `npm run core init-language-repo-vars` (or task **Init language repo vars**) — see [language-repo-vars.md](language-repo-vars.md)
4. Set up a local Apache + MySQL stack — see **[local-server-setup.md](local-server-setup.md)** (MAMP or XAMPP, PHP 7.4.33, edit `scripts/local-dev-vars.sh`, demo DB import)
5. Run task **eLearning: First-time web setup** (or `copy-demo-assets` then `sync-local-backend`)
6. For mobile builds: run **eLearning: Sync mobile to core (platform → core)** so `platform/android/` and `platform/ios/` are copied into `core/frontend/` (required after a fresh clone before opening Android Studio or Xcode)

Then **Terminal → Run Task…** → **eLearning: Start demo dev server**.

- Frontend: http://localhost:4200
- API: http://localhost/backend/api/

Use **demo**, not **local**, for everyday testing. The demo DB has lessons, exercises, and teacher-portal cases everyone can reproduce.
Only modify **demo** in ways that should be saved to the `core` submodule (useful for everyone), otherwise use **local** for non-generically useful testing.

## Daily workflow (Run Task)

In Cursor/VS Code (with `elearning-platform.code-workspace` open): **Terminal → Run Task…**

| Task | When to use it |
|------|----------------|
| **Start demo dev server** | Normal web development (most days) |
| **Apply backend config changes (demo)** | You edited `app_local.php` or `platform/config/demo/.env` |
| **Sync backend to local server** | You changed PHP under `core/backend/` |
| **Build demo** | Verify a production-like frontend build |
| **Build staging / production** | Mobile or web release builds (includes Capacitor sync) |
| **Sync mobile to core (platform → core)** | After clone, or when `platform/android/` or `platform/ios/` changed — copies into `core/frontend/` for builds |
| **Sync mobile to platform (core → platform)** | After Android Studio or Xcode edits under `core/frontend/` — copies to `platform/` for commit |

**Default build task:** **eLearning: Start demo dev server** — **Terminal → Run Build Task** (often Cmd+Shift+B).

## When you change something…

| What you changed | What to run |
|------------------|-------------|
| `platform/assets/` or `platform/config/demo/app-config.json` | Start demo dev server |
| `platform/config/demo/app_local.php` or `.env` | Apply backend config changes (demo) |
| Code in `core/frontend/` | Dev server hot-reloads; restart if needed |
| Code in `core/backend/` | Sync backend to local server |
| Demo DB content | Re-import `demo/elearning_demo_db.sql`; commit SQL to **elearning-core** |
| Core submodule version | Update core submodule, then install dependencies if needed |

## Architecture cheat sheet

```
core/frontend/src/default-assets/  ──(1) copy──►  core/frontend/src/assets/
platform/assets/                   ──(2) copy──►  core/frontend/src/assets/   (overwrites same paths)
platform/config/                   ──generate──►  environment.ts, backend config
core/frontend/                     ──ng serve──►  :4200
core/backend/                      ──sync──►      $ELEARNING_WWW_PATH/backend/
```

Asset copying is two steps (`prepare-platform` / `serve:demo` runs this via `copy-platform-assets-to-core.js`):

1. **Defaults first** — everything under `core/frontend/src/default-assets/` is copied into `core/frontend/src/assets/`.
2. **Platform on top** — `platform/assets/` is copied into the same tree (images, `scss/` → `assets/scss/modules/`, translations, fonts, keyboard, favicon).

Overrides are **whole files**, not merged content. If a platform file lands at the same path as a default, it replaces the default entirely. You cannot patch part of a file — for example, you cannot override only a few colors in `_theme.scss`; copy the full file into `platform/assets/scss/` and edit the copy.

Demo lesson media is separate: `core/demo/webroot/` → `core/backend/webroot/` (first-time setup task).

Mobile is an extra loop: native projects live in `platform/android/` and `platform/ios/`, get copied into `core/frontend/` for builds. See [.cursor/skills/bootstrap-new-platform/SKILL.md](../../.cursor/skills/bootstrap-new-platform/SKILL.md) and [README.md — Mobile apps](../../README.md#mobile-apps).

## Troubleshooting

- **No eLearning tasks in Run Task** — Open `core/elearning-platform.code-workspace`, not `elearning-<app-name>/` or `core/` alone.
- **ESLint/TypeScript errors on Angular files** — Reopen via `core/elearning-platform.code-workspace` so the **frontend** root is loaded (not `core/frontend` opened in isolation, and not the language repo folder alone).
- **API 404 or wrong DB** — MAMP running? Demo DB imported? Run **Sync backend to MAMP**.
- **Config changes not showing** — Frontend config needs a dev-server restart; backend config needs **Apply backend config changes**, not just serve.
- **Command not found at repo root** — Use `npm run core <command>`, not bare `npm run serve:demo`.
- **Submodule empty** — `npm run init`

## More detail

- Language repo var files: [language-repo-vars.md](language-repo-vars.md)
- Local Apache/MySQL setup: [local-server-setup.md](local-server-setup.md)
- Core overview and npm commands: [README.md](../../README.md)
- Documentation index: [docs/README.md](../README.md)
- Social login setup (Facebook, Google, Apple): [frontend/social-login.md](../frontend/social-login.md)
- Daily dev reference (for AI/agents): [.cursor/skills/elearning-daily-dev/SKILL.md](../../.cursor/skills/elearning-daily-dev/SKILL.md)
