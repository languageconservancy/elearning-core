<?php

namespace App\Controller\Api;

use App\Lib\UtilLibrary;
use App\Model\Table\ActivityTypesTable; // Needed for enums
use Cake\Log\Log;

const MIN_CARDS_FOR_REVIEW = 2;
const NUM_EXERCISES_TO_SEND = 4;
const CARD_TYPE_ID_WORD = 1;
const CARD_TYPE_ID_PATTERN = 3;

/**
 * Get all cards
 */
class ReviewController extends AppController
{
    private $_cardTypes = array();
    /*
     * 1. get all card order by and skill from review deck which is included
        in review(cardid, skilltype, xp_avg)
     * 2. get combination of exercise type based on contain of the card.
        (exercise type, promt type, response type)
     * 3. get exercise type from point reference table filter based on non
        zero entry (card pattern,exercise type, promt type, response type, skill_type)
        (exercise type, promt type, response type)
     * 4. get exercise type from point reference table based on xp_avg lower
        than 3 (exercise type, promt type, response type)
     * 5. randomly pick one of the exercise and generate.(we have generated 5
        exercise at once for smooth ui).
     */

    /*
    1. when attending any card from lesson and exercse the reading writing
        speaking and listening points inserted in review queue table based
        on PointReferences table.
    2. review card is fetched based on assending order of sort column.
    3. sort equation is calculated before inserting in review queue table.
    */

    /**
     * Initialize the class and its parent.
     * Load required tables from the database.
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->_cardTypes = $this->getCardtypeTable()->find()->all()->combine('id', 'title')->toArray();
    }

    /**
     * Receives request data from the frontend, during a user's review session,
     * finds all the review cards for the user, with no limit on the number of cards
     * and including any unit they've learned/reviewed before. Based on the result
     * respond to frontend with whether or not there are cards to review or not.
     * If there are then also send info about the time spent learning and reviewing,
     * as well as the number of fire and streak days.
     */
    public function getReviewHaveOrNot()
    {
        /* Get request data from the frontend */
        $data = $this->request->getData();
        $this->validateRequest($data, ['user_id']);
        $userId = $data['user_id'];

        /* Get all review cards for this user from all units so far to check if there are cards to review. */
        $cards = $this->getReviewCardsDetailsFromDeck($userId);
        $response = array();
        if (empty($cards->toArray())) {
            /* If no review cards were retreived, specify so in the response object. */
            $response['haveReviewExercise'] = false;
        } else {
            /* Else if there are review cards, specify so in the response object, and also fill in the
             fire and streak info. */
            $response['haveReviewExercise'] = true;
            $FireData = $this->getGlobalFiresTable()->find('all')
                ->where(['user_id' => $userId])
                ->first();
            $response['FireData'] = array(
                "fire_days" => $FireData['fire_days'] ?? 0,
                "streak_days" => $FireData['streak_days'] ?? 0,
            );

            /* Get values for the time spent reviewing and learning. This includes multiple instances of the same
             item depending on how many times a user has reviewed the same unit/path combo. */
            $reviewTimer = $this->getProgressTimersTable()
                ->find('all', ['conditions' => ['user_id' => $userId, 'timer_type' => 'review']]);
            $pathTimer = $this->getProgressTimersTable()
                ->find('all', ['conditions' => ['user_id' => $userId, 'timer_type' => 'path']]);

            /* Update user progress data in the response object by summing all the minute_spent's across the
             items found above for both reivew and path timers. */
            $response['progressData'] = array(
                "pathTimeMinute" => $pathTimer->all()->sumOf('minute_spent'),
                "reviewTimeMinute" => $reviewTimer->all()->sumOf('minute_spent')
            );
        }
        /* Send the response data back to the frontend */
        $this->sendApiData(true, 'Result return successfully.', $response);
    }

    /**
     * Get all the review cards from the ReviewQueuesTable for the current user,
     * ordered using the 'sort' column, and only include those cards that are meant to be reviewed.
     * Only get a max number of cards to review according to the limit passed in (if it is specified),
     * and only those cards from the specified unit (if it is specified).
     * @param $userId ID of user
     * @param $limit Max number of rows to return from the query
     * @param $unitCards Cards in the unit to run query on
     * @return cards Cake/ORM/Query object
     */
    private function getReviewCardsDetailsFromDeck($userId, $limit = null, $unitCards = null)
    {
        /* Create query WHERE conditions, associations to eager load (Cards array), and order them by 'sort' field */
        $ReviewOptions = array(
            'conditions' => array(
                'user_id IS' => $userId
            ),
            'contain' => array('Cards'),
            'order' => 'sort'
        );
        if ($limit != null) {
            /* Add limit to number of rows to return from table query */
            $ReviewOptions['limit'] = $limit;
        }
        if ($unitCards != null) {
            /* Add WHERE condition to query */
            $ReviewOptions['conditions']['card_id IN'] = $unitCards;
        }

        /* Add WHERE condition to query: Find only cards that are intended to be included in the review session */
        $ReviewOptions['conditions']['Cards.include_review'] = '1';

        /* Submit query and return cards found */
        $cards = $this->getReviewQueuesTable()->find('all', $ReviewOptions);

        return $cards;
    }

    /**
     * Creates four prompt-response exercises and returns them to the requester with the following steps.
     * - Stack the percentages in the activity_type_percentages table up select a random number in between 0 and the
     * sum of the percentages.
     * - Where the random number falls in the stack determines the activity type
     * - Given the activity type and global skill, find the first row in the review_queues table that has that skill
     * and the required prompt and response types.
     * NOTE: Updated version. See getReviewExerciseObsolete() for old version
     */
    public function getReviewExercise()
    {
        /* Get the data passed from the frontend */
        $requestData = $this->request->getData();
        $userId = $requestData['user_id']; /* Current user */
        $unitId = $requestData['unit_id'] ?? null;
        $exercises = $this->getReviewExercises($userId, $unitId);
        if (!isset($exercises[0])) {
            $exercises = $this->getReviewExercises($userId, null);
        }
        $this->sendExercisesToFrontend($exercises);
    }

    private function getReviewExercises($userId, $unitId)
    {
        $exercises = array(); /* Array to be send to frontend */
        $numExercisesToCreate = 8; /* number of exercises to return to the frontend */
        // TODO maybe put this in the database so we can easily change it

        /*---------------------------*/
        /* Get All User Review Cards */
        /*---------------------------*/
        /* Get review cards for the user and the speficic unit (if it's defined. If it's not,
         then user is doing a level review as opposed to a unit review) */
        $userReviewQueue = $this->getUserReviewCards($userId, $unitId);

        /*---------------*/
        /* RETURN Branch */
        /*---------------*/
        if ($userReviewQueue === null) {
            return $exercises;
        }
        $userReviewQueue = $userReviewQueue->toArray();

        /*--------------------------------------------*/
        /* Select Activity Types Based On Percentages */
        /*--------------------------------------------*/
        /* Which activity types get selected narrows down what possible cards will work with them
            because each activity type has one or more valid prompt-response pairs */
        // TODO should we get more items here to use in case no cards exist that match the first 4?
        $wordActivities = $this->getActivityTypesTable()->getProbabilisticallyAccordingToReviewPercentages(
            $numExercisesToCreate,
            ActivityTypesTable::WORD_CARDS
        );
        $wordActCount = count($wordActivities);
        for ($i = 0; $i < $wordActCount; $i++) {
            $wordActivities[$i]['card_type_id'] = CARD_TYPE_ID_WORD;
        }
        $patternActivities = $this->getActivityTypesTable()->getProbabilisticallyAccordingToReviewPercentages(
            $numExercisesToCreate,
            ActivityTypesTable::PATTERN_CARDS
        );
        $patternActCount = count($patternActivities);
        for ($i = 0; $i < $patternActCount; $i++) {
            $patternActivities[$i]['card_type_id'] = CARD_TYPE_ID_PATTERN;
        }
        $activities = array_merge($wordActivities, $patternActivities);
        // ActivityTypesTable::printActivities($activities);

        /*---------------------------*/
        /* Create Option Cards Array */
        /*---------------------------*/
        /* Create option cards ids array from all the available review cards */
        $optionCards = array_map(function ($e) {
            return is_object($e) ? $e->card_id : $e['card_id'];
        }, $userReviewQueue);
        $optionCards = array_values(array_unique($optionCards));

        /*--------------------------------*/
        /* Select Cards For Each Activity */
        /*--------------------------------*/
        /* For each activity selected, go through the user review cards and find the first
            review card that has the same skill type and prompt and reponse items */
        $selectedActivityArrays = $this->findReviewCardInArrayForEachActivity(
            $activities,
            $userReviewQueue
        );

        if ($selectedActivityArrays != null) {
            /*------------------------------------------------------*/
            /* Sort Activity Arrays by sort Column and Take First 4 */
            /*------------------------------------------------------*/
            usort($selectedActivityArrays, function ($a, $b) {
                return $a['sort'] - $b['sort'];
            });

            /*-----------------------------------------------------*/
            /* Create exercise combos for match-the-pair exercises */
            /*-----------------------------------------------------*/
            $exerciseCombos = $this->createExerciseCombos($selectedActivityArrays, $userReviewQueue);

            /*--------------------------------------------------------*/
            /* With Activities and Cards Selected, Generate Exercises */
            /*--------------------------------------------------------*/
            $numExercisesCreated = 0;
            foreach ($selectedActivityArrays as $selectedActivityArray) {
                /* Create exercise array from activity and card */
                $element = $this->createExerciseArrayFromActivityAndCard($selectedActivityArray);
                /* Generate an exercise from it and add it to the array that goes to the frontend */
                if ($element != null) {
                    $newExercise = $this->generateExercise($optionCards, $element, $exerciseCombos, $userId);
                    if (!empty($newExercise)) {
                        /* Add created exercise to the output array */
                        array_push($exercises, $newExercise);

                        /* If required number of exercises have been created, break out and return them */
                        if (++$numExercisesCreated >= NUM_EXERCISES_TO_SEND) {
                            break;
                        }
                    }
                }
            }
        }

        /*----------------------------*/
        /* Send Exercises To Frontend */
        /*----------------------------*/
        return $exercises;
    }


    private function getUserReviewCards($userId, $unitId)
    {
        /* Get the frontend request data */
        $limit = null; /* Currently not used */

        /* Get list of all non-duplicate cards in this unit */
        // $unitCards = isset($unitId) ? $this->getCardUnitsTable()->getCardsByUnitId($unitId) : null;
        $unitCards = isset($unitId) ? $this->getCardsByUnitId($unitId) : null;
        if (isset($unitId) && count($unitCards) < MIN_CARDS_FOR_REVIEW) {
            /* This unit has no review cards */
            return null;
        }

        /*--------------------------------*/
        /*  Create deck of review cards   */
        /*--------------------------------*/
        /* Get all review cards from review queue for this user with limit on number of cards, if set,
         and from specified unit, if specified, otherwise from any unit user has completed. */
        // return $this->getReviewQueuesTable()->getReviewCardsDetailsFromDeck($userId, $limit, $unitCards);
        return $this->getReviewCardsDetailsFromDeck($userId, $limit, $unitCards);
    }

    /**
     * Get array of all unique cards in the specified unit.
     * @param $UnitId ID of unit to get cards from
     * @return array of non-duplicate cards in the specified unit
     */
    private function getCardsByUnitId($unitId)
    {
        /* Create query items */
        $unitOptions = array(
            'conditions' => array(
                'unit_id' => $unitId
            ),
            'keyField' => 'id',
            'valueField' => 'card_id'
        );
        /* Get array of cards in specified unit with just the fields 'id' and 'card_id' */
        $unitDetails = $this->getCardUnitsTable()->find('list', $unitOptions)->toArray();
        /* Get rid of all duplicate cards */
        return array_values(array_unique($unitDetails));
    }

    /**
     * Search through the query array result from the ReviewQueues table for the current user,
     * in order of 'sort' (spaced-repitition algorithm value) to find one card for each activity
     * that has the skill, prompt and response corresponding to the activity.
     * The only way a suitable card won't be found for a selected activity type is if that selected
     * activity type contains a prompt-response pair with audio or image and we don't yet have any
     * cards with those fields valid.
     * // TODO one option would be to get 10 or 15 activities and wait till 4 are valid
     * @param $activities Array of activities previously selected for the current request for review exercises.
     * @param $userReviewCards Query result array of ReviewQueues items corresponding to the current user
     */
    private function findReviewCardInArrayForEachActivity($activities, $userReviewQueue)
    {
        $selectedActivityArrays = array();

        /* Loop through selected activities and find an appropriate card for each, with prompt
         and response types that work for the card and prevent having the same type for the
         prompt and the response. */
        foreach ($activities as $activity) {
            $promptsAndResponses = $this->getActivityTypesTable()->getPromptAndResponseWords($activity);
            if ($promptsAndResponses == null) {
                return null;
            }
            $cardType = $this->_cardTypes[$activity['card_type_id']];
            $cardType = $cardType == 'Verb' ? 'Word' : $cardType;
            $suffix = $cardType == 'Word' ? ActivityTypesTable::WORD_CARDS : ActivityTypesTable::PATTERN_CARDS;

            /* Option 1 loop through cards to find a matching one */
            foreach ($userReviewQueue as $index => $reviewQueueElement) {
                /* Get card in the review queue element */
                $card = $this->getCardTable()->get($reviewQueueElement['card_id']);
                /* Look for a card with the skill type matching that of the activity type */
                if (
                    $reviewQueueElement['skill_type'] === $activity['global_skill'] &&
                    $card['card_type_id'] === $activity['card_type_id']
                ) {
                    /* Get prompt-response pairs that are valid for the card */
                    $validPairs = $this->getValidPromptResponsePairsForCard(
                        $promptsAndResponses['prompt_types'],
                        $promptsAndResponses['response_types'],
                        $card
                    );
                    if (empty($validPairs)) {
                        break;
                    }

                    /* Choose random pair from valid prompt-response pairs */
                    $pair = UtilLibrary::getRandomItemFromArray($validPairs);

                    /* Create activity array */
                    $activityArray = array(
                        'activity' => $activity,
                        'prompt_type' =>
                            UtilLibrary::convertPromptWordToLetter($pair['prompt_type']),
                        'response_type' =>
                            UtilLibrary::convertPromptWordToLetter($pair['response_type']),
                        'card' => $card,
                        'exercise_type' => $activity['exercise_type_' . $suffix],
                        'sort' => $reviewQueueElement['sort']
                    );

                    /* Add this card and activity to the output array */
                    array_push($selectedActivityArrays, $activityArray);
                    /* Remove this card from the options so it doesn't get chosen again */
                    unset($userReviewQueue[$index]);
                    break;
                }
            }
        }

        /* Return array with chosen activities and chosen cards */
        return $selectedActivityArrays;
    }

    private function getValidPromptResponsePairsForCard($promptTypeWords, $responseTypeWords, $card)
    {
        $validPairs = array();
        /* Go through prompt-response pairs to find a card that contains it */
        foreach ($promptTypeWords as $promptType) {
            foreach ($responseTypeWords as $responseType) {
                if ($promptType != $responseType) {
                    if (
                        UtilLibrary::isValid($card[$promptType]) &&
                        UtilLibrary::isValid($card[$responseType])
                    ) {
                        $element = array(
                            'prompt_type' => $promptType,
                            'response_type' => $responseType);
                        $validPairs[] = $element;
                    }
                }
            }
        }
        return $validPairs;
    }

    /**
     * Search through the ReviewQueues table for the current user, in order of
     * 'sort' (spaced-repitition algorithm value) to find one card for each
     * activity that has the skill, prompt and response corresponding to the activity.
     * @param $activities Array of activities previously selected for the
     *  current request for review exercises.
     */
    // private function findReviewCardInTableForEachActivity($activities) {
    //     $chosenCards = array();
    //     $usedCardIds = array();

    //     foreach ($activities as $activity) {
    //         $promptAndResponse = $this->getRandomPromptAndResponseFromActivity($activity);
    //         $promptType = $promptAndResponse['prompt'];
    //         $responseType = $promptAndResponse['response'];
    //         $skill_type = $activity['global_skill'];

    //         /* Option 2 do a database query in ReviewQueues table for a card of the current user that is
    //            of the correct skill type, and for which the prompt and response values required by the activity type
    //            are valid. */
    //         $card = $this->getReviewQueuesTable()->find()
    //             ->join([
    //                 'c' => [
    //                     'table' => 'cards',
    //                     'type' => 'LEFT',
    //                     'conditions' => 'c.id = ReviewQueues.card_id']])
    //             ->order(['order' => 'ASC'])
    //             ->where([
    //                 'skill_type' => $skill_type,
    //                 'user_id' => $userId,
    //                 $promptType.' IS NOT' => null,
    //                 $responseType.' IS NOT' => null],
    //                 'card_id NOT IN' => $usedCardIds)
    //             ->first();

    //         if ($card != null) {
    //             /* Add this card to the output array */
    //             array_push($chosenCards, $reviewCard);
    //             /* Add card id to the black list so we don't pick it again this round */
    //             array_push($usedCardIds, $reviewCard['card_id']);
    //         }
    //     }

    //      return $chosenCards;
    // }
    /**
     * Create array of arrays where each internal array contains
     * card_id and skill.
     */
    private function createExerciseCombos($activities, $reviewQueue)
    {
        $exerciseCombos = array();
        $activitySkills = array();
        /* For each match-the-pair activity that was randomly selected,
            store the skill type so that we can target just review queue elements
            with those skill types */
        foreach ($activities as $activity) {
            if ($activity['exercise_type'] == 'match-the-pair') {
                $activitySkills[] = $activity['activity']['global_skill'];
            }
        }

        /* If no match-the-pair exercises were chosen, just return */
        if (empty($activitySkills)) {
            return null;
        }

        /* For each element in the review queue, if it has one of the match-the-pair activity
            skill types, then store it in the array that gets used later */
        foreach ($reviewQueue as $reviewQueueElement) {
            if (in_array($reviewQueueElement['skill_type'], $activitySkills)) {
                $element = array();
                $element['card_id'] = $reviewQueueElement['card_id'];
                $element['skill'] = $reviewQueueElement['skill_type'];
                $exerciseCombos[] = $element;
            }
        }

        return $exerciseCombos;
    }

    /**
     * Takes an activity and a review queue element and converts them into a
     * an exercise array with the help of other tables.
     * $activity An activity array
     */
    private function createExerciseArrayFromActivityAndCard($activityArray)
    {
        if (!UtilLibrary::isValid($activityArray)) {
            Log::error("Invalid activity");
            return null;
        }
        $element = array();
        $card = $activityArray['card'];
        // TOOD maybe make sure they both exist before choosing random one
        // $exerciseType = $this->getActivityTypesTable()->getRandomExerciseType($activity['activity']);
        $exerciseType = $activityArray['exercise_type'];
        // TODO rethink if ->first() is the right thing here
        $promptType = $activityArray['prompt_type'];
        $responseType = $activityArray['response_type'];
        $cardType = $this->_cardTypes[$card['card_type_id']];
        $cardType = $cardType == 'Verb' ? 'Word' : $cardType;

        // TODO rethink if ->first() is the right thing here
        $pointReference = $this->getPointReferencesTable()->find()
            ->where(['exercise' => $exerciseType,
                'prompt_type' => $promptType,
                'response_type' => $responseType,
                'card_type' => $cardType,
                'is_review_included' => '1'])
            ->first();
        if (UtilLibrary::isValid($pointReference)) {
            $element['card_id'] = $card['id'];
            $element['skill'] = $activityArray['activity']['global_skill'];
            $element['combination'] = array(
                'exercise' => $exerciseType,
                'prompt_type' => $promptType,
                'response_type' => $responseType,
                'card_type' => 'card',
                'is_review_included' => '1',
                'instruction' => $pointReference['instructions']);
        } else {
            Log::error("Invalid pointReference " . $exerciseType . ", "
                . $promptType . ", " . $responseType . ", " . $cardType);
            $element = null;
        }

        return $element;
    }

    /**
     * Creates a prompt-response review exercise depending on the randomly
     * chosen type of exercise, and returns it.
     * @param $cards array of unique card IDs corresponding to the cards
     *  included in the array of prompt-response exercises
     * @param $singlePromptResponseCombination single prompt-response exercise
     *  conditions array
     * @param $promptResponseCombinations array of prompt-response exercise
     *  conditions arrays
     * @return array prompt-response review exercise of random type
     */
    private function generateExercise($cards, $combination, $allExerciseCombination, $userId): array
    {
        /* Pick one random key from the combination array (e.g. prompt-type,
            response-type, pattern-type, etc. */
        // $rand_key = array_rand($combination['combination'], 1);
        // $randomCombination = $combination['combination'];
        // $ExerciseData = $combination[$rand_key];
        $ExerciseData = $combination['combination'];
        $ExerciseData['card_id'] = $combination['card_id'];
        $ExerciseData['skill'] = $combination['skill'];
        $exerciseType = $ExerciseData['exercise'];

        /* Create the exercise depending on the randomly chosen prompt-response type */
        switch ($exerciseType) {
            case 'truefalse':
                $Exercise = $this->generateTrueFalseExercise($ExerciseData, $cards, $userId);
                break;
            case 'multiple-choice':
                $Exercise = $this->generateMultipleChoiceExercise($ExerciseData, $cards, $userId);
                break;
            case 'match-the-pair':
                $Exercise = $this->generateMatchThePairExercise($ExerciseData, $allExerciseCombination);
                break;
            case 'fill_in_the_blanks_typing':
                $Exercise = $this->generateFillInBlanksTypingExercise($ExerciseData);
                break;
            case 'anagram':
                $Exercise = $this->generateAnagramExercise($ExerciseData);
                break;
            case 'recording':
                $Exercise = $this->generateRecordingExercise($ExerciseData);
                break;
            case 'fill_in_the_blanks_mcq':
                $Exercise = array();
                break;
            default:
                Log::error("Got unhandled exercise type: " . $exerciseType);
                $Exercise = array();
                break;
        }

        return $Exercise;
    }

    private function generateTrueFalseExercise($data, $relatedReviewCardsIds, $userId)
    {
        $cardId = $data['card_id'];
        /* get group card by cardId */

        if (!empty($relatedReviewCardsIds)) {
            $responseCardsIds = $this->getCardsInUnitsOfPromptCard($relatedReviewCardsIds, $cardId, $userId);
        } else {
            $responseCardsIds = $this->getCardsByGroupId($cardId);
        }

        /* Return early if no response card ids exist */
        if ($responseCardsIds === null) {
            return null;
        }

        if (($key = array_search($cardId, $responseCardsIds)) !== false) {
            unset($responseCardsIds[$key]);
            /* Reindex the keys as a preventative measure */
            $responseCardsIds = array_values($responseCardsIds);
        }

        /* Get rid of cards that don't have available the same response type as the exercise's response type */
        $filteredCardsIds = $this->filterCardsByResponseType($data['response_type'], $responseCardsIds);

        /* get response card with 50% logic */
        if (!empty($filteredCardsIds)) {
            $rand_key = array_rand($filteredCardsIds, 1);
            $responseCard = $filteredCardsIds[$rand_key];
            $responseCardsArr = array($cardId, $responseCard);
            $rand_key = array_rand($responseCardsArr, 1);
            $responseCard = $responseCardsArr[$rand_key];
        } else {
            $responseCard = $cardId;
        }

        $exercise = array(
            "exercise_type" => "truefalse",
            "instruction" => $data['instruction'],
            'card_type' => 'card',
            "promteresponsetype" => $data['prompt_type'] . '-' . $data['response_type'],
            "promotetype" => $data['prompt_type'],
            "responsetype" => $data['response_type']
        );

        $exercise['question'] = $this->getCardDetails($cardId);
        $exercise['response'] = $this->getCardDetails($responseCard);
        if ($cardId == $responseCard) {
            $option = array(
                "prompt_preview_option" => $data['prompt_type'],
                "responce_preview_option" => $data['response_type'],
                "response_true_false" => "Y"
            );
        } else {
            $option = array(
                "prompt_preview_option" => $data['prompt_type'],
                "responce_preview_option" => $data['response_type'],
                "response_true_false" => "N"
            );
        }

        $exercise['question']['exerciseOptions'] = $option;
        $exercise['response']['exerciseOptions'] = $option;
        return $exercise;
    }

    private function getCardsInUnitsOfPromptCard($cardsToFilter, $promptCard, $userId)
    {

        /* Check arguments to function */
        if ($cardsToFilter == null || $promptCard == null || $userId == null) {
            Log::error("got null argument in getCardsInUnitOfPromptCard()");
            return null;
        }

        /* Determine unit(s) that the prompt is in. This should definitely return at least one unit */
        $promptCardUnitIds = $this->getCardUnitsTable()
            ->find()
            ->where(['card_id' => $promptCard])
            ->all()
            ->combine('id', 'unit_id')
            ->toArray();

        /* If prompt card is in multiple units, search user activities to determine which
         units the user has attempted, which determines which units from which to include
         cards. $userUnitIds should at least be of length 1. */
        if (count($promptCardUnitIds) > 1) {
            $userUnitIds = $this->getUserActivitiesTable()->find()
                ->where(['unit_id IN' => $promptCardUnitIds, 'user_id' => $userId])
                ->all()
                ->combine('id', 'unit_id')
                ->toArray();
            $userUnitIds = array_values(array_unique($userUnitIds));
        } elseif (count($promptCardUnitIds) == 1) {
            $userUnitIds = $promptCardUnitIds;
        } else {
            return null;
        }

        if (empty($userUnitIds)) {
            return null;
        }

        /* Get card in those units */
        $cardIds = $this->getCardUnitsTable()->find()
            ->contain('Cards')
            ->where(['unit_id IN' => $userUnitIds, 'Cards.include_review' => '1'])
            ->all()
            ->combine('id', 'card_id')
            ->toArray();

        return $cardIds;
    }

    /**
     * This table Cardcardgroup is far from correct or copmlete and
     * shouldn't be used.
     */
    private function getCardsByGroupId($CardId)
    {
        $CardgroupCondition = array(
            'conditions' => array(
                'card_id' => $CardId
            ),
            'keyField' => 'id',
            'valueField' => 'card_group_id'
        );
        $Cards = array();
        $Cardgroup = $this->getCardcardgroupTable()->find('list', $CardgroupCondition);
        $count = $Cardgroup->count();
        if ($count > 0) {
            $cardgroupIds = $Cardgroup->toArray();
            $cardgroupIds = array_values($cardgroupIds);
            $CardCondition = array(
                'conditions' => array(
                    'card_group_id IN' => $cardgroupIds
                ),
                'keyField' => 'id',
                'valueField' => 'card_id'
            );
            $Cards = $this->getCardcardgroupTable()->find('list', $CardCondition)->toArray();
            $Cards = array_values(array_unique($Cards));
            return $Cards;
        }
        return $Cards;
    }

    /**
     * Takes an array of cards and filters out the cards that don't have a valid
     * entry for the response type passed in.
     * @param $responseType Response type for which the filter should let cards pass through the filter
     * @param $responseCardsIds Array of card IDs that should be filtered
     * @return array array of card IDs where all cards contain a valid entry for the $responseType specified
     */
    private function filterCardsByResponseType($responseType, $responseCardsIds)
    {
        if (empty($responseCardsIds)) {
            return [];
        }

        /* Create a string of the response type to make it easier to grab that field for each card in the for loop */
        $responseTypes = array(
            'a' => 'audio',
            'i' => 'image_id',
            'v' => 'video_id',
            'l' => 'lakota',
            'e' => 'english');
        $exResponseType = $responseTypes[$responseType] ?? null;

        /* Array of cards that all contain a valid version of the response type */
        $filteredCardsIds = array();

        /* Get the data for all the cards in the input array and get it as a list
         with key=id (card_id) and value=$exResponseType */
        $cardsData = $this->getCardTable()->find(
            'list',
            ['keyField' => 'id',
                'valueField' => $exResponseType]
        )
            ->where(['Card.id IN' => $responseCardsIds])
            ->toArray();
        if (empty($cardsData)) {
            return $filteredCardsIds;
        }

        /* Fill array with only cards that contain the valid response type */
        if ($exResponseType !== null) {
            foreach ($cardsData as $cardId => $responseTypeValue) {
                if ($responseTypeValue !== null && $responseTypeValue !== '') {
                    $filteredCardsIds[] = $cardId;
                }
            }
        }
        return $filteredCardsIds;
    }

    /**
     * @param $data Exercise data info about exercise type (card_id, prompt, response, etc)
     * @param $relatedReviewCardsIds array of cards from filtered review deck
     *  corresponding to allExerciseCombination array too
     */
    private function generateMultipleChoiceExercise($data, $relatedReviewCardsIds, $userId)
    {
        $cardId = $data['card_id'];

        if (!empty($relatedReviewCardsIds)) {
            $responseCardsIds = $this->getCardsInUnitsOfPromptCard($relatedReviewCardsIds, $cardId, $userId);
        } else {
            /* Get group card by cardId if no related cards passed in */
            $responseCardsIds = $this->getCardsByGroupId($cardId);
        }

        /* Return early if no response card ids exist */
        if (empty($responseCardsIds)) {
            return [];
        }

        /* Remove the prompt card from the response cards */
        if (($key = array_search($cardId, $responseCardsIds)) !== false) {
            unset($responseCardsIds[$key]);
            /* Reindex the keys as a preventative measure */
            $responseCardsIds = array_values($responseCardsIds);
        }

        /* Get rid of cards that don't have available the same response type as the exercise's response type */
        $filteredCardsIds = $this->filterCardsByResponseType($data['response_type'], $responseCardsIds);

        $filteredCardIds = $this->removeCardIdsWithSameResponseAsPromptCard(
            $cardId,
            $data['prompt_type'],
            $data['response_type'],
            $filteredCardsIds
        );

        if (empty($filteredCardIds)) {
            return [];
        }

        /* Get response card */
        if (count($filteredCardsIds) < 3) {
            $responseCard = $filteredCardsIds;
        } else {
            $rand_key = array_rand($filteredCardsIds, 3);
            $responseCardFlag = array();
            foreach ($rand_key as $key) {
                $responseCardFlag[] = $filteredCardsIds[$key];
            }
            $responseCard = $responseCardFlag;
        }
        $responseCard[] = $cardId;
        shuffle($responseCard);
        $responseCard = array_unique($responseCard);
        if (count($responseCard) == 1) {
            $choices = array($this->getCardDetails($responseCard));
        } else {
            $choices = $this->getCardDetails($responseCard);
        }
        return array(
            "exercise_type" => "multiple-choice",
            "instruction" => $data['instruction'],
            'card_type' => 'card',
            "promteresponsetype" => $data['prompt_type'] . '-' . $data['response_type'],
            "promotetype" => $data['prompt_type'],
            "responsetype" => $data['response_type'],
            'question' => $this->getCardDetails($cardId),
            'response' => $this->getCardDetails($cardId),
            'choices' => $choices
        );
    }

    /**
     * Get rid of cards with same response as the prompt card's response,
     * to avoid ambiguity. I was originally filtering the input array by reference
     * and returning the number of card IDs removed, but functions with arrays
     * passed by reference wasn't very testable in PHPUnit so I changed it.
     * @param integer $promptCardId - ID of the exercise prompt card
     * @param string $promptTypeLetter - Prompt letter
     * @param string $responseTypeLetter - Response letter
     * @param integer [out] &$optionCardIds - IDs of the exercise option cards
     * @return Array of option card IDs with duplicates removed
     */
    private function removeCardIdsWithSameResponseAsPromptCard(
        $promptCardId,
        $promptTypeLetter,
        $responseTypeLetter,
        $optionCardIds
    ) {

        $cardIdsWithoutDups = array();
        $promptCard = $this->getCardDetails($promptCardId);
        $optionCards = $this->getCardDetails($optionCardIds);
        $promptType = UtilLibrary::convertPromptLetterToWord($promptTypeLetter);
        $responseType = UtilLibrary::convertPromptLetterToWord($responseTypeLetter);

        if (empty($optionCards)) {
            Log::error("optionCards is empty, but shouldn't be.");
            return null;
        }

        foreach ($optionCards as $optionCard) {
            // Make sure two ambiguous cards don't show up in the same exercise
            if (
                $optionCard['id'] === $promptCard['id']
                || $optionCard[$responseType] !== $promptCard[$responseType]
            ) {
                $cardIdsWithoutDups[] = $optionCard['id'];
            }
        }

        return $cardIdsWithoutDups;
    }

    /**
     * Get rid of cards duplicates that have the same prompt or response as
     * another card, given the prompt and response types for the exercises.
     * This is primarily for match-the-pair exercises.
     * @param integer $cardIds - IDs of the exercise cards
     * @param string $promptTypeLetter - Prompt letter
     * @param string $responseTypeLetter - Response letter
     * @return Number of duplicates removed from option card IDs
     */
    private function removeCardIdsWithSamePromptOrResponseAsOthers(
        $cardIds,
        $promptTypeLetter,
        $responseTypeLetter
    ) {
        $cards = $this->getCardDetails($cardIds);
        $promptType = UtilLibrary::convertPromptLetterToWord($promptTypeLetter);
        $responseType = UtilLibrary::convertPromptLetterToWord($responseTypeLetter);
        $dupIds = array();

        if (gettype($cards) == "object") {
            $cards = [$cards];
        }

        if (!empty($cards)) {
            for ($i = 0; $i < count($cards); ++$i) {
                for ($j = $i + 1; $j < count($cards); ++$j) {
                    if ($cards[$i]['id'] === $cards[$j]['id']) {
                        continue;
                    }
                    if (
                        $cards[$i][$promptType] === $cards[$j][$promptType]
                        || $cards[$i][$responseType] === $cards[$j][$responseType]
                    ) {
                        $dupIds[] = $cards[$j]['id'];
                    }
                }
            }
        }

        $dupIds = array_unique($dupIds);

        return array_diff($cardIds, $dupIds);
    }

    private function filterForMatchThePair($promptType, $responseType, $combos)
    {
        /* Return array with bad values filtered out */
        $newCombos = array();

        /* Create a string of the response type to make it easier to grab
            that field for each card in the for loop */
        $types = array('a' => 'audio',
            'i' => 'image_id',
            'v' => 'video_id',
            'l' => 'lakota',
            'e' => 'english');

        /* Set type words */
        $exPromptTypeWord = $types[$promptType] ?? null;
        $exResponseTypeWord = $types[$responseType] ?? null;

        /* Filter out cards that don't have both the prompt and response types
         as the prompt card */
        foreach ($combos as $combo) {
            $card = $this->getCardTable()->get($combo['card_id']);
            if (isset($card[$exPromptTypeWord]) && isset($card[$exResponseTypeWord])) {
                $newCombos[] = $combo;
            }
        }

        return $newCombos;
    }

    /*
     * Generates match-the-pair review exercise.
     */
    private function generateMatchThePairExercise($data, $allExerciseCombination)
    {
        /* Check input arguments */
        if ($data == null || $allExerciseCombination == null) {
            return [];
        }

        $numCardsToSelect = 5;
        $cardCount = 0;
        $cardIds = array();

        /* Filter out cards that don't have the right prompt types */
        $filteredCombos = $this->filterForMatchThePair(
            $data['prompt_type'],
            $data['response_type'],
            $allExerciseCombination
        );
        if (empty($filteredCombos)) {
            return [];
        }

        /* Select 5 cards to go along with original prompt card */
        foreach ($filteredCombos as $Combination) {
            $duplicateGlosses = false;
            if (
                $data['card_id'] != $Combination['card_id'] &&
                $data['skill'] == $Combination['skill']
            ) {
                // Make sure two ambiguous cards don't show up in the same exercise
                $comboCard = $this->getCardDetails($Combination['card_id']);
                foreach ($cardIds as $cardId) {
                    $card = $this->getCardDetails($cardId);

                    if ($comboCard['lakota'] == $card['lakota'] || $comboCard['english'] == $card['english']) {
                        $duplicateGlosses = true;
                    }
                }

                // Add the card to the exercise if it doesn't have a duplicate gloss
                if (!$duplicateGlosses) {
                    $cardIds[] = $Combination['card_id'];

                    if (++$cardCount >= $numCardsToSelect) {
                        break;
                    }
                }
            }
        }

        $cardIds[] = $data['card_id'];

        // Remove duplicates
        $cardIds = $this->removeCardIdsWithSamePromptOrResponseAsOthers(
            $cardIds,
            $data['prompt_type'],
            $data['response_type']
        );

        if (empty($cardIds)) {
            return [];
        }

        $exercise = array(
            "exercise_type" => "match-the-pair",
            "instruction" => $data['instruction'],
            'card_type' => 'card',
            'questions' => array()
        );

        $promptConversion = false;
        $responseConversion = false;

        foreach ($cardIds as $cardId) {
            $element = array();
            //$option = array();
            $element['question'] = $this->getCardDetails($cardId);
            $element['response'] = $element['question'];
            if (!UtilLibrary::isValid($data['prompt_type'])) {
                $promptConversion = true;
            }
            if (!UtilLibrary::isValid($data['response_type'])) {
                $responseConversion = true;
            }

            $exercise['questions'][] = $element;
        }

        if ($promptConversion) {
            $promotetype = 'l';
        } else {
            $promotetype = $data['prompt_type'];
        }
        if ($responseConversion) {
            $responsetype = 'e';
        } else {
            $responsetype = $data['response_type'];
        }

        $exercise['promteresponsetype'] = $promotetype . '-' . $responsetype;
        $exercise['promotetype'] = $promotetype;
        $exercise['responsetype'] = $responsetype;

        shuffle($cardIds);
        if (count($cardIds) == 1) {
            $exercise['choices'] = array($this->getCardDetails($cardIds));
        } else {
            $exercise['choices'] = $this->getCardDetails($cardIds);
        }
        return $exercise;
    }

    /*----------------------------------------*/
    /* Exercise Generation For Review (Start) */
    /*----------------------------------------*/

    /*
     * Generates match-the-pair review exercise.
     */

    private function generateFillInBlanksTypingExercise($data)
    {
        $cardId = $data['card_id'];
        $exercise = array(
            "exercise_type" => "fill_in_the_blanks",
            "instruction" => $data['instruction'],
            'card_type' => 'card',
            "promteresponsetype" => $data['prompt_type'] . '-' . $data['response_type'],
            "promotetype" => $data['prompt_type'],
            "responsetype" => $data['response_type']
        );
        $exercise['question'] = $this->getCardDetails($cardId);
        $lakotaString = preg_split(
            '/(?<=\s)|(?<=\w)(?=[.,:;!?()-])|(?<=[.,!()?\x{201C}])(?=[^ ])/u',
            $exercise['question']['lakota']
        );
        $str = '';
        $choices = array();
        foreach ($lakotaString as $key => $s) {
            if (!ctype_punct($s)) {
                $choices[] = array('option_name' => trim($s), 'position' => ($key + 1));
                $str .= "[" . trim($s) . "] ";
            } else {
                $str .= $s;
            }
        }
        $exercise['question']['question'] = trim($str);
        $exercise['choices'] = $choices;
        return $exercise;
    }

    /*
     * Generates true/false review exercise.
     */

    private function generateAnagramExercise($data)
    {
        $cardId = $data['card_id'];
        $exercise = array(
            "exercise_type" => "anagram",
            "instruction" => $data['instruction'],
            'card_type' => 'card',
            "promteresponsetype" => $data['prompt_type'] . '-' . $data['response_type'],
            "promotetype" => $data['prompt_type'],
            "responsetype" => $data['response_type']
        );
        $exercise['question'] = $this->getCardDetails($cardId);
        if (empty($exercise['question'])) {
            return [];
        }
        return $exercise;
    }

    private function generateRecordingExercise($data)
    {
        $cardId = $data['card_id'];
        $exercise = array(
            "exercise_type" => "recording",
            "instruction" => $data['instruction'],
            'card_type' => 'card',
            "promteresponsetype" => $data['prompt_type'] . '-' . $data['response_type'],
            "promotetype" => $data['prompt_type'],
            "responsetype" => $data['response_type']
        );
        $exercise['question'] = $this->getCardDetails($cardId);
        $exercise['response'] = $this->getCardDetails($cardId);
        $option = array(
            "prompt_preview_option" => $data['prompt_type'],
            "responce_preview_option" => $data['response_type'],
            "response_true_false" => "Y"
        );
        $exercise['question']['exerciseOptions'] = $option;
        $exercise['response']['exerciseOptions'] = $option;
        return $exercise;
    }

    /*
     * Generates 'fill in the blanks by typing' review exercise.
     */

    private function sendExercisesToFrontend($exercises)
    {
        /*----------------------------*/
        /* Send Exercises To Frontend */
        /*----------------------------*/
        /* Set the response data with the final array of prompt-response exercises */
        $data = array(
            'status' => true,
            'message' => 'Result return successfully',
            'results' => $exercises
        );
        $message = 'valid token';
        $this->set(compact('data', 'message'));
        $this->viewBuilder()->setOption('serialize', ['data', 'message']);
    }

    /*
     * Generates anagram review exercise.
     */

    /**
     * Creates four prompt-response exercises and returns them to the requester:
     * Gets the request data, checks if the frontend specified a unit ID and uses it to get
     * all non-duplicate card IDs in the unit.
     * Creates a deck of review cards with no limit on the number of cards and only from the current unit if specified,
     * otherwise from any unit from which cards have been added to the review deck.
     * NOTE: Depricated version. See getReviewExercise() for up-to-date version
     */
    public function getReviewExerciseObsolete()
    {
        /* Get the frontend request data */
        $requestData = $this->request->getData();
        $limit = null; /* Current not used */
        $userId = $requestData['user_id']; /* Current user */
        $unitCards = array(); /* For storing list of non-duplicate cards in the current unit */

        if (isset($requestData['unit_id'])) {
            $unitId = $requestData['unit_id'];
            /* Get list of all non-duplicate cards in this unit */
            $unitCards = $this->getCardsByUnitId($unitId);
        } else {
            $unitCards = null;
        }

        /*--------------------------------*/
        /*  Create deck of review cards   */
        /*--------------------------------*/
        /* Get all review cards from review queue for this user with limit on number of cards, if set,
         and from specified unit, if specified, otherwise from any unit user has completed. */
        $ReviewCardDeck = $this->getReviewCardsDetailsFromDeck($userId, $limit, $unitCards);

        /*-------------------------------------------------*/
        /*  Create array of Prompt-Response Combinations   */
        /*-------------------------------------------------*/
        $allExerciseCombination = array();
        $allRelevantCards = array();

        $DeckCount = 1;
        $DeckLimit = 32;
        /* For each card in the review card deck, get all combinations of prompt-response exercises for
         that card that rewards points above the average (xp_avg), and only add up to limit for the deck. */
        // TODO how is limit of 32 decided?
        foreach ($ReviewCardDeck as $Deck) {
            /* Collect all the cards to use as Option cards for exercises,
                therefore don't break out of foreach loop early */
            $allRelevantCards[] = $Deck['card_id'];

            if ($DeckCount <= $DeckLimit) {
                $cardId = $Deck['card_id'];
                /* Get all prompt-response combinations for each exercise type
                    for the current card based on its contents */
                $AllCombination = $this->getCombinationOfExercise($cardId);
                /* Get parameters for the filter, corresponding to average points
                    and skill type for the current card */
                $param = array('xp_avg' => $Deck['xp_avg'], 'skill' => $Deck['skill_type']);
                /* Get array of query conditions arrays that correspond to
                    point-rewarding exercises for this card */
                $FilterCombination = $this->getFilterCombinationOfExercise($param, $AllCombination);
                /* If there are still exercises left add array of info to array */
                if (!empty($FilterCombination)) {
                    $element = array();
                    $element['card_id'] = $cardId;
                    $element['skill'] = $Deck['skill_type'];
                    $element['combination'] = $FilterCombination;
                    /* Add to array of review exercise details */
                    $allExerciseCombination[] = $element;
                    $DeckCount++;
                }
            }
        }

        /* Ensure there is only one card for each card ID */
        $allRelevantCards = array_values(array_unique($allRelevantCards));

        /*---------------------------------------------------*/
        /* Generate 4 random prompt-reponse Review exercises */
        /*---------------------------------------------------*/
        $exercise = array();
        $exerciseCount = 1;
        $exerciseLimit = 5;
        // TODO there's a more concise way to handle the exerciseLimit here
        foreach ($allExerciseCombination as $combination) {
            if ($exerciseCount <= $exerciseLimit) {
                $generatedExercise = $this->generateExercise(
                    $allRelevantCards,
                    $combination,
                    $allExerciseCombination
                );
                if ($generatedExercise != null) {
                    $exercise[] = $generatedExercise;
                    $exerciseCount++;
                }
            }
            if ($exerciseCount == $exerciseLimit) {
                break;
            }
        }
        /* Set the response data with the final array of prompt-response exercises */
        $data = array('status' => true, 'message' => 'Result return successfully', 'results' => $exercise);
        $message = 'valid token';
        $this->set(compact('data', 'message'));
        $this->viewBuilder()->setOption('serialize', ['data', 'message']);
    }

    /*
     * Generates recording review exercise.
     */

    /**
     * Creates an array of prompt-response pairs for each exercise type for the card passed in.
     * @param $cardId int ID of the card to grab from the Cards table
     * @return array of prompt-response exercises where prompt & response are not the same, for each type of exercise.
     */
    private function getCombinationOfExercise($cardId)
    {
        /* Get single card entity with id $cardId and related image, video, audio_details and cardtype items */
        $cards = $this->getCardTable()->get($cardId, ['contain' => ['image', 'video', 'audio_details', 'Cardtype']]);

        /* Create exercise type array to use below for convenience */
        $exerciseType = ['match-the-pair', 'fill_in_the_blanks_typing', 'anagram', 'multiple-choice', 'truefalse'];

        /* Randomize the order of the exercise types */
        shuffle($exerciseType);

        /* Get type of card it is (Word, Pattern, etc) */
        $pattern = $cards['cardtype']['title'];

        /* Fill in prompt response types */
        $promptResponse = array();
        if ($cards['english'] != '') {
            $promptResponse[] = 'e';
        }
        if ($cards['lakota'] != '') {
            $promptResponse[] = 'l';
        }
        if ($cards['image_id'] != '') {
            $promptResponse[] = 'i';
        }
        if ($cards['audio'] != '') {
            $promptResponse[] = 'a';
        }
        if ($cards['video'] != '') {
            $promptResponse[] = 'v';
        }

        /* Create array of all combinations of prompt-response pairs for each exercise that aren't the same */
        $combination = array();
        foreach ($exerciseType as $EType) {
            foreach ($promptResponse as $prompt) {
                foreach ($promptResponse as $response) {
                    if ($prompt != $response) {
                        $element = array();
                        $element['exerciseType'] = $EType;
                        $element['promptType'] = $prompt;
                        $element['responseType'] = $response;
                        $element['pattern'] = $pattern;
                        if ($EType == 'recording') {
                            $element['responseType'] = 'r';
                        }
                        array_push($combination, $element);
                    }
                }
            }
        }
        /* Return array of exercise combinations */
        // TODO fix spelling here
        return $combination;
    }

    /*--------------------------------------*/
    /* Exercise Generation For Review (End) */
    /*--------------------------------------*/

    /**
     * Goes through all the prompt-response exercise combinations for a single card in the review cards deck
     * and builds an array of database query conditions arrays containing the parameters for each exercise
     * including the exercise instructions, all only if the rewarded points for the exercise is more than the average.
     * @param $param float average points and skill type for the next review exercise
     * @param $AllCombination array of all combinations of prompt-response
     *  exercises for a single card in the review deck
     * @return array of conditions arrays that correspond to each prompt-response exercise
     */
    private function getFilterCombinationOfExercise($param, $AllCombination)
    {
        $combination = array();
        /* For each prompt-response exercise combination create conditions array for a database query
         and only include those cards that reward points specifically those that reward more points
         than the average */
        foreach ($AllCombination as $c) {
            // TODO move these $c[] items directly to conditions array to avoid memory allocation
            $exercise = $c['exerciseType'];
            $promptType = $c['promptType'];
            $responseType = $c['responseType'];
            $pattern = $c['pattern'];
            /* Create a database query condition array for the WHERE items */
            $conditions = array(
                'conditions' => array(
                    'exercise' => $exercise,
                    'prompt_type' => $promptType,
                    'response_type' => $responseType,
                    'card_type' => $pattern,
                    'is_review_included' => 1
                )
            );
            /* Find all database entries in the PointReferences table meeting the WHERE conditions above
             to find the points for this specific prompt-response combination exercise. */
            $PointReferencesDetails = $this->getPointReferencesTable()->find('all', $conditions)->toArray();

            if (!empty($PointReferencesDetails)) {
                // TODO can do this more efficiently by doing $PointReferncesDetails[0]['$param['skill']._pts']; maybe
                /* Store the points value for the skill type being reviewed */
                $skill_pts_str = $param['skill'] . '_pts';
                $point = $PointReferencesDetails[0][$skill_pts_str];
                // TODO is there a better way to do this?? Can you have multiple points for one item?
                /* If prompt-response exercise rewards user with points, and they are more than the average
                 add instructions to the filter exercise search conditions */
                if (
                    ($PointReferencesDetails[0]['reading_pts'] != 0
                        || $PointReferencesDetails[0]['writing_pts'] != 0
                        || $PointReferencesDetails[0]['speaking_pts'] != 0
                        || $PointReferencesDetails[0]['listening_pts'] != 0)
                    && $point > $param['xp_avg'] && $point != 0
                ) {
                    $conditions['conditions']['instruction'] = $PointReferencesDetails[0]['instructions'];
                    $combination[] = $conditions['conditions'];
                }
            }
        }
        /* Return the array of filtered exercise conditions */
        return $combination;
    }

    private function printExercises($exercises)
    {
        $resultsStr = "Count:" . count($exercises) . "\n";
        foreach ($exercises as $exercise) {
            $resultsStr .= "Exercise:\n";
            $resultsStr .= "  - type: " . $exercise['exercise_type'] . "\n";
            $resultsStr .= "  - instrunction: " . $exercise['instruction'] . "\n";
            $resultsStr .= "  - card_type: " . $exercise['card_type'] . "\n";
            $resultsStr .= "  - p-r: " . $exercise['promteresponsetype'] . "\n";
            $resultsStr .= "  - prompt: " . $exercise['promotetype'] . "\n";
            $resultsStr .= "  - response: " . $exercise['responsetype'] . "\n";
            if (isset($exercise['choices'])) {
                $resultsStr .= '  - choices:\n';
                foreach ($exercise['choices'] as $choice) {
                    $resultsStr .= "    - " . $choice['id'] . ", "
                    . $choice['lakota'] . ", " . $choice['english'] . "\n";
                }
            }
        }
    }
}
