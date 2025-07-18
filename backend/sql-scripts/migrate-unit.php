<?php

function exportUnitData($unitId, $pdo)
{
    // Fetch unit data
    $unitQuery = $pdo->prepare("SELECT * FROM units WHERE id = ?");
    $unitQuery->execute([$unitId]);
    $unit = $unitQuery->fetch(PDO::FETCH_ASSOC);

    if (empty($unit)) {
        throw new Exception("Unit with ID $unitId not found");
    }

    // Fetch related data
    $unitDetailsQuery = $pdo->prepare("SELECT * FROM unit_details WHERE unit_id = ?");
    $unitDetailsQuery->execute([$unitId]);
    $unitDetails = $unitDetailsQuery->fetchAll(PDO::FETCH_ASSOC);

    if (empty($unitDetails)) {
        throw new Exception("Unit details not found for unit with ID $unitId");
    }

    // Fetch related exercises
    $exerciseIds = array_column($unitDetails, 'exercise_id');
    if (!empty($exerciseIds)) {
        $in  = str_repeat('?,', count($exerciseIds) - 1) . '?';
        $exercisesQuery = $pdo->prepare("SELECT * FROM exercises WHERE id IN ($in)");
        $exercisesQuery->execute($exerciseIds);
        $exercises = $exercisesQuery->fetchAll(PDO::FETCH_ASSOC);

        // Fetch related exercise options and custom options
        $exerciseOptionIds = array_column($exercises, 'id');
        $exOptionsQuery = $pdo->prepare("SELECT * FROM exercise_options WHERE exercise_id IN ($in)");
        $exOptionsQuery->execute($exerciseOptionIds);
        $exerciseOptions = $exOptionsQuery->fetchAll(PDO::FETCH_ASSOC);

        $exCustomOptionsQuery = $pdo->prepare("SELECT * FROM exercise_custom_options WHERE exercise_id IN ($in)");
        $exCustomOptionsQuery->execute($exerciseOptionIds);
        $exerciseCustomOptions = $exCustomOptionsQuery->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $exercises = $exerciseOptions = $exerciseCustomOptions = [];
    }

    // Fetch related lessons and lesson frames
    $lessonIds = array_column($unitDetails, 'lesson_id');
    if (!empty($lessonIds)) {
        $lessonsQuery = $pdo->prepare("SELECT * FROM lessons WHERE id IN ($in)");
        $lessonsQuery->execute($lessonIds);
        $lessons = $lessonsQuery->fetchAll(PDO::FETCH_ASSOC);

        $lessonFrameQuery = $pdo->prepare("SELECT * FROM lesson_frames WHERE lesson_id IN ($in)");
        $lessonFrameQuery->execute($lessonIds);
        $lessonFrames = $lessonFrameQuery->fetchAll(PDO::FETCH_ASSOC);

        // Fetch related lesson frame blocks
        $lessonFrameIds = array_column($lessonFrames, 'id');
        $lessonFrameBlocksQuery = $pdo->prepare("SELECT * FROM lesson_frame_blocks WHERE lesson_frame_id IN ($in)");
        $lessonFrameBlocksQuery->execute($lessonFrameIds);
        $lessonFrameBlocks = $lessonFrameBlocksQuery->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $lessons = $lessonFrames = $lessonFrameBlocks = [];
    }

    // Fetch related cards
    $cardIds = array_merge(
        array_column($exerciseOptions, 'card_id'),
        array_column($exerciseOptions, 'responce_card_id'),
        array_column($lessonFrameBlocks, 'card_id')
    );
    if (!empty($cardIds)) {
        $cardQuery = $pdo->prepare("SELECT * FROM cards WHERE id IN ($in)");
        $cardQuery->execute($cardIds);
        $cards = $cardQuery->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $cards = [];
    }

    return [
        'unit' => $unit,
        'unitDetails' => $unitDetails,
        'exercises' => $exercises,
        'exerciseOptions' => $exerciseOptions,
        'exerciseCustomOptions' => $exerciseCustomOptions,
        'lessons' => $lessons,
        'lessonFrames' => $lessonFrames,
        'lessonFrameBlocks' => $lessonFrameBlocks,
        'cards' => $cards
    ];
}

function transformData($data)
{
    // Remove existing IDs
    foreach ($data as &$tableData) {
        foreach ($tableData as &$row) {
            unset($row['id']);
        }
    }
    return $data;
}

function importData($data, $pdo)
{
    $idMap = [];

    // Insert unit
    $unit = $data['unit'];
    $stmt = $pdo->prepare("INSERT INTO units (name, description, type, created, modified) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$unit['name'], $unit['description'], $unit['type'], $unit['created'], $unit['modified']]);
    $idMap['unit'][$unit['id']] = $pdo->lastInsertId();

    // Insert unit details
    foreach ($data['unitDetails'] as $unitDetail) {
        $stmt = $pdo->prepare("INSERT INTO unit_details (unit_id, lesson_id, exercise_id) VALUES (?, ?, ?)");
        $stmt->execute([
            $idMap['unit'][$unitDetail['unit_id']],
            $unitDetail['lesson_id'] ? $idMap['lesson'][$unitDetail['lesson_id']] : null,
            $unitDetail['exercise_id'] ? $idMap['exercise'][$unitDetail['exercise_id']] : null
        ]);
    }

    // Insert exercises and related data
    foreach ($data['exercises'] as $exercise) {
        $stmt = $pdo->prepare("INSERT INTO exercises (name, exercise_type, card_type, noofcard, instruction, bonus, promteresponsetype, promotetype, responsetype, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$exercise['name'], $exercise['exercise_type'], $exercise['card_type'], $exercise['noofcard'], $exercise['instruction'], $exercise['bonus'], $exercise['promteresponsetype'], $exercise['promotetype'], $exercise['responsetype'], $exercise['created'], $exercise['modified']]);
        $idMap['exercise'][$exercise['id']] = $pdo->lastInsertId();
    }

    foreach ($data['exerciseOptions'] as $exerciseOption) {
        $stmt = $pdo->prepare("INSERT INTO exercise_options (type, card_type, exercise_id, card_id, group_id, responce_card_id, prompt_preview_option, responce_preview_option, response_true_false, fill_in_the_blank_type, text_option, option_position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$exerciseOption['type'], $exerciseOption['card_type'], $idMap['exercise'][$exerciseOption['exercise_id']], $exerciseOption['card_id'], $exerciseOption['group_id'], $exerciseOption['responce_card_id'], $exerciseOption['prompt_preview_option'], $exerciseOption['responce_preview_option'], $exerciseOption['response_true_false'], $exerciseOption['fill_in_the_blank_type'], $exerciseOption['text_option'], $exerciseOption['option_position']]);
    }

    foreach ($data['exerciseCustomOptions'] as $exerciseCustomOption) {
        $stmt = $pdo->prepare("INSERT INTO exercise_custom_options (exercise_id, exercise_option_id, prompt_audio_id, prompt_image_id, prompt_html, response_audio_id, response_image_id, response_html, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$idMap['exercise'][$exerciseCustomOption['exercise_id']], $exerciseCustomOption['exercise_option_id'], $exerciseCustomOption['prompt_audio_id'], $exerciseCustomOption['prompt_image_id'], $exerciseCustomOption['prompt_html'], $exerciseCustomOption['response_audio_id'], $exerciseCustomOption['response_image_id'], $exerciseCustomOption['response_html'], $exerciseCustomOption['created'], $exerciseCustomOption['modified']]);
    }

    // Insert lessons and lesson frames
    foreach ($data['lessons'] as $lesson) {
        $stmt = $pdo->prepare("INSERT INTO lessons (name, type, created, modified) VALUES (?, ?, ?, ?)");
        $stmt->execute([$lesson['name'], $lesson['type'], $lesson['created'], $lesson['modified']]);
        $idMap['lesson'][$lesson['id']] = $pdo->lastInsertId();
    }

    foreach ($data['lessonFrames'] as $lessonFrame) {
        $stmt = $pdo->prepare("INSERT INTO lesson_frames (lesson_id, audio_id, duration, name, number_of_block, frame_preview, frameorder, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$idMap['lesson'][$lessonFrame['lesson_id']], $lessonFrame['audio_id'], $lessonFrame['duration'], $lessonFrame['name'], $lessonFrame['number_of_block'], $lessonFrame['frame_preview'], $lessonFrame['frameorder'], $lessonFrame['created'], $lessonFrame['modified']]);
        $idMap['lessonFrame'][$lessonFrame['id']] = $pdo->lastInsertId();
    }

    // Insert lesson frame blocks
    foreach ($data['lessonFrameBlocks'] as $lessonFrameBlock) {
        $stmt = $pdo->prepare("INSERT INTO lesson_frame_blocks (lesson_frame_id, card_id, audio_id, image_id, video_id, block_no, type, is_card_lakota, is_card_english, is_card_audio, is_card_video, is_card_image, custom_html, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $idMap['lessonFrame'][$lessonFrameBlock['lesson_frame_id']], $lessonFrameBlock['card_id'], $lessonFrameBlock['audio_id'], $lessonFrameBlock['image_id'], $lessonFrameBlock['video_id'], $lessonFrameBlock['block_no'], $lessonFrameBlock['type'], $lessonFrameBlock['is_card_lakota'], $lessonFrameBlock['is_card_english'], $lessonFrameBlock['is_card_audio'], $lessonFrameBlock['is_card_video'], $lessonFrameBlock['is_card_image'], $lessonFrameBlock['custom_html'], $lessonFrameBlock['created'], $lessonFrameBlock['modified']
        ]);
    }

    // Insert cards
    foreach ($data['cards'] as $card) {
        $stmt = $pdo->prepare("INSERT INTO cards (inflection_id, reference_dictionary_id, image_id, video_id, audio, card_type_id, lakota, english, gender, include_review, alt_lakota, alt_english, created, modified, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$card['inflection_id'], $card['reference_dictionary_id'], $card['image_id'], $card['video_id'], $card['audio'], $card['card_type_id'], $card['lakota'], $card['english'], $card['gender'], $card['include_review'], $card['alt_lakota'], $card['alt_english'], $card['created'], $card['modified'], $card['is_active']]);
        $idMap['card'][$card['id']] = $pdo->lastInsertId();
    }

    return $idMap;
}

function migrateUnit($dsn, $username, $password, $options, $unitId)
{
    try {
        $sourcePdo = new PDO($dsn, $username, $password, $options);
        $targetPdo = new PDO($dsn, $username, $password, $options);

        // Export data from source database
        $unitData = exportUnitData($unitId, $sourcePdo);

        // Transform data
        $transformedData = transformData($unitData);

        // Import data into target database
        $idMap = importData($transformedData, $targetPdo);

        print_r($idMap); // See the mapping of old IDs to new IDs
    } catch (\PDOException $e) {
        // Handle any database-related errors
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    } catch (Exception $e) {
        // Handle other exceptions
        echo $e->getMessage();
    }
}

migrateUnit('mysql:host=localhost;dbname=source_db', 'source_user', 'source_password', [], 1);

?>