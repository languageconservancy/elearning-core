<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SchoolUsersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SchoolUsersTable Test Case
 */
class SchoolUsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SchoolUsersTable
     */
    public $SchoolUsers;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.SchoolUsers',
        'app.Schools',
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
        $config = TableRegistry::getTableLocator()->exists('SchoolUsers')
            ? [] : ['className' => SchoolUsersTable::class];
        $this->SchoolUsers = TableRegistry::getTableLocator()->get('SchoolUsers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->SchoolUsers);

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
