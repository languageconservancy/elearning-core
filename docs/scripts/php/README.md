# PHP scripts (`core/scripts/php/`)

Server-side utilities run against a deployed platform database. Scripts use different bootstrap styles — see each section below.

| Script | Description |
|--------|-------------|
| [`export-cards-to-rrc-csv.php`](export-cards-to-rrc-csv.md) | Export card data by ID to Record Tool CSV format |
| [`create-missing-usersettings.php`](../../scripts/php/create-missing-usersettings.php) | Backfill missing `user_settings` rows for users who have none |
| [`migrate-path.php`](../../scripts/php/migrate-path.php) | Copy a complete learning path (levels, units, exercises, lessons, cards, media) from one database to another with ID remapping |
| [`migrate-unit.php`](../../scripts/php/migrate-unit.php) | Copy a single unit and its exercises, lessons, and cards into the same database with new IDs |

## Content migration scripts

### `migrate-path.php`

Copies an entire learning path tree from a source database into a target database that shares the same schema. All primary keys are remapped so existing rows in the target are not overwritten.

`files` rows are cloned so foreign keys resolve in the target. `aws_link` values are copied as-is — if the target uses a different S3 bucket or local `webroot/images/` folder, copy the binary assets separately.

```bash
php core/scripts/php/migrate-path.php \
  --src-dsn="mysql:host=localhost;dbname=source_db;charset=utf8" \
  --src-user=root --src-pass=secret \
  --tgt-dsn="mysql:host=localhost;dbname=target_db;charset=utf8" \
  --tgt-user=root --tgt-pass=secret \
  --path-id=1 \
  [--dry-run]
```

Or call `migratePathById()` from another PHP script.

### `migrate-unit.php`

Exports one unit (with related exercises, lessons, lesson frames, and cards) and re-imports it with new auto-increment IDs. Intended for duplicating a unit within the same database — edit the DSN, credentials, and `$unitId` at the bottom of the script before running.

```bash
php core/scripts/php/migrate-unit.php
```

Unlike `migrate-path.php`, this script has no CLI argument parsing yet; configuration is inline in the file.

## User settings backfill

### `create-missing-usersettings.php`

Finds users in `users` with no matching row in `user_settings` and inserts the missing settings. For each user it sets:

- `profile_desc` — default intro text using the user's name
- `age_over_adult` — `1` if the user is 18 or older (from `dob`), otherwise `0`

Database credentials come from `backend/config/app.php` (not CakePHP bootstrap). The script uses mysqli and outputs HTML.

**Deployment:** copy or symlink this file to `{site-root}/info/` on the server so `../backend/config/app.php` resolves correctly. Typical layout:

```text
{site-root}/
├── backend/
│   └── config/app.php
└── info/
    └── create-missing-usersettings.php
```

**Dry run:** set `$modifyDb = false` at the top of the script to print INSERT statements without writing to the database.

```bash
# From site root, after placing the file in info/
php info/create-missing-usersettings.php
```

Or open `/info/create-missing-usersettings.php` in a browser on the deployed server.

## Card export

See [export-cards-to-rrc-csv.md](export-cards-to-rrc-csv.md) for usage, CSV columns, and customization. That script bootstraps CakePHP and must be run from `core/backend/`.
