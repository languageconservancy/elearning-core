SELECT COUNT('unit_id') FROM `level_units`
JOIN `learningpaths` ON `level_units`.`learningpath_id` = `learningpaths`.`id`
WHERE `learningpaths`.`id` = 4 AND `level_units`.`unit_id` IS NOT NULL