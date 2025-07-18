<?php

namespace App\Controller\Api;

use App\Exceptions\RequiredFieldException;
use Cake\Log\Log;
use App\Lib\UtilLibrary;

class UserPointsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * @throws RequiredFieldException
     */
    public function getGlobalFire()
    {

        $this->validateRequest($this->request->getData(), [
            'user_id',
            'timestamp_offset',
            'type'
        ]);

        $data = $this->request->getData();
        $userid = $data['user_id'];

        $type = $data['type'];
        $userdate = $this->getUserTime($data['timestamp_offset'], 'Y-m-d');
        $globalFire = $this->getGlobalFiresTable()->find('all', ['conditions' => ['user_id' => $userid]]);
        $count = $globalFire->count();
        $element = array();
        $element['user_id'] = $userid;
        $element['last_day'] = $userdate;
        if ($count == 0) {
            $activitiy = $this->getGlobalFiresTable()->newEmptyEntity();
        } else {
            $globalFireData = $globalFire->first();
            $activitiy = $this->getGlobalFiresTable()->get($globalFireData->id);
            if ($type == 'nextuse') {
                $days = $this->getDaysByTwoDate($userdate, $activitiy['last_day']);
                if ($days >= 2) {
                    $fireDays = $activitiy['streak_days'] - $days;
                    $element['fire_days'] = $fireDays;
                    if ($fireDays < 0) {
                        $element['fire_days'] = 0;
                        $element['streak_days'] = 0;
                    }
                }
            } elseif ($type == 'achievement') {
                $element['fire_days'] = $activitiy['fire_days'] + 1;
                $element['streak_days'] = $activitiy['streak_days'] + 1;
            }
        }
        $Data = $this->getGlobalFiresTable()->patchEntity($activitiy, $element);
        $this->getGlobalFiresTable()->save($Data);
        $this->sendApiData(true, 'GlobalFires updated Successfully');
    }

    private function getUserTime($timestampOffset, $format)
    {
        $servertimestamp = time();
        $timestampdiff = $timestampOffset;
        $usertimestamp = $servertimestamp + $timestampdiff;
        $usertime = date($format, $usertimestamp);
        return $usertime;
    }

    //hit when complete for set

    private function getDaysByTwoDate($date1, $date2)
    {
        $date1 = date_create($date1);
        $date2 = date_create($date2);
        $diff = date_diff($date1, $date2);
        return $diff->format("%a");
    }

    //hit when complete the unit

    /**
     * Adds the new or updated activity to the UserActivities Table and
     * called whenever cards.component.ts's lessonComplete() or exerciseComplete() functions are called, which happens
     * if a user completes an exercise or lesson.
     * Element items: user_id, activity_type, path_score, reading_score, writing_score, speaking_score, listening_score,
     * level_id, unit_id, user_unit_activity_id, card_id, reading_pts, writing_pts, speaking_pts, listening_pts,
     * exercise_id, type (right/wrong), path_score, etc.
     * Hit when every question and lesson frame.
     */
    public function addUserActivity()
    {
        /* Get request data, which consists of parameters from lessonComplete() function in cards.component.ts */
        $data = $this->request->getData();
        /* Instantiate new UserActivity entity and fill in its data from the request data and defaults */
        $activitiy = $this->getUserActivitiesTable()->newEmptyEntity();
        $element = array();
        $element['user_id'] = $data['user_id'];
        $element['activity_type'] = $data['activity_type'];
        $element['path_score'] = 0;
        $element['reading_score'] = 0;
        $element['writing_score'] = 0;
        $element['speaking_score'] = 0;
        $element['listening_score'] = 0;
        if ($data['activity_type'] == 'exercise' || $data['activity_type'] == 'lesson') {
            /* If activity type is an exercise or lesson, fill in the appropriate data */
            $element['level_id'] = $data['level_id'];
            $element['unit_id'] = $data['unit_id'];
            /* Get data related to whether to user completed the unit and how many times they've attempted it */
            $unitresponse = $this->getAndLogUserUnitActivity($data);
            $element['user_unit_activity_id'] = $unitresponse['lastid'];
        }
        if ($data['activity_type'] == 'exercise') {
            /*-------------------*/
            /*  Exercise Activity  */
            /*-------------------*/
            /* If activity type is an exercise...
             retrieve the point value and card ID of the exercise */
            if (isset($data['card_id']) && $data['card_id'] != '') {
                $point = $this->getPointByExercise($data['exercise_id'], $data['card_id']);
                $element['card_id'] = $data['card_id'];
            } else {
                $point = $this->getPointByExercise($data['exercise_id']);
                $element['card_id'] = null;
            }
            /* Divide number of points for each review type by 2^(number of attempts), because
             for every attempt to complete the unit, the user's points are halfed, because they've
             already done these exercises and they aren't review exercises. Not sure if this makes sense or not. */
            $UnitAttemptcounter = $unitresponse['attempt'];
            for ($i = 1; $i < $UnitAttemptcounter; $i++) {
                $point['reading_pts'] = (float)$point['reading_pts'] / 2;
                $point['writing_pts'] = (float)$point['writing_pts'] / 2;
                $point['speaking_pts'] = (float)$point['speaking_pts'] / 2;
                $point['listening_pts'] = (float)$point['listening_pts'] / 2;
            }

            $element['exercise_id'] = $data['exercise_id'];
            $element['type'] = $data['answar_type'];

            // Check No. of attempts at this exercise activity within this
            // exercise session and whether the user answered it correctly.
            // There are multiple exercise sessions per lesson. Each "?" circle
            // is a different exercise session.

            //this is where correct response percentage gets updated
            if ($data['answar_type'] == 'right') {
                /* User answered the exercise correctly */
                $count = $this->getAttemptCounter(
                    $data['exercise_id'],
                    $data['unit_id'],
                    $data['level_id'],
                    $data['card_id'] ?? null,
                    $data['user_id'],
                    $unitresponse['lastid']
                );
                /* Divide number of points for each review type by 2^(number of attempts), because
                 for every attempt to complete the unit, the user's points are halfed. This is because
                 it's just within an exercise session and therefore if it takes the user several times
                 to answer the activity correctly, it means they are stuggling with this card and should
                 receive fewer points to reflect their lack of mastery with this card. */
                for ($i = 0; $i < $count; $i++) {
                    $point['reading_pts'] = (float)$point['reading_pts'] / 2;
                    $point['writing_pts'] = (float)$point['writing_pts'] / 2;
                    $point['speaking_pts'] = (float)$point['speaking_pts'] / 2;
                    $point['listening_pts'] = (float)$point['listening_pts'] / 2;
                }
                /* Round up the point values to the nearest integer */
                $element['reading_score'] = ceil($point['reading_pts']);
                $element['writing_score'] = ceil($point['writing_pts']);
                $element['speaking_score'] = ceil($point['speaking_pts']);
                $element['listening_score'] = ceil($point['listening_pts']);
                $element['path_score'] = 0;

                $UserActivitiescheck = $this->getUserActivitiesTable()
                    ->find('all', ['conditions' => [
                        'user_id' => $data['user_id'],
                        'exercise_id' => $data['exercise_id'],
                        'unit_id' => $data['unit_id'],
                        'level_id' => $data['level_id'],
                        'card_id IS' => $data['card_id'] ?? null,
                        'type' => 'wrong',
                        'type !=' => 'right',
                        'user_unit_activity_id' => $unitresponse['lastid']]])
                    ->count();
                if ($UserActivitiescheck == 0) {
                    /* If a corresponding user activity doesn't exist, find the corresponding exercise */
                    $conditionex = array('id' => $data['exercise_id']);
                    $excecices = $this->getExercisesTable()->find()->where($conditionex)->first();
                    $element['path_score'] = $excecices['bonus'];
                    for ($i = 1; $i < $UnitAttemptcounter; $i++) {
                        $element['path_score'] = (float)$element['path_score'] / 2;
                    }
                    $element['path_score'] = ceil($element['path_score']);
                }
            } else {
                /* Exercise activity answered incorrectly. Don't award any points */
                $element['reading_score'] = 0;
                $element['writing_score'] = 0;
                $element['speaking_score'] = 0;
                $element['listening_score'] = 0;
                $element['path_score'] = 0;
            }

            /* Reset point for match the pair question attempt */
            $Exercise = $this->getExercisesTable()->get($data['exercise_id']);
            $exerciseType = $Exercise->exercise_type;
            if ($exerciseType == 'match-the-pair') {
                $matchCounter = $this->getUserActivitiesTable()
                    ->find('all', ['conditions' => [
                        'user_id' => $data['user_id'],
                        'exercise_id' => $data['exercise_id'],
                        'unit_id' => $data['unit_id'],
                        'level_id' => $data['level_id'],
                        'card_id IS' => $data['card_id'],
                        'type' => 'right',
                        'user_unit_activity_id' => $unitresponse['lastid']]])
                    ->count();
                if ($matchCounter > 0) {
                    $element['reading_score'] = 0;
                    $element['writing_score'] = 0;
                    $element['speaking_score'] = 0;
                    $element['listening_score'] = 0;
                    $element['path_score'] = 0;
                }
            }
            $element['exercise_type'] = $exerciseType;
        } elseif ($data['activity_type'] == 'lesson') {
            /*-------------------*/
            /*  Lesson Activity  */
            /*-------------------*/
            $element['lesson_id'] = $data['lesson_id'];

            /* Get lesson frame entry start */
            $lessonOptions = array(
                'contain' => array(
                    'Lessonframes'
                )
            );
            $lesson = $this->getLessonsTable()->get($data['lesson_id'], $lessonOptions);
            $Framecounter = count($lesson['lessonframes']);


            $Act = $this->getUserActivitiesTable()
                ->find('list', ['keyField' => 'id', 'valueField' => 'lessonframe_id'])
                ->where([
                    'user_id' => $data['user_id'],
                    'lesson_id' => $data['lesson_id'],
                    'unit_id' => $data['unit_id'],
                    'level_id' => $data['level_id'],
                    'user_unit_activity_id' => $unitresponse['lastid']])
                ->group(['UserActivities.lessonframe_id']);
            $Frameids = array_values($Act->toArray());
            $ActCounter = $Act->count();
            if (($Framecounter - 1) == $ActCounter && !in_array($data['lessonframe_id'], $Frameids)) {
                $UnitAttemptcounter = $unitresponse['attempt'];
                $element['path_score'] = $this->getBonusPointByKey('Lesson Completion');
                for ($i = 1; $i < $UnitAttemptcounter; $i++) {
                    $element['path_score'] = (float)$element['path_score'] / 2;
                }
                $element['path_score'] = ceil($element['path_score']);
            } else {
                $element['path_score'] = 0;
            }
            //get lesson frame entry end

            $element['reading_score'] = 0;
            $element['writing_score'] = 0;
            $element['speaking_score'] = 0;
            $element['listening_score'] = 0;
            $element['lessonframe_id'] = $data['lessonframe_id'];
        } elseif ($data['activity_type'] == 'review') {
            /*-------------------*/
            /*  Review Activity  */
            /*-------------------*/
            $promptType = $data['prompt_type'];
            $responseType = $data['response_type'];
            $exerciseType = $data['exercise_type'];
            $element['exercise_type'] = $exerciseType;
            /* Get array of points by skill type for the current exercise that was just answered */
            $point = $this->getPointByReview($data['card_id'] ?? null, $promptType, $responseType, $exerciseType);
            $element['card_id'] = $data['card_id'] ?? null;
            $element['type'] = $data['answar_type'];

            /* Set level ID and unit ID if they exist in the argument array */
            if (isset($data['level_id'])) {
                $element['level_id'] = $data['level_id'];
            }
            if (isset($data['unit_id'])) {
                $element['unit_id'] = $data['unit_id'];
            }

            /* Assign points to the exericse if the user answered correctly, otherwise no points */
            if ($data['answar_type'] == 'right') {
                $element['reading_score'] = ceil($point['reading_pts']);
                $element['writing_score'] = ceil($point['writing_pts']);
                $element['speaking_score'] = ceil($point['speaking_pts']);
                $element['listening_score'] = ceil($point['listening_pts']);
                $element['path_score'] = 0;
            } else {
                $element['reading_score'] = 0;
                $element['writing_score'] = 0;
                $element['speaking_score'] = 0;
                $element['listening_score'] = 0;
                $element['path_score'] = 0;
            }


            /* Save for review counter */
            $reviewCounter = array();
            $reviewCounter['unit_id'] = null;
            $reviewCounter['level_id'] = null;
            if (isset($data['level_id'])) {
                $reviewCounter['level_id'] = $data['level_id'];
            }
            $element['showModal'] = false;
            if (isset($data['unit_id'])) {
                $reviewCounter['unit_id'] = $data['unit_id'];
                $element['showModal'] = true;
            }
            $reviewCounter['user_id'] = $data['user_id'];
            if ($reviewCounter['unit_id'] == null) {
                // Create condition for the level review, which has no unit.
                // Before it was just using whatever unit_id was set to, which
                // result in not finding anything and creating a bunch of the
                // same items with a counter of 1 for each. We want the counter
                // to count up on the item where the unit is null.
                $reviewCounterCondition = array(
                    'user_id' => $data['user_id'],
                    'OR' => array('unit_id IS NULL',
                        'unit_id' => 0));
            } else {
                $reviewCounterCondition = array(
                    'user_id' => $data['user_id'],
                    'unit_id' => $reviewCounter['unit_id']);
            }
            $counter = $this->getReviewCountersTable()
                ->find()
                ->where($reviewCounterCondition)
                ->count();
            if ($counter == 0) {
                $ReviewCounterModel = $this->getReviewCountersTable()->newEmptyEntity();
            } else {
                $ReviewCounterModel = $this->getReviewCountersTable()->find()
                    ->where($reviewCounterCondition)
                    ->order(['counter' => 'DESC'])
                    ->first();
                $counter = $ReviewCounterModel['counter'];
            }
            /* Only increment the review counter if the user answered the activity correctly,
             i.e. only reward the user for correct answers */
            if ($data['answar_type'] == 'right') {
                $reviewCounter['counter'] = $counter + 1;
                $ReviewCounterData = $this->getReviewCountersTable()->patchEntity($ReviewCounterModel, $reviewCounter);
                $this->getReviewCountersTable()->save($ReviewCounterData);
            } else {
                $reviewCounter['counter'] = $counter;
            }
            $element['review_counter'] = $reviewCounter['counter'];

            // Store number of review cards in unit
            if (!empty($data['unit_id'])) {
                $numReviewCardsInUnit = $this->getCardUnitsTable()->numReviewCardsInUnit($data['unit_id']);
                $element['num_correct_review_answers_to_unlock_unit'] = UtilLibrary::numCorrectReviewAnswersToUnlockUnit($numReviewCardsInUnit);
            } else {
                $element['num_correct_review_answers_to_unlock_unit'] = -1;
            }

            /* save for review counter End */
        }
        /* Add the updated activity to the UserActivities table in the database */
        $Data = $this->getUserActivitiesTable()->patchEntity($activitiy, $element);
        $savedData = $this->getUserActivitiesTable()->save($Data);
        /* Add the activity passed from the frontend to the user's Review queue */
        $this->addToReviewQueue($data);
        /* Update user points */
        $userPoint = $this->updatePointByUserId($data['user_id']);
        /* Send response to frontend with the status, message, and result data */
        $this->sendApiData(true, 'Result return successfully.', $element);
    }

    //get review point

    /**
     * Get all activities that the user has attempted on this unit and check
     * which attempt to complete the unit this is and if the user completed the unit yet.
     */
    private function getAndLogUserUnitActivity($data)
    {
        $response = array();
        $percent = $this->calculateUnitProgressPercentage($data['unit_id'], $data['user_id']);
        $activityData = array(
            'path_id' => $data['path_id'],
            'level_id' => $data['level_id'],
            'unit_id' => $data['unit_id'],
            'user_id' => $data['user_id'],
            'percent' => $percent);
        $UnitAttempt = $this->getUnitAttemptIdAndIsCompleted(
            $data['level_id'],
            $data['unit_id'],
            $data['user_id']
        );
        if ($UnitAttempt['attempt'] == 0) {
            /* First time attempting this unit, so insert a new entity into the database */
            $activityEntity = $this->getUserUnitActivitiesTable()->newEmptyEntity();
            $Data = $this->getUserUnitActivitiesTable()->patchEntity($activityEntity, $activityData);
            $savedData = $this->getUserUnitActivitiesTable()->save($Data);
            $response['lastid'] = $savedData['id'];
            $response['attempt'] = 1;
        } else {
            /* If user has attempted this unit before */
            if ($UnitAttempt['isunitComplete']) {
                /* And they completed the unit, save it and increment the attempt variable by one */
                $activityEntity = $this->getUserUnitActivitiesTable()->newEmptyEntity();
                $Data = $this->getUserUnitActivitiesTable()->patchEntity($activityEntity, $activityData);
                $savedData = $this->getUserUnitActivitiesTable()->save($Data);
                $response['lastid'] = $savedData['id'];
                $response['attempt'] = $UnitAttempt['attempt'] + 1;
            } else {
                /* They haven't completed the unit, so just return the original data */
                $response['lastid'] = $UnitAttempt['last_id'];
                $response['attempt'] = $UnitAttempt['attempt'];
            }
        }
        return $response;
    }

    //hit for add the timer

    private function getPointByExercise($exerciseId, $cardId = null)
    {
        $Exercise = $this->getExercisesTable()->get($exerciseId, ['contain' => ['Exerciseoptions']]);
        $type = explode('-', $Exercise->promteresponsetype);
        $exerciseType = $Exercise->exercise_type;
        $promptType = $type[0];
        $responseType = $type[1];
        if ($exerciseType == 'fill_in_the_blanks') {
            $fillInType = $Exercise['exerciseoptions'][0]['fill_in_the_blank_type'] ?? 'mcq';
            $exerciseType = $exerciseType . '_' . $fillInType;
        }

        if ($cardId != null) {
            $options = array('contain' => ['Cardtype', 'image', 'video'], 'conditions' => ['Card.id' => $cardId]);
            $cards = $this->getCardTable()->find('all', $options)->first()->toArray();
            $cardtype = $cards['cardtype']['title'];
            if ($cardtype == 'Word' || $cardtype == 'Verb') {
                $cardtype = 'Word';
            }
        } else {
            $cardtype = 'Pattern';
        }
        $points = $this->getPointReferencesTable()
            ->find()
            ->where([
                'exercise' => $exerciseType,
                'prompt_type' => $promptType,
                'response_type' => $responseType,
                'card_type' => $cardtype])
            ->first();
        if (empty($points)) {
            Log::warning("Missing points reference for exercise type: " . $exerciseType
                . ", prompt type: " . $promptType . ", response type: " . $responseType
                . ", card type: " . $cardtype);
        }
        return array(
            'reading_pts' => $points['reading_pts'] ?? 0,
            'writing_pts' => $points['writing_pts'] ?? 0,
            'speaking_pts' => $points['speaking_pts'] ?? 0,
            'listening_pts' => $points['listening_pts'] ?? 0);
    }

    //hit for get the timer

    private function getAttemptCounter($exerciseId, $unitId, $levelId, $cardId, $userId, $UserUnitActivity)
    {
        if ($cardId == null) {
            $UserActivitiesCount = $this->getUserActivitiesTable()
                ->find()
                ->where([
                    'user_id' => $userId,
                    'exercise_id' => $exerciseId,
                    'unit_id' => $unitId,
                    'level_id' => $levelId,
                    'user_unit_activity_id' => $UserUnitActivity])
                ->count();
            $Exercise = $this->getExercisesTable()->get($exerciseId);
            if ($Exercise->exercise_type == 'match-the-pair' && $Exercise->card_type == 'custom') {
                $UserActivitiesCount = $UserActivitiesCount / $Exercise->noofcard;
                if ($UserActivitiesCount < 1) {
                    $UserActivitiesCount = 0;
                } else {
                    $UserActivitiesCount = round($UserActivitiesCount);
                }
            }
        } else {
            $UserActivitiesCount = $this->getUserActivitiesTable()
                ->find()
                ->where([
                    'user_id' => $userId,
                    'exercise_id' => $exerciseId,
                    'unit_id' => $unitId,
                    'level_id' => $levelId,
                    'card_id' => $cardId,
                    'user_unit_activity_id' => $UserUnitActivity])
                ->count();
        }
        return $UserActivitiesCount;
    }

    //api function for reset progress data

    /**
     * Get the points associated with the exercise type passed in, which corresponds to how many
     * points the user gets for each skill type for this exercise.
     * Return that array of points values.
     */
    private function getPointByReview($cardId, $promptType, $responseType, $exerciseType)
    {
        /* Get card based on card ID and associated image and video, as an array */
        $options = array('contain' => ['Cardtype', 'image', 'video'], 'conditions' => ['Card.id IS' => $cardId]);
        $cards = $this->getCardTable()->find('all', $options)->first();
        if (!$cards) {
            return array(
                'reading_pts' => 0,
                'writing_pts' => 0,
                'speaking_pts' => 0,
                'listening_pts' => 0
            );
        }

        $cards = $cards->toArray();

        /* Set the card type */
        $cardtype = $cards['cardtype']['title'];
        if ($cardtype == 'Word' || $cardtype == 'Verb') {
            $cardtype = 'Word';
        }
        /* If type of exercise is fill_in_the_blanks, add _typing suffix to it */
        if ($exerciseType == 'fill_in_the_blanks') {
            $exerciseType = $exerciseType . '_typing';
        }
        /* Get Points References entity corresponding to the card, exercise, and prompt-response type. */
        $condition = array(
            'exercise_type' => $exerciseType,
            'prompt_type' => $promptType,
            'response_type' => $responseType,
            'card_type' => $cardtype);
        $points = $this->getPointReferencesTable()
            ->find()
            ->where([
                'exercise' => $exerciseType,
                'prompt_type' => $promptType,
                'response_type' => $responseType,
                'card_type' => $cardtype])
            ->first();
        if (!$points) {
            Log::error("Points not found for etype: " . $exerciseType
                . ", pType: " . $promptType . ", rType: " . $responseType
                . ", cType: " . $cardtype);
        }
        /* Return array with corresponding points for each skill type for this exercise */
        return array(
            'reading_pts' => $points['reading_pts'] ?? 0,
            'writing_pts' => $points['writing_pts'] ?? 0,
            'speaking_pts' => $points['speaking_pts'] ?? 0,
            'listening_pts' => $points['listening_pts'] ?? 0);
    }

    //api function for reset progress data

    private function addToReviewQueue($data)
    {
        if (isset($data['lessonframe_id'])) {
            // If activity was a lesson and frame id is set, add all cards in the frame
            $cardIds = $this->getCardsByLessonFrame($data['lessonframe_id']);
            foreach ($cardIds as $card) {
                $this->addCardToReviewQueue($data['user_id'], $card, $data['lessonframe_id']);
            }
        } elseif (isset($data['card_id'])) {
            // otherwise, add all the cards experienced in the activity, or just the single card
            // if experiencecard_ids was not set
            if (isset($data['experiencecard_ids']) && $data['experiencecard_ids'] != '') {
                $cardIds = explode(",", $data['experiencecard_ids']);
                foreach ($cardIds as $card) {
                    $this->addCardToReviewQueue($data['user_id'], $card);
                }
            } else {
                $this->addCardToReviewQueue($data['user_id'], $data['card_id']);
            }
        }

        return;
    }

    private function getCardsByLessonFrame($frameId)
    {
        $frame = $this->getLessonFramesTable()->get($frameId, ['contain' => ['LessonFrameBlocks']]);
        $cards = array();
        foreach ($frame['lesson_frame_blocks'] as $frame) {
            if ($frame['type'] == 'card' && $frame['card_id'] != '') {
                array_push($cards, $frame['card_id']);
            }
        }
        return $cards;
    }

    /**
     * Add card to review queue for the user, which means either add a new card
     * or update a card that's been reviewed before. The points are updated elsewhere.
     */
    private function addCardToReviewQueue($userId, $cardId, $frameId = null)
    {

        if ($cardId != '' && $userId != '') {
            // Find all review cards for this user that correspond to this card and get number of them that exist
            // to determine whether
            $condition = array('user_id' => $userId, 'card_id' => $cardId);
            $Counter = $this->getReviewQueuesTable()->find('all', ['conditions' => $condition])->count();
            $skill_type = array('reading', 'writing', 'speaking', 'listening');
            if ($Counter == 0) {
                /* If no review cards exist for this cardId and userId combination */
                if (isset($cardId)) {
                    /* Get number of experience points for this card/frame combo */
                    $xpValue = $this->getXpValue($userId, $cardId, $frameId);
                    /* For each skill type, add card/points info to the review queues.
                     This is part of the spaced-repetition algorithm */
                    foreach ($skill_type as $skill) {
                        /* Instatiate new ReviewQueues entity to fill in and insert into the table */
                        $activitiy = $this->getReviewQueuesTable()->newEmptyEntity();
                        $dataarray = array();
                        $dataarray['user_id'] = $userId;
                        $dataarray['card_id'] = $cardId;
                        $dataarray['skill_type'] = $skill;
                        $dataarray['xp_1'] = $xpValue[$skill]['xp_1'] ?? null;
                        $dataarray['xp_2'] = $xpValue[$skill]['xp_2'] ?? null;
                        $dataarray['xp_3'] = $xpValue[$skill]['xp_3'] ?? null;
                        $dataarray['xp_4'] = $xpValue[$skill]['xp_4'] ?? null;
                        $dataarray['num_times'] = 1; /* User has seen this exercise once */
                        $dataarray['daystamp'] = time() / 86400; /* Days since Unix Epoch */
                        $saveData = $this->getReviewQueuesTable()->patchEntity($activitiy, $dataarray);
                        $savedData = $this->getReviewQueuesTable()->save($saveData);
                    }
                }
            } else {
                /* Card that has been seen in the review session before */
                if (isset($cardId)) {
                    // If User already has some review cards for this card...
                    /* Get number of experience points for this card/frame combo */
                    $xpValue = $this->getXpValue($userId, $cardId, $frameId);
                    /* For each skill type, add card/points info to the review queues.
                     This is part of the spaced-repetition algorithm */
                    foreach ($skill_type as $skill) {
                        // Find first card that has that ID, skill type and user ID in the review queues
                        $condition = array('card_id' => $cardId, 'skill_type' => $skill, 'user_id' => $userId);
                        $activity = $this->getReviewQueuesTable()->find('all', ['conditions' => $condition])->first();
                        // Get details of review card
                        $activityDetails = $this->getReviewQueuesTable()->get($activity['id']);
                        // Create and fill in data array to update the review queue entity for this card
                        $dataarray = array();
                        $dataarray['user_id'] = $userId;
                        $dataarray['card_id'] = $cardId;
                        $dataarray['skill_type'] = $skill;
                        $dataarray['xp_1'] = $xpValue[$skill]['xp_1'] ?? null;
                        $dataarray['xp_2'] = $xpValue[$skill]['xp_2'] ?? null;
                        $dataarray['xp_3'] = $xpValue[$skill]['xp_3'] ?? null;
                        $dataarray['xp_4'] = $xpValue[$skill]['xp_4'] ?? null;
                        // Increment number of times user has seen this card
                        $dataarray['num_times'] = $activityDetails['num_times'] + 1;
                        // Update the last time this card with it's skill_type was reviewed
                        $dataarray['daystamp'] = time() / 86400; /* Days since Unix Epoch */
                        // Update activity details and save it to the table
                        $saveData = $this->getReviewQueuesTable()->patchEntity($activityDetails, $dataarray);
                        $savedData = $this->getReviewQueuesTable()->save($saveData);
                    }
                }
            }
        }
        return;
    }

    /**
     * Retrieves the experience points (for all four skill types) for the last
     * four activities completed by the user, where xp_1 corresponds to the last
     * activity completed, and xp_4 corresponds to the fourth to last activity completed
     * by the user.
     * TODO this could include any activity type: review, lesson, exercise, forumpost, NULL. Is this correct?
     * with a non-null cardId, activity types NULL and forumpost won't be retrieved.
     * Lesson has level_id, unit_id, lesson_id, lessonframe_id, user_unit_activity_id
     * Review has type, card_id, level_id (sometimes)
     * Exercise has type, level_id, unit_id, exercise_id (sometimes), card_id
     * Forumpost has nothing
     */
    private function getXpValue($UserId, $CardId = null, $frameId = null)
    {
        /* Set query conditions based on userId, cardId and frameId (if passed in).
         If frameid isn't null, lessons will be retrieved.
         If cardId isn't null, lesson exercises or review exercises will be retreived.
         If just UserId is non-null, any activity type may be retrieved, but always the latest four. */
        if ($frameId == null) {
            $condition = array('user_id' => $UserId, 'card_id' => $CardId);
        } else {
            $condition = array('OR' => [
                'card_id' => $CardId,
                'lessonframe_id' => $frameId
            ],
                'user_id' => $UserId);
        }
        $Activities = $this->getUserActivitiesTable()->find()->where($condition)->order(['id' => 'DESC'])->limit(4);
        $skill_type = array('reading', 'writing', 'speaking', 'listening');
        $response = array();
        $i = 1;
        /* Take the last four activities and for each skill type, get the experience points earned for it.
         xp_1 corresponds to the last activity completed by the user for this card.
         xp_4 corresponds to the 4 to last activity completed by the user for this card. */
        foreach ($Activities as $Activity) {
            foreach ($skill_type as $skill) {
                $response[$skill]['xp_' . $i] = $Activity[$skill . '_score'];
            }
            $i++;
        }
        return $response;
    }

    public function getScorePerSet()
    {

        $data = $this->request->getData();
        $activityType = $data['activity_type'];
        $level_id = $data['level_id'];
        $unit_id = $data['unit_id'];
        $userId = $data['user_id'];
        $UnitAttempt = $this->getUnitAttemptIdAndIsCompleted($level_id, $unit_id, $userId);
        $userUnitactivityId = $UnitAttempt['last_id'];
        $condition = array(
            'user_id' => $userId,
            'level_id' => $level_id,
            'unit_id' => $unit_id,
            'user_unit_activity_id' => $userUnitactivityId);
        if ($activityType == 'exercise') {
            $exercise_id = $data['exercise_id'];
            $exercise_ids = explode(",", $exercise_id);
            $condition['exercise_id IN'] = $exercise_ids;

            $score = $this->addBonusExercise($exercise_ids, $condition, $UnitAttempt);

            $UserActivities = $this->getUserActivitiesTable()->find()->where($condition);
            $response = array();

            //$response['path_score_total'] = $score['path_score'];
            $response['path_score_total'] = $UserActivities->all()->sumOf('path_score') + $score['path_score'];
            $response['review_score_total'] = $UserActivities->all()->sumOf('review_score');
            $response['social_score_total'] = $UserActivities->all()->sumOf('social_score');
            $response['reading_score_total'] = $UserActivities->all()->sumOf('reading_score');
            $response['writing_score_total'] = $UserActivities->all()->sumOf('writing_score');
            $response['speaking_score_total'] = $UserActivities->all()->sumOf('speaking_score');
            $response['listening_score_total'] = $UserActivities->all()->sumOf('listening_score');

            $response['exercise_set'] = true;
            foreach ($exercise_ids as $exercise_id) {
                $exerciseData = array(
                    'user_id' => $userId,
                    'unit_id' => $unit_id,
                    'level_id' => $level_id,
                    'exercise_id' => $exercise_id,
                    'user_unit_activity_id' => $userUnitactivityId);
                $status = $this->isCompleted($exerciseData);
                if ($status['status'] == false) {
                    $response['exercise_set'] = false;
                    break;
                }
            }

            $this->sendApiData(true, 'Result return successfully.', $response);
        } elseif ($activityType == 'lesson') {
            $lesson_id = $data['lesson_id'];
            $lesson_ids = explode(",", $lesson_id);
            $condition['lesson_id IN'] = $lesson_ids;
            $UserActivities = $this->getUserActivitiesTable()->find()->where($condition);
            $response = array();
            $response['path_score_total'] = $UserActivities->all()->sumOf('path_score');
            $response['review_score_total'] = $UserActivities->all()->sumOf('review_score');
            $response['social_score_total'] = $UserActivities->all()->sumOf('social_score');
            $response['reading_score_total'] = $UserActivities->all()->sumOf('reading_score');
            $response['writing_score_total'] = $UserActivities->all()->sumOf('writing_score');
            $response['speaking_score_total'] = $UserActivities->all()->sumOf('speaking_score');
            $response['listening_score_total'] = $UserActivities->all()->sumOf('listening_score');
            $this->sendApiData(true, 'Result return successfully.', $response);
        }
    }

    private function addBonusExercise($exerciseIds, $condition, $UnitParam)
    {
        $condition['exercise_id IN'] = $exerciseIds;
        $condition['type'] = 'wrong';
        $counter = $this->getUserActivitiesTable()->find()->where($condition)->count();
        if ($counter == 0) {
            $activitiy = $this->getUserActivitiesTable()->newEmptyEntity();
            $element = array();
            $bonus = $this->getBonusPointByKey('Quiz activity completion (per activity type per card)');
            for ($i = 1; $i < $UnitParam['attempt']; $i++) {
                $bonus = (float)$bonus / 2;
            }
            $bonus = ceil($bonus);
            $element['level_id'] = $condition['level_id'];
            $element['unit_id'] = $condition['unit_id'];
            $element['user_id'] = $condition['user_id'];
            $element['user_unit_activity_id'] = $condition['user_unit_activity_id'];
            $element['activity_type'] = 'exercise';
            $element['path_score'] = $bonus;
            $Data = $this->getUserActivitiesTable()->patchEntity($activitiy, $element);
            $savedData = $this->getUserActivitiesTable()->save($Data);
            return array('path_score' => $bonus);
        }
        return array('path_score' => 0);
    }

    public function getUnitCompleteScore()
    {
        $data = $this->request->getData();
        $level_id = $data['level_id'];
        $unit_id = $data['unit_id'];
        $userId = $data['user_id'];
        $UnitAttempt = $this->getUnitAttemptIdAndIsCompleted($data['level_id'], $data['unit_id'], $data['user_id']);
        $userUnitactivityId = $UnitAttempt['last_id'];
        $condition = array(
            'user_id' => $userId,
            'level_id' => $level_id,
            'unit_id' => $unit_id,
            'user_unit_activity_id' => $userUnitactivityId);
        $this->addBonusUnit($condition, $UnitAttempt);
        $UserActivities = $this->getUserActivitiesTable()->find()->where($condition);
        $Details = $this->getProgressTimersTable()->find();
        $reviewTimeDetails = $Details->where(['timer_type' => 'review']);
        $pathTimeDetails = $Details->where(['timer_type' => 'path']);
        $reviewTime = $reviewTimeDetails->all()->sumOf('minute_spent');
        $pathTime = $pathTimeDetails->all()->sumOf('minute_spent');

        $reviewTime = 0;
        $pathTime = 1;

        $var = $this->GCD($reviewTime, $pathTime);

        if ($reviewTime != 0 && $pathTime != 0) {
            $PathRatio = $reviewTime / $var;
            $ReviewRatio = $pathTime / $var;
            if ($PathRatio == 1 && $ReviewRatio == 1) {
                $message = 'Your approach is balanced. You are on your way to Lakȟóta mastery.';
            } elseif ($PathRatio == 2 && $ReviewRatio == 1) {
                $message = 'Mastery of Lakȟóta comes with practice. Time to do some review!';
            } elseif ($PathRatio == 3 && $ReviewRatio == 1) {
                $message = "Your fire is dwindling. Try doing some review to "
                    . "strengthen the Lakȟóta you've learned so far.";
            } elseif ($PathRatio >= 4 && $ReviewRatio <= 1) {
                $message = "You've been focusing on new lessons. "
                    . "It's time to rekindle your fire with some review.";
            } elseif ($PathRatio == 1 && $ReviewRatio == 2) {
                $message = "Your campfire is warm. Let's try some new lessons.";
            } elseif ($PathRatio == 1 && $ReviewRatio == 3) {
                $message = "You're doing well mastering what you've learned so far. "
                    . "New lessons and challenges are waiting.";
            } elseif ($PathRatio <= 1 && $ReviewRatio >= 4) {
                $message = "Your Lakȟóta fire is strong. Don't be afraid to "
                    . "follow the learning path to new challenges.";
            }
        } elseif ($pathTime == 0 && $reviewTime >= 0) {
            $message = "You've been focusing on new lessons. "
                . "It's time to rekindle your fire with some review.";
        } elseif ($pathTime > 0 && $reviewTime == 0) {
            $message = "You've been focusing on new lessons. It's time to "
                . "rekindle your fire with some review.";
        }

        $dataArray = array();
        $dataArray['path_score'] = $UserActivities->all()->sumOf('path_score');
        $dataArray['review_score'] = $UserActivities->all()->sumOf('review_score');
        $dataArray['social_score'] = $UserActivities->all()->sumOf('social_score');
        $dataArray['reading_score'] = $UserActivities->all()->sumOf('reading_score');
        $dataArray['writing_score'] = $UserActivities->all()->sumOf('writing_score');
        $dataArray['speaking_score'] = $UserActivities->all()->sumOf('speaking_score');
        $dataArray['listening_score'] = $UserActivities->all()->sumOf('listening_score');
        $dataArray['unitFire'] = $this->getUnitFire($unit_id, $userId);
        $dataArray['message'] = $message;
        $dataArray['reviewTime'] = $reviewTime;
        $dataArray['pathTime'] = $pathTime;
        $unit = $this->getUnitsTable()->get($unit_id);
        $dataArray['unitname'] = $unit['name'];
        $pathdata = $this->getPathlevelTable()
            ->find('all', ['conditions' => ['level_id' => $level_id]])
            ->first();
        if ($pathdata['learningpath_id'] != null) {
            $this->addlevelBadge($userId, $pathdata['learningpath_id']);
        }
        $this->sendApiData(true, 'Result return successfully.', $dataArray);
    }

    private function addBonusUnit($condition, $UnitParam)
    {
        $condition['type'] = 'wrong';
        $condition['activity_type'] = 'exercise';
        $counter = $this->getUserActivitiesTable()->find()->where($condition)->count();
        if ($counter == 0) {
            $bonus = $this->getBonusPointByKey('Unit Completion');
            for ($i = 1; $i < $UnitParam['attempt']; $i++) {
                $bonus = (float)$bonus / 2;
            }
            $bonus = ceil($bonus);

            $activitiy = $this->getUserActivitiesTable()->newEmptyEntity();
            $element = array();
            $element['level_id'] = $condition['level_id'];
            $element['unit_id'] = $condition['unit_id'];
            $element['user_id'] = $condition['user_id'];
            $element['user_unit_activity_id'] = $condition['user_unit_activity_id'];
            $element['path_score'] = $bonus;
            $Data = $this->getUserActivitiesTable()->patchEntity($activitiy, $element);
            $savedData = $this->getUserActivitiesTable()->save($Data);
            return array('path_score' => $bonus);
        }
        return array('path_score' => 0);
    }

    private function GCD($a, $b)
    {
        while ($b != 0) {
            $remainder = $a % $b;
            $a = $b;
            $b = $remainder;
        }
        return abs($a);
    }

    private function getUnitFire($unitId, $userId)
    {
        $unitOptions = array(
            'conditions' => array(
                'unit_id' => $unitId,
                'user_id' => $userId
            )
        );
        $UnitDetails = $this->getUnitFiresTable()->find('all', $unitOptions);
        $counter = $UnitDetails->count();
        if ($counter != 0) {
            $Details = $UnitDetails->first()->toArray();
            return array(
                'reding_percentage' => round($Details['reading_persantage']),
                'writing_percentage' => round($Details['writing_percentage']),
                'listening_percentage' => round($Details['listening_percentage']),
                'speaking_percentage' => round($Details['speaking_percentage']),
                'all_percentage' => round($Details['all_persentage'])
            );
        }
        return array(
            'reding_percentage' => 0,
            'writing_percentage' => 0,
            'listening_percentage' => 0,
            'speaking_percentage' => 0,
            'all_percentage' => 0
        );
    }

    private function addlevelBadge($userId, $pathId)
    {
        $pathOptions = array(
            'contain' => array(
                'Levels.Units' => array(
                    'conditions' => array(
                        'learningpath_id' => $pathId
                    )
                )
            )
        );
        $levelbadge = 0;
        $path = $this->getLearningpathsTable()->get($pathId, $pathOptions);
        foreach ($path->levels as $level) {
            $levelComplete = true;
            foreach ($level['units'] as $unit) {
                $percentage = $this->getUnitProgressPercentage($unit->id, $userId);
                if ($percentage < 100) {
                    $levelComplete = false;
                }
            }
            if ($levelComplete) {
                $count = $this->getUserLevelBadgesTable()
                    ->find(
                        'all',
                        ['conditions' => [
                            'user_id' => $userId,
                            'level_id' => $level['id'],
                            'path_id' => $pathId]
                        ]
                    )
                    ->count();
                if ($count == 0) {
                    $element = array();
                    $element['user_id'] = $userId;
                    $element['level_id'] = $level['id'];
                    $element['path_id'] = $pathId;
                    $activity = $this->getUserLevelBadgesTable()->newEmptyEntity();
                    $Data = $this->getUserLevelBadgesTable()->patchEntity($activity, $element);
                    $savedData = $this->getUserLevelBadgesTable()->save($Data);
                }
            }
        }
    }

    public function getReviewScoreByUserId()
    {
        $data = $this->request->getData();
        $unitId = $data['unit_id'] ?? null;
        $userId = $data['user_id'];
        $condition = array('user_id' => $userId, 'activity_type' => 'review');
        if ($unitId != null) {
            $condition['unit_id'] = $unitId;
        }

        $UserActivities = $this->getUserActivitiesTable()->find()->where($condition);
        $response = array();
        $response['path_score_total'] = $UserActivities->all()->sumOf('path_score');
        $response['review_score_total'] = $UserActivities->all()->sumOf('path_score')
            + $UserActivities->all()->sumOf('review_score')
            + $UserActivities->all()->sumOf('social_score')
            + $UserActivities->all()->sumOf('reading_score')
            + $UserActivities->all()->sumOf('writing_score')
            + $UserActivities->all()->sumOf('speaking_score')
            + $UserActivities->all()->sumOf('listening_score');
        $response['social_score_total'] = $UserActivities->all()->sumOf('social_score');
        $response['reading_score_total'] = $UserActivities->all()->sumOf('reading_score');
        $response['writing_score_total'] = $UserActivities->all()->sumOf('writing_score');
        $response['speaking_score_total'] = $UserActivities->all()->sumOf('speaking_score');
        $response['listening_score_total'] = $UserActivities->all()->sumOf('listening_score');
        $response['review_counter'] = 0;
        $reviewCounterCondition = array('user_id' => $userId);
        if ($unitId != null) {
            $reviewCounterCondition['unit_id'] = $unitId;
        }
        $counter = $this->getReviewCountersTable()
            ->find()
            ->where($reviewCounterCondition)
            ->count();
        if ($counter > 0) {
            $datacounter = $this->getReviewCountersTable()
                ->find()
                ->where($reviewCounterCondition)
                ->order(['counter' => 'DESC'])
                ->first()
                ->toArray();
            $response['review_counter'] = $datacounter['counter'];
        }

        if (!!$unitId) {
            // Compute required number of review answers to unlock next unit
            $numReviewCardsInUnit = $this->getCardUnitsTable()->numReviewCardsInUnit($unitId);
            $response['num_correct_review_answers_to_unlock_unit'] = UtilLibrary::numCorrectReviewAnswersToUnlockUnit($numReviewCardsInUnit);
        } else {
            $response['num_correct_review_answers_to_unlock_unit'] = 0;
        }

        $this->sendApiData(true, 'Result return successfully.', $response);
    }

    public function addProgressTimer()
    {
        $data = $this->request->getData();
        $userdate = $this->getUserTime($data['timestamp_offset'], 'Y-m-d');
        $timer_type = $data['timer_type'];
        $userId = $data['user_id'];
        $pathId = $data['path_id'] ?? null;
        $level_id = $data['level_id'] ?? null;
        $unit_id = $data['unit_id'] ?? null;
        $minute_spent = $data['minute_spent'];
        $timer_type = $data['timer_type'];
        $currentDate = $userdate;
        $dataArray = array();
        if ($timer_type == 'review') {
            $condition = array(
               'user_id' => $userId,
               'entry_date' => $currentDate,
               'timer_type' => 'review');
        } else {
            $condition = array(
                'path_id' => $pathId,
                'user_id' => $userId,
                'entry_date' => $currentDate,
                'unit_id' => $unit_id,
                'timer_type' => 'path');
        }

        $timeactivities = $this->getProgressTimersTable()->find()->where($condition);
        $counter = $timeactivities->count();

        if ($counter == 0) {
            $Timers = $this->getProgressTimersTable()->newEmptyEntity();
            $dataArray['minute_spent'] = $minute_spent;
        } else {
            $TimersFlag = $timeactivities->first();
            $Timers = $this->getProgressTimersTable()->get($TimersFlag['id']);
            $dataArray['minute_spent'] = $minute_spent + $TimersFlag['minute_spent'];
        }

        $dataArray['path_id'] = $pathId;
        $dataArray['level_id'] = $level_id;
        $dataArray['unit_id'] = $unit_id;
        $dataArray['user_id'] = $userId;
        $dataArray['timer_type'] = $timer_type;
        $dataArray['entry_date'] = $currentDate;
        $Data = $this->getProgressTimersTable()->patchEntity($Timers, $dataArray);
        $savedData = $this->getProgressTimersTable()->save($Data);
        $this->sendApiData(true, 'Result return successfully.', array());
    }

    public function getProgressTimer()
    {
        $data = $this->request->getData();
        $userdate = $this->getUserTime($data['timestamp_offset'], 'Y-m-d');
        $userId = $data['user_id'];
        $pathId = $data['path_id'];
        $currentDate = $userdate;
        $condition = array('path_id' => $pathId, 'user_id' => $userId, 'entry_date' => $currentDate);
        $timeactivities = $this->getProgressTimersTable()->find()->where($condition);
        $totalTimeSpend = $timeactivities->all()->sumOf('minute_spent');
        $user = $this->getUsersTable()->get($userId, ['contain' => ['Learningspeed']]);
        if (isset($user['learningspeed']['minutes'])) {
            $alocated = $user['learningspeed']['minutes'];
        } else {
            $alocated = 0;
        }

        $response = array();
        $response['time_allocated'] = $alocated;
        $response['time_spent'] = $totalTimeSpend;
        $time_remaining = $alocated - $totalTimeSpend;
        if ($time_remaining > 0) {
            $response['time_remaining'] = $time_remaining;
            $response['time_left_flag'] = true;
        } else {
            $response['time_remaining'] = 0;
            $response['time_left_flag'] = false;
        }
        $this->sendApiData(true, 'Result return successfully.', $response);
    }

    public function resetProgressData()
    {
        $data = $this->request->getData();
        $logintype = $data['type'];
        if ($logintype == 'site') {
            $user = $this->getAuthUser();
            if (isset($user) && isset($user['id'])) {
                if ($user['is_active'] == '1') {
                    $UserId = $user['id'];
                    $activity = $this->getUserActivitiesTable()->deleteAll(['user_id' => $UserId]);
                    $Timers = $this->getProgressTimersTable()->deleteAll(['user_id' => $UserId]);
                    $UnitActivities = $this->getUserUnitActivitiesTable()->deleteAll(['user_id' => $UserId]);
                    $Review = $this->getReviewQueuesTable()->deleteAll(['user_id' => $UserId]);
                    $GlobalFires = $this->getGlobalFiresTable()->deleteAll(['user_id' => $UserId]);
                    $UnitFires = $this->getUnitFiresTable()->deleteAll(['user_id' => $UserId]);
                    $Points = $this->getUserPointsTable()->deleteAll(['user_id' => $UserId]);
                    $badge = $this->getUserLevelBadgesTable()->deleteAll(['user_id' => $UserId]);
                    $ReviewCounters = $this->getReviewCountersTable()->deleteAll(['user_id' => $UserId]);
                    $this->sendApiData(true, 'Reset progress action successfully Done.', array());
                } else {
                    $this->sendApiData(
                        false,
                        'Your account is currently deactivated. Please contact admin for support.',
                        array()
                    );
                }
            } else {
                $this->sendApiData(false, 'Invalid password. Please try again.', array());
            }
        } else {
            $UserId = $data['user_id'];
            $users = $this->getUserById($UserId);
            if (isset($users) && $users[0]['registration_type'] != 'site') {
                $activity = $this->getUserActivitiesTable()->deleteAll(['user_id' => $UserId]);
                $Timers = $this->getProgressTimersTable()->deleteAll(['user_id' => $UserId]);
                $UnitActivities = $this->getUserUnitActivitiesTable()->deleteAll(['user_id' => $UserId]);
                $Review = $this->getReviewQueuesTable()->deleteAll(['user_id' => $UserId]);
                $GlobalFires = $this->getGlobalFiresTable()->deleteAll(['user_id' => $UserId]);
                $UnitFires = $this->getUnitFiresTable()->deleteAll(['user_id' => $UserId]);
                $Points = $this->getUserPointsTable()->deleteAll(['user_id' => $UserId]);
                $badge = $this->getUserLevelBadgesTable()->deleteAll(['user_id' => $UserId]);
                $this->sendApiData(true, 'Reset progress action successfully Done.', array());
            } else {
                $this->sendApiData(false, 'Invalid Authentication.Please try again.', array());
            }
        }
    }

    public function getProgressData()
    {
        $data = $this->request->getData();
        $userId = $data['user_id'];
        $pathId = $data['path_id'];
        $dataArray = array();

        $FireData = $this->getGlobalFiresTable()->find('all', ['conditions' => ['user_id IS' => $userId]])->first();
        $condition = array('user_id' => $userId);
        $UserActivities = $this->getUserActivitiesTable()->find()->where($condition);

        $condition = array('user_id' => $userId, 'activity_type' => 'review');
        $reviewUserActivities = $this->getUserActivitiesTable()->find()->where($condition);

        $Options = array(
            'conditions' => array(
                'learningpath_id' => $pathId
            ),
            'keyField' => 'id',
            'valueField' => 'level_id'
        );
        $levels = $this->getPathlevelTable()->find('list', $Options)->toArray();
        $levels = array_values($levels);

//      $dataArray['path_score'] = $UserActivities->all()->sumOf('path_score');
//      $dataArray['review_score'] = $UserActivities->all()->sumOf('review_score');
        $dataArray['social_score'] = $UserActivities->all()->sumOf('social_score');
        $dataArray['reading_score'] = $UserActivities->all()->sumOf('reading_score');
        $dataArray['writing_score'] = $UserActivities->all()->sumOf('writing_score');
        $dataArray['speaking_score'] = $UserActivities->all()->sumOf('speaking_score');
        $dataArray['listening_score'] = $UserActivities->all()->sumOf('listening_score');
        $dataArray['total_score'] = $UserActivities->all()->sumOf('path_score')
            + $UserActivities->all()->sumOf('review_score')
            + $UserActivities->all()->sumOf('social_score')
            + $UserActivities->all()->sumOf('reading_score')
            + $UserActivities->all()->sumOf('writing_score')
            + $UserActivities->all()->sumOf('speaking_score')
            + $UserActivities->all()->sumOf('listening_score');
        $dataArray['FireData'] = array(
            "fire_days" => $FireData['fire_days'] ?? 0,
            "streak_days" => $FireData['streak_days'] ?? 0,
        );

        $dataArray['review_total_score'] = $reviewUserActivities->all()->sumOf('path_score')
            + $reviewUserActivities->all()->sumOf('review_score')
            + $reviewUserActivities->all()->sumOf('social_score')
            + $reviewUserActivities->all()->sumOf('reading_score')
            + $reviewUserActivities->all()->sumOf('writing_score')
            + $reviewUserActivities->all()->sumOf('speaking_score')
            + $reviewUserActivities->all()->sumOf('listening_score');

        if (!empty($levels)) {
            $condition1 = array('user_id' => $userId, 'level_id IN' => $levels);
            $pathUserActivities = $this->getUserActivitiesTable()->find()->where($condition1);
            $dataArray['path_total_score'] = $pathUserActivities->all()->sumOf('path_score')
                + $pathUserActivities->all()->sumOf('review_score')
                + $pathUserActivities->all()->sumOf('social_score')
                + $pathUserActivities->all()->sumOf('reading_score')
                + $pathUserActivities->all()->sumOf('writing_score')
                + $pathUserActivities->all()->sumOf('speaking_score')
                + $pathUserActivities->all()->sumOf('listening_score');
        } else {
            $dataArray['path_total_score'] = 0;
        }
        $dataArray['badge'] = $this->getBadgeByUser($userId);
        $this->sendApiData(true, 'data Successfully return.', $dataArray);
    }

    public function getUserTimeDate()
    {
        echo date_default_timezone_get();
        $servertimestamp = time();
        $timestampdiff = 19800;
        $usertimestamp = $servertimestamp + $timestampdiff;
        $usertime = date('l jS \of F Y h:i:s A', $usertimestamp);
        echo $usertime;
        die;
    }

    private function getCardsByUnitId($UnitId)
    {
        $unitOptions = array(
            'conditions' => array(
                'unit_id' => $UnitId
            ),
            'keyField' => 'id',
            'valueField' => 'card_id'
        );
        $UnitDetails = $this->getCardUnitsTable()->find('list', $unitOptions)->toArray();
        $cards = array_values(array_unique($UnitDetails));
        return $cards;
    }
}
