# CakePHP Migrations

This directory contains CakePHP migration files for database schema changes.

## Available Migrations

### 20250121000000_ComprehensiveDatabaseUpdates.php

This migration implements all the database changes defined in the `core/backend/sql-scripts/` directory:

1. **add_coppa_fields** - Adds COPPA-related site settings for managing minor user access
2. **users_add_approximate_age** - Adds `approximate_age` column to users table for privacy compliance
3. **users_set_age_adults_students** - Populates approximate_age for eligible users (13+ or school users)
4. **school_roles_create_table** - Creates school_roles table with teacher, substitute, and student roles
5. **emailcontents_insert_parent_notification** - Adds parent notification email template for minor accounts
6. **roles_rename_student_to_user** - Renames existing 'student' role to 'user'
7. **roles_add_developer_and_student** - Adds new 'content developer' and 'student' roles
8. **users_school_users_to_student** - Updates school users to use the new student role
9. **sitesettings_insert_version_fields** - Adds app version tracking settings

## Running Migrations

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
