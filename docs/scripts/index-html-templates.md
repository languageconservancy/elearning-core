# Index.html Template Generator

This directory contains the Handlebars template for generating the `index.html` file used by the Angular frontend application.

## Overview

The template system uses [Handlebars](https://handlebarsjs.com/) to generate dynamic HTML files based on configuration data from `index-meta.json` files. This allows for environment-specific customization of the HTML head section, meta tags, and script includes.

## Template Features

### Basic Variable Substitution

```handlebars
<title>{{title}}</title>
<meta name="description" content="{{description}}" />
```

### Conditional Blocks

```handlebars
{{#if googleAnalyticsTagId}}
  <!-- Google Analytics script -->
  <script async src="https://www.googletagmanager.com/gtag/js?id={{googleAnalyticsTagId}}"></script>
{{/if}}
```

### Conditional with Else

```handlebars
{{#if ogTitle}}
  <meta property="og:title" content="{{ogTitle}}" />
{{else}}
  <meta property="og:title" content="{{title}}" />
{{/if}}
```

### Array Iteration

```handlebars
{{#if customScripts}}
{{#each customScripts}}
<script src="{{this}}" {{#if ../scriptAttributes}}{{../scriptAttributes}}{{/if}}></script>
{{/each}}
{{/if}}
```

### Custom Helpers

The template includes several custom Handlebars helpers:

- `{{ifEquals arg1 arg2}}` - Compare two values for equality
- `{{ifNotEmpty value}}` - Check if a value is not empty
- `{{toLowerCase str}}` - Convert string to lowercase
- `{{toUpperCase str}}` - Convert string to uppercase

## Configuration Variables

### Required Variables

- `title` - Page title
- `description` - Page description for SEO

### SEO Variables

- `author` - Page author
- `keywords` - SEO keywords
- `language` - HTML lang attribute (defaults to "en")

### Open Graph Variables

- `ogImage` - Open Graph image URL
- `ogTitle` - Open Graph title (falls back to `title`)
- `ogUrl` - Open Graph URL
- `ogSiteName` - Open Graph site name
- `ogDescription` - Open Graph description (falls back to `description`)

### Twitter Card Variables

- `twitterCard` - Twitter card type (defaults to "summary_large_image")
- `twitterSite` - Twitter site handle
- `twitterCreator` - Twitter creator handle

### Analytics & Tracking

- `googleAnalyticsTagId` - Google Analytics tracking ID
- `facebookAppId` - Facebook App ID for SDK
- `googleClientId` - Google Client ID for OAuth
- `recaptchaSiteKey` - Google reCAPTCHA site key

### Mobile & PWA Variables

- `themeColor` - Theme color for mobile browsers
- `msApplicationTileColor` - Windows tile color
- `appleMobileWebAppCapable` - iOS web app capability
- `appleMobileWebAppStatusBarStyle` - iOS status bar style
- `appleMobileWebAppTitle` - iOS web app title

### Asset Variables

- `favicon` - Favicon URL
- `appleTouchIcon` - Apple touch icon URL

### Custom Scripts

- `customScripts` - Array of custom script URLs
- `scriptAttributes` - Attributes for custom scripts (e.g., "async defer")

## Usage

### 1. Configuration Files

Create or update `index-meta.json` files in your platform configuration:

```
platform/config/
├── local/
│   └── index-meta.json
├── staging/
│   └── index-meta.json
└── production/
    └── index-meta.json
```

### 2. Generate HTML

Run the generator script with the appropriate build type:

```bash
# Local development
node core/scripts/generate-index.js -l

# Staging
node core/scripts/generate-index.js -s

# Production
node core/scripts/generate-index.js -p
```

### 3. Build Process Integration

The generator is automatically called during the build process:

```bash
npm run build:local    # Includes generate-index:local
npm run build:staging  # Includes generate-index:staging
npm run build:production # Includes generate-index:production
```

## Example Configuration

```json
{
  "title": "My eLearning Platform",
  "author": "eLearning Team",
  "description": "Learn your community's language faster than ever.",
  "language": "en",
  "keywords": "language learning, education, community",
  "ogImage": "https://example.com/og-image.png",
  "ogTitle": "My eLearning Platform",
  "ogUrl": "https://example.com",
  "ogSiteName": "My eLearning Platform",
  "ogDescription": "Learn your community's language faster than ever.",
  "twitterCard": "summary_large_image",
  "twitterSite": "@myplatform",
  "twitterCreator": "@myplatform",
  "themeColor": "#047eb9",
  "msApplicationTileColor": "#047eb9",
  "appleMobileWebAppCapable": "yes",
  "appleMobileWebAppStatusBarStyle": "default",
  "appleMobileWebAppTitle": "eLearning",
  "favicon": "/assets/images/favicon.ico",
  "appleTouchIcon": "/assets/images/apple-touch-icon.png",
  "googleAnalyticsTagId": "GA-XXXXXXXXX-X",
  "facebookAppId": "123456789012345",
  "googleClientId": "your-google-client-id.apps.googleusercontent.com",
  "recaptchaSiteKey": "your-recaptcha-site-key",
  "customScripts": ["/assets/js/custom-analytics.js", "/assets/js/performance-monitor.js"],
  "scriptAttributes": "async defer"
}
```

## Advanced Features

### Conditional Script Loading

Scripts are only included if their corresponding configuration variables are provided:

```handlebars
{{#if facebookAppId}}
  <script src="https://connect.facebook.net/en_US/sdk.js" async defer crossorigin="anonymous"></script>
{{/if}}
```

### Performance Optimizations

The template includes preconnect links for external domains:

```html
<link rel="preconnect" href="https://www.googletagmanager.com" />
<link rel="preconnect" href="https://connect.facebook.net" />
```

### Fallback Values

The template provides sensible defaults when variables are missing:

```handlebars
<title>{{#if title}}{{title}}{{else}}eLearning Platform{{/if}}</title>
```

## Troubleshooting

### Common Issues

1. **Template not found**: Ensure `core/scripts/templates/index.html` exists
2. **Config file not found**: Check that `platform/config/{buildType}/index-meta.json` exists
3. **Invalid JSON**: Verify your `index-meta.json` file has valid JSON syntax
4. **Missing dependencies**: Run `npm install` in the core directory to install Handlebars

### Debugging

The generator provides detailed output including:

- Build type being used
- Output file location
- List of variables used with their values
- Error messages for missing files or invalid JSON

### Validation

You can validate your configuration by running the generator and checking the output. The script will show you exactly which variables are being used and their values.

## Extending the Template

### Adding New Variables

1. Add the variable to your `index-meta.json` file
2. Use it in the template with `{{variableName}}`
3. Add conditional logic if needed: `{{#if variableName}}...{{/if}}`

### Adding New Helpers

Edit `core/scripts/generate-index.js` and add new helpers:

```javascript
Handlebars.registerHelper("newHelper", function (value, options) {
  // Your helper logic here
  return processedValue;
});
```

### Custom Templates

You can create additional templates by:

1. Creating new `.html` files in the templates directory
2. Adding corresponding generator scripts
3. Updating the build process to use your new templates
