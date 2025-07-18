<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\GradesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\GradesTable Test Case
 */
class GradesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var GradesTable
     */
    public $Grades;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Grades'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Grades') ? [] : ['className' => GradesTable::class];
        $this->Grades = TableRegistry::getTableLocator()->get('Grades', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Grades);

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
}
