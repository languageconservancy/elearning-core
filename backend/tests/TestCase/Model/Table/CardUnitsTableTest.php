<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CardUnitsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CardUnitsTable Test Case
 */
class CardUnitsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CardUnitsTable
     */
    public $CardUnits;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.CardUnits'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('CardUnits') ? [] : ['className' => CardUnitsTable::class];
        $this->CardUnits = TableRegistry::getTableLocator()->get('CardUnits', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->CardUnits);

        parent::tearDown();
    }

    /**
     * Test initialize method
     * @group ignore
     * @return void
     */
    public function testInitialize()
    {
        // $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     * @group ignore
     * @return void
     */
    public function testValidationDefault()
    {
        // $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getCardsByUnitId method
     * @return void
     */
    public function testGetCardsByUnitId()
    {
        $result = $this->CardUnits->getCardsByUnitId(-1);
        $this->assertCount(0, $result);

        $result = $this->CardUnits->getCardsByUnitId(134);
        $this->assertCount(10, $result);

        $result = $this->CardUnits->getCardsByUnitId(192);
        $this->assertCount(8, $result);
    }
}
