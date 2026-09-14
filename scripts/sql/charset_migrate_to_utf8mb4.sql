-- Migrate elearning database to utf8mb4
--
-- Full procedure: docs/backend/database-charset-migration.md
--
-- PREREQUISITES
-- 1. Full backup: mysqldump --single-transaction --routines --triggers DB_NAME > backup.sql
-- 2. Run charset_audit_metadata.sql and charset_audit_data.sql; save spot-check rows for comparison
-- 3. Run on a staging copy first; verify Lakota text in admin + learner UI
-- 4. Schedule a maintenance window — ALTER TABLE rewrites data (can lock large tables)
--
-- TARGET
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_0900_ai_ci   (MySQL 8 default; use utf8mb4_general_ci only if required)
--
-- POST-MIGRATION (application)
--   backend/config/app.php:
--     'encoding' => 'utf8mb4'   (both default and test datasources)
--   Re-export demo/elearning_demo_db.sql after validation
--
-- MOJIBAKE NOTE
-- If latin1 columns already contain UTF-8 byte sequences stored as latin1,
-- CONVERT TO CHARACTER SET utf8mb4 is usually correct (MySQL reinterpretes bytes).
-- If data was double-encoded or corrupted, fix those rows before bulk ALTER.
-- Use charset_audit_data.sql for mojibake spot-checks.
--
-- ZERO DATE NOTE (MySQL 8 error #1067)
-- ALTER TABLE ... CONVERT TO rebuilds the table and re-validates column defaults.
-- Defaults like DEFAULT '0000-00-00 00:00:00' are rejected under NO_ZERO_DATE.
-- Affected tables in demo dump: global_fires, review_queues (fixed inline below).
-- Find others first: run charset_audit_metadata.sql section 6.

SET NAMES utf8mb4;
SET @target_charset = 'utf8mb4';
SET @target_collation = 'utf8mb4_0900_ai_ci';

-- ---------------------------------------------------------------------------
-- Step 0: Database default (optional at start — see Step 5 at end)
-- ALTER DATABASE does NOT convert existing tables; it only sets the default
-- for new tables/columns (CakePHP migrations, manual CREATE TABLE, etc.).
-- ---------------------------------------------------------------------------
-- ALTER DATABASE `elearning_demo_db`
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------------
-- Phase 1 — Curriculum / Lakota content (highest priority)
-- Demo: utf8 tables + latin1 text on mixed-column tables
-- ---------------------------------------------------------------------------

ALTER TABLE `cards`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `reference_dictionary`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `inflections`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `wordlinks`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `contents`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `exercises`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `exercise_options`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `exercise_custom_options`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `lessons`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `lesson_frames`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `lesson_frame_blocks`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `levels`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `units`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `unit_details`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `card_groups`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `card_group_types`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `card_types`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `card_card_groups`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `card_units`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `activity_types`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `banned_words`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `files`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------------
-- Phase 2 — User-facing text, forums, email, settings
-- ---------------------------------------------------------------------------

ALTER TABLE `users`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `user_settings`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `user_images`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `emailcontents`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `sitesettings`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `forums`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `forum_posts`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `forum_flags`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `forum_flag_reasons`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `forum_post_viewers`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `schools`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `classrooms`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `grades`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `learningspeed`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `learningpaths`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `recording_audios`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `passwordreset`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------------
-- Phase 3 — Roles, progress, junction / mostly numeric + enum tables
-- Still convert for consistent joins and future-proofing
-- ---------------------------------------------------------------------------

ALTER TABLE `roles`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `school_roles`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `school_levels`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `school_users`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `classroom_users`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `classroom_level_units`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `level_units`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `path_levels`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `friends`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `bonus_points`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `global_fires`
  MODIFY `created` datetime DEFAULT NULL,
  MODIFY `modified` datetime DEFAULT NULL,
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `unit_fires`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `point_references`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `progress_timers`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `review_counters`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `review_queues`
  MODIFY `created` datetime DEFAULT NULL,
  MODIFY `modified` datetime DEFAULT NULL,
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `review_vars`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `user_activities`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `user_unit_activities`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `user_progress`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `user_points`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `user_level_badges`
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------------
-- Step 5: Set database default (run AFTER all tables converted)
-- Ensures new tables from migrations/scripts inherit utf8mb4_0900_ai_ci.
-- Edit database name if not elearning_demo_db.
-- ---------------------------------------------------------------------------
ALTER DATABASE `elearning_demo_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------------
-- Step 6: Verify (re-run charset_audit_metadata.sql)
-- ---------------------------------------------------------------------------
-- Section 1 should show: charset utf8mb4, collation utf8mb4_0900_ai_ci
-- Section 3 should return 0 rows (no tables left to convert)
-- Then run charset_audit_data.sql and spot-check Lakota in the app
-- Update backend/config/app.php -> 'encoding' => 'utf8mb4'
