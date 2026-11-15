<?php

namespace App\Lib;

use Cake\Database\Driver\Sqlite;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Locator\LocatorInterface;
use Cake\ORM\Table;
use InvalidArgumentException;

/**
 * Structural edits for the learning hierarchy: creating records inside a parent, linking and
 * unlinking shared lessons/exercises, and copying units, lessons and exercises.
 *
 * Mirrors the side effects of the legacy LearningPath editor (sequence placement, discussion
 * forums and the derived CardUnits rows) so both editors leave the data in the same shape.
 */
class WorkspaceStructure
{
    /** child type => parent type it is created in; null means it may also stand alone. */
    public const CREATE_IN = [
        'levels' => ['paths'],
        'units' => ['levels'],
        'lessons' => ['units', null],
        'exercises' => ['units', null],
    ];
    public const COPYABLE = ['units', 'lessons', 'exercises'];
    private const DEFAULT_PAIRS = ['a-l', 'l-e', 'e-l', 'i-l', 'l-r', 'a-r'];

    private LocatorInterface $tables;
    private bool $copyCards = false;
    private bool $copyGroups = false;
    /** @var array<int, int> */
    private array $cardMap = [];
    /** @var array<int, int> */
    private array $groupMap = [];

    public function __construct(LocatorInterface $tables)
    {
        $this->tables = $tables;
    }

    /**
     * @param array{type?: string|null, id?: int|null, pathId?: int|null}|null $parent
     * @return array{type: string, id: int, linkId: int|null}
     */
    public function create(string $type, array $fields, ?array $parent): array
    {
        if (!isset(self::CREATE_IN[$type])) {
            throw new InvalidArgumentException('This record type cannot be created here.');
        }
        $parentType = $parent['type'] ?? null;
        if (!in_array($parentType, self::CREATE_IN[$type], true)) {
            throw new InvalidArgumentException('Choose where the new record belongs.');
        }
        $parentId = $parentType ? $this->id($parent['id'] ?? null) : null;
        $pathId = isset($parent['pathId']) && $parent['pathId'] ? $this->id($parent['pathId']) : null;
        $name = $this->text($fields['name'] ?? '', 'Enter a name.');
        $description = trim((string)($fields['description'] ?? ''));

        return $this->transaction(function () use ($type, $parentType, $parentId, $pathId, $name, $description, $fields) {
            $linkId = null;
            switch ($type) {
                case 'levels':
                    $this->tables->get('Learningpaths')->get($parentId);
                    $level = $this->save('Levels', ['name' => $name, 'description' => $description ?: null]);
                    $linkId = $this->insert('Pathlevel', ['learningpath_id' => $parentId], [
                        'level_id' => $level->id,
                    ]);
                    $this->forum(['path_id' => $parentId, 'level_id' => $level->id, 'title' => 'Lessons by Unit']);
                    return ['type' => $type, 'id' => (int)$level->id, 'linkId' => $linkId];
                case 'units':
                    $this->tables->get('Levels')->get($parentId);
                    $pathId = $this->pathForLevel($parentId, $pathId);
                    $unit = $this->save('Units', [
                        'name' => $name,
                        'description' => $this->text($description, 'Enter a unit description.'),
                    ]);
                    $linkId = $this->insert('LevelUnits', ['learningpath_id' => $pathId, 'level_id' => $parentId], [
                        'unit_id' => $unit->id,
                    ]);
                    $this->forum(['path_id' => $pathId, 'level_id' => $parentId, 'unit_id' => $unit->id, 'title' => $name]);
                    return ['type' => $type, 'id' => (int)$unit->id, 'linkId' => $linkId];
                case 'lessons':
                    $this->assertUnique('Lessons', $name, 'A lesson with this name already exists.');
                    $record = $this->save('Lessons', ['name' => $name]);
                    break;
                default:
                    $this->assertUnique('Exercises', $name, 'An exercise with this name already exists.');
                    $record = $this->save('Exercises', $this->exerciseSetup(
                        $name,
                        (string)($fields['exercise_type'] ?? ''),
                        (string)($fields['card_type'] ?? 'card')
                    ));
            }
            if ($parentType === 'units') {
                $linkId = $this->attach((int)$parentId, $pathId, $type, (int)$record->id, null);
            }
            return ['type' => $type, 'id' => (int)$record->id, 'linkId' => $linkId];
        });
    }

    /**
     * Adds existing lessons or exercises to the end of a unit.
     *
     * @return int[] New link ids, in submitted order.
     */
    public function link(int $unitId, ?int $pathId, $items): array
    {
        if (!is_array($items) || !array_is_list($items) || !$items || count($items) > 50) {
            throw new InvalidArgumentException('Choose between 1 and 50 lessons or exercises to add.');
        }
        $seen = [];
        $items = array_map(function ($item) use (&$seen) {
            $type = is_array($item) ? ($item['type'] ?? '') : '';
            if (!in_array($type, ['lessons', 'exercises'], true)) {
                throw new InvalidArgumentException('Only lessons and exercises can be added to a unit.');
            }
            $id = $this->id($item['id'] ?? null);
            if (isset($seen["$type:$id"])) {
                throw new InvalidArgumentException('The same activity was chosen twice.');
            }
            $seen["$type:$id"] = true;
            return [$type, $id];
        }, $items);

        return $this->transaction(function () use ($unitId, $pathId, $items) {
            $this->lock('Units', $unitId);
            $details = $this->tables->get('Unitdetails');
            $ids = [];
            foreach ($items as [$type, $id]) {
                $record = $this->tables->get($type === 'lessons' ? 'Lessons' : 'Exercises')->get($id);
                $key = $type === 'lessons' ? 'lesson_id' : 'exercise_id';
                if ($details->exists(['unit_id' => $unitId, $key => $id])) {
                    throw new InvalidArgumentException('"' . $record->name . '" is already in this unit.');
                }
                $ids[] = $this->attach($unitId, $pathId, $type, $id, null, false);
            }
            (new UnitCardContent($this->tables))->synchronizeUnit($unitId);
            return $ids;
        });
    }

    /** Removes an activity from a unit without deleting the lesson or exercise itself. */
    public function unlink(int $unitId, int $linkId): void
    {
        $this->transaction(function () use ($unitId, $linkId) {
            $this->lock('Units', $unitId);
            $details = $this->tables->get('Unitdetails');
            $link = $details->find()->where(['id' => $linkId, 'unit_id' => $unitId])->firstOrFail();
            $details->deleteOrFail($link);
            $this->resequence($details, ['unit_id' => $unitId], null, null);
            (new UnitCardContent($this->tables))->synchronizeUnit($unitId);
        });
    }

    /** Counts what a copy would include, so the author can choose what to duplicate. */
    public function plan(string $type, int $id): array
    {
        if (!in_array($type, self::COPYABLE, true)) {
            throw new InvalidArgumentException('This record type cannot be copied.');
        }
        $definition = LearningWorkspace::definition($type);
        $record = $this->tables->get($definition['table'])->get($id);
        $lessons = $type === 'lessons' ? [$id] : [];
        $exercises = $type === 'exercises' ? [$id] : [];
        if ($type === 'units') {
            foreach ($this->tables->get('Unitdetails')->find()->where(['unit_id' => $id]) as $detail) {
                if ($detail->lesson_id) {
                    $lessons[] = (int)$detail->lesson_id;
                } elseif ($detail->exercise_id) {
                    $exercises[] = (int)$detail->exercise_id;
                }
            }
        }
        [$cards, $groups, $frames, $items] = $this->references(array_unique($lessons), array_unique($exercises));
        $title = (string)$record->get($definition['title']);
        return [
            'type' => $type, 'id' => $id, 'title' => $title,
            'lessons' => count(array_unique($lessons)), 'exercises' => count(array_unique($exercises)),
            'frames' => $frames, 'items' => $items, 'cards' => count($cards), 'groups' => count($groups),
            'name' => $this->copyName($type, $title),
        ];
    }

    /**
     * @param array{name?: string, activities?: string, cards?: string, groups?: string,
     *     parent?: array{type?: string, id?: int, pathId?: int|null}|null} $options
     * @return array{type: string, id: int, linkId: int|null}
     */
    public function copy(string $type, int $id, array $options): array
    {
        if (!in_array($type, self::COPYABLE, true)) {
            throw new InvalidArgumentException('This record type cannot be copied.');
        }
        foreach (['activities', 'cards', 'groups'] as $key) {
            if (!in_array($options[$key] ?? 'link', ['link', 'copy'], true)) {
                throw new InvalidArgumentException('Choose whether to link or copy ' . $key . '.');
            }
        }
        $this->copyCards = ($options['cards'] ?? 'link') === 'copy';
        $this->copyGroups = ($options['groups'] ?? 'link') === 'copy';
        $this->cardMap = [];
        $this->groupMap = [];
        $parent = is_array($options['parent'] ?? null) ? $options['parent'] : null;
        $parentType = $parent['type'] ?? null;
        $parentId = $parentType ? $this->id($parent['id'] ?? null) : null;
        $pathId = !empty($parent['pathId']) ? $this->id($parent['pathId']) : null;
        $name = isset($options['name']) && trim((string)$options['name']) !== ''
            ? $this->text($options['name'], 'Enter a name for the copy.') : null;

        return $this->transaction(function () use ($type, $id, $options, $parentType, $parentId, $pathId, $name) {
            if ($type === 'units') {
                if ($parentType !== 'levels') {
                    throw new InvalidArgumentException('Open the unit inside its level to copy it.');
                }
                $result = $this->copyUnit($id, (int)$parentId, $pathId, $name, ($options['activities'] ?? 'link') === 'copy');
            } else {
                if ($parentType !== null && $parentType !== 'units') {
                    throw new InvalidArgumentException('Copies can only be placed in a unit.');
                }
                $copyId = $type === 'lessons' ? $this->copyLesson($id, $name) : $this->copyExercise($id, $name);
                $linkId = null;
                if ($parentType === 'units') {
                    $key = $type === 'lessons' ? 'lesson_id' : 'exercise_id';
                    $original = $this->tables->get('Unitdetails')->find()
                        ->where(['unit_id' => $parentId, $key => $id])->order(['sequence' => 'ASC'])->first();
                    $linkId = $this->attach((int)$parentId, $pathId, $type, $copyId, $original ? (int)$original->id : null);
                }
                $result = ['type' => $type, 'id' => $copyId, 'linkId' => $linkId];
            }
            $this->finishGroups();
            return $result;
        });
    }

    private function copyUnit(int $id, int $levelId, ?int $pathId, ?string $name, bool $copyActivities): array
    {
        $units = $this->tables->get('Units');
        $source = $units->get($id);
        $pathId = $this->pathForLevel($levelId, $pathId);
        $links = $this->tables->get('LevelUnits');
        $original = $links->find()->where([
            'unit_id' => $id, 'level_id' => $levelId, 'learningpath_id' => $pathId,
        ])->first();
        if (!$original) {
            throw new InvalidArgumentException('This unit is not in the selected level.');
        }
        $unit = $this->duplicate($units, $source, ['name' => $name ?? $this->copyName('units', (string)$source->name)]);
        $linkId = $this->insert(
            'LevelUnits',
            ['learningpath_id' => $pathId, 'level_id' => $levelId],
            ['unit_id' => $unit->id, 'optional' => $original->get('optional') ?? 0],
            (int)$original->id
        );
        $this->forum(['path_id' => $pathId, 'level_id' => $levelId, 'unit_id' => $unit->id, 'title' => $unit->name]);
        $details = $this->tables->get('Unitdetails');
        $sequence = 0;
        foreach ($details->find()->where(['unit_id' => $id])->order(['sequence' => 'ASC', 'id' => 'ASC']) as $detail) {
            $lessonId = $detail->lesson_id ? (int)$detail->lesson_id : null;
            $exerciseId = $detail->exercise_id ? (int)$detail->exercise_id : null;
            if (!$lessonId && !$exerciseId) {
                continue;
            }
            if ($copyActivities) {
                $lessonId = $lessonId ? $this->copyLesson($lessonId, null) : null;
                $exerciseId = $exerciseId ? $this->copyExercise($exerciseId, null) : null;
            }
            $this->save('Unitdetails', [
                'learningpath_id' => $pathId, 'unit_id' => $unit->id, 'lesson_id' => $lessonId,
                'exercise_id' => $exerciseId, 'sequence' => ++$sequence,
            ]);
        }
        (new UnitCardContent($this->tables))->synchronizeUnit((int)$unit->id);
        return ['type' => 'units', 'id' => (int)$unit->id, 'linkId' => $linkId];
    }

    private function copyLesson(int $id, ?string $name): int
    {
        $lessons = $this->tables->get('Lessons');
        $source = $lessons->get($id);
        if ($name !== null) {
            $this->assertUnique('Lessons', $name, 'A lesson with this name already exists.');
        }
        $lesson = $this->duplicate($lessons, $source, ['name' => $name ?? $this->copyName('lessons', (string)$source->name)]);
        $frames = $this->tables->get('LessonFrames');
        $blocks = $this->tables->get('LessonFrameBlocks');
        foreach ($frames->find()->where(['lesson_id' => $id])->order(['frameorder' => 'ASC', 'id' => 'ASC']) as $frame) {
            $copy = $this->duplicate($frames, $frame, ['lesson_id' => $lesson->id]);
            foreach ($blocks->find()->where(['lesson_frame_id' => $frame->id])->order(['block_no' => 'ASC', 'id' => 'ASC']) as $block) {
                $this->duplicate($blocks, $block, [
                    'lesson_frame_id' => $copy->id, 'card_id' => $this->card($block->card_id),
                ]);
            }
        }
        return (int)$lesson->id;
    }

    private function copyExercise(int $id, ?string $name): int
    {
        $exercises = $this->tables->get('Exercises');
        $source = $exercises->get($id);
        if ($name !== null) {
            $this->assertUnique('Exercises', $name, 'An exercise with this name already exists.');
        }
        $exercise = $this->duplicate($exercises, $source, [
            'name' => $name ?? $this->copyName('exercises', (string)$source->name),
        ]);
        $options = $this->tables->get('Exerciseoptions');
        $custom = $this->tables->get('ExerciseCustomOptions');
        foreach ($options->find()->where(['exercise_id' => $id])->order(['id' => 'ASC']) as $option) {
            $copy = $this->duplicate($options, $option, [
                'exercise_id' => $exercise->id,
                'card_id' => $this->card($option->card_id),
                'responce_card_id' => $this->card($option->responce_card_id),
                'group_id' => $this->group($option->group_id),
            ]);
            foreach ($custom->find()->where(['exercise_option_id' => $option->id]) as $row) {
                $this->duplicate($custom, $row, ['exercise_id' => $exercise->id, 'exercise_option_id' => $copy->id]);
            }
        }
        return (int)$exercise->id;
    }

    private function card($id): ?int
    {
        if (!$id) {
            return null;
        }
        $id = (int)$id;
        if (!$this->copyCards) {
            return $id;
        }
        if (!isset($this->cardMap[$id])) {
            $cards = $this->tables->get('Card');
            $this->cardMap[$id] = (int)$this->duplicate($cards, $cards->get($id), [])->id;
        }
        return $this->cardMap[$id];
    }

    private function group($id): ?int
    {
        if (!$id) {
            return null;
        }
        $id = (int)$id;
        if (!$this->copyGroups) {
            return $id;
        }
        if (!isset($this->groupMap[$id])) {
            $groups = $this->tables->get('Cardgroup');
            $source = $groups->get($id);
            $this->groupMap[$id] = (int)$this->duplicate($groups, $source, [
                'name' => $this->copyName('groups', (string)$source->name),
            ])->id;
        }
        return $this->groupMap[$id];
    }

    /** Copied groups get the original members, swapped for their duplicates when cards were copied too. */
    private function finishGroups(): void
    {
        $members = $this->tables->get('Cardcardgroup');
        foreach ($this->groupMap as $original => $copy) {
            $cardIds = [];
            foreach ($members->find()->where(['card_group_id' => $original])->order(['id' => 'ASC']) as $member) {
                $cardIds[] = $this->cardMap[(int)$member->card_id] ?? (int)$member->card_id;
            }
            foreach (array_unique($cardIds) as $cardId) {
                $members->saveOrFail($members->newEntity(['card_group_id' => $copy, 'card_id' => $cardId]));
            }
        }
    }

    /** @return array{0: int[], 1: int[], 2: int, 3: int} cards, groups, frame count, exercise item count */
    private function references(array $lessons, array $exercises): array
    {
        $cards = [];
        $groups = [];
        $frames = 0;
        $items = 0;
        if ($lessons) {
            $frameIds = $this->tables->get('LessonFrames')->find('list', ['valueField' => 'id'])
                ->where(['lesson_id IN' => $lessons])->toArray();
            $frames = count($frameIds);
            if ($frameIds) {
                foreach (
                    $this->tables->get('LessonFrameBlocks')->find()
                    ->where(['lesson_frame_id IN' => array_values($frameIds), 'card_id IS NOT' => null]) as $block
                ) {
                    $cards[] = (int)$block->card_id;
                }
            }
        }
        if ($exercises) {
            foreach ($this->tables->get('Exerciseoptions')->find()->where(['exercise_id IN' => $exercises]) as $option) {
                $items++;
                foreach ([$option->card_id, $option->responce_card_id] as $card) {
                    if ($card) {
                        $cards[] = (int)$card;
                    }
                }
                if ($option->group_id) {
                    $groups[] = (int)$option->group_id;
                }
            }
        }
        return [array_values(array_unique($cards)), array_values(array_unique($groups)), $frames, $items];
    }

    private function exerciseSetup(string $name, string $type, string $source): array
    {
        $rules = ExerciseContent::rules();
        if (!isset($rules[$type][$source])) {
            throw new InvalidArgumentException('Choose a supported exercise type and content source.');
        }
        $pairs = $rules[$type][$source]['pairs'];
        $pair = current(array_intersect(self::DEFAULT_PAIRS, $pairs)) ?: $pairs[0];
        [$prompt, $response] = explode('-', $pair);
        return [
            'name' => $name, 'instruction' => $name, 'bonus' => 0, 'exercise_type' => $type,
            'card_type' => $source, 'promteresponsetype' => $pair, 'promotetype' => $prompt,
            'responsetype' => $response,
        ];
    }

    /** Links an activity to a unit, after an existing link or at the end. */
    private function attach(int $unitId, ?int $pathId, string $type, int $id, ?int $after, bool $sync = true): int
    {
        $this->tables->get('Units')->get($unitId);
        $linkId = $this->insert('Unitdetails', ['unit_id' => $unitId], [
            'learningpath_id' => $this->pathForUnit($unitId, $pathId),
            $type === 'lessons' ? 'lesson_id' : 'exercise_id' => $id,
        ], $after);
        if ($sync) {
            (new UnitCardContent($this->tables))->synchronizeUnit($unitId);
        }
        return $linkId;
    }

    /** Inserts a sequenced link row after another row in the same list (or at the end) and renumbers the list. */
    private function insert(string $alias, array $scope, array $data, ?int $after = null): int
    {
        $table = $this->tables->get($alias);
        $entity = $this->save($alias, $scope + $data + ['sequence' => 0]);
        $this->resequence($table, $scope, (int)$entity->id, $after);
        return (int)$entity->id;
    }

    private function resequence(Table $table, array $scope, ?int $newId, ?int $after): void
    {
        $query = $table->find()->where($scope)->order(['sequence' => 'ASC', 'id' => 'ASC']);
        if (!($table->getConnection()->getDriver() instanceof Sqlite)) {
            $query->epilog('FOR UPDATE');
        }
        $rows = [];
        $new = null;
        foreach ($query as $row) {
            if ((int)$row->id === $newId) {
                $new = $row;
            } else {
                $rows[] = $row;
            }
        }
        if ($new) {
            $position = count($rows);
            foreach ($rows as $index => $row) {
                if ((int)$row->id === $after) {
                    $position = $index + 1;
                }
            }
            array_splice($rows, $position, 0, [$new]);
        }
        $changed = [];
        foreach ($rows as $index => $row) {
            if ((int)$row->get('sequence') !== $index + 1) {
                $row->set('sequence', $index + 1);
                $changed[] = $row;
            }
        }
        if ($changed) {
            $table->saveManyOrFail($changed);
        }
    }

    private function pathForLevel(int $levelId, ?int $pathId): int
    {
        $paths = array_values(array_unique(array_map('intval', $this->tables->get('Pathlevel')
            ->find('list', ['valueField' => 'learningpath_id'])->where(['level_id' => $levelId])->toArray())));
        if ($pathId && in_array($pathId, $paths, true)) {
            return $pathId;
        }
        if (!$pathId && count($paths) === 1) {
            return $paths[0];
        }
        throw new InvalidArgumentException('Open this level from its learning path first.');
    }

    private function pathForUnit(int $unitId, ?int $pathId): int
    {
        $paths = array_map('intval', $this->tables->get('LevelUnits')
            ->find('list', ['valueField' => 'learningpath_id'])->where(['unit_id' => $unitId])->toArray());
        if ($pathId && in_array($pathId, $paths, true)) {
            return $pathId;
        }
        $existing = $this->tables->get('Unitdetails')->find()->where(['unit_id' => $unitId])
            ->order(['id' => 'ASC'])->first();
        if ($existing && $existing->learningpath_id) {
            return (int)$existing->learningpath_id;
        }
        if ($paths) {
            return reset($paths);
        }
        throw new InvalidArgumentException('Add this unit to a level before adding activities.');
    }

    private function forum(array $data): void
    {
        $forums = $this->tables->get('Forums');
        $conditions = $data + ['subtitle' => $data['title']];
        foreach (['path_id', 'level_id', 'unit_id'] as $key) {
            $conditions[$key] = $conditions[$key] ?? null;
        }
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[$value === null ? "$key IS" : $key] = $value;
        }
        if (!$forums->exists($where)) {
            $forums->saveOrFail($forums->newEntity($conditions));
        }
    }

    private function copyName(string $type, string $title): string
    {
        $base = preg_replace('/ \(copy(?: \d+)?\)$/', '', $title) ?: $title;
        $alias = LearningWorkspace::definition($type)['table'];
        $field = LearningWorkspace::definition($type)['title'];
        $table = $this->tables->get($alias);
        for ($n = 1;; $n++) {
            $name = $base . ($n === 1 ? ' (copy)' : " (copy $n)");
            if (!$table->exists([$field => $name])) {
                return $name;
            }
        }
    }

    private function assertUnique(string $alias, string $name, string $message): void
    {
        if ($this->tables->get($alias)->exists(['name' => $name])) {
            throw new InvalidArgumentException($message);
        }
    }

    private function duplicate(Table $table, EntityInterface $source, array $overrides): EntityInterface
    {
        $data = [];
        foreach ($table->getSchema()->columns() as $column) {
            if (!in_array($column, ['id', 'created', 'modified'], true)) {
                $data[$column] = $source->get($column);
            }
        }
        $entity = $table->newEmptyEntity();
        $table->patchEntity($entity, $overrides + $data, ['accessibleFields' => ['*' => true, 'id' => false]]);
        return $table->saveOrFail($entity, ['associated' => []]);
    }

    private function save(string $alias, array $data): EntityInterface
    {
        $table = $this->tables->get($alias);
        $entity = $table->newEmptyEntity();
        $table->patchEntity($entity, $data, ['accessibleFields' => ['*' => true, 'id' => false]]);
        return $table->saveOrFail($entity, ['associated' => []]);
    }

    private function lock(string $alias, int $id): EntityInterface
    {
        $table = $this->tables->get($alias);
        $query = $table->find()->where([$table->aliasField('id') => $id]);
        if (!($table->getConnection()->getDriver() instanceof Sqlite)) {
            $query->epilog('FOR UPDATE');
        }
        return $query->firstOrFail();
    }

    private function transaction(callable $callback)
    {
        return $this->tables->get('Units')->getConnection()->transactional($callback);
    }

    private function id($value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new InvalidArgumentException('A valid record ID is required.');
        }
        return $id;
    }

    private function text($value, string $message): string
    {
        $value = is_scalar($value) ? trim((string)$value) : '';
        if ($value === '') {
            throw new InvalidArgumentException($message);
        }
        if (mb_strlen($value) > 255) {
            throw new InvalidArgumentException('Names can be at most 255 characters.');
        }
        return $value;
    }
}
