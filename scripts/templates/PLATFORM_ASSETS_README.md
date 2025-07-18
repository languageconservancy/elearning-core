# Platform Assets System

This system handles copying platform-specific assets and generating templated files for Android and iOS applications.

## Overview

The platform assets system consists of:

1. **Asset Copying**: Copies platform-specific assets to the appropriate locations using `copy-platform-assets-to-core.js`
2. **Template Generation**: Uses Handlebars to generate platform-specific files using `generate-config-based-files.js`
3. **Configuration Management**: Environment-specific configuration files in `platform/config/`

## Directory Structure

```
platform/
├── assets/
│   ├── android/
|   |   └── production/
│   │       ├── drawable/           # Android drawable resources
│   │       ├── drawable-hdpi/      # High density drawables
│   │       ├── drawable-mdpi/      # Medium density drawables
│   │       ├── drawable-xhdpi/     # Extra high density drawables
│   │       ├── drawable-xxhdpi/    # Extra extra high density drawables
│   │       ├── drawable-xxxhdpi/   # Extra extra extra high density drawables
│   │       ├── drawable-land/      # Landscape orientation drawables
│   │       ├── drawable-port/      # Portrait orientation drawables
│   │       └── mipmap-*/           # App icons and launcher icons
│   ├── fonts/
│   ├── images/
│   ├── ios/
│   │   └── production/
│   │       └── Assets.xcassets/    # iOS asset catalog
│   │           ├── AppIcon.appiconset
│   │           └── launch-image.imageset
│   ├── keyboard/
│   │   └── keyboard.json        # Keyboard and characters configuration
│   ├── scss/
│   │   └── _theme.scss          # styling theme colors
│   ├── translations/
│   │   ├── translations-en.json # English version of translations for reference only
│   │   └── translations.json    # Translations used in popups, etc.
|   ├── favicon.ico              # App favorite icon for address bar, tabs, and bookmarks
└── config/
    ├── local/
    │   ├── app-config.json     # App config for local
    │   └── env-backend         # Backend configuration for local
    ├── staging/
    │   ├── app-config.json     # App config for staging
    │   └── env-backend         # Backend configuration for staging
    └── production/
        ├── app-config.json     # App config for staging
        └── env-backend         # Backend configuration for staging
```

## Asset Copying

### Web Assets

The system copies web assets from `platform/assets/` to `core/frontend/src/assets/`:

- `images/` → `assets/images/`
- `scss/` → `assets/scss/modules/`
- `translations/` → `assets/translations/`
- `fonts/` → `assets/fonts/`
- `keyboard/` → `assets/keyboard/`
- `favicon.ico` → `favicon.ico`

### Android Assets

The system copies Android drawable assets from `platform/assets/android/{buildType}/` to `core/frontend/android/app/src/main/res/`.

Supported folders:

- `drawable/` - General drawable resources
- `drawable-hdpi/` - High density drawables
- `drawable-mdpi/` - Medium density drawables
- `drawable-xhdpi/` - Extra high density drawables
- `drawable-xxhdpi/` - Extra extra high density drawables
- `drawable-xxxhdpi/` - Extra extra extra high density drawables
- `drawable-land/` - Landscape orientation drawables
- `drawable-port/` - Portrait orientation drawables
- `mipmap-*/` - App icons and launcher icons

### iOS Assets

The system copies iOS assets from `platform/assets/ios/{buildType}/Assets.xcassets/` to `core/frontend/ios/App/App/Assets.xcassets/`.

## Template Generation

The system generates the following files using Handlebars templates:

### Web Files

- `index.html` - Main HTML file with meta tags and configuration
- `environment.ts` - Angular environment configuration

### iOS Files

- `Info.plist` - iOS app configuration
- `Podfile` - iOS dependency management

### Android Files

- `MainActivity.java` - Android main activity with plugin registrations
- `strings.xml` - Android string resources
- `build.gradle` - Android build configuration

## Configuration Files

### App Configuration (app-config.json)

```json
{
  "production": false,
  "apiUrl": "http://localhost/backend/api/",
  "webUrl": "http://localhost:4200/",
  "googleClientIdWeb": "your-google-client-id-web.apps.googleusercontent.com",
  "googleClientIdIos": "your-google-client-id-ios.apps.googleusercontent.com",
  "googleReverseClientIdIos": "com.googleusercontent.apps.123456789012345-123456789012345",
  "googleApiKey": "AIzaSyA-example-google-api-key",
  "facebookAppId": "123456789012345",
  "facebookClientToken": "123456789012345",
  "cleverId": "clever-id-placeholder",
  "loginUri": "http://localhost:4200/",
  "appName": "My eLearning Platform",
  "languageEnglish": "MyLanguageEnglish",
  "languageNative": "MyLanguageNative",
  "appOwner": "MyLanguage Community Council",
  "googlePlayStoreUrl": "https://play.google.com/store/apps/details?id=org.mylanguage.app",
  "itunesStoreUrl": "https://apps.apple.com/app/id1234567890",
  "appId": "org.mylanguage.app",
  "iosAppIdNumber": "1234567890",
  "androidFlavorIosScheme": "mylanguage-ios-scheme",
  "androidVersionCode": "1.0.0",
  "androidVersionName": "1",
  "androidMainImports": ["com.codetrixstudio.capacitor.GoogleAuth.GoogleAuth"],
  "androidMainPluginRegistrations": ["GoogleAuth"],
  "ogImage": "https://local.elearning.com/assets/images/og_default.png",
  "ogDescription": "Learn the language of your community faster than ever.",
  "googleAnalyticsTagId": "",
  "enableFacebookSdk": true,
  "enableGoogleSdk": true,
  "enableCapacitorSocialLogins": true,
  "keywords": "eLearning, language, learning, community"
}
```

## Usage

### Command Line

```bash
# Copy assets for local environment
node core/scripts/copy-platform-assets-to-core.js -l

# Copy assets for staging environment
node core/scripts/copy-platform-assets-to-core.js -s

# Copy assets for production environment
node core/scripts/copy-platform-assets-to-core.js -p

# Generate config-based files for local environment
node core/scripts/generate-config-based-files.js -l

# Generate config-based files for staging environment
node core/scripts/generate-config-based-files.js -s

# Generate config-based files for production environment
node core/scripts/generate-config-based-files.js -p
```

### NPM Scripts

```bash
# Copy assets for local environment
npm run copy-assets:local

# Copy assets for staging environment
npm run copy-assets:staging

# Copy assets for production environment
npm run copy-assets:production

# Generate config-based files for local environment
npm run generate-config-based-files:local

# Generate config-based files for staging environment
npm run generate-config-based-files:staging

# Generate config-based files for production environment
npm run generate-config-based-files:production

# Prepare platform (copy assets + generate files) for local
npm run prepare-platform:local

# Prepare platform (copy assets + generate files) for staging
npm run prepare-platform:staging

# Prepare platform (copy assets + generate files) for production
npm run prepare-platform:production
```

### Build Process Integration

The platform assets generation is automatically included in the build process:

```bash
npm run build:local      # Includes prepare-platform:local
npm run build:staging    # Includes prepare-platform:staging
npm run build:production # Includes prepare-platform:production
```

### Development Server

```bash
npm run serve            # Starts development server with local assets
```

## Template Features

### Handlebars Helpers

The system includes custom Handlebars helpers:

- `{{variable}}` - Basic variable substitution
- `{{#if variable}}` - Conditional blocks
- `{{#unless variable}}` - Inverse conditional blocks
- `{{#each array}}` - Array iteration
- `{{ifEquals a b}}` - Equality comparison
- `{{ifNotEmpty value}}` - Check if value is not empty
- `{{toLowerCase str}}` - Convert to lowercase
- `{{toUpperCase str}}` - Convert to uppercase

## Adding New Assets

### Web Assets

1. Place your assets in the appropriate folder in `platform/assets/`
2. Run the copy assets script
3. Assets will be copied to the Angular app

### Android Drawables

1. Place your drawable assets in the appropriate density folder in `platform/assets/android/{buildType}/`
2. Run the copy assets script
3. Assets will be copied to the Android app

### iOS Assets

1. Place your assets in `platform/assets/ios/{buildType}/Assets.xcassets/`
2. Follow the standard iOS asset catalog structure
3. Run the copy assets script
4. Assets will be copied to the iOS app

### Custom Configuration

1. Add configuration variables to your `app-config.json` file
2. Update the appropriate Handlebars template in `core/scripts/templates/`
3. Run the generate config-based files script
4. Files will be generated with your custom configuration

## Troubleshooting

### Common Issues

1. **Assets not copied**: Check that the source directories exist in `platform/assets/`
2. **Template errors**: Verify that configuration files have valid JSON syntax
3. **Missing files**: Ensure all required template files exist in `core/scripts/templates/`

### Debugging

The scripts provide detailed output including:

- Which assets are being copied
- Which files are being generated
- Error messages for missing files or invalid configuration

### Validation

You can validate your configuration by:

1. Running the copy assets script
2. Running the generate config-based files script
3. Checking the generated files in the appropriate directories
4. Verifying that assets are copied to the correct locations

## Extending the System

### Adding New Template Types

1. Create a new Handlebars template in `core/scripts/templates/`
2. Add a generation function to `generate-config-based-files.js`
3. Update the `generateFiles` function to call your new generator
4. Add configuration variables to your config files

### Adding New Asset Types

1. Create the source directory structure in `platform/assets/`
2. Add copying logic to the appropriate function in `copy-platform-assets-to-core.js`
3. Update the documentation

### Adding New Configuration Variables

1. Add the variable to your config files
2. Update the Handlebars template to use the variable
3. Update this documentation

## Scripts Overview

### copy-platform-assets-to-core.js

Copies platform-specific assets to the core application:

- Web assets (images, fonts, translations, etc.)
- Android drawable assets
- iOS asset catalogs
- Backend environment configuration

### generate-config-based-files.js

Generates platform-specific files using Handlebars templates:

- Web files (index.html, environment.ts)
- iOS files (Info.plist, Podfile)
- Android files (MainActivity.java, strings.xml, build.gradle)

### build-with-type.js

Builds the Angular application with specific configurations:

- Local development build
- Staging build with optimizations
- Production build with full optimizations
- Mock local build for testing
