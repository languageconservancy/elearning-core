SELECT COUNT('exercise_id') FROM `unit_details`
JOIN `learningpaths` ON `unit_details`.`learningpath_id` = `learningpaths`.`id`
WHERE `learningpaths`.`user_access` = 1 AND `unit_details`.`exercise_id` IS NOT NULL