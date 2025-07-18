<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ClassroomUsersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ClassroomUsersTable Test Case
 */
class ClassroomUsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ClassroomUsersTable
     */
    public $ClassroomUsers;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ClassroomUsers',
        'app.Classrooms',
        'app.Users',
        'app.Roles'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('ClassroomUsers')
            ? [] : ['className' => ClassroomUsersTable::class];
        $this->ClassroomUsers = TableRegistry::getTableLocator()->get('ClassroomUsers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ClassroomUsers);

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
