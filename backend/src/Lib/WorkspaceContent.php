<?php

namespace App\Lib;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Locator\LocatorInterface;
use InvalidArgumentException;

class WorkspaceContent
{
    public const EXERCISE_FIELDS = [
        'name', 'instruction', 'bonus', 'exercise_type', 'card_type', 'noofcard',
        'promteresponsetype', 'promotetype', 'responsetype', 'vbl_activity',
        'vbl_mode', 'vbl_variant', 'vbl_review_enabled',
    ];
    public const OPTION_FIELDS = [
        'type', 'card_type', 'card_id', 'group_id', 'responce_card_id',
        'prompt_preview_option', 'responce_preview_option', 'response_true_false',
        'fill_in_the_blank_type', 'text_option', 'option_position', 'vbl_letter',
        'vbl_letter_audio_id', 'vbl_letter_image_id', 'vbl_coloring_image_id', 'vbl_alignment',
    ];
    public const CUSTOM_FIELDS = [
        'prompt_html', 'response_html', 'prompt_audio_id', 'prompt_image_id',
        'response_audio_id', 'response_image_id',
    ];
    public const FRAME_FIELDS = ['name', 'audio_id', 'duration', 'frame_preview'];
    public const BLOCK_FIELDS = [
        'type', 'card_id', 'audio_id', 'image_id', 'video_id', 'custom_html',
        'is_card_lakota', 'is_card_english', 'is_card_audio', 'is_card_image', 'is_card_video',
    ];

    private LocatorInterface $tables;

    public function __construct(LocatorInterface $tables)
    {
        $this->tables = $tables;
    }

    public function exercise(int $id): array
    {
        $entity = $this->tables->get('Exercises')->get($id);
        $rows = $this->tables->get('Exerciseoptions')->find()
            ->where(['exercise_id' => $id])->order(['id' => 'ASC'])->toArray();
        $options = [];
        foreach ($rows as $row) {
            $custom = $this->tables->get('ExerciseCustomOptions')->find()
                ->where(['exercise_option_id' => $row->id, 'exercise_id' => $id])->first();
            $option = ['id' => $row->id, 'fields' => $this->values($row, self::OPTION_FIELDS),
                'custom' => $custom ? $this->values($custom, self::CUSTOM_FIELDS) : []];
            $options[] = $option;
        }
        $activities = VocabBuilder::ACTIVITIES;
        foreach ($activities as $key => &$activity) {
            $activity['modes'] = VocabBuilder::modes($key);
        }
        unset($activity);
        return [
            'id' => $id, 'fields' => $this->values($entity, self::EXERCISE_FIELDS), 'options' => $options,
            'revision' => $this->hash([$this->storedValues($entity), $options]),
            'schema' => array_values(array_intersect(
                self::OPTION_FIELDS,
                $this->tables->get('Exerciseoptions')->getSchema()->columns()
            )),
            'fieldSchema' => array_values(array_intersect(
                self::EXERCISE_FIELDS,
                $this->tables->get('Exercises')->getSchema()->columns()
            )),
            'types' => ExerciseContent::TYPES, 'modalities' => ExerciseContent::MODALITIES,
            'sources' => ExerciseContent::SOURCES, 'rules' => ExerciseContent::rules(),
            'activities' => $activities,
        ];
    }

    public function frames(int $lessonId, int $page = 1): array
    {
        $this->tables->get('Lessons')->get($lessonId);
        $query = $this->tables->get('LessonFrames')->find()->where(['lesson_id' => $lessonId]);
        $total = $query->count();
        $items = [];
        $size = WorkspaceOrder::PAGE_SIZE;
        foreach ($query->order(['frameorder' => 'ASC', 'id' => 'ASC'])->limit($size)->offset(($page - 1) * $size) as $frame) {
            $items[] = ['id' => $frame->id, 'name' => $frame->name, 'order' => $frame->frameorder,
                'blocks' => $frame->number_of_block, 'duration' => $frame->duration,
                'orientation' => $frame->frame_preview];
        }
        return ['items' => $items, 'total' => $total, 'page' => $page, 'pageSize' => $size,
            'order' => $total <= $size ? ['scope' => 'lesson-frames', 'parentId' => $lessonId, 'pathId' => null] : null];
    }

    public function frame(int $lessonId, int $frameId): array
    {
        $frame = $this->tables->get('LessonFrames')->find()->where([
            'id' => $frameId, 'lesson_id' => $lessonId,
        ])->firstOrFail();
        $blocks = $this->tables->get('LessonFrameBlocks')->find()->where(['lesson_frame_id' => $frameId])
            ->order(['block_no' => 'ASC', 'id' => 'ASC'])->toArray();
        return ['id' => $frameId, 'fields' => $this->values($frame, self::FRAME_FIELDS),
            'blocks' => array_map(fn($block) => ['id' => $block->id, 'fields' => $this->values($block, self::BLOCK_FIELDS)], $blocks),
            'revision' => $this->hash([array_diff_key($this->storedValues($frame), ['frameorder' => 0, 'modified' => 0]), array_map(fn($block) => $this->storedValues($block), $blocks)])];
    }

    public function saveExercise(int $id, array $data): array
    {
        $table = $this->tables->get('Exercises');
        return $table->getConnection()->transactional(function () use ($table, $id, $data) {
            $entity = $this->lock($table, $id);
            $current = $this->exercise($id);
            $this->assertRevision($current['revision'], $data['revision'] ?? '');
            $fields = $this->input($data['fields'] ?? [], array_intersect(self::EXERCISE_FIELDS, $current['fieldSchema']));
            $fields = $fields + $current['fields'];
            $options = $data['options'] ?? null;
            if (!is_array($options) || !array_is_list($options) || count($options) > 500) {
                throw new InvalidArgumentException('Submit up to 500 exercise content items.');
            }
            $options = array_map(function ($row) use ($current) {
                if (!is_array($row) || array_diff(array_keys($row), ['id', 'fields', 'custom'])) {
                    throw new InvalidArgumentException('Invalid exercise content item.');
                }
                $fields = $this->input($row['fields'] ?? [], $current['schema']);
                if (isset($fields['option_position']) && ($fields['option_position'] === '' || (string)$fields['option_position'] === '0')) {
                    $fields['option_position'] = null;
                }
                return $fields + [
                    'id' => $row['id'] ?? null, 'custom' => $this->input($row['custom'] ?? [], self::CUSTOM_FIELDS),
                ];
            }, $options);
            ExerciseContent::validateOptions($fields, $options);
            if ($fields['exercise_type'] === VocabBuilder::TYPE) {
                $builder = new VocabBuilder();
                $rows = array_map(fn($row) => $row + ['target' => ($row['card_type'] ?? '') === 'P'], $options);
                $options = array_map(
                    fn($normalized, $original) => $normalized + $original,
                    $builder->normalizeOptions($rows, $fields),
                    $options
                );
                $builder->payload($fields, $options);
                foreach ($options as &$option) {
                    foreach (array_diff(array_keys($option), $current['schema'], ['id', 'custom']) as $unsupported) {
                        if ($option[$unsupported] !== null && $option[$unsupported] !== '') {
                            throw new InvalidArgumentException("Apply the database migrations to edit $unsupported.");
                        }
                        unset($option[$unsupported]);
                    }
                }
                unset($option);
                if (
                    $fields['vbl_review_enabled'] && (
                    VocabBuilder::mode($fields) === 'letter' || !VocabBuilder::ACTIVITIES[$fields['vbl_activity']]['review']
                    )
                ) {
                    throw new InvalidArgumentException('Scored review is unavailable for this vocabulary activity.');
                }
            }
            $bonus = filter_var($fields['bonus'] ?? 0, FILTER_VALIDATE_FLOAT);
            if ($bonus === false || !is_finite($bonus) || $bonus < 0) {
                throw new InvalidArgumentException('Bonus points must be a non-negative number.');
            }
            $fields['bonus'] = (int)ceil($bonus);
            if ($fields['exercise_type'] === 'match-the-pair' && $fields['card_type'] !== 'card_group') {
                $fields['noofcard'] = count($options);
            }
            $table->patchEntity($entity, ['id' => $id] + $fields, ['fields' => array_merge(['id'], array_keys($fields))]);
            $table->saveOrFail($entity, ['associated' => []]);
            $optionsTable = $this->tables->get('Exerciseoptions');
            $keep = [];
            foreach ($options as $index => $row) {
                if (!is_array($row)) {
                    throw new InvalidArgumentException('Invalid exercise content item.');
                }
                $custom = $this->input($row['custom'] ?? [], self::CUSTOM_FIELDS);
                if (!array_filter($custom, fn($value) => $value !== null && $value !== '')) {
                    $custom = [];
                }
                $optionId = isset($row['id']) ? $this->positiveId($row['id']) : null;
                unset($row['id'], $row['custom']);
                $row = $this->input($row, $current['schema']);
                $this->validateReferences($row);
                $this->validateReferences($custom);
                if (($row['type'] ?? 'card') !== 'card' && ($row['type'] ?? '') !== 'group') {
                    throw new InvalidArgumentException('Content sources must be cards or groups.');
                }
                $option = $optionId ? $optionsTable->find()->where([
                    'id' => $optionId, 'exercise_id' => $id,
                ])->firstOrFail() : $optionsTable->newEmptyEntity();
                if (isset($keep[(int)$optionId]) && $optionId) {
                    throw new InvalidArgumentException('An exercise content item was submitted twice.');
                }
                $row['exercise_id'] = $id;
                $optionsTable->patchEntity($option, $row);
                $optionsTable->saveOrFail($option);
                $keep[(int)$option->id] = true;
                $customTable = $this->tables->get('ExerciseCustomOptions');
                $customEntity = $customTable->find()->where(['exercise_option_id' => $option->id])->first();
                if ($custom) {
                    $customEntity = $customEntity ?: $customTable->newEmptyEntity();
                    $customTable->patchEntity($customEntity, $custom + [
                        'exercise_id' => $id, 'exercise_option_id' => $option->id,
                    ]);
                    $customTable->saveOrFail($customEntity);
                } elseif ($customEntity) {
                    $customTable->deleteOrFail($customEntity);
                }
            }
            $removed = ['exercise_id' => $id, 'id NOT IN' => array_keys($keep)];
            $removedIds = $optionsTable->find()->select(['id'])->where($removed);
            $this->tables->get('ExerciseCustomOptions')->deleteAll(['exercise_option_id IN' => $removedIds]);
            $optionsTable->deleteAll($removed);
            (new UnitCardContent($this->tables))->synchronize('exercise_id', $id);
            return $this->exercise($id);
        });
    }

    public function saveFrame(int $lessonId, ?int $frameId, array $data): array
    {
        $table = $this->tables->get('LessonFrames');
        return $table->getConnection()->transactional(function () use ($table, $lessonId, $frameId, $data) {
            $this->lock($this->tables->get('Lessons'), $lessonId);
            $entity = $frameId ? $this->lock($table, $frameId) : $table->newEmptyEntity();
            if ($frameId && (int)$entity->lesson_id !== $lessonId) {
                throw new RecordNotFoundException('This frame does not belong to the selected lesson.');
            }
            if ($frameId) {
                $this->assertRevision($this->frame($lessonId, $frameId)['revision'], $data['revision'] ?? '');
            }
            $fields = $this->input($data['fields'] ?? [], self::FRAME_FIELDS);
            $blocks = $data['blocks'] ?? null;
            if (!is_array($blocks) || !array_is_list($blocks) || count($blocks) < 1 || count($blocks) > 3) {
                throw new InvalidArgumentException('A frame needs between one and three blocks.');
            }
            $blocks = array_map(function ($block) {
                if (!is_array($block) || array_diff(array_keys($block), ['id', 'fields'])) {
                    throw new InvalidArgumentException('Invalid lesson block.');
                }
                return $this->input($block['fields'] ?? [], self::BLOCK_FIELDS) + ['id' => $block['id'] ?? null];
            }, $blocks);
            LessonBlockContent::validate($blocks);
            if (trim((string)($fields['name'] ?? '')) === '') {
                throw new InvalidArgumentException('Enter a frame name.');
            }
            $duration = filter_var($fields['duration'] ?? 0, FILTER_VALIDATE_FLOAT);
            if ($duration === false || !is_finite($duration) || $duration < 0) {
                throw new InvalidArgumentException('Duration must be a non-negative number of seconds.');
            }
            if (!in_array($fields['frame_preview'] ?? '', ['portrait', 'landscape'], true)) {
                throw new InvalidArgumentException('Select portrait or landscape presentation.');
            }
            $this->validateReferences($fields);
            $fields['lesson_id'] = $lessonId;
            $fields['number_of_block'] = count($blocks);
            if (!$frameId) {
                $max = $table->find()->select(['maximum' => $table->find()->func()->max('frameorder')])
                    ->where(['lesson_id' => $lessonId])->first();
                $fields['frameorder'] = (int)$max->maximum + 1;
            } else {
                $fields['id'] = $frameId;
            }
            $table->patchEntity($entity, $fields);
            $table->saveOrFail($entity, ['associated' => []]);
            $blocksTable = $this->tables->get('LessonFrameBlocks');
            $keep = [];
            foreach ($blocks as $index => $block) {
                $blockId = isset($block['id']) ? $this->positiveId($block['id']) : null;
                if ($blockId && in_array($blockId, $keep, true)) {
                    throw new InvalidArgumentException('A lesson block was submitted twice.');
                }
                unset($block['id']);
                $block = $this->input($block, self::BLOCK_FIELDS);
                $this->validateReferences($block);
                $row = $blockId ? $blocksTable->find()->where([
                    'id' => $blockId, 'lesson_frame_id' => $entity->id,
                ])->firstOrFail() : $blocksTable->newEmptyEntity();
                $blocksTable->patchEntity($row, $block + ['lesson_frame_id' => $entity->id, 'block_no' => $index + 1]);
                $blocksTable->saveOrFail($row);
                $keep[] = $row->id;
            }
            $blocksTable->deleteAll(['lesson_frame_id' => $entity->id, 'id NOT IN' => $keep]);
            (new UnitCardContent($this->tables))->synchronize('lesson_id', $lessonId);
            return $this->frame($lessonId, (int)$entity->id);
        });
    }

    private function validateReferences(array $fields): void
    {
        foreach (['card_id', 'responce_card_id'] as $key) {
            if (isset($fields[$key]) && $fields[$key] !== '') {
                $this->tables->get('Card')->get($this->positiveId($fields[$key]));
            }
        }
        if (isset($fields['group_id']) && $fields['group_id'] !== '') {
            $this->tables->get('Cardgroup')->get($this->positiveId($fields['group_id']));
        }
        foreach ($fields as $key => $value) {
            if ($value === null || $value === '' || !preg_match('/(?:^|_)(audio|image|video)_id$/', $key, $match)) {
                continue;
            }
            $file = $this->tables->get('Files')->get($this->positiveId($value));
            if ($file->type !== $match[1]) {
                throw new InvalidArgumentException("The $key attachment must be {$match[1]}.");
            }
        }
    }

    private function positiveId($value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new InvalidArgumentException('Record and attachment IDs must be positive integers.');
        }
        return $id;
    }

    private function input($data, array $allowed): array
    {
        if (!is_array($data) || array_diff(array_keys($data), $allowed)) {
            throw new InvalidArgumentException('Unknown or read-only content fields were submitted.');
        }
        foreach ($data as $value) {
            if ($value !== null && !is_scalar($value)) {
                throw new InvalidArgumentException('Content field values must be scalar.');
            }
        }
        return $data;
    }

    private function values($entity, array $fields): array
    {
        return array_intersect_key($this->storedValues($entity), array_flip($fields));
    }

    private function storedValues($entity): array
    {
        return $entity->extract(array_diff($entity->getVisible(), $entity->getVirtual()));
    }

    private function lock($table, int $id)
    {
        $query = $table->find()->where([$table->aliasField('id') => $id]);
        if (!($table->getConnection()->getDriver() instanceof \Cake\Database\Driver\Sqlite)) {
            $query->epilog('FOR UPDATE');
        }
        return $query->firstOrFail();
    }

    private function hash(array $data): string
    {
        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function assertRevision(string $actual, string $submitted): void
    {
        if (!hash_equals($actual, $submitted)) {
            throw new \Cake\Http\Exception\ConflictException('This content changed elsewhere. Reload before saving.');
        }
    }
}
