# SQL scripts (`core/scripts/sql/`)

Reference and ad-hoc SQL for schema changes, reporting, and content audits. For production schema updates, use [CakePHP migrations](../../backend/migrations.md) — these files informed those migrations or are run manually as needed.

| Script | Description |
|--------|-------------|
| `add_coppa_fields.sql` | Insert COPPA-related site settings (leaderboard, village, friends access for minors) |
| `users_add_approximate_age.sql` | Add `approximate_age` column to `users` for privacy compliance |
| `users_set_age_adults_students.sql` | Backfill `approximate_age` from date of birth for eligible users |
| `school_roles_create_table.sql` | Create `school_roles` table and seed teacher, substitute, and student roles |
| `emailcontents_insert_parent_notification.sql` | Insert parent notification email template for minor accounts |
| `roles_rename_student_to_user.sql` | Rename the existing `student` role to `user` |
| `roles_add_developer_and_student.sql` | Add `content developer` and `student` roles |
| `users_school_users_to_student.sql` | Update school users to the new student role |
| `sitesettings_insert_version_fields.sql` | Insert app version tracking site settings |
| `elearning_stats.sql` | Platform usage statistics (users, activities, classrooms, schools) |
| `exercise_custom_options_export_mcq_responses.sql` | Export multiple-choice custom exercise prompts and responses |
| `exercises_count_in_path_id.sql` | Count exercises in specific learning path IDs |
| `exercises_count_in_public_paths.sql` | Count exercises in public (`user_access = 1`) paths |
| `lessons_counts_in_path_id.sql` | Count lessons in specific learning path IDs |
| `lessons_count_in_public_paths.sql` | Count lessons in public paths |
| `units_count_in_path_id.sql` | Count units in specific learning path IDs |
| `units_count_in_public_paths.sql` | Count units in public paths |

Content migration between databases is handled by PHP scripts in [`core/scripts/php/`](../php/README.md) (`migrate-path.php`, `migrate-unit.php`).
