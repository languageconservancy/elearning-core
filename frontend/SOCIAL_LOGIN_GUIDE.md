# Social Login Setup Guide

This guide explains how to configure social logins for an eLearning platform from scratch. Each platform repo owns credentials and native config under `platform/`; shared login logic lives in the `core` submodule.

**Start here for Facebook.** Guides for Google and Apple will follow the same pattern.

---

## How social login works in this codebase

| Surface | Library | Config source |
|---------|---------|---------------|
| **Web** | [`@abacritt/angularx-social-login`](https://github.com/abacritt/angularx-social-login) | `environment.ts` (generated from `app-config.json`) |
| **iOS / Android** | [`@capgo/capacitor-social-login`](https://github.com/Cap-go/capacitor-social-login) | `environment.ts` + native Android/iOS files |
| **Backend** | CakePHP `FacebookLoginService` | No Facebook secrets required — trusts `social_id` from the client |

Login flow (all surfaces):

1. User taps **Sign in with Facebook**.
2. Frontend obtains Facebook user ID, name, and email.
3. Frontend POSTs to the users login API with `type: "fb"` and `social_id`, `name`, `email`.
4. Backend looks up `fb_id` in the database; creates an account if none exists.

Relevant frontend files:

- `src/app/_services/social-web.service.ts` — web Facebook SDK / angularx-social-login
- `src/app/_services/social-mobile.service.ts` — Capacitor Facebook login
- `src/app/pages/login/login.module.ts` — registers `FacebookLoginProvider`
- `capacitor.config.ts` — includes `@capgo/capacitor-social-login` when `ENABLE_FACEBOOK_LOGIN` is true

---

## Facebook Login — setup checklist

Use this checklist when onboarding a new platform or adding Facebook to an existing one.

- [ ] Create Facebook app(s) at [developers.facebook.com/apps](https://developers.facebook.com/apps)
- [ ] Update `platform/config/<env>/app-config.json` for each environment
- [ ] Run `npm run core prepare-platform:<env>` to regenerate `environment.ts`
- [ ] Configure Android native files (`strings.xml`, `build.gradle` `applicationId`)
- [ ] Configure iOS `*-Info.plist` files (production and staging)
- [ ] Sync native projects: `copy-android-to-core` / `copy-ios-to-core`, edit, then `copy-core-*-to-platform`
- [ ] Build and test web, iOS, and Android

> **Staging vs production:** Use separate Facebook apps (and therefore separate App IDs / client tokens) for staging and production. Nuuwayga follows this pattern — compare `platform/config/staging/app-config.json` and `platform/config/production/app-config.json`.

---

## Step 1 — Create and configure the Facebook app

Go to [developers.facebook.com/apps](https://developers.facebook.com/apps) and create an app (or use an existing one).

### Add Facebook Login

1. In the app dashboard, add the **Facebook Login** product.
2. Under **Facebook Login → Settings**, configure:
   - **Valid OAuth Redirect URIs** — your site login URL(s). Use the `loginUri` value from `app-config.json`, e.g. `https://nuuwayga.com` and `https://nuuwayga.com/`.
   - **Login from Devices** — leave off unless you have a specific need.

### Collect credentials

From **App settings → Basic**:

| Value | Used in | Notes |
|-------|---------|-------|
| **App ID** | `facebookAppId` | Public numeric ID |
| **Client token** | `facebookClientToken` | Under **Settings → Advanced → Security → Client token** (not the App Secret) |

The **App Secret** is not used in this frontend — do not commit it to the repo.

### Add platforms

Facebook validates that the calling app matches registered platform settings. Bundle / package IDs must match your `app-config.json` `appId` and Android `build.gradle` flavors.

#### iOS

1. Add **iOS** platform.
2. **Bundle ID** = `appId` from `app-config.json` for that environment.
   - Production example: `org.utelanguage.nuuwayga`
   - Staging example: `org.utelanguage.nuuwayga.staging`
3. Enable **Single Sign On** if offered.

#### Android

1. Add **Android** platform.
2. **Package name** = `applicationId` from the matching `productFlavor` in `app/build.gradle`.
3. **Default Activity Class Name** = `org.tlc.elearning.MainActivity` (this namespace is shared across all platforms — do not rename it).
4. **Key hashes** — add both debug and release signing certificate hashes.

Generate a debug key hash (macOS):

```bash
keytool -exportcert -alias androiddebugkey -keystore ~/.android/debug.keystore \
  | openssl sha1 -binary | openssl base64
```

Default debug keystore password is `android`. For release builds, use your release keystore alias and path.

#### Web

1. Under **Facebook Login → Settings**, set **Site URL** to your `webUrl` / `loginUri` domain.
2. Add your production and staging domains to **App Domains** (Settings → Basic).

### Permissions

The app requests `public_profile` and `email`. Email access may require **App Review** before working for users who are not app admins/developers/testers.

---

## Step 2 — Platform config (`app-config.json`)

Edit the config for each environment under `platform/config/`:

```
platform/config/
├── local/app-config.json
├── staging/app-config.json
└── production/app-config.json
```

Add or update these fields:

```json
{
  "facebookAppId": "123456789012345",
  "facebookClientToken": "your-client-token-here",
  "enableFacebookLogin": true,
  "loginUri": "https://your-domain.com",
  "appId": "org.yourorg.yourapp",
  "appName": "Your App Name"
}
```

| Field | Purpose |
|-------|---------|
| `facebookAppId` | Facebook App ID — drives web SDK and mobile init |
| `facebookClientToken` | Required by Facebook SDK on iOS/Android |
| `enableFacebookLogin` | Shows the login button; includes Capacitor social-login plugin |
| `loginUri` | OAuth redirect base URL (web); must match Facebook console redirect URIs |
| `appId` | Capacitor / iOS bundle ID; must match Facebook iOS settings and Android `applicationId` |
| `appName` | Display name; used for `FacebookDisplayName` on iOS |

Optional (web HTML generation only):

```json
{
  "enableFacebookSdk": true
}
```

When set, `prepare-platform` injects the Facebook JS SDK `<script>` tag into `index.html`. Web login also works via `@abacritt/angularx-social-login`, which loads the SDK at runtime — so this flag is optional.

### Regenerate frontend environment

From the **platform repo root**:

```bash
npm run core prepare-platform:staging
# or
npm run core prepare-platform:production
```

This writes:

- `core/frontend/src/environments/environment.ts` — `FACEBOOK_APP_ID`, `FACEBOOK_CLIENT_TOKEN`, `ENABLE_FACEBOOK_LOGIN`
- `core/frontend/src/index.html`

`capacitor.config.ts` reads `environment.ts` at sync time, so always run `prepare-platform` before mobile builds.

---

## Step 3 — Android native configuration

Native projects are version-controlled in `platform/android/` but **built** from `core/frontend/android/`. See [mobile-native-files.md](../.cursor/skills/bootstrap-new-platform/mobile-native-files.md) for the copy workflow.

```bash
npm run core copy-android-to-core
# Edit files under core/frontend/android/
npm run core copy-core-android-to-platform   # when ready to commit
```

### Files to update

| File | What to set |
|------|-------------|
| `app/build.gradle` | `productFlavors` → `applicationId` per flavor (must match Facebook Android package name) |
| `app/src/staging/res/values/strings.xml` | `facebook_app_id`, `facebook_client_token` |
| `app/src/production/res/values/strings.xml` | Same keys, production values |

Example `strings.xml`:

```xml
<?xml version="1.0" encoding="utf-8"?>
<resources>
    <string name="app_name">My App</string>
    <string name="facebook_app_id">123456789012345</string>
    <string name="facebook_client_token">your-client-token-here</string>
</resources>
```

Example `build.gradle` flavor block:

```gradle
productFlavors {
    production {
        dimension 'env'
        applicationId 'org.yourorg.yourapp'
    }
    staging {
        dimension 'env'
        applicationId 'org.yourorg.yourapp.staging'
    }
}
```

### Files that usually need no changes

`app/src/main/AndroidManifest.xml` already declares Facebook SDK metadata, activities, and intent filters. It reads credentials from `@string/facebook_app_id` and `@string/facebook_client_token`.

`app/src/main/java/org/tlc/elearning/MainActivity.java` implements `ModifiedMainActivityForSocialLoginPlugin` (required by `@capgo/capacitor-social-login` for Google; Facebook uses the same plugin entry point).

---

## Step 4 — iOS native configuration

```bash
npm run core copy-ios-to-core
# Edit files under core/frontend/ios/
npm run core copy-core-ios-to-platform   # when ready to commit
```

Each environment has its own Info.plist (names vary by platform, e.g. `nuuwayga-Info.plist` / `nuuwaygaStaging-Info.plist`).

### Required plist keys

| Key | Value |
|-----|-------|
| `FacebookAppID` | Your `facebookAppId` |
| `FacebookClientToken` | Your `facebookClientToken` |
| `FacebookDisplayName` | Your `appName` |
| `CFBundleURLSchemes` | Include `fb{facebookAppId}` (literal `fb` prefix + App ID) |
| `LSApplicationQueriesSchemes` | Standard Facebook scheme list (copy from a working platform) |

Example URL scheme entry inside `CFBundleURLTypes`:

```xml
<key>CFBundleURLSchemes</key>
<array>
    <string>fb123456789012345</string>
</array>
```

If you also use Google login, the same `CFBundleURLTypes` array includes the Google reversed client ID — add Facebook as an additional `<string>` in that array.

`LSApplicationQueriesSchemes` should include at minimum: `fbapi`, `fbauth`, `fbauth2`, and the versioned `fbapi*` entries. Copy the block from an existing platform Info.plist rather than hand-typing it.

---

## Step 5 — Build and test

### Web

```bash
npm run core prepare-platform:staging
npm run core serve:demo    # or your usual dev task
```

1. Open the login page — the Facebook button appears when `ENABLE_FACEBOOK_LOGIN` is true and `FACEBOOK_APP_ID` is non-empty.
2. Sign in with a Facebook test user (or an admin account while in Development mode).
3. Confirm the API receives `type: "fb"` and returns a session.

### Mobile

```bash
npm run core prepare-platform:staging
npm run core build:staging    # includes cap sync
```

- **Android Studio:** open `core/frontend/android/`, select the `staging` build variant, run on device/emulator.
- **Xcode:** open `core/frontend/ios/App/App.xcworkspace`, run the staging scheme.

Test Facebook login on a physical device when possible — simulators can behave differently with the Facebook app / Safari handoff.

After native edits, sync back to the platform repo before committing:

```bash
npm run core copy-core-android-to-platform
npm run core copy-core-ios-to-platform
```

---

## Quick reference — every file that touches Facebook

| Location | File | What |
|----------|------|------|
| Platform config | `platform/config/<env>/app-config.json` | `facebookAppId`, `facebookClientToken`, `enableFacebookLogin` |
| Generated | `core/frontend/src/environments/environment.ts` | `FACEBOOK_APP_ID`, `FACEBOOK_CLIENT_TOKEN`, `ENABLE_FACEBOOK_LOGIN` |
| Generated | `core/frontend/src/index.html` | Optional FB SDK script (`enableFacebookSdk`) |
| Capacitor | `core/frontend/capacitor.config.ts` | Conditionally bundles social-login plugin |
| Angular | `core/frontend/src/app/pages/login/login.module.ts` | `FacebookLoginProvider` registration |
| Android | `platform/android/app/build.gradle` | `applicationId` per flavor |
| Android | `platform/android/app/src/<flavor>/res/values/strings.xml` | `facebook_app_id`, `facebook_client_token` |
| Android | `platform/android/app/src/main/AndroidManifest.xml` | Facebook SDK activities + meta-data |
| iOS | `platform/ios/App/App/<app>-Info.plist` | Facebook keys, URL schemes, query schemes |
| Facebook | [developers.facebook.com/apps](https://developers.facebook.com/apps) | App ID, client token, platform settings, OAuth URIs |
| Backend | `core/backend/src/Controller/Api/Login/FacebookLoginService.php` | Login/signup by `fb_id` — no config changes needed |

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---------|--------------|-----|
| Facebook button hidden | `enableFacebookLogin: false` or empty `facebookAppId` | Set both in `app-config.json`, run `prepare-platform` |
| Web: "URL Blocked" / redirect error | OAuth redirect URI mismatch | Add exact `loginUri` URLs in Facebook Login settings |
| iOS: login opens then fails | Bundle ID mismatch | `appId` in config, Xcode bundle ID, and Facebook iOS platform must match |
| Android: login fails immediately | Package name or key hash mismatch | Verify `applicationId` and add debug/release key hashes in Facebook console |
| Email is null | App Review not approved for `email` | Add test users in Facebook app roles, or submit for review |
| Mobile changes not in build | Edited `platform/` but built from `core/frontend/` | Run `copy-android-to-core` / `copy-ios-to-core` before building |
| Capacitor plugin missing | Skipped `prepare-platform` before `cap sync` | Run `prepare-platform:<env>` then rebuild |
| Works for admins only | Facebook app still in Development mode | Switch to Live mode after completing required settings and review |

### Verify config reached the frontend

After `prepare-platform`, check `core/frontend/src/environments/environment.ts`:

```typescript
FACEBOOK_APP_ID: "123456789012345",
FACEBOOK_CLIENT_TOKEN: "...",
ENABLE_FACEBOOK_LOGIN: true,
```

The login component treats Facebook as configured when `FACEBOOK_APP_ID` is non-empty (`social-web.service.ts` → `facebookConfigValid()`).

---

## Other social providers (overview)

| Provider | Web library | Mobile library |
|----------|-------------|----------------|
| **Google** | `@abacritt/angularx-social-login` | `@capgo/capacitor-social-login` |
| **Apple** | `@capacitor-community/apple-sign-in` | `@capgo/capacitor-social-login` |

Separate setup guides for Google and Apple will be added to this file.
