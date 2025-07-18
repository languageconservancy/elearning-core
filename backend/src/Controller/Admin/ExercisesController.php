<?php

namespace App\Controller\Admin;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Log\Log;
use Cake\Routing\Router;

class ExercisesController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
    }

    public function getExerciseList()
    {
        $this->request->allowMethod(['get']);

        // Fetch data for the DataTables response
        $query = $this->getExercisesTable()->find('all')
            ->select(['id', 'name']);

        // Get DataTables parameters
        $start = $this->request->getQuery('start', 0);
        $length = $this->request->getQuery('length', 10);
        $searchValue = $this->request->getQuery('search')['value'];

        // Apply search filter if present
        if (!empty($searchValue)) {
            $query->where(function ($exp) use ($searchValue) {
                if (!is_numeric($searchValue)) {
                    // Search within name field using a LIKE condition
                    return $exp->like('name', '%' . $searchValue . '%');
                } else {
                    // Search for an exact match on the 'id' field
                    return $exp->eq('id', $searchValue);
                }
            });
        }

        // Pagination
        $totalRecords = $query->count();
        $filteredQuery = $query->limit($length)->offset($start);
        $filteredRecords = $filteredQuery->count();

        // Convert data to array for JSON response
        $editUrl = Router::url(['controller' => 'Exercises', 'action' => 'manageExercises']) . '/';
        $data = [];
        foreach ($filteredQuery as $exercise) {
            $data[] = [
                'id' => $exercise->id,
                'name' => $exercise->name,
                'action' =>
                    '<a href="' . $editUrl . $exercise->id . '">
                        <i class="fa fa-pencil"></i>
                    </a> |
                    <a href="javascript:void(0)" onclick="deleteExercise(' . $exercise->id . ')">
                        <i class="fa fa-trash"></i>
                    </a>',
            ];
        }

        // Build response structure
        $response = [
            'draw' => intval($this->request->getQuery('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ];

        // Send JSON response, bypassing CakePHP's view rendering
        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($response));
        return $this->response;
    }

    protected function getOrCreateExercise($exerciseId) {
        if ($exerciseId === null) {
            return $this->getExercisesTable()->newEmptyEntity();
        }

        return $this->getExercisesTable()->get(
            $exerciseId,
            [ 'contain' => [ 'Exerciseoptions' => [ 'ExerciseCustomOptions' ] ] ]
        );
    }

    protected function renderExerciseList() {
        $exercise = $this->getExercisesTable()->newEmptyEntity();
        $this->set(compact('exercise'));
        $this->render('manage_exercises');
    }

    private function isRequestValid($data)
    {
        return !isset($data['divid']);
    }

    private function isMultipleChoiceCardGroup($exercise)
    {
        return $exercise['card_type'] == 'card_group' && $exercise['exercise_type'] == 'multiple-choice';
    }

    private function isMultipleChoiceCard($exercise)
    {
        return $exercise['card_type'] == 'card' && $exercise['exercise_type'] == 'multiple-choice';
    }

    private function isMultipleChoiceCustom($exercise)
    {
        return $exercise['card_type'] == 'custom' && $exercise['exercise_type'] == 'multiple-choice';
    }

    private function isMatchThePairCard($exercise)
    {
        return $exercise['card_type'] == 'card' && $exercise['exercise_type'] == 'match-the-pair';
    }

    private function isMatchThePairCardGroup($exercise)
    {
        return $exercise['card_type'] == 'card_group' && $exercise['exercise_type'] == 'match-the-pair';
    }

    private function isMatchThePairCustom($exercise)
    {
        return $exercise['card_type'] == 'custom' && $exercise['exercise_type'] == 'match-the-pair';
    }

    private function isTrueFalseCardGroup($exercise)
    {
        return $exercise['card_type'] == 'card_group' && $exercise['exercise_type'] == 'truefalse';
    }

    private function isTrueFalseCustom($exercise)
    {
        return $exercise['card_type'] == 'custom' && $exercise['exercise_type'] == 'truefalse';
    }

    private function isAnagramCardGroup($exercise)
    {
        return $exercise['card_type'] == 'card_group' && $exercise['exercise_type'] == 'anagram';
    }

    private function isFillInTheBlanksCard($exercise)
    {
        return $exercise['card_type'] == 'card' && $exercise['exercise_type'] == 'fill_in_the_blanks';
    }

    private function isFillInTheBlanksCustom($exercise)
    {
        return $exercise['card_type'] == 'custom' && $exercise['exercise_type'] == 'fill_in_the_blanks';
    }

    private function isRecordingCardGroup($exercise)
    {
        return $exercise['card_type'] == 'card_group' && $exercise['exercise_type'] == 'recording';
    }

    // manage exercise
    public function manageExercises($exerciseId = null)
    {
        $languageName = Configure::read('LANGUAGE');
        $data = $this->request->getData();
        $validRequest = $this->isRequestValid($data);

        $promoteCardIds = array();
        $promoteGroupIds = array();
        $promoteGroupCardIds = array();
        $optionCardIds = array();
        $optionGroupids = array();
        $optionGroupCardIds = array();

        $promoteSingleCardId = -1;
        $responseSingleCardId = -1;
        $selectedOptioncardIds = array();
        $OptionGroupId = -1;

        $matchThePairvalue = array();
        $matchThePairGroupvalue = array();
        $matchThePairGroupCardId = array();
        $matchThePairGroupId = array();
        $matchThePairGroupIdCardId = array();

        $TrueFalseValue = array();
        $TrueFalseGroupPromptCardId = array();
        $TrueFalseGroupPromptGroupId = array();
        $TrueFalseGroupPromptGroupCardId = array();


        $AnagramGroupValue = array();
        $AnagramGroupPromptCardId = array();
        $AnagramGroupPromptGroupId = array();
        $AnagramGroupPromptGroupCardId = array();


        $FillInTheBlankValue = array();
        $FillInTheBlankOptions = array();

        $RecordingGroupValue = array();
        $RecordingGroupPromptCardId = array();
        $RecordingGroupPromptGroupId = array();
        $RecordingGroupPromptGroupCardId = array();

        $truefalseCustomAsset = array();

        $mcqCustomOptionAsset = array();
        $exFormType = '';

        $exercise = $this->getOrCreateExercise($exerciseId);
        if ($exerciseId == null) {
            $exFormType = 'new-exercise';
        } else {
            $exFormType = 'existing-exercise';

            $matchcount = 1;
            foreach ($exercise['exerciseoptions'] as $flagCard) {
                if ($this->isMultipleChoiceCardGroup($exercise)) {
                    if ($flagCard['card_type'] == 'P' && $flagCard['type'] == 'card') {
                        $promoteCardIds[] = $flagCard['card_id'];
                    }
                    if ($flagCard['card_type'] == 'P' && $flagCard['type'] == 'group') {
                        if ($flagCard['group_id'] != null) {
                            $promoteGroupIds[] = $flagCard['group_id'];
                        }
                    }
                    if ($flagCard['card_type'] == 'P' && $flagCard['type'] == 'group') {
                        if ($flagCard['card_id'] != null) {
                            $promoteGroupCardIds[] = $flagCard['card_id'];
                        }
                    }
                    if ($flagCard['card_type'] == 'O' && $flagCard['type'] == 'card') {
                        $optionCardIds[] = $flagCard['card_id'];
                    }
                    if ($flagCard['card_type'] == 'O' && $flagCard['type'] == 'group') {
                        if ($flagCard['group_id'] != null) {
                            array_push($optionGroupids, $flagCard['group_id']);
                        }
                    }
                    if ($flagCard['card_type'] == 'O' && $flagCard['type'] == 'group') {
                        if ($flagCard['card_id'] != null) {
                            array_push($optionGroupCardIds, $flagCard['card_id']);
                        }
                    }
                }

                if ($this->isMultipleChoiceCard($exercise)) {
                    if ($flagCard['card_type'] == 'P' && $flagCard['type'] == 'card') {
                        $promoteSingleCardId = $flagCard['card_id'];
                    }
                    if ($flagCard['card_type'] == 'R' && $flagCard['type'] == 'card') {
                        $responseSingleCardId = $flagCard['card_id'];
                    }
                    if ($flagCard['card_type'] == 'O' && $flagCard['type'] == 'card') {
                        if ($flagCard['card_id'] != null) {
                            array_push($selectedOptioncardIds, $flagCard['card_id']);
                        }
                    }
                    if ($flagCard['card_type'] == 'O' && $flagCard['type'] == 'group') {
                        if ($flagCard['group_id'] != null) {
                            $OptionGroupId = $flagCard['group_id'];
                        }
                    }
                }

                if ($this->isMultipleChoiceCustom($exercise)) {
                    if ($flagCard['card_type'] == 'O' && $flagCard['type'] == 'card') {
                        array_push($mcqCustomOptionAsset, $flagCard);
                    }
                }

                if ($this->isMatchThePairCard($exercise)) {
                    $matchThePairvalue['promptcard' . $matchcount] = $flagCard['card_id'];
                    $matchThePairvalue['responcecard' . $matchcount] = $flagCard['responce_card_id'];
                    $matchThePairvalue['promptpreview' . $matchcount] = $flagCard['prompt_preview_option'];
                    $matchThePairvalue['responcepreview' . $matchcount] = $flagCard['responce_preview_option'];
                    $matchcount++;
                }
                if ($this->isMatchThePairCardGroup($exercise)) {
                    if ($flagCard['type'] == 'card') {
                        array_push($matchThePairGroupCardId, $flagCard['card_id']);
                    }
                    if ($flagCard['type'] == 'group' && $flagCard['group_id'] != null) {
                        array_push($matchThePairGroupId, $flagCard['group_id']);
                    }
                    if ($flagCard['type'] == 'group' && $flagCard['group_id'] == null && $flagCard['card_id'] != null) {
                        array_push($matchThePairGroupIdCardId, $flagCard['card_id']);
                    }
                }
                if ($this->isMatchThePairCustom($exercise)) {
//                    if ($flagCard['type'] == 'card') {
//                        array_push($matchThePairGroupCardId, $flagCard['card_id']);
//                    }
//                    if ($flagCard['type'] == 'group' && $flagCard['group_id'] != null) {
//                        array_push($matchThePairGroupId, $flagCard['group_id']);
//                    }
//                    if (
                        // $flagCard['type'] == 'group'
                        // && $flagCard['group_id'] == null
                        // && $flagCard['card_id'] != null
                    // ) {
//                        array_push($matchThePairGroupIdCardId, $flagCard['card_id']);
//                    }
                }
                if ($this->isTrueFalseCardGroup($exercise)) {
                    if ($flagCard['type'] == 'card' && !empty($flagCard['card_id'])) {
                        array_push($TrueFalseGroupPromptCardId, $flagCard['card_id']);
                    }

                    if ($flagCard['type'] == 'group' && !empty($flagCard['card_id'])) {
                        array_push($TrueFalseGroupPromptGroupCardId, $flagCard['card_id']);
                    }

                    if ($flagCard['type'] == 'group' && !empty($flagCard['group_id'])) {
                        array_push($TrueFalseGroupPromptGroupId, $flagCard['group_id']);
                    }
                }
                if ($this->isTrueFalseCustom($exercise)) {
                    $truefalseCustomAsset['prompt_audio'] =
                        (isset($flagCard['exercise_custom_options'][0])
                            && $flagCard['exercise_custom_options'][0]['prompt_audio_id'] != null)
                        ? $this->getFile($flagCard['exercise_custom_options'][0]['prompt_audio_id']) : null;
                    $truefalseCustomAsset['prompt_image'] =
                        (isset($flagCard['exercise_custom_options'][0])
                            && $flagCard['exercise_custom_options'][0]['prompt_image_id'] != null)
                        ? $this->getFile($flagCard['exercise_custom_options'][0]['prompt_image_id']) : null;
                    $truefalseCustomAsset['responce_audio'] =
                        (isset($flagCard['exercise_custom_options'][0])
                            && $flagCard['exercise_custom_options'][0]['response_audio_id'] != null)
                        ? $this->getFile($flagCard['exercise_custom_options'][0]['response_audio_id']) : null;
                    $truefalseCustomAsset['responce_image'] =
                        (isset($flagCard['exercise_custom_options'][0])
                            && !empty($flagCard['exercise_custom_options'][0]['response_image_id']))
                        ? $this->getFile($flagCard['exercise_custom_options'][0]['response_image_id']) : null;
                }

                if ($this->isAnagramCardGroup($exercise)) {
                    if ($flagCard['type'] == 'card' && !empty($flagCard['card_id'])) {
                        array_push($AnagramGroupPromptCardId, $flagCard['card_id']);
                    }

                    if ($flagCard['type'] == 'group' && !empty($flagCard['card_id'])) {
                        array_push($AnagramGroupPromptGroupCardId, $flagCard['card_id']);
                    }

                    if ($flagCard['type'] == 'group' && !empty($flagCard['group_id'])) {
                        array_push($AnagramGroupPromptGroupId, $flagCard['group_id']);
                    }
                }

                if ($this->isFillInTheBlanksCard($exercise)) {
                    if ($flagCard['card_type'] == 'P' && !empty($flagCard['card_id'])) {
                        $FillInTheBlankValue = $flagCard;
                    }
                    if ($flagCard['card_type'] == 'O' && !empty($flagCard['card_id'])) {
                        array_push($FillInTheBlankOptions, $flagCard);
                    }
                }
                if ($this->isFillInTheBlanksCustom($exercise)) {
                    if ($flagCard['card_type'] == 'P' && !empty($flagCard['card_id'])) {
                        $FillInTheBlankValue = $flagCard;
                    }
                }
                if ($this->isRecordingCardGroup($exercise)) {
                    if ($flagCard['card_type'] == 'P' && $flagCard['type'] == 'card') {
                        array_push($RecordingGroupPromptCardId, $flagCard['card_id']);
                    }
                    if ($flagCard['card_type'] == 'P' && $flagCard['type'] == 'group') {
                        if (!empty($flagCard['card_id'])) {
                            array_push($RecordingGroupPromptGroupCardId, $flagCard['card_id']);
                        }
                        if (!empty($flagCard['group_id'])) {
                            array_push($RecordingGroupPromptGroupId, $flagCard['group_id']);
                        }
                    }
                }
            }
            if ($this->isMatchThePairCardGroup($exercise)) {
                $matchThePairGroupvalue['cardId'] = $matchThePairGroupCardId;
                $matchThePairGroupvalue['groupId'] = $matchThePairGroupId;
                $optionGroupCardIds = $matchThePairGroupIdCardId;
            }
            if ($this->isTrueFalseCardGroup($exercise)) {
                $TrueFalseValue['PromptGroupId'] = $TrueFalseGroupPromptGroupId;
                $TrueFalseValue['PromptCardId'] = $TrueFalseGroupPromptCardId;
                $TrueFalseValue['PromptGroupCardId'] = $TrueFalseGroupPromptGroupCardId;
            }
            if ($this->isAnagramCardGroup($exercise)) {
                $AnagramGroupValue['PromptGroupId'] = $AnagramGroupPromptGroupId;
                $AnagramGroupValue['PromptCardId'] = $AnagramGroupPromptCardId;
                $AnagramGroupValue['PromptGroupCardId'] = $AnagramGroupPromptGroupCardId;
            }
            if ($this->isRecordingCardGroup($exercise)) {
                $RecordingGroupValue['PromptGroupId'] = $RecordingGroupPromptGroupId;
                $RecordingGroupValue['PromptCardId'] = $RecordingGroupPromptCardId;
                $RecordingGroupValue['PromptGroupCardId'] = $RecordingGroupPromptGroupCardId;
            }
        }
        if ($validRequest && $this->request->is(['PATCH', 'POST', 'PUT'])) {
            $validedResponse = $this->validateExercise($data);
            if ($validedResponse['validate']) {
                if ($data['formtype'] != 'namevalue') {
                    $prtype = explode("-", $data['promteresponsetype']);
                    $mainPromptType = $prtype[0];
                    $mainResponseType = $prtype[1];
                    if ($data['exercise_type'] == 'multiple-choice' && $data['card_type'] == 'card_group') {
                        if (isset($data['promptgrouptype'])) {
                            $data['promotetype'] = implode(", ", $data['promptgrouptype']) . ', ' . $prtype[0];
                        } else {
                            $data['promotetype'] = $prtype[0];
                        }
                        if (isset($data['responsegrouptype'])) {
                            $data['responsetype'] = implode(", ", $data['responsegrouptype']) . ', ' . $prtype[1];
                        } else {
                            $data['responsetype'] = $prtype[1];
                        }
                    } elseif ($data['exercise_type'] == 'multiple-choice' && $data['card_type'] == 'card') {
                        if (isset($data['prompttype'])) {
                            $data['promotetype'] = implode(", ", $data['prompttype']) . ', ' . $prtype[0];
                        } else {
                            $data['promotetype'] = $prtype[0];
                        }

                        if (isset($data['responsetype'])) {
                            $data['responsetype'] = implode(", ", $data['responsetype']) . ', ' . $prtype[1];
                        } else {
                            $data['responsetype'] = $prtype[1];
                        }
                    } elseif ($data['exercise_type'] == 'multiple-choice' && $data['card_type'] == 'custom') {
                        $data['promotetype'] = $prtype[0];
                        $data['responsetype'] = $prtype[1];
                    } elseif ($data['exercise_type'] == 'match-the-pair' && $data['card_type'] == 'card') {
                        $data['noofcard'] = isset($data['MatchPairCardNoOfCard'])
                            ? $data['MatchPairCardNoOfCard'] : null;
                    } elseif (
                        $data['exercise_type'] == 'match-the-pair'
                        && $data['card_type'] == 'card_group'
                    ) {
                        $data['noofcard'] = isset($data['MatchPairGroupNoOfCard'])
                            ? $data['MatchPairGroupNoOfCard'] : null;
                        if (isset($data['matchthepairpromptgrouptype'])) {
                            $data['promotetype'] = implode(
                                ", ",
                                $data['matchthepairpromptgrouptype']
                            ) . ', ' . $prtype[0];
                        } else {
                            $data['promotetype'] = $prtype[0];
                        }
                        if (isset($data['matchthepairresponsegrouptype'])) {
                            $data['responsetype'] = implode(
                                ", ",
                                $data['matchthepairresponsegrouptype']
                            ) . ', ' . $prtype[1];
                        } else {
                            $data['responsetype'] = $prtype[1];
                        }
                    } elseif ($data['exercise_type'] == 'match-the-pair' && $data['card_type'] == 'custom') {
                        $data['noofcard'] = isset($data['MatchPairCustomNoOfCard'])
                            ? $data['MatchPairCustomNoOfCard'] : null;
                    } elseif ($data['exercise_type'] == 'truefalse' && $data['card_type'] == 'card') {
                        if (isset($data['TrueFalseCardPromptType'])) {
                            $data['promotetype'] = implode(", ", $data['TrueFalseCardPromptType'])
                                . ', ' . $mainPromptType;
                        } else {
                            $data['promotetype'] = $mainPromptType;
                        }

                        if (isset($data['TrueFalseCardResponseType'])) {
                            $data['responsetype'] = implode(", ", $data['TrueFalseCardResponseType'])
                                . ', ' . $mainResponseType;
                        } else {
                            $data['responsetype'] = $mainResponseType;
                        }
                    } elseif ($data['exercise_type'] == 'truefalse' && $data['card_type'] == 'card_group') {
                        $data['noofcard'] = isset($data['TrueFalseNoOfCard'])
                            ? $data['TrueFalseNoOfCard'] : null;
                        if (isset($data['TrueFalseGruopPromptType'])) {
                            $data['promotetype'] = implode(", ", $data['TrueFalseGruopPromptType'])
                                . ', ' . $mainPromptType;
                        } else {
                            $data['promotetype'] = $mainPromptType;
                        }

                        if (isset($data['TrueFalseGruopResponseType'])) {
                            $data['responsetype'] = implode(", ", $data['TrueFalseGruopResponseType'])
                                . ', ' . $mainResponseType;
                        } else {
                            $data['responsetype'] = $mainResponseType;
                        }
                    } elseif ($data['exercise_type'] == 'truefalse' && $data['card_type'] == 'custom') {
                        $data['promotetype'] = $mainPromptType;
                        if (!empty($data['TrueFalseCustomPromptInput_image_id'])) {
                            $data['promotetype'] .= ', i';
                        }
                        if (!empty($data['TrueFalseCustomPromptInput_audio_id'])) {
                            $data['promotetype'] .= ', a';
                        }
                        $data['responsetype'] = $mainResponseType;
                        if (!empty($data['TrueFalseCustomResponseInput_image_id'])) {
                            $data['responsetype'] .= ', i';
                        }
                        if (!empty($data['TrueFalseCustomResponseInput_audio_id'])) {
                            $data['responsetype'] .= ', a';
                        }
                    } elseif ($data['exercise_type'] == 'anagram' && $data['card_type'] == 'card') {
                        if (isset($data['anagramCardPromptType'])) {
                            $data['promotetype'] = implode(", ", $data['anagramCardPromptType'])
                                . ', ' . $prtype[0];
                        } else {
                            $data['promotetype'] = $prtype[0];
                        }

                        if (isset($data['anagramCardResponseType'])) {
                            $data['responsetype'] = implode(", ", $data['anagramCardResponseType'])
                                . ', ' . $prtype[1];
                        } else {
                            $data['responsetype'] = $prtype[1];
                        }
                    } elseif ($data['exercise_type'] == 'anagram' && $data['card_type'] == 'card_group') {
                        $data['noofcard'] = isset($data['AnagramGroupCards']) ? $data['AnagramGroupCards'] : 0;
                        if (isset($data['anagramGroupPromptType'])) {
                            $data['promotetype'] = implode(", ", $data['anagramGroupPromptType'])
                                . ', ' . $prtype[0];
                        } else {
                            $data['promotetype'] = $prtype[0];
                        }
                        if (isset($data['anagramGroupResponseType'])) {
                            $data['responsetype'] = implode(", ", $data['anagramGroupResponseType'])
                                . ', ' . $prtype[1];
                        } else {
                            $data['responsetype'] = $prtype[1];
                        }
                    } elseif ($data['exercise_type'] == 'fill_in_the_blanks' && $data['card_type'] == 'card') {
                        //$data['noofcard'] = $data['AnagramGroupCards'];
                        if (isset($data['FillInTheBlankCardPromptType'])) {
                            $data['promotetype'] = implode(", ", $data['FillInTheBlankCardPromptType'])
                                . ', ' . $prtype[0];
                        } else {
                            $data['promotetype'] = $prtype[0];
                        }
                        if (isset($data['FillInTheBlankCardResponseType'])) {
                            $data['responsetype'] = implode(", ", $data['FillInTheBlankCardResponseType'])
                                . ', ' . $prtype[1];
                        } else {
                            $data['responsetype'] = $prtype[1];
                        }
                    } elseif ($data['exercise_type'] == 'fill_in_the_blanks' && $data['card_type'] == 'custom') {
                        if (isset($data['Fill_In_The_Balnks_CustomRadio'])) {
                            $data['promotetype'] = implode(", ", $data['Fill_In_The_Balnks_CustomRadio'])
                                . ', ' . $prtype[0];
                        } else {
                            $data['promotetype'] = $prtype[0];
                        }
                        $data['responsetype'] = $prtype[1];
                    } elseif ($data['exercise_type'] == 'recording' && $data['card_type'] == 'card') {
                        if (isset($data['recording_prompt'])) {
                            $data['promotetype'] = implode(", ", $data['recording_prompt'])
                                . ', ' . $prtype[0];
                        } else {
                            $data['promotetype'] = $prtype[0];
                        }
                        $data['responsetype'] = $prtype[1];
                    } elseif ($data['exercise_type'] == 'recording' && $data['card_type'] == 'card_group') {
                        $data['noofcard'] = isset($data['RecordinNoOfCard']) ? $data['RecordinNoOfCard'] : null;
                        if (isset($data['recordingpromptgrouptype'])) {
                            $data['promotetype'] = implode(", ", $data['recordingpromptgrouptype'])
                                . ', ' . $prtype[0];
                        } else {
                            $data['promotetype'] = $prtype[0];
                        }
                        $data['responsetype'] = $prtype[1];
                    } elseif ($data['exercise_type'] == 'recording' && $data['card_type'] == 'custom') {
                        if (isset($data['Recording_CustomRadio'])) {
                            $data['promotetype'] = implode(",", $data['Recording_CustomRadio'])
                                . ',' . $prtype[0];
                        } else {
                            $data['promotetype'] = $prtype[0];
                        }
                        $data['responsetype'] = $prtype[1];
                    }
                    $data['bonus'] = ceil($data['bonus']);
                }

                // Store data in exercise object and save it to Exercises table
                $exerciseData = $this->getExercisesTable()->patchEntity($exercise, $data);
                $exerciseData = $this->getExercisesTable()->save($exerciseData);
                if ($exerciseData == false) {
                    $this->Flash->error(__('The Exercise could not be saved. Please, try again.'));
                } else {
                    $errors = $exerciseData->getErrors();
                    if (!empty($errors)) {
                        foreach ($errors as $key => $err) {
                            foreach ($err as $key1 => $err1) {
                                $this->Flash->error($err1);
                            }
                        }
                    } else {
                        if ($data['formtype'] != 'namevalue') {
                            $this->saveOptionData($data, $exerciseData->id);
                        }
                        if ($this->updateCardUnitsAccordingToExercise($exerciseData) == false) {
                            $this->Flash->error(__(
                                'CardUnits table failed to update. Invalid Exercise '
                                . 'or ExerciseId. Please check error log for more details.'
                            ));
                        }
                        $this->Flash->success(__('The Exercise saved successfully.'));
                        return $this->redirect(['action' => 'manageExercises', $exerciseData->id]);
                    }
                }
            } else {
                $this->Flash->error($validedResponse['message']);
                return $this->redirect($this->referer());
            }
        }

        $promptResponse = array(
            ['val' => 'a', 'label' => 'Audio'],
            ['val' => 'e', 'label' => 'English'],
            ['val' => 'i', 'label' => 'Image'],
            ['val' => 'l', 'label' => $languageName],
            ['val' => 'r', 'label' => 'Recording'],
            ['val' => 'v', 'label' => 'Video']
        );

        // Prompt types allowed to be both prompt and response
        $promptResponseAllowedSelfPairs = array('e', 'l', 'a');

        if ($exerciseId == null) {
            $this->set(compact('exercise'));
        } else {
            if ($exFormType == 'new-exercise') {
                $this->set(compact('exercise', 'promptResponse'));
            } else {
                $cardsgroup = $this->getCardgroupTable()
                    ->find('list', array('keyField' => 'id', 'valueField' => 'name'))
                    ->toArray();

                $this->set(compact(
                    'cardsgroup',
                    'promptResponse',
                    'exercise',
                    'promoteCardIds',
                    'promoteGroupIds',
                    'promoteGroupCardIds',
                    'optionCardIds',
                    'optionGroupids',
                    'optionGroupCardIds',
                    'OptionGroupId',
                    'selectedOptioncardIds',
                    'responseSingleCardId',
                    'promoteSingleCardId',
                    'matchThePairvalue',
                    'matchThePairGroupvalue',
                    'TrueFalseValue',
                    'truefalseCustomAsset',
                    'AnagramGroupValue',
                    'FillInTheBlankValue',
                    'FillInTheBlankOptions',
                    'RecordingGroupValue',
                    'mcqCustomOptionAsset',
                    'promptResponseAllowedSelfPairs',
                    'languageName'
                ));
            }
        }

        $this->render('manage_exercises');
    }

    //delete Exercise data

    private function getFile($id)
    {
        return $this->getFilesTable()->get($id);
    }

    private function validateExercise($data)
    {
        $validate = true;
        $message = '';
        if ($data['formtype'] != 'namevalue') {
            if (!isset($data['name']) || trim($data['name']) == '') {
                $validate = false;
                $message = 'Please enter valid name.';
                return array('message' => $message, 'validate' => $validate);
            }
            if (!isset($data['card_type']) || $data['card_type'] == '') {
                $validate = false;
                $message = 'Please select card type';
                return array('message' => $message, 'validate' => $validate);
            }
            if (!isset($data['promteresponsetype']) || $data['promteresponsetype'] == '') {
                $validate = false;
                $message = 'Please select prompt->response type';
                return array('message' => $message, 'validate' => $validate);
            }
            if (!isset($data['exercise_type']) || $data['exercise_type'] == '') {
                $validate = false;
                $message = 'Please select exercise type';
                return array('message' => $message, 'validate' => $validate);
            }
        } else {
            if (!isset($data['name']) || trim($data['name']) == '') {
                $validate = false;
                $message = 'Please enter valid name.';
                return array('message' => $message, 'validate' => $validate);
            }
        }
        return array('message' => $message, 'validate' => $validate);
    }

    //save Exercise data

    private function saveOptionData($data, $excriseId = null)
    {
        $this->getExerciseoptionsTable()->deleteAll(['exercise_id' => $excriseId]);

        if ($data['exercise_type'] == 'multiple-choice' && $data['card_type'] == 'card_group') {
            $promotecardIdFlag = array();
            /* for promote input */
            if (isset($data['prompt_group_card_id'])) {
                foreach ($data['prompt_group_card_id'] as $card) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['card_id'] = $card;
                    $element['type'] = 'card';
                    $element['card_type'] = 'P';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                    array_push($promotecardIdFlag, $card);
                }
            }
            if (isset($data['promotegroupcardid'])) {
                foreach ($data['promotegroupcardid'] as $card) {
                    if (!in_array($card, $promotecardIdFlag)) {
                        $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                        $element = array();
                        $element['exercise_id'] = $excriseId;
                        $element['card_id'] = $card;
                        $element['type'] = 'group';
                        $element['card_type'] = 'P';
                        $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                        $this->getExerciseoptionsTable()->save($Data);
                        array_push($promotecardIdFlag, $card);
                    }
                }
            }
            if (isset($data['prompt_group_group_id'])) {
                foreach ($data['prompt_group_group_id'] as $group) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['group_id'] = $group;
                    $element['type'] = 'group';
                    $element['card_type'] = 'P';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                }
            }
            /* for Option input */
            $optioncardIdFlag = array();
            if (isset($data['group_card_option_id'])) {
                foreach ($data['group_card_option_id'] as $card) {
                    //if (!in_array($card, $promotecardIdFlag)) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['card_id'] = $card;
                    $element['type'] = 'card';
                    $element['card_type'] = 'O';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                    array_push($optioncardIdFlag, $card);
                    // }
                }
            }
            if (isset($data['group_option_id'])) {
                foreach ($data['group_option_id'] as $group) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['group_id'] = $group;
                    $element['type'] = 'group';
                    $element['card_type'] = 'O';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                }
            }
            if (isset($data['optiongroupcardid'])) {
                foreach ($data['optiongroupcardid'] as $card) {
                    if (!in_array($card, $optioncardIdFlag)) {
                        $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                        $element = array();
                        $element['exercise_id'] = $excriseId;
                        $element['card_id'] = $card;
                        $element['type'] = 'group';
                        $element['card_type'] = 'O';
                        $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                        $this->getExerciseoptionsTable()->save($Data);
                    }
                }
            }
        } elseif ($data['exercise_type'] == 'multiple-choice' && $data['card_type'] == 'card') {
            /* for promote input */
            if (isset($data['prompt_card_id'])) {
                $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                $element = array();
                $element['exercise_id'] = $excriseId;
                $element['card_id'] = $data['prompt_card_id'];
                $element['type'] = 'card';
                $element['card_type'] = 'P';
                $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                $this->getExerciseoptionsTable()->save($Data);
            }

            /* for response input */
            if (isset($data['response_card_id'])) {
                $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                $element = array();
                $element['exercise_id'] = $excriseId;
                $element['card_id'] = $data['response_card_id'];
                $element['type'] = 'card';
                $element['card_type'] = 'R';
                $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                $this->getExerciseoptionsTable()->save($Data);
            }
            /* for option input */

            if (isset($data['card_id'])) {
                foreach ($data['card_id'] as $card) {
                    if ($data['response_card_id'] != $card && $data['prompt_card_id'] != $card) {
                        $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                        $element = array();
                        $element['exercise_id'] = $excriseId;
                        $element['card_id'] = $card;
                        $element['type'] = 'card';
                        $element['card_type'] = 'O';
                        $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                        $this->getExerciseoptionsTable()->save($Data);
                    }
                }
            }
            if (isset($data['card_group_id'])) {
                $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                $element = array();
                $element['exercise_id'] = $excriseId;
                $element['group_id'] = $data['card_group_id'];
                $element['type'] = 'group';
                $element['card_type'] = 'O';
                $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                $this->getExerciseoptionsTable()->save($Data);
            }
        } elseif ($data['exercise_type'] == 'multiple-choice' && $data['card_type'] == 'custom') {
            $this->getExerciseCustomOptionsTable()->deleteAll(['exercise_id' => $excriseId]);
            $prtype = explode("-", $data['promteresponsetype']);
            $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
            $element = array();
            $option = array();
            $element['exercise_id'] = $excriseId;
            $option['exercise_id'] = $excriseId;
            $element['type'] = 'card';
            $element['card_type'] = 'P';

            if (isset($data['mcq_custom_prompt_type'])) {
                if ($data['mcq_custom_prompt_type'] == 'card') {
                    $element['card_id'] = $data['MCQCustomPrompt'];
                    if (isset($data['MCQCustomPromptRadio'])) {
                        $element['prompt_preview_option'] =
                            implode(",", $data['MCQCustomPromptRadio']) . ',' . $prtype[0];
                    } else {
                        $element['prompt_preview_option'] = $prtype[0];
                    }
                } elseif ($data['mcq_custom_prompt_type'] == 'html') {
                    $element['card_id'] = null;
                    $option['prompt_html'] = isset($data['MCQCustomPrompt'])
                        ? $data['MCQCustomPrompt'] : null;
                    $option['prompt_audio_id'] = isset($data['MCQCustomPrompt_audio_id'])
                        ? $data['MCQCustomPrompt_audio_id'] : null;
                    $option['prompt_image_id'] = isset($data['MCQCustomPrompt_image_id'])
                        ? $data['MCQCustomPrompt_image_id'] : null;
                }
                if ($data['mcq_custom_response_type'] == 'card') {
                    $element['responce_card_id'] = isset($data['MCQCustomResponse'])
                        ? $data['MCQCustomResponse'] : null;
                    if (isset($data['MCQCustomResponseRadio'])) {
                        $element['responce_preview_option'] =
                            implode(",", $data['MCQCustomResponseRadio']) . ',' . $prtype[1];
                    } else {
                        $element['responce_preview_option'] = $prtype[1];
                    }
                } elseif ($data['mcq_custom_response_type'] == 'html') {
                    $element['responce_card_id'] = null;
                    $option['response_html'] = isset($data['MCQCustomResponse'])
                        ? $data['MCQCustomResponse'] : null;
                    $option['response_audio_id'] = isset($data['MCQCustomResponse_audio_id'])
                        ? $data['MCQCustomResponse_audio_id'] : null;
                    $option['response_image_id'] = isset($data['MCQCustomResponse_image_id'])
                        ? $data['MCQCustomResponse_image_id'] : null;
                }
            }

            $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
            $OptionsData = $this->getExerciseoptionsTable()->save($Data);
            if (
                (isset($data['mcq_custom_response_type']) && $data['mcq_custom_response_type'] == 'html') ||
                (isset($data['mcq_custom_prompt_type']) && $data['mcq_custom_prompt_type'] == 'html')
            ) {
                $CustomOptions = $this->getExerciseCustomOptionsTable()->newEmptyEntity();
                $option['exercise_option_id'] = $OptionsData['id'];
                $customOptionData = $this->getExerciseCustomOptionsTable()->patchEntity($CustomOptions, $option);
                $this->getExerciseCustomOptionsTable()->save($customOptionData);
            }


            $count = isset($data['mcq_custom_option_no']) ? $data['mcq_custom_option_no'] : 0;
            for ($i = 1; $i <= $count; $i++) {
                $dataelement = array();
                $dataelement['type'] = 'card';
                $dataelement['exercise_id'] = $excriseId;
                $dataelement['card_type'] = 'O';
                if ($data['Mcq_custom_Option_type' . $i] == 'card') {
                    $dataelement['responce_card_id'] = $data['MCQOptionCustom' . $i];
                    if (isset($data['MCQOptionCustom' . $i . 'Radio'])) {
                        $dataelement['responce_preview_option'] =
                            implode(",", $data['MCQOptionCustom' . $i . 'Radio']) . ',' . $prtype[1];
                    } else {
                        $dataelement['responce_preview_option'] = $prtype[1];
                    }
                } elseif ($data['Mcq_custom_Option_type' . $i] == 'html') {
                    $dataelement['response_html'] = $data['MCQOptionCustom' . $i];
                    $dataelement['response_audio_id'] = $data['MCQOptionCustom' . $i . '_audio_id'];
                    $dataelement['response_image_id'] = $data['MCQOptionCustom' . $i . '_image_id'];
                }
                $CustomMCQOptions = $this->getExerciseCustomOptionsTable()->newEmptyEntity();
                $Data = $this->getExerciseoptionsTable()->patchEntity($CustomMCQOptions, $dataelement);
                $OptionsMCQData = $this->getExerciseoptionsTable()->save($Data);
                if ($data['Mcq_custom_Option_type' . $i] == 'html') {
                    $CustomMcqOptions = $this->getExerciseCustomOptionsTable()->newEmptyEntity();
                    $dataelement['exercise_option_id'] = $OptionsMCQData['id'];
                    $customMCQOptionData = $this->getExerciseCustomOptionsTable()->patchEntity($CustomMcqOptions, $dataelement);
                    $this->getExerciseCustomOptionsTable()->save($customMCQOptionData);
                }
            }
        } elseif ($data['exercise_type'] == 'match-the-pair' && $data['card_type'] == 'card') {
            $prtype = explode("-", $data['promteresponsetype']);
            $noofcard = $data['noofcard'];
            for ($i = 1; $i <= $noofcard; $i++) {
                if (isset($data['MatchThePairCardPromptType' . $i . ''])) {
                    array_push($data['MatchThePairCardPromptType' . $i . ''], $prtype[0]);
                    $matchpreviewPro = implode(", ", $data['MatchThePairCardPromptType' . $i . '']);
                } else {
                    $matchpreviewPro = $prtype[0];
                }
                if (isset($data['MatchThePairCardResponseType' . $i . ''])) {
                    array_push($data['MatchThePairCardResponseType' . $i . ''], $prtype[1]);
                    $matchpreviewRes = implode(", ", $data['MatchThePairCardResponseType' . $i . '']);
                } else {
                    $matchpreviewRes = $prtype[1];
                }
//                echo $matchpreviewPro;
//                echo '<br>';
//                echo $matchpreviewRes;
                $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                $element = array();
                $element['exercise_id'] = $excriseId;
                $element['card_id'] = $data['match_the_pair_prompt_card_id' . $i . ''];
                $element['responce_card_id'] = $data['match_the_pair_response_card_id' . $i . ''];
                $element['prompt_preview_option'] = $matchpreviewPro;
                $element['responce_preview_option'] = $matchpreviewRes;
                $element['type'] = 'card';
                $element['card_type'] = 'O';
                $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                $this->getExerciseoptionsTable()->save($Data);
            }
        } elseif ($data['exercise_type'] == 'match-the-pair' && $data['card_type'] == 'card_group') {
            $prtype = explode("-", $data['promteresponsetype']);

            if (isset($data['matchthepairpromptgrouptype'])) {
                $PromptType = implode(", ", $data['matchthepairpromptgrouptype']) . ', ' . $prtype[0];
            } else {
                $PromptType = $prtype[0];
            }

            if (isset($data['matchthepairresponsegrouptype'])) {
                $ResponseType = implode(", ", $data['matchthepairresponsegrouptype']) . ', ' . $prtype[1];
            } else {
                $ResponseType = $prtype[1];
            }
            if (isset($data['match_the_pair_prompt_group_card_id'])) {
                foreach ($data['match_the_pair_prompt_group_card_id'] as $card) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['card_id'] = $card;
                    $element['responce_card_id'] = $card;
                    $element['prompt_preview_option'] = $PromptType;
                    $element['responce_preview_option'] = $ResponseType;
                    $element['type'] = 'card';
                    $element['card_type'] = 'O';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                }
            }
            if (isset($data['match_the_pair_prompt_group_group_id'])) {
                foreach ($data['match_the_pair_prompt_group_group_id'] as $group) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['group_id'] = $group;
                    $element['type'] = 'group';
                    $element['card_type'] = 'O';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                }
            }

            if (isset($data['optiongroupcardid'])) {
                foreach ($data['optiongroupcardid'] as $card) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['card_id'] = $card;
                    $element['responce_card_id'] = $card;
                    $element['prompt_preview_option'] = $PromptType;
                    $element['responce_preview_option'] = $ResponseType;
                    $element['type'] = 'group';
                    $element['card_type'] = 'O';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                }
            }
        } elseif ($data['exercise_type'] == 'match-the-pair' && $data['card_type'] == 'custom') {
            $this->getExerciseCustomOptionsTable()->deleteAll(['exercise_id' => $excriseId]);
            $prtype = explode("-", $data['promteresponsetype']);
            $noofcard = $data['noofcard'];
            for ($i = 1; $i <= $noofcard; $i++) {
                $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                $options = array();
                $options['exercise_id'] = $excriseId;
                if (
                    isset($data['match_the_pair_custom_prompt_type' . $i])
                    && $data['match_the_pair_custom_prompt_type' . $i] == 'card'
                ) {
                    $options['card_id'] = $data['Match_The_Pair_Custom_Prompt_' . $i . ''];
                    if (isset($data['Match_The_Pair_Custom' . $i . 'PromptRadio'])) {
                        array_push($data['Match_The_Pair_Custom' . $i . 'PromptRadio'], $prtype[0]);
                        $matchpreviewPro = implode(", ", $data['Match_The_Pair_Custom' . $i . 'PromptRadio']);
                    } else {
                        $matchpreviewPro = $prtype[0];
                    }
                    $options['prompt_preview_option'] = $matchpreviewPro;
                }
                if (
                    isset($data['match_the_pair_custom_response_type' . $i])
                    && $data['match_the_pair_custom_response_type' . $i] == 'card'
                ) {
                    $options['responce_card_id'] = $data['Match_The_Pair_Custom_Response_' . $i . ''];
                    if (isset($data['Match_The_Pair_Custom' . $i . 'ResponseRadio'])) {
                        array_push($data['Match_The_Pair_Custom' . $i . 'ResponseRadio'], $prtype[1]);
                        $matchpreviewRes = implode(", ", $data['Match_The_Pair_Custom' . $i . 'ResponseRadio']);
                    } else {
                        $matchpreviewRes = $prtype[1];
                    }
                    $options['responce_preview_option'] = $matchpreviewRes;
                }
                $options['type'] = 'card';
                $options['card_type'] = 'O';
                $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $options);
                $OptionData = $this->getExerciseoptionsTable()->save($Data);

                $custom_options = array();
                $custom_options['exercise_id'] = $excriseId;
                $custom_options['exercise_option_id'] = $OptionData['id'];
                if (
                    isset($data['match_the_pair_custom_prompt_type' . $i])
                    && $data['match_the_pair_custom_prompt_type' . $i] == 'html'
                ) {
                    $custom_options['prompt_audio_id'] = $data['Match_The_Pair_Prompt_' . $i . '_audio_id'];
                    $custom_options['prompt_image_id'] = $data['Match_The_Pair_Prompt_' . $i . '_image_id'];
                    $custom_options['prompt_html'] = $data['Match_The_Pair_Prompt_' . $i];
                }
                if (
                    isset($data['match_the_pair_custom_response_type' . $i])
                    && $data['match_the_pair_custom_response_type' . $i] == 'html'
                ) {
                    $custom_options['response_audio_id'] = $data['Match_The_Pair_Response_' . $i . '_audio_id'];
                    $custom_options['response_image_id'] = $data['Match_The_Pair_Response_' . $i . '_image_id'];
                    $custom_options['response_html'] = $data['Match_The_Pair_Response_' . $i];
                }
                if (
                    (isset($data['match_the_pair_custom_prompt_type' . $i])
                        && $data['match_the_pair_custom_prompt_type' . $i] == 'html')
                    || (isset($data['match_the_pair_custom_response_type' . $i])
                        && $data['match_the_pair_custom_response_type' . $i] == 'html')
                ) {
                    $exerciseCustomOption = $this->getExerciseCustomOptionsTable()->newEmptyEntity();
                    $CustomData = $this->getExerciseCustomOptionsTable()
                        ->patchEntity($exerciseCustomOption, $custom_options);
                    $this->getExerciseCustomOptionsTable()->save($CustomData);
                }
            }
        } elseif ($data['exercise_type'] == 'truefalse' && $data['card_type'] == 'card') {
            $prtype = explode("-", $data['promteresponsetype']);
            if (isset($data['TrueFalseCardPromptType'])) {
                $PromptType = implode(", ", $data['TrueFalseCardPromptType']) . ', ' . $prtype[0];
            } else {
                $PromptType = $prtype[0];
            }
            if (isset($data['TrueFalseCardResponseType'])) {
                $ResponseType = implode(", ", $data['TrueFalseCardResponseType']) . ', ' . $prtype[1];
            } else {
                $ResponseType = $prtype[1];
            }
            if (isset($data['true_false_prompt_card_id']) && isset($data['true_false_response_card_id'])) {
                $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                $element = array();
                $element['exercise_id'] = $excriseId;
                $element['card_id'] = $data['true_false_prompt_card_id'];
                $element['responce_card_id'] = $data['true_false_response_card_id'];
                $element['prompt_preview_option'] = $PromptType;
                $element['responce_preview_option'] = $ResponseType;
                $element['response_true_false'] = $data['response_card_type'];
                $element['type'] = 'card';
                $element['card_type'] = 'O';
                $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                $this->getExerciseoptionsTable()->save($Data);
            }
        } elseif ($data['exercise_type'] == 'truefalse' && $data['card_type'] == 'custom') {
            $this->getExerciseCustomOptionsTable()->deleteAll(['exercise_id' => $excriseId]);
            $prtype = explode("-", $data['promteresponsetype']);
            if (isset($data['TrueFalseCustomPromptInputRadio'])) {
                $PromptType = implode(", ", $data['TrueFalseCustomPromptInputRadio']) . ', ' . $prtype[0];
            } else {
                $PromptType = $prtype[0];
            }

            if (isset($data['TrueFalseCustomResponseInputRadio'])) {
                $ResponseType = implode(", ", $data['TrueFalseCustomResponseInputRadio']) . ', ' . $prtype[1];
            } else {
                $ResponseType = $prtype[1];
            }

            $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
            $element = array();
            $element['exercise_id'] = $excriseId;

            if (isset($data['true_false_prompt_type'])) {
                if ($data['true_false_prompt_type'] == 'card') {
                    $element['card_id'] = $data['TrueFalseCustomPromptInput'];
                    $element['prompt_preview_option'] = $PromptType;
                    $element['prompt_image_id'] = null;
                    $element['prompt_audio_id'] = null;
                } else {
                    $element['card_id'] = null;
                    $element['prompt_preview_option'] = null;
                    $element['prompt_html'] = $data['TrueFalseCustomPromptInput'];

                    if ($data['TrueFalseCustomPromptInput_image_id']) {
                        $element['prompt_image_id'] = $data['TrueFalseCustomPromptInput_image_id'];
                    } else {
                        $element['prompt_image_id'] = null;
                    }
                    if ($data['TrueFalseCustomPromptInput_audio_id']) {
                        $element['prompt_audio_id'] = $data['TrueFalseCustomPromptInput_audio_id'];
                    } else {
                        $element['prompt_audio_id'] = null;
                    }
                }
            }

            if (isset($data['true_false_response_type'])) {
                if ($data['true_false_response_type'] == 'card') {
                    $element['responce_card_id'] = $data['TrueFalseCustomResponseInput'];
                    $element['responce_preview_option'] = $ResponseType;
                    $element['responce_audio_id'] = null;
                    $element['responce_image_id'] = null;
                } else {
                    $element['responce_card_id'] = null;
                    $element['responce_preview_option'] = null;
                    $element['response_html'] = $data['TrueFalseCustomResponseInput'];

                    if ($data['TrueFalseCustomResponseInput_image_id']) {
                        $element['response_image_id'] = $data['TrueFalseCustomResponseInput_image_id'];
                    } else {
                        $element['response_image_id'] = null;
                    }
                    if ($data['TrueFalseCustomResponseInput_audio_id']) {
                        $element['response_audio_id'] = $data['TrueFalseCustomResponseInput_audio_id'];
                    } else {
                        $element['response_audio_id'] = null;
                    }
                }
            }
            $element['response_true_false'] = isset($data['response_custom_type'])
                ? $data['response_custom_type'] : null;
            $element['type'] = 'card';
            $element['card_type'] = 'O';
            $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
            $optionsData = $this->getExerciseoptionsTable()->save($Data);
            $element['exercise_option_id'] = $optionsData['id'];
            $CustomOptionsData = $this->getExerciseCustomOptionsTable()->newEmptyEntity();
            $OptionsData = $this->getExerciseCustomOptionsTable()->patchEntity($CustomOptionsData, $element);
            $this->getExerciseCustomOptionsTable()->save($OptionsData);
        } elseif ($data['exercise_type'] == 'truefalse' && $data['card_type'] == 'card_group') {
            $prtype = explode("-", $data['promteresponsetype']);
            if (isset($data['TrueFalseGruopPromptType'])) {
                $PromptType = implode(", ", $data['TrueFalseGruopPromptType']) . ', ' . $prtype[0];
            } else {
                $PromptType = $prtype[0];
            }
            if (isset($data['TrueFalseGruopResponseType'])) {
                $ResponseType = implode(", ", $data['TrueFalseGruopResponseType']) . ', ' . $prtype[1];
            } else {
                $ResponseType = $prtype[1];
            }
            $arr = array();
            if (isset($data['true_false_prompt_group_card_id'])) {
                foreach ($data['true_false_prompt_group_card_id'] as $m) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['card_id'] = $m;
                    $element['prompt_preview_option'] = $PromptType;
                    $element['responce_preview_option'] = $ResponseType;
                    $element['type'] = 'card';
                    $element['card_type'] = 'P';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                    array_push($arr, $m);
                }
            }
            if (isset($data['truefalsegruoppromptcardid'])) {
                foreach ($data['truefalsegruoppromptcardid'] as $c) {
                    if (!in_array($c, $arr)) {
                        $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                        $element = array();
                        $element['exercise_id'] = $excriseId;
                        $element['card_id'] = $c;
                        $element['prompt_preview_option'] = $PromptType;
                        $element['responce_preview_option'] = $ResponseType;
                        $element['type'] = 'group';
                        $element['card_type'] = 'P';
                        $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                        $this->getExerciseoptionsTable()->save($Data);
                    }
                }
            }
            if (isset($data['true_false_prompt_group_id'])) {
                foreach ($data['true_false_prompt_group_id'] as $g) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['group_id'] = $g;
                    $element['type'] = 'group';
                    $element['card_type'] = 'P';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                }
            }
        } elseif ($data['exercise_type'] == 'anagram' && $data['card_type'] == 'card') {
            $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
            $element = array();
            $element['exercise_id'] = $excriseId;
            $element['card_id'] = isset($data['anagram_card']) ? $data['anagram_card'] : null;
            $element['prompt_preview_option'] = $data['promotetype'];
            $element['responce_preview_option'] = $data['responsetype'];
            $element['instruction'] = $data['instruction'];
            $element['type'] = 'card';
            $element['card_type'] = 'O';
            $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
            $this->getExerciseoptionsTable()->save($Data);
        } elseif ($data['exercise_type'] == 'anagram' && $data['card_type'] == 'card_group') {
            if (isset($data['anagram_group_card_id'])) {
                foreach ($data['anagram_group_card_id'] as $c) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['card_id'] = $c;
                    $element['prompt_preview_option'] = $data['promotetype'];
                    $element['responce_preview_option'] = $data['responsetype'];
                    $element['instruction'] = $data['instruction'];
                    $element['type'] = 'card';
                    $element['card_type'] = 'P';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                }
            }
            if (isset($data['anagram_group_group_id'])) {
                foreach ($data['anagram_group_group_id'] as $g) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['group_id'] = $g;
                    $element['prompt_preview_option'] = $data['promotetype'];
                    $element['responce_preview_option'] = $data['responsetype'];
                    $element['instruction'] = $data['instruction'];
                    $element['type'] = 'group';
                    $element['card_type'] = 'P';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                }
            }
            if (isset($data['anagram_group_group_card_id'])) {
                foreach ($data['anagram_group_group_card_id'] as $gc) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['card_id'] = $gc;
                    $element['prompt_preview_option'] = $data['promotetype'];
                    $element['responce_preview_option'] = $data['responsetype'];
                    $element['instruction'] = $data['instruction'];
                    $element['type'] = 'group';
                    $element['card_type'] = 'P';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                }
            }
        } elseif ($data['exercise_type'] == 'fill_in_the_blanks' && $data['card_type'] == 'card') {
            /* insert question */
            $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
            $element = array();
            $element['exercise_id'] = $excriseId;
            $element['card_id'] = isset($data['fill_in_the_blank_card_typing'])
                ? $data['fill_in_the_blank_card_typing'] : null;
            $element['prompt_preview_option'] = $data['promotetype'];
            $element['responce_preview_option'] = $data['responsetype'];
            $element['instruction'] = $data['instruction'];
            $element['text_option'] = isset($data['fill_blank_lakota']) ? $data['fill_blank_lakota'] : null;
            $element['fill_in_the_blank_type'] = isset($data['fill_in_the_blank_type'])
                ? $data['fill_in_the_blank_type'] : null;
            $element['type'] = 'card';
            $element['card_type'] = 'P';
            $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
            $this->getExerciseoptionsTable()->save($Data);

//            $str='Required. Sp[ecifies the st[ring to return a part of';
//            $needle = "[";
//            $lastPos = 0;
//            $startpositions = array();
//            while (($lastPos = strpos($str, $needle, $lastPos))!== false) {
//                $startpositions[] = $lastPos;
//                $lastPos = $lastPos + strlen($needle);
//            }
//            print_r($startpositions);

            /* insert option */

            if (isset($data['fill_in_the_blank_type'])) {
                if ($data['fill_in_the_blank_type'] == 'typing') {
                    $options = explode(",", $data['fill_blank_lakota_ans']);
                } else {
                    $options = $data['fill_blank_lakota_options'];
                    $optionsAnswar = explode(",", $data['fill_blank_lakota_ans']);
                    $tempOptionsAnswer = $optionsAnswar;
                }
            }

            $c = 1;
            if (isset($options)) {
                foreach ($options as $option) {
                    if ($option == '') {
                        // Marks blank is empty, probably cause just switched to new fill-in type
                        continue;
                    }
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['card_id'] = $data['fill_in_the_blank_card_typing'];
                    $element['prompt_preview_option'] = $data['promotetype'];
                    $element['responce_preview_option'] = $data['responsetype'];
                    $element['instruction'] = $data['instruction'];
                    $element['text_option'] = $option;
                    $element['fill_in_the_blank_type'] = $data['fill_in_the_blank_type'];
                    if ($data['fill_in_the_blank_type'] == 'typing') {
                        $element['option_position'] = $c;
                    } else {
                        if (in_array($option, $tempOptionsAnswer)) {
                            $optionIndex = array_search($option, $tempOptionsAnswer);
                            unset($tempOptionsAnswer[$optionIndex]);
                            // Check for duplicate, ie. if there are 2 "same" correct answers
                            $element['option_position'] = $optionIndex + 1;
                        } else {
                            $element['option_position'] = null;
                        }
                    }
                    $element['type'] = 'card';
                    $element['card_type'] = 'O';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                    $c++;
                }
            }
        } elseif ($data['exercise_type'] == 'fill_in_the_blanks' && $data['card_type'] == 'custom') {
            if (!isset($data['fill_in_the_blanks_prompt_type'])) {
                return true;
            }
            $this->getExerciseCustomOptionsTable()->deleteAll(['exercise_id' => $excriseId]);
            $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
            $element = array();
            $element['exercise_id'] = $excriseId;
            if (!empty($data['Fill_In_The_Balnks_Custom'])) {
                $element['card_id'] = $data['Fill_In_The_Balnks_Custom'];
                $element['type'] = 'card';
                $element['card_type'] = 'P';
                $element['prompt_preview_option'] = $data['promotetype'];
                $element['responce_preview_option'] = $data['responsetype'];
                $element['text_option'] = $data['fill_blank_custom_lakota'];
                $element['fill_in_the_blank_type'] = 'typing';
                $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                $this->getExerciseoptionsTable()->save($Data);
                $options = explode(",", $data['fill_blank_custom_lakota_ans']);
                $c = 1;
                foreach ($options as $option) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['card_id'] = $data['Fill_In_The_Balnks_Custom'];
                    $element['prompt_preview_option'] = $data['promotetype'];
                    $element['responce_preview_option'] = $data['responsetype'];
                    $element['text_option'] = $option;
                    $element['option_position'] = $c;
                    $element['type'] = 'card';
                    $element['card_type'] = 'O';
                    $element['fill_in_the_blank_type'] = 'typing';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                    $c++;
                }
            } elseif (!empty($data['Fill_In_The_Blanks'])) {
                $element['type'] = 'card';
                $element['card_type'] = 'P';
                $element['text_option'] = $data['fill_blank_custom_lakota'];
                $element['fill_in_the_blank_type'] = 'typing';
                $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                $optionData = $this->getExerciseoptionsTable()->save($Data);

                $CustomOptions = $this->getExerciseCustomOptionsTable()->newEmptyEntity();
                $option = array();
                $option['exercise_id'] = $excriseId;
                $option['exercise_option_id'] = $optionData['id'];
                $option['prompt_audio_id'] = $data['Fill_In_The_Blanks_audio_id'];
                $option['prompt_image_id'] = $data['Fill_In_The_Blanks_image_id'];
                $option['prompt_html'] = $data['Fill_In_The_Blanks'];
                $optionData = $this->getExerciseCustomOptionsTable()->patchEntity($CustomOptions, $option);
                $this->getExerciseCustomOptionsTable()->save($optionData);

                $options = explode(",", $data['fill_blank_custom_lakota_ans']);
                $c = 1;
                foreach ($options as $option) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['text_option'] = $option;
                    $element['option_position'] = $c;
                    $element['type'] = 'card';
                    $element['card_type'] = 'O';
                    $element['fill_in_the_blank_type'] = 'typing';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                    $c++;
                }
            }
        } elseif ($data['exercise_type'] == 'recording' && $data['card_type'] == 'card') {
            $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
            $element = array();
            $element['exercise_id'] = $excriseId;
            $element['card_id'] = $data['recording_prompt_card'];
            $element['responce_card_id'] = $data['recording_response_card'];
            $element['type'] = 'card';
            $element['card_type'] = 'P';
            $element['prompt_preview_option'] = $data['promotetype'];
            $element['responce_preview_option'] = $data['responsetype'];
            $element['response_true_false'] = isset($data['response_true_false_card'])
                ? $data['response_true_false_card'] : 'N';
            $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
            $this->getExerciseoptionsTable()->save($Data);
        } elseif ($data['exercise_type'] == 'recording' && $data['card_type'] == 'card_group') {
            $promotecardIdFlag = array();
            /* for promote input */
            if (isset($data['recording_prompt_group_card_id'])) {
                foreach ($data['recording_prompt_group_card_id'] as $card) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['card_id'] = $card;
                    $element['type'] = 'card';
                    $element['card_type'] = 'P';
                    $element['prompt_preview_option'] = $data['promotetype'];
                    $element['responce_preview_option'] = $data['responsetype'];
                    $element['response_true_false'] = isset($data['response_true_false_group'])
                        ? $data['response_true_false_group'] : 'N';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                    array_push($promotecardIdFlag, $card);
                }
            }

            if (isset($data['recording_group_card_id'])) {
                foreach ($data['recording_group_card_id'] as $card) {
                    if (!in_array($card, $promotecardIdFlag)) {
                        $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                        $element = array();
                        $element['exercise_id'] = $excriseId;
                        $element['card_id'] = $card;
                        $element['type'] = 'group';
                        $element['card_type'] = 'P';
                        $element['prompt_preview_option'] = $data['promotetype'];
                        $element['responce_preview_option'] = $data['responsetype'];
                        $element['response_true_false'] = isset($data['response_true_false_group'])
                            ? $data['response_true_false_group'] : 'N';
                        $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                        $this->getExerciseoptionsTable()->save($Data);
                        array_push($promotecardIdFlag, $card);
                    }
                }
            }
            if (isset($data['recording_prompt_group_group_id'])) {
                foreach ($data['recording_prompt_group_group_id'] as $group) {
                    $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
                    $element = array();
                    $element['exercise_id'] = $excriseId;
                    $element['group_id'] = $group;
                    $element['type'] = 'group';
                    $element['card_type'] = 'P';
                    $element['prompt_preview_option'] = $data['promotetype'];
                    $element['responce_preview_option'] = $data['responsetype'];
                    $element['response_true_false'] = isset($data['response_true_false_group'])
                        ? $data['response_true_false_group'] : 'N';
                    $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
                    $this->getExerciseoptionsTable()->save($Data);
                }
            }
        } elseif ($data['exercise_type'] == 'recording' && $data['card_type'] == 'custom') {
            $this->getExerciseCustomOptionsTable()->deleteAll(['exercise_id' => $excriseId]);
            $exerciseOption = $this->getExerciseoptionsTable()->newEmptyEntity();
            $element = array();
            $element['exercise_id'] = $excriseId;
            if (
                isset($data['recording_custom_prompt_type'])
                && $data['recording_custom_prompt_type'] == 'card'
            ) {
                $element['card_id'] = $data['Recording_Custom'];
            }
            $element['responce_card_id'] = $data['recording_custom_response_card_id'];
            $element['type'] = 'card';
            $element['card_type'] = 'P';
            $element['prompt_preview_option'] = $data['promotetype'];
            $element['responce_preview_option'] = $data['responsetype'];
            $element['response_true_false'] = isset($data['response_true_false_custom'])
                ? $data['response_true_false_custom'] : 'N';
            $Data = $this->getExerciseoptionsTable()->patchEntity($exerciseOption, $element);
            $optionData = $this->getExerciseoptionsTable()->save($Data);
            if (
                isset($data['recording_custom_prompt_type'])
                && $data['recording_custom_prompt_type'] == 'html'
            ) {
                $CustomOptions = $this->getExerciseCustomOptionsTable()->newEmptyEntity();
                $option = array();
                $option['exercise_id'] = $excriseId;
                $option['exercise_option_id'] = $optionData['id'];
                $option['prompt_audio_id'] = $data['Recording_audio_id'];
                $option['prompt_image_id'] = $data['Recording_image_id'];
                $option['prompt_html'] = $data['Recording'];
                $optionData = $this->getExerciseCustomOptionsTable()->patchEntity($CustomOptions, $option);
                $this->getExerciseCustomOptionsTable()->save($optionData);
            }
        }
        return true;
    }

    //validate Exercise data

    /**
     * Using this for frame deletion and update/addition because it's thorough
     * and multiple lesson frames or exercise could have the same card, so in
     * order to not make any assumptions (and just delete the cards related to
     * this newly deleted frame) we will check all lesson frames and exercises
     * and make sure the CardUnits table is up-to-date.
     */
    public function updateCardUnitsAccordingToExercise($exercise)
    {
        if ($exercise == null) {
            Log::error('$exercise is null');
            return false;
        } elseif ($exercise->id == null) {
            Log::error('$exercise->id is null');
            return false;
        }

        // Use LessonFrame to determine Lesson, find Units with that lesson,
        // find CardUnits with that Unit, add cards from lesson frame if
        // they don't exist already in that unit

        // Find unit details that contain the lesson with the frame being
        // added/changed/deleted, to get list of units
        $unitsWithThisExercise = $this->getUnitdetailsTable()
            ->find()
            ->select('unit_id')
            ->distinct(['unit_id'])
            ->where(['exercise_id' => $exercise->id])
            ->all()
            ->toList();

        $unitsWithThisExerciseCount = $this->getUnitdetailsTable()
            ->find()
            ->select('unit_id')
            ->distinct(['unit_id'])
            ->where(['exercise_id' => $exercise->id])
            ->count();


        if ($unitsWithThisExerciseCount <= 0) {
            return true;
        }

        // Use list of units to find unit details for those units
        foreach ($unitsWithThisExercise as $unit_id) {
            $unitOptions = array(
                'conditions' => array(
                    'unit_id IS' => $unit_id->unit_id
                ),
                'contain' => array(
                    'Lessons',
                    'Lessons.Lessonframes',
                    'Lessons.Lessonframes.LessonFrameBlocks',
                    'Exercises',
                    'Exercises.Exerciseoptions'
                )
            );

            $unitsDetails = $this->getUnitdetailsTable()->find('all', $unitOptions);

            // For the current unit in the loop, get all the cards in any exercises or lessons
            $cardIds = array();
            foreach ($unitsDetails as $key => $unit) {
                $this->addCardsFromActivityToCardUnits($unit, $cardIds);
            }

            if (empty($cardIds)) {
                continue;
            }

            $cardIds = array_values(array_unique($cardIds));

            // Find cardUnits for the current unit
            $unitOptions = array(
                'conditions' => array(
                    'unit_id IS' => $unit_id->unit_id
                ),
                'keyField' => 'id',
                'valueField' => 'card_id'
            );
            $unitCardIds = $this->getCardUnitsTable()->find('list', $unitOptions)->toArray();

            // If some cardUnits exist for the current unit, get the cardIds, without duplicates
            if (!empty($unitCardIds)) {
                $unitCardIds = array_values(array_unique($unitCardIds));
            }

            // Delete cards that are no longer in the unit
            $this->getCardUnitsTable()->deleteAll(['unit_id' => $unit_id->unit_id, 'card_id NOT IN' => $cardIds]);

            // Create array of new items to batch save to the database table
            $newCardUnits = [];
            foreach ($cardIds as $cardId) {
                if (!in_array($cardId, $unitCardIds)) {
                    $newCardUnits[] = ['card_id' => $cardId, 'unit_id' => $unit_id->unit_id];
                }
            }

            // Batch create entities from above array and save atomically to database table using
            // cakephp Saving Multiple Entities functionality since v3.2.8
            if (!empty($newCardUnits)) {
                $entities = $this->getCardUnitsTable()->newEntities($newCardUnits);
                $result = $this->getCardUnitsTable()->saveMany($entities);
            }
        }

        return true;
    }

    public function deleteExercises($exerciseId = null)
    {
        $exercise = $this->getExercisesTable()->get($exerciseId);
        if ($this->getExercisesTable()->delete($exercise)) {
            $this->Flash->success(__('The Exercise has been deleted.'));
        } else {
            $this->Flash->error(__('The Exercise could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'manageExercises']);
    }

    // preview html for edit in admin

    public function previewBlock()
    {
        $data = $_POST;

        if (isset($data['type'])) {
            $types = $data['type'];
            $cid = array();

            if (isset($data['responseCardId'])) {
                $responseCardId = $data['responseCardId'];
            } else {
                $responseCardId = null;
            }


            if (isset($data['cardId'])) {
                if (is_array($data['cardId'])) {
                    $cardIds = $data['cardId'];
                    $condition = array('Card.id IN' => $cardIds);
                } else {
                    $cardIds = $data['cardId'];
                    $condition = array('Card.id' => $cardIds);
                }
                $cardsDetails = $this->getCardTable()
                    ->find('all', ['contain' => ['Cardtype', 'image', 'video'],])
                    ->where($condition);
                if (isset($data['responseCardId'])) {
                    $responseCardId = $data['responseCardId'];
                } else {
                    $responseCardId = null;
                }
                foreach ($cardsDetails as $card) {
                    foreach ($types as $type) {
                        if ($responseCardId == null) {
                            array_push($cid, $card['id']);
                            if ($type == 'l') {
                                echo '<div class="lakota">' . $card['lakota'] . '</div>';
                            }
                            if ($type == 'e') {
                                echo '<div class="english">' . $card['english'] . '</div>';
                            }
                            if ($type == 'a') {
                                ?>
                                       <div class='audiodiv'>
                                    <?php if (!empty($card['FullAudioUrl'])) { ?>
                                                <audio controls style="max-width: 95%;">
                                                    <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                            type="audio/mpeg">
                                                    <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                        type="audio/ogg">
                                                    <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                        type="audio/wav">
                                                    Your browser does not support the audio element.
                                                </audio>
                                        <?php
                                    } else {
                                        ?>
                                                No audio Available
                                    <?php } ?>
                                       </div>

                                <?php
                            }
                            if ($type == 'v') {
                                ?>
                                       <div class='videodiv'>
                                    <?php if (!empty($card['video']) && !empty($card['video']['FullUrl'])) { ?>
                                                <video height="100" controls>
                                                    <source src="<?php echo $card['video']['FullUrl'] ?>"
                                                            type="video/mp4">
                                                    <source src="<?php echo $card['video']['FullUrl'] ?>"
                                                            type="video/ogg">
                                                    Your browser does not support the video tag.
                                                </video>
                                        <?php
                                    } else {
                                        ?>
                                                No Video Available.
                                    <?php } ?>
                                       </div>

                                <?php
                            }
                            if ($type == 'i') {
                                ?>
                                       <div class='imagediv'>
                                    <?php if (!empty($card['image']['FullUrl'])) { ?>
                                                <img src="<?php echo $card['image']['FullUrl'] ?>"
                                                    style="max-width: 95%;">
                                        <?php
                                    } else {
                                        ?>
                                                No Image Available.
                                    <?php } ?>
                                       </div>
                                <?php
                            }
                        } else {
                            if ($responseCardId != $card['id']) {
                                array_push($cid, $card['id']);
                                if ($type == 'l') {
                                    echo '<div class="lakota">' . $card['lakota'] . '</div>';
                                }
                                if ($type == 'e') {
                                    echo '<div class="english">' . $card['english'] . '</div>';
                                }
                                if ($type == 'a') {
                                    ?>
                                            <div class='audiodiv'>
                                        <?php if (!empty($card['FullAudioUrl'])) { ?>
                                                     <audio controls style="max-width: 95%;">
                                                         <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                                 type="audio/mpeg">
                                                         <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                                 type="audio/ogg">
                                                         <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                                 type="audio/wav">
                                                         Your browser does not support the audio element.
                                                     </audio>
                                            <?php
                                        } else {
                                            ?>
                                                     No audio Available
                                        <?php } ?>
                                            </div>

                                    <?php
                                }
                                if ($type == 'v') {
                                    ?>
                                            <div class='videodiv'>
                                        <?php if (!empty($card['video']) && !empty($card['video']['FullUrl'])) { ?>
                                                     <video height="100" controls>
                                                         <source src="<?php echo $card['video']['FullUrl'] ?>"
                                                                 type="video/mp4">
                                                         <source src="<?php echo $card['video']['FullUrl'] ?>"
                                                                 type="video/ogg">
                                                         Your browser does not support the video tag.
                                                     </video>
                                            <?php
                                        } else {
                                            ?>
                                                     No Video Available.
                                        <?php } ?>
                                            </div>

                                    <?php
                                }
                                if ($type == 'i') {
                                    ?>
                                            <div class='imagediv'>
                                        <?php if (!empty($card['image']['FullUrl'])) { ?>
                                                     <img src="<?php echo $card['image']['FullUrl'] ?>"
                                                        style="max-width: 95%;">
                                            <?php
                                        } else {
                                            ?>
                                                     No Image Available.
                                        <?php } ?>
                                            </div>
                                    <?php
                                }
                            }
                        }
                    }
                }
            }

            if (isset($data['groupId'])) {
                $groupCardsDetails = $this->getCardcardgroupTable()
                    ->find('all', ['contain' => 'Card'])
                    ->where(['card_group_id' => $data['groupId']]);
                foreach ($groupCardsDetails as $cardFlag) {
                    $card = $this->getCardTable()
                        ->find('all', ['contain' => ['Cardtype', 'image', 'video']])
                        ->where(['Card.id' => $cardFlag['card_id']])
                        ->first();
                    foreach ($types as $type) {
                        if ($responseCardId == null && !in_array($card['id'], $cid)) {
                            if ($type == 'l') {
                                echo '<div class="lakota">' . $card['lakota'] . '</div>';
                            }
                            if ($type == 'e') {
                                echo '<div class="english">' . $card['english'] . '</div>';
                            }
                            if ($type == 'a') {
                                ?>
                                       <div class='audiodiv'>
                                    <?php if (!empty($card['FullAudioUrl'])) { ?>
                                                <audio controls style="max-width: 95%;">
                                                    <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                        type="audio/mpeg">
                                                    <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                        type="audio/ogg">
                                                    <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                        type="audio/wav">
                                                    Your browser does not support the audio element.
                                                </audio>
                                        <?php
                                    } else {
                                        ?>
                                                No audio Available
                                    <?php } ?>
                                       </div>

                                <?php
                            }
                            if ($type == 'v') {
                                ?>
                                       <div class='videodiv'>
                                    <?php if (!empty($card['video']) && !empty($card['video']['FullUrl'])) { ?>
                                                <video height="100" controls>
                                                    <source src="<?php echo $card['video']['FullUrl'] ?>"
                                                        type="video/mp4">
                                                    <source src="<?php echo $card['video']['FullUrl'] ?>"
                                                        type="video/ogg">
                                                    Your browser does not support the video tag.
                                                </video>
                                        <?php
                                    } else {
                                        ?>
                                                No Video Available.
                                    <?php } ?>
                                       </div>

                                <?php
                            }
                            if ($type == 'i') {
                                ?>
                                <div class='imagediv'>
                                    <img src="<?php echo $card['image']['FullUrl'] ?>" style="max-width: 95%;">
                                </div>
                                <?php
                            }
                        } else {
                            if ($responseCardId != $card['id'] && !in_array($card['id'], $cid)) {
                                if ($type == 'l') {
                                    echo '<div class="lakota">' . $card['lakota'] . '</div>';
                                }
                                if ($type == 'e') {
                                    echo '<div class="english">' . $card['english'] . '</div>';
                                }
                                if ($type == 'a') {
                                    ?>
                                            <div class='audiodiv' style="overflow: auto;">
                                        <?php if (!empty($card['FullAudioUrl'])) { ?>
                                                     <audio controls style="max-width: 95%;">
                                                         <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                                 type="audio/mpeg">
                                                         <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                                 type="audio/ogg">
                                                         <source src="<?php echo $card['FullAudioUrl']; ?>"
                                                                 type="audio/wav">
                                                         Your browser does not support the audio element.
                                                     </audio>
                                            <?php
                                        } else {
                                            ?>
                                                     No audio Available
                                        <?php } ?>
                                            </div>

                                    <?php
                                }
                                if ($type == 'v') {
                                    ?>
                                            <div class='videodiv'>
                                        <?php if (!empty($card['video']) && !empty($card['video']['FullUrl'])) { ?>
                                                     <video height="100" controls>
                                                         <source src="<?php echo $card['video']['FullUrl'] ?>"
                                                                 type="video/mp4">
                                                         <source src="<?php echo $card['video']['FullUrl'] ?>"
                                                                 type="video/ogg">
                                                         Your browser does not support the video tag.
                                                     </video>
                                            <?php
                                        } else {
                                            ?>
                                                     No Video Available.
                                        <?php } ?>
                                            </div>

                                    <?php
                                }
                                if ($type == 'i') {
                                    ?>
                                    <div class='imagediv'>
                                        <img src="<?php echo $card['image']['FullUrl'] ?>" style="max-width: 95%;">
                                    </div>
                                    <?php
                                }
                            }
                        }
                    }
                }
            }
        }
        die;
    }

    // preview html for edit in admin
    public function getCard()
    {
        $data = $_POST;
        $cardIds = isset($data['cardId']) ? $data['cardId'] : array();
        $condition = array('Card.id' => $cardIds);
        $cardsDetails = $this->getCardTable()
            ->find('all', ['contain' => ['Cardtype', 'image', 'video'],])
            ->where($condition)
            ->first();

        if (!$cardsDetails) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array('message' => 'Error, no card details found')));
        } else {
            echo json_encode($cardsDetails);
            die;
        }
    }

    //get card checkbox html by groupid
    public function getCardByGroupIds()
    {
        $data = $_POST;
        // make sure there are groups
        if (!isset($data['groupIds'])) {
            die;
        }
        $groupIds = $data['groupIds'];
        $divId = $data['divid'];
        $excludeCardIds = array();
        if (!empty($data['excludeCardIds'])) {
            $excludeCardIds = $data['excludeCardIds'];
        } else {
            $excludeCardIds = array();
        }
        $groupCardsDetails = $this->getCardcardgroupTable()
            ->find('all', ['contain' => 'Card'])
            ->where(['card_group_id in' => $groupIds]);
        $checkdup = array();

        foreach ($groupCardsDetails as $card) {
            if (!in_array($card['card_id'], $checkdup) && !in_array($card['card_id'], $excludeCardIds)) {
                if ($divId == 'PomptCardList') {
                    $name = 'promotegroupcardid[]';
                    $class = 'promotegroupcheck';
                    $id = 'promotegroupcheck' . $card['card_id'];
                } elseif ($divId == 'TrueFalseGruopPromptCardCheckbox') {
                    $name = 'truefalsegruoppromptcardid[]';
                    $class = 'truefalsegruoppromptcheck';
                    $id = 'truefalsegruoppromptcheck' . $card['card_id'];
                } elseif ($divId == 'anagram_group_single_card_preview') {
                    $name = 'anagram_group_group_card_id[]';
                    $class = 'anagram_group_cardcheck';
                    $id = 'anagram_group_cardcheck' . $card['card_id'];
                } elseif ($divId == 'RecordingPomptCardList') {
                    $name = 'recording_group_card_id[]';
                    $class = 'recording_group_cardcheck';
                    $id = 'recording_group_cardcheck' . $card['card_id'];
                } else {
                    $name = 'optiongroupcardid[]';
                    $class = 'optiongroupcheck';
                    $id = 'optiongroupcheck' . $card['card_id'];
                }
                ?>
                   <div class="checkbox">
                       <label>
                            <input type="checkbox" name="<?php echo $name; ?>"
                                value="<?php echo $card['card_id']; ?>"
                                class="<?php echo $class; ?>"
                                id="<?php echo $id; ?>"> Card : <?php echo $card['card_id']; ?>
                       </label>
                   </div>
                <?php
                array_push($checkdup, $card['card_id']);
            }
        }
        die;
    }

    //get card lavel preview by selected card for card type
    public function getCardLabelPreview()
    {
        $data = $_POST;
        if (isset($data['groupId'])) {
            $groupId = $data['groupId'];
        }
        if (isset($data['cardId'])) {
            $cardId = $data['cardId'];
        }
        $excludeCardIds = $data['responseCardId'];

        $result = array();
        if (isset($groupId)) {
            $groupcard = $this->getCardcardgroupTable()
                ->find()
                ->select('card_id')
                ->where(['card_group_id in' => $groupId])
                ->toArray();
            $result = array_merge($result, $groupcard);
        }

        if (isset($cardId)) {
            if (is_array($cardId)) {
                $result = array_merge($result, $cardId);
            } else {
                if ($cardId) {
                    $result = array_push($result, $cardId);
                }
            }
        }

        $result1 = array_unique($result);
        if (($key = array_search($excludeCardIds, $result1)) !== false) {
            unset($result1[$key]);
        }
        $data = array_values($result1);

        if (!empty($data)) {
            echo 'Cards: ' . implode(", ", $data);
        } else {
            echo '';
        }
        die;
    }

    //get card lavel preview by selected card for card type
    public function getMatchThePairHtml()
    {
        $languageName = Configure::read('LANGUAGE');
        $data = $this->request->getData();
        if (empty($data['exerciseId'])) {
            throw new BadRequestException('Missing exerciseId');
        }

        $type = $data['type'];
        $count = $data['count'];
        $exerciseId = $data['exerciseId'];
        $matchThePairValue = [];

        // Get the exercise and its options
        $exercise = $this->getExercisesTable()->get($exerciseId, ['contain' => 'Exerciseoptions']);

        $matchcount = 1;

        foreach ($exercise['exerciseoptions'] as $flagCard) {
            if ($exercise['card_type'] == 'card' && $exercise['exercise_type'] == 'match-the-pair') {
                $matchThePairValue['promptcard' . $matchcount] = $flagCard['card_id'];
                $matchThePairValue['responcecard' . $matchcount] = $flagCard['responce_card_id'];
                $matchThePairValue['promptpreview' . $matchcount] = $flagCard['prompt_preview_option'];
                $matchThePairValue['responcepreview' . $matchcount] = $flagCard['responce_preview_option'];
                $matchcount++;
            }
        }
        $matchThePairValue['promteresponsetype'] = $exercise['promteresponsetype'];
        $this->set(compact('count', 'type', 'matchThePairValue', 'languageName'));
        $this->viewBuilder()->disableAutoLayout();
        $this->render('match_the_pair_html', '');
    }

    //get card lavel preview by selected card for card type
    public function getTrueFalseCardPickerHtml()
    {
        $data = $_POST;
        $InputName = $data['InputName'];
        $languageName = Configure::read('LANGUAGE');
        $this->set(compact('InputName', 'languageName'));
        $this->viewBuilder()->disableAutoLayout();
        $this->render('true_false_card_picker_html');
    }

    //get card lavel preview by selected card for card type
    public function getCardPickerHtml()
    {
        $languageName = Configure::read('LANGUAGE');
        $data = $_POST;
        $inputName = $data['inputName'];
        $blockNo = '';
        $responseType = '';
        if (isset($data['blockNo'])) {
            $blockNo = $data['blockNo'];
        }
        if (isset($data['responseType'])) {
            $responseType = $data['responseType'];
        }
        $this->set(compact('inputName', 'blockNo', 'responseType', 'languageName'));
        $this->viewBuilder()->disableAutoLayout();
        $this->render('card_picker_html');
    }

    //ajax builk user action
    public function exerciseDeleteWarning()
    {
        $data = $_POST;
        $exerciseId = $data['exerciseId'];
        $exercises = $this->getUnitdetailsTable()->find('all', ['conditions' => array('exercise_id' => $exerciseId)]);
        if ($exercises->count() != 0) {
            $linkIds = array();
            foreach ($exercises as $p) {
                $element = array();
                $element['learningPathId'] = $p['learningpath_id'];
                $element['unitId'] = $p['unit_id'];

                $LevelFlag = $this->getLevelUnitsTable()
                    ->find('all', [
                        'conditions' => ['learningpath_id' => $p['learningpath_id'],
                        'unit_id' => $p['unit_id']]])
                    ->first();
                if ($LevelFlag != null) {
                    $LevelData = $LevelFlag->toArray();
                    $element['levelId'] = $LevelData['level_id'];
                    $path = $this->getLearningpathsTable()->get($p['learningpath_id'])->toArray();
                    $element['name'] = $path['label'];
                    array_push($linkIds, $element);
                }
            }
            ?>
            <div class="row">
                <div class='col-sm-12 col-md-12'>
                    This exercise is associated with some Learning paths.
                    Please change the exercise in the path and try to delete it again.
                </div>
                <div class='col-sm-6 col-md-6'>
                    <h3>Path List</h3>
                    <?php
                    foreach ($linkIds as $l) {
                        ?>
                        <div>
                            <a href="<?php echo Router::url('/admin/learning-path/manage-paths/')
                                . $l['learningPathId'] . '/' . $l['levelId'] . '/' . $l['unitId']; ?>"
                                target="_blank"><i class="fa fa-pencil"></i> <?php echo $l['name']; ?></a></div>
                        <?php
                    }
                    if (empty($linkIds)) {
                        echo 'No Path Found.';
                    }
                    ?>
                  </div>
              </div>
            <?php
        } else {
            echo 'success';
            die;
        }
        die;
    }
}
