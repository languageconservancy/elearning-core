<?php

namespace App\Lib;

use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * Explicit public editing contract. Structural edits stay in the legacy workflows
 * until their derived-data updates have been extracted into shared services.
 */
class LearningWorkspace
{
    public const TYPES = [
        'paths' => [
            'table' => 'Learningpaths', 'label' => 'Learning paths', 'title' => 'label',
            'fields' => ['label', 'description'],
            'required' => ['label', 'description'],
            'media' => ['image_id' => 'image'],
            'editor' => ['LearningPath', 'managePaths'],
            'create' => true,
        ],
        'levels' => [
            'table' => 'Levels', 'label' => 'Levels', 'title' => 'name',
            'fields' => ['name'], 'required' => ['name'],
            'media' => ['image_id' => 'image'], 'create' => false,
        ],
        'units' => [
            'table' => 'Units', 'label' => 'Units', 'title' => 'name',
            'fields' => ['name', 'description'], 'required' => ['name', 'description'],
            'media' => [], 'create' => false,
        ],
        'lessons' => [
            'table' => 'Lessons', 'label' => 'Lessons', 'title' => 'name',
            'fields' => ['name'], 'required' => ['name'], 'media' => [],
            'editor' => ['Lessons', 'manageLesson'], 'create' => true,
        ],
        'exercises' => [
            'table' => 'Exercises', 'label' => 'Exercises', 'title' => 'name',
            'fields' => ['name'], 'required' => ['name'], 'media' => [],
            'editor' => ['Exercises', 'manageExercises'], 'create' => false,
        ],
        'cards' => [
            'table' => 'Card', 'label' => 'Cards', 'title' => 'lakota',
            'fields' => ['lakota', 'english', 'alt_lakota', 'alt_english', 'card_type_id'],
            'required' => ['lakota', 'english', 'card_type_id'],
            'references' => ['card_type_id' => 'card-types'],
            'media' => ['image_id' => 'image', 'audio' => 'audio', 'video_id' => 'video'],
            'editor' => ['Cards', 'edit'], 'create' => true,
        ],
        'groups' => [
            'table' => 'Cardgroup', 'label' => 'Card groups', 'title' => 'name',
            'fields' => ['name', 'card_group_type_id'], 'required' => ['name', 'card_group_type_id'], 'media' => [],
            'references' => ['card_group_type_id' => 'group-types'],
            'editor' => ['CardGroups', 'edit'], 'create' => true,
        ],
    ];

    // [related type, junction table, source foreign key, target foreign key]
    public const RELATIONS = [
        'paths' => [
            ['levels', 'Pathlevel', 'learningpath_id', 'level_id'],
        ],
        'levels' => [
            ['paths', 'Pathlevel', 'level_id', 'learningpath_id'],
            ['units', 'LevelUnits', 'level_id', 'unit_id'],
        ],
        'units' => [
            ['levels', 'LevelUnits', 'unit_id', 'level_id'],
            ['lessons', 'Unitdetails', 'unit_id', 'lesson_id'],
            ['exercises', 'Unitdetails', 'unit_id', 'exercise_id'],
        ],
        'lessons' => [
            ['units', 'Unitdetails', 'lesson_id', 'unit_id'],
            ['cards', 'LessonFrameBlocks', 'lesson_frame_id', 'card_id'],
        ],
        'exercises' => [
            ['units', 'Unitdetails', 'exercise_id', 'unit_id'],
            ['cards', 'Exerciseoptions', 'exercise_id', 'card_id'],
        ],
        'cards' => [
            ['groups', 'Cardcardgroup', 'card_id', 'card_group_id'],
            ['exercises', 'Exerciseoptions', 'card_id', 'exercise_id'],
            ['lessons', 'LessonFrameBlocks', 'card_id', 'lesson_frame_id'],
        ],
        'groups' => [
            ['cards', 'Cardcardgroup', 'card_group_id', 'card_id'],
        ],
    ];

    public static function definition(string $type): array
    {
        if (!isset(self::TYPES[$type])) {
            throw new NotFoundException('Unknown learning record type.');
        }

        return self::TYPES[$type];
    }

    public static function revision(EntityInterface $entity): string
    {
        $data = $entity->toArray();
        ksort($data);

        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
    }
}
