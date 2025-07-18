<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ActivityTypesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ActivityTypesTable Test Case
 */
class ActivityTypesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ActivityTypesTable
     */
    public $ActivityTypes;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ActivityTypes'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('ActivityTypes')
            ? [] : ['className' => ActivityTypesTable::class];
        $this->ActivityTypes = TableRegistry::getTableLocator()->get('ActivityTypes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ActivityTypes);

        parent::tearDown();
    }

    /**
     * Test initialize method
     * @group ignore
     * @return void
     */
    public function testInitialize()
    {
    }

    /**
     * Test getAll method
     * @return void
     */
    public function testGetAll()
    {
        $all = $this->ActivityTypes->getAll();
        $this->assertCount($this->ActivityTypes->find()->count(), $all);
    }

    /**
     * Test getProbabilisticallyAccordingToReviewPercentages method
     *
     * @return void
     */
    public function testGetProbabilisticallyAccordingToReviewPercentages()
    {
        /* Success tests */
        $result = $this->ActivityTypes
            ->getProbabilisticallyAccordingToReviewPercentages(0, ActivityTypesTable::WORD_CARDS);
        $this->assertNull($result);

        $result = $this->ActivityTypes
            ->getProbabilisticallyAccordingToReviewPercentages(1, ActivityTypesTable::WORD_CARDS);
        $this->assertCount(1, $result);

        $result = $this->ActivityTypes
            ->getProbabilisticallyAccordingToReviewPercentages(8, ActivityTypesTable::WORD_CARDS);
        $this->assertCount(8, $result);

        /* Array of sums */
        $activityTypes = $this->ActivityTypes->getAllNotExcluded(ActivityTypesTable::WORD_CARDS)->toArray();
        $allActivities = $this->ActivityTypes->getAll();
        $sums = array_fill(0, $allActivities->count(), 0);
        $percentages = array_map(function ($e) {
            return is_object($e) ? $e->review_percentage_words : $e['review_percentage_words'];
        }, $activityTypes);
        $percentagesSum = array_sum($percentages);

        $numLoops = 1000;
        $numActivitiesPerCall = 4;
        $total = $numLoops * $numActivitiesPerCall;
        /* Run test so many times collecting statistics */
        for ($i = 0; $i < $numLoops; $i++) {
            $result = $this->ActivityTypes->getProbabilisticallyAccordingToReviewPercentages(
                $numActivitiesPerCall,
                ActivityTypesTable::WORD_CARDS
            );
            for ($j = 0; $j < $numActivitiesPerCall; $j++) {
                $sums[$result[$j]['id'] - 1] += 1;
            }
        }
        /* Check statistics of above test */
        $tolerance = 2.5;
        for ($i = 0; $i < count($sums); $i++) {
            $percentage = $sums[$i] / $total * 100;

            /* Multiplying by 100/sum because the sum of the percentages is not 100 */
            $act = $allActivities->toArray()[$i];

            $expectedPercentage = $act['review_percentage_words']
                * 100 / $percentagesSum * (!$act['exclude_words']);
            $this->assertThat($percentage, $this->logicalAnd(
                $this->lessThan($expectedPercentage + $tolerance),
                $this->greaterThan($expectedPercentage - $tolerance)
            ));
        }
    }
}
