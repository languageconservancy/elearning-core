# eLearning Core

[![GitHub Release](https://img.shields.io/github/v/release/languageconservancy/elearning-core?style=flat-square)](https://github.com/languageconservancy/elearning-core/releases)
[![License: MPL 2.0](https://img.shields.io/badge/License-MPL_2.0-brightgreen.svg?style=flat-square)](https://opensource.org/licenses/MPL-2.0)
![GitHub contributors](https://img.shields.io/github/contributors/languageconservancy/elearning-core?style=flat-square)

Shared backend, frontend (web, Android, iOS), and build tooling for eLearning language apps. Each language project adds this repo as a **git submodule** at `core/` so platform-specific assets, config, and mobile projects stay in the language repo while shared code stays in one place.

**New developer?** Start with [docs/getting-started/developing.md](docs/getting-started/developing.md) and open [elearning-platform.code-workspace](elearning-platform.code-workspace) in Cursor/VS Code (loads the language repo, **core**, and **frontend** roots for tasks and Angular linting).

**Full documentation index:** [docs/README.md](docs/README.md)

## Table of contents

- [Technologies](#technologies)
- [How the repos fit together](#how-the-repos-fit-together)
- [Quick start](#quick-start)
- [Prerequisites](#prerequisites)
- [npm commands](#npm-commands)
- [Build, prepare, and serve](#build-prepare-and-serve)
- [Secrets and language repo vars](#secrets-env)
- [Mobile apps](#mobile-apps)
- [Repository structure](#repository-structure)
- [Language repo layout](#language-repo-layout)
- [Social login](#social-login)
- [Adding this repo as a submodule](#adding-this-repo-as-a-submodule)
- [License](#license)

## Technologies

See [frontend/package.json](frontend/package.json) and [backend/composer.json](backend/composer.json) for current versions.

- **Frontend**: Angular (TypeScript)
- **Backend**: CakePHP (PHP)
- **Mobile**: CapacitorJS
- **Styling**: Bootstrap
- **Tooling**: Node.js, npm, Composer, Prettier

## How the repos fit together

Every language app repo (e.g. `elearning-nuuwayga/`) has three layers:

| Layer | Path | Contents |
|-------|------|----------|
| **Language repo** | `elearning-<app-name>/` | `package.json`, `npm run core`, `scripts/deploy-vars.sh`, `scripts/local-dev-vars.sh` |
| **Platform-specific files** | `elearning-<app-name>/platform/` | Branding, config, version-controlled Android/iOS projects |
| **Core submodule** | `elearning-<app-name>/core/` | This repo — shared Angular app, CakePHP API, build scripts |

Day-to-day workflow, VS Code tasks, and architecture diagrams: **[docs/getting-started/developing.md](docs/getting-started/developing.md)**.

## Quick start

From your **language repo root** after cloning:

```bash
npm run init                              # init core submodule
npm run core install-dependencies         # npm, composer, cocoapods
```

Then follow the getting-started guides:

1. **`npm run core init-language-repo-vars`** — create `scripts/local-dev-vars.sh` / `deploy-vars.sh` from [core/scripts/examples/](scripts/examples/) if missing ([language-repo-vars.md](docs/getting-started/language-repo-vars.md))
2. **[Local server setup](docs/getting-started/local-server-setup.md)** — MAMP or XAMPP, edit `scripts/local-dev-vars.sh`, demo DB import
3. **[Developing on a platform](docs/getting-started/developing.md)** — first-time web setup task, daily serve/sync workflow

Typical daily loop once set up:

```bash
npm run core serve:demo                   # frontend at http://localhost:4200
# API at http://localhost/backend/api/ — requires local server + sync-local-backend
```

Use VS Code task **eLearning: Start demo dev server** when the workspace is open. See [developing.md](docs/getting-started/developing.md) for the full task list.

## Prerequisites

### Language repo with submodule

Create a language repo by forking the [elearning-template](https://github.com/languageconservancy/elearning-template) repo or clone an existing one. The `core/` submodule must be initialized (`npm run init`).

### GitHub personal access token (Composer)

`composer install` may prompt for a GitHub token when pulling a private dependency:

1. GitHub → Settings → Developer settings → Personal access tokens → **Tokens (classic)**
2. Generate a token with only **public_repo** checked (not full **repo**) in the Read permissions section.
3. Paste when Composer prompts during `npm run core install-dependencies`

### Install dependencies

From the language repo root:

```bash
npm run core install-dependencies
```

Installs frontend npm packages, backend Composer packages, and CocoaPods (macOS).

## npm commands

| Where you are | How to run core scripts |
|---------------|-------------------------|
| Language repo root (`elearning-<app-name>/`) | `npm run core <command>` |
| This repo (`core/`) | `npm run <command>` |

The language repo proxies through `scripts/proxy.js`. VS Code tasks run from the **core** root directly.

## Build, prepare, and serve

All commands from the **language repo root** unless noted.

| Command | Purpose |
|---------|---------|
| `npm run core prepare-platform:<env>` | Copy `platform/assets/` → core, generate `environment.ts` and backend config |
| `npm run core build:<env>` | Prepare + Angular production build (`demo`, `local`, `staging`, `production`) |
| `npm run core serve:<env>` | Prepare + dev server at `http://localhost:4200` (`demo` or `local`) |
| `npm run core init-language-repo-vars` | Create `scripts/local-dev-vars.sh` and `scripts/deploy-vars.sh` from templates if missing ([language-repo-vars.md](docs/getting-started/language-repo-vars.md)) |
| `npm run core sync-local-backend` | Push `core/backend/` → `$ELEARNING_WWW_PATH/backend/` ([local-server-setup.md](docs/getting-started/local-server-setup.md)) |
| `npm run core copy-demo-assets` | Copy demo lesson media into `core/backend/webroot/` ([demo/README.md](docs/demo/README.md)) |

**Which command when?** See the tables in [developing.md](docs/getting-started/developing.md#when-you-change-something).

Asset copy order (defaults, then platform overrides): [developing.md — Architecture](docs/getting-started/developing.md#architecture-cheat-sheet).

Platform asset and template details: [docs/scripts/platform-assets.md](docs/scripts/platform-assets.md).

## Secrets and language repo vars

**Guide:** [language-repo-vars.md](docs/getting-started/language-repo-vars.md) — templates in `core/scripts/examples/`, your copies in language repo `scripts/`.

Backend database credentials and secrets live in `platform/config/<env>/.env`

See `core/backend/config/.env.default` for available keys. Never commit production secrets to a public repo.

Deploy server paths and SSH targets (non-secret) live in the language repo's `scripts/deploy-vars.sh`. Templates: `core/scripts/examples/deploy-vars.example.sh`. See [language-repo-vars.md](docs/getting-started/language-repo-vars.md).

Local Apache document root: `scripts/local-dev-vars.sh` (gitignored). Template: `core/scripts/examples/local-dev-vars.example.sh`.

## Mobile apps

Capacitor projects are **built** under `core/frontend/` but **version-controlled** in `platform/android/` and `platform/ios/`. Copy scripts keep the two in sync:

| Direction | Command |
|-----------|---------|
| platform → core (before build / open IDE) | `npm run core copy-android-to-core` / `copy-ios-to-core` |
| core → platform (after IDE edits, to commit) | `npm run core copy-core-android-to-platform` / `copy-core-ios-to-platform` |

First-time Capacitor setup, native file customization, and release builds: [.cursor/skills/bootstrap-new-platform/SKILL.md](.cursor/skills/bootstrap-new-platform/SKILL.md) and [mobile-native-files.md](.cursor/skills/bootstrap-new-platform/mobile-native-files.md).

After a fresh clone, run VS Code task **eLearning: Sync mobile to core (platform → core)** before opening Android Studio or Xcode.

## Repository structure

| Path | Purpose |
|------|---------|
| `backend/` | CakePHP API (`/api/*`) and admin panel (`/admin/*`) |
| `frontend/` | Angular app; Capacitor `android/` and `ios/` live here during builds |
| `scripts/` | Build/deploy tooling — asset copy, config generation, `deploy.sh`, `sync-local-backend.sh`, `examples/` (var templates) |
| `demo/` | Demo database SQL, webroot media, MAMP `my.cnf` — [demo/README.md](docs/demo/README.md) |
| `docs/` | Documentation index — [docs/README.md](docs/README.md) |
| `elearning-platform.code-workspace` | Multi-root VS Code workspace — [developing.md](docs/getting-started/developing.md#open-the-workspace-required-for-tasks-and-linting) |
| `package.json` | Core npm scripts (invoked via `npm run core` from the language repo) |

## Language repo layout

A complete language repo typically looks like:

```text
elearning-<app-name>/
├── core/                    # this submodule
├── platform/
│   ├── assets/              # branding (overrides core default-assets)
│   ├── config/
│   │   ├── demo/
│   │   ├── local/
│   │   ├── staging/
│   │   └── production/
│   ├── android/
│   └── ios/
├── scripts/
│   ├── deploy-vars.sh       # deploy targets (version controlled; from core/scripts/examples/)
│   └── local-dev-vars.sh    # local Apache root (gitignored; from core/scripts/examples/)
└── package.json             # "core": "node scripts/proxy.js"
```

### What goes where

Most assets and config feed the **frontend**. Exceptions:

| Files | Used by |
|-------|---------|
| `platform/config/<env>/app_local.php`, `.env` | Backend only |
| `platform/assets/keyboard/keyboard.json` | Frontend and backend |
| Everything else under `platform/assets/` | Frontend (via copy into `core/frontend/src/assets/`) |

Platform files **replace whole files** at the same path — there is no partial merge (e.g. you override all of `_theme.scss`, not individual colors). Details: [developing.md](docs/getting-started/developing.md#architecture-cheat-sheet).

## Social login

Apple, Google, and Facebook login are supported on web and mobile to varying degrees. Setup is per-platform (credentials in `platform/config/`, native Android/iOS files).

**Full setup guide:** [docs/frontend/social-login.md](docs/frontend/social-login.md)

## Adding this repo as a submodule

```bash
git submodule add git@github.com:languageconservancy/elearning-core.git core
git submodule update --init --recursive
```

Or from an existing language repo: `npm run init`.

## License

Mozilla Public License 2.0 — see [LICENSE](LICENSE).
