<?php

namespace App\Controller\Api;

use App\Exceptions\RequiredFieldException;
use App\Exceptions\AuthUserException;
use App\Lib\HttpStatusCode;
use App\Lib\UtilLibrary;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\View\JsonView;
use Cake\View\XmlView;
use Cake\Log\Log;
use App\Model\Entity\Exercise;
use App\Model\Entity\UserActivity;
use Cake\Controller\Controller;
use App\Lib\RegionPolicy;
use Exception;

class AppController extends \App\Controller\AppController
{
    /**
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        // Load components specific to Api prefix
        /* For authentication to call the Api */
        $this->loadComponent('RequestHandler');
    }

    public function viewClasses(): array
    {
        return [JsonView::class, XmlView::class];
    }

    /**
     * Validate the request with the given parameters for our API
     *
     * @param array $payload
     * @param array $keys
     * @return bool
     * @throws RequiredFieldException
     */
    public function validateRequest(array $payload, array $keys): bool
    {
        $missing = [];
        $response = "";
        foreach ($keys as $item) {
            if (!isset($payload[$item])) {
                $missing[] = $item;
            }
        }
        if (count($missing) == 0) {
            return true;
        }

        $params = count($missing) == 1 ? "parameter: " : "parameters: ";
        $index = 0;
        foreach ($missing as $value) {
            count($missing) == ++$index ? $response .= $value : $response .= $value . ", ";
        }

        throw new RequiredFieldException('Request is missing ' . $params . $response, HttpStatusCode::BAD_REQUEST);
    }

    public function validateUserIsInteractingWithTheirOwnData($requestUserId) {
        $authUser = $this->getAuthUser();
        if (empty($authUser)) {
            throw new AuthUserException(
                'A user must be authenticated to fetch or modify their own data.',
                HttpStatusCode::UNAUTHORIZED
            );
        }
        if ($authUser->id != intval($requestUserId)) {
            throw new AuthUserException(
                'A user can only fetch or modify their own data.',
                HttpStatusCode::FORBIDDEN
            );
        }
        return true;
    }

    // send all the response through this function
    public function sendApiData($status, $message, $result = array(), int $statusCode = HttpStatusCode::OK)
    {
        $data = array('status' => $status, 'message' => $message, 'results' => $result);
        $message = 'valid token';
        $this->set(compact('data', 'message'));
        $this->viewBuilder()->setOption('serialize', ['data', 'message']);
        $this->response->withStatus($statusCode);

        return $this->response;
    }

    public function getUnitProgressPercentage($unitId, $userId)
    {
        try {
            // Check user unit activities first
            $unitActivity = $this->getUserUnitActivitiesTable()
                ->find()
                ->select(['id', 'percent'])
                ->where(['unit_id' => $unitId, 'user_id' => $userId])
                ->first();

            if ($unitActivity) {
                // Return cached percentage if complete
                if (!is_null($unitActivity['percent']) && $unitActivity['percent'] >= 100) {
                    return $unitActivity['percent'];
                } else {
                    // Recalculate if incomplete
                    $percent = $this->calculateUnitProgressPercentage($unitId, $userId);
                    $userUnitActivityEntity = $this->getUserUnitActivitiesTable()->get($unitActivity['id']);
                    $userUnitActivityData = $this->getUserUnitActivitiesTable()->patchEntity($userUnitActivityEntity, ['percent' => $percent]);
                    $this->getUserUnitActivitiesTable()->save($userUnitActivityData);
                    return (int)$percent;
                }
            } else {
                return -1;
            }
        } catch (\Exception $e) {
            // Handle exceptions
            Log::error("Error in getUnitProgressPercentage: " . $e->getMessage());
            return -1;
        }
    }

    public function calculateUnitProgressPercentage($unitId, $userId)
    {
        try {
            // Find correct responses for unit, excluding Match-the-pair exercises
            $correctExerciseResponsesMinusMTP = $this->getUserActivitiesTable()
                ->find()
                ->select(['UserActivities.exercise_id'])
                ->distinct(['UserActivities.exercise_id'])
                ->where([
                    'UserActivities.user_id' => $userId,
                    'UserActivities.unit_id IS' => $unitId,
                    'UserActivities.type' => 'right',
                    'UserActivities.exercise_type !=' => 'match-the-pair'
                ]);

            // Find remaining exercises including Match-the-pair
            $exercisesRemainingPlusAllMTPQuery = $this->getUnitdetailsTable()
                ->find()
                ->select(['Unitdetails.exercise_id'])
                ->distinct(['Unitdetails.exercise_id'])
                ->where([
                    'Unitdetails.unit_id' => $unitId,
                    'Unitdetails.exercise_id IS NOT NULL',
                    'Unitdetails.exercise_id NOT IN' => $correctExerciseResponsesMinusMTP
                ]);

            $exercisesRemaining = $exercisesRemainingPlusAllMTPQuery->count();

            // Handle remaining Match-the-pair exercises
            $remainingMTPExercises = $this->getExercisesTable()
                ->find()
                ->select(['id', 'noofcard'])
                ->where(['exercise_type' => 'match-the-pair', 'id IN' => $exercisesRemainingPlusAllMTPQuery])
                ->all();

            $numCorrectMtpResponses = 0;

            if ($remainingMTPExercises->count() > 0) {
                foreach ($remainingMTPExercises as $MTPExercise) {
                    if (!$MTPExercise instanceof Exercise) {
                        throw new \UnexpectedValueException('Expected an instance of Exercise.');
                    }
                    $numCorrectMtpResponses = $this->getCorrectMTPResponsesCount($unitId, $userId, $MTPExercise->get('id'));
                    if ($numCorrectMtpResponses >= 6 || $numCorrectMtpResponses >= $MTPExercise->get('noofcard')) {
                        $exercisesRemaining--;
                    }
                }
            }

            // Find remaining lesson frames
            $lessonsRemaining = $this->getRemainingLessonsCount($unitId, $userId);

            // Find total activities in the unit
            $activitiesInUnit = $this->getUnitdetailsTable()
                ->find()
                ->select(['id'])
                ->where(['unit_id' => $unitId])
                ->count();

            // Calculate and return the percentage
            if ($activitiesInUnit != 0) {
                return ($activitiesInUnit - $lessonsRemaining - $exercisesRemaining) / $activitiesInUnit * 100;
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            // Handle exceptions
            Log::error("Error in calculateUnitProgressPercentage: " . $e->getMessage());
            return 0;
        }
    }

    private function getCorrectMTPResponsesCount($unitId, $userId, $exerciseId)
    {
        $correctMTPExerciseResponses = $this->getUserActivitiesTable()
            ->find()
            ->contain(['Exercises.Exerciseoptions'])
            ->where([
                'UserActivities.user_id' => $userId,
                'UserActivities.unit_id' => $unitId,
                'UserActivities.type' => 'right',
                'UserActivities.exercise_id' => $exerciseId
            ])
            ->all();

        $exerciseOptionIds = [];
        if ($correctMTPExerciseResponses->count() > 0) {
            foreach ($correctMTPExerciseResponses as $userActivity) {
                if (!$userActivity instanceof UserActivity) {
                    throw new \UnexpectedValueException('Expected an instance of UserActivity.');
                }
                foreach ($userActivity->exercise->exerciseoptions as $option) {
                    if (is_null($option->group_id)) {
                        $exerciseOptionIds[$option->id] = true;
                    }
                }
            }
        }

        return count($exerciseOptionIds);
    }

    private function getRemainingLessonsCount($unitId, $userId)
    {
        $lessonFrameResponses = $this->getUserActivitiesTable()
            ->find()
            ->select(['lessonframe_id'])
            ->where(['user_id' => $userId, 'unit_id' => $unitId]);

        $lessonsQuery = $this->getUnitdetailsTable()
            ->find()
            ->select(['lesson_id'])
            ->where(['unit_id' => $unitId, 'lesson_id IS NOT NULL']);

        $lessonsRemaining = 0;
        foreach ($lessonsQuery as $lesson) {
            $remainingLessonFrames = $this->getLessonFramesTable()
                ->find()
                ->select(['id'])
                ->where(['lesson_id' => $lesson['lesson_id'], 'id NOT IN' => $lessonFrameResponses])
                ->count();
            if ($remainingLessonFrames > 0) {
                $lessonsRemaining++;
            }
        }

        return $lessonsRemaining;
    }

    public function getUnitAttemptIdAndIsCompleted($levelId, $unitId, $userId): array
    {
        $conditions = ['unit_id IS' => $unitId, 'user_id' => $userId];

        // Get all user unit activies for a unit -- this just gives a bunch of copies of the information you gave it
        $unitActivities = $this->getUserUnitActivitiesTable()->find('all', ['conditions' => $conditions])->order(['id' => 'DESC']);
        $numUnitActivities = $unitActivities->count();

        $response = [
            'isunitComplete' => false,
            'last_id' => null,
            'percent' => 0,
            'attempt' => $numUnitActivities,
        ];

        if ($numUnitActivities > 0) {
            $lastFlag = $unitActivities->first();
            $response['last_id'] = $lastFlag['id'];
            $response['percent'] = $lastFlag['percent'] ?? 0;

            //this just gives a copy of the unit, level, and path -- pretty pointless
            $unitOptions = array(
                'conditions' => ['unit_id IS' => $unitId],
                'order' => 'sequence',
                'contain' => array(
                    'Lessons',
                    'Lessons.Lessonframes',
                    'Lessons.Lessonframes.LessonFrameBlocks',
                    'Exercises'
                )
            );
            //get all the exercises and lessons for a unit -- again!!
            $UnitDetails = $this->getUnitdetailsTable()->find('all', $unitOptions);

            $unitComplete = 1;

            //loop through all the activities and if any is not completed set as incomplete
            foreach ($UnitDetails as $unit) {
                //requestData holds: level, unit, user, last_unit_attempt, and exercise_id or lesson_id
                $activityParams = [
                    'unit_id' => $unitId,
                    'user_id' => $userId,
                    'user_unit_activity_id' => $response['last_id']
                ];

                if ($unit->exercise_id != null) {
                    $activityParams['exercise_id'] = $unit->exercise_id;
                    $status = $this->isCompleted($activityParams);
                    if (!$status['status'] && $unitComplete) {
                        $unitComplete = 0;
                    }
                }

                if ($unit->lesson_id != null) {
                    $activityParams['lesson_id'] = $unit->lesson_id;
                    $status = $this->isCompleted($activityParams);
                    if (!$status['status'] && $unitComplete) {
                        $unitComplete = 0;
                    }
                }
            }
            $response['isunitComplete'] = ($unitComplete === 1);
        } else {
            $response['last_id'] = 1;
        }

        return $response;
    }

    //returns percentage of unit completed
    /**
     * User Activities
     *  match-the-pair: exercise_options are all Os
     *  multiple-choice: exercise_options are P, R, Os, so Os work
     *  truefalse: exercise_options is just one O
     *  anagram: exercise_options is just one O
     *
     * @param array $requestData
     * @param number $requestData.userId
     * @param number $requestData.levelId
     * @param number $requestData.unit_id
     * @param number $requestData.user_unit_activity_id
     * @param number? $requestData.exercise_id
     * @param number? $requestData.lesson_id
     */
    public function isCompleted($activityParams)
    {
        $user = $this->getAuthUser();
        $userId = $user['id'];
        $unitId = $activityParams['unit_id'] ?? null;

        $conditions = array(
            'user_id' => $user['id'],
            'unit_id IS' => $unitId
        );

        if (isset($activityParams['exercise_id'])) {
            $conditions['exercise_id'] = $activityParams['exercise_id'];
        }

        if (isset($activityParams['lesson_id'])) {
            $conditions['lesson_id'] = $activityParams['lesson_id'];
        }

        /* For exercise Completed */
        if (isset($activityParams['exercise_id'])) {
            return $this->checkExerciseCompletion($activityParams, $conditions, $userId, $unitId);
        }

        if (isset($activityParams['lesson_id'])) {
            return $this->checkLessonCompletion($activityParams, $conditions, $userId, $unitId);
        }

        return ['status' => false, 'attempted' => false];
    }

    private function checkExerciseCompletion($activityParams, $conditions, $userId, $unitId)
    {
        //find all useractivities from last unit attempt for a particular exercise
        $activities = $this->getUserActivitiesTable()->find('all', [
            'conditions' => array_merge($conditions, [
                'user_unit_activity_id IS' => $activityParams['user_unit_activity_id']
            ])
        ])->group(['UserActivities.card_id']);

        $count = $activities->count();
        $numCorrectNullActivities = $this->getUserActivitiesTable()->find('all', [
            'conditions' => array_merge($conditions, [
                'user_unit_activity_id IS' => $activityParams['user_unit_activity_id'],
                'card_id IS' => null,
                'type' => 'right'
            ])
        ])->count();
        // Cap the number of null correct activities to the number of HTML questions
        $numHtmlQuestions = $this->getExerciseNumHtmlQuestions($activityParams['exercise_id']);
        $numCorrectNullActivities = min($numHtmlQuestions, $numCorrectNullActivities);

        if ($count + $numCorrectNullActivities > 0) {
            return $this->processExerciseActivities($activityParams, $activities, $numCorrectNullActivities, $unitId, $userId);
        } else {
            return ['status' => false, 'attempted' => false];
        }
    }

    private function processExerciseActivities($activityParams, $activities, $numCorrectNullActivities, $unitId, $userId)
    {
        $exercise = $this->getExercisesTable()->get($activityParams['exercise_id'])->toArray();
        $activitiesArray = $activities->toArray();
        $exerciseType = $exercise['exercise_type'];
        $cardType = $exercise['card_type'];

        $f = false;
        $limitMCard = $exercise['noofcard'];

        foreach ($activitiesArray as $act) {
            $activityDetailsCounter = $this->getUserActivitiesTable()->find('all', [
                'conditions' => [
                    'user_id' => $userId,
                    'exercise_id' => $activityParams['exercise_id'],
                    'unit_id IS' => $unitId,
                    'card_id IS' => $act['card_id'],
                    'user_unit_activity_id' => $activityParams['user_unit_activity_id'],
                    'type' => 'right'
                ]
            ])->count();

            $cardIdStr = $act['card_id'] ? $act['card_id'] : "null";

            if ($this->checkActivityCompletion($exerciseType, $cardType, $limitMCard, $activityDetailsCounter, $numCorrectNullActivities)) {
                return ['status' => true, 'attempted' => true];
            }
        }

        return ['status' => false, 'attempted' => true];
    }

    private function checkActivityCompletion($exerciseType, $cardType, $limitMCard, $activityDetailsCounter, $numCorrectNullActivities)
    {
        if ($cardType == 'card' &&
            in_array($exerciseType, [
                'anagram', 'truefalse', 'fill_in_the_blanks', 'multiple-choice', 'recording'
            ])
        ) {
            return $activityDetailsCounter >= 1;
        } elseif ($cardType == 'custom') {
            if ($exerciseType == 'match-the-pair' && $limitMCard > $activityDetailsCounter + $numCorrectNullActivities) {
                return false;
            }
            return $activityDetailsCounter >= 1 || ($exerciseType == 'match-the-pair' && $activityDetailsCounter >= $limitMCard);
        } else {
            if ($exerciseType == 'match-the-pair' && $limitMCard > 6) {
                $limitMCard = 6;
            }
            return $limitMCard <= $activityDetailsCounter + $numCorrectNullActivities;
        }
    }

    private function getExerciseNumHtmlQuestions($exerciseId)
    {
        $exercise = $this->getExercisesTable()->get($exerciseId)->toArray();
        $exerciseOptions = $this->getExerciseoptionsTable()
            ->find('all', ['contain' => 'ExerciseCustomOptions'])
            ->where(['exercise_id' => $exerciseId])
            ->toArray();

        $questions = [];
        $responses = [];
        $choices = [];

        foreach ($exerciseOptions as $option) {
            $this->processExerciseOption(
                $option,
                $exercise['exercise_type'],
                $exercise['card_type'],
                $questions,
                $responses,
                $choices
            );
        }

        $htmlQuestions = [];
        if (is_array($questions) && !empty($questions)) {
            $htmlQuestions = array_filter($questions, function ($question) {
                return !empty($question['option']) && empty($question['option']['card_id']);
            });
        }

        return count($htmlQuestions);
    }

    private function checkLessonCompletion($activityParams, $conditions, $userId, $unitId)
    {
        $conditions = array_merge($conditions, [
            'user_unit_activity_id IS' => $activityParams['user_unit_activity_id']
        ]);

        $activities = $this->getUserActivitiesTable()->find('all', ['conditions' => $conditions]);
        $count = $activities->count();

        $lessonOptions = ['contain' => ['Lessonframes']];
        $lesson = $this->getLessonsTable()->get($activityParams['lesson_id'], $lessonOptions);
        $numFrames = count($lesson['lessonframes']);
        $numActivities = $this->getUserActivitiesTable()->find()
            ->where($conditions)
            ->group(['UserActivities.lessonframe_id'])
            ->count();

        if ($numFrames == $numActivities) {
            return ['status' => true, 'attempted' => true];
        } elseif ($numActivities > 0) {
            return ['status' => false, 'attempted' => true];
        } else {
            return ['status' => false, 'attempted' => false];
        }
    }

    public function processExerciseOption($option, $exerciseType, $cardType, &$questions, &$responses, &$choices)
    {
        switch ($exerciseType) {
            case 'multiple-choice':
                $this->processMultipleChoiceOption($option, $cardType, $questions, $responses, $choices);
                break;
            case 'match-the-pair':
                $this->processMatchPairOption($option, $cardType, $questions);
                break;
            case 'truefalse':
                $this->processTrueFalseOption($option, $cardType, $questions);
                break;
            case 'anagram':
                $this->processAnagramOption($option, $cardType, $questions);
                break;
            case 'fill_in_the_blanks':
                $this->processFillInTheBlanksOption($option, $cardType, $questions, $responses);
                break;
            case 'recording':
                $this->processRecordingOption($option, $cardType, $questions, $responses);
                break;
            default:
                break;
        }
    }

    // Process functions for multiple-choice options
    private function processMultipleChoiceOption($option, $cardType, &$questions, &$responses, &$choices)
    {
        switch ($cardType) {
            case 'card':
                $this->processCardTypeMultipleChoice($option, $questions, $responses, $choices);
                break;
            case 'card_group':
                $this->processCardGroupTypeMultipleChoice($option, $questions, $responses, $choices);
                break;
            case 'custom':
                $this->processCustomTypeMultipleChoice($option, $questions, $choices);
                break;
            default:
                break;
        }
    }

    private function processCardTypeMultipleChoice($option, &$questions, &$responses, &$choices)
    {
        switch ($option['card_type']) {
            case 'P':
                $questions[] = $option['card_id'];
                break;
            case 'R':
                $responses[] = $option['card_id'];
                break;
            case 'O':
                if (!empty($option['card_id'])) {
                    $choices[] = $option['card_id'];
                }
                if ($option['type'] == 'group' && !empty($option['group_id'])) {
                    $groups = $this->getCardcardgroupTable()
                        ->find('all', [
                            'conditions' => [
                                'card_group_id' => $option['group_id']
                            ]
                        ])->toArray();
                    foreach ($groups as $cardGroup) {
                        $choices[] = $cardGroup['card_id'];
                    }
                }
                break;
            default:
                break;
        }
    }

    private function processCardGroupTypeMultipleChoice($option, &$questions, &$responses, &$choices)
    {
        switch ($option['card_type']) {
            case 'P':
                if (!empty($option['card_id'])) {
                    $questions[] = $option['card_id'];
                    $responses[] = $option['card_id'];
                }
                break;
            case 'O':
                if (!empty($option['card_id'])) {
                    $choices[] = $option['card_id'];
                }
                break;
            default:
                break;
        }
    }

    private function processCustomTypeMultipleChoice($option, &$questions, &$choices)
    {
        switch ($option['card_type']) {
            case 'P':
                $questions[] = $option;
                break;
            case 'O':
                $choices[] = $option;
                break;
            default:
                break;
        }
    }

    // Process functions for match-the-pair options
    private function processMatchPairOption($option, $cardType, &$questions)
    {
        switch ($cardType) {
            case 'card':
            case 'card_group':
                $this->processCardTypeMatchPair($option, $questions);
                break;
            case 'custom':
                $this->processCustomTypeMatchPair($option, $questions);
                break;
            default:
                break;
        }
    }

    private function processCardTypeMatchPair($option, &$questions)
    {
        switch ($option['card_type']) {
            case 'O':
                if (!empty($option['card_id']) && !empty($option['responce_card_id'])) {
                    $questions[] = [
                        'question' => $option['card_id'],
                        'response' => $option['responce_card_id'],
                        'option' => $option
                    ];
                }
                break;
            default:
                break;
        }
    }

    private function processCustomTypeMatchPair($option, &$questions)
    {
        switch ($option['card_type']) {
            case 'O':
                $element = [];
                if (!empty($option['card_id'])) {
                    $element['PromptType'] = 'card';
                    $element['PromptCard'] = $option['card_id'];
                    $element['PromptHtmlDetails'] = '';
                } else {
                    $element['PromptType'] = 'html';
                    $element['PromptCard'] = '';
                    $element['PromptHtmlDetails'] = $option['exercise_custom_options'];
                }

                if (!empty($option['responce_card_id'])) {
                    $element['ResponseType'] = 'card';
                    $element['ResponseCard'] = $option['responce_card_id'];
                    $element['ResponseHtmlDetails'] = '';
                } else {
                    $element['ResponseType'] = 'html';
                    $element['ResponseCard'] = '';
                    $element['ResponseHtmlDetails'] = $option['exercise_custom_options'];
                }
                $element['option'] = $option;
                $questions[] = $element;
                break;
            default:
                break;
        }
    }

    // Process functions for true/false options
    private function processTrueFalseOption($option, $cardType, &$questions)
    {
        switch ($cardType) {
            case 'card':
                $this->processCardTypeTrueFalse($option, $questions);
                break;
            case 'card_group':
                $this->processCardGroupTypeTrueFalse($option, $questions);
                break;
            case 'custom':
                $this->processCustomTypeTrueFalse($option, $questions);
                break;
            default:
                break;
        }
    }

    private function processCardTypeTrueFalse($option, &$questions)
    {
        switch ($option['card_type']) {
            case 'O':
                if (!empty($option['card_id']) && !empty($option['responce_card_id'])) {
                    $questions[] = [
                        'question' => $option['card_id'],
                        'response' => $option['responce_card_id'],
                        'option' => $option
                    ];
                }
                break;
            default:
                break;
        }
    }

    private function processCardGroupTypeTrueFalse($option, &$questions)
    {
        switch ($option['card_type']) {
            case 'P':
                if (!empty($option['card_id'])) {
                    $questions[] = $option['card_id'];
                }
                break;
            default:
                break;
        }
    }

    private function processCustomTypeTrueFalse($option, &$questions)
    {
        switch ($option['card_type']) {
            case 'O':
                $element = [];
                if (!empty($option['card_id'])) {
                    $element['PromptType'] = 'card';
                    $element['PromptCard'] = $option['card_id'];
                    $element['PromptHtmlDetails'] = '';
                } else {
                    $element['PromptType'] = 'html';
                    $element['PromptCard'] = '';
                    $element['PromptHtmlDetails'] = $option['exercise_custom_options'];
                }

                if (!empty($option['responce_card_id'])) {
                    $element['ResponseType'] = 'card';
                    $element['ResponseCard'] = $option['responce_card_id'];
                    $element['ResponseHtmlDetails'] = '';
                } else {
                    $element['ResponseType'] = 'html';
                    $element['ResponseCard'] = '';
                    $element['ResponseHtmlDetails'] = $option['exercise_custom_options'];
                }
                $element['option'] = $option;
                $questions[] = $element;
                break;
            default:
                break;
        }
    }

    // Process functions for anagram options
    private function processAnagramOption($option, $cardType, &$questions)
    {
        switch ($cardType) {
            case 'card':
                $this->processCardTypeAnagram($option, $questions);
                break;
            case 'card_group':
                $this->processCardGroupTypeAnagram($option, $questions);
                break;
            default:
                break;
        }
    }

    private function processCardTypeAnagram($option, &$questions)
    {
        switch ($option['card_type']) {
            case 'O':
                $questions[] = [
                    'question' => $option['card_id'],
                    'option' => $option
                ];
                break;
            default:
                break;
        }
    }

    private function processCardGroupTypeAnagram($option, &$questions)
    {
        switch ($option['card_type']) {
            case 'P':
                if (!empty($option['card_id'])) {
                    $questions[] = [
                        'question' => $option['card_id'],
                        'option' => $option
                    ];
                }
                break;
            default:
                break;
        }
    }

    // Process functions for fill-in-the-blanks options
    private function processFillInTheBlanksOption($option, $cardType, &$questions, &$responses)
    {
        switch ($cardType) {
            case 'card':
                $this->processCardTypeFillInTheBlanks($option, $questions, $responses);
                break;
            case 'custom':
                $this->processCustomTypeFillInTheBlanks($option, $questions, $responses);
                break;
            default:
                break;
        }
    }

    private function processCardTypeFillInTheBlanks($option, &$questions, &$responses)
    {
        switch ($option['card_type']) {
            case 'P':
                if (!empty($option['card_id'])) {
                    $questions = $option;
                }
                break;
            case 'O':
                if (!empty($option['card_id'])) {
                    $responses[] = $option;
                }
                break;
            default:
                break;
        }
    }

    private function processCustomTypeFillInTheBlanks($option, &$questions, &$responses)
    {
        switch ($option['card_type']) {
            case 'P':
                $element = [];
                if (!empty($option['card_id'])) {
                    $element['PromptType'] = 'card';
                    $element['PromptCard'] = $option['card_id'];
                    $element['PromptHtmlDetails'] = '';
                } else {
                    $element['PromptType'] = 'html';
                    $element['PromptCard'] = '';
                    $element['PromptHtmlDetails'] = $option['exercise_custom_options'];
                }
                $element['option'] = $option;
                $questions[] = $element;
                break;
            case 'O':
                $responses[] = $option;
                break;
            default:
                break;
        }
    }

    // Process functions for recording options
    private function processRecordingOption($option, $cardType, &$questions, &$responses)
    {
        switch ($cardType) {
            case 'card':
                $this->processCardTypeRecording($option, $questions);
                break;
            case 'card_group':
                $this->processCardGroupTypeRecording($option, $questions);
                break;
            case 'custom':
                $this->processCustomTypeRecording($option, $questions, $responses);
                break;
            default:
                break;
        }
    }

    private function processCardTypeRecording($option, &$questions)
    {
        switch ($option['card_type']) {
            case 'P':
                if (!empty($option['card_id']) && !empty($option['responce_card_id'])) {
                    $questions[] = [
                        'question' => $option['card_id'],
                        'response' => $option['responce_card_id'],
                        'option' => $option
                    ];
                }
                break;
            default:
                break;
        }
    }

    private function processCardGroupTypeRecording($option, &$questions)
    {
        switch ($option['card_type']) {
            case 'P':
                if (!empty($option['card_id'])) {
                    $questions[] = [
                        'card_id' => $option['card_id'],
                        'option' => $option
                    ];
                }
                break;
            default:
                break;
        }
    }

    private function processCustomTypeRecording($option, &$questions, &$responses)
    {
        switch ($option['card_type']) {
            case 'P':
                $element = [];
                if (!empty($option['card_id'])) {
                    $element['PromptType'] = 'card';
                    $element['PromptCard'] = $option['card_id'];
                    $element['PromptHtmlDetails'] = '';
                } else {
                    $element['PromptType'] = 'html';
                    $element['PromptCard'] = '';
                    $element['PromptHtmlDetails'] = $option['exercise_custom_options'];
                }
                $element['ResponseCard'] = $option['responce_card_id'];
                $element['option'] = $option;
                $questions[] = $element;
                break;
            case 'O':
                $responses[] = $option;
                break;
            default:
                break;
        }
    }

    public function getForumIdAccessByUserId($userId)
    {
        $user = $userAccess = $this->getUserById($userId);
        if (empty($user)) {
            return [];
        }
        $ForumsIds = array();
        if ($user[0]['role']['role'] == UtilLibrary::ROLE_STUDENT_STR) {
            $learningpath_id = $user[0]['learningpath_id'];
            $allUnits = $this->getLevelUnitsTable()
                ->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'unit_id',
                    'conditions' => ['learningpath_id' => $learningpath_id]])
                ->toArray();
            $allUnits = array_values($allUnits);
            if (!empty($allUnits)) {
                $allAttemptUnits = array();
                $allAttemptUnits = $this->getUserActivitiesTable()
                    ->find('list', [
                        'keyField' => 'id',
                        'valueField' => 'unit_id',
                        'conditions' => ['user_id' => $userId, 'unit_id IN' => $allUnits]])
                    ->toArray();
                $allAttemptUnits = array_unique(array_values($allAttemptUnits));

                if (!empty($allAttemptUnits)) {
                    $condition = array(
                        "OR" => [
                            ["OR" => [
                                'path_id' => $learningpath_id, 'path_id IS' => null],
                                'unit_id IS' => null, 'level_id IS' => null],
                            ['unit_id IN' => $allAttemptUnits]
                        ]);
                } else {
                    $condition = array(
                        "OR" => [
                            [
                                "OR" => [
                                    'path_id' => $learningpath_id,
                                    'path_id IS' => null
                                ],
                                'unit_id IS' => null,
                                'level_id IS' => null
                            ]
                        ]);
                }
            } else {
                $condition = array(
                    "OR" => [
                        [
                            "OR" => [
                                'path_id' => $learningpath_id,
                                'path_id IS' => null
                            ],
                            'unit_id IS' => null,
                            'level_id IS' => null
                        ]
                    ]);
            }
            $Forums = $this->getForumsTable()
                ->find('list', ['keyField' => 'id', 'valueField' => 'unit_id'])
                ->where($condition)
                ->toArray();
            $ForumsIds = array_keys($Forums);
        } else {
            $Forums = $this->getForumsTable()
                ->find('list', ['keyField' => 'id', 'valueField' => 'unit_id'])
                ->toArray();
            $ForumsIds = array_keys($Forums);
        }
        return $ForumsIds;
    }

    //

    public function getUserById(int $id)
    {
        //prep query for user data

        $contain = ['Usersetting', 'Userimages', 'Roles'];
        $contain['Learningspeed'] = function ($q) {
            return $q->select([
                'Learningspeed.id',
                'Learningspeed.label',
                'Learningspeed.description',
                'Learningspeed.minutes']);
        };
        $contain['Learningpaths'] = function ($q) {
            return $q->select(['Learningpaths.id', 'Learningpaths.image_id', 'Learningpaths.label']);
        };
        $users = $this->getUsersTable()
            ->find('all')
            ->contain($contain)
            ->where(['Users.id =' => $id])
            ->toArray();

        if (empty($users)) {
            return null;
        }
        $users[0]['classroom_count'] = $this->getClassroomUsersTable()
            ->find()
            ->distinct('classroom_id')
            ->where(['user_id' => $id])
            ->count();
        if ($users[0]['id'] == $id) {
            //convert login data to registration type for signin validation steps
            if ($users[0]['google_id'] == null && $users[0]['fb_id'] == null) {
                $users['registration_type'] = 'site';
            } elseif ($users[0]['google_id'] != null && $users[0]['fb_id'] == null) {
                $users['registration_type'] = 'google';
            } elseif ($users[0]['google_id'] == null && $users[0]['fb_id'] != null) {
                $users['registration_type'] = 'fb';
            } else {
                //handle null case -- is this user properly registered?
                return null;
            }
            return $users;
        } else {
            return null;
        }
    }

    //general function user data,

    public function getBonusPointByKey($key)
    {
        $Bonus = $this->getBonusPointsTable()->find()->where(['bonus_key' => $key])->first()->toArray();
        $point = $Bonus['points'];
        return $point;
    }

    public function getBadgeByUser($userId)
    {
        $user = $this->getUsersTable()->get($userId);
        $pathID = $user['learningpath_id'];
        $LevelBadge = array();
        if (isset($pathID)) {
            $LevelBadges = $this->getUserLevelBadgesTable()
                ->find('list', ['keyField' => 'id', 'valueField' => 'level_id'])
                ->where(['user_id' => $userId]);
            $pathOptions = array(
                'contain' => array(
                    'Levels.Image'
                )
            );
            $path = $this->getLearningpathsTable()->get($pathID, []);
            foreach ($path->levels ?? [] as $level) {
                $element = array();
                $element['name'] = $level['name'];
                if ($level['image']['aws_link'] == '') {
                    $element['image'] = Configure::read('ADMIN_LINK')
                        . 'img/UploadedFile/' . $level['image']['file_name'];
                } else {
                    $element['image'] = $level['image']['aws_link'];
                }
                if ($LevelBadges->count() == 0) {
                    $element['status'] = false;
                } else {
                    $LevelBadgeArr = array_values($LevelBadges->toArray());
                    if (in_array($level['id'], $LevelBadgeArr)) {
                        $element['status'] = true;
                    } else {
                        $element['status'] = false;
                    }
                }
                $LevelBadge[] = $element;
            }
        }
        $response = array();
        $response['socialpoint'] = 0;
        $response['firebadges'] = array('streak_days' => 0, 'fire_days' => 0);
        $GlobalFire = $this->getGlobalFiresTable()->find()->where(['user_id' => $userId]);
        if ($GlobalFire->count() != 0) {
            $GlobalFire = $GlobalFire->first();
            $response['firebadges'] = array(
                'streak_days' => $GlobalFire->streak_days,
                'fire_days' => $GlobalFire->fire_days);
        }

        $response['levelbadge'] = $LevelBadge;


        $userPointDetails = $this->getUserPointsTable()->find('all', ['conditions' => ['user_id' => $userId]]);
        $counter = $userPointDetails->count();
        if ($counter != 0) {
            $PointDetails = $userPointDetails->first();
            $response['socialpoint'] = $PointDetails['social_score'];
        }
        return $response;
    }

    public function updatePointByUserId($userId)
    {
        $condition = array();
        $condition['user_id'] = $userId;
        $UserActivities = $this->getUserActivitiesTable()->find()->where($condition);
        $dataArray = array();
        $dataArray['path_score'] = $UserActivities->all()->sumOf('path_score');
        $dataArray['review_score'] = $UserActivities->all()->sumOf('review_score');
        $dataArray['social_score'] = $UserActivities->all()->sumOf('social_score');
        $dataArray['reading_score'] = $UserActivities->all()->sumOf('reading_score');
        $dataArray['writing_score'] = $UserActivities->all()->sumOf('writing_score');
        $dataArray['speaking_score'] = $UserActivities->all()->sumOf('speaking_score');
        $dataArray['listening_score'] = $UserActivities->all()->sumOf('listening_score');
        $dataArray['total_score'] = $dataArray['path_score']
            + $dataArray['review_score']
            + $dataArray['social_score']
            + $dataArray['reading_score']
            + $dataArray['writing_score']
            + $dataArray['speaking_score']
            + $dataArray['listening_score'];

        $userPointDetails = $this->getUserPointsTable()->find('all', ['conditions' => ['user_id' => $userId]]);
        $counter = $userPointDetails->count();
        if ($counter == 0) {
            $points = $this->getUserPointsTable()->newEmptyEntity();
            $dataArray['user_id'] = $userId;
            $saveData = $this->getUserPointsTable()->patchEntity($points, $dataArray);
        } else {
            $details = $userPointDetails->toArray();
            $userPoint = $this->getUserPointsTable()->get($details[0]['id']);
            $saveData = $this->getUserPointsTable()->patchEntity($userPoint, $dataArray);
        }

        $this->getUserPointsTable()->save($saveData);
        return $saveData;
    }

    public function getCardDetails($ids)
    {
        if (empty($ids)) {
            return [];
        }
        $options = array('contain' => ['Cardtype', 'image', 'video'], 'conditions' => ['Card.id IN' => $ids]);
        $cards = $this->getCardTable()->find('all', $options)->toArray();

        if (
            (UtilLibrary::isCountable($ids) && count($ids) === 1) ||
            (!UtilLibrary::isCountable($ids))
        ) {
            return $cards[0];
        } else {
            return $cards;
        }
    }

    public function getFileDetails($ids)
    {
        $options = array('conditions' => ['Files.id IN' => $ids]);
        $files = $this->getFilesTable()->find('all', $options)->toArray();
        if (count($ids) == 1) {
            return $files[0];
        } else {
            return $files;
        }
    }

    public function getAssetUrls($array = null)
    {
        $Urls = $this->getUrls($array);
        $assets = array();
        $image = array();
        $audio = array();
        $video = array();
        foreach ($Urls as $u) {
            $ext = pathinfo($u, PATHINFO_EXTENSION);
            $ext = strtolower($ext);
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $image[] = $u;
            } elseif (in_array($ext, ['mp3', 'wav', 'ogg', 'aif'])) {
                $audio[] = $u;
            } elseif (in_array($ext, array('mp4', 'webm'))) {
                $video[] = $u;
            }
        }
        $assets['image'] = $image;
        $assets['audio'] = $audio;
        $assets['video'] = $video;
        return $assets;
    }

    public function getUrls($array = null)
    {
        $result = array();
        if (!is_array($array)) {
            $array = func_get_args();
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->getUrls($value));
            } else {
                $result = array_merge($result, array($value));
            }
        }

        $result = array_unique($result);
        $urls = array();


        foreach ($result as $r) {
            if (filter_var($r, FILTER_VALIDATE_URL)) {
                array_push($urls, $r);
            }
        }
        return $urls;
    }

    /**
     * set default values for newly created user
     */
    public function setUserDefaults($data)
    {
        //get defaults that can change with path data
        $defaultLearningPath = $this->getLearningpathsTable()
            ->find('all', [])
            ->where(['Learningpaths.user_access = 1'])
            ->first();
        $defaultLearningSpeed = $this->getLearningspeedTable()->find('all', [])->first();
        $data['learningpath_id'] = $defaultLearningPath['id'];
        $data['learningspeed_id'] = $defaultLearningSpeed['id'];
        return $data;
    }

    public function setUserSettingsDefaults($id): bool
    {
        $user = $this->getUsersTable()->get($id);
        $element = array(
            'user_id' => $id,
            'profile_desc' => 'Hi, I am ' . $user['name']
                . '. I am interested in learning ' . Configure::read('LANGUAGE') . '.'
        );

        if (RegionPolicy::requiresParentalConsent($user['approximate_age'])) {
            $element['public_leaderboard'] = 0;
            $element['public_profile'] = 0;
        } else {
            $element['public_leaderboard'] = 1;
            $element['public_profile'] = 1;
        }
        $Usersettings = $this->getUserSettingsTable()->newEmptyEntity();
        $settingsData = $this->getUserSettingsTable()->patchEntity($Usersettings, $element);
        if ($this->getUserSettingsTable()->save($settingsData)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * general function for updating the user.
     * @param integer $userId ID of user
     * @param array   $data   key => value pairs where keys are the User fields
     *     to update.
     * @return bool|array false is update fails. User object if update succeeds.
     */
    public function updateUserData($userId, $data)
    {
        if (!empty($data['name'])) {
            if ($this->getBannedWordsTable()->presentInText($data['name'])) {
                throw new Exception("Name cannot contain inappropriate language");
                return;
            }
        }
        // Get user that needs updating
        $user = $this->getUsersTable()->get($userId);
        if (empty($user)) {
            Log::error("Bad User ID");
            return false;
        }

        // Update user data with those from $data argument

        foreach ($data as $key => $value) {
            if (!empty($value) || $value === "0" || $value === 0) {
                $user->$key = $value;
            } else {
                Log::warning(
                    "Got empty value for " . $key
                    . " of " . $value . " while trying to update user"
                );
            }
        }

        // Save updated User
        $user = $this->getUsersTable()->save($user);
        if (!$user) {
            Log::error("Failed to update user data");
            return false;
        }

        return $user;
    }

    /**
     * Send Mail for Api Function
     */
    protected function sendMail($parameters, $template = 'email_template', $layout = 'email_layout')
    {
        $mailfunction = Configure::read('MAILFUNCTION');

        if ($mailfunction) {
            $subject = $parameters['subject'];
            $body = $parameters['body'];

            $siteSettings = $this->getSitesettingsTable()
                ->find('list', ['keyField' => 'key', 'valueField' => 'value'])
                ->toArray();

            $email = new Mailer('default');
            $email->setEmailFormat('both')
                ->setTransport('smtp')
                ->setFrom([$siteSettings['site_email'] => $siteSettings['site_name']])
                ->setTo($parameters['param']['email'])
                ->setReplyTo($siteSettings['site_email'])
                ->setReturnPath($siteSettings['site_email'])
                ->setSubject($subject)
                ->setViewVars([
                    'emailcontent' => $body,
                    'site_settings' => $siteSettings,
                    'site_link' => Configure::read('sitepath')])
                ->viewBuilder()
                    ->setTemplate($template)
                    ->setLayout($layout);
            $email->deliver();
        }
        return 1;
    }

    protected function updateAgeFromDob($userId): void
    {
        $user = $this->getUsersTable()->get($userId);
        if (empty($user)) {
            throw new Exception("[updateAgeFromDob] User not found");
            return;
        }

        $user->approximate_age = $this->getAgeFromDob($user->dob);
        if ($this->getUsersTable()->save($user) === false) {
            Log::error("[updateAgeFromDob] Failed to update user age");
        }
    }

    protected function getAgeFromDob(?string $dob): ?int
    {
        if (empty($dob)) {
            return null;
        } else {
            return date_diff(date_create($dob), date_create('now'))->y;
        }
    }
}
