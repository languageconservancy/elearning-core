const { run } = require("node-cmd");

let preparePlatform = function (buildType) {
  run("node ./copy-platform-assets-to-core.js -" + buildType, (err, data, stderr) => {
    if (err) {
      console.error(err);
    }
  });
  run("node ./generate-environment.js -" + buildType, (err, data, stderr) => {
    if (err) {
      console.error(err);
    }
  });
};

/**
 * Show script usage help text
 */
let showUsage = function () {
  console.log(
    CYAN +
      "prepare-platform.js:" +
      RESET +
      "\nScript to prepare the platform for deployment." +
      "\n  - Copies platform assets to the core" +
      "\n  - Generates environment files" +
      "\n  - Generates index.html" +
      "\n" +
      YELLOW +
      "Usage" +
      RESET +
      ":" +
      "\n  node ./prepare-platform.js [options]" +
      "\n" +
      YELLOW +
      "Options" +
      RESET +
      ":" +
      "\n  -h    Display this help message" +
      "\n  -d    Build type: demo" +
      "\n  -l    Build type: local (default)" +
      "\n  -s    Build type: staging" +
      "\n  -p    Build type: production" +
      "\n" +
      YELLOW +
      "Examples" +
      RESET +
      ":" +
      "\n  node ./prepare-platform.js     // prepare local platform" +
      "\n  node ./prepare-platform.js -d  // prepare demo platform" +
      "\n  node ./prepare-platform.js -l  // prepare local platform (same as above)" +
      "\n  node ./prepare-platform.js -s  // prepare staging platform" +
      "\n  node ./prepare-platform.js -p  // prepare production platform" +
      "\n" +
      YELLOW +
      "Notes" +
      RESET +
      ":" +
      "\n  -w watches for changes in the default-assets and platform config directories and copies them to the core"
  );
  process.exit(0);
};

/**
 * Immediately Invoked Function Express that
 * parses the command-line arguments and initiates the copying of
 * the chosen platform directory.
 */
let parseCommandLine = function () {
  const args = process.argv.slice(2); /* Get command-line arguments */
  let buildTypeSpecified = false;
  /* Make sure number of arguments is valid */
  if (args.length > 3 || args.length < 0) {
    showUsage();
  }
  /* Parse arguments */
  for (let i = 0; i < args.length; ++i) {
    if (args[i][0] === "-") {
      if (args[i] === "-h") {
        showUsage();
      } else if (args[i] === "-d") {
        buildType = "demo";
        buildTypeSpecified = true;
      } else if (args[i] === "-l") {
        buildType = "local";
        buildTypeSpecified = true;
      } else if (args[i] === "-s") {
        buildType = "staging";
        buildTypeSpecified = true;
      } else if (args[i] === "-p") {
        buildType = "production";
        buildTypeSpecified = true;
      } else {
        showUsage();
      }
    }
  }

  if (!buildTypeSpecified) {
    console.log(YELLOW + "No build type specified. Defaulting to 'local'" + RESET);
  }

  console.log("\n🔵 Preparing platform 🔵\n");
  preparePlatform(buildType);
};

parseCommandLine();
