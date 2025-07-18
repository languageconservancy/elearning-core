<?php

namespace App\Test\TestCase\Controller;

use App\Controller\Api\LearningPathController;
use Cake\TestSuite\IntegrationTestCase;
use Cake\ORM\TableRegistry;
use App\Lib\UtilLibrary;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use App\Test\Helpers\TestHelpers;

/**
 * Database Tables Used:
 *   - ReviewQueues, PointReferences, CardUnits, Card, Cardcardgroup,
 *   - GlobalFires, ProgressTimers, ActivityTypes, Exercises.
 */
class LearningPathControllerTest extends IntegrationTestCase
{
    public $learningPathCtrl;
    protected $client;

    public $fixtures = [
        'app.Files',
        'app.Learningpaths',
        'app.Levels',
        'app.LevelUnits',
        'app.Pathlevel',
        'app.Units',
        'app.Users',
    ];

    public function setUp(): void
    {
        parent::setUp();
        /* Load table fixtures (test database table based on live database tables */
        $this->Files = TableRegistry::getTableLocator()->get('Files');
        $this->Learningpaths = TableRegistry::getTableLocator()->get('Learningpaths');
        $this->Levels = TableRegistry::getTableLocator()->get('Levels');
        $this->LevelUnits = TableRegistry::getTableLocator()->get('LevelUnits');
        $this->PathLevels = TableRegistry::getTableLocator()->get('Pathlevel');
        $this->Units = TableRegistry::getTableLocator()->get('Units');
        $this->Users = TableRegistry::getTableLocator()->get('Users');

        /* Instantiate controller object to use to call it's methods */
        $this->learningPathCtrl = new LearningPathController();
    }

    /**
     */
    public function testGetPathDetails()
    {
        //----------------------------------------------------------------------
        // Arrange
        //----------------------------------------------------------------------

        // Create learning path for testing
        $learningPath = $this->Learningpaths->newEntity(
            [
                'id' => 2,
                'label' => 'Test Learning Path',
                'description' => 'Learning path to test code',
                'admin_access' => '1',
                'user_access' => '1'
            ]
        );
        $learningPath = $this->Learningpaths->save($learningPath);

        // Create some levels out of order
        $level1 = $this->Levels->newEntity(
            [
                'name' => 'Level 1',
                'description' => 'This is level 1',
                'image_id' => null
            ]
        );
        $level3 = $this->Levels->newEntity(
            [
                'name' => 'Level 3',
                'description' => 'This is level 3',
                'image_id' => null
            ]
        );
        $level2 = $this->Levels->newEntity(
            [
                'name' => 'Level 2',
                'description' => 'This is level 2',
                'image_id' => null
            ]
        );
        $level1 = $this->Levels->save($level1);
        $level3 = $this->Levels->save($level3);
        $level2 = $this->Levels->save($level2);

        // Add levels to test learning path
        $pathLevel1 = $this->PathLevels->newEntity(
            [
                'learningpath_id' => $learningPath['id'],
                'level_id' => $level1['id'],
                'sequence' => 1
            ]
        );
        $pathLevel2 = $this->PathLevels->newEntity(
            [
                'learningpath_id' => $learningPath['id'],
                'level_id' => $level3['id'],
                'sequence' => 3
            ]
        );
        $pathLevel3 = $this->PathLevels->newEntity(
            [
                'learningpath_id' => $learningPath['id'],
                'level_id' => $level2['id'],
                'sequence' => 2
            ]
        );
        $this->PathLevels->save($pathLevel1);
        $this->PathLevels->save($pathLevel2);
        $this->PathLevels->save($pathLevel3);

        // Set up POST data
        $postData = [
            'user_id' => 1,
            'path_id' => $learningPath['id'],
            'type' => 'site'
        ];
        $user = $this->Users->get($postData['user_id']);

        //----------------------------------------------------------------------
        // Act
        //----------------------------------------------------------------------
        // Get learning path with levels ordered as in database
        $dbPath = $this->Learningpaths->get(
            $learningPath['id'],
            [
                'contain' => [
                    'Levels'
                ]
            ]
        );

        // Get learning path with levels ordered based on PathLevels.sequence
        list($status, $msg, $path) = TestHelpers::invokeMethod(
            $this->learningPathCtrl,
            'handleGetPathDetails',
            [$postData, $user]
        );

        //----------------------------------------------------------------------
        // Assert
        //----------------------------------------------------------------------
        // check dbPath
        $this->assertEquals($dbPath->id, $learningPath['id']);
        $this->assertEquals(count($dbPath->levels), 3);
        // assert levels are ordered by PathLevels.sequence
        $this->assertEquals($dbPath->levels[0]->name, 'Level 1');
        $this->assertEquals($dbPath->levels[1]->name, 'Level 3');
        $this->assertEquals($dbPath->levels[2]->name, 'Level 2');
        // check path
        $this->assertTrue($status);
        $this->assertEquals($path->id, $learningPath['id']);
        $this->assertEquals($path->label, $learningPath['label']);
        $this->assertEquals(count($path->levels), 3);
        // assert levels are ordered by PathLevels.sequence
        $this->assertEquals($path->levels[0]->name, 'Level 1');
        $this->assertEquals($path->levels[1]->name, 'Level 2');
        $this->assertEquals($path->levels[2]->name, 'Level 3');
    }
}
