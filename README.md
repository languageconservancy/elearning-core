# eLearning Core

This repo contains the core backend, frontend (web, Android, iOS), and build logic for the eLearning platform main code. It is designed as a submodule to be used by each specific eLearning platform, in order to keep those platforms separate.
This enables easy transfer of ownership of platform-specific code, database, etc., while keep the main code available for everyone to use.

## Overview

The `elearning-core` submodule provides essential functionality, configuration, and scripts that support the frontend and deployment processes.
Platform repos, which add the `elearning-core` repo as a submodule, provide the platform specific assets and configuration.

## Getting Started

## Quick Start

1. **Fork this template** to your organization and name it the name of your platform/app.

1. **Clone your fork**:

   ```bash
   git clone git@github.com:your-org/your-platform.git
   cd your-platform
   ```

1. **Initialize the core submodules**:

   ```bash
   npm run init
   # or
   git submodule update --init --recursive
   ```

1. **Install dependencies**:

   ```bash
   npm run core install-dependencies
   ```

1. **Customize your platform**:

   - Edit `platform/assets/` with your content
   - Update `platform/config/` with your settings

1. **Upload demo database to phpMyAdmin**:

   - Import core/demo/elearning_demo_db.sql to phpMyAdmin

1. **Copy demo assets to core/backend/webroot**:

   ```bash
   npm run core copy-demo-assets
   ```

1. **Set the environment variable for your local web server root directory**:
   This example points to where MAMP places its web server root.

   ```bash
   echo "export WWW_PATH='/Applications/MAMP/htdocs'" >> ~/.bash_profile
   ```

1. **Copy backend to your local web server root directory**:

   ```bash
   npm run core update-local-backend
   ```

1. **Build and Serve your platform**:
   ```bash
   npm run serve:demo
   ```

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
npm run core prepare-platform:<local|staging|production|demo>
```

This will both copy assets and generate files from templates and your config values.

## Building for an environment

To prepare and build for a specific environment you run a similar NPM script, as below. This prepare the platform, compiles the code, and syncs capacitor files to the Android and iOS projects.

```bash
npm run core build:<local|staging|production|demo>
```

## Serving for local testing

To run the code locally, for testing in a web browser, you can use the serve command below. This will allow you to test the web-app at `http://localhost:4200`.

```bash
npm run core serve:<demo|local>
```

## Android & iOS Apps

Android and iOS apps are created by you (with CapacitorJS commands) and stored in your project repo.
NPM scripts exist to copy from the core/frontend/ios|android directories to platform/ios|android and the reverse.

The core/frontend/ios|android directories are where you open the projects in Xcode and Android Studio.
Then use the commands below to copy the projects from the core repo to the platform repo where you should version control them. `.gitignore` already exists in these mobile projects, so you can add all the files with `git add platform/ios` and `git add platform/android`.

```bash
npm run core copy-core-ios-to-platform
npm run core copy-core-android-to-platform
```

### Creating the Android & iOS Projects

- Android & iOS apps should be created per CapacitorJS instructions:

From the project directory:

```bash
npm run core cap:add-android
npm run core cap:add-ios
```

These will we create `core/frontend/ios` and `core/frontend/android`.

## License

Mozilla Public License - see LICENSE file for details.
