-- Charset / collation DATA spot-checks (Lakota text, mojibake)
-- Full procedure: docs/backend/database-charset-migration.md
--
-- Run this file ALONE in phpMyAdmin SQL tab on elearning_demo_db.
--
-- Do NOT combine with charset_audit_metadata.sql in one paste — phpMyAdmin can
-- lose the default database after information_schema queries, causing:
--   #1109 Unknown table 'CARDS' in information_schema
--
-- Edit the database name below if yours differs.

USE `elearning_demo_db`;

-- Sanity check — should return a row count, not error 1109
SELECT COUNT(*) AS cards_row_count FROM `elearning_demo_db`.`cards`;

-- ---------------------------------------------------------------------------
-- Non-ASCII text (multi-byte UTF-8 characters)
-- Uses LENGTH (bytes) vs CHAR_LENGTH (characters) — no REGEXP needed.
-- ---------------------------------------------------------------------------
SELECT 'cards' AS source, id, lakota, english
FROM `elearning_demo_db`.`cards`
WHERE CHAR_LENGTH(lakota) < LENGTH(lakota)
   OR CHAR_LENGTH(english) < LENGTH(english)
LIMIT 20;

SELECT 'reference_dictionary' AS source, id, lakota, english
FROM `elearning_demo_db`.`reference_dictionary`
WHERE CHAR_LENGTH(lakota) < LENGTH(lakota)
   OR CHAR_LENGTH(english) < LENGTH(english)
LIMIT 20;

SELECT 'exercises' AS source, id, LEFT(instruction, 80) AS instruction
FROM `elearning_demo_db`.`exercises`
WHERE CHAR_LENGTH(instruction) < LENGTH(instruction)
   OR CHAR_LENGTH(name) < LENGTH(name)
LIMIT 20;

SELECT 'forums' AS source, id, title
FROM `elearning_demo_db`.`forums`
WHERE CHAR_LENGTH(title) < LENGTH(title)
   OR CHAR_LENGTH(subtitle) < LENGTH(subtitle)
LIMIT 20;

-- ---------------------------------------------------------------------------
-- Mojibake heuristics (UTF-8 bytes mis-read as latin1)
-- Common symptom: sequences like Ã, Â, â€ when viewed in utf8 client.
-- ---------------------------------------------------------------------------
SELECT 'users.name' AS field, id, name
FROM `elearning_demo_db`.`users`
WHERE name LIKE '%Ã%'
   OR name LIKE '%Â%'
   OR name LIKE '%â€%'
   OR name LIKE '%ï¿½%'
LIMIT 20;

SELECT 'cards.lakota' AS field, id, lakota
FROM `elearning_demo_db`.`cards`
WHERE lakota LIKE '%Ã%'
   OR lakota LIKE '%Â%'
   OR lakota LIKE '%â€%'
   OR lakota LIKE '%ï¿½%'
LIMIT 20;

SELECT 'reference_dictionary.lakota' AS field, id, lakota
FROM `elearning_demo_db`.`reference_dictionary`
WHERE lakota LIKE '%Ã%'
   OR lakota LIKE '%Â%'
   OR lakota LIKE '%â€%'
   OR lakota LIKE '%ï¿½%'
LIMIT 20;
