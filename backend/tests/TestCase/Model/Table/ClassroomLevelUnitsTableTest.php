<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ClassroomLevelUnitsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ClassroomLevelUnitsTable Test Case
 */
class ClassroomLevelUnitsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ClassroomLevelUnitsTable
     */
    public $ClassroomLevelUnits;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ClassroomLevelUnits',
        'app.LevelUnits',
        'app.Classrooms'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('ClassroomLevelUnits')
            ? [] : ['className' => ClassroomLevelUnitsTable::class];
        $this->ClassroomLevelUnits = TableRegistry::getTableLocator()->get('ClassroomLevelUnits', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ClassroomLevelUnits);

        parent::tearDown();
    }

    /**
     * Test initialize method
     * @group ignore
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     * @group ignore
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     * @group ignore
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
