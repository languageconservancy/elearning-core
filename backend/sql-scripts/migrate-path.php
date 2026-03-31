<?php
/**
 * migrate-path.php
 *
 * Migrates a complete learning path (with all levels, units, exercises, lessons,
 * cards, card groups, and media file records) from one database into another
 * database that shares the same schema.
 *
 * All primary-key IDs are remapped so the import never conflicts with rows that
 * already exist in the target.
 *
 * Usage (CLI):
 *   php migrate-path.php \
 *     --src-dsn="mysql:host=localhost;dbname=source_db;charset=utf8" \
 *     --src-user=root --src-pass=secret \
 *     --tgt-dsn="mysql:host=localhost;dbname=target_db;charset=utf8" \
 *     --tgt-user=root --tgt-pass=secret \
 *     --path-id=1 \
 *     [--dry-run]
 *
 * Or call migratePathById() directly from another PHP script.
 *
 * Notes on media (files table):
 *   The files rows are cloned so every FK resolves correctly in the target.
 *   The aws_link values are copied verbatim – if both databases share the same
 *   S3 bucket (or local asset folder) the actual binary assets are immediately
 *   available.  If they use separate buckets you must copy the objects in S3
 *   separately; this script only handles the DB rows.
 *   If the files are storage locally in the webroot/images/ directory, then you must copy the
 *   files to the target webroot/images/ directory.
 */

// ---------------------------------------------------------------------------
// Entry point
// ---------------------------------------------------------------------------

function migratePathById(
    string $srcDsn, string $srcUser, string $srcPass,
    string $tgtDsn, string $tgtUser, string $tgtPass,
    int    $pathId,
    bool   $dryRun = false
): array {
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    $src  = new PDO($srcDsn, $srcUser, $srcPass, $opts);
    $tgt  = new PDO($tgtDsn, $tgtUser, $tgtPass, $opts);

    log_info("Exporting path {$pathId} from source …");
    $data = exportPathData($pathId, $src);

    if ($dryRun) {
        log_info('Dry-run mode – nothing written to target.');
        return summarise($data);
    }

    log_info('Importing into target …');
    $tgt->beginTransaction();
    try {
        $idMap = importPathData($data, $tgt);
        $tgt->commit();
        log_info('Done. New path ID = ' . $idMap['path'][$pathId]);
        return $idMap;
    } catch (\Throwable $e) {
        $tgt->rollBack();
        throw $e;
    }
}

// ---------------------------------------------------------------------------
// Export – collect the entire tree from the source DB
// ---------------------------------------------------------------------------

function exportPathData(int $pathId, PDO $pdo): array
{
    // ---- learning path ----
    $path = fetchOne($pdo, 'SELECT * FROM learningpaths WHERE id = ?', [$pathId]);
    if (!$path) {
        throw new \RuntimeException("Learning path {$pathId} not found.");
    }

    // ---- path → levels ----
    $pathLevels = fetchAll($pdo, 'SELECT * FROM path_levels WHERE learningpath_id = ?', [$pathId]);
    $levelIds   = array_column($pathLevels, 'level_id');

    $levels = $levelIds
        ? fetchAll($pdo, 'SELECT * FROM levels WHERE id IN (' . placeholders($levelIds) . ')', $levelIds)
        : [];

    // ---- level → units (scoped to this path) ----
    $levelUnits = $levelIds
        ? fetchAll($pdo,
            'SELECT * FROM level_units WHERE learningpath_id = ? AND level_id IN (' . placeholders($levelIds) . ')',
            array_merge([$pathId], $levelIds))
        : [];

    $unitIds = array_values(array_unique(array_column($levelUnits, 'unit_id')));

    $units = $unitIds
        ? fetchAll($pdo, 'SELECT * FROM units WHERE id IN (' . placeholders($unitIds) . ')', $unitIds)
        : [];

    // ---- unit_details (activities inside each unit for this path) ----
    $unitDetails = $unitIds
        ? fetchAll($pdo,
            'SELECT * FROM unit_details WHERE learningpath_id = ? AND unit_id IN (' . placeholders($unitIds) . ')',
            array_merge([$pathId], $unitIds))
        : [];

    // ---- exercises ----
    $exerciseIds = array_values(array_filter(array_unique(array_column($unitDetails, 'exercise_id'))));

    $exercises = $exerciseIds
        ? fetchAll($pdo, 'SELECT * FROM exercises WHERE id IN (' . placeholders($exerciseIds) . ')', $exerciseIds)
        : [];

    $exerciseOptions = $exerciseIds
        ? fetchAll($pdo,
            'SELECT * FROM exercise_options WHERE exercise_id IN (' . placeholders($exerciseIds) . ')',
            $exerciseIds)
        : [];

    $exerciseOptionIds = array_values(array_unique(array_column($exerciseOptions, 'id')));

    $exerciseCustomOptions = $exerciseOptionIds
        ? fetchAll($pdo,
            'SELECT * FROM exercise_custom_options WHERE exercise_id IN (' . placeholders($exerciseIds) . ')',
            $exerciseIds)
        : [];

    // ---- card groups referenced by exercise_options ----
    $groupIds = array_values(array_filter(array_unique(array_column($exerciseOptions, 'group_id'))));

    $cardGroups = $groupIds
        ? fetchAll($pdo, 'SELECT * FROM card_groups WHERE id IN (' . placeholders($groupIds) . ')', $groupIds)
        : [];

    $cardCardGroups = $groupIds
        ? fetchAll($pdo,
            'SELECT * FROM card_card_groups WHERE card_group_id IN (' . placeholders($groupIds) . ')',
            $groupIds)
        : [];

    // ---- lessons ----
    $lessonIds = array_values(array_filter(array_unique(array_column($unitDetails, 'lesson_id'))));

    $lessons = $lessonIds
        ? fetchAll($pdo, 'SELECT * FROM lessons WHERE id IN (' . placeholders($lessonIds) . ')', $lessonIds)
        : [];

    $lessonFrames = $lessonIds
        ? fetchAll($pdo,
            'SELECT * FROM lesson_frames WHERE lesson_id IN (' . placeholders($lessonIds) . ')',
            $lessonIds)
        : [];

    $frameIds = array_values(array_unique(array_column($lessonFrames, 'id')));

    $lessonFrameBlocks = $frameIds
        ? fetchAll($pdo,
            'SELECT * FROM lesson_frame_blocks WHERE lesson_frame_id IN (' . placeholders($frameIds) . ')',
            $frameIds)
        : [];

    // ---- cards ----
    // Collect card IDs from exercise_options and lesson_frame_blocks.
    $cardIdsFromExOpts = array_merge(
        array_filter(array_column($exerciseOptions, 'card_id')),
        array_filter(array_column($exerciseOptions, 'responce_card_id'))
    );
    $cardIdsFromCcg    = array_filter(array_column($cardCardGroups, 'card_id'));
    $cardIdsFromBlocks = array_filter(array_column($lessonFrameBlocks, 'card_id'));

    $cardIds = array_values(array_unique(array_merge(
        $cardIdsFromExOpts,
        $cardIdsFromCcg,
        $cardIdsFromBlocks
    )));

    $cards = $cardIds
        ? fetchAll($pdo, 'SELECT * FROM cards WHERE id IN (' . placeholders($cardIds) . ')', $cardIds)
        : [];

    // ---- files ----
    // Collect every file ID referenced across all tables.
    $fileIds = collectFileIds(
        $path, $levels, $cards,
        $lessonFrames, $lessonFrameBlocks,
        $exerciseCustomOptions
    );

    $files = $fileIds
        ? fetchAll($pdo, 'SELECT * FROM files WHERE id IN (' . placeholders($fileIds) . ')', $fileIds)
        : [];

    return compact(
        'path', 'pathLevels', 'levels',
        'levelUnits', 'units', 'unitDetails',
        'exercises', 'exerciseOptions', 'exerciseOptionIds', 'exerciseCustomOptions',
        'cardGroups', 'cardCardGroups',
        'lessons', 'lessonFrames', 'lessonFrameBlocks',
        'cards', 'files'
    );
}

// ---------------------------------------------------------------------------
// Import – insert every row into the target DB in FK-safe order
// ---------------------------------------------------------------------------

function importPathData(array $data, PDO $pdo): array
{
    $idMap = [
        'file'        => [],
        'card'        => [],
        'cardGroup'   => [],
        'path'        => [],
        'level'       => [],
        'unit'        => [],
        'exercise'    => [],
        'exOption'    => [],
        'lesson'      => [],
        'frame'       => [],
    ];

    $now = date('Y-m-d H:i:s');

    // 1. files ---------------------------------------------------------------
    foreach ($data['files'] as $row) {
        $oldId = $row['id'];
        $pdo->prepare(
            'INSERT INTO files (upload_user_id, name, description, format, type, file_name, aws_link, created, modified)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $row['upload_user_id'], $row['name'], $row['description'],
            $row['format'], $row['type'], $row['file_name'], $row['aws_link'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
        $idMap['file'][$oldId] = (int)$pdo->lastInsertId();
        log_info("  file {$oldId} → " . $idMap['file'][$oldId]);
    }

    // 2. cards ---------------------------------------------------------------
    foreach ($data['cards'] as $row) {
        $oldId = $row['id'];
        // cards.audio stores a file ID as a plain string
        $audioFileId = ($row['audio'] !== null && $row['audio'] !== '')
            ? ($idMap['file'][(int)$row['audio']] ?? $row['audio'])
            : $row['audio'];

        $pdo->prepare(
            'INSERT INTO cards
               (inflection_id, reference_dictionary_id, image_id, video_id, audio,
                card_type_id, lakota, english, gender, include_review,
                alt_lakota, alt_english, created, modified, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $row['inflection_id'],
            $row['reference_dictionary_id'],
            remapId($row['image_id'], $idMap['file']),
            remapId($row['video_id'], $idMap['file']),
            $audioFileId,
            $row['card_type_id'],
            $row['lakota'], $row['english'], $row['gender'],
            $row['include_review'],
            $row['alt_lakota'], $row['alt_english'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
            $row['is_active'],
        ]);
        $idMap['card'][$oldId] = (int)$pdo->lastInsertId();
        log_info("  card {$oldId} → " . $idMap['card'][$oldId]);
    }

    // 3. card_groups ---------------------------------------------------------
    foreach ($data['cardGroups'] as $row) {
        $oldId = $row['id'];
        $pdo->prepare(
            'INSERT INTO card_groups (name, card_group_type_id, created, modified)
             VALUES (?, ?, ?, ?)'
        )->execute([
            $row['name'], $row['card_group_type_id'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
        $idMap['cardGroup'][$oldId] = (int)$pdo->lastInsertId();
        log_info("  card_group {$oldId} → " . $idMap['cardGroup'][$oldId]);
    }

    // card_card_groups -------------------------------------------------------
    foreach ($data['cardCardGroups'] as $row) {
        $pdo->prepare(
            'INSERT INTO card_card_groups (card_id, card_group_id, created, modified)
             VALUES (?, ?, ?, ?)'
        )->execute([
            remapId($row['card_id'],       $idMap['card']),
            remapId($row['card_group_id'], $idMap['cardGroup']),
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
    }

    // 4. learning path -------------------------------------------------------
    $pathRow  = $data['path'];
    $oldPathId = $pathRow['id'];
    $pdo->prepare(
        'INSERT INTO learningpaths
           (label, description, admin_access, user_access, image_id, owner_id, created, modified)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $pathRow['label'], $pathRow['description'],
        $pathRow['admin_access'], $pathRow['user_access'],
        remapId($pathRow['image_id'], $idMap['file']),
        $pathRow['owner_id'],
        $pathRow['created'] ?? $now, $pathRow['modified'] ?? $now,
    ]);
    $idMap['path'][$oldPathId] = (int)$pdo->lastInsertId();
    log_info("  learningpath {$oldPathId} → " . $idMap['path'][$oldPathId]);

    // 5. levels --------------------------------------------------------------
    foreach ($data['levels'] as $row) {
        $oldId = $row['id'];
        $pdo->prepare(
            'INSERT INTO levels (name, description, image_id, created, modified)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $row['name'], $row['description'],
            remapId($row['image_id'], $idMap['file']),
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
        $idMap['level'][$oldId] = (int)$pdo->lastInsertId();
        log_info("  level {$oldId} → " . $idMap['level'][$oldId]);
    }

    // 6. path_levels ---------------------------------------------------------
    foreach ($data['pathLevels'] as $row) {
        $pdo->prepare(
            'INSERT INTO path_levels (learningpath_id, level_id, sequence, created, modified)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $idMap['path'][$row['learningpath_id']],
            $idMap['level'][$row['level_id']],
            $row['sequence'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
    }

    // 7. units ---------------------------------------------------------------
    foreach ($data['units'] as $row) {
        $oldId = $row['id'];
        $pdo->prepare(
            'INSERT INTO units (name, description, type, created, modified)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $row['name'], $row['description'], $row['type'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
        $idMap['unit'][$oldId] = (int)$pdo->lastInsertId();
        log_info("  unit {$oldId} → " . $idMap['unit'][$oldId]);
    }

    // 8. level_units ---------------------------------------------------------
    foreach ($data['levelUnits'] as $row) {
        $pdo->prepare(
            'INSERT INTO level_units
               (learningpath_id, level_id, unit_id, optional, sequence, created, modified)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $idMap['path'][$row['learningpath_id']],
            $idMap['level'][$row['level_id']],
            $idMap['unit'][$row['unit_id']],
            $row['optional'],
            $row['sequence'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
    }

    // 9. exercises -----------------------------------------------------------
    foreach ($data['exercises'] as $row) {
        $oldId = $row['id'];
        $pdo->prepare(
            'INSERT INTO exercises
               (name, exercise_type, card_type, noofcard, instruction, bonus,
                promteresponsetype, promotetype, responsetype, created, modified)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $row['name'], $row['exercise_type'], $row['card_type'],
            $row['noofcard'], $row['instruction'], $row['bonus'],
            $row['promteresponsetype'], $row['promotetype'], $row['responsetype'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
        $idMap['exercise'][$oldId] = (int)$pdo->lastInsertId();
        log_info("  exercise {$oldId} → " . $idMap['exercise'][$oldId]);
    }

    // exercise_options -------------------------------------------------------
    foreach ($data['exerciseOptions'] as $row) {
        $oldId = $row['id'];
        $pdo->prepare(
            'INSERT INTO exercise_options
               (type, card_type, exercise_id, card_id, group_id, responce_card_id,
                prompt_preview_option, responce_preview_option, response_true_false,
                fill_in_the_blank_type, text_option, option_position)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $row['type'],
            $row['card_type'],
            $idMap['exercise'][$row['exercise_id']],
            remapId($row['card_id'],         $idMap['card']),
            remapId($row['group_id'],         $idMap['cardGroup']),
            remapId($row['responce_card_id'], $idMap['card']),
            $row['prompt_preview_option'],
            $row['responce_preview_option'],
            $row['response_true_false'],
            $row['fill_in_the_blank_type'],
            $row['text_option'],
            $row['option_position'],
        ]);
        $idMap['exOption'][$oldId] = (int)$pdo->lastInsertId();
    }

    // exercise_custom_options ------------------------------------------------
    foreach ($data['exerciseCustomOptions'] as $row) {
        $pdo->prepare(
            'INSERT INTO exercise_custom_options
               (exercise_id, exercise_option_id,
                prompt_audio_id, prompt_image_id, prompt_html,
                response_audio_id, response_image_id, response_html,
                created, modified)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $idMap['exercise'][$row['exercise_id']],
            remapId($row['exercise_option_id'], $idMap['exOption']),
            remapId($row['prompt_audio_id'],    $idMap['file']),
            remapId($row['prompt_image_id'],    $idMap['file']),
            $row['prompt_html'],
            remapId($row['response_audio_id'],  $idMap['file']),
            remapId($row['response_image_id'],  $idMap['file']),
            $row['response_html'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
    }

    // 10. lessons ------------------------------------------------------------
    foreach ($data['lessons'] as $row) {
        $oldId = $row['id'];
        $pdo->prepare(
            'INSERT INTO lessons (name, type, created, modified) VALUES (?, ?, ?, ?)'
        )->execute([
            $row['name'], $row['type'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
        $idMap['lesson'][$oldId] = (int)$pdo->lastInsertId();
        log_info("  lesson {$oldId} → " . $idMap['lesson'][$oldId]);
    }

    // lesson_frames ----------------------------------------------------------
    foreach ($data['lessonFrames'] as $row) {
        $oldId = $row['id'];
        $pdo->prepare(
            'INSERT INTO lesson_frames
               (lesson_id, audio_id, duration, name, number_of_block, frame_preview,
                frameorder, modified, created)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $idMap['lesson'][$row['lesson_id']],
            remapId($row['audio_id'], $idMap['file']),
            $row['duration'], $row['name'], $row['number_of_block'],
            $row['frame_preview'], $row['frameorder'],
            $row['modified'] ?? $now, $row['created'] ?? $now,
        ]);
        $idMap['frame'][$oldId] = (int)$pdo->lastInsertId();
    }

    // lesson_frame_blocks ----------------------------------------------------
    foreach ($data['lessonFrameBlocks'] as $row) {
        $pdo->prepare(
            'INSERT INTO lesson_frame_blocks
               (lesson_frame_id, card_id, audio_id, image_id, video_id, block_no,
                type, is_card_lakota, is_card_english, is_card_audio,
                is_card_video, is_card_image, custom_html, created, modified)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $idMap['frame'][$row['lesson_frame_id']],
            remapId($row['card_id'],  $idMap['card']),
            remapId($row['audio_id'], $idMap['file']),
            remapId($row['image_id'], $idMap['file']),
            remapId($row['video_id'], $idMap['file']),
            $row['block_no'], $row['type'],
            $row['is_card_lakota'], $row['is_card_english'], $row['is_card_audio'],
            $row['is_card_video'],  $row['is_card_image'],
            $row['custom_html'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
    }

    // 11. unit_details -------------------------------------------------------
    foreach ($data['unitDetails'] as $row) {
        $pdo->prepare(
            'INSERT INTO unit_details
               (learningpath_id, unit_id, lesson_id, exercise_id, sequence, created, modified)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $idMap['path'][$row['learningpath_id']],
            $idMap['unit'][$row['unit_id']],
            remapId($row['lesson_id'],   $idMap['lesson']),
            remapId($row['exercise_id'], $idMap['exercise']),
            $row['sequence'],
            $row['created'] ?? $now, $row['modified'] ?? $now,
        ]);
    }

    // 12. card_units ---------------------------------------------------------
    // Build from scratch: every card referenced in any unit detail for this path.
    $insertedCardUnits = [];
    foreach ($data['unitDetails'] as $row) {
        $newUnitId = $idMap['unit'][$row['unit_id']];

        $cardIdsForUnit = [];
        if ($row['lesson_id']) {
            // Collect cards from all frame blocks in this lesson
            foreach ($data['lessonFrameBlocks'] as $block) {
                $frameId = $block['lesson_frame_id'];
                foreach ($data['lessonFrames'] as $frame) {
                    if ($frame['id'] === $frameId && $frame['lesson_id'] === (int)$row['lesson_id']) {
                        if ($block['card_id']) {
                            $cardIdsForUnit[] = (int)$block['card_id'];
                        }
                    }
                }
            }
        }
        if ($row['exercise_id']) {
            foreach ($data['exerciseOptions'] as $opt) {
                if ($opt['exercise_id'] === (int)$row['exercise_id']) {
                    if ($opt['card_id'])         $cardIdsForUnit[] = (int)$opt['card_id'];
                    if ($opt['responce_card_id']) $cardIdsForUnit[] = (int)$opt['responce_card_id'];
                }
            }
            // Also include cards from card_groups belonging to this exercise
            foreach ($data['exerciseOptions'] as $opt) {
                if ($opt['exercise_id'] === (int)$row['exercise_id'] && $opt['group_id']) {
                    foreach ($data['cardCardGroups'] as $ccg) {
                        if ($ccg['card_group_id'] === (int)$opt['group_id']) {
                            $cardIdsForUnit[] = (int)$ccg['card_id'];
                        }
                    }
                }
            }
        }

        foreach (array_unique($cardIdsForUnit) as $oldCardId) {
            $newCardId = $idMap['card'][$oldCardId] ?? null;
            if (!$newCardId) continue;
            $key = "{$newCardId}:{$newUnitId}";
            if (isset($insertedCardUnits[$key])) continue;
            $insertedCardUnits[$key] = true;

            $pdo->prepare('INSERT INTO card_units (card_id, unit_id) VALUES (?, ?)')
                ->execute([$newCardId, $newUnitId]);
        }
    }

    return $idMap;
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function collectFileIds(array $path, array $levels, array $cards,
                        array $lessonFrames, array $lessonFrameBlocks,
                        array $exerciseCustomOptions): array
{
    $ids = [];

    // learningpath image
    if ($path['image_id']) $ids[] = (int)$path['image_id'];

    // level images
    foreach ($levels as $row) {
        if ($row['image_id']) $ids[] = (int)$row['image_id'];
    }

    // card media (image_id, video_id, audio is stored as string)
    foreach ($cards as $row) {
        if ($row['image_id']) $ids[] = (int)$row['image_id'];
        if ($row['video_id']) $ids[] = (int)$row['video_id'];
        if ($row['audio'] !== null && $row['audio'] !== '') $ids[] = (int)$row['audio'];
    }

    // lesson frame audio
    foreach ($lessonFrames as $row) {
        if ($row['audio_id']) $ids[] = (int)$row['audio_id'];
    }

    // lesson frame block media
    foreach ($lessonFrameBlocks as $row) {
        if ($row['audio_id']) $ids[] = (int)$row['audio_id'];
        if ($row['image_id']) $ids[] = (int)$row['image_id'];
        if ($row['video_id']) $ids[] = (int)$row['video_id'];
    }

    // exercise custom option media
    foreach ($exerciseCustomOptions as $row) {
        if ($row['prompt_audio_id'])   $ids[] = (int)$row['prompt_audio_id'];
        if ($row['prompt_image_id'])   $ids[] = (int)$row['prompt_image_id'];
        if ($row['response_audio_id']) $ids[] = (int)$row['response_audio_id'];
        if ($row['response_image_id']) $ids[] = (int)$row['response_image_id'];
    }

    return array_values(array_unique(array_filter($ids)));
}

/** Returns the remapped ID, or null if the original is null/absent. */
function remapId($oldId, array $map): ?int
{
    if ($oldId === null || $oldId === '') return null;
    $old = (int)$oldId;
    return $map[$old] ?? null;
}

function placeholders(array $items): string
{
    return implode(',', array_fill(0, count($items), '?'));
}

function fetchOne(PDO $pdo, string $sql, array $params = []): ?array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function fetchAll(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function summarise(array $data): array
{
    $counts = [];
    foreach ($data as $key => $value) {
        $counts[$key] = is_array($value) ? count($value) : 1;
    }
    log_info('Dry-run summary: ' . json_encode($counts));
    return $counts;
}

function log_info(string $msg): void
{
    echo '[' . date('H:i:s') . '] ' . $msg . PHP_EOL;
}

// ---------------------------------------------------------------------------
// CLI runner
// ---------------------------------------------------------------------------

if (PHP_SAPI === 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $opts = getopt('', [
        'src-dsn:', 'src-user:', 'src-pass:',
        'tgt-dsn:', 'tgt-user:', 'tgt-pass:',
        'path-id:', 'dry-run',
    ]);

    $required = ['src-dsn', 'src-user', 'tgt-dsn', 'tgt-user', 'path-id'];
    foreach ($required as $key) {
        if (empty($opts[$key])) {
            fwrite(STDERR, "Missing required option --{$key}\n");
            exit(1);
        }
    }

    try {
        $idMap = migratePathById(
            $opts['src-dsn'],  $opts['src-user'],  $opts['src-pass']  ?? '',
            $opts['tgt-dsn'],  $opts['tgt-user'],  $opts['tgt-pass']  ?? '',
            (int)$opts['path-id'],
            isset($opts['dry-run'])
        );
        if (!isset($opts['dry-run'])) {
            echo 'ID map (old → new):' . PHP_EOL;
            foreach ($idMap as $table => $map) {
                foreach ((array)$map as $old => $new) {
                    echo "  {$table}: {$old} → {$new}" . PHP_EOL;
                }
            }
        }
    } catch (\Throwable $e) {
        fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
