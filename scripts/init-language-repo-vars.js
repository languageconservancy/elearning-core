/**
 * Copy var-file templates from core/scripts/examples/ to the language repo scripts/
 * if they do not already exist. Never overwrites existing files.
 */

const { copySync, existsSync, mkdirSync } = require("fs-extra");
const path = require("path");

const CORE_DIR = path.resolve(__dirname, "..");
const PLATFORM_REPO_DIR = path.resolve(CORE_DIR, "..");
const EXAMPLES_DIR = path.join(__dirname, "examples");
const PLATFORM_SCRIPTS_DIR = path.join(PLATFORM_REPO_DIR, "scripts");

const VAR_FILES = [
  {
    example: "local-dev-vars.example.sh",
    target: "local-dev-vars.sh",
    note: "gitignored — set ELEARNING_WWW_PATH for your machine",
  },
  {
    example: "deploy-vars.example.sh",
    target: "deploy-vars.sh",
    note: "version controlled — set staging/production deploy paths for this platform",
  },
];

function main() {
  mkdirSync(PLATFORM_SCRIPTS_DIR, { recursive: true });

  let created = 0;
  let skipped = 0;

  for (const { example, target, note } of VAR_FILES) {
    const src = path.join(EXAMPLES_DIR, example);
    const dest = path.join(PLATFORM_SCRIPTS_DIR, target);

    if (!existsSync(src)) {
      console.error(`❗ Template not found: ${src}`);
      process.exit(1);
    }

    if (existsSync(dest)) {
      console.log(`⏭️  Skipped ${target} (already exists)`);
      skipped++;
      continue;
    }

    copySync(src, dest, { overwrite: false });
    console.log(`✅ Created scripts/${target} — ${note}`);
    created++;
  }

  console.log("");
  if (created > 0) {
    console.log("Edit the new file(s) under scripts/ in your language repo, then:");
    console.log("  • local-dev-vars.sh — set ELEARNING_WWW_PATH (see docs/getting-started/local-server-setup.md)");
    console.log("  • deploy-vars.sh — set deploy hosts/paths and commit (if this is a new platform)");
  }
  if (created === 0 && skipped === VAR_FILES.length) {
    console.log("All language repo var files already exist. No changes made.");
  }
}

main();
