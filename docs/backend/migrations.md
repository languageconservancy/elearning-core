# CakePHP Migrations

This directory contains CakePHP migration files for database schema changes.

## Available Migrations

### 20261106000000_AddVocabBuilderTargetMode.php

Adds nullable `exercises.vbl_mode` for supported word/letter target variants.
Existing activities retain their defaults: Memory and Select Grid use letters;
other activities use words. Apply after the initial Vocab Builder migration,
before deploying the updated editor. Rollback removes only this new column.

### 20261105000000_AddVocabBuilderExercises.php

Adds the activity ID and opt-in review flag to `exercises`, plus letter text,
letter audio/image attachment IDs and Coloring line-art attachment IDs to
`exercise_options`. Existing card references, target roles and `text_option` are
reused. Existing exercises are unchanged and review generation defaults to off.
See [authoring and review](README.md#vocab-builder-authoring-and-review).

Run `./bin/cake migrations migrate` from `core/backend` **before deploying the
updated backend/frontend**. The reversible `change()` migration removes only its
new columns on rollback; export Vocab Builder content before rolling it back,
and roll back application code too. Pending migrations may include earlier
schema changes, so check `./bin/cake migrations status` first.

### 20250121000000_ComprehensiveDatabaseUpdates.php

This migration implements all the database changes defined in the reference SQL files under [`core/scripts/sql/`](../../scripts/sql/) — see [docs/scripts/sql/README.md](../scripts/sql/README.md) for a per-file index.

1. **add_coppa_fields** - Adds COPPA-related site settings for managing minor user access
2. **users_add_approximate_age** - Adds `approximate_age` column to users table for privacy compliance
3. **users_set_age_adults_students** - Populates approximate_age for eligible users (13+ or school users)
4. **school_roles_create_table** - Creates school_roles table with teacher, substitute, and student roles
5. **emailcontents_insert_parent_notification** - Adds parent notification email template for minor accounts
6. **roles_rename_student_to_user** - Renames existing 'student' role to 'user'
7. **roles_add_developer_and_student** - Adds new 'content developer' and 'student' roles
8. **users_school_users_to_student** - Updates school users to use the new student role
9. **sitesettings_insert_version_fields** - Adds app version tracking settings

Content migration between databases is documented in [docs/scripts/php/README.md](../scripts/php/README.md).

MySQL 8 charset standardization (`latin1` / `utf8` → `utf8mb4`) is a separate manual procedure — see [Database charset migration](../backend/database-charset-migration.md).

## Running Migrations

### Local database tasks in VS Code

In the multi-root platform workspace, choose **Terminal > Run Task**:

1. **eLearning: Check local database migration status**
2. **eLearning: Migrate local database (Vocab Builder)**

The migration task applies all pending migrations through `20261106000000`,
including any earlier pending migrations. Back up your local database first.
It does not run automatically when opening the workspace.

Both tasks use [migrate-local-backend.sh](../../scripts/migrate-local-backend.sh)
and your selected MAMP/XAMPP PHP, located using `ELEARNING_WWW_PATH` in the language
repository's `scripts/local-dev-vars.sh`. They use the **current default connection
in core/backend/config** (including environment overrides) and refuse database
hosts other than `localhost`, `127.0.0.1` or `::1`. They do not copy platform
configuration or sync the backend, so ensure your current backend configuration
points to the intended local database.

To run all pending migrations:

```bash
# From the backend directory
./bin/cake migrations migrate
```

To run a specific migration:

```bash
./bin/cake migrations migrate -t 20250121000000
```

To rollback the last migration:

```bash
./bin/cake migrations rollback
```

To check migration status:

```bash
./bin/cake migrations status
```

## Important Notes

- This migration includes both `up()` and `down()` methods for complete reversibility
- All INSERT operations use `ON DUPLICATE KEY UPDATE` to prevent conflicts on re-runs
- Column additions check for existence before adding to prevent errors
- The migration handles data transformation safely with proper NULL checks
- All changes maintain referential integrity and proper data types

## Safety Features

- Uses transactions implicitly through CakePHP's migration system
- Includes proper error handling for duplicate data
- Maintains backward compatibility through complete rollback functionality
- Preserves existing data while adding new features
