<?php

namespace App\Controller\Api;

class DownloadController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Mail');
    }

    public function downloadUnitdata()
    {
        $data = $this->request->getData();
        $unitId = $data['unit_id'];
        $userId = $data['user_id'];
        $unitOptions = array(
            'conditions' => array(
                'unit_id' => $unitId
            ),
            'order' => 'sequence',
            'contain' => array(
                'Lessons',
                'Lessons.Lessonframes',
                'Lessons.Lessonframes.LessonFrameBlocks',
                'Exercises',
                'Exercises.Exerciseoptions',
                'Exercises.Exerciseoptions.ExerciseCustomOptions'
            )
        );
        $unit = $this->getUnitdetailsTable()->find('all', $unitOptions)->toArray();
        $pathId = $unit[0]['learningpath_id'];
        $level = $this->getLevelUnitsTable()
            ->find('all', ['conditions' => ['unit_id' => $unitId, 'learningpath_id' => $pathId]])
            ->select(['level_id'])
            ->first();
        $levelId = $level['level_id'];
        $unitElements = array();
        foreach ($unit as $unitElement) {
            $accessElement = array(
                'unit_id' => $unitId,
                'path_id' => $pathId,
                'level_id' => $levelId,
                'user_id' => $userId);
            if ($unitElement['lesson'] != null) {
                $lesson = $unitElement['lesson'];
                $accessElement['lesson_id'] = $unitElement['lesson']['id'];
                $unitAttempt = $this->getUnitAttemptIdAndIsCompleted($levelId, $unitId, $userId);
                $accessElement['user_unit_activity_id'] = $unitAttempt['last_id'];
                if ($unitAttempt['isunitComplete']) {
                    $lesson['IsCompleted'] = array("status" => false);
                } else {
                    $lesson['IsCompleted'] = $this->isCompleted($accessElement);
                }
                $lesson['element_type'] = 'lesson';
                $unitElements[] = $lesson;
            } else {
                $accessElement['exercise_id'] = $unitElement['exercise']['id'];
                $exercise = $this->generateExercise($accessElement);
                $exercise['element_type'] = 'exercise';
                $unitElements[] = $exercise;
            }
        }
        $unitElements = json_decode(json_encode($unitElements), true);
        $urls = $this->getAssetUrls($unitElements);


        $userActivity = $this->getUserActivitiesTable()
            ->find('all', [
                'conditions' => [
                    'unit_id' => $unitId,
                    'user_id' => $userId,
                    'activity_type IN' => ['exercise', 'lesson']
                ],
                'order' => ['id DESC']])
            ->select(['id']);
        $userActivityFlag = $userActivity->count();
        if ($userActivityFlag > 0) {
            $userActivityFlag = $userActivity->first();
            $lastActivityId = $userActivityFlag['id'];
        } else {
            $lastActivityId = null;
        }

        $userUnitActivity = $this->getUserUnitActivitiesTable()
            ->find('all', [
                'conditions' => [
                    'unit_id' => $unitId,
                    'user_id' => $userId
                ],
                'order' => ['id DESC']]);
        $round = $userUnitActivity->count();
        if ($round > 0) {
            $userUnitActivityFlag = $userUnitActivity->first();
            $lastUnitActivityId = $userUnitActivityFlag['id'];
        } else {
            $lastUnitActivityId = null;
        }

        $response = array(
            'data' => $unitElements,
            'assets' => $urls,
            'round' => $round,
            'last_unit_activity_id' => $lastUnitActivityId,
            'last_activity_id' => $lastActivityId);

        $this->sendApiData(true, 'Result Return Successfully.', $response);
    }

    private function generateExercise($data)
    {
        $id = $data['exercise_id'];
        $userId = $data['user_id'];
        $Exercise = $this->getExercisesTable()->get($id)->toArray();
        $ExercisOption = $this->getExerciseoptionsTable()
            ->find('all', ['contain' => 'ExerciseCustomOptions'])
            ->where(['exercise_id' => $id])
            ->toArray();
        $response = array();
        $response = $Exercise;

        $exerciseType = $Exercise['exercise_type'];
        $cardType = $Exercise['card_type'];
        $promteresponsetype = $Exercise['promteresponsetype'];
        $promotetype = $Exercise['promotetype'];
        $responsetype = $Exercise['responsetype'];
        $questions = array();
        $responses = array();
        $choices = array();
        foreach ($ExercisOption as $option) {
            if ($exerciseType == 'multiple-choice' && $cardType == 'card') {
                switch ($option['card_type']) {
                    case 'P':
                        $questions[] = $option['card_id'];
                        break;
                    case 'R':
                        $responses[] = $option['card_id'];
                        break;
                    case 'O':
                        if ($option['card_id'] != '') {
                            $choices[] = $option['card_id'];
                        }
                        if ($option['type'] == 'group' && $option['group_id'] != '') {
                            $groups = $this->getCardcardgroupTable()
                                ->find('all', ['conditions' => ['card_group_id' => $option['group_id']]])
                                ->toArray();
                            foreach ($groups as $cardGroup) {
                                $choices[] = $cardGroup['card_id'];
                            }
                        }
                        break;
                    default:
                        break;
                }
            } elseif ($exerciseType == 'multiple-choice' && $cardType == 'card_group') {
                switch ($option['card_type']) {
                    case 'P':
                        if ($option['card_id'] != '') {
                            $questions[] = $option['card_id'];
                            $responses[] = $option['card_id'];
                        }
                        break;
                    case 'O':
                        if ($option['card_id'] != '') {
                            $choices[] = $option['card_id'];
                        }
                        break;
                    default:
                        break;
                }
            } elseif ($exerciseType == 'multiple-choice' && $cardType == 'custom') {
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
            } elseif ($exerciseType == 'match-the-pair' && ($cardType == 'card' || $cardType == 'card_group')) {
                switch ($option['card_type']) {
                    case 'O':
                        if ($option['card_id'] != '' && $option['responce_card_id'] != '') {
                            $questions[] = array(
                                'question' => $option['card_id'],
                                'response' => $option['responce_card_id'],
                                'option' => $option);
                        }
                        break;
                    default:
                        break;
                }
            } elseif ($exerciseType == 'match-the-pair' && $cardType == 'custom') {
                switch ($option['card_type']) {
                    case 'O':
                        $element = array();
                        if ($option['card_id'] != '') {
                            $element['PromptType'] = 'card';
                            $element['PromptCard'] = $option['card_id'];
                            $element['PromptHtmlDetails'] = '';
                        } else {
                            $element['PromptType'] = 'html';
                            $element['PromptCard'] = '';
                            $element['PromptHtmlDetails'] = $option['exercise_custom_options'];
                        }

                        if ($option['responce_card_id'] != '') {
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
            } elseif ($exerciseType == 'truefalse' && ($cardType == 'card')) {
                switch ($option['card_type']) {
                    case 'O':
                        if ($option['card_id'] != '' && $option['responce_card_id'] != '') {
                            $questions[] = array(
                                'question' => $option['card_id'],
                                'response' => $option['responce_card_id'],
                                'option' => $option);
                        }
                        break;
                    default:
                        break;
                }
            } elseif ($exerciseType == 'truefalse' && $cardType == 'card_group') {
                switch ($option['card_type']) {
                    case 'P':
                        if ($option['card_id'] != '') {
                            $questions[] = $option['card_id'];
                            $choices[] = $option;
                        }
                        break;
                    default:
                        break;
                }
            } elseif ($exerciseType == 'truefalse' && $cardType == 'custom') {
                switch ($option['card_type']) {
                    case 'O':
                        $element = array();
                        if ($option['card_id'] != '') {
                            $element['PromptType'] = 'card';
                            $element['PromptCard'] = $option['card_id'];
                            $element['PromptHtmlDetails'] = '';
                        } else {
                            $element['PromptType'] = 'html';
                            $element['PromptCard'] = '';
                            $element['PromptHtmlDetails'] = $option['exercise_custom_options'];
                        }

                        if ($option['responce_card_id'] != '') {
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
            } elseif ($exerciseType == 'anagram' && $cardType == 'card') {
                switch ($option['card_type']) {
                    case 'O':
                        $questions[] = array('question' => $option['card_id'], 'option' => $option);
                        break;
                    default:
                        break;
                }
            } elseif ($exerciseType == 'anagram' && $cardType == 'card_group') {
                switch ($option['card_type']) {
                    case 'P':
                        if ($option['card_id'] != '') {
                            $questions[] = array('question' => $option['card_id'], 'option' => $option);
                        }
                        break;
                    default:
                        break;
                }
            } elseif ($exerciseType == 'fill_in_the_blanks' && $cardType == 'card') {
                switch ($option['card_type']) {
                    case 'P':
                        if ($option['card_id'] != '') {
                            $questions = $option;
                        }
                        break;
                    case 'O':
                        if ($option['card_id'] != '') {
                            $responses[] = $option;
                        }
                        break;
                    default:
                        break;
                }
            } elseif ($exerciseType == 'fill_in_the_blanks' && $cardType == 'custom') {
                switch ($option['card_type']) {
                    case 'P':
                        $element = array();
                        if ($option['card_id'] != '') {
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
            } elseif ($exerciseType == 'recording' && ($cardType == 'card')) {
                switch ($option['card_type']) {
                    case 'P':
                        if ($option['card_id'] != '' && $option['responce_card_id'] != '') {
                            $questions[] = array(
                                'question' => $option['card_id'],
                                'response' => $option['responce_card_id'],
                                'option' => $option);
                        }
                        break;
                    default:
                        break;
                }
            } elseif ($exerciseType == 'recording' && $cardType == 'card_group') {
                switch ($option['card_type']) {
                    case 'P':
                        if ($option['card_id'] != '') {
                            $questions[] = array('card_id' => $option['card_id'], 'option' => $option);
                        }
                        break;
                    default:
                        break;
                }
            } elseif ($exerciseType == 'recording' && $cardType == 'custom') {
                switch ($option['card_type']) {
                    case 'P':
                        $element = array();
                        if ($option['card_id'] != '') {
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
        }
        $response['questions'] = array();
        /* get last activity of user of this unit start */
//        $LastActivity = $this->getLastActivity($userId);
        /* get last activity of user of this unit End */
        $UnitAttempt = $this->getUnitAttemptIdAndIsCompleted($data['level_id'], $data['unit_id'], $data['user_id']);


        /* variable only for single card */
        if ($UnitAttempt['isunitComplete']) {
            $exerciseStatus = false;
        } else {
            $exerciseStatusFlag = $this->isCompleted($data);
            $exerciseStatus = $exerciseStatusFlag['status'];
        }


        $data['user_unit_activity_id'] = $UnitAttempt['last_id'];
        if ($exerciseType == 'multiple-choice' && $cardType == 'card') {
            $limitMCard = 4;
            if (count($choices) <= ($limitMCard - 1)) {
                shuffle($choices);
            } else {
                $choicesIndexs = array_rand($choices, ($limitMCard - 1));
                $ca = array();
                foreach ($choicesIndexs as $i) {
                    $ca[] = $choices[$i];
                }
                $choices = $ca;
            }
            $choices[] = $responses[0];
            shuffle($choices);
            foreach ($questions as $qa) {
                $QuestionResponse = array();
                $QuestionResponse['question'] = $this->getCardDetails($qa);
                $QuestionResponse['question']['exerciseOptions'] = $this->getExerciseoptionsTable()
                    ->find()
                    ->where(['exercise_id' => $id, 'card_id' => $qa])
                    ->first()
                    ->toArray();
                $QuestionResponse['response'] = $this->getCardDetails($responses);
                $QuestionResponse['response']['exerciseOptions'] = $QuestionResponse['question']['exerciseOptions'];
                //$QuestionResponse['choices'] = $this->getCardDetails($choices);
                $choicesss = $this->getCardDetails($choices);
                shuffle($choicesss);
                $QuestionResponse['choices'] = $choicesss;
                $QuestionResponse['attended'] = $exerciseStatus;
                $response['questions'][] = $QuestionResponse;
            }
        } elseif ($exerciseType == 'multiple-choice' && $cardType == 'card_group') {
            $limitMCard = $Exercise['noofcard'];
            $chooseLimit = 4;
            $wrongCards = array();

            /* get last activity of user of this unit start */
            $getAttempt = $this->getAttempt($data, $Exercise);
            if ($limitMCard > $getAttempt['remainingcardcount'] && $getAttempt['remainingcardcount'] != 0) {
                $limitMCard = $getAttempt['remainingcardcount'];
                //remove cards from Question
                foreach ($getAttempt['attendCard'] as $q) {
                    if (($key = array_search($q, $questions)) !== false) {
                        unset($questions[$key]);
                    }
                }
            }
//            else if ($getAttempt['remainingcardcount'] == 0 && count($getAttempt['attendCard']) != 0) {
//                $wrongCards = $this->getwrongCards($data);
//            }
            $wrongCards = $this->getwrongCards($data);
            /* get last activity of user of this unit End */

            $QuestionResponse = array();
            if (count($questions) <= ($limitMCard - 1)) {
                shuffle($questions);
            } else {
                $choicesIndexs = array_rand($questions, ($limitMCard));
                if (is_array($choicesIndexs)) {
                    $ca = array();
                    foreach ($choicesIndexs as $i) {
                        $ca[] = $questions[$i];
                    }
                    $questions = $ca;
                } else {
                    $questions = array($questions[$choicesIndexs]);
                }
            }

            if (
                $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                && !empty($getAttempt['attendCard'])
                && $getAttempt['remainingcardcount'] != 0
            ) {
                $questions = array_merge($getAttempt['attendCard'], $questions);
            } elseif (
                $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                && !empty($getAttempt['attendCard'])
                && $getAttempt['remainingcardcount'] == 0
                && !empty($wrongCards)
            ) {
                $questions = $getAttempt['attendCard'];
            }

            foreach ($questions as $ques) {
                $choicesFlag = $choices;
                /* Delete the prompt card from option card start */
                if (($key = array_search($ques, $choicesFlag)) !== false) {
                    unset($choicesFlag[$key]);
                }
                /* Delete the prompt card from option card End */

                if (count($choicesFlag) <= ($chooseLimit - 1)) {
                    shuffle($choicesFlag);
                } else {
                    $choicesIndexs = array_rand($choicesFlag, ($chooseLimit - 1));
                    $ca = array();
                    foreach ($choicesIndexs as $i) {
                        $ca[] = $choicesFlag[$i];
                    }
                    $choicesFlag = $ca;
                }
                $choicesFlag[] = $ques;
                shuffle($choices);
                $element = array();
                $element['question'] = $this->getCardDetails($ques);
                $element['question']['exerciseOptions'] = $this->getExerciseoptionsTable()
                    ->find()
                    ->where(['exercise_id' => $id, 'card_id' => $ques])
                    ->first()
                    ->toArray();
                $element['response'] = $this->getCardDetails($ques);
                $element['response']['exerciseOptions'] = $element['question']['exerciseOptions'];
                $choicesss = $this->getCardDetails($choicesFlag);
                shuffle($choicesss);
                $element['choices'] = $choicesss;

                if (
                    $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                    && !empty($getAttempt['attendCard'])
                    && $getAttempt['remainingcardcount'] != 0
                ) {
                    if (in_array($q, $getAttempt['attendCard'])) {
                        if (!empty($wrongCards) && in_array($q, $wrongCards)) {
                            $element['attended'] = false;
                        } else {
                            $element['attended'] = true;
                        }
                    } else {
                        $element['attended'] = false;
                    }
                } elseif (
                    $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                    && !empty($getAttempt['attendCard'])
                    && $getAttempt['remainingcardcount'] == 0 && !empty($wrongCards)
                ) {
                    if (!in_array($q, $wrongCards)) {
                        $element['attended'] = true;
                    } else {
                        $element['attended'] = false;
                    }
                } else {
                    $element['attended'] = false;
                }


//                if (count($getAttempt['attendCard']) != 0 && in_array($ques, $getAttempt['attendCard'])) {
//                    $element['attended'] = true;
//                }
//                else {
//                    $element['attended'] = false;
//                }
                $QuestionResponse[] = $element;
            }
            $response['questions'] = $QuestionResponse;
        } elseif ($exerciseType == 'multiple-choice' && $cardType == 'custom') {
            $responsechoice = array();
            foreach ($questions as $qa) {
                $element = array();
                if ($qa['card_id'] != '') {
                    $element['question'] = $this->getCardDetails(array($qa['card_id']));
                    $element['question']['PromptType'] = 'card';
                } else {
                    $element['question'] = $this->getHtmlDetails($qa['exercise_custom_options'][0], 'Prompt');
                    $element['question']['PromptType'] = 'html';
                }
                $element['question']['exerciseOptions'] = $qa;

                if ($qa['responce_card_id'] != '') {
                    $element['response'] = $this->getCardDetails(array($qa['responce_card_id']));
                    $element['response']['ResponseType'] = 'card';
                } else {
                    $element['response'] = $this->getHtmlDetails($qa['exercise_custom_options'][0], 'Response');
                    $element['response']['ResponseType'] = 'html';
                }
                $element['question']['exerciseOptions'] = $qa;
                $element['response']['exerciseOptions'] = $qa;

                $choicesArray = array();
                $choicesArray[] = $element['response'];
                foreach ($choices as $choice) {
                    $choiceElement = array();
                    if ($choice['responce_card_id'] != '') {
                        $choiceElement = $this->getCardDetails(array($choice['responce_card_id']));
                        $choiceElement['ResponseType'] = 'card';
                    } else {
                        $choiceElement = $this->getHtmlDetails($choice['exercise_custom_options'][0], 'Response');
                        $choiceElement['ResponseType'] = 'html';
                    }
                    $choiceElement['exerciseOptions'] = $choice;
                    $choicesArray[] = $choiceElement;
                }
                shuffle($choicesArray);
                $element['choices'] = $choicesArray;
                $element['attended'] = $exerciseStatus;
                $response['questions'][] = $element;
            }
        } elseif ($exerciseType == 'match-the-pair' && ($cardType == 'card' || $cardType == 'card_group')) {
            /* set limit form noof card */
            $limitMCard = $Exercise['noofcard'];
            if ($limitMCard > 6) {
                $limitMCard = 6;
            }
            $limitMCardFlag = $limitMCard;
            $wrongCards = array();

            $getAttempt = $this->getAttempt($data, $Exercise);
            if ($limitMCard > $getAttempt['remainingcardcount'] && $getAttempt['remainingcardcount'] != 0) {
                $limitMCard = $getAttempt['remainingcardcount'];
                foreach ($getAttempt['attendCard'] as $q) {
                    foreach ($questions as $key => $qDet) {
                        if ($qDet['question'] == $q) {
                            unset($questions[$key]);
                        }
                    }
                }
            } elseif ($getAttempt['remainingcardcount'] == 0 && !empty($getAttempt['attendCard'])) {
                $wrongCards = $this->getwrongCards($data);
            }

            if (count($questions) <= $limitMCard) {
                shuffle($questions);
                foreach ($questions as $q) {
                    $choices[] = $q['response'];
                }
            } else {
                $choicesIndexs = array_rand($questions, ($limitMCard));
                if (is_array($choicesIndexs)) {
                    $ca = array();
                    foreach ($choicesIndexs as $i) {
                        $ca[] = $questions[$i];
                        $choices[] = $questions[$i]['response'];
                    }
                    $questions = $ca;
                } else {
                    $cElement = $questions[$choicesIndexs];
                    $questions = array($cElement);
                    $choices[] = $cElement;
                }
            }

            if ($limitMCardFlag > $getAttempt['remainingcardcount'] && $getAttempt['remainingcardcount'] != 0) {
                $MatchPairCards = $getAttempt['attendCard'];
                foreach ($MatchPairCards as $c) {
                    $option = $this->getExerciseoptionsTable()
                        ->find()
                        ->where(['exercise_id' => $id, 'card_id' => $c])
                        ->first()
                        ->toArray();
                    $element = array('question' => $c, 'response' => $c, 'option' => $option);
                    $questions[] = $element;
                }
                shuffle($questions);
                $ca = array();
                foreach ($questions as $q1) {
                    $ca[] = $q1['response'];
                }
                $choices = $ca;
            } elseif ($getAttempt['remainingcardcount'] == 0 && !empty($wrongCards)) {
                $questions = array();
                $MatchPairCards = $getAttempt['attendCard'];
                foreach ($MatchPairCards as $c) {
                    $option = $this->getExerciseoptionsTable()
                        ->find()
                        ->where(['exercise_id' => $id, 'card_id' => $c])
                        ->first()
                        ->toArray();
                    $element = array('question' => $c, 'response' => $c, 'option' => $option);
                    $questions[] = $element;
                }
                shuffle($questions);
                $ca = array();
                foreach ($questions as $q1) {
                    $ca[] = $q1['response'];
                }
                $choices = $ca;
            }

            $questions = array_values($questions);
            $res = array();
            foreach ($questions as $q) {
                $element = array();
                $element['question'] = $this->getCardDetails($q['question']);
                $element['question']['exerciseOptions'] = $q['option'];
                $element['response'] = $this->getCardDetails($q['response']);
                $element['attended'] = $exerciseStatus;
                $res[] = $element;
            }
            $response['questions'] = $res;
            if (count($choices) == 1) {
                $response['choices'] = array($this->getCardDetails($choices));
            } else {
                $response['choices'] = $this->getCardDetails($choices);
            }


            $choicesCount = 0;
            foreach ($response['choices'] as $c) {
                $response['choices'][$choicesCount]['exerciseOptions'] = $questions[$choicesCount]['option'];
                $choicesCount++;
            }
        } elseif ($exerciseType == 'match-the-pair' && $cardType == 'custom') {
            $res = array();
            $choise = array();
            foreach ($questions as $q) {
                $element = array();
                if ($q['PromptType'] == 'card') {
                    $element['question'] = $this->getCardDetails(array($q['PromptCard']));
                    $element['question']['PromptType'] = 'card';
                    $element['question']['exerciseOptions'] = $q['option'];
                } else {
                    $element['question'] = $this->getHtmlDetails($q['PromptHtmlDetails'][0], 'Prompt');
                    $element['question']['PromptType'] = 'html';
                }

                if ($q['ResponseType'] == 'card') {
                    $element['response'] = $this->getCardDetails(array($q['ResponseCard']));
                    $element['response']['ResponseType'] = 'card';
                    $element['response']['exerciseOptions'] = $q['option'];
                } else {
                    $element['response'] = $this->getHtmlDetails($q['ResponseHtmlDetails'][0], 'Response');
                    $element['response']['ResponseType'] = 'html';
                }
                $element['attended'] = $exerciseStatus;
                $choise[] = $element['response'];
                $res[] = $element;
            }
            $response['questions'] = $res;
            shuffle($choise);
            $response['choices'] = $choise;
        } elseif ($exerciseType == 'truefalse' && ($cardType == 'card')) {
            $res = array();
            foreach ($questions as $q) {
                $element = array();
                $element['question'] = $this->getCardDetails($q['question']);
                $element['question']['exerciseOptions'] = $q['option'];
                $element['response'] = $this->getCardDetails($q['response']);
                $element['response']['exerciseOptions'] = $q['option'];
                $element['attended'] = $exerciseStatus;
                $res[] = $element;
            }
            $response['questions'] = $res;
        } elseif ($exerciseType == 'truefalse' && ($cardType == 'card_group')) {
            $res = array();
            $c = 0;
            $flagMainQuestions = $questions;
            $limitMCard = $Exercise['noofcard'];
            $wrongCards = array();

            $getAttempt = $this->getAttempt($data, $Exercise);
            if ($limitMCard > $getAttempt['remainingcardcount'] && $getAttempt['remainingcardcount'] != 0) {
                $limitMCard = $getAttempt['remainingcardcount'];
                foreach ($getAttempt['attendCard'] as $q) {
                    foreach ($questions as $key => $qDet) {
                        if ($qDet == $q) {
                            unset($questions[$key]);
                        }
                    }
                }
            }
//            else if ($getAttempt['remainingcardcount'] == 0 && count($getAttempt['attendCard']) != 0) {
//                $wrongCards = $this->getwrongCards($data);
//            }
            $wrongCards = $this->getwrongCards($data);


            if (count($questions) <= ($limitMCard - 1)) {
                shuffle($questions);
            } else {
                $choicesIndexs = array_rand($questions, ($limitMCard));
                if (is_array($choicesIndexs)) {
                    $ca = array();
                    foreach ($choicesIndexs as $i) {
                        $ca[] = $questions[$i];
                    }
                    $questions = $ca;
                } else {
                    $questions = array($questions[$choicesIndexs]);
                }
            }

            if (
                $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                && !empty($getAttempt['attendCard']) && $getAttempt['remainingcardcount'] != 0
            ) {
                $questions = array_merge($getAttempt['attendCard'], $questions);
            } elseif (
                $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                && !empty($getAttempt['attendCard'])
                && $getAttempt['remainingcardcount'] == 0 && !empty($wrongCards)
            ) {
                $questions = $getAttempt['attendCard'];
            }

//            print_r($questions);
//            die;
            foreach ($questions as $q) {
                $element = array();

                /* 50% logic */
                $flagQuestions = $flagMainQuestions;
                /* remove current card */
                array_splice($flagQuestions, $c, 1);
                /* suffele and pick one card */
                shuffle($flagQuestions);
                $index = array_rand($flagQuestions, 1);
                /* create response array and pick0 position for 50% chance */
                $flagresponseArr = array($q, $flagQuestions[$index]);
                shuffle($flagresponseArr);
                $responseCardId = $flagresponseArr[0];

                $element['question'] = $this->getCardDetails($q);
                $element['response'] = $this->getCardDetails($responseCardId);
                $element['question']['exerciseOptions'] = $choices[$c];
                $element['response']['exerciseOptions'] = $choices[$c];
                if ($responseCardId == $q) {
                    $element['question']['exerciseOptions']['response_true_false'] = 'Y';
                    $element['response']['exerciseOptions']['response_true_false'] = 'Y';
                } else {
                    $element['question']['exerciseOptions']['response_true_false'] = 'N';
                    $element['response']['exerciseOptions']['response_true_false'] = 'N';
                }

                if (
                    $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                    && !empty($getAttempt['attendCard'])
                    && $getAttempt['remainingcardcount'] != 0
                ) {
                    if (!empty($getAttempt['attendCard']) && in_array($q, $getAttempt['attendCard'])) {
                        if (!empty($wrongCards) && in_array($q, $wrongCards)) {
                            $element['attended'] = false;
                        } else {
                            $element['attended'] = true;
                        }
                    } else {
                        $element['attended'] = false;
                    }
                } elseif (
                    $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                    && !empty($getAttempt['attendCard'])
                    && $getAttempt['remainingcardcount'] == 0
                    && !empty($wrongCards)
                ) {
                    if (!empty($wrongCards) && !in_array($q, $wrongCards)) {
                        $element['attended'] = true;
                    } else {
                        $element['attended'] = false;
                    }
                } else {
                    $element['attended'] = false;
                }

                $res[] = $element;
                $c++;
            }

            $response['questions'] = $res;
        } elseif ($exerciseType == 'truefalse' && ($cardType == 'custom')) {
            $res = array();
            foreach ($questions as $q) {
                $elementTrueFalse = array();
                $elementTrueFalse['question'] = array();
                if ($q['PromptType'] == 'card') {
                    $elementTrueFalse['question'] = $this->getCardDetails(array($q['PromptCard']));
                    $elementTrueFalse['question']['PromptType'] = 'card';
                } else {
                    $elementTrueFalse['question'] = $this->getHtmlDetails($q['PromptHtmlDetails'][0], 'Prompt');
                    $elementTrueFalse['question']['PromptType'] = 'html';
                }
                $elementTrueFalse['question']['exerciseOptions'] = $q['option'];

                $elementTrueFalse['response'] = array();
                if ($q['ResponseType'] == 'card') {
                    $elementTrueFalse['response'] = $this->getCardDetails(array($q['ResponseCard']));
                    $elementTrueFalse['response']['ResponseType'] = 'card';
                } else {
                    $elementTrueFalse['response'] = $this->getHtmlDetails($q['ResponseHtmlDetails'][0], 'Response');
                    $elementTrueFalse['response']['ResponseType'] = 'html';
                }
                $elementTrueFalse['response']['exerciseOptions'] = $q['option'];
                $elementTrueFalse['attended'] = $exerciseStatus;
                $res[] = $elementTrueFalse;
            }
            $response['questions'] = $res;
        } elseif ($exerciseType == 'anagram' && ($cardType == 'card' || $cardType == 'card_group')) {
            $res = array();
            foreach ($questions as $q) {
                $Question = array();
                $Question['question'] = $this->getCardDetails($q['question']);
                $Question['question']['exerciseOptions'] = $q['option'];
                $Question['attended'] = $exerciseStatus;
                $res[] = $Question;
            }

            if ($cardType == 'card_group') {
                $limitMCard = $Exercise['noofcard'];
                $getAttempt = $this->getAttempt($data, $Exercise);
                if ($limitMCard > $getAttempt['remainingcardcount'] && $getAttempt['remainingcardcount'] != 0) {
                    $limitMCard = $getAttempt['remainingcardcount'];
//                  //remove cards from Question
                    foreach ($getAttempt['attendCard'] as $q) {
                        foreach ($res as $key => $qDet) {
                            if ($qDet['question']['id'] == $q) {
                                unset($res[$key]);
                            }
                        }
                    }
                }
                $wrongCards = $this->getwrongCards($data);

                if (count($res) <= $limitMCard) {
                    shuffle($res);
                } else {
                    $choicesIndexs = array_rand($res, ($limitMCard));
                    $ca = array();
                    foreach ($choicesIndexs as $i) {
                        $ca[] = $res[$i];
                    }
                    $res = $ca;
                }

                if (
                    $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                    && !empty($getAttempt['attendCard'])
                    && $getAttempt['remainingcardcount'] != 0
                ) {
                    $resFlag = array();
                    foreach ($getAttempt['attendCard'] as $c) {
                        $Question = array();
                        $Question['question'] = $this->getCardDetails($c);
                        $Question['question']['exerciseOptions'] = $this->getExerciseoptionsTable()
                            ->find()
                            ->where(['exercise_id' => $data['exercise_id'], 'card_id' => $c])
                            ->first()
                            ->toArray();
                        $Question['attended'] = true;
                        $resFlag[] = $Question;
                    }
                    $res = array_merge($res, $resFlag);
                } elseif (
                    $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                    && !empty($getAttempt['attendCard'])
                    && $getAttempt['remainingcardcount'] == 0
                    && !empty($wrongCards)
                ) {
                    $resFlag = array();
                    foreach ($getAttempt['attendCard'] as $c) {
                        $Question = array();
                        $Question['question'] = $this->getCardDetails($c);
                        $Question['question']['exerciseOptions'] = $this->getExerciseoptionsTable()
                            ->find()
                            ->where(['exercise_id' => $data['exercise_id'], 'card_id' => $c])
                            ->first()
                            ->toArray();
                        $Question['attended'] = true;
                        $resFlag[] = $Question;
                    }
                    $res = $resFlag;
                }
            }
            $response['questions'] = $res;
        } elseif ($exerciseType == 'fill_in_the_blanks' && $cardType == 'card') {
            $res = array();
            $Question['question'] = $this->getCardDetails($questions['card_id']);
            $Question['question']['exerciseOptions'] = $questions;
            $responseOption = array();
            if ($responses[0]['fill_in_the_blank_type'] == 'mcq') {
                shuffle($responses);
            }
            foreach ($responses as $r) {
                $responseOption[] = array('option_name' => $r['text_option'], 'position' => $r['option_position']);
            }
            //$Question['response']=$responseOption;
            $Question['choices'] = $responseOption;
            $Question['attended'] = $exerciseStatus;
            $response['questions'] = array($Question);
        } elseif ($exerciseType == 'fill_in_the_blanks' && $cardType == 'custom') {
            $res = array();
            $choise = array();
            foreach ($questions as $q) {
                $element = array();
                if ($q['PromptType'] == 'card') {
                    $element['question'] = $this->getCardDetails(array($q['PromptCard']));
                    $element['question']['PromptType'] = 'card';
                } else {
                    $element['question'] = $this->getHtmlDetails($q['PromptHtmlDetails'][0], 'Prompt');
                    $element['question']['PromptType'] = 'html';
                }
                $element['question']['exerciseOptions'] = $q['option'];
                $element['question']['exerciseOptions']['fill_in_the_blank_type'] = 'typing';
                $res = $element;
            }
            $responseOption = array();
            foreach ($responses as $r) {
                $responseOption[] = array('option_name' => $r['text_option'], 'position' => $r['option_position']);
            }
            $res['choices'] = $responseOption;
            $res['attended'] = $exerciseStatus;
            $response['questions'] = array($res);
        } elseif ($exerciseType == 'recording' && $cardType == 'card') {
            $element = array();
            $element['question'] = $this->getCardDetails($questions[0]['question']);
            $element['question']['exerciseOptions'] = $questions[0]['option'];
            $element['response'] = $this->getCardDetails($questions[0]['response']);
            $element['response']['exerciseOptions'] = $questions[0]['option'];
            $element['attended'] = $exerciseStatus;
            $response['questions'] = array($element);
        } elseif ($exerciseType == 'recording' && $cardType == 'card_group') {
            $wrongCards = array();
            $limitMCard = $Exercise['noofcard'];
            $getAttempt = $this->getAttempt($data, $Exercise);
            if ($limitMCard > $getAttempt['remainingcardcount'] && $getAttempt['remainingcardcount'] != 0) {
                $limitMCard = $getAttempt['remainingcardcount'];
                //remove cards from Question
                foreach ($getAttempt['attendCard'] as $q) {
                    foreach ($questions as $key => $val) {
                        if ($val['card_id'] == $q) {
                            unset($questions[$key]);
                        }
                    }
                }
            }
//            else if ($getAttempt['remainingcardcount'] == 0 && count($getAttempt['attendCard']) != 0) {
//                $wrongCards = $this->getwrongCards($data);
//            }
            $wrongCards = $this->getwrongCards($data);

            if (count($questions) <= ($limitMCard)) {
                shuffle($questions);
            } else {
                $choicesIndexs = array_rand($questions, ($limitMCard));
                if (is_array($choicesIndexs)) {
                    $ca = array();
                    foreach ($choicesIndexs as $i) {
                        $ca[] = $questions[$i];
                    }
                    $questions = $ca;
                } else {
                    $questions = array($questions[$choicesIndexs]);
                }
            }

            if (
                $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                && !empty($getAttempt['attendCard'])
                && $getAttempt['remainingcardcount'] != 0
            ) {
                $resFlag = array();
                foreach ($getAttempt['attendCard'] as $c) {
                    $Question = array();
                    $Question['card_id'] = $c;
                    $Question['option'] = $this->getExerciseoptionsTable()
                        ->find()
                        ->where(['exercise_id' => $data['exercise_id'], 'card_id' => $c])
                        ->first()
                        ->toArray();
                    $resFlag[] = $Question;
                }


                $questions = array_merge($resFlag, $questions);
            } elseif (
                $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                && !empty($getAttempt['attendCard'])
                && $getAttempt['remainingcardcount'] == 0
                && !empty($wrongCards)
            ) {
                $resFlag = array();
                foreach ($getAttempt['attendCard'] as $c) {
                    $Question = array();
                    $Question['card_id'] = $c;
                    $Question['option'] = $this->getExerciseoptionsTable()
                        ->find()
                        ->where(['exercise_id' => $data['exercise_id'], 'card_id' => $c])
                        ->first()
                        ->toArray();
                    $resFlag[] = $Question;
                }
                $questions = $resFlag;
            }

            $res = array();
            foreach ($questions as $q) {
                $element = array();
                $element['question'] = $this->getCardDetails($q['card_id']);
                $element['question']['exerciseOptions'] = $q['option'];
                $element['response'] = $this->getCardDetails($q['card_id']);
                $element['response']['exerciseOptions'] = $q['option'];

                if (
                    $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                    && !empty($getAttempt['attendCard'])
                    && $getAttempt['remainingcardcount'] != 0
                ) {
                    if (
                        !empty($getAttempt['attendCard'])
                        && in_array($q['card_id'], $getAttempt['attendCard'])
                    ) {
                        if (!empty($wrongCards) && in_array($q['card_id'], $wrongCards)) {
                            $element['attended'] = false;
                        } else {
                            $element['attended'] = true;
                        }
                    } else {
                        $element['attended'] = false;
                    }
                } elseif (
                    $Exercise['noofcard'] > $getAttempt['remainingcardcount']
                    && !empty($getAttempt['attendCard'])
                    && $getAttempt['remainingcardcount'] == 0
                    && !empty($wrongCards)
                ) {
                    if (!empty($wrongCards) && !in_array($q['card_id'], $wrongCards)) {
                        $element['attended'] = true;
                    } else {
                        $element['attended'] = false;
                    }
                } else {
                    $element['attended'] = false;
                }

                $res[] = $element;
            }
            $response['questions'] = $res;
        } elseif ($exerciseType == 'recording' && $cardType == 'custom') {
            $res = array();
            $choise = array();
            foreach ($questions as $q) {
                $element = array();
                if ($q['PromptType'] == 'card') {
                    $element['question'] = $this->getCardDetails(array($q['PromptCard']));
                    $element['question']['PromptType'] = 'card';
                } else {
                    $element['question'] = $this->getHtmlDetails($q['PromptHtmlDetails'][0], 'Prompt');
                    $element['question']['PromptType'] = 'html';
                }
                $element['response'] = $this->getCardDetails(array($q['ResponseCard']));
                $element['response']['exerciseOptions'] = $q['option'];
                $element['attended'] = $exerciseStatus;
                $res[] = $element;
            }
            $response['questions'] = $res;
        }
        if ($UnitAttempt['isunitComplete']) {
            $response['IsCompleted'] = array("status" => false);
        } else {
            $response['IsCompleted'] = $this->isCompleted($data);
        }
        return $response;
    }

    /*
     * multiple-choice card_group Done+
     * recording card_group Done+
     * truefalse card_group Done +
     * match-the-pair card_group need to check +
     * anagram card_group Done +
     */
    /* download  data */

    private function getAttempt($data, $Exercise)
    {
        $exerciseType = $Exercise['exercise_type'];
        $cardType = $Exercise['card_type'];
        $limitMCard = $Exercise['noofcard'];

        if ($exerciseType == 'match-the-pair' && $limitMCard > 6) {
            $limitMCard = 6;
        }
        $exerciseId = $Exercise['id'];

        $unitId = $data['unit_id'];
        $levelId = $data['level_id'];
        $userId = $data['user_id'];
        $userUnitactivityId = $data['user_unit_activity_id'];
        $response = array();
        $activities = $this->getUserActivitiesTable()
            ->find('all', [
                'conditions' => [
                    'user_id' => $userId,
                    'exercise_id' => $exerciseId,
                    'unit_id' => $unitId,
                    'level_id' => $levelId,
                    'user_unit_activity_id' => $userUnitactivityId
                ]])
            ->group(['UserActivities.card_id']);
        $count = $activities->count();
        $response['remainingcardcount'] = $limitMCard - $count;
        $cardsArr = $activities->select(['card_id'])->toArray();
        $cards = array();

        foreach ($cardsArr as $card) {
            $cards[] = $card['card_id'];
        }
        $response['attendCard'] = $cards;
        return $response;
    }

    private function getwrongCards($data)
    {
        $exerciseId = $data['exercise_id'];
        $unitId = $data['unit_id'];
        $levelId = $data['level_id'];
        $userId = $data['user_id'];
        $userUnitactivityId = $data['user_unit_activity_id'];
        $activities = $this->getUserActivitiesTable()
            ->find('all', [
                'conditions' => [
                    'user_id' => $userId,
                    'exercise_id' => $exerciseId,
                    'unit_id' => $unitId,
                    'level_id' => $levelId,
                    'user_unit_activity_id' => $userUnitactivityId
                ]])
            ->group(['UserActivities.card_id'])->toArray();

        $cards = array();
        foreach ($activities as $card) {
            $cardflag = $this->getUserActivitiesTable()
                ->find('all', [
                    'conditions' => [
                        'user_id' => $userId,
                        'exercise_id' => $exerciseId,
                        'unit_id' => $unitId,
                        'level_id' => $levelId,
                        'card_id' => $card['card_id'],
                        'type' => 'right',
                        'user_unit_activity_id' => $userUnitactivityId
                    ]]);
            $counter = $cardflag->count();
            if ($counter == 0) {
                $cards[] = $card['card_id'];
            }
        }
        return $cards;
    }

    private function getHtmlDetails($data, $Type)
    {
        $element = array();
        $element['id'] = $data['id'];
        $element['exercise_id'] = $data['exercise_id'];
        $element['exercise_option_id'] = $data['exercise_option_id'];
        $element['prompt_image_id'] = $data['prompt_image_id'];
        $element['prompt_audio_id'] = $data['prompt_audio_id'];
        $element['prompt_html'] = $data['prompt_html'];
        $element['response_audio_id'] = $data['response_audio_id'];
        $element['response_image_id'] = $data['response_image_id'];
        $element['response_html'] = $data['response_html'];
        if ($Type == 'Prompt') {
            if (isset($data['prompt_audio_id']) && $data['prompt_audio_id'] != '') {
                $element['audio'] = $this->getFileDetails(array($data['prompt_audio_id']));
            } else {
                $element['audio'] = null;
            }
            if (isset($data['prompt_image_id']) && $data['prompt_image_id'] != '') {
                $element['image'] = $this->getFileDetails(array($data['prompt_image_id']));
            } else {
                $element['image'] = null;
            }
        } else {
            if (isset($data['response_audio_id']) && $data['response_audio_id'] != '') {
                $element['audio'] = $this->getFileDetails(array($data['response_audio_id']));
            } else {
                $element['audio'] = null;
            }
            if (isset($data['response_image_id']) && $data['response_image_id'] != '') {
                $element['image'] = $this->getFileDetails(array($data['response_image_id']));
            } else {
                $element['image'] = null;
            }
        }

        return $element;
    }

    public function syncActivityData()
    {
        $data = $this->request->getData();
        $unit_id = $data['unit_id'];
        $user_id = $data['user_id'];
        $last_activity_id = $data['last_activity_id'];
        $last_unit_activity_id = $data['last_unit_activity_id'];

        die;
        /*
         * for offline data upload
         * ===================================
         * 1. Delete attempted data from online
         * 2. upload data attempted in offline.
         *
         * for offline data upload
         * ===================================
         * 1. Delete attempted data from online
         * 2. upload data attempted in offline.
         */

        $level = $this->getUserUnitActivitiesTable()
            ->find('all', ['conditions' => ['unit_id' => $unit_id, 'learningpath_id' => $path_id]])
            ->select(['level_id'])
            ->first();
        $level = $this->getUserActivitiesTable()
            ->find('all', ['conditions' => ['unit_id' => $unit_id, 'learningpath_id' => $path_id]])
            ->select(['level_id'])
            ->first();


        $this->sendApiData(true, 'Result Return Successfully.', $data);
    }
}
