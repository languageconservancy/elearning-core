<?php

namespace App\Test\TestCase\Controller;

use App\Controller\Admin\WorkspaceController;
use App\Lib\WorkspacePresentation;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\Locator\LocatorInterface;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

class IsolatedWorkspaceController extends WorkspaceController
{
    public function initialize(): void
    {
    }

    protected function getAuthUser(): ?object
    {
        return (object)['id' => 1];
    }
}

class WorkspaceControllerTest extends TestCase
{
    private TableLocator $tables;
    private LocatorInterface $originalTables;

    public function setUp(): void
    {
        parent::setUp();
        ConnectionManager::setConfig('workspace_test', [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Sqlite', 'database' => ':memory:',
        ]);
        $connection = ConnectionManager::get('workspace_test');
        $schemas = [
            'lessons' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, created DATETIME, modified DATETIME',
            'units' => 'id INTEGER PRIMARY KEY, name TEXT, description TEXT',
            'unit_details' => 'id INTEGER PRIMARY KEY, lesson_id INTEGER, exercise_id INTEGER, unit_id INTEGER, sequence INTEGER',
            'card_groups' => 'id INTEGER PRIMARY KEY, name TEXT, card_group_type_id INTEGER, created DATETIME, modified DATETIME',
            'card_card_groups' => 'id INTEGER PRIMARY KEY, card_group_id INTEGER, card_id INTEGER, created DATETIME, modified DATETIME',
            'cards' => 'id INTEGER PRIMARY KEY, lakota TEXT, english TEXT, alt_lakota TEXT, alt_english TEXT, '
                . 'image_id INTEGER, video_id INTEGER, audio TEXT, created DATETIME, modified DATETIME',
            'exercise_options' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, exercise_id INTEGER, card_id INTEGER, '
                . 'responce_card_id INTEGER, group_id INTEGER, type TEXT, card_type TEXT, prompt_preview_option TEXT, '
                . 'responce_preview_option TEXT, response_true_false TEXT, fill_in_the_blank_type TEXT, text_option TEXT, '
                . 'option_position INTEGER, vbl_letter TEXT, vbl_letter_audio_id INTEGER, vbl_letter_image_id INTEGER, vbl_coloring_image_id INTEGER',
            'exercise_custom_options' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, exercise_id INTEGER, exercise_option_id INTEGER, '
                . 'prompt_html TEXT, response_html TEXT, prompt_audio_id INTEGER, prompt_image_id INTEGER, response_audio_id INTEGER, '
                . 'response_image_id INTEGER, created DATETIME, modified DATETIME',
            'exercises' => 'id INTEGER PRIMARY KEY, name TEXT, instruction TEXT, bonus INTEGER, exercise_type TEXT, card_type TEXT, '
                . 'noofcard INTEGER, promteresponsetype TEXT, promotetype TEXT, responsetype TEXT, vbl_activity TEXT, '
                . 'vbl_mode TEXT, vbl_review_enabled INTEGER, created DATETIME, modified DATETIME',
            'lesson_frames' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, lesson_id INTEGER, name TEXT, duration REAL, audio_id INTEGER, '
                . 'number_of_block INTEGER, frame_preview TEXT, frameorder INTEGER, created DATETIME, modified DATETIME',
            'lesson_frame_blocks' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, lesson_frame_id INTEGER, card_id INTEGER, type TEXT, '
                . 'audio_id INTEGER, image_id INTEGER, video_id INTEGER, custom_html TEXT, block_no INTEGER, '
                . 'is_card_lakota TEXT, is_card_english TEXT, is_card_audio TEXT, is_card_image TEXT, is_card_video TEXT, '
                . 'created DATETIME, modified DATETIME',
            'card_units' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, unit_id INTEGER, card_id INTEGER',
            'files' => 'id INTEGER PRIMARY KEY, name TEXT, type TEXT, file_name TEXT, aws_link TEXT',
            'levels' => 'id INTEGER PRIMARY KEY, name TEXT, description TEXT, created DATETIME, modified DATETIME',
            'path_levels' => 'id INTEGER PRIMARY KEY, learningpath_id INTEGER, level_id INTEGER, sequence INTEGER, '
                . 'created DATETIME, modified DATETIME',
            'level_units' => 'id INTEGER PRIMARY KEY, learningpath_id INTEGER, level_id INTEGER, unit_id INTEGER, '
                . 'sequence INTEGER, created DATETIME, modified DATETIME',
        ];
        foreach ($schemas as $table => $columns) {
            $connection->execute("CREATE TABLE $table ($columns)");
        }
        $connection->execute("INSERT INTO lessons (id, name) VALUES (1, 'First'), (2, 'Second')");
        $connection->execute("INSERT INTO units (id, name) VALUES (1, 'Unit')");
        $connection->execute('INSERT INTO unit_details (id, lesson_id, unit_id) VALUES (1, 1, 1)');
        $connection->execute("INSERT INTO card_groups (id, name, card_group_type_id) VALUES (1, 'Group', 1)");
        $connection->execute("INSERT INTO cards (id, lakota, english) VALUES (1, 'One', 'One'), (2, 'Two', 'Two')");
        $connection->execute('INSERT INTO card_card_groups (id, card_group_id, card_id) VALUES (1, 1, 1)');
        $connection->execute("INSERT INTO exercises (id, name, exercise_type, card_type, promteresponsetype, bonus) "
            . "VALUES (1, 'Pairs', 'match-the-pair', 'custom', 'l-e', 0)");
        $connection->execute('INSERT INTO unit_details (id, exercise_id, unit_id) VALUES (2, 1, 1)');
        $connection->execute("INSERT INTO files (id, name, type, aws_link) VALUES "
            . "(1, 'Image', 'image', 'https://example.test/image.png'), (2, 'Audio', 'audio', 'https://example.test/audio.mp3'), "
            . "(3, 'Other audio', 'audio', 'https://example.test/other.mp3')");
        $this->tables = new TableLocator();
        foreach ([
            'Lessons', 'Units', 'Unitdetails', 'Cardgroup', 'Cardcardgroup', 'Card',
            'Exerciseoptions', 'ExerciseCustomOptions', 'Exercises', 'LessonFrames', 'Lessonframes', 'LessonFrameBlocks', 'CardUnits', 'Files',
            'Levels', 'Pathlevel', 'LevelUnits',
        ] as $alias) {
            $this->tables->setConfig($alias, ['connection' => $connection]);
        }
        $this->originalTables = TableRegistry::getTableLocator();
        TableRegistry::setTableLocator($this->tables);
        $routes = require CONFIG . 'routes.php';
        $routes(Router::createRouteBuilder('/'));
        $_SERVER['HTTP_HOST'] = 'localhost';
    }

    public function tearDown(): void
    {
        $this->tables->clear();
        TableRegistry::setTableLocator($this->originalTables);
        ConnectionManager::drop('workspace_test');
        Router::reload();
        parent::tearDown();
    }

    private function controller(string $method = 'GET', array $data = [], array $query = []): IsolatedWorkspaceController
    {
        $request = new ServerRequest([
            'environment' => ['REQUEST_METHOD' => $method],
            'post' => $data, 'query' => $query,
        ]);
        $controller = new IsolatedWorkspaceController($request, new Response());
        $controller->setTableLocator($this->tables);
        return $controller;
    }

    private function body(Response $response): array
    {
        return json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function exercisePayload(): array
    {
        $content = $this->body($this->controller()->exerciseContent('1'));
        return ['revision' => $content['revision'], 'fields' => $content['fields'], 'options' => [
            ['fields' => ['type' => 'card', 'card_type' => 'O', 'card_id' => 1, 'responce_card_id' => 2],
                'custom' => ['prompt_html' => '<b>Prompt</b>', 'response_audio_id' => 2]],
        ]];
    }

    private function framePayload(): array
    {
        return ['fields' => ['name' => 'A frame', 'duration' => 3.5, 'audio_id' => 2, 'frame_preview' => 'landscape'],
            'blocks' => [
                ['fields' => ['type' => 'card', 'card_id' => 1, 'is_card_lakota' => 'Y', 'is_card_audio' => 'N']],
                ['fields' => ['type' => 'html', 'custom_html' => '<p>Lesson text</p>']],
            ]];
    }

    public function testExerciseOptionsAndCustomAttachmentsRoundTripAndSynchronizeCards(): void
    {
        $payload = $this->exercisePayload();
        $response = $this->controller('POST', $payload)->exerciseContent('1');
        $this->assertSame(200, $response->getStatusCode());
        $content = $this->body($response);
        $this->assertSame('<b>Prompt</b>', $content['options'][0]['custom']['prompt_html']);
        $this->assertSame('https://example.test/audio.mp3', $content['files'][2]);
        $this->assertSame(1, $content['fields']['noofcard']);
        // Legacy CardUnits rules count only the option card_id, not the response card.
        $this->assertSame([1], array_values($this->tables->get('CardUnits')->find('list', ['valueField' => 'card_id'])->toArray()));
        $this->assertSame(409, $this->controller('POST', $payload)->exerciseContent('1')->getStatusCode());
        $this->assertSame(1, $this->tables->get('Exerciseoptions')->find()->count());
        $content['options'][0]['custom'] = [];
        $again = $this->controller('POST', $content)->exerciseContent('1');
        $this->assertSame(200, $again->getStatusCode());
        $this->assertSame(0, $this->tables->get('ExerciseCustomOptions')->find()->count());
    }

    public function testExerciseInvalidAttachmentRollsBackAllChanges(): void
    {
        $payload = $this->exercisePayload();
        $payload['fields']['name'] = 'Must not persist';
        $payload['options'][0]['custom']['response_audio_id'] = 1;
        $response = $this->controller('POST', $payload)->exerciseContent('1');
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('Pairs', $this->tables->get('Exercises')->get(1)->name);
        $this->assertSame(0, $this->tables->get('Exerciseoptions')->find()->count());
    }

    public function testDuplicateExerciseOptionIdsAreRejectedAtomically(): void
    {
        $created = $this->body($this->controller('POST', $this->exercisePayload())->exerciseContent('1'));
        $created['options'][] = $created['options'][0];
        $this->assertSame(422, $this->controller('POST', $created)->exerciseContent('1')->getStatusCode());
        $this->assertSame(1, $this->tables->get('Exerciseoptions')->find()->count());
    }

    public function testVocabularySavePreservesExistingOptionIdsOnOlderSchema(): void
    {
        $payload = $this->exercisePayload();
        $payload['fields'] = array_replace($payload['fields'], [
            'exercise_type' => 'vocab-builder', 'card_type' => 'card', 'vbl_activity' => 'word-list',
            'vbl_mode' => 'word', 'vbl_review_enabled' => 0,
        ]);
        $payload['options'][0] = ['fields' => ['type' => 'card', 'card_type' => 'P', 'card_id' => 1], 'custom' => []];
        $response = $this->controller('POST', $payload)->exerciseContent('1');
        $this->assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        $content = $this->body($response);
        $id = $content['options'][0]['id'];
        $response = $this->controller('POST', $content)->exerciseContent('1');
        $this->assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        $this->assertSame($id, $this->body($response)['options'][0]['id']);
    }

    public function testFrameCreationEditingOrderingAndCardRemoval(): void
    {
        $response = $this->controller('POST', $this->framePayload())->lessonFrame('1');
        $this->assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        $frame = $this->body($response);
        $id = (string)$frame['id'];
        $this->assertSame('landscape', $frame['fields']['frame_preview']);
        $this->assertSame(3.5, $frame['fields']['duration']);
        $this->assertSame('Y', $frame['blocks'][0]['fields']['is_card_lakota']);
        $this->assertSame(1, $this->tables->get('CardUnits')->find()->count());
        $frame['blocks'] = array_reverse($frame['blocks']);
        $response = $this->controller('POST', $frame)->lessonFrame('1', $id);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('html', $this->body($response)['blocks'][0]['fields']['type']);
        $this->assertSame(409, $this->controller('POST', $frame)->lessonFrame('1', $id)->getStatusCode());
        $frame = $this->body($response);
        array_pop($frame['blocks']);
        $response = $this->controller('POST', $frame)->lessonFrame('1', $id);
        $this->assertSame(200, $response->getStatusCode());
        // Like the legacy editor, a unit left without any card content keeps its previous CardUnits.
        $this->assertSame(1, $this->tables->get('CardUnits')->find()->count());
        $this->assertSame(1, $this->tables->get('LessonFrameBlocks')->find()->count());
    }

    public function testDuplicateBlocksAndInvalidDurationDoNotChangeFrame(): void
    {
        $frame = $this->body($this->controller('POST', $this->framePayload())->lessonFrame('1'));
        $frame['blocks'][] = $frame['blocks'][0];
        $response = $this->controller('POST', $frame)->lessonFrame('1', (string)$frame['id']);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(2, $this->tables->get('LessonFrameBlocks')->find()->count());
        array_pop($frame['blocks']);
        $frame['fields']['duration'] = -1;
        $this->assertSame(422, $this->controller('POST', $frame)->lessonFrame('1', (string)$frame['id'])->getStatusCode());
        $this->assertSame(3.5, $this->tables->get('LessonFrames')->get($frame['id'])->duration);
    }

    public function testFrameCannotBeSavedUnderAnotherLesson(): void
    {
        $frame = $this->body($this->controller('POST', $this->framePayload())->lessonFrame('1'));
        $this->expectException(\Cake\Datasource\Exception\RecordNotFoundException::class);
        $this->controller('POST', $frame)->lessonFrame('2', (string)$frame['id']);
    }

    public function testActivityRowsExposeConfigurationCountsAndMediaPreviews(): void
    {
        $this->controller('POST', $this->framePayload())->lessonFrame('1');
        $payload = $this->exercisePayload();
        $payload['fields']['instruction'] = '<p>Pick the matching picture</p>';
        $this->controller('POST', $payload)->exerciseContent('1');
        $card = $this->tables->get('Card')->get(1);
        $card->image_id = 1; $card->audio = '2, 3';
        $this->tables->get('Card')->saveOrFail($card);
        $rows = $this->body($this->controller()->activities('1'))['items'];
        $this->assertCount(2, $rows);
        $this->assertContains('1 frames', $rows[0]['metadata']);
        $this->assertContains('2 blocks', $rows[0]['metadata']);
        $this->assertSame('https://example.test/audio.mp3', $rows[0]['audioUrl']);
        $this->assertSame('https://example.test/image.png', $rows[0]['previewCards'][0]['imageUrl']);
        $this->assertCount(2, $rows[0]['previewCards'][0]['audioUrls']);
        $this->assertSame('Pick the matching picture', $rows[1]['description']);
        $this->assertContains('Match pairs', $rows[1]['metadata']);
        $this->assertContains('1 content items', $rows[1]['metadata']);
        $this->assertSame(1, $this->tables->get('CardUnits')->find()->count());
    }

    public function testContentRecalculationRemovesStaleAndDuplicateUnitCards(): void
    {
        $payload = $this->exercisePayload();
        $payload['fields']['exercise_type'] = 'anagram';
        $payload['fields']['card_type'] = 'card';
        $payload['options'] = [['fields' => ['type' => 'card', 'card_type' => 'O', 'card_id' => 1], 'custom' => []]];
        $connection = $this->tables->get('CardUnits')->getConnection();
        $connection->execute('INSERT INTO card_units (unit_id, card_id) VALUES (1, 1), (1, 1), (1, 2)');
        $response = $this->controller('POST', $payload)->exerciseContent('1');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([1], array_values($this->tables->get('CardUnits')->find('list', ['valueField' => 'card_id'])->toArray()));
        $content = $this->body($response);
        $content['fields']['exercise_type'] = 'match-the-pair';
        $content['fields']['card_type'] = 'custom';
        $content['options'] = [['fields' => ['type' => 'card', 'card_type' => 'O'],
            'custom' => ['prompt_html' => 'Prompt', 'response_html' => 'Response']]];
        $this->assertSame(200, $this->controller('POST', $content)->exerciseContent('1')->getStatusCode());
        $this->assertSame(1, $this->tables->get('CardUnits')->find()->count());
    }

    public function testInvalidExerciseFieldsAndBlockFlagsAreExplicitlyRejected(): void
    {
        foreach (['bonus' => 'invalid', 'noofcard' => -1, 'promotetype' => 'invalid', 'exercise_type' => 'anagram'] as $field => $value) {
            $payload = $this->exercisePayload();
            $payload['fields'][$field] = $value;
            $response = $this->controller('POST', $payload)->exerciseContent('1');
            $this->assertSame(422, $response->getStatusCode());
            $this->assertNotEmpty($this->body($response)['message']);
        }
        $payload = $this->framePayload();
        $payload['blocks'][0]['fields']['is_card_lakota'] = 'invalid';
        $this->assertSame(422, $this->controller('POST', $payload)->lessonFrame('1')->getStatusCode());
        $this->assertSame(0, $this->tables->get('LessonFrames')->find()->count());
    }

    public function testLessonFrameListsArePaginatedOnTheServer(): void
    {
        $connection = $this->tables->get('LessonFrames')->getConnection();
        $count = \App\Lib\WorkspaceOrder::PAGE_SIZE + 1;
        for ($index = 1; $index <= $count; $index++) {
            $connection->execute(
                'INSERT INTO lesson_frames (lesson_id, name, frameorder, number_of_block, duration, frame_preview) VALUES (?, ?, ?, 1, 1, ?)',
                [1, "Frame $index", $index, 'landscape']
            );
        }
        $first = $this->body($this->controller()->lessonFrames('1'));
        $this->assertCount($count - 1, $first['items']);
        $this->assertSame($count, $first['total']);
        $this->assertNull($first['order'], 'Partial frame lists cannot be reordered.');
        $second = $this->body($this->controller('GET', [], ['page' => 2])->lessonFrames('1'));
        $this->assertCount(1, $second['items']);
        $this->assertSame("Frame $count", $second['items'][0]['name']);
    }

    public function testPreviewIdsAcceptNumericAggregateTypes(): void
    {
        $rows = (new WorkspacePresentation($this->tables))->decorate([
            ['id' => 1.0, 'type' => 'cards', 'title' => 'One'],
        ]);
        $this->assertCount(1, $rows[0]['previewCards']);
        $this->assertSame(1, $rows[0]['previewCards'][0]['id']);
    }

    public function testSearchAndRelationshipFilteringAreServerSide(): void
    {
        $result = $this->body($this->controller('GET', [], [
            'parentType' => 'units', 'parentId' => 1, 'q' => 'First',
        ])->records('lessons'));
        $this->assertSame(1, $result['total']);
        $this->assertSame(25, $result['pageSize']);
        $this->assertSame('First', $result['items'][0]['title']);
    }

    public function testUpdatesPreserveModelUniquenessAndRejectStaleRevisions(): void
    {
        $original = $this->body($this->controller()->record('lessons', '1'));
        $result = $this->controller('POST', [
            'fields' => ['name' => 'Renamed'], 'revision' => $original['revision'],
        ])->save('lessons', '1');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('Renamed', $this->body($result)['title']);
        $stale = $this->controller('POST', [
            'fields' => ['name' => 'Overwrite'], 'revision' => $original['revision'],
        ])->save('lessons', '1');
        $this->assertSame(409, $stale->getStatusCode());
        $current = $this->body($result);
        $duplicate = $this->controller('POST', [
            'fields' => ['name' => 'Second'], 'revision' => $current['revision'],
        ])->save('lessons', '1');
        $this->assertSame(422, $duplicate->getStatusCode());
    }

    public function testUnchangedNameDoesNotFailItsOwnUniquenessCheck(): void
    {
        $original = $this->body($this->controller()->record('lessons', '1'));
        $result = $this->controller('POST', [
            'fields' => ['name' => 'First'], 'revision' => $original['revision'],
        ])->save('lessons', '1');
        $this->assertSame(200, $result->getStatusCode());
    }

    public function testCreateUsesModelValidation(): void
    {
        $invalid = $this->controller('POST', ['fields' => ['name' => '']])->save('lessons');
        $this->assertSame(422, $invalid->getStatusCode());
        $created = $this->controller('POST', ['fields' => ['name' => 'New lesson']])->save('lessons');
        $this->assertSame(200, $created->getStatusCode());
        $this->assertSame('New lesson', $this->body($created)['title']);
    }

    public function testUnknownFieldsCannotBeMassAssigned(): void
    {
        $this->expectException(BadRequestException::class);
        $this->controller('POST', ['fields' => ['name' => 'Allowed', 'id' => 200]])->save('lessons');
    }

    public function testInvalidSortCannotReachTheQuery(): void
    {
        $this->expectException(BadRequestException::class);
        $this->controller('GET', [], ['sort' => 'unknown'])->records('lessons');
    }

    public function testGroupAssociationChangesAreRevisionCheckedAndDoNotDeleteCards(): void
    {
        $group = $this->body($this->controller()->record('groups', '1'));
        $added = $this->controller('POST', [
            'cardId' => 2, 'operation' => 'add', 'revision' => $group['revision'],
        ])->membership('1');
        $this->assertSame(200, $added->getStatusCode());
        $this->assertNotSame($group['revision'], $this->body($added)['revision']);
        $stale = $this->controller('POST', [
            'cardId' => 1, 'operation' => 'remove', 'revision' => $group['revision'],
        ])->membership('1');
        $this->assertSame(409, $stale->getStatusCode());
        $removed = $this->controller('POST', [
            'cardId' => 1, 'operation' => 'remove', 'revision' => $this->body($added)['revision'],
        ])->membership('1');
        $this->assertSame(200, $removed->getStatusCode());
        $this->assertSame(2, $this->tables->get('Card')->find()->count());
        $last = $this->controller('POST', [
            'cardId' => 2, 'operation' => 'remove', 'revision' => $this->body($removed)['revision'],
        ])->membership('1');
        $this->assertSame(422, $last->getStatusCode());
    }

    public function testLinkedLevelsAndUnitsListInStoredSequenceAndCanBeReordered(): void
    {
        $connection = $this->tables->get('Levels')->getConnection();
        $connection->execute("INSERT INTO levels (id, name) VALUES (1, 'Alpha'), (2, 'Beta'), (3, 'Gamma')");
        $connection->execute('INSERT INTO path_levels (id, learningpath_id, level_id, sequence) VALUES (10, 1, 1, 3), (11, 1, 2, 1), (12, 1, 3, 2), (13, 2, 1, 1)');
        $query = ['parentType' => 'paths', 'parentId' => 1, 'sort' => 'sequence'];
        $list = $this->body($this->controller('GET', [], $query)->records('levels'));
        $this->assertSame([2, 3, 1], array_column($list['items'], 'id'));
        $this->assertSame([11, 12, 10], array_column($list['items'], 'linkId'));
        $this->assertSame('path-levels', $list['order']['scope']);
        $byName = $this->body($this->controller('GET', [], ['sort' => 'name'] + $query)->records('levels'));
        $this->assertSame([1, 2, 3], array_column($byName['items'], 'id'));
        $this->assertNull($byName['order']);

        $response = $this->controller('POST', ['scope' => 'path-levels', 'parentId' => 1, 'ids' => [10, 11, 12]])->reorder();
        $this->assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        $list = $this->body($this->controller('GET', [], $query)->records('levels'));
        $this->assertSame([1, 2, 3], array_column($list['items'], 'id'));
        $this->assertSame(1, $this->tables->get('Pathlevel')->get(13)->sequence, 'Other paths keep their order.');

        $connection->execute('INSERT INTO level_units (id, learningpath_id, level_id, unit_id, sequence) VALUES (20, 1, 1, 1, 1)');
        $units = $this->body($this->controller('GET', [], [
            'parentType' => 'levels', 'parentId' => 1, 'pathId' => 1, 'sort' => 'sequence',
        ])->records('units'));
        $this->assertSame(['scope' => 'level-units', 'parentId' => 1, 'pathId' => 1], $units['order']);
        $noPath = $this->body($this->controller('GET', [], [
            'parentType' => 'levels', 'parentId' => 1, 'sort' => 'sequence',
        ])->records('units'));
        $this->assertNull($noPath['order'], 'Unit order belongs to a path, so it needs one.');
    }

    public function testReorderRejectsStaleOrIncompleteListsWithoutChanges(): void
    {
        $activities = $this->body($this->controller()->activities('1'));
        $this->assertSame([1, 2], array_column($activities['items'], 'linkId'));
        $this->assertSame('unit-activities', $activities['order']['scope']);
        $stale = $this->controller('POST', ['scope' => 'unit-activities', 'parentId' => 1, 'ids' => [2]])->reorder();
        $this->assertSame(409, $stale->getStatusCode());
        $foreign = $this->controller('POST', ['scope' => 'unit-activities', 'parentId' => 1, 'ids' => [2, 99]])->reorder();
        $this->assertSame(409, $foreign->getStatusCode());
        $twice = $this->controller('POST', ['scope' => 'unit-activities', 'parentId' => 1, 'ids' => [2, 2]])->reorder();
        $this->assertSame(422, $twice->getStatusCode());
        $unknown = $this->controller('POST', ['scope' => 'cards', 'parentId' => 1, 'ids' => [1]])->reorder();
        $this->assertSame(422, $unknown->getStatusCode());
        $this->assertNull($this->tables->get('Unitdetails')->get(1)->sequence);

        $ok = $this->controller('POST', ['scope' => 'unit-activities', 'parentId' => 1, 'ids' => [2, 1]])->reorder();
        $this->assertSame(200, $ok->getStatusCode());
        $this->assertSame(['exercises', 'lessons'], array_column($this->body($this->controller()->activities('1'))['items'], 'type'));
    }

    public function testFrameReorderKeepsOpenFrameRevisionValid(): void
    {
        $first = $this->body($this->controller('POST', $this->framePayload())->lessonFrame('1'));
        $payload = $this->framePayload();
        $payload['fields']['name'] = 'Another frame';
        $response = $this->controller('POST', $payload)->lessonFrame('1');
        $this->assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        $second = $this->body($response);
        $frames = $this->body($this->controller()->lessonFrames('1'));
        $this->assertSame([$first['id'], $second['id']], array_column($frames['items'], 'id'));
        $response = $this->controller('POST', [
            'scope' => 'lesson-frames', 'parentId' => 1, 'ids' => [$second['id'], $first['id']],
        ])->reorder();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([$second['id'], $first['id']], array_column($this->body($this->controller()->lessonFrames('1'))['items'], 'id'));
        $first['fields']['name'] = 'Renamed after reorder';
        $this->assertSame(200, $this->controller('POST', $first)->lessonFrame('1', (string)$first['id'])->getStatusCode());
    }

    public function testMutationsRequireTheSessionToken(): void
    {
        $this->expectException(ForbiddenException::class);
        $controller = $this->controller('POST', ['fields' => ['name' => 'New']]);
        $controller->beforeFilter(new Event('Controller.beforeFilter'));
    }
}
