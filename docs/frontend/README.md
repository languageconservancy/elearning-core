# E-Learning Web-App & Mobile Apps

## Description
This is the frontend of a learning platform for specific endangered languages that [The Language Conservancy](https://languageconservancy.org/) works on.
This repo supports multiple language platforms built and released to different websites and apps.
It utilizes the [CapacitorJS](https://capacitorjs.com/) framework to build Android and iOS apps.

## Vocab Builder activities

The exercise and review screens use a shared [VocabBuilderComponent](../../frontend/src/app/shared/exercises/vocab-builder/vocab-builder.component.ts)
to render Bird, Coloring, Fill Letter, Match Sound, Memory, Select Grid and Word List.
Each guided activity reports completion through the existing lesson/review answer pipeline.
Word List has an explicit Continue button. Completion counts as correct practice; it
does not measure mistakes or award reading/writing/speaking/listening XP. Existing
exercise bonuses and review-unlock counters continue to apply.

### Local package setup

The frontend currently consumes a **built local package**, not the published 1.0.2
package, whose Angular peer requirements exclude Angular 22. Check out the library
alongside the platform repository:

```text
Source/
  elearning-ammilaau/core/frontend/
  vocab-builder-library/
```

Build the sibling library before installing the frontend:

```bash
cd vocab-builder-library
npm ci
npm run build
cd ../elearning-ammilaau/core/frontend
npm ci
npm run build:production
npm run test:vocab-builder
```

The frontend's [`.npmrc`](../../frontend/.npmrc) copies local dependencies rather
than symlinking them, so the library uses the host's Angular 22 runtime instead of
loading Angular 21 from its development checkout. After rebuilding the library,
refresh its installed copy with `npm ci`. Stop the dev server, run `npx ng cache clean`
and restart it after replacing a local package; Vite may otherwise retain its old
prebundled code and styles across restarts.

For deployment/CI without a sibling checkout, publish a **new** library version
with the Angular 22-compatible peer declarations, replace the `file:` dependency
in [package.json](../../frontend/package.json) with that released version, and
regenerate the lockfile. Do not reinstall published 1.0.2 with forced peer resolution.

The build and test targets in [angular.json](../../frontend/angular.json) include
the library design tokens and bundled assets, including the Coloring module worker.
The host resolves those assets against the document base URI and receives absolute
word/letter media URLs from the backend. Remote line-art images must allow CORS
for canvas pixel access, and CSP must permit the same-origin module worker.
The backend webroot's Apache configuration adds `Access-Control-Allow-Origin: *`
to public static images when `mod_headers` is enabled. Deploy that configuration
alongside the images; API CORS settings alone do not cover static files. After
changing those headers, hard-refresh or invalidate previously cached images.
The backend's `mode` selects the native word or letter target. Match Sound and
Coloring receive exactly one target type, so a word question is not accidentally
turned into a letter question. Fill Letter receives its word and missing letter.
The host rebinds word targets to the indexed content word after JSON parsing.
Letter-only activities accept a null target card and no experienced word cards.
The library currently filters letter choices by `words.length`; the host supplies
an internal letter-shaped word entry for otherwise empty letter groups. Authors
do not need to create dummy word cards, and those entries never enter word review.
Library audio and Coloring services are scoped to the host so media paths and
worker cleanup follow the exercise lifetime.
The activity frame supplies explicit height and width for the library's responsive
layouts. Coloring in the rebuilt local library (1.0.3) uses its own named size
container: desktop controls appear only when the activity itself is at least
1024px wide, regardless of the browser viewport.

Unit completion queries select distinct card IDs rather than grouping full rows,
so MySQL's `ONLY_FULL_GROUP_BY` mode does not prevent units from loading. Unit
API failures display an explicit load error rather than "This unit has no activities";
empty-unit text is reserved for successful responses with an empty activity list.

### Adding an activity

1. Upgrade/rebuild the library so its component and activity ID are exported.
2. Add the component to the typed [activity registry](../../frontend/src/app/shared/exercises/vocab-builder/vocab-builder-activities.ts).
   Set `guided` for components accepting `config` and emitting `completed`.
3. Add its content requirements and review eligibility to the backend
   [VocabBuilder registry](../../backend/src/Lib/VocabBuilder.php).
4. Extend host/payload tests. Components needing different inputs or outputs must
   have their host wiring added explicitly; they are not treated as auto-completing.

The focused host suite uses [its own TypeScript configuration](../../frontend/tests/tsconfig.vocab-builder.json)
because some unrelated legacy specs and the old test bootstrap are incompatible
with the current Angular toolchain.

## Software Versions
This project is currently using the following software
- [Angular](https://angular.io/) v14
- [Angular CLI](https://github.com/angular/angular-cli) v12.2.18
- [Node](https://nodejs.org/en) v16.20.0
- [NPM](https://www.npmjs.com/) v9.6.7
- [CapacitorJS](https://capacitorjs.com/) v5

## Multi-Platform Support
### Overview
The app can be build for different languages, pointing to different domain names, and using language-specific assets and styling. The following five sections of the code control the selection and building of the language-specific items. This is detailed futher down, with the real details in the repository [language-platform-web-language](https://bitbucket.org/owoksapedevelopers/learning-platform-web-language).
1. `angular.json` defines build and serve configurations for different languages and environments.
1. `package.json`  defines scripts that make serving, building and running language-specific code easier.
1. `src/environments` directory holds language- and build-specific files with associated variables. Then `environment-<platform>.ts` file gets copied to `environment.ts` so the rest of the software doesn't require changes.
1. `src/language` directorys contains default assets, and language-specific assets that override any same-namded default assets.
1. `src/language/default-assets/translations/translations.json` is a custom translation file for strings in the source code. The Angular service `src/app/_services/localize.service.ts` reads in the translations.json file, making the translations available to components that inject this service. Each language has its own translations.json file.
- `src/language/scripts/set-language.js` is a script that copies the specified language assets to the project locations to be used during serving and building.

### src/language
##### How it works
- The directory src/language contains
  - a set of default assets that get copied to src/assets
  - language-specific assets that overwrite default ones
  - a script at scripts/set-language.js used to changed the language of the app
- scripts/set-language.js does the following, in order
	1. Copies `<project-root>/src/language/default-assets/src/language/default-assets/` to `<project-root>/src/assets`
	1. Copies language-specific files in `<project-root>/src/language/<language>/assets` to the `<project-root>/src/assets`
	1. Copies language-specific `<project-root>/src/language/<language>/index.html,favicon.ico` to `<project-root>/src/`

##### Updating files during development
**Manually**
- Two ways to copy language-specific files
  1. `./src/scripts/set-language.js <language>`
  1. `npm run set-language:<language>`  (this defined in package.json)
**Automatically**
- To have language-specific files copied automatically before compiling, run one of the dedicated commands in package.json, where `<environment>` is one of `local|staging|production`. See the [Build](#build) section for more info.
  1. `npm run serve:<language>-<environment>`
  1. `npm run build:<language>-<environment>`
**Automatically, and live updates (for serve mode only)**
- To *watch* language files, especially after running an `npm run serve` type command you can run the following, which will recopy all language files when one changes. This needs to be run in a separate terminal.
  - `npm run set-language-watch <language>`

## Development server

To build and serve the app, rebuilding on file changes, and making it available at [http://localhost:4200](http://localhost:4200) run:

`npm run serve:<language>-<environment>`
  - where environment is one of `local|staging|production`

## Building

To build the app for distribution, run the command below. The build artifacts will be stored in the `dist/` directory. This command will run the `src/language/scripts/set-language.js` script automatically, in order to copy language-specific assets to the main project before building.
`npm run build:<language>-<environment>`
- where environment is one of `local|staging|production`
  - **local**: build for localhost
  - **staging**: build for production-like staging on https://\<language\>.elearnresource.com
  - **production**: build for production on a site like https://www.owoksape.com

## Mobile Apps (CapacitorJS)
### Overview
CapacitorJS is a cross-platform native runtime for web apps, thus allowing mobile apps to be built and run from our Angular web app. Documentation is quite thorough on their site, so I will detail just the changes in our code.
### Development Workflow
The following are the steps to build the web app and sync it to the android and ios directories to be used during the building of the mobile apps. An example of `<language>-<environment>` is `lakota-staging`.
1. Build the web app
  - `npm run build:<language>-<environment>` builds the web app to the `dist/` directory.
1. Sync the built web app to the `android` and `ios` directories
  - `npm run cap:<language>-<environment>` syncs after copying the correct `environment.<language>-<environment>.ts` file to `src/environments/environment.ts` so that the correct environment constants can be used during the syncing process. See `package.json` for more info on this command.
1. To do both of the above steps in one go, run
  - `npm run buildsync:<language>-<environment>`
1. To run the app on a device or simulator, there is a capacitor command to do so, but I've just been building/running in Android Studio and Xcode directly.
  - In Android Studio you select the flavor from the `Build` menu -> `Select Build Variant...`.
  - In Xcode you select the Scheme in the Toolbar build controls, under the Active Scheme drop-down to the left of the Active Device drop-down.

### Configuration
The CapacitorJS website gives information on [environement specific configurations](https://capacitorjs.com/docs/guides/environment-specific-configurations)
- **capacitor.config.ts**
  - Configuration happens in the `capacitor.config.ts` file, which makes use of the environment.ts file to handle platform differences, thereby avoiding the need for switch statements and environment variables inside `capacitor.config.ts`.
- **Android Product Flavors**
  - Used in Android Studio to build for the different language platforms.
  - The app's `build.gradle` file contains the `productFlavors` and `sourceSets`
    - `productFlavors` allows configuration of Application ID, Namespace, and developer-specified variables called manifestPlaceholders.
    - `sourceSets` allows use of environment-specific java and resource files, such as app icons.
  - The app's `MainActivity.java` file is contained within the path `src/main/java/org/tlc/elearning`, thus the package name is `org.tlc.elearning`. There is a package statement at the top of `MainActivity.java`, but as far as I can tell, we should be able to keep the same package name for different apps, since Application ID seems to be the important unique information about an app for app stores, OAuth, etc.
- **Xcode Schemes/Targets**
  - Used in Xcode to build for the different language platforms.
  - Under `App->Targets`, a target was/will be created for each staging and production environment.
  - `Assets.xcassets` contains the image sets for the different app icons and splash screen images.
  - There are `<environment>-Info.plist` files.
  - The app's Podfile now contains the following section of code for each environment:
    ```ruby
    target '<environment>' do
      capacitor_pods
    end
    ```
- App Icons & Splash Screen
  - Different icon sets are created in Xcode and Android Studio and are targeted by the different Android flavors and Xcode schemes.

### Plugins
We are using the following Capacitor plugins:
- Cookies
  - This allows cookies to work on Android and iOS.
  - A new angular service, `src/app/_services/cookie.service.ts` was created for this.
- HTTP
  - This allows HTTP requests to work on Android and iOS.
- Preferences
  - This allows the app to store persistent data so users aren't logged out when the app is closed or when the device is restarted.
  - UserDefaults is used on iOS. SharedPreferences is used on Android.
  - Preferences are handled in `src/app/_services/cookie.service.ts` since they are coupled. Suggestions on how to better separate the use of cookies and preferences are welcome.
- GoogleAuth
  - This allows the app to handle Google Sign-in on both Android and iOS.
  - For iOS, in Xcode, Each environment's plist file contains a `URL identifier` of REVERSED_CLIENT_ID and `URL Scheme` of the Google Client ID.
  - For Android, the Google Client ID is specified in `capacitor.config.ts` which references `environment.ts`

## Unit tests

#### Running unit tests

Run `ng test` or `npm run test` to execute the unit tests via [Karma](https://karma-runner.github.io).

#### Writing unit tests

Unit tests are the .spec.ts files in each of the components.

## end-to-end tests

#### Running end-to-end tests

Run `ng e2e` or `npm run e2e` to execute the end-to-end tests via [Protractor](http://www.protractortest.org/).

#### Writing end-to-end tests

Look in the e2e/ directory, and use the Protractor framework, which is based on Selenium.

## Further help

To get more help on the Angular CLI use `ng help` or go check out the [Angular CLI README](https://github.com/angular/angular-cli/blob/master/README.md).
