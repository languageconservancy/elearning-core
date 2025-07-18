<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PathlevelTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PathlevelTable Test Case
 */
class PathlevelTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PathlevelTable
     */
    public $PathLevels;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Pathlevel',
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
            ->exists('Pathlevel') ? [] : ['className' => PathlevelTable::class];
        $this->PathLevels = TableRegistry::getTableLocator()->get('Pathlevel', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->PathLevels);

        parent::tearDown();
    }

    public function testResequencePathLevels()
    {
        //----------------------------------------------------------------------
        // Arrange
        //----------------------------------------------------------------------
        $pathId = 5;
        $otherPathId = 17;
        $otherLevelId = 4;
        // Create path levels with spaced sequence numbers
        $pathLevels = [
            [
                'id' => 1,
                'learningpath_id' => $pathId,
                'level_id' => 1,
                'sequence' => 1
            ],
            [
                'id' => 2,
                'learningpath_id' => $pathId,
                'level_id' => 2,
                'sequence' => 3
            ],
            [
                'id' => 3,
                'learningpath_id' => $pathId,
                'level_id' => 3,
                'sequence' => 4
            ],
            [
                'id' => 4,
                'learningpath_id' => $pathId,
                'level_id' => 4,
                'sequence' => 9
            ],
            [
                'id' => 5,
                'learningpath_id' => $pathId,
                'level_id' => 5,
                'sequence' => 21
            ],
            [
                'id' => 6,
                'learningpath_id' => $otherPathId,
                'level_id' => $otherLevelId,
                'sequence' => 1
            ]
        ];
        // Save level unit entities to test database
        $newEntities = $this->PathLevels->newEntities($pathLevels);
        $result = $this->PathLevels->saveMany($newEntities);
        if (empty($result)) {
            fwrite(STDERR, "Failed to save pathLevels");
        }

        //----------------------------------------------------------------------
        // Act
        //----------------------------------------------------------------------
        // Resequence the non-contiguous path levels
        $status = $this->PathLevels->resequence($pathId);

        // Get the path levels that we just resequenced from the database
        $sequencedPathLevels = $this->PathLevels->find()
            ->where(['learningpath_id' => $pathId])
            ->all();

        // Get unrelated level unit
        $unrelatedPathLevels = $this->PathLevels->find()
            ->where(['learningpath_id' => $otherPathId])
            ->all()
            ->toArray();

        //----------------------------------------------------------------------
        // Assert
        //----------------------------------------------------------------------
        // Result should not be false
        $this->assertTrue($status);
        $this->assertNotFalse($sequencedPathLevels);
        $this->assertEquals(count($sequencedPathLevels), count($pathLevels) - 1);
        // Assert sequence is now contiguous
        foreach ($sequencedPathLevels as $idx => $slu) {
            $this->assertEquals($slu->id, ($idx + 1));
            $this->assertEquals($slu->sequence, ($idx + 1));
        }

        // Assert path level from other path was not touched
        $this->assertCount(1, $unrelatedPathLevels);
        $this->assertNotFalse($unrelatedPathLevels);
        $this->assertTrue(!empty($unrelatedPathLevels));
        $this->assertEquals($unrelatedPathLevels[0]->id, end($pathLevels)['id']);
        $this->assertEquals($unrelatedPathLevels[0]->learningpath_id, $otherPathId);
        $this->assertEquals($unrelatedPathLevels[0]->level_id, $otherLevelId);
        $this->assertEquals($unrelatedPathLevels[0]->sequence, 1);
    }
}
