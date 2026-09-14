# demo

Demo assets and database file for testing frontend lessons and exercises consistently and in a maintainable way. The assets and database are version-controlled in `core` so that any eLearning platform project can use them. They should be updated only to add new test cases.

## Usage

- These are to be used nominally in a local environment
- The `elearning_demo_db.sql` database file should be imported into `phpMyAdmin`
- For MySQL 8, the demo dump may still use legacy `latin1` / `utf8` charsets — see [Database charset migration](../backend/database-charset-migration.md) to standardize on `utf8mb4`
- The webroot directory should be copied to your `www/backend/` directory or wherever your local server is. For MAMP it's in `/Applications/MAMP/htdocs/backend/`
- The `mamp/my.cnf` file is placed in `/Applications/MAMP/conf/` directory when using MAMP for your local Apache server, in order to set the SQL mode automatically.
