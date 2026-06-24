# Demo Database Workflow

The demo database (`core/demo/elearning_demo_db.sql`) is the shared test fixture for all eLearning platforms. It should contain representative lessons, exercises (every type), and teacher portal scenarios so bugs can be reproduced consistently.

## Philosophy

When a bug shows up in an exercise (or lesson, or teacher portal feature) on **any** platform:

1. Fix the code in **elearning-core** (or platform config if platform-specific).
2. **Add that scenario to demo** so the case is permanently covered.
3. **Commit the updated SQL dump to core** so every developer and platform can test it.

A bug fix without a demo case is incomplete — the same regression can slip through again.

## Initial setup (once)

1. In phpMyAdmin, create database `elearning_demo_db` (collation: `utf8mb4_general_ci`).
2. Import `core/demo/elearning_demo_db.sql`.
3. Ensure `platform/config/demo/app_local.php` points at `elearning_demo_db` (default).
4. `npm run core copy-demo-assets && npm run core sync-local-backend`
5. `npm run core serve:demo` → test at `http://localhost:4200`

## Adding a new test case to demo

### 1. Reproduce locally

```bash
npm run core serve:demo
```

Confirm the bug with the current demo DB, or identify what's missing.

### 2. Add content in demo

Use the **admin panel** at `http://localhost/backend/admin/` (demo DB, local MAMP) to create or adjust:

- Lessons and units containing the exercise type
- Exercise configuration that triggers the bug scenario
- Teacher portal / classroom setup if the bug is teacher-facing

Aim for the **minimal case** that reproduces the issue — don't dump entire platform content into demo.

If the bug needs specific webroot media (audio, images), add files to `core/demo/webroot/` and run `npm run core copy-demo-assets`.

### 3. Export the database

In phpMyAdmin:

1. Select the `elearning_demo_db` database.
2. **Export** tab → Quick or Custom export → SQL format.
3. Save and replace `core/demo/elearning_demo_db.sql`.

Or from the command line (MAMP MySQL example):

```bash
/Applications/MAMP/Library/bin/mysqldump -u root -proot elearning_demo_db > core/demo/elearning_demo_db.sql
```

Adjust credentials/path for your local MySQL setup.

### 4. Verify the export

```bash
# Re-import into a fresh database (or drop and re-import elearning_demo_db)
npm run core serve:demo
```

Walk through the new lesson/exercise and confirm the scenario is covered.

### 5. Commit to elearning-core

```bash
cd core
git add demo/elearning_demo_db.sql
# Also commit demo/webroot/ if you added media assets
git commit -m "Add demo case for <exercise type / bug description>"
git push
```

Commit the SQL in **core**, not the platform repo.

### 6. Team picks up the update

After pulling the core submodule update:

```bash
npm run update-core
```

Re-import `core/demo/elearning_demo_db.sql` into phpMyAdmin (replace existing `elearning_demo_db`).

## What belongs in demo

| Include | Exclude |
|---------|---------|
| Every exercise type at least once | Platform-specific branding content |
| Edge cases found via production bugs | Real user PII or production data |
| Teacher portal representative flows | Full curriculum from any one language |
| Minimal data to trigger each code path | Entire platform lesson libraries |

## Checklist: bug found on a platform

```
- [ ] Reproduce with serve:demo (or identify missing demo case)
- [ ] Fix code in core (or platform config if not shared)
- [ ] Add minimal lesson/exercise to demo DB via admin
- [ ] Add demo webroot assets if needed (copy-demo-assets)
- [ ] Export → replace core/demo/elearning_demo_db.sql
- [ ] Verify locally after re-import
- [ ] Commit SQL (and webroot) to elearning-core
- [ ] Notify team to re-import demo DB after submodule update
```
