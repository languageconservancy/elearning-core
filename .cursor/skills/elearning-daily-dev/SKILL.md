---
name: elearning-daily-dev
description: >-
  Day-to-day eLearning platform development workflow. Covers demo-based local
  testing, build/serve commands, backend sync, core submodule updates, and
  expanding the demo database when bugs are found. Use when developing,
  testing locally, fixing bugs, updating core, or when the user asks
  what command to run after changing assets, config, or backend code.
---

# eLearning Daily Development

## Command convention

Run from the **platform repo root**:

```bash
npm run core <command>
```

For **frontend** platform changes, `serve:demo` is enough (it copies assets and generates config). For **backend** platform config, run `prepare-platform:demo` then `sync-local-backend`. `build:*` also includes prepare if you need a full build.

## Default local environment: demo

Use **demo** for everyday local testing, not `local`.

| Why demo | |
|----------|---|
| Demo DB | `core/demo/elearning_demo_db.sql` — exercises, lessons, and teacher portal cases in one place |
| Demo config | `platform/config/demo/` — points API at `localhost`, social logins disabled |
| Coverage goal | Every exercise type and edge case should eventually live in demo so all devs can reproduce it |

`local` exists for platform-specific localhost config; use it only when you explicitly need that environment.

## Daily web development loop

### Start of session (once per machine setup)

```bash
# MAMP running, WWW_PATH set, demo DB imported into phpMyAdmin as elearning_demo_db
npm run core copy-demo-assets        # demo webroot → core/backend/webroot
npm run core sync-local-backend      # core/backend → $WWW_PATH/backend/
```

Create `platform/config/demo/.env` locally if needed (gitignored; copied to backend on build/serve).

### Changing `platform/` files

Platform content splits into frontend and backend paths. Use the workflow that matches what you changed.

**Frontend** — just serve:

```bash
npm run core serve:demo
```

Re-run after changing:
- `platform/assets/` (images, theme, translations, fonts, favicon)
- `platform/assets/keyboard/keyboard.json` (frontend + backend, but serve copies it)
- `platform/config/demo/app-config.json` (API URLs, app name, social login flags → `environment.ts`)

`serve:demo` copies demo assets into `core/frontend/` and generates frontend config. No separate prepare step needed.

**Backend** — prepare, then sync:

```bash
npm run core prepare-platform:demo
npm run core sync-local-backend
```

Run after changing:
- `platform/config/demo/app_local.php` (DB name, AWS, teacher portal settings)
- `platform/config/demo/.env` (secrets; gitignored, local only)

`prepare-platform:demo` copies backend config into `core/backend/config/`. `sync-local-backend` pushes that to `$WWW_PATH/backend/` on MAMP. Serving alone does not update the running backend.

### Verifying a production-like frontend build

```bash
npm run core build:demo              # includes prepare-platform:demo + Angular build
```

Use when you want to confirm the built output, not just dev-server behavior.

### Backend (PHP) changes in core

After editing files under `core/backend/`:

```bash
npm run core sync-local-backend
```

This rsyncs backend to `$WWW_PATH/backend/` and clears CakePHP caches. Does not copy `.env` (keeps server secrets separate).

## Environment cheat sheet

| Goal | Command |
|------|---------|
| Daily web dev & testing | `npm run core serve:demo` |
| Platform backend config changed | `npm run core prepare-platform:demo` then `sync-local-backend` |
| Verify demo build output | `npm run core build:demo` |
| Test staging mobile / pre-release | `npm run core build:staging` (includes `cap:sync`) |
| Production release build | `npm run core build:production` (includes `cap:sync`) |
| Sync backend to MAMP | `npm run core sync-local-backend` |
| Refresh demo webroot assets | `npm run core copy-demo-assets` |

**Mobile note:** `build:demo` and `build:local` do **not** run `cap:sync`. For mobile testing use `build:staging` or `build:production` after `copy-*-to-core`. See [bootstrap-new-platform](../bootstrap-new-platform/SKILL.md) for mobile workflow.

## Updating the core submodule

```bash
npm run update-core                  # from platform repo root
npm run core install-dependencies    # if package versions changed
npm run core serve:demo              # verify web still works
```

If core updated Capacitor or native dependencies, also run mobile copy scripts and test in Android Studio / Xcode.

## Demo database workflow

The demo DB is the **shared regression suite** for lessons, exercises, and teacher portal. When a bug appears on any platform, the fix should be verifiable in demo for everyone.

See [demo-db-workflow.md](demo-db-workflow.md) for the full add-to-demo and commit process.

**Short version:**

1. Reproduce the bug locally with `serve:demo` + demo DB.
2. Add or update the lesson/exercise (or teacher portal case) in demo so the scenario is covered.
3. Export the database and replace `core/demo/elearning_demo_db.sql`.
4. Commit the SQL file to **elearning-core** (not the platform repo).
5. Team members re-import the updated dump into phpMyAdmin.

## When you change…

| What changed | What to run |
|--------------|-------------|
| `platform/assets/` or `platform/config/demo/app-config.json` | `serve:demo` |
| `platform/config/demo/app_local.php` or `.env` | `prepare-platform:demo` → `sync-local-backend` |
| `core/frontend/` Angular code | `serve:demo` (hot reload) or restart serve |
| `core/backend/` PHP | `sync-local-backend` |
| Demo DB content | Re-import `elearning_demo_db.sql`; commit SQL to core |
| `core` submodule version | `update-core`, then `install-dependencies` if needed |
| Staging/production config or mobile | `build:staging` or `build:production` + mobile copy scripts |

## Gotchas

1. **Frontend platform changes → `serve:demo`**; **backend platform config → `prepare-platform:demo` + `sync-local-backend`**. Don't use serve alone for `app_local.php` / `.env` changes — MAMP won't see them until you sync.
2. **Use demo, not local**, for routine testing — demo DB has the breadth of test cases.
3. **`serve:demo` needs MAMP + demo DB** — frontend at `:4200`, API at `localhost/backend/api/`.
4. **Demo DB lives in core** — `core/demo/elearning_demo_db.sql`. Platform repos don't own it.
5. **Re-import after pulling core** — when a teammate commits an updated demo DB, drop/re-import in phpMyAdmin.
6. **Root README commands may be wrong** — always use `npm run core <command>`, not bare `npm run serve:demo` at repo root.

## Related skills

- First-time setup: [bootstrap-new-platform](../bootstrap-new-platform/SKILL.md)
- Mobile file customization: [mobile-native-files.md](../bootstrap-new-platform/mobile-native-files.md)
