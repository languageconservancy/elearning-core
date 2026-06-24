---
name: bootstrap-new-platform
description: >-
  Bootstraps a new eLearning platform from the elearning-template repo.
  Covers submodule setup, platform assets/config, web dev environment, and
  first-time Capacitor mobile setup with platform/core copy scripts.
  Use when creating a new platform, forking the template, setting up mobile
  (iOS/Android), or when the user mentions cap:add, copy-android-to-core,
  platform/android, or strings.xml.
---

# Bootstrap New eLearning Platform

## Architecture (read this first)

Every platform repo has two layers:

| Layer | Path | Version controlled in | Purpose |
|-------|------|----------------------|---------|
| **Platform** | `platform/` | Platform repo | Branding, config, native mobile projects |
| **Core** | `core/` | Separate submodule (`elearning-core`) | Shared Angular/CakePHP/Capacitor logic |

**Critical mobile nuance:** Capacitor builds in `core/frontend/android/` and `core/frontend/ios/`, but those folders are **gitignored in core**. The real native projects live in `platform/android/` and `platform/ios/` and are synced via rsync scripts.

```
platform/android/  ←→  core/frontend/android/   (ephemeral build location)
platform/ios/      ←→  core/frontend/ios/
```

Run all core commands from the **platform repo root**:

```bash
npm run core <command>    # from platform repo root
npm run <command>         # only when cwd is core/
```

## Phase 1 — Repo & submodule

1. Fork [elearning-template](https://github.com/languageconservancy/elearning-template), rename, clone.
2. Initialize submodule:
   ```bash
   npm run init
   # or: git submodule update --init --recursive
   ```
3. Install dependencies:
   ```bash
   npm install                    # at repo root (hoists core/frontend workspace deps)
   npm run core install-dependencies
   ```
4. Create GitHub Personal Access Token (classic, `public_repo` only) for private composer package.

## Phase 2 — Platform assets & config

Customize these before any build:

```
platform/
├── assets/          # images, theme, translations, keyboard, favicon
└── config/
    ├── demo/
    ├── local/
    ├── staging/
    └── production/
        ├── app-config.json   # frontend config (URLs, appId, social login keys)
        └── app_local.php     # backend config (DB name, AWS, links)
```

**Per-environment secrets:** create `platform/config/<env>/.env` locally (gitignored). `copy-assets` copies it to `core/backend/config/.env`.

Key `app-config.json` fields for mobile:

- `appId` — Capacitor app ID (e.g. `org.utelanguage.myapp`)
- `appName` — display name
- `googleClientIdWeb`, `googleClientIdIos`, `facebookAppId`, `facebookClientToken`
- `loginUri`, `iosAppIdNumber`
- `enableAppleLogin`, `enableGoogleLogin`, `enableFacebookLogin`

`prepare-platform:<env>` copies assets and generates:
- `core/frontend/src/environments/environment.ts`
- `core/frontend/src/index.html`

`capacitor.config.ts` reads `environment.ts` at sync time — **must run prepare before cap commands**.

## Phase 3 — Local web dev

1. Set server root env var (MAMP example):
   ```bash
   echo "export WWW_PATH='/Applications/MAMP/htdocs'" >> ~/.bash_profile
   source ~/.bash_profile
   ```
2. Import `core/demo/elearning_demo_db.sql` into phpMyAdmin.
3. Copy demo webroot assets:
   ```bash
   npm run core copy-demo-assets
   ```
4. Sync backend to local server:
   ```bash
   npm run core sync-local-backend
   ```
5. Prepare and serve:
   ```bash
   npm run core prepare-platform:local
   npm run core serve:local    # http://localhost:4200
   ```

## Phase 4 — Mobile setup (template has no ios/android)

A fresh template fork has **no** `platform/android/` or `platform/ios/`. Two approaches:

### Approach A — Copy from reference platform (recommended)

Copy entire directories from an existing platform (e.g. nuuwayga), then rebrand:

```bash
# Copy from reference platform into your new platform repo
cp -r <reference>/platform/android platform/
cp -r <reference>/platform/ios platform/
```

Then customize all platform-specific values. See [mobile-native-files.md](mobile-native-files.md) for the full file checklist.

After copying, pull into core before building:

```bash
npm run core copy-android-to-core
npm run core copy-ios-to-core
```

### Approach B — Scaffold with Capacitor (first time only)

1. **Must** prepare an environment first (generates `environment.ts`):
   ```bash
   npm run core prepare-platform:staging
   ```
2. Add native projects:
   ```bash
   npm run core cap:add-android
   npm run core cap:add-ios
   ```
3. Customize the generated projects (flavors, plists, icons, social login — see reference).
4. **Persist to platform repo** (do not skip):
   ```bash
   npm run core copy-core-android-to-platform
   npm run core copy-core-ios-to-platform
   ```
5. Commit `platform/android/` and `platform/ios/` in the platform repo.

## Phase 5 — Mobile dev loop

```bash
# 1. Pull version-controlled native projects into build location
npm run core copy-android-to-core
npm run core copy-ios-to-core

# 2. Build web + sync to native (staging/production include cap:sync)
npm run core build:staging

# 3. Open IDEs (paths are under core/frontend after copy)
# Android Studio → core/frontend/android/  (select staging|production flavor)
# Xcode → core/frontend/ios/App/App.xcworkspace  (NOT .xcodeproj)

# 4. After native edits, push back to platform repo
npm run core copy-core-android-to-platform
npm run core copy-core-ios-to-platform
```

## Copy script reference

| Command | Direction | When |
|---------|-----------|------|
| `copy-android-to-core` | platform → core | Before build, cap:sync, or opening Android Studio |
| `copy-ios-to-core` | platform → core | Before build, cap:sync, or opening Xcode |
| `copy-core-android-to-platform` | core → platform | After cap:add, plugin install, or Android Studio edits |
| `copy-core-ios-to-platform` | core → platform | After cap:add, pod install, or Xcode edits |
| `cap:sync` | web dist → native | After Angular build; included in `build:staging` and `build:production` |
| `cap:add-android` / `cap:add-ios` | scaffold | Once per platform, after `prepare-platform:<env>` |

**Warning:** rsync uses `--delete`. Uncommitted files on only one side will be removed.

## Gotchas

1. **Two homes for mobile** — edit in `core/frontend/`, version-control in `platform/`. Forgetting copy scripts loses work.
2. **`environment.ts` before cap** — `cap:add-*` and `cap:sync` require `prepare-platform:<env>` first.
3. **Only staging/production auto-sync** — `build:local` and `build:demo` do not run `cap:sync`.
4. **Open projects under core/frontend** — `node_modules` paths in Gradle/Podfile assume that location.
5. **Android package vs applicationId** — `MainActivity.java` stays at `org.tlc.elearning`; unique store identity is in `productFlavors.applicationId`.
6. **Docs may describe unimplemented generators** — `PLATFORM_ASSETS_README.md` mentions generating `strings.xml` and plists; current `generate-config-based-files.js` only generates `environment.ts` and `index.html`. Native files are hand-maintained in `platform/`.
7. **Social login needs triple alignment** — `app-config.json` → `environment.ts` → native files (Android flavor `strings.xml`, iOS `Info.plist` URL schemes).

## Checklist

```
Bootstrap progress:
- [ ] Fork template, init submodule, install dependencies
- [ ] Customize platform/assets/ and platform/config/<env>/
- [ ] Create platform/config/<env>/.env locally
- [ ] Verify web: prepare-platform:local → serve:local
- [ ] Mobile: copy from reference OR cap:add + customize
- [ ] copy-core-*-to-platform and commit platform/android + platform/ios
- [ ] Verify mobile: copy-*-to-core → build:staging → open in IDE
```

## Additional resources

- Full mobile file customization list: [mobile-native-files.md](mobile-native-files.md)
- Authoritative setup docs: `core/README.md`
- Social login: `core/frontend/SOCIAL_LOGIN_GUIDE.md`
