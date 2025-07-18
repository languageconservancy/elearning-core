const path = require("path");
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

let buildType = "";

let generateIndexHtml = function (config) {
  const templatePath = path.resolve(__dirname, "templates/index.html");
  const outputPath = path.resolve(__dirname, "../frontend/src/index.html");

  generateFileFromTemplate(templatePath, outputPath, config, "index.html", buildType);
};

let generateEnvironmentTs = function (config) {
  const templatePath = path.resolve(__dirname, "templates/environment.ts");
  const outputPath = path.resolve(__dirname, "../frontend/src/environments/environment.ts");

  generateFileFromTemplate(templatePath, outputPath, config, "environment.ts", buildType);
};

let generateInfoPlist = function (config) {
  const templatePath = path.resolve(__dirname, "templates/Info.plist");
  const outputPath = path.resolve(__dirname, "../frontend/ios/App/Info.plist");

  generateFileFromTemplate(templatePath, outputPath, config, "Info.plist", buildType);
};

let generateMainActivity = function (config) {
  const templatePath = path.resolve(__dirname, "templates/MainActivity.java");
  const outputPath = path.resolve(
    __dirname,
    "../frontend/android/app/src/main/java/org/tlc/elearning/MainActivity.java"
  );

  generateFileFromTemplate(templatePath, outputPath, config, "MainActivity.java", buildType);
};

let generateAndroidStrings = function (config) {
  const templatePath = path.resolve(__dirname, "templates/strings.xml");
  const outputPath = path.resolve(__dirname, "../frontend/android/app/src/main/res/values/strings.xml");

  generateFileFromTemplate(templatePath, outputPath, config, "strings.xml", buildType);
};

let generateBuildGradle = function (config) {
  const templatePath = path.resolve(__dirname, "templates/build.gradle");
  const outputPath = path.resolve(__dirname, "../frontend/android/app/build.gradle");

  generateFileFromTemplate(templatePath, outputPath, config, "build.gradle", buildType);
};

let generatePodfile = function (config) {
  const templatePath = path.resolve(__dirname, "templates/Podfile");
  const outputPath = path.resolve(__dirname, "../frontend/ios/App/Podfile");

  generateFileFromTemplate(templatePath, outputPath, config, "Podfile", buildType);
};

let generateFiles = function (config) {
  generateIndexHtml(config);
  generateEnvironmentTs(config);
  generateInfoPlist(config);
  generateMainActivity(config);
  generateAndroidStrings(config);
  generateBuildGradle(config);
  generatePodfile(config);
};

let showUsage = function () {
  console.log(
    CYAN +
      "generate-config-based-files.js:" +
      RESET +
      "\nScript to generate various files for the platform from the app-config.json file using Handlebars templating." +
      "\nThe app-config.json file is located in platform/config/${buildType}/app-config.json." +
      "\nTemplate files are located in core/scripts/templates/." +
      "\n" +
      YELLOW +
      "Usage" +
      RESET +
      ":" +
      "\n  node ./generate-config-based-files.js [options]" +
      "\n" +
      YELLOW +
      "Options" +
      RESET +
      ":" +
      "\n  -h, --help     Display this help message" +
      "\n  -d             Build type: demo" +
      "\n  -l             Build type: local" +
      "\n  -s             Build type: staging" +
      "\n  -p             Build type: production" +
      "\n  --no-color     Disable colored console output" +
      "\n" +
      YELLOW +
      "Examples" +
      RESET +
      ":" +
      "\n  node ./generate-config-based-files.js     // show help" +
      "\n  node ./generate-config-based-files.js -h  // show help" +
      "\n  node ./generate-config-based-files.js -d  // use demo app-config.json" +
      "\n  node ./generate-config-based-files.js -l  // use local app-config.json" +
      "\n  node ./generate-config-based-files.js -s  // use staging app-config.json" +
      "\n  node ./generate-config-based-files.js -p  // use production app-config.json" +
      "\n  node ./generate-config-based-files.js -l --no-color  // local build without colors" +
      "\n" +
      YELLOW +
      "Generated Files" +
      RESET +
      ":" +
      "\n  - index.html (web)" +
      "\n  - environment.ts (Angular)" +
      "\n  - Info.plist (iOS)" +
      "\n  - MainActivity.java (Android)" +
      "\n  - strings.xml (Android)" +
      "\n" +
      YELLOW +
      "Handlebars Features" +
      RESET +
      ":" +
      "\n  {{variable}}           // Basic variable substitution" +
      "\n  {{#if variable}}      // Conditional blocks" +
      "\n  {{#unless variable}}  // Inverse conditional blocks" +
      "\n  {{#each array}}       // Array iteration" +
      "\n  {{ifEquals a b}}      // Equality comparison" +
      "\n  {{ifNotEmpty value}}  // Check if value is not empty" +
      "\n  {{toLowerCase str}}   // Convert to lowercase" +
      "\n  {{toUpperCase str}}   // Convert to uppercase" +
      "\n"
  );
  process.exit(1);
};

let main = function () {
  const args = process.argv.slice(2);

  // Check for help flag
  if (args.includes("-h") || args.includes("--help")) {
    showUsage();
  }

  // Filter out --no-color from args for validation
  const filteredArgs = args.filter((arg) => arg !== "--no-color");

  const validation = validateBuildTypeArgs(filteredArgs);
  if (validation === "help") {
    showUsage();
  } else if (!validation) {
    showUsage();
  }

  buildType = parseBuildType(filteredArgs);
  if (!buildType) {
    showUsage();
  }

  const configPath = path.join(__dirname, `../../platform/config/${buildType}/app-config.json`);
  const config = loadConfig(configPath);
  showVariablesUsed(config);

  console.log("📝" + YELLOW + ` Generating files for build type "${buildType}"` + RESET + "\n");

  generateFiles(config);

  console.log(GREEN + "✅ All files generated successfully!" + RESET);
};

main();
