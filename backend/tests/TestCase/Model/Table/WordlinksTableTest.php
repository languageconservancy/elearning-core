<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WordlinksTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WordlinksTable Test Case
 */
class WordlinksTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WordlinksTable
     */
    public $Wordlinks;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Wordlinks',
        'app.Classrooms',
        'app.Schools'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Wordlinks') ? [] : ['className' => WordlinksTable::class];
        $this->Wordlinks = TableRegistry::getTableLocator()->get('Wordlinks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Wordlinks);

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
