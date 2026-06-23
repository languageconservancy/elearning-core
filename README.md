# eLearning Core

[![GitHub Release](https://img.shields.io/github/v/release/languageconservancy/elearning-core?style=flat-square)](https://github.com/languageconservancy/elearning-core/releases)
[![License: MPL 2.0](https://img.shields.io/badge/License-MPL_2.0-brightgreen.svg?style=flat-square)](https://opensource.org/licenses/MPL-2.0)
![GitHub contributors](https://img.shields.io/github/contributors/languageconservancy/elearning-core?style=flat-square)

This repo contains the core backend, frontend (web, Android, iOS), and build logic for the eLearning platform main code. It is designed as a submodule to be used by each specific eLearning platform, in order to keep those platforms separate.
This enables easy transfer of ownership of platform-specific code, database, etc., while keep the main code available for everyone to use.

## Technologies

See [package.json](https://github.com/languageconservancy/elearning-core/blob/main/frontend/package.json) and [composer.json](https://github.com/languageconservancy/elearning-core/blob/main/backend/composer.json) for current versions.

- **Frontend**: Angular (TypeScript)
- **Backend**: CakePHP (PHP)
- **Mobile Wrapper**: CapacitorJS
- **Styling**: Bootstrap
- **Tooling**: Node.js, npm, Composer, Prettier

## Overview

The `elearning-core` submodule provides essential functionality, configuration, and scripts that support the frontend and deployment processes.
Platform repos, which add the `elearning-core` repo as a submodule, provide the platform specific assets and configuration.

## Prerequisites

### Platform repo with submodule

- If you haven't already, create your platform repository by following the instruction in the [elearning-template repo](https://github.com/languageconservancy/elearning-template).
- Before starting the instructions below, you should have your platform repo cloned to your local computer and the `core` submodule is pulled in.

### Local Apache Server

- You must have a local Apache server to run this project locally.
- This README assume the use of MAMP.

#### Installing & Setting Up MAMP

1. Install MAMP from [https://www.mamp.info/en/mac/](https://www.mamp.info/en/mac/). As of January 2026 we are on v7.3.
1. In MAMP set the following paramters:

    - PHP version: v7.4.33
    - Ports: Apache: 80, Nginx: 80, MySQL: 3306
    - MySQL server: v5.7.44
    - PHP-Cache: OPcache
    - Document Root: Application->MAMP->htdocs (assumes backend is in htdocs. If it's in a subfolder, point to whatever its parent is)
  

    1. **NOTE**: If you don’t see 7.4.33 in the PHP version dropdown do the following: Goto /Application/MAMP/bin/php and rename all php versions except the two you want as options by adding an underscore to the beginning of their names.
    1. **NOTE**: You may have to add this to your ~/.bash_profile to use PHP v7.4.33 instead of the latest version:
        - `export PATH="/Applications/MAMP/bin/php/php7.4.33/bin/:$PATH"`

1. In /Application/MAMP/conf/apache/httpd.conf, make sure this line is uncommented so plugins load properly in the backend.
    - `LoadModule rewrite_module modules/mod_rewrite.so`

1. Set `sql_mode`. This is to avoid having to change it in phpMyAdmin every time the server is restarted.

    - Copy `core/demo/mamp/my.cnf` to `/Applications/MAMP/conf/`
    - The contents are:
```bash
[mysqld]
sql_mode="STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
```

1. Start the server
    - Click the **Start** button at the top-left of the MAMP window.

### Add Path to Server Root as Environment Variable

**Set the environment variable for your local web server root directory and source it**:

This example points to where MAMP places its web server root. Run one of the following to add the `WWW_PATH` used in the `npm run core sync-local-backend` script.

   For bash shells on Mac

   ```bash
   echo "export WWW_PATH='/Applications/MAMP/htdocs'" >> ~/.bash_profile
   source ~/.bash_profile
   ```

   For zsh shells on Mac

   ```bash
   echo "export WWW_PATH='/Applications/MAMP/htdocs'" >> ~/.zshenv
   source ~/.zshenv
   ```

### Demo database

**Open phpMyAdmin**

- With MAMP server started, open **phpMyAdmin** by going to `http://localhost/phpmyadmin`

**Create new elearning_demo_db database**
- In the left side menu, click **New**
- Database name: **elearning_demo_db**
- Collation type: **utf8mb4_general_ci**

**Import the database**
- Select the newly crated database in the left menu
- Select the **Import** tab at the top
- Select **Choose File** and select the database located at `core/demo/elearning_demo_db.sql`
- You can leave all the other settings alone
- Click the **Import** button

### Github Personal Access Token

#### Create a Personal Access Token in Github

  - This is required because we are pulling a private repo using composer, since the public one has a bug.
  1. On github.com go to your user settings (Avatar icon in top right -> Settings)
  1. Scroll to very bottom of side menu and click on **Developer Settings**
  1. Under **Personal Access Tokens** dropdown, select **Tokens (classic)**
  1. Click the dropdown **Generate new token** and select
  1. Make sure only **public_repo** under **repo** is checked. **repo** should not be checked.
  1. Create the token and save it somewhere safe. You won't be able to access it again.

### Install dependencies

Run to following command to from the parent repo to install the following:
- frontend NPM packages
- backend composer packages (this is where you'll paste your Personal Access Token when prompted)
- cocoapods

```bash
npm run core install-dependencies
```

### Copy demo assets to core/backend/webroot

```bash
npm run core copy-demo-assets
```

## Prepare Environment

### Notes on running `npm` commands

- If you're in your platform directory, you'll run `npm run core` ...
- If you're in the core directory, you'll just run `npm run` ...

The following instructions assume you're in your platform directory, just above the core submodule.

### Prepare Platform (demo)

Run this command so that `environment.ts` is built, which the next step requires.

```bash
npm run core prepare-platform:demo
```

## Build the Web-App

### Build the demo web-app

This will build the Angular web-app and sync the iOS and Android projects if they exist.

```bash
npm run core build:demo
```

## .env file

A .env file is required for database and secrets access by the backend.

This file must live on the server (local or production) in `backend/config/`.
A default file exists at `core/backend/config/env.default`.

Where you store this file or the info in it is up to you. Just make sure the staging and production version are secure.

## Serving the demo

To serve the demo web-app you'll need to make sure the backend is copied to the server root, and then serve the Angular app:

### Copy repo backend to server root

This will copy the core/backend files in your sandbox to the server root at `$WWW_PATH/backend/`

```bash
npm run core sync-local-backend
```

### Serve

Build and serve the app. The default url is `localhost:4200`

```bash
npm run core serve:demo
```

## Android & iOS Apps

### Add Android & iOS Projects

If you need to build Android and iOS apps, add the projects using CapacitorJS.
These command need to be run after preparing the platform, so that `environment.ts` is already generated (See previous step).

```bash
npm run core cap:add-android
npm run core cap:add-ios
```

### Version control of Mobile Apps

The Android and iOS projects must be built inside the core/frontend directory, but can't be version-controlled there, because that's a submodule and is platform-agnostic.

Two pairs of NPM scripts exist to solve this:

#### Update your version-controlled Android app from the one in `core/frontend/android`

```bash
npm run core copy-core-android-to-platform
```

#### Update your version-controlled iOS project from the one in `core/frontend/ios`

```bash
npm run core copy-core-ios-to-platform
```

#### Update the buildable Android project from your version-controlled one

```bash
npm run core copy-android-to-core
```

#### Update the buildable iOS project from your version-controlled one

```bash
npm run core copy-ios-to-core
```

## Syncing the Mobile Apps

If the web-app is built but your need to sync it to the mobile app projects again, you can run the following command:

```bash
npm run core cap:sync
```

You should make to the apps are synced before building/running the apps in Android Studio or Xcode.

### Building and running the Android app

You must open and build/run the Android app using Android Studio.

Any time you update the web-app, you need to rebuild so the build web-app in the Android folder is updated.

### Building and running the iOS app

You must open and build/run the iOS app using Xcode.

Any time you update the web-app, you need to rebuild so the build web-app in the iOS folder is updated.

### Running the Android app on a physical device

To run the staging or production Android app on your physical Android device, you'll need to create a signed APK and transfer it to your device.

In Android Studio:

1. **Build** -> **Generate Signed App Bundle or APK**
1. Select **APK** in the dialog window and click **Next**
1. Under **Key store path**, click **Choose existing...** to find your staging keystore.
1. Fill in **Key store password**, **Key alias**, and **Key password**
1. Click **Next**

#### Ensure your have ADB (Android Debugger command-line tool)

You can install ADB via Android Studio or Homebrew

**Android Studio:**

1. **Android Studio** -> **Settings...**
1. **Languages & Frameworks** -> **Android SKD**
1. Select the **SDK Tools** tab
1. Make sure the following lines are checked:
    - Android SDK Build-Tools
    - Android SDK Command-line Tools (latest)
    - Android SDK Platform-Tools
1. Click **Apply**

In the terminal, navigate to where `adb` is:

```bash
cd ~/Library/Android/sdk/platform-tools/
```

#### Transfer signed APK to device

**Check existing devices**

```bash
./adb devices
```

**Transfer APK to device**

```bash
./adb -s <device_id> install <path-to-apk>
```

### Running the iOS app

To run the iOS, open Xcode workspace (NOT the project), which is at `core/frontend/ios/App/App.xcworkspace/`

1. In the top bar, select the production or staging app, and the device or simulator to run on
1. Click the Run icon at the top-left

## Structure

- `backend/`
  Contains the CakePHP backend Application Programming Interface (API) and Admin panel user interface.

  - API includes two prefixes
    - `/api/*`: used by the web, Android, and iOS apps used by general users.
    - `/admin/*`: used by the Admin panel web interface used by curriculum developers and admins.

- `frontend/`
  Contains the Angular frontend application source code and assets. This is where the ios and android projects are placed by Capacitor.

- `scripts/`
  Includes build scripts, template generators, and utilities for managing the frontend and deployment.

- `demo/`
  A pre-filled database and assets, meant to include all the various lesson and exercise types, for thorough manual testing via the frontend apps. New exercises and lessons that confirm bugfixes will be added to this database.
  To use this, import the database into your local phpMyAdmin and copy the webroot assets to your www/backend/webroot directory.

- `README.md`
  This file.

- `LICENSE`
  Terms of use of this repository.

- `package.json`
  NPM packages and scripts. From the project repo, you can run `npm run core <command>` if it contains the `scripts/proxy.js` script and its `package.json` file includes the script `"core": "node scripts/proxy.js"`.

## Incorporating this repo as a submodule

This submodule is typically included as a Git submodule in the main repository. To initialize and update submodules, run:

`git submodule add git@github.com:languageconservancy/elearning-core.git core`

## Preparing your project

Your project will contain the **assets** and **configuration** for each environment that you need. Not all environments are required. A complete project setup looks like this:

#### High-level structure

```text
platform/
├── core/   # elearning-core submodule
├── assets/ # platform-specific assets (used as or replace defaults)
├── config/ # platform-specific configuration (used to generate files from templates)
├── package.json # platform project manifest
└── README.md # platform readme
```

#### Detailed structure

```text
platform/
├── core/ # elearning-core submodule
├── assets/
│   ├── fonts/
│   ├── images/ # UI image overrides
│   ├── keyboard/
│   │   └── keyboard.json # keyboard/chars config
│   ├── scss/
│   │   └── _theme.scss # color theme override
│   ├── translations/
│   │   ├── translations-en.json # English reference
│   │   └── translations.json # translations for popups
│   └── favicon.ico # favorite icon
├── config/
│   ├── demo
│   │   ├── app_local.php
│   │   └── app-config.json
│   ├── local
│   │   ├── app_local.php
│   │   └── app-config.json
│   ├── production
│   │   ├── app_local.php
│   │   └── app-config.json
│   └── staging
│   │   ├── app_local.php
│   │   └── app-config.json
├── android/ # Android project
├── ios/ # iOS project
├── package.json # project manifest file
└── README.md
```

### Frontend vs Backend Assets & Configs

Most of the asssets/configs are used in the `frontend`. The following are exceptions:

Files used only for the `backend`:

- `config/<environment>/app_local.php`
- `config/<environment/.env>`

Files used for both the `frontend` & `backend`:

- `assets/keyboard/keyboard.json`

## Preparing an environment for building

To prepare a specific environment you run the high-level NPM script below from your platform repo root directory, where `<local|staging|...` are the options that you choose from depending on which environment want to prepare, such as `local`, `staging`, `production`, or `demo`.

```bash
npm run core prepare-platform:<demo|local|staging|production>
```

This will both copy assets and generate files from templates and your config values.

## Building for an environment

To prepare and build for a specific environment you run a similar NPM script, as below. This prepare the platform, compiles the code, and syncs capacitor files to the Android and iOS projects.

```bash
npm run core build:<demo|local|staging|production>
```

## Serving for local testing

To run the code locally, for testing in a web browser, you can use the serve command below. This will allow you to test the web-app at `http://localhost:4200`.

```bash
npm run core serve:<demo|local>
```

These will we create `core/frontend/ios` and `core/frontend/android`.

## Social Logins

### Overview

Apple, Google, and Facebook login are supported to varying degrees. Social logins are handled by two separate packages:

- [Cap-go/capacitor-social-login](https://github.com/Cap-go/capacitor-social-login) for Android and iOS
- [angularx-social-login](https://github.com/abacritt/angularx-social-login) for web

### Web

Supported social logins on web are Apple, Google, and Facebook.

### Android

Supported social logins on Android are Google and Facebook.

### iOS

Supported social logins on iOS are Apple, Google, and Facebook.

## License

Mozilla Public License - see LICENSE file for details.
