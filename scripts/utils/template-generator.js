const { readFileSync, writeFileSync, existsSync, ensureDirSync } = require("fs-extra");
const path = require("path");
const Handlebars = require("handlebars");

// Check for --no-color flag
const noColor = process.argv.includes("--no-color");

// Color constants - empty strings if --no-color is set
const RESET = noColor ? "" : "\x1b[0m";
const YELLOW = noColor ? "" : "\x1b[33m";
const RED = noColor ? "" : "\x1b[31m";
const CYAN = noColor ? "" : "\x1b[36m";
const GREEN = noColor ? "" : "\x1b[32m";

// Register Handlebars helpers
Handlebars.registerHelper("ifEquals", function (arg1, arg2, options) {
  return arg1 == arg2 ? options.fn(this) : options.inverse(this);
});

Handlebars.registerHelper("ifNotEmpty", function (value, options) {
  return value && value.trim() !== "" ? options.fn(this) : options.inverse(this);
});

Handlebars.registerHelper("toLowerCase", function (str) {
  return str ? str.toLowerCase() : "";
});

Handlebars.registerHelper("toUpperCase", function (str) {
  return str ? str.toUpperCase() : "";
});

/**
 * Generic function to compile template and generate file
 * @param {string} templatePath - Path to the Handlebars template file
 * @param {string} outputPath - Path where the generated file should be written
 * @param {object} config - Configuration object to pass to the template
 * @param {string} fileType - Human-readable name of the file type for logging
 * @param {string} buildType - Build type (local, staging, production) for logging
 * @returns {boolean} - True if successful, false otherwise
 */
function generateFileFromTemplate(templatePath, outputPath, config, fileType, buildType = "") {
  // Check if template file exists
  if (!existsSync(templatePath)) {
    console.log(
      RED + `Error: ${fileType} template file not found.` + RESET + "\nExpected path: " + templatePath + "\n"
    );
    return false;
  }

  try {
    // Read template
    const templateSource = readFileSync(templatePath, "utf-8");

    // Compile template with Handlebars
    const template = Handlebars.compile(templateSource);

    // Generate content with data
    const generatedContent = template(config);

    // Ensure output directory exists
    const outputDir = path.dirname(outputPath);
    if (!existsSync(outputDir)) {
      ensureDirSync(outputDir);
    }

    // Write output
    writeFileSync(outputPath, generatedContent);

    console.log(GREEN + `✅ ${fileType} generated` + RESET);
    console.log(CYAN + `📁 Output: ${outputPath}` + RESET + "\n");
    return true;
  } catch (error) {
    console.log(RED + `Error generating ${fileType}:` + RESET);
    console.log(RED + error.message + RESET);
    return false;
  }
}

/**
 * Load configuration from a JSON file
 * @param {string} configPath - Path to the configuration JSON file
 * @returns {object} - Parsed configuration object
 */
function loadConfig(configPath) {
  if (!existsSync(configPath)) {
    console.log(RED + "Error: Configuration file not found." + RESET + "\nExpected path: " + configPath + "\n");
    process.exit(1);
  }

  try {
    const config = JSON.parse(readFileSync(configPath, "utf-8"));

    if (!config) {
      console.log(RED + "Error: Configuration file is empty." + RESET + "\nExpected path: " + configPath + "\n");
      process.exit(1);
    }

    return config;
  } catch (error) {
    console.log(RED + "Error parsing configuration file:" + RESET);
    console.log(RED + error.message + RESET);
    process.exit(1);
  }
}

/**
 * Display variables used in configuration
 * @param {object} config - Configuration object
 */
function showVariablesUsed(config) {
  console.log(YELLOW + "📋 Variables used:" + RESET);
  Object.entries(config).forEach(([key, value]) => {
    const displayValue = value ? (value.length > 50 ? value.substring(0, 50) + "..." : value) : "(empty)";
    console.log(`   ${key}: ${displayValue}`);
  });
  console.log("\n");
}

/**
 * Parse command line arguments for build type
 * @param {Array} args - Command line arguments
 * @returns {string} - Build type (local, staging, production)
 */
function parseBuildType(args) {
  let buildType = "";

  for (let i = 0; i < args.length; ++i) {
    if (args[i][0] === "-") {
      if (args[i] === "-l") {
        buildType = "local";
      } else if (args[i] === "-s") {
        buildType = "staging";
      } else if (args[i] === "-p") {
        buildType = "production";
      } else if (args[i] === "-d") {
        buildType = "demo";
      }
    }
  }

  if (buildType === "") {
    console.log(RED + "Error: Build type is required" + RESET);
    return null;
  }

  return buildType;
}

/**
 * Validate that exactly one build type argument is provided
 * @param {Array} args - Command line arguments
 * @returns {boolean} - True if valid, false otherwise
 */
function validateBuildTypeArgs(args) {
  if (args.length > 1 || args.length <= 0) {
    return false;
  }

  for (let i = 0; i < args.length; ++i) {
    if (args[i][0] === "-") {
      if (args[i] === "-h") {
        return "help";
      } else if (args[i] === "-l" || args[i] === "-s" || args[i] === "-p" || args[i] === "-d") {
        return true;
      } else {
        return false;
      }
    }
  }

  return false;
}

/**
 * Check if --no-color flag is present
 * @returns {boolean} - True if --no-color is set
 */
function isNoColorMode() {
  return noColor;
}

module.exports = {
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
};
