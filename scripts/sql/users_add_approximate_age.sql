ALTER TABLE `users`
ADD COLUMN `approximate_age` TINYINT unsigned DEFAULT NULL
COMMENT 'Approximate age to reduce sensitive data. Null if not updated.'
AFTER `dob`;