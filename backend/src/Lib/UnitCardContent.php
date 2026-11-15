<?php

namespace App\Lib;

use Cake\ORM\Locator\LocatorInterface;

class UnitCardContent
{
    private LocatorInterface $tables;

    public function __construct(LocatorInterface $tables)
    {
        $this->tables = $tables;
    }

    public function synchronize(string $foreignKey, int $activityId): void
    {
        $details = $this->tables->get('Unitdetails');
        $units = array_unique($details->find('list', ['valueField' => 'unit_id'])
            ->where([$foreignKey => $activityId])->toArray());
        sort($units);
        foreach ($units as $unitId) {
            $this->synchronizeUnit((int)$unitId, false);
        }
    }

    /** Rebuilds the derived CardUnits rows for one unit from its lessons and exercises. */
    public function synchronizeUnit(int $unitId, bool $clearWhenEmpty = true): void
    {
        $details = $this->tables->get('Unitdetails');
        $query = $this->tables->get('Units')->find()->where(['id' => $unitId]);
        if (!($details->getConnection()->getDriver() instanceof \Cake\Database\Driver\Sqlite)) {
            $query->epilog('FOR UPDATE');
        }
        $query->firstOrFail();
        $cardIds = [];
        $activities = $details->find()->where(['unit_id' => $unitId])->contain([
            'Lessons.Lessonframes.LessonFrameBlocks', 'Exercises.Exerciseoptions',
        ]);
        foreach ($activities as $activity) {
            $this->collect($activity, $cardIds);
        }
        $cardIds = array_values(array_unique($cardIds));
        if (!$cardIds && !$clearWhenEmpty) {
            return;
        }
        $cards = $this->tables->get('CardUnits');
        $existing = $cards->find('list', ['valueField' => 'card_id'])->where(['unit_id' => $unitId])->toArray();
        $keep = [];
        foreach ($existing as $id => $cardId) {
            if (in_array($cardId, $cardIds, true) && !isset($keep[$cardId])) {
                $keep[$cardId] = $id;
            }
        }
        $condition = ['unit_id' => $unitId];
        if ($keep) {
            $condition['id NOT IN'] = array_values($keep);
        }
        $cards->deleteAll($condition);
        foreach (array_diff($cardIds, array_keys($keep)) as $cardId) {
            $cards->saveOrFail($cards->newEntity(['unit_id' => $unitId, 'card_id' => $cardId]));
        }
    }

    public function collect($activity, array &$cardIds): array
    {
        if ($activity->exercise_id && isset($activity->exercise->exerciseoptions)) {
            // Same per-type role rules the legacy editors have always used for CardUnits.
            $roles = [
                'multiple-choice' => ['P', 'R'], 'truefalse' => ['P', 'R'],
                'anagram' => ['O'], 'match-the-pair' => ['O'],
                'fill_in_the_blanks' => ['P'], 'recording' => ['P'],
                'vocab-builder' => ['P', 'R', 'O'],
            ];
            $type = $activity->exercise->exercise_type;
            foreach ($activity->exercise->exerciseoptions as $option) {
                if ($option->card_id && in_array($option->card_type, $roles[$type] ?? [], true)) {
                    $cardIds[] = (int)$option->card_id;
                }
            }
        }
        if ($activity->lesson_id && isset($activity->lesson->lessonframes)) {
            foreach ($activity->lesson->lessonframes as $frame) {
                foreach ($frame->lesson_frame_blocks as $block) {
                    if ($block->type === 'card' && $block->card_id) {
                        $cardIds[] = (int)$block->card_id;
                    }
                }
            }
        }
        return $cardIds;
    }
}
