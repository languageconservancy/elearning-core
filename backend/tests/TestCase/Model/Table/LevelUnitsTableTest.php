<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LevelUnitsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\LevelUnitsTable Test Case
 */
class LevelUnitsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\LevelUnitsTable
     */
    public $LevelUnits;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.LevelUnits',
        'app.Levels',
        'app.Units'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()
            ->exists('LevelUnits') ? [] : ['className' => LevelUnitsTable::class];
        $this->LevelUnits = TableRegistry::getTableLocator()->get('LevelUnits', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->LevelUnits);

        parent::tearDown();
    }

    public function testResequenceLevelUnits()
    {
        //----------------------------------------------------------------------
        // Arrange
        //----------------------------------------------------------------------
        $pathId = 5;
        $levelId = 2;
        $otherLevelId = 4;
        $otherUnitId = 4;
        // Create level units with spaced sequence numbers
        $levelUnits = [
            [
                'id' => 1,
                'learningpath_id' => $pathId,
                'level_id' => $levelId,
                'unit_id' => '1',
                'optional' => '1',
                'sequence' => '1'
            ],
            [
                'id' => 2,
                'learningpath_id' => $pathId,
                'level_id' => $levelId,
                'unit_id' => '2',
                'optional' => '1',
                'sequence' => '3'
            ],
            [
                'id' => 3,
                'learningpath_id' => $pathId,
                'level_id' => $levelId,
                'unit_id' => '3',
                'optional' => '1',
                'sequence' => '4'
            ],
            [
                'id' => 4,
                'learningpath_id' => $pathId,
                'level_id' => $levelId,
                'unit_id' => '4',
                'optional' => '1',
                'sequence' => '9'
            ],
            [
                'id' => 5,
                'learningpath_id' => $pathId,
                'level_id' => $levelId,
                'unit_id' => '5',
                'optional' => '1',
                'sequence' => '21'
            ],
            [
                'id' => 6,
                'learningpath_id' => $pathId,
                'level_id' => $otherLevelId,
                'unit_id' => $otherUnitId,
                'optional' => '1',
                'sequence' => '1'
            ]
        ];
        // Save level unit entities to test database
        $newEntities = $this->LevelUnits->newEntities($levelUnits);
        $result = $this->LevelUnits->saveMany($newEntities);
        if (empty($result)) {
            fwrite(STDERR, "Failed to save levelUnits");
        }

        //----------------------------------------------------------------------
        // Act
        //----------------------------------------------------------------------
        // Resequence the non-contiguous level units
        $status = $this->LevelUnits->resequence($pathId, $levelId);

        // Get the level units that we just resequenced from the database
        $sequencedLevelUnits = $this->LevelUnits->find()
            ->where([
                'learningpath_id' => $pathId,
                'level_id' => $levelId
            ])
            ->all();

        // Get unrelated level unit
        $unrelatedLevelUnit = $this->LevelUnits->find()
            ->where([
                'learningpath_id' => $pathId,
                'level_id' => $otherLevelId
            ])
            ->first();

        //----------------------------------------------------------------------
        // Assert
        //----------------------------------------------------------------------
        // Result should not be false
        $this->assertTrue($status);
        $this->assertNotFalse($sequencedLevelUnits);
        $this->assertEquals(count($sequencedLevelUnits), count($levelUnits) - 1);
        // Assert sequence is now contiguous
        foreach ($sequencedLevelUnits as $idx => $slu) {
            $this->assertEquals($slu->id, ($idx + 1));
            $this->assertEquals($slu->sequence, ($idx + 1));
        }

        // Assert level unit from other level was not touched
        $this->assertNotFalse($unrelatedLevelUnit);
        $this->assertTrue(!empty($unrelatedLevelUnit));
        $this->assertEquals($unrelatedLevelUnit->id, end($levelUnits)['id']);
        $this->assertEquals($unrelatedLevelUnit->learningpath_id, $pathId);
        $this->assertEquals($unrelatedLevelUnit->level_id, $otherLevelId);
        $this->assertEquals($unrelatedLevelUnit->unit_id, $otherUnitId);
        $this->assertEquals($unrelatedLevelUnit->sequence, 1);
    }
}
