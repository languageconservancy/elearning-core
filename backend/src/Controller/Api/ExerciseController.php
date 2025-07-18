<?php

namespace App\Controller\Api;

use Cake\Core\Configure;
use Cake\Log\Log;
use App\Lib\HttpStatusCodes;
use FFMpeg;

class ExerciseController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('FilesCommon');
        $this->loadComponent('Mail');
    }

    public function getExercise()
    {
        $data = $this->request->getData();
        if (!empty($data['exercise_id'])) {
            $exercise = $this->generateExercise($data);
        } else {
            $this->sendApiData(false, 'Please Send Id.', [], HttpStatusCodes::BAD_REQUEST);
            return;
        }

        $exerciseElements = json_decode(json_encode($exercise), true);
        $exercise['assets'] = $this->getAssetUrls($exerciseElements);

        $this->sendApiData(true, 'Result return successfully.', $exercise);
    }

    private function generateExercise($data)
    {
        $exerciseId = $data['exercise_id'];

        $exercise = $this->getExercisesTable()->get($data['exercise_id'])->toArray();
        $exerciseOptions = $this->getExerciseoptionsTable()
            ->find('all', ['contain' => 'ExerciseCustomOptions'])
            ->where(['exercise_id' => $data['exercise_id']])
            ->toArray();

        $response = $exercise;

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

        $unitAttempt = $this->getUnitAttemptIdAndIsCompleted(
            $data['level_id'],
            $data['unit_id'],
            $data['user_id']
        );
        $data['user_unit_activity_id'] = $unitAttempt['last_id'];

        $this->generateQuestions(
            $exercise['card_type'],
            $exercise['exercise_type'],
            $data,
            $exercise,
            $questions,
            $responses,
            $choices,
            $response,
        );

        if ($unitAttempt['isunitComplete']) {
            $response['IsCompleted'] = ["status" => false];
        } else {
            $response['IsCompleted'] = $this->isCompleted($data);
        }

        return $response;
    }

    // Generate questions based on exercise type and card type
    private function generateQuestions($cardType, $exerciseType, $data, $exercise, &$questions, &$responses, &$choices, &$response)
    {
        switch ($exerciseType) {
            case 'multiple-choice':
                $this->generateMultipleChoiceQuestions($cardType, $data, $exercise, $questions, $responses, $choices, $response);
                break;
            case 'match-the-pair':
                $this->generateMatchPairQuestions($cardType, $data, $exercise, $questions, $choices, $responses, $response);
                break;
            case 'truefalse':
                $this->generateTrueFalseQuestions($cardType, $data, $exercise, $questions, $choices, $response);
                break;
            case 'anagram':
                $this->generateAnagramQuestions($cardType, $data, $exercise, $questions, $response);
                break;
            case 'fill_in_the_blanks':
                $this->generateFillInTheBlanksQuestions($cardType, $questions, $responses, $response);
                break;
            case 'recording':
                $this->generateRecordingQuestions($cardType, $questions);
                break;
            default:
                break;
        }
    }

    private function generateMultipleChoiceQuestions($cardType, $data, $exercise, &$questions, &$responses, &$choices, &$response)
    {
        switch ($cardType) {
            case 'card':
                $this->generateCardTypeMultipleChoiceQuestions($exercise, $questions, $responses, $choices, $response);
                break;
            case 'card_group':
                $this->generateCardGroupTypeMultipleChoiceQuestions($data, $exercise, $response, $choices, $questions, $response);
                break;
            case 'custom':
                $this->generateCustomTypeMultipleChoiceQuestions($exercise, $choices, $questions, $response);
                break;
            default:
                // Throw error
                Log::error("Invalid card type: " . $cardType);
                break;
        }
    }

    private function generateCardTypeMultipleChoiceQuestions($exercise, $questions, $responses, &$choices, &$response)
    {
        $limitMCard = 4;
        if (count($choices) > ($limitMCard - 1)) {
            $choicesIndexs = array_rand($choices, ($limitMCard - 1));
            $ca = array();
            foreach ($choicesIndexs as $i) {
                $ca[] = $choices[$i];
            }
            $choices = $ca;
        }
        // Add the correct response to the choices
        $choices[] = $responses[0];
        foreach ($questions as $qa) {
            $questionResponse = array();
            $questionResponse['question'] = $this->getCardDetails($qa);
            $questionResponse['question']['exerciseOptions'] = $this->getExerciseoptionsTable()
                ->find()
                ->where([
                    'exercise_id' => $exercise['id'],
                    'card_id' => $qa
                ])
                ->first()
                ->toArray();
            $questionResponse['response'] = $this->getCardDetails($responses);
            $questionResponse['response']['exerciseOptions'] = $questionResponse['question']['exerciseOptions'];
            $questionResponse['response']['exerciseOptions']['prompt_preview_option'] = $exercise['promotetype'];
            $questionResponse['response']['exerciseOptions']['responce_preview_option'] = $exercise['responsetype'];
            $choiceCards = $this->getCardDetails($choices);
            shuffle($choiceCards);
            $questionResponse['choices'] = $choiceCards;
            $response['questions'][] = $questionResponse;
        }
    }

    private function generateCardGroupTypeMultipleChoiceQuestions($data, $exercise, $responses, &$choices, &$questions, &$response)
    {
        $maxNumQuestions = $exercise['noofcard'];
        $maxNumChoices = 4;
        $cardsUserHasntAnsweredCorrectly = array();

        $getAttempt = $this->getAttempt($data, $exercise);
        if ($getAttempt['remainingcardcount'] < $maxNumQuestions && $getAttempt['remainingcardcount'] > 0) {
            $maxNumQuestions = $getAttempt['remainingcardcount'];
            // remove cards from Question that are already attempted
            $this->removeAttemptedCardsFromQuestions($questions, $getAttempt['attendCard']);
        } elseif ($getAttempt['remainingcardcount'] == 0 && !empty($getAttempt['attendCard'])) {
            // All cards are attempted, so get the cards the user has not answered correctly
            $cardsUserHasntAnsweredCorrectly = $this->getWrongCards($data);
        }

        $questionResponse = [];
        if (count($questions) <= ($maxNumQuestions - 1)) {
            shuffle($questions);
        } else {
            // reduce the number of questions to the maxNumQuestions
            $selectQuestionIndices = array_rand($questions, ($maxNumQuestions));

            if (is_array($selectQuestionIndices)) {
                $selectQuestionsArray = array();
                foreach ($selectQuestionIndices as $i) {
                    $selectQuestionsArray[] = $questions[$i];
                }
                $questions = $selectQuestionsArray;
            } else {
                $questions = array($questions[$selectQuestionIndices]);
            }
        }
        if (!empty($cardsUserHasntAnsweredCorrectly)) {
            $questions = $cardsUserHasntAnsweredCorrectly;
        }

        foreach ($questions as $ques) {
            $choicesArray = $choices;
            // delete the prompt card from option cards, since it's in the responses array
            if (($key = array_search($ques, $choicesArray)) !== false) {
                unset($choicesArray[$key]);
            }

            if (count($choicesArray) > ($maxNumChoices - 1)) {
                $choicesIndexs = array_rand($choicesArray, ($maxNumChoices - 1));
                $ca = array();
                foreach ($choicesIndexs as $i) {
                    $ca[] = $choicesArray[$i];
                }
                $choicesArray = $ca;
            }
            $choicesArray[] = $ques;
            $element = array();
            $element['question'] = $this->getCardDetails($ques);
            $element['question']['exerciseOptions'] = $this->getExerciseoptionsTable()
                ->find()
                ->where([
                    'exercise_id' => $exercise['id'],
                    'card_id' => $ques
                ])
                ->first()
                ->toArray();
            $element['response'] = $this->getCardDetails($ques);
            $element['response']['exerciseOptions'] = $element['question']['exerciseOptions'];
            $choiceCards = $this->getCardDetails($choicesArray);
            shuffle($choiceCards);
            $element['choices'] = $choiceCards;
            $questionResponse[] = $element;
        }
        $response['questions'] = $questionResponse;
    }

    private function generateCustomTypeMultipleChoiceQuestions($exercise, $choices, $questions, &$response)
    {
        foreach($questions as $qa) {
            $element = [];
            if (!empty($qa['card_id'])) {
                $element['question'] = $this->getCardDetails(array($qa['card_id']));
                $element['question']['PromptType'] = 'card';
            } else {
                $element['question'] = $this->getHtmlDetails($qa['exercise_custom_options'][0], 'Prompt');
                $element['question']['PromptType'] = 'html';
            }
            $element['question']['exerciseOptions'] = $qa;

            if (!empty($qa['responce_card_id'])) {
                $element['response'] = $this->getCardDetails(array($qa['responce_card_id']));
                $element['response']['ResponseType'] = 'card';
            } else {
                $element['response'] = $this->getHtmlDetails($qa['exercise_custom_options'][0], 'Response');
                $element['response']['ResponseType'] = 'html';
            }
            $element['question']['exerciseOptions'] = $qa;
            $element['response']['exerciseOptions'] = $qa;

            $choicesArray = [];
            $choicesArray[] = $element['response'];
            foreach ($choices as $choice) {
                $choiceElement = array();
                if (!empty($choice['responce_card_id'])) {
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
            $response['questions'][] = $element;
        }
    }

    private function generateMatchPairQuestions($cardType, $data, $exercise, &$questions, &$choices, &$responses, &$response)
    {
        switch ($cardType) {
            case 'card':
            case 'card_group':
                $this->generateCardTypeMatchPairQuestions($data, $exercise, $questions, $choices, $responses, $response);
                break;
            case 'custom':
                $this->generateCustomTypeMatchPairQuestions($questions, $response);
                break;
            default:
                // Throw error
                Log::error("Invalid card type: " . $cardType);
                break;
        }
    }

    private function removeAttemptedCardsFromQuestions(&$questions, $attemptedCards)
    {
        // Remove questions with question card_id in the attemptedCards ids array
        $filteredQuestions = array_filter($questions, function($question) use ($attemptedCards) {
            return !(isset($question['question']) && in_array($question['question'], $attemptedCards));
        });

        // Reindex the array to prevent gaps in array keys
        $questions = array_values($filteredQuestions);
    }

    private function generateCardTypeMatchPairQuestions($data, $exercise, &$questions, &$choices, &$responses, &$response)
    {
        /* set limit from number of cards */
        $maxNumQuestions = min($exercise['noofcard'], 6);
        $limitMCardFlag = $maxNumQuestions;
        $wrongCards = [];
        $choices = [];

        $getAttempt = $this->getAttempt($data, $exercise);
        $optionIds = array_filter(
            array_map(fn($q) => isset($q['option']) && is_object($q['option']) ? $q['option']->card_id : null, $questions),
            fn($id) => $id !== null
        );

        if ($getAttempt['remainingcardcount'] < $maxNumQuestions && $getAttempt['remainingcardcount'] > 0) {
            // update total number of questions to the number of remaining cards
            $maxNumQuestions = $getAttempt['remainingcardcount'];
            // remove questions that have already been completed
            $this->removeAttemptedCardsFromQuestions($questions, $getAttempt['attendCard']);
        } elseif ($getAttempt['remainingcardcount'] == 0 && !empty($getAttempt['attendCard'])) {
            $wrongCards = $this->getWrongCards($data);
        }

        if (count($questions) <= $maxNumQuestions) {
            // Number of questions within limit. Just shuffle questions
            // and create choices array.
            shuffle($questions);
            foreach ($questions as $q) {
                $choices[] = $q['response'];
            }
        } else {
            // Number of questions is greater than limit.
            $questionIndices = array_rand($questions, ($maxNumQuestions));
            if (is_array($questionIndices)) {
                // Pick limit number of questions and create choices array.
                $ca = array();
                foreach ($questionIndices as $i) {
                    $ca[] = $questions[$i];
                    $choices[] = $questions[$i]['response'];
                }
                $questions = $ca;
            } else {
                // Only one question. Create array with that question and
                // create corresonding choice array.
                // FIXME Why are we presenting a single card to the user with only a single choice?
                $cElement = $questions[$questionIndices];
                $questions = [$cElement];
                $choices[] = $cElement['response'];
            }
        }

        $optionIds = array_filter(
            array_map(fn($q) => isset($q['option']) && is_object($q['option']) ? $q['option']->card_id : null, $questions),
            fn($id) => $id !== null
        );

        if ($getAttempt['remainingcardcount'] < $limitMCardFlag && $getAttempt['remainingcardcount'] > 0) {
            // User hasn't responded to all the questions, but has at least attempted one of them.
            $MatchPairCards = $getAttempt['attendCard'];
            foreach ($MatchPairCards as $c) {
                $option = $this->getExerciseoptionsTable()
                    ->find()
                    ->where(['exercise_id' => $exercise['id'], 'card_id' => $c])
                    ->first()
                    ->toArray();
                $questions[] = [
                    'question' => $c,
                    'option' => $option,
                    'response' => $option['responce_card_id'] ?? $c
                ];
            }
            $optionIds = array_filter(
                array_map(fn($q) => !empty($q['question']) ? $q['question'] : null, $questions),
                fn($id) => $id !== null
            );
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
                    ->where(['exercise_id' => $exercise['id'], 'card_id' => $c])
                    ->first()
                    ->toArray();
                $questions[] = [
                    'question' => $c,
                    'option' => $option,
                    'response' => $option['responce_card_id'] ?? $c
                ];
            }
            shuffle($questions);
            $ca = array();
            foreach ($questions as $q1) {
                $ca[] = $q1['response'];
            }
            $choices = $ca;
        }

        $questions = array_values($questions);
        $res = [];
        foreach ($questions as $q) {
            $element = [];
            $element['question'] = $this->getCardDetails($q['question']);
            $element['question']['exerciseOptions'] = $q['option'];
            $element['response'] = $this->getCardDetails($q['response']);
            $element['response']['exerciseOptions'] = $q['option'];
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
    }

    private function generateCustomTypeMatchPairQuestions($questions, &$response)
    {
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
            $choise[] = $element['response'];
            $res[] = $element;
        }
        $response['questions'] = $res;
        shuffle($choise);
        $response['choices'] = $choise;
    }

    private function generateTrueFalseQuestions($cardType, $data, $exercise, &$questions, $choices, &$response)
    {
        switch ($cardType) {
            case 'card':
                $this->generateCardTypeTrueFalseQuestions($questions, $response);
                break;
            case 'card_group':
                $this->generateCardGroupTypeTrueFalseQuestions($data, $exercise, $choices, $questions, $response);
                break;
            case 'custom':
                $this->generateCustomTypeTrueFalseQuestions($questions, $response);
                break;
            default:
                // Throw error
                Log::error("Invalid card type: " . $cardType);
                break;
        }
    }

    private function generateCardTypeTrueFalseQuestions($questions, &$response)
    {
        $res = array();
        foreach ($questions as $q) {
            $element = array();
            $element['question'] = $this->getCardDetails($q['question']);
            $element['question']['exerciseOptions'] = $q['option'];
            $element['response'] = $this->getCardDetails($q['response']);
            $element['response']['exerciseOptions'] = $q['option'];
            $res[] = $element;
        }
        $response['questions'] = $res;
    }

    private function generateCardGroupTypeTrueFalseQuestions($data, $exercise, $choices, &$questions, &$response)
    {
        $res = array();
        $c = 0;
        $flagMainQuestions = $questions;
        $limitMCard = $exercise['noofcard'];
        $wrongCards = array();

        $getAttempt = $this->getAttempt($data, $exercise);

        if ($limitMCard > $getAttempt['remainingcardcount'] && $getAttempt['remainingcardcount'] != 0) {
            $limitMCard = $getAttempt['remainingcardcount'];
            foreach ($getAttempt['attendCard'] as $q) {
                foreach ($questions as $key => $qDet) {
                    if ($qDet == $q) {
                        unset($questions[$key]);
                    }
                }
            }
        } elseif ($getAttempt['remainingcardcount'] == 0 && !empty($getAttempt['attendCard'])) {
            $wrongCards = $this->getWrongCards($data);
        }
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

        if (!empty($wrongCards)) {
            $questions = $wrongCards;
        }


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
            $res[] = $element;
            $c++;
        }
        $response['questions'] = $res;
    }

    private function generateCustomTypeTrueFalseQuestions($questions, &$response) {
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
            $res[] = $elementTrueFalse;
        }
        $response['questions'] = $res;
    }

    private function generateAnagramQuestions($cardType, $data, $exercise, $questions, &$response)
    {
        switch ($cardType) {
            case 'card':
            case 'card_group':
                $this->generateCardTypeAnagramQuestions($cardType, $data, $exercise, $questions, $response);
                break;
            default:
                // Throw error
                Log::error("Invalid card type: " . $cardType);
                break;
        }
    }

    private function generateCardTypeAnagramQuestions($cardType, $data, $exercise, $questions, &$response)
    {
        $res = array();
        $wrongCards = array();
        foreach ($questions as $q) {
            $Question = array();
            $Question['question'] = $this->getCardDetails($q['question']);
            $Question['question']['exerciseOptions'] = $q['option'];
            $res[] = $Question;
        }

        if ($cardType == 'card_group') {
            $limitMCard = $exercise['noofcard'];
            $getAttempt = $this->getAttempt($data, $exercise);
            if ($limitMCard > $getAttempt['remainingcardcount'] && $getAttempt['remainingcardcount'] != 0) {
                $limitMCard = $getAttempt['remainingcardcount'];
                //remove cards from Question
                foreach ($getAttempt['attendCard'] as $q) {
                    foreach ($res as $key => $qDet) {
                        if ($qDet['question']['id'] == $q) {
                            unset($res[$key]);
                        }
                    }
                }
            } elseif ($getAttempt['remainingcardcount'] == 0 && !empty($getAttempt['attendCard'])) {
                $wrongCards = $this->getWrongCards($data);
            }

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

            if (!empty($wrongCards)) {
                $resFlag = array();
                foreach ($wrongCards as $wc) {
                    $Question = array();
                    $Question['question'] = $this->getCardDetails($wc);
                    $Question['question']['exerciseOptions'] = $this->getExerciseoptionsTable()
                        ->find()
                        ->where(['exercise_id' => $data['exercise_id'], 'card_id' => $wc])
                        ->first()
                        ->toArray();
                    $resFlag[] = $Question;
                }
                $res = $resFlag;
            }
        }
        $response['questions'] = $res;
    }

    private function generateFillInTheBlanksQuestions($cardType, $questions, &$responses, &$response)
    {
        switch ($cardType) {
            case 'card':
                $this->generateCardTypeFillInTheBlanksQuestions($questions, $responses, $response);
                break;
            case 'custom':
                $this->generateCustomTypeFillInTheBlanksQuestions($questions, $responses, $response);
                break;
            default:
                // Throw error
                Log::error("Invalid card type: " . $cardType);
                break;
        }
    }

    private function generateCardTypeFillInTheBlanksQuestions($questions, &$responses, &$response)
    {
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
        $response['questions'] = array($Question);
    }

    private function generateCustomTypeFillInTheBlanksQuestions($questions, $responses, &$response)
    {
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
        $response['questions'] = array($res);
    }

    private function generateRecordingQuestions($cardType, $data, $exercise, $questions, &$response)
    {
        switch ($cardType) {
            case 'card':
                $this->generateCardTypeRecordingQuestions($questions, $response);
                break;
            case 'card_group':
                $this->generateCardGroupTypeRecordingQuestions($data, $exercise, $questions, $response);
                break;
            case 'custom':
                $this->generateCustomTypeRecordingQuestions($questions, $response);
                break;
            default:
                // Throw error
                Log::error("Invalid card type: " . $cardType);
                break;
        }
    }

    private function generateCardTypeRecordingQuestions($questions, &$response)
    {
        $element = array();
        $element['question'] = $this->getCardDetails($questions[0]['question']);
        $element['question']['exerciseOptions'] = $questions[0]['option'];
        $element['response'] = $this->getCardDetails($questions[0]['response']);
        $element['response']['exerciseOptions'] = $questions[0]['option'];
        $response['questions'] = array($element);
    }

    private function generateCardGroupTypeRecordingQuestions($data, $exercise, &$questions, &$response)
    {
        $wrongCards = array();
        $limitMCard = $exercise['noofcard'];
        $getAttempt = $this->getAttempt($data, $exercise);
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
        } elseif ($getAttempt['remainingcardcount'] == 0 && !empty($getAttempt['attendCard'])) {
            $wrongCards = $this->getWrongCards($data);
        }

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

        if (!empty($wrongCards)) {
            $resFlag = array();
            foreach ($wrongCards as $wc) {
                $Question = array();
                $Question['card_id'] = $wc;
                $Question['option'] = $this->getExerciseoptionsTable()
                    ->find()
                    ->where(['exercise_id' => $data['exercise_id'], 'card_id' => $wc])
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
            $res[] = $element;
        }
        $response['questions'] = $res;
    }

    private function generateCustomTypeRecordingQuestions($questions, &$response)
    {
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
            $res[] = $element;
        }
        $response['questions'] = $res;
    }

    private function getAttempt($data, $exercise)
    {
        $numOptionsInExercise = $exercise['noofcard'];

        // Limit the number of options to 6 for match-the-pair exercises
        if ($exercise['exercise_type'] == 'match-the-pair') {
            $numOptionsInExercise = min($numOptionsInExercise, 6);
        }

        $response = array();
        // Get the number of user activities for this exercise grouped by card_id
        $Activities = $this->getUserActivitiesTable()
            ->find('all', [
                'conditions' => [
                    'user_id' => $data['user_id'],
                    'exercise_id' => $exercise['id'],
                    'unit_id' => $data['unit_id'],
                    'level_id' => $data['level_id'],
                    'user_unit_activity_id' => $data['user_unit_activity_id']
                ]])
            ->group(['UserActivities.card_id']);
        $numUniqueUserActivities = $Activities->count();
        $response['remainingcardcount'] = $numOptionsInExercise - $numUniqueUserActivities;

        $cardsUserHasAnswered = $Activities->select(['card_id'])->toArray();
        $cards = array();
        foreach ($cardsUserHasAnswered as $card) {
            $cards[] = $card['card_id'];
        }

        $response['attendCard'] = $cards;
        return $response;
    }

    private function getWrongCards($data)
    {
        // Get all attempts for this exercise by card id
        $Activities = $this->getUserActivitiesTable()
            ->find('all', [
                'conditions' => [
                    'user_id' => $data['user_id'],
                    'exercise_id' => $data['exercise_id'],
                    'unit_id' => $data['unit_id'],
                    'level_id' => $data['level_id'],
                    'user_unit_activity_id' => $data['user_unit_activity_id']
                ]])
            ->group(['UserActivities.card_id'])->toArray();

        $cardsUserHasntAnsweredCorrectly = array();
        foreach ($Activities as $activity) {
            $correctActivitiesForCardId = $this->getUserActivitiesTable()
                ->find('all', [
                    'conditions' => [
                        'user_id' => $data['user_id'],
                        'exercise_id' => $data['exercise_id'],
                        'unit_id' => $data['unit_id'],
                        'level_id' => $data['level_id'],
                        'card_id' => $activity['card_id'],
                        'type' => 'right',
                        'user_unit_activity_id' => $data['user_unit_activity_id']
                    ]]);
            $numCorrectActivitiesForCard = $correctActivitiesForCardId->count();
            if ($numCorrectActivitiesForCard == 0) {
                // User has not answered this card correctly
                // Add it to the array of incorrect cards
                $cardsUserHasntAnsweredCorrectly[] = $activity['card_id'];
            }
        }
        return $cardsUserHasntAnsweredCorrectly;
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
            if (!empty($data['prompt_audio_id'])) {
                $element['audio'] = $this->getFileDetails(array($data['prompt_audio_id']));
            } else {
                $element['audio'] = null;
            }
            if (!empty($data['prompt_image_id'])) {
                $element['image'] = $this->getFileDetails(array($data['prompt_image_id']));
            } else {
                $element['image'] = null;
            }
        } else {
            if (!empty($data['response_audio_id'])) {
                $element['audio'] = $this->getFileDetails(array($data['response_audio_id']));
            } else {
                $element['audio'] = null;
            }
            if (!empty($data['response_image_id'])) {
                $element['image'] = $this->getFileDetails(array($data['response_image_id']));
            } else {
                $element['image'] = null;
            }
        }

        return $element;
    }

    public function shareRecordingAudioFileByEmail()
    {
        $requestData = $this->request->getData();
        $email_ids = $requestData['email_ids'];
        $audio_id = $requestData['audio_id'];
        $Audio = $this->getRecordingAudiosTable()->get($audio_id);
        $param = array('email' => '', 'link' => $Audio['link']);
        $getMailData = $this->Mail->createMailTemplate('share_record_audio', $param);
        $email_idsarra = explode(",", $email_ids);
        $validate = true;
        foreach ($email_idsarra as $email) {
            if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                $validate = false;
            }
        }
        if ($validate == false) {
            $this->sendApiData(false, 'please enter all valid email.', array());
        } else {
            foreach ($email_idsarra as $email) {
                $getMailData['param']['email'] = trim($email);
                @$this->sendMail($getMailData, $getMailData['template'], $getMailData['layout']);
            }
            $this->sendApiData(true, 'Audio shared Successfully.', array());
        }
    }

    public function saveRecordingAudioFile()
    {
        $requestData = $this->request->getData();
        $userid = $requestData['user_id'];

        if (isset($requestData["is_app"]) && $requestData["is_app"] == 1) {
            $temp = base64_decode($requestData['audio']);
            $tempfile = "temp.mp3";
            $setNewFileName = $this->randomString();
            $ext = 'mp3';
            file_put_contents(WWW_ROOT . 'img/RecordingAudio/' . $setNewFileName . '.' . $ext, $temp);
            $filename = $setNewFileName . '.' . $ext;

            $var = shell_exec('which ffmpeg');
            if ($var !== null) {
                $ffmpeg = FFMpeg\FFMpeg::create();
                $video = $ffmpeg->open(WWW_ROOT . 'img/RecordingAudio/' . $setNewFileName . '.' . $ext);
                $audio_format = new FFMpeg\Format\Audio\Mp3();
                $video->save($audio_format, WWW_ROOT . 'img/RecordingAudio/' . $setNewFileName . '.mp3');
            }

            $data = array('user_id' => $userid, 'file_name' => $setNewFileName . '.' . $ext);
            $data['aws_link'] = null;
            if (Configure::read('AWSUPLOAD')) {
                $aws = $this->FilesCommon->uploadFileToAws(
                    WWW_ROOT . 'img/RecordingAudio/' . $setNewFileName . '.mp3',
                    $setNewFileName . '.mp3',
                    'RECORD'
                );
                $data['aws_link'] = $aws['result']['ObjectURL'];
            }
        } else {
            $validData = true;
            $typeFormat = explode("/", $requestData['audio']['type']);
            $type = $typeFormat[0];
            $format = $typeFormat[1];
            if ($type != 'audio') {
                $validData = false;
            }
            if ($validData) {
                //upload audio
                $param = array();
                $uploadResult = $this->FilesCommon->uploadFile($requestData['audio'], $param, 'RECORD');
                $data = array(
                    'user_id' => $userid,
                    'file_name' => $uploadResult['filename'],
                    'aws_link' => $uploadResult['awsupload']['result']['ObjectURL']);
            } else {
                $this->sendApiData(false, 'please upload valid Audio.', array());
            }
        }
        if (isset($requestData['exercise_id'])) {
            $data['exercise_id'] = $requestData['exercise_id'];
        }
        if (isset($requestData['type'])) {
            $data['type'] = $requestData['type'];
        }
        $Audio = $this->getRecordingAudiosTable()->newEmptyEntity();
        $AudioData = $this->getRecordingAudiosTable()->patchEntity($Audio, $data);
        $result = $this->getRecordingAudiosTable()->save($AudioData);
        $this->sendApiData(true, 'Audio Upload Successfully.', $result);
    }

    private function randomString()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $randstring = '';
        for ($i = 0; $i < 15; $i++) {
            $randstring .= $characters[rand(0, 35)];
        }
        return $randstring . time();
    }
}
