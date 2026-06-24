# Mobile Native Files — Customization Reference

## Where to edit (read this first)

Native mobile projects exist in **two locations**. Always edit in `core/frontend/`; use `platform/` only for version control.

| Location | Purpose | Edit here? |
|----------|---------|------------|
| `core/frontend/android/`, `core/frontend/ios/` | Build location — Android Studio, Xcode, `cap:sync` | **Yes — always** |
| `platform/android/`, `platform/ios/` | Version-controlled copy in the platform repo | **No — commit destination only** |

### Workflow

```bash
# 1. Start a session: pull the version-controlled copy into core
npm run core copy-android-to-core
npm run core copy-ios-to-core

# 2. Edit, build, and test under core/frontend/ (see file lists below)
#    Android Studio → core/frontend/android/
#    Xcode         → core/frontend/ios/App/App.xcworkspace

# 3. When changes are ready to commit: push back to platform
npm run core copy-core-android-to-platform
npm run core copy-core-ios-to-platform

# 4. Commit platform/android/ and platform/ios/ in the platform repo
```

**Do not** open or edit `platform/android/` or `platform/ios/` directly in Android Studio or Xcode. Gradle and Podfile paths assume the project lives under `core/frontend/`.

When bootstrapping from a reference platform, copy `platform/android/` and `platform/ios/` into your new platform repo first, then run `copy-*-to-core` and customize everything under `core/frontend/`.

## Generated automatically (do not hand-edit)

These are produced by `npm run core prepare-platform:<env>`:

| Output | Source |
|--------|--------|
| `core/frontend/src/environments/environment.ts` | `core/scripts/templates/environment.ts` + `platform/config/<env>/app-config.json` |
| `core/frontend/src/index.html` | `core/scripts/templates/index.html` + same config |
| `core/frontend/capacitor.config.ts` | Reads `environment.ts` at sync time |

## Android — files to customize in `core/frontend/android/`

Paths below are relative to `core/frontend/android/`.

### Build & identity

| File | What to change |
|------|----------------|
| `app/build.gradle` | `productFlavors` → `applicationId` per flavor (e.g. `org.myorg.myapp`, `org.myorg.myapp.staging`); `versionCode`, `versionName` |
| `app/src/main/res/values/strings.xml` | `package_name`, `custom_url_scheme` (may still use generic `org.tlc.elearning`) |
| `app/src/staging/res/values/strings.xml` | `app_name`, `facebook_app_id`, `facebook_client_token` |
| `app/src/production/res/values/strings.xml` | Same as staging, production values |

### Icons & splash (per flavor)

```
app/src/staging/res/mipmap-*/       # launcher icons
app/src/staging/res/drawable*/      # splash screens
app/src/production/res/mipmap-*/
app/src/production/res/drawable*/
```

### Social login & manifest

| File | What to change |
|------|----------------|
| `app/src/main/AndroidManifest.xml` | Facebook activities, permissions |
| `app/src/main/java/org/tlc/elearning/MainActivity.java` | Social login hooks (package name intentionally shared — do not rename) |

### Do not hand-edit (Capacitor-regenerated)

- `app/capacitor.build.gradle`
- `capacitor.settings.gradle`
- `app/src/main/assets/` (web build output)

## iOS — files to customize in `core/frontend/ios/`

Paths below are relative to `core/frontend/ios/`.

### Project structure (typical)

```
App/
├── Podfile / Podfile.lock
├── <appname>.entitlements          # Apple Sign In; auto-generated when capability added in Xcode Signing & Capabilities
├── App/
│   ├── AppDelegate.swift
│   ├── <appname>-Info.plist        # Production
│   ├── <appname>Staging-Info.plist # Staging
│   ├── Assets.xcassets/            # AppIcon + Splash
│   └── Base.lproj/                 # LaunchScreen, Main storyboard
└── App.xcodeproj/                  # Targets, schemes, bundle IDs
```

### Files to customize

| File | What to change |
|------|----------------|
| `App/Podfile` | Capacitor pod targets; add staging target block if needed |
| `App/App/<appname>-Info.plist` | `CFBundleDisplayName`, Facebook keys, Google reversed client ID URL schemes |
| `App/App/<appname>Staging-Info.plist` | Staging equivalents |
| `App/App.xcodeproj/project.pbxproj` | Bundle IDs, target names, schemes, version; **use relative paths** for plist references |
| `App/App/Assets.xcassets/` | App icons, splash images |

Open **`core/frontend/ios/App/App.xcworkspace`** (after `pod install`), not `.xcodeproj`.

## Rebrand search-and-replace targets

After `copy-*-to-core`, search under `core/frontend/android/` and `core/frontend/ios/` for and replace:

| Pattern | Example (nuuwayga) | Replace with |
|---------|-------------------|--------------|
| App display name | `Nuuwayga` | Your app name |
| Android applicationId | `org.utelanguage.nuuwayga` | Your `appId` from app-config.json |
| Staging applicationId | `org.utelanguage.nuuwayga.staging` | Your staging app ID |
| iOS target/scheme names | `nuuwayga`, `nuuwaygaStaging` | Your app name variants |
| iOS bundle identifier | matches applicationId | Your bundle IDs |
| Facebook app ID/token | per-environment values | Your Facebook app credentials |
| Google reversed client ID | in Info.plist URL schemes | Your iOS Google client ID (reversed) |
| File names | `nuuwayga-Info.plist`, `nuuwayga.entitlements` | Rename to match your app |

When done, run `copy-core-*-to-platform` before committing.

## Android flavor pattern (reference)

```gradle
flavorDimensions 'env'
productFlavors {
    production {
        dimension 'env'
        applicationId 'org.myorg.myapp'
    }
    staging {
        dimension 'env'
        applicationId 'org.myorg.myapp.staging'
    }
}
namespace "org.tlc.elearning"   // shared across all platforms — leave as-is
```

In Android Studio: **Build → Select Build Variant** → `staging` or `production`.

## Verification after customization

1. `npm run core copy-android-to-core && npm run core copy-ios-to-core` (if not already done)
2. Customize files under `core/frontend/android/` and `core/frontend/ios/` (not `platform/`)
3. `npm run core prepare-platform:staging`
4. `npm run core build:staging` (includes `cap:sync`)
5. Android Studio: open `core/frontend/android/`, build staging flavor
6. Xcode: open `core/frontend/ios/App/App.xcworkspace`, run staging scheme
7. Test social logins on each platform (web keys ≠ mobile keys)
8. `npm run core copy-core-android-to-platform && npm run core copy-core-ios-to-platform`
9. Commit `platform/android/` and `platform/ios/`
