<?php

declare(strict_types=1);

namespace App\Test\TestCase\Lib;

use App\Lib\VocabBuilder;
use App\Controller\Admin\ExercisesController;
use App\Controller\Api\ExerciseController;
use App\Controller\Api\DownloadController;
use App\Controller\Api\ReviewController;
use App\Controller\Api\AppController as ApiAppController;
use App\Model\Table\ExercisesTable;
use App\Model\Table\ExerciseoptionsTable;
use App\Model\Table\ExerciseCustomOptionsTable;
use App\Model\Table\UnitdetailsTable;
use App\Model\Table\UserActivitiesTable;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use Cake\ORM\Entity;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Phinx\Db\Adapter\SQLiteAdapter;
use Phinx\Db\Adapter\ProxyAdapter;
use PDO;
use ReflectionMethod;

class VocabBuilderTest extends TestCase
{
    private $originalLocator;
    private VocabBuilder $builder;
    private array $cards;
    private array $files;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalLocator = TableRegistry::getTableLocator();
        $locator = new TableLocator();
        TableRegistry::setTableLocator($locator);
        $this->builder = new VocabBuilder();
        $this->cards = [
            1 => ['id' => 1, 'lakota' => 'apple', 'english' => 'Apple', 'audio' => '10,11', 'image_id' => 20],
            2 => ['id' => 2, 'lakota' => 'boat', 'english' => 'Boat', 'audio' => '10', 'image_id' => 20],
            3 => ['id' => 3, 'lakota' => 'cat', 'english' => 'Cat', 'audio' => '10', 'image_id' => 20],
        ];
        $this->files = [
            10 => ['type' => 'audio', 'FullUrl' => 'https://example.test/word.mp3'],
            11 => ['type' => 'audio', 'FullUrl' => 'https://example.test/word-alt.mp3'],
            12 => ['type' => 'audio', 'FullUrl' => 'https://example.test/letter.mp3'],
            20 => ['type' => 'image', 'FullUrl' => 'https://example.test/word.png'],
            21 => ['type' => 'image', 'FullUrl' => 'https://example.test/line-art.png'],
        ];
        $cards = $this->getMockBuilder(Table::class)->onlyMethods(['get'])->getMock();
        $cards->method('get')->willReturnCallback(function ($id) {
            return new Entity($this->cards[$id]);
        });
        $files = $this->getMockBuilder(Table::class)->onlyMethods(['get'])->getMock();
        $files->method('get')->willReturnCallback(function ($id) {
            return new Entity($this->files[$id]);
        });
        $locator->set('Card', $cards);
        $locator->set('Files', $files);
    }

    protected function tearDown(): void
    {
        TableRegistry::setTableLocator($this->originalLocator);
        parent::tearDown();
    }

    private function rows(): array
    {
        return [
            ['card_id' => 1, 'vbl_letter' => 'a', 'vbl_letter_audio_id' => 12,
                'vbl_coloring_image_id' => 21, 'target' => 1],
            ['card_id' => 2, 'vbl_letter' => 'b', 'vbl_letter_audio_id' => 12],
            ['card_id' => 3, 'vbl_letter' => 'c', 'vbl_letter_audio_id' => 12],
        ];
    }

    public function testAllActivitiesHavePlayablePayloadsAndOneTrackedQuestion(): void
    {
        foreach (array_keys(VocabBuilder::ACTIVITIES) as $activity) {
            $rows = $activity === 'memory-activity' ? $this->memoryRows() : $this->rows();
            $options = $this->builder->normalizeOptions($rows);
            $options[0]['id'] = 99;
            $exercise = $this->builder->exercise(['vbl_activity' => $activity], $options);
            $this->assertCount(1, $exercise['questions']);
            $payload = $exercise['questions'][0]['vocab_builder'];
            $this->assertSame($activity, $payload['activity']);
            $this->assertSame('guided', $payload['config']['playMode']);
            $this->assertSame(array_column($rows, 'card_id'), $payload['card_ids']);
            $this->assertSame(99, $payload['question']['exerciseOptions']['id']);
            if ($payload['mode'] === 'word') {
                $this->assertSame([
                    'https://example.test/word.mp3', 'https://example.test/word-alt.mp3',
                ], $payload['config']['targetWord']['mp3']);
            } else {
                $this->assertNull($payload['config']['targetWord']);
            }
            if ($activity === 'fill-letter') {
                $this->assertSame('[a]pple', $payload['config']['targetWord']['word']);
            }
        }
    }

    private function memoryRows(): array
    {
        $rows = $this->rows();
        foreach (['d', 'e', 'f', 'g', 'h'] as $index => $letter) {
            $id = $index + 4;
            $this->cards[$id] = [
                'id' => $id, 'lakota' => $letter . 'word', 'english' => $letter,
                'audio' => '10', 'image_id' => 20,
            ];
            $rows[] = ['card_id' => $id, 'vbl_letter' => $letter, 'vbl_letter_audio_id' => 12];
        }
        return $rows;
    }

    public function testMemoryRequiresEnoughLettersForEveryOfferedGridSize(): void
    {
        $rows = $this->memoryRows();
        $this->builder->payload(['vbl_activity' => 'memory-activity'], $this->builder->normalizeOptions($rows));
        array_pop($rows);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('8 distinct letters');
        $this->builder->payload(['vbl_activity' => 'memory-activity'], $this->builder->normalizeOptions($rows));
    }

    public function testReviewCanRetargetAnAuthoredDistractor(): void
    {
        $payload = $this->builder->payload(
            ['vbl_activity' => 'match-sound'],
            $this->builder->normalizeOptions($this->rows()),
            2
        );
        $this->assertSame(2, $payload['question']['id']);
        $this->assertNull($payload['config']['targetLetter']);
        $this->assertSame('boat', $payload['config']['targetWord']['word']);
        $this->assertSame(1, $payload['target_word_index']);
        $this->assertFalse(VocabBuilder::ACTIVITIES['word-list']['review']);
    }

    public function testRejectsTooFewDistinctLettersAtExactThreshold(): void
    {
        $rows = array_slice($this->rows(), 0, 2);
        $this->builder->payload(['vbl_activity' => 'select-grid'], $this->builder->normalizeOptions($rows));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('3 distinct letters');
        $this->builder->payload(['vbl_activity' => 'fill-letter'], $this->builder->normalizeOptions($rows));
    }

    public function testRejectsDuplicateWordDistractors(): void
    {
        $this->cards[3]['lakota'] = 'boat';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('3 distinct words');
        $this->builder->payload(['vbl_activity' => 'match-sound'], $this->builder->normalizeOptions($this->rows()));
    }

    public function testRejectsMissingTarget(): void
    {
        $rows = $this->rows();
        unset($rows[0]['target']);
        $this->expectException(InvalidArgumentException::class);
        $this->builder->normalizeOptions($rows);
    }

    public function testRejectsTwoTargets(): void
    {
        $rows = $this->rows();
        $rows[1]['target'] = 1;
        $this->expectException(InvalidArgumentException::class);
        $this->builder->normalizeOptions($rows);
    }

    public function testRejectsWrongAttachmentType(): void
    {
        $rows = $this->rows();
        $rows[0]['vbl_letter_audio_id'] = 20;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('audio file');
        $this->builder->payload(['vbl_activity' => 'memory-activity'], $this->builder->normalizeOptions($rows));
    }

    public function testRejectsMissingWordMedia(): void
    {
        $this->cards[1]['audio'] = '';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('language text, audio and an image');
        $this->builder->payload(['vbl_activity' => 'word-list'], $this->builder->normalizeOptions($this->rows()));
    }

    public function testRejectsMissingColoringImage(): void
    {
        $rows = $this->rows();
        unset($rows[0]['vbl_coloring_image_id']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('line-art image');
        $this->builder->payload(['vbl_activity' => 'coloring'], $this->builder->normalizeOptions($rows));
    }

    public function testRejectsMarkedWordThatChangesCardContent(): void
    {
        $rows = $this->rows();
        $rows[0]['text_option'] = '[b]anana';
        $this->expectException(InvalidArgumentException::class);
        $this->builder->payload(['vbl_activity' => 'fill-letter'], $this->builder->normalizeOptions($rows));
    }

    public function testSupportsMultiCharacterLetterAndExplicitPosition(): void
    {
        $this->cards[1]['lakota'] = 'shash';
        $rows = $this->rows();
        $rows[0]['vbl_letter'] = 'sh';
        $rows[0]['text_option'] = 'sha[sh]';
        $payload = $this->builder->payload(['vbl_activity' => 'fill-letter'], $this->builder->normalizeOptions($rows));
        $this->assertSame('sha[sh]', $payload['config']['targetWord']['word']);
    }

    public function testMigrationPreservesExistingRowsAndRollsBack(): void
    {
        require_once dirname(__DIR__, 3) . '/config/Migrations/20261105000000_AddVocabBuilderExercises.php';
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE exercises (id INTEGER PRIMARY KEY, name TEXT)');
        $pdo->exec('CREATE TABLE exercise_options (id INTEGER PRIMARY KEY, card_id INTEGER)');
        $pdo->exec("INSERT INTO exercises (id, name) VALUES (1, 'Existing')");
        $adapter = new SQLiteAdapter(['name' => ':memory:', 'connection' => $pdo]);
        $migration = new \AddVocabBuilderExercises('test', 20261105000000);
        $migration->setAdapter($adapter);
        $migration->change();
        $this->assertTrue($adapter->hasColumn('exercises', 'vbl_activity'));
        $this->assertTrue($adapter->hasColumn('exercise_options', 'vbl_coloring_image_id'));
        $row = $pdo->query('SELECT * FROM exercises')->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('Existing', $row['name']);
        $this->assertSame(0, $row['vbl_review_enabled']);
        $proxy = new ProxyAdapter($adapter);
        $migration->setAdapter($proxy);
        $migration->change();
        $proxy->executeInvertedCommands();
        $this->assertFalse($adapter->hasColumn('exercises', 'vbl_activity'));
        $this->assertFalse($adapter->hasColumn('exercise_options', 'vbl_letter'));
        $this->assertSame('Existing', $pdo->query('SELECT name FROM exercises')->fetchColumn());
    }

    private function authoringTables(): array
    {
        $connection = new Connection(['driver' => Sqlite::class, 'database' => ':memory:']);
        $connection->execute('CREATE TABLE exercises (
            id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, instruction TEXT, bonus INTEGER,
            exercise_type TEXT, card_type TEXT, promteresponsetype TEXT, promotetype TEXT,
            responsetype TEXT, noofcard INTEGER, vbl_activity TEXT, vbl_mode TEXT, vbl_review_enabled BOOLEAN,
            created DATETIME, modified DATETIME
        )');
        $connection->execute('CREATE TABLE exercise_options (
            id INTEGER PRIMARY KEY AUTOINCREMENT, exercise_id INTEGER, card_id INTEGER, type TEXT,
            card_type TEXT, text_option TEXT, vbl_letter TEXT, vbl_letter_audio_id INTEGER,
            vbl_letter_image_id INTEGER, vbl_coloring_image_id INTEGER
        )');
        $connection->execute('CREATE TABLE exercise_custom_options (
            id INTEGER PRIMARY KEY AUTOINCREMENT, exercise_option_id INTEGER
        )');
        $connection->execute('CREATE TABLE unit_details (
            id INTEGER PRIMARY KEY AUTOINCREMENT, exercise_id INTEGER, unit_id INTEGER
        )');
        $exercises = new ExercisesTable(['alias' => 'Exercises', 'connection' => $connection]);
        $options = new ExerciseoptionsTable(['alias' => 'Exerciseoptions', 'connection' => $connection]);
        TableRegistry::getTableLocator()->set('Exercises', $exercises);
        TableRegistry::getTableLocator()->set('Exerciseoptions', $options);
        TableRegistry::getTableLocator()->set('Unitdetails', new UnitdetailsTable([
            'alias' => 'Unitdetails', 'connection' => $connection,
        ]));
        TableRegistry::getTableLocator()->set('ExerciseCustomOptions', new ExerciseCustomOptionsTable([
            'alias' => 'ExerciseCustomOptions', 'table' => 'exercise_custom_options',
            'connection' => $connection,
        ]));
        return [$exercises, $options];
    }

    private function authoringController($exercises, $options, bool $updateSucceeds): ExercisesController
    {
        $controller = $this->getMockBuilder(ExercisesController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getExercisesTable', 'getExerciseoptionsTable', 'updateCardUnitsAccordingToExercise'])
            ->getMock();
        $controller->method('getExercisesTable')->willReturn($exercises);
        $controller->method('getExerciseoptionsTable')->willReturn($options);
        $controller->method('updateCardUnitsAccordingToExercise')->willReturn($updateSucceeds);
        return $controller;
    }

    public function testAuthoringPersistsExerciseAndOptionsAtomically(): void
    {
        [$exercises, $options] = $this->authoringTables();
        $controller = $this->authoringController($exercises, $options, true);
        $method = new ReflectionMethod(ExercisesController::class, 'saveVocabBuilder');
        $method->invoke($controller, $exercises->newEmptyEntity(), [
            'name' => 'Vocab activity', 'vbl_activity' => 'match-sound',
            'vbl_review_enabled' => 1, 'vbl_options' => $this->rows(),
        ]);
        $exercise = $exercises->find()->first();
        $this->assertSame('vocab-builder', $exercise->exercise_type);
        $this->assertSame(1, $exercise->noofcard);
        $this->assertTrue($exercise->vbl_review_enabled);
        $this->assertSame(3, $options->find()->count());
        $this->assertSame(1, $options->find()->where(['card_type' => 'P'])->count());
        $this->assertSame('word', $exercise->vbl_mode);
        $this->assertNull($options->find()->where(['card_type' => 'P'])->first()->vbl_letter);

        $failedController = $this->authoringController($exercises, $options, false);
        try {
            $method->invoke($failedController, $exercise, [
                'name' => 'Changed', 'vbl_activity' => 'select-grid', 'vbl_options' => $this->rows(),
            ]);
            $this->fail('Unit sync failure must abort the entire save.');
        } catch (InvalidArgumentException $error) {
            $this->assertSame('Could not update unit word content.', $error->getMessage());
        }
        $this->assertSame('Vocab activity', $exercises->find()->first()->name);
        $this->assertSame('match-sound', $exercises->find()->first()->vbl_activity);
        $this->assertSame(3, $options->find()->count());
    }

    public function testAuthoringRejectsWordListReviewWithoutWriting(): void
    {
        [$exercises, $options] = $this->authoringTables();
        $controller = $this->authoringController($exercises, $options, true);
        $method = new ReflectionMethod(ExercisesController::class, 'saveVocabBuilder');
        try {
            $method->invoke($controller, $exercises->newEmptyEntity(), [
                'name' => 'Word list', 'vbl_activity' => 'word-list',
                'vbl_review_enabled' => 1, 'vbl_options' => $this->rows(),
            ]);
            $this->fail('Word List review must be rejected.');
        } catch (InvalidArgumentException $error) {
            $this->assertSame('Word List cannot be used in scored review.', $error->getMessage());
        }
        $this->assertSame(0, $exercises->find()->count());
        $this->assertSame(0, $options->find()->count());
    }

    private function saveReviewSource($exercises, $options)
    {
        $controller = $this->authoringController($exercises, $options, true);
        $method = new ReflectionMethod(ExercisesController::class, 'saveVocabBuilder');
        $exercise = $exercises->newEmptyEntity();
        $method->invoke($controller, $exercise, [
            'name' => 'Review source', 'vbl_activity' => 'match-sound',
            'vbl_review_enabled' => 1, 'vbl_options' => $this->rows(),
        ]);
        return $exercise;
    }

    public function testGeneratedReviewRespectsUnitOptInAndQueueMembership(): void
    {
        [$exercises, $options] = $this->authoringTables();
        $source = $this->saveReviewSource($exercises, $options);
        $units = TableRegistry::getTableLocator()->get('Unitdetails');
        $units->getConnection()->execute(
            'INSERT INTO unit_details (exercise_id, unit_id) VALUES (?, ?)',
            [$source->id, 7]
        );
        $controller = $this->getMockBuilder(ReviewController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getExercisesTable', 'getUnitdetailsTable'])
            ->getMock();
        $controller->method('getExercisesTable')->willReturn($exercises);
        $controller->method('getUnitdetailsTable')->willReturn($units);
        $method = new ReflectionMethod(ReviewController::class, 'generateVocabBuilderReview');
        $queue = [['card_id' => 2]];
        $review = $method->invoke($controller, $queue, 7);
        $this->assertSame(2, $review['question']['id']);
        $this->assertSame('boat', $review['vocab_builder']['config']['targetWord']['word']);
        $this->assertSame($source->id, $review['source_exercise_id']);
        $this->assertNull($method->invoke($controller, $queue, 8));
        $this->assertNull($method->invoke($controller, [['card_id' => 999]], 7));
        $this->assertNotNull($method->invoke($controller, $queue, null));
        $exercises->updateAll(['vbl_review_enabled' => false], ['id' => $source->id]);
        $this->assertNull($method->invoke($controller, $queue, 7));
        $exercises->updateAll(['vbl_review_enabled' => true, 'vbl_activity' => 'word-list'], ['id' => $source->id]);
        $this->assertNull($method->invoke($controller, $queue, 7));
    }

    public function testLessonAndDownloadApisUseIdenticalTrackedPayloads(): void
    {
        [$exercises, $options] = $this->authoringTables();
        $source = $this->saveReviewSource($exercises, $options);
        $data = ['exercise_id' => $source->id, 'level_id' => 4, 'unit_id' => 7, 'user_id' => 8];
        $results = [];
        foreach ([ExerciseController::class, DownloadController::class] as $class) {
            $controller = $this->getMockBuilder($class)->disableOriginalConstructor()
                ->onlyMethods([
                    'getExercisesTable',
                    'getExerciseoptionsTable',
                    'getUnitAttemptIdAndIsCompleted',
                    'isCompleted',
                ])
                ->getMock();
            $controller->method('getExercisesTable')->willReturn($exercises);
            $controller->method('getExerciseoptionsTable')->willReturn($options);
            $controller->method('getUnitAttemptIdAndIsCompleted')->willReturn([
                'last_id' => 1, 'isunitComplete' => false,
            ]);
            $controller->method('isCompleted')->willReturn(['status' => false, 'attempted' => false]);
            $method = new ReflectionMethod($class, 'generateExercise');
            $results[] = $method->invoke($controller, $data);
        }
        $this->assertSame($results[0]['questions'], $results[1]['questions']);
        $this->assertSame($results[0]['IsCompleted'], $results[1]['IsCompleted']);
        $this->assertCount(1, $results[0]['questions']);
        $this->assertNotEmpty($results[0]['questions'][0]['question']['exerciseOptions']['id']);
    }

    public function testUnitWordContentIncludesAllAttachedVocabCardsWithoutChangingLegacyRoles(): void
    {
        $controller = $this->getMockBuilder(ExercisesController::class)->disableOriginalConstructor()->getMock();
        $method = new ReflectionMethod(ExercisesController::class, 'addCardsFromActivityToCardUnits');
        $exercise = new Entity([
            'exercise_type' => VocabBuilder::TYPE,
            'exerciseoptions' => array_map(static function ($row) {
                return new Entity($row);
            }, $this->builder->normalizeOptions($this->rows())),
        ]);
        $unit = new Entity(['exercise_id' => 1, 'exercise' => $exercise]);
        $cardIds = [];
        $method->invokeArgs($controller, [$unit, &$cardIds]);
        $this->assertSame([1, 2, 3], $cardIds);
        $exercise->exercise_type = 'multiple-choice';
        $cardIds = [];
        $method->invokeArgs($controller, [$unit, &$cardIds]);
        $this->assertSame([1], $cardIds);
    }

    public function testOneCorrectPracticeAnswerCompletesVocabExercise(): void
    {
        $controller = $this->getMockBuilder(ExerciseController::class)->disableOriginalConstructor()->getMock();
        $method = new ReflectionMethod(ApiAppController::class, 'checkActivityCompletion');
        $this->assertFalse($method->invoke($controller, VocabBuilder::TYPE, 'card', 1, 0, 0));
        $this->assertTrue($method->invoke($controller, VocabBuilder::TYPE, 'card', 1, 1, 0));
        $this->assertFalse($method->invoke($controller, 'multiple-choice', 'card', 1, 0, 0));
        $this->assertTrue($method->invoke($controller, 'multiple-choice', 'card', 1, 1, 0));
    }

    public static function supportedVersions(): array
    {
        return [
            'Bird word' => ['bird-activity', 'word', 2],
            'Coloring word' => ['coloring', 'word', 1],
            'Coloring letter' => ['coloring', 'letter', 1],
            'Fill Letter' => ['fill-letter', 'word', 3],
            'Match Sound word' => ['match-sound', 'word', 3],
            'Match Sound letter' => ['match-sound', 'letter', 3],
            'Memory letter' => ['memory-activity', 'letter', 8],
            'Select Grid letter' => ['select-grid', 'letter', 2],
            'Word List' => ['word-list', 'word', 1],
        ];
    }

    /** @dataProvider supportedVersions */
    public function testEachVersionNeedsOnlyItsOwnResponseData(string $activity, string $mode, int $count): void
    {
        $config = ['vbl_activity' => $activity, 'vbl_mode' => $mode];
        $rows = array_slice($this->memoryRows(), 0, $count);
        foreach ($rows as $index => &$row) {
            if ($mode === 'letter' || ($activity === 'fill-letter' && $index > 0)) {
                unset($row['card_id']);
            }
            if ($mode === 'word' && $activity !== 'fill-letter') {
                unset($row['vbl_letter'], $row['vbl_letter_audio_id']);
            }
        }
        unset($row);
        if ($activity === 'coloring' && $mode === 'letter') {
            $rows[0]['vbl_letter_image_id'] = 21;
        }
        if (in_array($activity, ['match-sound', 'coloring'], true)) {
            $this->cards[1]['image_id'] = null;
        }
        $options = $this->builder->normalizeOptions($rows, $config);
        $options[0]['id'] = 99;
        $payload = $this->builder->payload($config, $options);
        $this->assertSame($mode, $payload['mode']);
        $this->assertSame(99, $payload['question']['exerciseOptions']['id']);
        if ($mode === 'letter') {
            $this->assertNull($payload['question']['id']);
            $this->assertSame([], $payload['card_ids']);
            $this->assertNull($payload['target_word_index']);
            $this->assertNull($payload['config']['targetWord']);
            $this->assertSame('a', $payload['config']['targetLetter']['letter']);
        } else {
            $this->assertNotNull($payload['config']['targetWord']);
            $this->assertSame($activity === 'fill-letter' ? [1] : range(1, $count), $payload['card_ids']);
            if ($activity !== 'fill-letter') {
                $this->assertNull($options[0]['vbl_letter_audio_id']);
                $this->assertNull($payload['config']['targetLetter']);
            }
        }
        if ($count > 1) {
            array_pop($options);
            $this->expectException(InvalidArgumentException::class);
            $this->builder->payload($config, $options);
        }
    }

    public function testRejectsUnsupportedWordGridVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        VocabBuilder::mode(['vbl_activity' => 'select-grid', 'vbl_mode' => 'word']);
    }

    public function testLetterColoringRequiresLineArt(): void
    {
        $config = ['vbl_activity' => 'coloring', 'vbl_mode' => 'letter'];
        $options = $this->builder->normalizeOptions([[
            'vbl_letter' => 'a', 'vbl_letter_audio_id' => 12, 'target' => 1,
        ]], $config);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('letter line-art');
        $this->builder->payload($config, $options);
    }

    public function testLetterOnlyAuthoringPersistsWithoutWordCards(): void
    {
        [$exercises, $options] = $this->authoringTables();
        $controller = $this->authoringController($exercises, $options, true);
        $method = new ReflectionMethod(ExercisesController::class, 'saveVocabBuilder');
        $rows = array_slice($this->rows(), 0, 2);
        foreach ($rows as &$row) {
            unset($row['card_id']);
        }
        unset($row);
        $method->invoke($controller, $exercises->newEmptyEntity(), [
            'name' => 'Letter grid', 'vbl_activity' => 'select-grid',
            'vbl_mode' => 'letter', 'vbl_options' => $rows,
        ]);
        $this->assertSame('letter', $exercises->find()->first()->vbl_mode);
        $this->assertSame(2, $options->find()->where(['card_id IS' => null])->count());
    }

    public function testModeMigrationPreservesLegacyDefaultsAndReverses(): void
    {
        require_once dirname(__DIR__, 3) . '/config/Migrations/20261106000000_AddVocabBuilderTargetMode.php';
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE exercises (id INTEGER PRIMARY KEY, vbl_activity TEXT)');
        $pdo->exec("INSERT INTO exercises VALUES (1, 'match-sound'), (2, 'memory-activity')");
        $adapter = new SQLiteAdapter(['name' => ':memory:', 'connection' => $pdo]);
        $migration = new \AddVocabBuilderTargetMode('test', 20261106000000);
        $migration->setAdapter($adapter);
        $migration->change();
        $rows = $pdo->query('SELECT * FROM exercises')->fetchAll(PDO::FETCH_ASSOC);
        $this->assertSame('word', VocabBuilder::mode($rows[0]));
        $this->assertSame('letter', VocabBuilder::mode($rows[1]));
        $proxy = new ProxyAdapter($adapter);
        $migration->setAdapter($proxy);
        $migration->change();
        $proxy->executeInvertedCommands();
        $this->assertFalse($adapter->hasColumn('exercises', 'vbl_mode'));
        $this->assertSame(2, (int)$pdo->query('SELECT COUNT(*) FROM exercises')->fetchColumn());
    }

    public function testCompletionUsesDistinctCardsAndTracksCardlessLetterAnswers(): void
    {
        [$exercises, $options] = $this->authoringTables();
        $source = $this->saveReviewSource($exercises, $options);
        $connection = $exercises->getConnection();
        $connection->execute('CREATE TABLE user_activities (
            id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, unit_id INTEGER,
            exercise_id INTEGER, user_unit_activity_id INTEGER, card_id INTEGER, type TEXT
        )');
        $activities = new UserActivitiesTable([
            'alias' => 'UserActivities', 'table' => 'user_activities', 'connection' => $connection,
        ]);
        $queries = [];
        $trackedActivities = $this->getMockBuilder(UserActivitiesTable::class)->onlyMethods(['find'])->getMock();
        $trackedActivities->method('find')->willReturnCallback(
            function ($type = 'all', $options = []) use ($activities, &$queries) {
                $query = $activities->find($type, $options);
                $queries[] = $query;
                return $query;
            }
        );
        $controller = $this->getMockBuilder(ExerciseController::class)->disableOriginalConstructor()
            ->onlyMethods(['getExercisesTable', 'getExerciseoptionsTable', 'getUserActivitiesTable'])
            ->getMock();
        $controller->method('getExercisesTable')->willReturn($exercises);
        $controller->method('getExerciseoptionsTable')->willReturn($options);
        $controller->method('getUserActivitiesTable')->willReturn($trackedActivities);
        $method = new ReflectionMethod(ApiAppController::class, 'checkExerciseCompletion');
        $conditions = ['user_id' => 7, 'unit_id' => 9, 'exercise_id' => $source->id];
        $params = ['exercise_id' => $source->id, 'user_unit_activity_id' => 3];
        $this->assertSame(['status' => false, 'attempted' => false], $method->invoke(
            $controller,
            $params,
            $conditions,
            7,
            9
        ));
        $this->assertStringContainsString('SELECT DISTINCT', $queries[0]->sql());
        $this->assertStringNotContainsString('GROUP BY', $queries[0]->sql());
        $this->assertSame(['UserActivities__card_id' => 'UserActivities.card_id'], $queries[0]->clause('select'));
        foreach ([null, 1] as $cardId) {
            $connection->execute('DELETE FROM user_activities');
            $connection->insert('user_activities', $conditions + [
                'user_unit_activity_id' => 3, 'card_id' => $cardId, 'type' => 'wrong',
            ]);
            $this->assertSame(['status' => false, 'attempted' => true], $method->invoke(
                $controller,
                $params,
                $conditions,
                7,
                9
            ));
            $connection->insert('user_activities', $conditions + [
                'user_unit_activity_id' => 3, 'card_id' => $cardId, 'type' => 'right',
            ]);
            $this->assertSame(['status' => true, 'attempted' => true], $method->invoke(
                $controller,
                $params,
                $conditions,
                7,
                9
            ));
        }
    }

    public function testWordContentUsesPlainTextWithoutChangingTheTrackedCard(): void
    {
        $this->cards[1]['lakota'] = '<b>apple</b> &amp; pear';
        $this->cards[1]['english'] = '<i>Apple &amp; pear</i>';
        $config = ['vbl_activity' => 'word-list', 'vbl_mode' => 'word'];
        $payload = $this->builder->payload($config, $this->builder->normalizeOptions([
            ['card_id' => 1, 'target' => 1],
        ], $config));
        $this->assertSame('apple & pear', $payload['config']['targetWord']['word']);
        $this->assertSame('Apple & pear', $payload['config']['targetWord']['eng']);
        $this->assertSame('<b>apple</b> &amp; pear', $payload['question']['lakota']);
    }

    public function testBirdRequiresDistractorsDistinctUnderLibraryCaseMatching(): void
    {
        $this->cards[2]['lakota'] = 'APPLE';
        $config = ['vbl_activity' => 'bird-activity', 'vbl_mode' => 'word'];
        $options = $this->builder->normalizeOptions([
            ['card_id' => 1, 'target' => 1], ['card_id' => 2],
        ], $config);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('2 distinct words');
        $this->builder->payload($config, $options);
    }
}
