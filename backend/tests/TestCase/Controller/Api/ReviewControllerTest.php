<?php

namespace App\Test\TestCase\Controller;

use App\Controller\Api\ReviewController;
use Cake\TestSuite\IntegrationTestCase;
use Cake\ORM\TableRegistry;
use App\Lib\UtilLibrary;
use App\Model\Table\ActivityTypesTable;
use App\Test\Helpers\TestHelpers;

/**
 * Database Tables Used:
 *   - ReviewQueues, PointReferences, CardUnits, Card, Cardcardgroup,
 *   - GlobalFires, ProgressTimers, ActivityTypes, Exercises.
 */
class ReviewControllerTest extends IntegrationTestCase
{
    protected const TEST_USER_ID = 1976;
    protected const CARD_TYPE_ID_WORD = 1;
    protected const CARD_TYPE_ID_PATTERN = 3;

    public $reviewCtrl;
    protected $client;

    public $fixtures = [
        'app.ReviewQueues',
        'app.ActivityTypes',
        'app.Cardtype',
        'app.Files',
        'app.Cards',
        'app.Exercises',
        'app.PointReferences',
        'app.Cardcardgroup',
        'app.CardUnits',
        'app.UserActivities',
    ];

    public function setUp(): void
    {
        parent::setUp();
        /* Load table fixtures (test database table based on live database tables */
        $this->ReviewQueues = TableRegistry::getTableLocator()->get('ReviewQueues');
        $this->ActivityTypes = TableRegistry::getTableLocator()->get('ActivityTypes');
        $this->CardTypes = TableRegistry::getTableLocator()->get('Cardtype');
        $this->Files = TableRegistry::getTableLocator()->get('Files');
        $this->Cards = TableRegistry::getTableLocator()->get('Cards');
        $this->Exercises = TableRegistry::getTableLocator()->get('Exercises');
        $this->PointReferences = TableRegistry::getTableLocator()->get('PointReferences');
        $this->CardCardGroup = TableRegistry::getTableLocator()->get('Cardcardgroup');
        $this->CardUnits = TableRegistry::getTableLocator()->get('CardUnits');
        $this->UserActivities = TableRegistry::getTableLocator()->get('UserActivities');
        /* Instantiate controller object to use to call it's methods */
        $this->reviewCtrl = new ReviewController();
    }

    public function testFindReviewCardInArrayForEachActivity()
    {
        /* Get first two activity types */
        $cardType = ActivityTypesTable::WORD_CARDS;
        $activities = $this->ActivityTypes->find()
            ->select(['id', 'global_skill', 'prompt_response_pairs_'
                . $cardType, 'exercise_type_' . $cardType, 'review_percentage_'
                . $cardType
            ])
            ->where(['id IN' => array(1, 2, 3, 4)])
            ->toArray();
        $this->assertCount(4, $activities);
        for ($i = 0; $i < count($activities); $i++) {
            $activities[$i]['card_type_id'] = self::CARD_TYPE_ID_WORD;
        }

        /* Get cards from ReviewQueues table */
        $userReviewQueue = $this->ReviewQueues->find()->toArray();
        $this->assertCount(440, $userReviewQueue);

        /* Success tests */
        /* 1: reading, l-i or l-e. Expect 4246 */
        /* 2: reading, i-l or e-l. Expect 2354 */
        $result = TestHelpers::invokeMethod(
            $this->reviewCtrl,
            'findReviewCardInArrayForEachActivity',
            array($activities, $userReviewQueue)
        );

        $this->assertNotNull($result);
        if ($result == null) {
            return;
        }

        /* Verify fields in each item in the result arrays */
        $cardIdsUsed = array();
        foreach ($result as $activityArray) {
            /* Check array items */
            $this->assertTrue(UtilLibrary::isValid($activityArray['prompt_type']));
            $this->assertTrue(UtilLibrary::isValid($activityArray['response_type']));
            $this->assertTrue(UtilLibrary::isValid($activityArray['card']));
            $this->assertTrue(UtilLibrary::isValid($activityArray['exercise_type']));

            /* Check card prompt and response fields */
            $promptTypeWord = UtilLibrary::convertPromptLetterToWord($activityArray['prompt_type']);
            $responseTypeWord = UtilLibrary::convertPromptLetterToWord($activityArray['response_type']);
            $this->assertTrue(UtilLibrary::isValid($activityArray['card'][$promptTypeWord]));
            $this->assertTrue(UtilLibrary::isValid($activityArray['card'][$responseTypeWord]));
            /* Make sure card hasn't been used yet */
            $this->assertTrue(!in_array($activityArray['card']['id'], $cardIdsUsed));
            $cardIdsUsed[] = $activityArray['card']['id'];
        }

        /* TEST NON-DUPLICATION OF CARD_IDS */

        /* Get cards from ReviewQueues table */
        $acceptedCardIds = array(3, 4);
        $userReviewQueue = $this->ReviewQueues->find()
            ->where(['card_id IN' => $acceptedCardIds])
            ->toArray();
        $this->assertCount(8, $userReviewQueue);

        /* Success tests */
        /* 1: reading, l-i or l-e. Expect 4246 */
        /* 2: reading, i-l or e-l. Expect 2354 */
        $result = TestHelpers::invokeMethod(
            $this->reviewCtrl,
            'findReviewCardInArrayForEachActivity',
            array($activities, $userReviewQueue)
        );

        $this->assertNotNull($result);
        if ($result == null) {
            return;
        }

        $this->assertTrue(count($result) <= 2);

        /* Verify fields in each item in the result arrays */
        $cardIdsUsed = array();
        foreach ($result as $activityArray) {
            /* Check array items */
            $this->assertTrue(UtilLibrary::isValid($activityArray['prompt_type']));
            $this->assertTrue(UtilLibrary::isValid($activityArray['response_type']));
            $this->assertTrue(UtilLibrary::isValid($activityArray['card']));
            $this->assertTrue(UtilLibrary::isValid($activityArray['exercise_type']));

            /* Check card prompt and response fields */
            $promptTypeWord = UtilLibrary::convertPromptLetterToWord($activityArray['prompt_type']);
            $responseTypeWord = UtilLibrary::convertPromptLetterToWord($activityArray['response_type']);
            $this->assertTrue(UtilLibrary::isValid($activityArray['card'][$promptTypeWord]));
            $this->assertTrue(UtilLibrary::isValid($activityArray['card'][$responseTypeWord]));
            /* Make sure card hasn't been used yet */
            $this->assertTrue(!in_array($activityArray['card']['id'], $cardIdsUsed));
            $cardIdsUsed[] = $activityArray['card']['id'];
        }
    }

    public function testCreateExerciseArrayFromActivityAndCard()
    {
        /* Get all activities */
        $allActivities = $this->ActivityTypes->getAllNotExcluded(ActivityTypesTable::WORD_CARDS)->toArray();
        for ($i = 0; $i < count($allActivities); $i++) {
            $allActivities[$i]['card_type_id'] = self::CARD_TYPE_ID_WORD;
        }
        /* Get user review cards via the review queue */
        $userReviewQueue = $this->ReviewQueues->find()
            ->join(['table' => 'cards', 'conditions' => 'cards.id = card_id'])
            ->toArray();
        $userReviewCards = array_column($userReviewQueue, 'card_id');
        $cardTypes = $this->CardTypes->find()->toArray();

        /* Match a card to each activity */
        $selectedActivityArrays = TestHelpers::invokeMethod(
            $this->reviewCtrl,
            'findReviewCardInArrayForEachActivity',
            array($allActivities, $userReviewQueue)
        );
        $numData = count($selectedActivityArrays);

        /* Create exercises array */
        $numFailures = 0;
        for ($i = 0; $i < count($selectedActivityArrays); $i++) {
            $card = $selectedActivityArrays[$i]['card'];
            $activity = $selectedActivityArrays[$i];
            $exerciseType = $selectedActivityArrays[$i]['exercise_type'];
            $promptType = $selectedActivityArrays[$i]['prompt_type'];
            $responseType = $selectedActivityArrays[$i]['response_type'];
            $skillType = $selectedActivityArrays[$i]['activity']['global_skill'];
            $cardTypeId = $card['card_type_id'];
            $cardType = $cardTypes[$card['card_type_id']]['title'];
            $cardType = $cardType == 'Verb' ? 'Word' : $cardType;

            /* Get point reference */
            $pointReference = $this->PointReferences->find()
                ->where(['exercise' => $selectedActivityArrays[$i]['exercise_type'],
                         'prompt_type' => $promptType,
                         'response_type' => $responseType,
                         'card_type' => $cardType,
                         'is_review_included' => '1'])
                ->first();

            /* Create expected result to compare with */
            $expectedResult = array(
                'card_id' => $card['id'],
                'skill' => $skillType,
                'combination' => array(
                    'exercise' => $exerciseType,
                    'prompt_type' => $promptType,
                    'response_type' => $responseType,
                    'card_type' => 'card', /* only one used previously */
                    'is_review_included' => '1',
                    'instruction' => $pointReference['instructions']
                )
            );

            /* Call method under test, then check against all possible options
               and make it matches one of them. */
            $result = TestHelpers::invokeMethod(
                $this->reviewCtrl,
                'createExerciseArrayFromActivityAndCard',
                array($selectedActivityArrays[$i])
            );

            /* Check result and break if it's a match */
            if (!$this->exerciseMiniArraysAreEqual($result, $expectedResult)) {
                $numFailures += 1;
            }
        }

        /* Make sure we had no failures */
        $this->assertEquals(0, $numFailures);
    }

    private function exerciseMiniArraysAreEqual($ex1, $ex2)
    {
        return (
            $ex1['card_id'] === $ex2['card_id'] &&
            $ex1['skill'] === $ex2['skill'] &&
            $ex1['combination'] === $ex2['combination']
        );
    }

    public function testGenerateTrueFalseExercise()
    {
        /* Get user review cards via the review queue */
        $userReviewQueue = $this->ReviewQueues->find()
            ->join(['table' => 'cards', 'conditions' => 'cards.id = card_id'])
            ->toArray();
        $userReviewCardIds = array_column($userReviewQueue, 'card_id');
        $instruction = 'instructions dummy';

        $expectedResult = array(
            'exercise_type' => 'truefalse',
            'instruction' => $instruction,
            'card_type' => 'card',
            'promteresponsetype' => 'i' . '-' . 'l',
            'promotetype' => 'i',
            'responsetype' => 'l',
            'question' => $this->getCardDetails(3),
            'response' => $this->getCardDetails(4)
        );
        $expectedResult['question']['exerciseOptions'] = [
            "prompt_preview_option" => 'i',
            "responce_preview_option" => 'l',
            "response_true_false" => "N"];
        $expectedResult['response']['exerciseOptions'] =
            $expectedResult['question']['exerciseOptions'];

        $expectedResultTwo = $expectedResult;
        $expectedResultTwo['response'] = $this->getCardDetails(3);
        $expectedResultTwo['question'] = $this->getCardDetails(3);
        $expectedResultTwo['question']['exerciseOptions'] = [
            "prompt_preview_option" => 'i',
            "responce_preview_option" => 'l',
            "response_true_false" => "Y"];
        $expectedResultTwo['response']['exerciseOptions'] =
            $expectedResultTwo['question']['exerciseOptions'];

        /* Create expected result to compare with */
        $functionInput = array(
            'card_id' => 3,
            'skill' => 'reading',
            'exercise' => 'truefalse',
            'prompt_type' => 'i',
            'response_type' => 'l',
            'card_type' => 'card', /* only one used previously */
            'is_review_included' => '1',
            'instruction' => $instruction
        );

        /* Call method under test, then check against all possible options
           and make it matches one of them. */
        $result = TestHelpers::invokeMethod(
            $this->reviewCtrl,
            'generateTrueFalseExercise',
            // array($functionInput, $userReviewCardIds));
            array($functionInput, array(3, 4), self::TEST_USER_ID)
        );

        $this->assertThat(true, $this->logicalOr(
            $this->exercisesAreEqual($result, $expectedResult, '1'),
            $this->exercisesAreEqual($result, $expectedResultTwo, '2')
        ));
    }

    public function testGenerateMultipleChoiceExercise()
    {
        /* Get user review cards via the review queue */
        $userReviewQueue = $this->ReviewQueues->find()
            ->join(['table' => 'cards', 'conditions' => 'cards.id = card_id'])
            ->toArray();
        $userReviewCardIds = array_column($userReviewQueue, 'card_id');
        $instruction = 'instructions dummy';

        $expectedResult = array(
            'exercise_type' => 'multiple-choice',
            'instruction' => $instruction,
            'card_type' => 'card',
            'promteresponsetype' => 'i' . '-' . 'l',
            'promotetype' => 'i',
            'responsetype' => 'l',
            'question' => $this->getCardDetails(3),
            'response' => $this->getCardDetails(3),
            'choices' => $this->getCardDetails(array(3, 4, 5, 7))
        );

        /* Create expected result to compare with */
        $functionInput = array(
            'card_id' => 3,
            'skill' => 'reading',
            'exercise' => 'truefalse',
            'prompt_type' => 'i',
            'response_type' => 'l',
            'card_type' => 'card', /* only one used previously */
            'is_review_included' => '1',
            'instruction' => $instruction
        );

        /* Call method under test, then check against all possible options
           and make it matches one of them. */
        $result = TestHelpers::invokeMethod(
            $this->reviewCtrl,
            'generateMultipleChoiceExercise',
            // array($functionInput, $userReviewCardIds));
            array($functionInput, array(3, 4, 5, 7), self::TEST_USER_ID)
        );

        $this->assertEquals(count($result['choices']), count($expectedResult['choices']));
        $this->assertTrue(
            $this->exercisesAreEqual($result, $expectedResult, '1')
        );
    }

    public function testRemoveCardIdsWithSameResponseAsPromptCard()
    {
        // Call method under test, then check against all possible options
        // and make it matches one of them.
        $optionCardIds = array(3, 6, 4, 5);

        $resultingCardIds = TestHelpers::invokeMethod(
            $this->reviewCtrl,
            'removeCardIdsWithSameResponseAsPromptCard',
            array(5, 'e', 'l', $optionCardIds)
        );

        $this->assertEquals(count($resultingCardIds), count($optionCardIds) - 1);
        $this->assertEquals($resultingCardIds, array(3, 4, 5));


        // Make sure it works when the prompt card ID isn't last
        $optionCardIds = array(3, 5, 4, 6);

        $resultingCardIds = TestHelpers::invokeMethod(
            $this->reviewCtrl,
            'removeCardIdsWithSameResponseAsPromptCard',
            array(5, 'e', 'l', $optionCardIds)
        );

        $this->assertEquals(count($resultingCardIds), count($optionCardIds) - 1);
        sort($resultingCardIds);
        $this->assertEquals($resultingCardIds, array(3, 4, 5));
    }

    public function testRemoveCardIdsWithSamePromptOrResponseAsOthers()
    {
        $cardIds = array(5000, 5001, 5002, 5003, 5004, 5005, 5006, 5007, 5008, 5009);

        $resultingCardIds = TestHelpers::invokeMethod(
            $this->reviewCtrl,
            'removeCardIdsWithSamePromptOrResponseAsOthers',
            array($cardIds, 'e', 'l')
        );

        $expectedCardIds = array(5000, 5002, 5003, 5004, 5005, 5006, 5007, 5008, 5009);
        sort($resultingCardIds);
        $this->assertEquals($resultingCardIds, $expectedCardIds);

        $resultingCardIds = TestHelpers::invokeMethod(
            $this->reviewCtrl,
            'removeCardIdsWithSamePromptOrResponseAsOthers',
            array($cardIds, 'i', 'a')
        );

        $expectedCardIds = array(5000, 5001, 5002, 5003, 5004, 5005, 5006, 5008);
        sort($resultingCardIds);
        $this->assertEquals($resultingCardIds, $expectedCardIds);
    }

    public function testGenerateFillInBlanksTypingExercise()
    {
        /* Get user review cards via the review queue */
        $userReviewQueue = $this->ReviewQueues->find()
            ->join(['table' => 'cards', 'conditions' => 'cards.id = card_id'])
            ->toArray();
        $userReviewCardIds = array_column($userReviewQueue, 'card_id');
        $instruction = 'instructions dummy';

        $expectedResult = array(
            'exercise_type' => 'fill_in_the_blanks',
            'instruction' => $instruction,
            'card_type' => 'card',
            'promteresponsetype' => 'a' . '-' . 'l',
            'promotetype' => 'a',
            'responsetype' => 'l',
            'question' => $this->getCardDetails(3),
        );
        $lakotaString = UtilLibrary::convertPhraseIntoArrayOfWords(
            $expectedResult['question']['lakota']
        );

        /* Create expected result to compare with */
        $functionInput = array(
            'card_id' => 3,
            'skill' => 'reading',
            'exercise' => 'truefalse',
            'prompt_type' => 'a',
            'response_type' => 'l',
            'card_type' => 'card', /* only one used previously */
            'is_review_included' => '1',
            'instruction' => $instruction
        );

        /* Call method under test, then check against all possible options
           and make it matches one of them. */
        $result = TestHelpers::invokeMethod(
            $this->reviewCtrl,
            'generateFillInBlanksTypingExercise',
            array($functionInput)
        );

        $this->assertTrue(
            $this->exercisesAreEqual($result, $expectedResult, '1')
        );
    }

    /**
     * @group ignore
     */
    public function testGenerateAnagramExercise()
    {
        /* Currently not testing cause it's so simple */
    }

    public function exercisesAreEqual($ex1, $ex2, $msg)
    {
        /* Compare main proprerties */
        if (
            $ex1['exercise_type'] === $ex2['exercise_type'] &&
            $ex1['instruction'] === $ex2['instruction'] &&
            $ex1['card_type'] === $ex2['card_type'] &&
            $ex1['promteresponsetype'] === $ex2['promteresponsetype'] &&
            $ex1['promotetype'] === $ex2['promotetype'] &&
            $ex1['responsetype'] === $ex2['responsetype']
        ) {
        } else {
            // fwrite(STDOUT, "main properties mismatch\n");
            return false;
        }
        /* Compare question properties */
        if (
            $ex1['question']['id'] != null && $ex2['question']['id'] != null &&
            $ex1['question']['id'] === $ex2['question']['id'] &&
            $ex1['question']['image_id'] === $ex2['question']['image_id'] &&
            $ex1['question']['audio'] === $ex2['question']['audio'] &&
            $ex1['question']['card_type_id'] === $ex2['question']['card_type_id'] &&
            $ex1['question']['lakota'] === $ex2['question']['lakota'] &&
            $ex1['question']['english'] === $ex2['question']['english'] &&
            $ex1['question']['gender'] === $ex2['question']['gender'] &&
            $ex1['question']['include_review'] === $ex2['question']['include_review'] &&
            $ex1['question']['is_active'] === $ex2['question']['is_active']
        ) {
        } else {
            // fwrite(STDOUT, "question properties mismatch\n");
            $e = $ex1['question']['id'] . ", " . $ex2['question']['id'] . " | " .
                $ex1['question']['image_id'] . ", " . $ex2['question']['image_id'] . " | " .
                $ex1['question']['audio'] . ", " . $ex2['question']['audio'] . " | " .
                $ex1['question']['card_type_id'] . ", " . $ex2['question']['card_type_id'] . " | " .
                $ex1['question']['lakota'] . ", " . $ex2['question']['lakota'] . " | " .
                $ex1['question']['english'] . ", " . $ex2['question']['english'] . " | " .
                $ex1['question']['gender'] . ", " . $ex2['question']['gender'] . " | " .
                $ex1['question']['include_review'] . ", " . $ex2['question']['include_review'] . " | " .
                $ex1['question']['is_active'] . ", " . $ex1['question']['is_active'] . "\n";
            var_dump($e);
            return false;
        }
        /* Compare exerciseOptions (for truefalse exercises) */
        if (UtilLibrary::isValid($ex1['question']['exerciseOptions'])) {
            if (
                $ex1['question']['exerciseOptions']['prompt_preview_option']
                    === $ex2['question']['exerciseOptions']['prompt_preview_option'] &&
                $ex1['question']['exerciseOptions']['responce_preview_option']
                    === $ex2['question']['exerciseOptions']['responce_preview_option'] &&
                $ex1['question']['exerciseOptions']['response_true_false']
                    === $ex2['question']['exerciseOptions']['response_true_false']
            ) {
            } else {
                // fwrite(STDOUT, "question exerciseOptions properties mismatch\n");
                return false;
            }
        }
        /* Compare choices (for multiple-choice) */
        if (in_array('choices', $ex1)) {
            for ($i = 0; $i < count($ex1['choices']); $i++) {
                if ($ex1['choices'][$i]['id'] === $ex2['choices'][$i]['id']) {
                } else {
                    // fwrite(STDOUT, "choices properties mismatch\n");
                    return false;
                }
            }
        }
        /* Compare response properties */
        if (in_array('response', $ex1)) {
            if (
                $ex1['response']['id'] != null && $ex2['response']['id'] != null &&
                $ex1['response']['id'] === $ex2['response']['id'] &&
                $ex1['response']['image_id'] === $ex2['response']['image_id'] &&
                $ex1['response']['audio'] === $ex2['response']['audio'] &&
                $ex1['response']['card_type_id'] === $ex2['response']['card_type_id'] &&
                $ex1['response']['lakota'] === $ex2['response']['lakota'] &&
                $ex1['response']['english'] === $ex2['response']['english'] &&
                $ex1['response']['gender'] === $ex2['response']['gender'] &&
                $ex1['response']['include_review'] === $ex2['response']['include_review'] &&
                $ex1['response']['is_active'] === $ex2['response']['is_active'] &&
                $ex1['response']['image_id'] === $ex2['response']['image_id']
            ) {
            } else {
                // fwrite(STDOUT, "repsonse properties mismatch\n");
                return false;
            }
        }
        if (in_array('response', $ex1) && UtilLibrary::isValid($ex1['response']['exerciseOptions'])) {
            if (
                $ex1['response']['exerciseOptions']['prompt_preview_option']
                    === $ex2['response']['exerciseOptions']['prompt_preview_option'] &&
                $ex1['response']['exerciseOptions']['responce_preview_option']
                    === $ex2['response']['exerciseOptions']['responce_preview_option'] &&
                $ex1['response']['exerciseOptions']['response_true_false']
                    === $ex2['response']['exerciseOptions']['response_true_false']
            ) {
            } else {
                // fwrite(STDOUT, "response exerciseOptions properties mismatch\n");
                return false;
            }
        }

        return true;
    }

    /* BUG fix test. Wasn't catching the case when an activity type with
       and unimplemented exercise_type is not excluded in the database in
       the activity_types table and returns null from the generateExercise()
       function. */
    public function testGenerateExercise()
    {
        $userId = self::TEST_USER_ID;
        $unitId = 255;

        /* Get review cards from user's review queue */
        $userReviewQueue =
            TestHelpers::invokeMethod(
                $this->reviewCtrl,
                'getUserReviewCards',
                array($userId, $unitId)
            )->toArray();

        /* Get all activities */
        $wordActivities = $this->ActivityTypes->getAllNotExcluded(ActivityTypesTable::WORD_CARDS)->toArray();
        for ($i = 0; $i < count($wordActivities); ++$i) {
            $wordActivities[$i]['card_type_id'] = self::CARD_TYPE_ID_WORD;
        }
        $patternActivities = $this->ActivityTypes->getAllNotExcluded(ActivityTypesTable::PATTERN_CARDS)->toArray();
        for ($i = 0; $i < count($patternActivities); ++$i) {
            $patternActivities[$i]['card_type_id'] = self::CARD_TYPE_ID_PATTERN;
        }
        $activities = array_merge($wordActivities, $patternActivities);

        /* Create option cards ids array from all the available review cards */
        $optionCards = array_map(function ($e) {
            return is_object($e) ? $e->card_id : $e['card_id'];
        }, $userReviewQueue);
        $optionCards = array_values(array_unique($optionCards));

        $selectedActivityArrays =
            TestHelpers::invokeMethod(
                $this->reviewCtrl,
                'findReviewCardInArrayForEachActivity',
                array($activities, $userReviewQueue)
            );

        $exerciseCombos =
            TestHelpers::invokeMethod(
                $this->reviewCtrl,
                'createExerciseCombos',
                array($selectedActivityArrays, $userReviewQueue)
            );

        foreach ($selectedActivityArrays as $act) {
            $element =
                TestHelpers::invokeMethod(
                    $this->reviewCtrl,
                    'createExerciseArrayFromActivityAndCard',
                    array($act)
                );

            $newExercise =
                TestHelpers::invokeMethod(
                    $this->reviewCtrl,
                    'generateExercise',
                    array($optionCards, $element, $exerciseCombos, $userId)
                );

            $this->assertNotNull($newExercise);
            $this->assertNotNull($newExercise['exercise_type']);
        }
    }

    public function testGetReviewExercise()
    {
        $userId = self::TEST_USER_ID;
        $cardUnits = $this->CardUnits->find();
        $units = $cardUnits->distinct(['unit_id'])->toArray();
        $userReviewCards = $this->ReviewQueues->find()
            ->where(['user_id' => $userId])
            ->distinct(['card_id'])->toArray();
        $reviewCardIds = array_map(function ($e) {
            return is_object($e) ? $e->card_id : $e['card_id'];
        }, $userReviewCards);
        $reviewCardIds = array_values(array_unique($reviewCardIds));

        foreach ($units as $unit) {
            $unitId = $unit['unit_id'];
            // $numReviewCardsInUnit = $this->CardUnits->numReviewCardsInUnit($unitId);
            $reviewCardsInUnit = $this->CardUnits
                ->getReviewCardsInUnit($unitId)
                ->where(['card_id IN' => $reviewCardIds]);
            $numReviewCardsInUnit = count($reviewCardsInUnit->toArray());
            // fwrite(STDOUT, "unitId: $unitId, num: $numReviewCardsInUnit\n");
            if ($numReviewCardsInUnit >= 2) {
                $result = TestHelpers::invokeMethod(
                    $this->reviewCtrl,
                    'getReviewExercises',
                    array($userId, $unitId)
                );

                $expectedNumExercises = $numReviewCardsInUnit >= 2 ? 4 : 0;
                if ($numReviewCardsInUnit >= 2) {
                    $this->assertTrue(count($result) == 3 || count($result) == 4);
                } else {
                    $this->assertCount(0, $result);
                }
            }
        }
    }

    public function getCardDetails($cardIds)
    {
        $cards = $this->Cards->find()
            ->where(['Cards.id IN' => $cardIds])
            ->toArray();
        if ($cards == null) {
            return null;
        }

        if (
            (UtilLibrary::isCountable($cardIds) && count($cardIds) === 1) ||
            (!UtilLibrary::isCountable($cardIds))
        ) {
            /* Return as a single object, not an array */
            return $cards[0];
        } else {
            /* Else return as an array if input was an array,
               otherwise return single item if input was a single id */
            return $cards;
        }
    }
}
