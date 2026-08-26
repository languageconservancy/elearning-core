-- Charset / collation METADATA audit (sections 1-4, 7)
-- Full procedure: docs/backend/database-charset-migration.md
--
-- Safe to run from phpMyAdmin SQL tab on elearning_demo_db.
-- Queries information_schema only — no app table data.
--
-- For Lakota/mojibake spot-checks, run charset_audit_data.sql separately.

SET @schema_name = 'elearning_demo_db';

SELECT @schema_name AS auditing_database;

-- ---------------------------------------------------------------------------
-- 1. Database default
-- ---------------------------------------------------------------------------
SELECT
    SCHEMA_NAME,
    DEFAULT_CHARACTER_SET_NAME AS charset,
    DEFAULT_COLLATION_NAME AS collation
FROM information_schema.SCHEMATA
WHERE SCHEMA_NAME = @schema_name;

-- ---------------------------------------------------------------------------
-- 2. Table summary (grouped by charset/collation)
-- ---------------------------------------------------------------------------
SELECT
    t.TABLE_COLLATION,
    SUBSTRING_INDEX(t.TABLE_COLLATION, '_', 1) AS charset,
    COUNT(*) AS table_count,
    GROUP_CONCAT(t.TABLE_NAME ORDER BY t.TABLE_NAME SEPARATOR ', ') AS tables
FROM information_schema.TABLES t
WHERE t.TABLE_SCHEMA = @schema_name
  AND t.TABLE_TYPE = 'BASE TABLE'
GROUP BY t.TABLE_COLLATION
ORDER BY table_count DESC, t.TABLE_COLLATION;

-- ---------------------------------------------------------------------------
-- 3. Tables not yet on utf8mb4 (full list)
-- ---------------------------------------------------------------------------
SELECT
    t.TABLE_NAME,
    t.TABLE_COLLATION,
    CASE
        WHEN t.TABLE_COLLATION LIKE 'utf8mb4%' THEN 'ok'
        WHEN t.TABLE_COLLATION LIKE 'utf8%' THEN 'upgrade utf8mb3 -> utf8mb4'
        WHEN t.TABLE_COLLATION LIKE 'latin1%' THEN 'upgrade latin1 -> utf8mb4'
        ELSE 'review manually'
    END AS action
FROM information_schema.TABLES t
WHERE t.TABLE_SCHEMA = @schema_name
  AND t.TABLE_TYPE = 'BASE TABLE'
  AND t.TABLE_COLLATION NOT LIKE 'utf8mb4%'
ORDER BY action, t.TABLE_NAME;

-- ---------------------------------------------------------------------------
-- 4. Column-level mismatches
-- ---------------------------------------------------------------------------
SELECT
    c.TABLE_NAME,
    c.COLUMN_NAME,
    c.COLUMN_TYPE,
    c.CHARACTER_SET_NAME,
    c.COLLATION_NAME,
    t.TABLE_COLLATION AS table_collation
FROM information_schema.COLUMNS c
JOIN information_schema.TABLES t
  ON t.TABLE_SCHEMA = c.TABLE_SCHEMA
 AND t.TABLE_NAME = c.TABLE_NAME
WHERE c.TABLE_SCHEMA = @schema_name
  AND c.CHARACTER_SET_NAME IS NOT NULL
  AND (
      c.CHARACTER_SET_NAME <> 'utf8mb4'
      OR c.COLLATION_NAME NOT LIKE 'utf8mb4%'
  )
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION;

-- ---------------------------------------------------------------------------
-- 5. Index length risk (utf8mb4 uses up to 4 bytes per character)
-- ---------------------------------------------------------------------------
SELECT
    s.TABLE_NAME,
    s.INDEX_NAME,
    GROUP_CONCAT(s.COLUMN_NAME ORDER BY s.SEQ_IN_INDEX) AS indexed_columns,
    SUM(c.CHARACTER_MAXIMUM_LENGTH) AS summed_char_length
FROM information_schema.STATISTICS s
JOIN information_schema.COLUMNS c
  ON c.TABLE_SCHEMA = s.TABLE_SCHEMA
 AND c.TABLE_NAME = s.TABLE_NAME
 AND c.COLUMN_NAME = s.COLUMN_NAME
WHERE s.TABLE_SCHEMA = @schema_name
  AND c.DATA_TYPE IN ('varchar', 'char', 'text')
  AND c.CHARACTER_SET_NAME IS NOT NULL
GROUP BY s.TABLE_NAME, s.INDEX_NAME
HAVING summed_char_length >= 191
ORDER BY summed_char_length DESC, s.TABLE_NAME;

-- ---------------------------------------------------------------------------
-- 6. Zero-date column defaults (MySQL 8 rejects these during ALTER ... CONVERT)
-- Error: #1067 Invalid default value for 'created'
-- ---------------------------------------------------------------------------
SELECT
    c.TABLE_NAME,
    c.COLUMN_NAME,
    c.COLUMN_TYPE,
    c.IS_NULLABLE,
    c.COLUMN_DEFAULT
FROM information_schema.COLUMNS c
WHERE c.TABLE_SCHEMA = @schema_name
  AND c.COLUMN_DEFAULT LIKE '0000-%'
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION;
