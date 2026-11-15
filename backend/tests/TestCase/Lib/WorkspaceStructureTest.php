<?php

namespace App\Test\TestCase\Lib;

use App\Lib\WorkspaceStructure;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Locator\TableLocator;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

class WorkspaceStructureTest extends TestCase
{
    private TableLocator $tables;
    private Connection $db;
    private WorkspaceStructure $structure;

    public function setUp(): void
    {
        parent::setUp();
        ConnectionManager::setConfig('structure_test', [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Sqlite', 'database' => ':memory:',
        ]);
        $this->db = ConnectionManager::get('structure_test');
        $stamp = ', created DATETIME, modified DATETIME';
        $schemas = [
            'learningpaths' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, label TEXT, description TEXT' . $stamp,
            'levels' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, description TEXT, image_id INTEGER' . $stamp,
            'units' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, description TEXT, type TEXT' . $stamp,
            'lessons' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, type TEXT' . $stamp,
            'exercises' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, instruction TEXT, bonus INTEGER, '
                . 'exercise_type TEXT, card_type TEXT, noofcard INTEGER, promteresponsetype TEXT, promotetype TEXT, '
                . 'responsetype TEXT' . $stamp,
            'path_levels' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, learningpath_id INTEGER, level_id INTEGER, sequence INTEGER' . $stamp,
            'level_units' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, learningpath_id INTEGER NOT NULL, level_id INTEGER, '
                . 'unit_id INTEGER, optional INTEGER DEFAULT 0, sequence INTEGER' . $stamp,
            'unit_details' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, learningpath_id INTEGER NOT NULL, unit_id INTEGER, '
                . 'lesson_id INTEGER, exercise_id INTEGER, sequence INTEGER' . $stamp,
            'forums' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, path_id INTEGER, level_id INTEGER, unit_id INTEGER, '
                . 'title TEXT, subtitle TEXT' . $stamp,
            'lesson_frames' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, lesson_id INTEGER, name TEXT, audio_id INTEGER, '
                . 'frameorder INTEGER' . $stamp,
            'lesson_frame_blocks' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, lesson_frame_id INTEGER, card_id INTEGER, '
                . 'type TEXT, block_no INTEGER, custom_html TEXT' . $stamp,
            'exercise_options' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, exercise_id INTEGER, card_id INTEGER, '
                . 'responce_card_id INTEGER, group_id INTEGER, type TEXT, card_type TEXT' . $stamp,
            'exercise_custom_options' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, exercise_id INTEGER, '
                . 'exercise_option_id INTEGER, prompt_html TEXT' . $stamp,
            'cards' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, lakota TEXT, english TEXT, audio TEXT' . $stamp,
            'card_groups' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, card_group_type_id INTEGER' . $stamp,
            'card_card_groups' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, card_group_id INTEGER, card_id INTEGER' . $stamp,
            'card_units' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, unit_id INTEGER, card_id INTEGER',
        ];
        foreach ($schemas as $table => $columns) {
            $this->db->execute("CREATE TABLE $table ($columns)");
        }
        foreach (
            [
            "INSERT INTO learningpaths (id, label, description) VALUES (1, 'Path', 'Path'), (2, 'Other', 'Other')",
            "INSERT INTO levels (id, name) VALUES (1, 'Level'), (2, 'Shared level')",
            'INSERT INTO path_levels (learningpath_id, level_id, sequence) VALUES (1, 1, 1), (1, 2, 2), (2, 2, 1)',
            "INSERT INTO units (id, name, description) VALUES (1, 'Unit', 'About'), (2, 'Next', 'About')",
            'INSERT INTO level_units (id, learningpath_id, level_id, unit_id, optional, sequence) '
                . 'VALUES (1, 1, 1, 1, 1, 1), (2, 1, 1, 2, 0, 2)',
            "INSERT INTO lessons (id, name) VALUES (1, 'Greetings'), (2, 'Loose')",
            "INSERT INTO exercises (id, name, exercise_type, card_type, promteresponsetype, bonus) "
                . "VALUES (1, 'Pairs', 'match-the-pair', 'card', 'l-e', 0), (2, 'Spare', 'multiple-choice', 'card', 'a-l', 0)",
            'INSERT INTO unit_details (id, learningpath_id, unit_id, lesson_id, exercise_id, sequence) '
                . 'VALUES (1, 1, 1, 1, NULL, 1), (2, 1, 1, NULL, 1, 2)',
            "INSERT INTO cards (id, lakota, english) VALUES (1, 'Han', 'Hello'), (2, 'Toksa', 'Bye'), (3, 'Wa', 'Snow')",
            "INSERT INTO card_groups (id, name, card_group_type_id) VALUES (1, 'Greetings', 1)",
            'INSERT INTO card_card_groups (card_group_id, card_id) VALUES (1, 1), (1, 3)',
            "INSERT INTO lesson_frames (id, lesson_id, name, frameorder) VALUES (1, 1, 'Intro', 1)",
            "INSERT INTO lesson_frame_blocks (lesson_frame_id, card_id, type, block_no) VALUES (1, 1, 'card', 1), (1, NULL, 'html', 2)",
            "INSERT INTO exercise_options (id, exercise_id, card_id, responce_card_id, group_id, type, card_type) "
                . "VALUES (1, 1, 1, 2, NULL, 'card', 'O'), (2, 1, NULL, NULL, 1, 'group', 'O')",
            "INSERT INTO exercise_custom_options (exercise_id, exercise_option_id, prompt_html) VALUES (1, 1, '<b>Hi</b>')",
            'INSERT INTO card_units (unit_id, card_id) VALUES (1, 1), (1, 2)',
            ] as $sql
        ) {
            $this->db->execute($sql);
        }
        $this->tables = new TableLocator();
        foreach (
            [
            'Learningpaths', 'Levels', 'Units', 'Lessons', 'Exercises', 'Pathlevel', 'LevelUnits', 'Unitdetails', 'Forums',
            'LessonFrames', 'Lessonframes', 'LessonFrameBlocks', 'Exerciseoptions', 'ExerciseCustomOptions', 'Card',
            'Cardgroup', 'Cardcardgroup', 'CardUnits',
            ] as $alias
        ) {
            $this->tables->setConfig($alias, ['connection' => $this->db]);
        }
        $this->structure = new WorkspaceStructure($this->tables);
    }

    public function tearDown(): void
    {
        $this->tables->clear();
        ConnectionManager::drop('structure_test');
        parent::tearDown();
    }

    private function rows(string $sql): array
    {
        return $this->db->execute($sql)->fetchAll('assoc');
    }

    private function value(string $sql)
    {
        return $this->db->execute($sql)->fetchColumn(0);
    }

    public function testCreatesLevelInPathWithForum(): void
    {
        $result = $this->structure->create('levels', ['name' => 'Advanced'], ['type' => 'paths', 'id' => 1]);

        $this->assertSame('levels', $result['type']);
        $this->assertSame([['learningpath_id' => 1, 'sequence' => 3]], $this->rows(
            'SELECT learningpath_id, sequence FROM path_levels WHERE level_id = ' . $result['id']
        ));
        $this->assertSame('Lessons by Unit', $this->value('SELECT title FROM forums WHERE level_id = ' . $result['id']));
    }

    public function testCreatesUnitResolvingPathAndRequiringDescription(): void
    {
        $result = $this->structure->create('units', ['name' => 'Food', 'description' => 'Eating'], [
            'type' => 'levels', 'id' => 1,
        ]);

        $this->assertSame([['learningpath_id' => 1, 'sequence' => 3]], $this->rows(
            'SELECT learningpath_id, sequence FROM level_units WHERE unit_id = ' . $result['id']
        ));
        $this->assertSame(1, (int)$this->value("SELECT COUNT(*) FROM forums WHERE unit_id = {$result['id']} AND title = 'Food'"));

        $this->expectException(InvalidArgumentException::class);
        $this->structure->create('units', ['name' => 'No description'], ['type' => 'levels', 'id' => 1]);
    }

    public function testUnitInSharedLevelNeedsItsPath(): void
    {
        try {
            $this->structure->create('units', ['name' => 'X', 'description' => 'Y'], ['type' => 'levels', 'id' => 2]);
            $this->fail('A level in two paths must not guess its path.');
        } catch (InvalidArgumentException $error) {
            $this->assertStringContainsString('learning path', $error->getMessage());
        }
        $result = $this->structure->create('units', ['name' => 'X', 'description' => 'Y'], [
            'type' => 'levels', 'id' => 2, 'pathId' => 2,
        ]);
        $this->assertSame(2, (int)$this->value('SELECT learningpath_id FROM level_units WHERE unit_id = ' . $result['id']));
    }

    public function testCreatesExerciseWithValidDefaultsInUnit(): void
    {
        $result = $this->structure->create('exercises', ['name' => 'Quiz', 'exercise_type' => 'multiple-choice'], [
            'type' => 'units', 'id' => 1,
        ]);

        $exercise = $this->rows('SELECT * FROM exercises WHERE id = ' . $result['id'])[0];
        $this->assertSame('a-l', $exercise['promteresponsetype']);
        $this->assertSame(['a', 'l', 'card'], [$exercise['promotetype'], $exercise['responsetype'], $exercise['card_type']]);
        $this->assertSame([['id' => $result['linkId'], 'sequence' => 3, 'learningpath_id' => 1]], $this->rows(
            'SELECT id, sequence, learningpath_id FROM unit_details WHERE exercise_id = ' . $result['id']
        ));

        $this->expectExceptionMessage('already exists');
        $this->structure->create('exercises', ['name' => 'Quiz', 'exercise_type' => 'multiple-choice'], null);
    }

    public function testRejectsUnsupportedCreation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->structure->create('exercises', ['name' => 'Bad', 'exercise_type' => 'nonsense'], null);
    }

    public function testLinksAndUnlinksActivitiesKeepingRecordsAndSyncingCards(): void
    {
        $ids = $this->structure->link(2, null, [['type' => 'lessons', 'id' => 1], ['type' => 'exercises', 'id' => 2]]);

        $this->assertSame([[1, null, 1], [null, 2, 2]], array_map(fn ($row) => [
            $row['lesson_id'] === null ? null : (int)$row['lesson_id'],
            $row['exercise_id'] === null ? null : (int)$row['exercise_id'], (int)$row['sequence'],
        ], $this->rows('SELECT lesson_id, exercise_id, sequence FROM unit_details WHERE unit_id = 2 ORDER BY sequence')));
        $this->assertSame([1], array_map('intval', array_column($this->rows('SELECT card_id FROM card_units WHERE unit_id = 2'), 'card_id')));

        try {
            $this->structure->link(2, null, [['type' => 'lessons', 'id' => 1]]);
            $this->fail('Duplicate links must be rejected.');
        } catch (InvalidArgumentException $error) {
            $this->assertStringContainsString('already in this unit', $error->getMessage());
        }

        $this->structure->unlink(2, $ids[0]);
        $this->structure->unlink(2, $ids[1]);
        $this->assertSame(0, (int)$this->value('SELECT COUNT(*) FROM unit_details WHERE unit_id = 2'));
        $this->assertSame(0, (int)$this->value('SELECT COUNT(*) FROM card_units WHERE unit_id = 2'));
        $this->assertSame(1, (int)$this->value('SELECT COUNT(*) FROM lessons WHERE id = 1'));
    }

    public function testPlanCountsUnitContents(): void
    {
        $plan = $this->structure->plan('units', 1);

        $this->assertSame(
            ['lessons' => 1, 'exercises' => 1, 'frames' => 1, 'items' => 2, 'cards' => 2, 'groups' => 1],
            array_intersect_key($plan, array_flip(['lessons', 'exercises', 'frames', 'items', 'cards', 'groups']))
        );
        $this->assertSame('Unit (copy)', $plan['name']);
    }

    public function testCopiesLessonAfterOriginalLinkingCards(): void
    {
        $result = $this->structure->copy('lessons', 1, ['parent' => ['type' => 'units', 'id' => 1]]);

        $this->assertSame('Greetings (copy)', $this->value('SELECT name FROM lessons WHERE id = ' . $result['id']));
        $this->assertSame([[1, 1], [$result['id'], 2], [null, 3]], array_map(fn ($row) => [
            $row['lesson_id'] === null ? null : (int)$row['lesson_id'], (int)$row['sequence'],
        ], $this->rows('SELECT lesson_id, sequence FROM unit_details WHERE unit_id = 1 ORDER BY sequence')));
        $this->assertEquals([1, null], array_column($this->rows(
            'SELECT b.card_id FROM lesson_frame_blocks b JOIN lesson_frames f ON f.id = b.lesson_frame_id '
            . 'WHERE f.lesson_id = ' . $result['id'] . ' ORDER BY b.block_no'
        ), 'card_id'));
        $this->assertSame(3, (int)$this->value('SELECT COUNT(*) FROM cards'));
        $this->assertSame('Greetings (copy 2)', $this->structure->plan('lessons', 1)['name']);
    }

    public function testCopiesExerciseDuplicatingCardsAndGroups(): void
    {
        $result = $this->structure->copy('exercises', 1, ['name' => 'Pairs B', 'cards' => 'copy', 'groups' => 'copy']);

        $options = $this->rows('SELECT id, card_id, responce_card_id, group_id FROM exercise_options WHERE exercise_id = '
            . $result['id'] . ' ORDER BY id');
        $this->assertCount(2, $options);
        $this->assertNotContains((int)$options[0]['card_id'], [1, 2]);
        $this->assertSame('Han', $this->value('SELECT lakota FROM cards WHERE id = ' . $options[0]['card_id']));
        $this->assertSame('Greetings (copy)', $this->value('SELECT name FROM card_groups WHERE id = ' . $options[1]['group_id']));
        $this->assertEquals([(int)$options[0]['card_id'], 3], array_map('intval', array_column($this->rows(
            'SELECT card_id FROM card_card_groups WHERE card_group_id = ' . $options[1]['group_id'] . ' ORDER BY id'
        ), 'card_id')), 'Copied groups use the duplicated card when it exists and keep other members.');
        $this->assertSame('<b>Hi</b>', $this->value('SELECT prompt_html FROM exercise_custom_options WHERE exercise_option_id = '
            . $options[0]['id']));
        $this->assertNull($result['linkId']);

        $this->expectExceptionMessage('already exists');
        $this->structure->copy('exercises', 1, ['name' => 'Pairs B']);
    }

    public function testCopiesUnitWithLinkedOrIndependentActivities(): void
    {
        $linked = $this->structure->copy('units', 1, ['parent' => ['type' => 'levels', 'id' => 1]]);

        $this->assertSame([[1, 1, 1], [$linked['id'], 1, 2], [2, 0, 3]], array_map(
            fn ($row) => array_map('intval', array_values($row)),
            $this->rows('SELECT unit_id, optional, sequence FROM level_units WHERE level_id = 1 ORDER BY sequence')
        ));
        $this->assertSame([[1, null], [null, 1]], array_map(fn ($row) => [
            $row['lesson_id'] === null ? null : (int)$row['lesson_id'], $row['exercise_id'] === null ? null : (int)$row['exercise_id'],
        ], $this->rows('SELECT lesson_id, exercise_id FROM unit_details WHERE unit_id = ' . $linked['id'] . ' ORDER BY sequence')));
        $this->assertSame(1, (int)$this->value('SELECT COUNT(*) FROM forums WHERE unit_id = ' . $linked['id']));
        $this->assertSame(1, (int)$this->value('SELECT COUNT(*) FROM card_units WHERE unit_id = ' . $linked['id']));

        $copied = $this->structure->copy('units', 1, ['activities' => 'copy', 'parent' => ['type' => 'levels', 'id' => 1]]);
        $this->assertSame('Unit (copy 2)', $this->value('SELECT name FROM units WHERE id = ' . $copied['id']));
        $lessonId = (int)$this->value('SELECT lesson_id FROM unit_details WHERE lesson_id IS NOT NULL AND unit_id = ' . $copied['id']);
        $this->assertNotSame(1, $lessonId);
        $this->assertSame('Greetings (copy)', $this->value('SELECT name FROM lessons WHERE id = ' . $lessonId));
    }

    public function testFailedCopyRollsBack(): void
    {
        $this->db->execute('DELETE FROM cards WHERE id = 2');
        try {
            $this->structure->copy('units', 1, ['activities' => 'copy', 'cards' => 'copy', 'parent' => ['type' => 'levels', 'id' => 1]]);
            $this->fail('Copying a missing card must fail.');
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $error) {
        }
        $this->assertSame(2, (int)$this->value('SELECT COUNT(*) FROM units'));
        $this->assertSame(2, (int)$this->value('SELECT COUNT(*) FROM lessons'));
        $this->assertSame(2, (int)$this->value('SELECT COUNT(*) FROM level_units'));
    }
}
