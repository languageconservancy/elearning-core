SELECT E.`id` AS exercise_id, E.exercise_type, O.card_type, C.lakota, C.english, A.file_name AS audio, I.file_name as image, CO.prompt_html, CO.response_html
FROM `exercises` E
INNER JOIN `exercise_custom_options` CO ON E.id = CO.exercise_id
INNER JOIN `exercise_options` O ON O.id = CO.exercise_option_id
LEFT JOIN `cards` C ON O.card_id = C.id
LEFT JOIN `files` A ON A.id = C.audio
LEFT JOIN `files` I ON I.id = C.image_id
WHERE E.exercise_type = 'multiple-choice'
	AND E.card_type = 'custom'
	AND E.responsetype = 'l'
	AND CO.response_html IS NOT NULL
ORDER BY `exercise_id` ASC