#!/usr/bin/env node

const { spawn } = require("child_process");
const { resolve } = require("path");

const RESET = "\x1b[0m";
const GREEN = "\x1b[32m";
const YELLOW = "\x1b[33m";
const RED = "\x1b[31m";
const CYAN = "\x1b[36m";

/**
 * Show script usage help text
 */
let showUsage = function () {
  console.log(
    CYAN +
      "build-with-type.js:" +
      RESET +
      "\nScript to build the Angular application with a specific build type." +
      "\n" +
      "The build type can be one of:" +
      "\n  -l    local" +
      "\n  -s    staging" +
      "\n  -p    production" +
      "\n  -d    demo" +
      "\n" +
      YELLOW +
      "Usage" +
      RESET +
      ":" +
      "\n  node ./build-with-type.js <build-type>" +
      "\n" +
      YELLOW +
      "Examples" +
      RESET +
      ":" +
      "\n  node ./build-with-type.js -l   // Builds for local environment" +
      "\n  node ./build-with-type.js -s   // Builds for staging environment" +
      "\n  node ./build-with-type.js -p   // Builds for production environment" +
      "\n  node ./build-with-type.js -d   // Builds for demo environment"
  );
  process.exit(0);
};

/**
 * Get the appropriate build command based on build type
 */
let getBuildCommand = function (buildType) {
  switch (buildType) {
    case "local":
      return "ng build --configuration=local";
    case "staging":
      return "ng build --aot --build-optimizer --configuration=staging";
    case "production":
      return "ng build --aot --build-optimizer --configuration=production";
    case "demo":
      return "ng build --configuration=demo";
    default:
      console.error(RED + "❌ Error: Unknown buildType: " + buildType + RESET);
      process.exit(1);
  }
};

/**
 * Execute the build command
 */
let executeBuild = function (buildCommand, buildType) {
  console.log(YELLOW + `🏗️  Building for ${buildType}...` + RESET);
  console.log(CYAN + `Command: ${buildCommand}` + RESET);

  const buildProcess = spawn(buildCommand, [], {
    stdio: "inherit",
    shell: true,
    cwd: resolve(__dirname, "../frontend"),
  });

  buildProcess.on("close", (code) => {
    if (code === 0) {
      console.log(GREEN + `✅ Build completed successfully for ${buildType}` + RESET);
    } else {
      console.error(RED + `❌ Build failed for ${buildType} with exit code ${code}` + RESET);
      process.exit(code);
    }
  });

  buildProcess.on("error", (error) => {
    console.error(RED + `❌ Build process error: ${error.message}` + RESET);
    process.exit(1);
  });
};

/**
 * Parse command-line arguments and execute build
 */
let parseCommandLine = function () {
  let buildType = "local"; /* Default build type */
  const args = process.argv.slice(2); /* Get command-line arguments */

  /* Make sure number of arguments is valid */
  if (args.length !== 1) {
    showUsage();
  }

  /* Parse arguments */
  const arg = args[0];
  if (arg[0] === "-") {
    if (arg === "-l") {
      buildType = "local";
    } else if (arg === "-s") {
      buildType = "staging";
    } else if (arg === "-p") {
      buildType = "production";
    } else if (arg === "-d") {
      buildType = "demo";
    } else {
      showUsage();
    }
  } else {
    showUsage();
  }

  /* Execute build */
  const buildCommand = getBuildCommand(buildType);
  executeBuild(buildCommand, buildType);
};

parseCommandLine();
