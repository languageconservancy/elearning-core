# Template Generator Utility

This utility module provides shared functionality for generating files from Handlebars templates across multiple scripts in the eLearning platform.

## Overview

The `template-generator.js` module centralizes common template generation logic, reducing code duplication and improving maintainability across the build system.

## Features

### Core Functions

#### `generateFileFromTemplate(templatePath, outputPath, config, fileType, buildType)`

Generic function to compile a Handlebars template and generate a file.

**Parameters:**

- `templatePath` (string) - Path to the Handlebars template file
- `outputPath` (string) - Path where the generated file should be written
- `config` (object) - Configuration object to pass to the template
- `fileType` (string) - Human-readable name of the file type for logging
- `buildType` (string, optional) - Build type for logging (local, staging, production, demo)

**Returns:** `boolean` - True if successful, false otherwise

#### `loadConfig(configPath)`

Load and parse a JSON configuration file with error handling.

**Parameters:**

- `configPath` (string) - Path to the configuration JSON file

**Returns:** `object` - Parsed configuration object

#### `showVariablesUsed(config)`

Display a summary of variables used in the configuration.

**Parameters:**

- `config` (object) - Configuration object to display

#### `parseBuildType(args)`

Parse command line arguments to extract the build type.

**Parameters:**

- `args` (Array) - Command line arguments

**Returns:** `string|null` - Build type or null if invalid

#### `validateBuildTypeArgs(args)`

Validate that exactly one build type argument is provided.

**Parameters:**

- `args` (Array) - Command line arguments

**Returns:** `boolean|string` - True if valid, "help" if help requested, false otherwise

#### `isNoColorMode()`

Check if the `--no-color` flag is present.

**Returns:** `boolean` - True if `--no-color` is set

### Handlebars Helpers

The utility registers several custom Handlebars helpers:

- `{{ifEquals arg1 arg2}}` - Compare two values for equality
- `{{ifNotEmpty value}}` - Check if a value is not empty
- `{{toLowerCase str}}` - Convert string to lowercase
- `{{toUpperCase str}}` - Convert string to uppercase

### Color Constants

Exported color constants for consistent console output:

- `RESET` - Reset color
- `YELLOW` - Yellow text
- `RED` - Red text
- `CYAN` - Cyan text
- `GREEN` - Green text

**Note:** When `--no-color` flag is present, all color constants become empty strings, effectively disabling colored output.

## Usage

### Basic Usage

```javascript
const {
  generateFileFromTemplate,
  loadConfig,
  showVariablesUsed,
  parseBuildType,
  validateBuildTypeArgs,
  isNoColorMode,
  RESET,
  YELLOW,
  RED,
  CYAN,
  GREEN,
} = require("./utils/template-generator");

// Load configuration
const config = loadConfig("path/to/config.json");

// Generate a file from template
const success = generateFileFromTemplate(
  "templates/example.hbs",
  "output/example.txt",
  config,
  "example.txt",
  "production"
);

// Show variables used
showVariablesUsed(config);

// Check if no-color mode is enabled
if (isNoColorMode()) {
  console.log("Running in no-color mode");
}
```

### Command Line Parsing

```javascript
const args = process.argv.slice(2);

// Check for help flag
if (args.includes("-h") || args.includes("--help")) {
  showUsage();
}

// Filter out --no-color from args for validation
const filteredArgs = args.filter((arg) => arg !== "--no-color");

// Validate arguments
const validation = validateBuildTypeArgs(filteredArgs);
if (validation === "help") {
  showUsage();
} else if (!validation) {
  showUsage();
}

// Parse build type
const buildType = parseBuildType(filteredArgs);
if (!buildType) {
  showUsage();
}
```

### No-Color Mode

The utility automatically detects the `--no-color` flag and disables colored output:

```bash
# Normal output with colors
node ./generate-config-based-files.js -l

# Output without colors
node ./generate-config-based-files.js -l --no-color
```

This is useful for:

- CI/CD environments where colors may not be supported
- Log files where ANSI color codes are not desired
- Accessibility for users who prefer plain text output

## Benefits

### Code Reusability

- Eliminates duplicate template compilation logic
- Centralizes Handlebars helper registration
- Provides consistent error handling and logging

### Maintainability

- Single source of truth for template generation
- Easier to update Handlebars helpers across all scripts
- Consistent console output formatting

### Error Handling

- Standardized error messages
- Proper exit codes for build failures
- Detailed logging for debugging

### Extensibility

- Easy to add new Handlebars helpers
- Simple to extend with new utility functions
- Modular design allows for easy testing

### Accessibility

- `--no-color` option for plain text output
- Consistent formatting regardless of color mode
- Support for CI/CD environments

## Scripts Using This Utility

### `generate-config-based-files.js`

Generates various configuration-based files:

- `index.html` (web)
- `environment.ts` (Angular)
- `Info.plist` (iOS)
- `MainActivity.java` (Android)
- `strings.xml` (Android)

**Usage:**

```bash
node ./generate-config-based-files.js -l
node ./generate-config-based-files.js -s --no-color
```

### `generate-platform-assets.js`

Handles platform-specific asset copying and templating:

- Android drawable assets
- iOS Assets.xcassets
- Android strings.xml files
- Android MainActivity.java files

**Usage:**

```bash
node ./generate-platform-assets.js -l
node ./generate-platform-assets.js -p --no-color
```

## Adding New Scripts

To create a new script that uses this utility:

1. **Import the utility:**

   ```javascript
   const {
     generateFileFromTemplate,
     loadConfig,
     // ... other functions as needed
   } = require("./utils/template-generator");
   ```

2. **Create your generation function:**

   ```javascript
   function generateMyFile(config) {
     const templatePath = path.resolve(__dirname, "templates/my-template.hbs");
     const outputPath = path.resolve(__dirname, "../output/my-file.txt");

     return generateFileFromTemplate(templatePath, outputPath, config, "my-file.txt", buildType);
   }
   ```

3. **Use the utility functions:**

   ```javascript
   const config = loadConfig(configPath);
   showVariablesUsed(config);
   generateMyFile(config);
   ```

4. **Handle command line options:**

   ```javascript
   const args = process.argv.slice(2);

   // Check for help
   if (args.includes("-h") || args.includes("--help")) {
     showUsage();
   }

   // Filter out --no-color
   const filteredArgs = args.filter((arg) => arg !== "--no-color");

   // Validate and parse
   const validation = validateBuildTypeArgs(filteredArgs);
   if (!validation || validation === "help") {
     showUsage();
   }

   const buildType = parseBuildType(filteredArgs);
   if (!buildType) {
     showUsage();
   }
   ```

## Testing

The utility functions are designed to be easily testable:

```javascript
// Test template generation
const result = generateFileFromTemplate("test-template.hbs", "test-output.txt", { test: "value" }, "test.txt");
assert(result === true);

// Test config loading
const config = loadConfig("test-config.json");
assert(config.test === "value");

// Test no-color mode
process.argv.push("--no-color");
const noColor = isNoColorMode();
assert(noColor === true);
```

## Future Enhancements

Potential improvements to consider:

1. **Template Validation** - Add validation for template syntax
2. **Configuration Schema** - Add JSON schema validation for config files
3. **Template Caching** - Cache compiled templates for better performance
4. **Async Support** - Add async versions of functions for large files
5. **Plugin System** - Allow custom Handlebars helpers per script
6. **Logging Levels** - Add different verbosity levels (debug, info, warn, error)
7. **Output Formats** - Support different output formats (text, JSON, XML)
