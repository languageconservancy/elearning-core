SELECT COUNT('unit_id') FROM `level_units`
JOIN `learningpaths` ON `level_units`.`learningpath_id` = `learningpaths`.`id`
WHERE `learningpaths`.`user_access` = 1 AND `level_units`.`id` IS NOT NULL