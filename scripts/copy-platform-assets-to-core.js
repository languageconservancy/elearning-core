/**
 * set-platform.js
 * Script that allows copying of a platform-specific directory to the root
 * directory renamed to current-platform.
 * The default is to prompt the user to confirm the copy operation, but
 * this can be avoided for use in trusted commands.
 * This script can be called
 *   - alone using node
 *   - via npm using npm run set-platform after adding it to package.json
 */

const { existsSync, copySync, readFileSync, writeFileSync, ensureDirSync } = require("fs-extra");
const path = require("path");
const { watch } = require("hound");
const {
  parseBuildType,
  validateBuildTypeArgs,
  isNoColorMode,
  RESET,
  YELLOW,
  RED,
  CYAN,
  GREEN,
  BLUE,
} = require("./utils/template-generator");

let watchFiles = false;
let Consts = {};
let CopyPaths = [];
let dryRun = false;
let buildType = "local";

let setConsts = function () {
  Consts = {
    PLATFORM_DIR: path.resolve(__dirname + "/../../platform"),
    PLATFORM_ASSETS_DIR: path.resolve(__dirname + "/../../platform/assets"),
    PLATFORM_CONFIG_DIR: path.resolve(__dirname + "/../../platform/config"),
    ROOT_DIR: path.resolve(__dirname + "/../../"),
    FRONTEND_SRC_DIR: path.resolve(__dirname + "/../frontend/src"),
    FRONTEND_ASSETS_DIR: path.resolve(__dirname + "/../frontend/src/assets"),
    FRONTEND_DEFAULT_ASSETS_DIR: path.resolve(__dirname + "/../frontend/src/default-assets"),
    BACKEND_CONFIG_DIR: path.resolve(__dirname + "/../backend/config"),
    E2E_DIR: path.resolve(__dirname + "/../frontend/e2e/lib"),
    EXCLUDE_FILES: ["default-assets", "src", "scripts", "README.md", "platform"],
  };
};

let setCopyPaths = function () {
  CopyPaths = [
    {
      from: Consts.FRONTEND_DEFAULT_ASSETS_DIR,
      to: Consts.FRONTEND_ASSETS_DIR,
    },
    {
      from: Consts.PLATFORM_ASSETS_DIR + "/images/",
      to: Consts.FRONTEND_ASSETS_DIR + "/images/",
    },
    {
      from: Consts.PLATFORM_ASSETS_DIR + "/scss/",
      to: Consts.FRONTEND_ASSETS_DIR + "/scss/modules/",
    },
    {
      from: Consts.PLATFORM_ASSETS_DIR + "/translations/",
      to: Consts.FRONTEND_ASSETS_DIR + "/translations/",
    },
    {
      from: Consts.PLATFORM_ASSETS_DIR + "/fonts/",
      to: Consts.FRONTEND_ASSETS_DIR + "/fonts/",
    },
    {
      from: Consts.PLATFORM_ASSETS_DIR + "/keyboard/",
      to: Consts.FRONTEND_ASSETS_DIR + "/keyboard/",
    },
    {
      from: Consts.PLATFORM_ASSETS_DIR + "/favicon.ico",
      to: Consts.FRONTEND_SRC_DIR + "/favicon.ico",
    },
    {
      from: Consts.PLATFORM_CONFIG_DIR + "/" + buildType + "/env-backend",
      to: Consts.BACKEND_CONFIG_DIR + "/.env",
    },
  ];
};

// Copy Android drawable assets
function copyAndroidAssets() {
  const platformAssetsDir = path.resolve(__dirname + `/../../platform/assets/android/${buildType}/`);
  const androidResDir = path.resolve(__dirname, "../frontend/android/app/src/main/res/");

  console.log(CYAN + "📱 Copying Android assets..." + RESET);

  if (!existsSync(platformAssetsDir)) {
    console.log(YELLOW + "⚠️  No Android assets found at: " + platformAssetsDir + RESET);
    return;
  }

  try {
    // Copy all drawable folders
    const drawableFolders = [
      "drawable",
      "drawable-hdpi",
      "drawable-mdpi",
      "drawable-xhdpi",
      "drawable-xxhdpi",
      "drawable-xxxhdpi",
      "drawable-land",
      "drawable-port",
      "mipmap-hdpi",
      "mipmap-mdpi",
      "mipmap-xhdpi",
      "mipmap-xxhdpi",
      "mipmap-xxxhdpi",
    ];

    drawableFolders.forEach((folder) => {
      const sourcePath = path.join(platformAssetsDir, folder);
      const destPath = path.join(androidResDir, folder);

      if (existsSync(sourcePath)) {
        ensureDirSync(path.dirname(destPath));
        copySync(sourcePath, destPath, { overwrite: true });
        console.log(GREEN + "✅ Copied: " + folder + RESET);
      }
    });

    console.log(GREEN + "✅ Android assets copied successfully" + RESET);
  } catch (error) {
    console.log(RED + "❌ Error copying Android assets: " + error.message + RESET);
  }
}

// Copy iOS Assets.xcassets
function copyIosAssets() {
  const platformAssetsDir = path.resolve(__dirname + `/../../platform/assets/ios/${buildType}/Assets.xcassets/`);
  const iosAssetsDir = path.resolve(__dirname, "../frontend/ios/App/App/Assets.xcassets/");

  console.log(CYAN + " Copying iOS assets..." + RESET);

  if (!existsSync(platformAssetsDir)) {
    console.log(YELLOW + "⚠️  No iOS assets found at: " + platformAssetsDir + RESET);
    return;
  }

  try {
    ensureDirSync(path.dirname(iosAssetsDir));
    copySync(platformAssetsDir, iosAssetsDir, { overwrite: true });
    console.log(GREEN + "✅ iOS assets copied successfully" + RESET);
  } catch (error) {
    console.log(RED + "❌ Error copying iOS assets: " + error.message + RESET);
  }
}

/**
 * Copies chosen platform directory to root and names it current-platform
 * @param watching boolean Whether the script is being run in watch mode
 */
let copyAssets = function () {
  // Copy files from platform to core
  CopyPaths.forEach((path) => {
    let src = path.from;
    let dst = path.to;

    console.log("Copying " + YELLOW + src + RESET + "\n" + "     to " + CYAN + dst + RESET);

    if (!existsSync(src)) {
      console.log("⚠️" + RED + " Warning" + RESET + ": not copying " + src + " because it doesn't exist");
      return;
    }

    try {
      copySync(src, dst, { overwrite: true });
      console.log("✅ Success");
    } catch (err) {
      console.error(err);
      process.exit(1);
    }
  });

  // Copy mobile assets
  copyAndroidAssets();
  copyIosAssets();
};

let watchSrcFiles = function () {
  files = [Consts.ROOT_DIR + "/default-assets", Consts.PLATFORM_CONFIG_DIR];
  let watchers = [];

  for (let i = 0; i < files.length; ++i) {
    console.log("Adding " + files[i] + " to watcher");
    watchers.push(watch(files[i]));
    watchers[i].on("create", function (file) {
      console.log(file + " was created");
      copyAssets();
    });
    watchers[i].on("change", function (file) {
      console.log(file + " was changed");
      copyAssets();
    });
    watchers[i].on("delete", function (file) {
      console.log(file + " was deleted");
      copyAssets();
    });
  }
};

/**
 * Converts hex color with leading hash or not to an rgba string
 * such as 'rgba(23, 24, 25, 1)'
 * @param hex string Hex color value
 * @return string 'rgba(<value>, <value>, <value>, 1)'
 */
let hexToRgba = function (hex, noAlpha = false) {
  var components = hex.match(/[A-Za-z0-9]{2}/g);
  const rgb = [parseInt(components[0], 16), parseInt(components[1], 16), parseInt(components[2], 16)];
  return "rgb" + (noAlpha ? "" : "a") + "(" + rgb.join(", ") + (noAlpha ? ")" : ", 1)");
};

/**
 * Takes colors in _theme.scss module and creates a typescript file to be used
 * by end-to-end (e2e) tests. Creates file in e2e/lib/
 */
let createColorsTsFile = function () {
  const themePath = Consts.FRONTEND_DEFAULT_ASSETS_DIR + "/scss/modules/_theme.scss";
  let data;
  try {
    data = readFileSync(themePath, { encoding: "utf8" });
    // console.log(data);
  } catch (err) {
    console.error(err);
  }
  if (!data) {
    return console.error(themePath + " couldn't be read");
  }

  const labels = data.match(/(?<=\$).*(?=:)/gm);
  const colors = data.match(/#.{6}/gm);
  // console.log(labels);
  // console.log(colors);

  data = `
// Color theme definition used in End-to-End testing.
// Comments are there so that programs with a color helper work.
// You may need to change sytanx highlighting to CSS.\n\n`;

  data += "export const ColorThemeHex = {\n";
  for (let i = 0; i < labels.length; ++i) {
    data += `  ${labels[i].replace(/-/gm, "_").toUpperCase()}: '${colors[i]}', // ${colors[i]}\n`;
  }
  data += "};\n\n";

  data += "export const ColorThemeRgba = {\n";
  for (let i = 0; i < labels.length; ++i) {
    data += `  ${labels[i].replace(/-/gm, "_").toUpperCase()}: '${hexToRgba(colors[i])}', // ${hexToRgba(colors[i])}\n`;
  }
  data += "};\n\n";

  data += "export const ColorThemeRgb = {\n";
  for (let i = 0; i < labels.length; ++i) {
    data += `  ${labels[i].replace(/-/gm, "_").toUpperCase()}: '${hexToRgba(colors[i], true)}', // ${hexToRgba(
      colors[i],
      true
    )}\n`;
  }
  data += "};\n\n";

  writeFileSync(Consts.E2E_DIR + "/color-theme.ts", data);
};

/**
 * Show script usage help text
 */
let showUsage = function () {
  console.log(
    "copy-platform-assets-to-core.js:" +
      RESET +
      "\nScript to copy platform assets to the core, overwriting default assets." +
      "\n" +
      "Usage" +
      RESET +
      ":" +
      "\n  node ./copy-platform-assets-to-core.js [options]" +
      "\n" +
      "Options" +
      RESET +
      ":" +
      "\n  -h    Display this help message" +
      "\n  -d    Build type: demo" +
      "\n  -l    Build type: local (default)" +
      "\n  -s    Build type: staging" +
      "\n  -p    Build type: production" +
      "\n  -d    Dry run" +
      "\n  --no-color     Disable colored console output" +
      "\n" +
      "Examples" +
      RESET +
      ":" +
      "\n  node ./copy-platform-assets-to-core.js" +
      "\n  node ./copy-platform-assets-to-core.js -w" +
      "\n  node ./copy-platform-assets-to-core.js -d" +
      "\n  node ./copy-platform-assets-to-core.js -l" +
      "\n  node ./copy-platform-assets-to-core.js -s" +
      "\n  node ./copy-platform-assets-to-core.js -p" +
      "\n  node ./copy-platform-assets-to-core.js -d" +
      "\n  node ./copy-platform-assets-to-core.js -l --no-color" +
      "\n" +
      "Notes" +
      RESET +
      ":" +
      "\n  -w watches for changes in the default-assets and platform config directories and copies them to the core" +
      "\n  Also copies Android and iOS mobile assets to their respective platform directories"
  );
  process.exit(0);
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

  setConsts();
  setCopyPaths();

  if (dryRun) {
    console.log("Dry run mode enabled. No files will be copied.");
    console.log("Copy paths:", CopyPaths);
    process.exit(0);
  }

  if (watchFiles) {
    watchSrcFiles();
  } else {
    console.log("\n📝 Copying platform assets to core...\n");
    createColorsTsFile();
    copyAssets();
  }
};

main();
