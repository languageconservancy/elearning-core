SELECT COUNT('exercise_id') FROM `unit_details`
JOIN `learningpaths` ON `unit_details`.`learningpath_id` = `learningpaths`.`id`
WHERE `learningpaths`.`id` IN (2, 4) AND `unit_details`.`exercise_id` IS NOT NULL