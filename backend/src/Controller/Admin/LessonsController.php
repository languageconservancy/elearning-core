<?php

namespace App\Controller\Admin;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\Routing\Router;

const FRAME_PREFIX = "Frame ";

class LessonsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {

        parent::beforeFilter($event);

        ini_set('memory_limit', '-1');
    }

    // List all lessons
    public function index(): Response
    {
        return $this->redirect(['action' => 'manageLesson']);
    }

    // manage a lesson
    public function manageLesson($lessonId = null, $frameid = null)
    {
        $languageName = Configure::read('LANGUAGE');
        if ($lessonId == null) {
            // If there's no lesson being edited, then it means user has created a new one
            // so, create a new one and create an empty frame array
            // return $this->redirect(['action' => 'add']);
            $lesson = $this->getLessonsTable()->newEmptyEntity();
            $framelist = array();
        } else {
            // Get lesson entity based on the ID of the one being edited
            $lesson = $this->getLessonsTable()->get($lessonId, ['contain' => ['Lessonframes']]);
            // Get the list of frames in that lesson
            $framelist = $this->getLessonFramesTable()
                ->find()
                ->where(['lesson_id' => $lessonId])
                ->order(['frameorder', 'modified']);
        }
        $frameFile = '';
        if ($frameid != null) {
            // If user is editing a frame in the lesson, then get the frame entity
            $frame = $this->getLessonFramesTable()->get($frameid, ['contain' => ['LessonFrameBlocks']]);
            if (!empty($frame->audio_id)) {
                // If the frame has an audio file, and it's not blank, get the file and set the frameFile var to it
                $File = $this->getFilesTable()->get($frame->audio_id, ['contain' => []]);
                $frameFile = $File->FullUrl;
            }
            // Set the newframe var to the frame currently being edited
            $newframe = $this->getLessonFramesTable()->get($frameid, ['contain' => ['LessonFrameBlocks']]);
        } else {
            // else if no frame is being edited, it means the user just added a new one,
            // so create a new LessonFrame entity
            $frame = $this->getLessonFramesTable()->newEmptyEntity();
            $newframe = $this->getLessonFramesTable()->newEmptyEntity();
        }
        $submitted_block = array();
        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $submited = true;
            // Get the request data
            $data = $this->request->getData();
            if ($data['formtype'] == 'frame') {
                // If user is editing the content of a frame, validate all the fields for each block
                $validateResult = $this->processAndValidateBlockData($data, 1);
                // Update the data in the LessonFrame Entity
                $LessonFrameData = $this->getLessonFramesTable()->patchEntity($frame, $data);
                $submitted_block_Flag = $validateResult['data'];
                $submittedBlockIndex = 1;
                foreach ($submitted_block_Flag as $flag) {
                    if (
                        isset($flag['type']) && ($flag['type'] == 'video'
                        || $flag['type'] == 'image' || $flag['type'] == 'audio')
                    ) {
                        if ($flag['type'] == 'video' && isset($flag['video_id'])) {
                            $File = $this->getFilesTable()->get($flag['video_id'], ['contain' => []]);
                            $flag['VideoUrl'] = $File->FullUrl;
                        }
                        if ($flag['type'] == 'image' && isset($flag['image_id'])) {
                            $File = $this->getFilesTable()->get($flag['image_id'], ['contain' => []]);
                            $flag['ImageUrl'] = $File->FullUrl;
                        }
                        if ($flag['type'] == 'audio' && isset($flag['audio_id'])) {
                            $File = $this->getFilesTable()->get($flag['audio_id'], ['contain' => []]);
                            $flag['AudioUrl'] = $File->FullUrl;
                        }
                    }
                    $submitted_block[$submittedBlockIndex] = $flag;
                    $submittedBlockIndex++;
                }
                if ($validateResult['status'] == 0) {
                    $this->Flash->error($validateResult['message']);
                } else {
                    if ($frameEntity = $this->getLessonFramesTable()->save($LessonFrameData)) {
                        $this->saveFrameBlockData($data, $frameEntity->id);
                        /* Add card from new frame to CardUnits table */
                        if ($this->updateCardUnitsAccordingToLessonFrame($frameEntity) == false) {
                            $this->Flash->error(__(
                                'CardUnits table failed to update. '
                                . 'Invalid LessonFrame or LessonId. '
                                . 'Please check error log for more details.'
                            ));
                        } else {
                            $this->Flash->success(__('The Lesson Frame saved successfully.'));
                            return $this->redirect($this->referer());
                        }
                    } else {
                        $errors = array_values(array_values($LessonFrameData->getErrors()));
                        foreach ($errors as $key => $err) {
                            foreach ($err as $key1 => $err1) {
                                $this->Flash->error($err1);
                            }
                        }
                    }
                }
            } elseif ($data['formtype'] == 'onlyframe') { // else if user is just adding a new frame
                // Create new frames
                $frame = $this->getLessonFramesTable()->newEmptyEntity();
                $newframe = $this->getLessonFramesTable()->newEmptyEntity();

                // If user has added a new frame get the highest frame number
                // and set the new frame's number to one plus that
                $order = $this->getLessonFramesTable()
                    ->find('all', [
                        'fields' => ['maxorder' => 'MAX(LessonFrames.frameorder)']])
                    ->where(['lesson_id' => $lessonId])
                    ->toArray();
                if (empty($order[0]['maxorder'] === '')) {
                    $data['frameorder'] = $order[0]['maxorder'] + 1;
                } else {
                    $data['frameorder'] = 1;
                }
                // Set name of new frame to 'Frame #' where # is the same as the $frameorder value
                $data['name'] = 'Frame ' . $data['frameorder'];

                // Set the frames lesson ID
                $data['lesson_id'] = $lessonId;
                // Update the data of the LessonFrame Entity
                $LessonFrameData = $this->getLessonFramesTable()->patchEntity($newframe, $data);
                // Save the updated LessonFrame Entity to the LessonFramesTable table in the database
                if ($frameEntity = $this->getLessonFramesTable()->save($LessonFrameData)) {
                    $this->Flash->success(__('The Lesson Frame saved successfully.'));
                    return $this->redirect(['action' => 'manageLesson', $lessonId, $frameEntity->id]);
                } else {
                    $errors = array_values(array_values($LessonFrameData->getErrors()));
                    foreach ($errors as $key => $err) {
                        foreach ($err as $key1 => $err1) {
                            $this->Flash->error($err1);
                        }
                    }
                }
            } else { // else user is updating a lesson
                // Update the Lesson Entity with the new frame data
                $lessonData = $this->getLessonsTable()->patchEntity($lesson, $data);
                // Save the Lesson to the LessonsTable table
                if ($less = $this->getLessonsTable()->save($lessonData)) {
                    $this->Flash->success(__('The Lesson saved successfully.'));
                    return $this->redirect(['action' => 'manageLesson', $less->id]);
                    //return $this->redirect($this->referer());
                } else {
                    $errors = array_values(array_values($lessonData->getErrors()));
                    foreach ($errors as $key => $err) {
                        foreach ($err as $key1 => $err1) {
                            $this->Flash->error($err1);
                        }
                    }
                }
            }
        }

        // This seems unnecessary since it's also above only if lessonId is not null
        if ($lessonId != null) {
            $lesson = $this->getLessonsTable()->get($lessonId, ['contain' => ['Lessonframes']]);
        }

        // Get all the lessons
        $lessons = $this->getLessonsTable()->find()
            ->select(['id', 'name'])
            ->toArray();

        // Get the list of cards and add key value pairs to new array where key is the card ID
        // and value is the english text plus the lakota text in parenthesis
        $cardList = $this->getCardTable()->find()->toArray();
        $cards = array();
        foreach ($cardList as $c) {
            $cards[$c['id']] = $c['english'] . '( ' . $c['lakota'] . ' )';
        }
        if (empty($cards)) {
            // If there are no cards, then set the key '0' to 'No cards added' value
            $cards = array('0' => 'No cards added');
        }
        // Pass variables to manageLesson view by setting the vars to be serialized and then serializing them.
        $this->set(compact(
            'lessons',
            'lesson',
            'frame',
            'framelist',
            'frameFile',
            'cards',
            'newframe',
            'submitted_block',
            'languageName'
        ));
        $this->viewBuilder()->setOption('serialize', [
            'lessons',
            'lesson',
            'frame',
            'framelist',
            'frameFile',
            'cards',
            'newframe',
            'submitted_block',
            'languageName'
        ]);
        $this->render('manage_lesson');
    }


//------------------------


    //upload an Excel file for batch card creation

    public function processAndValidateBlockData($data, $lesson_frame_id = null)
    {
        $noOfblock = $data['number_of_block'];
        $frameBlockData = array();
        //process Data
        for ($i = 1; $i <= $noOfblock; $i++) {
            $element = array();
            if ($data['block' . $i . 'type'] == 'html') {
                $element['custom_html'] = $data['custom_html' . $i];
                $element['type'] = 'html';
            }
            if ($data['block' . $i . 'type'] == 'audio') {
                $element['audio_id'] = $data['block_audio_id' . $i];
                $element['type'] = 'audio';
            }
            if ($data['block' . $i . 'type'] == 'image') {
                $element['image_id'] = $data['block_image_id' . $i];
                $element['type'] = 'image';
            }
            if ($data['block' . $i . 'type'] == 'video') {
                $element['video_id'] = $data['block_video_id' . $i];
                $element['type'] = 'video';
            }
            if ($data['block' . $i . 'type'] == 'card') {
                $element['card_id'] = $data['block_card_id' . $i];
                $element['is_card_lakota'] = (isset($data['is_card_lakota' . $i])
                    && ($data['is_card_lakota' . $i] == 'on')) ? 'Y' : 'N';
                $element['is_card_english'] = (isset($data['is_card_english' . $i])
                    && ($data['is_card_english' . $i] == 'on')) ? 'Y' : 'N';
                $element['is_card_audio'] = (isset($data['is_card_audio' . $i])
                    && ($data['is_card_audio' . $i] == 'on')) ? 'Y' : 'N';
                $element['is_card_video'] = (isset($data['is_card_video' . $i])
                    && ($data['is_card_video' . $i] == 'on')) ? 'Y' : 'N';
                $element['is_card_image'] = (isset($data['is_card_image' . $i])
                    && ($data['is_card_image' . $i] == 'on')) ? 'Y' : 'N';
                $element['type'] = 'card';
            }
            array_push($frameBlockData, $element);
        }

        // check validation
        $validate = 1;
        $message = '';
        for ($i = 0; $i < $noOfblock; $i++) {
            if (isset($frameBlockData[$i]['type'])) {
                //validation for card
                if ($frameBlockData[$i]['type'] == 'card') {
                    if ($frameBlockData[$i]['card_id'] == '' || $frameBlockData[$i]['card_id'] == 0) {
                        $validate = 0;
                        $message = 'Please select a card for block ' . ($i + 1);
                        break;
                    }
                    if (
                        $frameBlockData[$i]['is_card_lakota'] == 'N'
                        && $frameBlockData[$i]['is_card_english'] == 'N'
                        && $frameBlockData[$i]['is_card_audio'] == 'N'
                        && $frameBlockData[$i]['is_card_video'] == 'N'
                        && $frameBlockData[$i]['is_card_image'] == 'N'
                    ) {
                        $validate = 0;
                        $message = 'Please check at least one card type under \'Select Card\' in block ' . ($i + 1);
                        break;
                    }
                }
                //validation for html
                if ($frameBlockData[$i]['type'] == 'html') {
                    if (trim($frameBlockData[$i]['custom_html']) == '') {
                        $validate = 0;
                        $message = 'Please enter html for block ' . ($i + 1);
                        break;
                    }
                }
                //validation for audio
                if ($frameBlockData[$i]['type'] == 'audio') {
                    if (trim($frameBlockData[$i]['audio_id']) == '') {
                        $validate = 0;
                        $message = 'Please select Audio from picker for block ' . ($i + 1);
                        break;
                    }
                }
                //validation for video
                if ($frameBlockData[$i]['type'] == 'video') {
                    if (trim($frameBlockData[$i]['video_id']) == '') {
                        $validate = 0;
                        $message = 'Please select video from picker for block ' . ($i + 1);
                        break;
                    }
                }
                //validation for image
                if ($frameBlockData[$i]['type'] == 'image') {
                    if (trim($frameBlockData[$i]['image_id']) == '') {
                        $validate = 0;
                        $message = 'Please select image from picker for block ' . ($i + 1);
                        break;
                    }
                }
            } else {
                $validate = 0;
                $message = 'Please Select Type for block ' . ($i + 1);
                break;
            }
        }
        return array('status' => $validate, 'message' => $message, 'data' => $frameBlockData);
    }

    //the intermediate page where cards are inspected before creation
    //OR user presses 'save all' from that page, leading to saving each entity

    public function saveFrameBlockData($data, $lesson_frame_id)
    {
        $noOfblock = $data['number_of_block'];
        $i = 1;
        $frameBlockData = array();
        $this->getLessonFrameBlocksTable()->deleteAll(['lesson_frame_id' => $lesson_frame_id]);
        for ($i = 1; $i <= $noOfblock; $i++) {
//                      if (isset($data['block' . $i . 'id'])) {
//                              $LessonFrame = $this->getLessonFrameBlocksTable()->get($data['block' . $i . 'id']);
//                      } else {
//                              $LessonFrame = $this->getLessonFrameBlocksTable()->newEmptyEntity();
//                      }
            $LessonFrame = $this->getLessonFrameBlocksTable()->newEmptyEntity();
            $element = array();
            if ($data['block' . $i . 'type'] == 'html') {
                $element['custom_html'] = $data['custom_html' . $i];
                $element['type'] = 'html';
            }
            if ($data['block' . $i . 'type'] == 'audio') {
                $element['audio_id'] = $data['block_audio_id' . $i];
                $element['type'] = 'audio';
            }
            if ($data['block' . $i . 'type'] == 'image') {
                $element['image_id'] = $data['block_image_id' . $i];
                $element['type'] = 'image';
            }
            if ($data['block' . $i . 'type'] == 'video') {
                $element['video_id'] = $data['block_video_id' . $i];
                $element['type'] = 'video';
            }
            if ($data['block' . $i . 'type'] == 'card') {
                $element['card_id'] = $data['block_card_id' . $i];
                $element['is_card_lakota'] = (isset($data['is_card_lakota' . $i])
                    && ($data['is_card_lakota' . $i] == 'on')) ? 'Y' : 'N';
                $element['is_card_english'] = (isset($data['is_card_english' . $i])
                    && ($data['is_card_english' . $i] == 'on')) ? 'Y' : 'N';
                $element['is_card_audio'] = (isset($data['is_card_audio' . $i])
                    && ($data['is_card_audio' . $i] == 'on')) ? 'Y' : 'N';
                $element['is_card_video'] = (isset($data['is_card_video' . $i])
                    && ($data['is_card_video' . $i] == 'on')) ? 'Y' : 'N';
                $element['is_card_image'] = (isset($data['is_card_image' . $i])
                    && ($data['is_card_image' . $i] == 'on')) ? 'Y' : 'N';
                $element['type'] = 'card';
            }
            $element['lesson_frame_id'] = $lesson_frame_id;
            $element['block_no'] = $i;
            $LessonFrameBlockData = $this->getLessonFrameBlocksTable()->patchEntity($LessonFrame, $element);
            $this->getLessonFrameBlocksTable()->save($LessonFrameBlockData);
        }
        return true;
    }



//--------------------


    //function for sorting

    /**
     * Using this for frame deletion and update/addition because it's thorough
     * and multiple lesson frames or exercise could have the same card, so
     * in order to not make any assumptions (and just delete the cards related
     * to this newly deleted frame) we will check all lesson frames and
     * exercises and make sure the CardUnits table is up-to-date.
     */
    private function updateCardUnitsAccordingToLessonFrame($LessonFrame)
    {
        if ($LessonFrame == null) {
            Log::error('$LessonFrame is null.');
            return false;
        }
        if ($LessonFrame->lesson_id == null || !isset($LessonFrame->lesson_id)) {
            Log::error('$LessonFrame->lesson_id is null or not set.');
            return false;
        }

        /* Use LessonFrame to determine Lesson, find Units with that lesson,
             find CardUnits with that Unit, add cards from lesson frame if they don't exist already
             in that unit */
        /* Find unit details that contain the lesson with the frame being added/changed/deleted, to get list of units */
        $unitOptions = array(
            'conditions' => array(
                'lesson_id' => $LessonFrame->lesson_id
            ),
            'keyField' => 'id',
            'valueField' => 'unit_id'
        );
        $unitsWithThisLesson = $this->getUnitdetailsTable()->find('list', $unitOptions)->toArray();
        $unitsWithThisLesson = array_values(array_unique($unitsWithThisLesson));

        /* Use list of units to find unit details for those units */
        foreach ($unitsWithThisLesson as $unit_id) {
            $unitOptions = array(
                'conditions' => array(
                    'unit_id' => $unit_id
                ),
                'contain' => array(
                    'Lessons',
                    'Lessons.Lessonframes',
                    'Lessons.Lessonframes.LessonFrameBlocks',
                    'Exercises',
                    'Exercises.Exerciseoptions'
                )
            );
            $unitsDetails = $this->getUnitdetailsTable()->find('all', $unitOptions)->toArray();

            /* Get all the cards in the lesson being edited */
            $cardIds = array();
            foreach ($unitsDetails as $key => $unitActivity) {
                $this->addCardsFromActivityToCardUnits($unitActivity, $cardIds);
            }
            $cardIds = array_values(array_unique($cardIds));
            $unitOptions = array(
                'conditions' => array(
                    'unit_id' => $unit_id
                ),
                'keyField' => 'id',
                'valueField' => 'card_id'
            );
            $unitCardIds = $this->getCardUnitsTable()->find('list', $unitOptions)->toArray();
            $unitCardIds = array_values(array_unique($unitCardIds));

            /* Delete cards that are no longer in a lesson or exercise */
            if (!empty($cardIds)) {
                $this->getCardUnitsTable()->deleteAll(['unit_id' => $unit_id, 'card_id NOT IN' => $cardIds]);
            }

            /* Create array of new items to batch save to the database table */
            $newCards = array();
            foreach ($cardIds as $card_id) {
                if (!in_array($cardIds, $unitCardIds)) {
                    array_push($newCards, ['card_id' => $card_id, 'unit_id' => $unit_id]);
                }
            }
            /* Batch create entities from above array and save atomically to database table using
                 cakephp Saving Multiple Entities functionality since v3.2.8 */
            $entities = $this->getCardUnitsTable()->newEntities($newCards);
            $result = $this->getCardUnitsTable()->saveMany($entities);
        }
        return true;
    }

    public function uploadLessons()
    {
        $File = $this->getFilesTable()->newEmptyEntity();
        if ($this->request->is('post')) {
            if (!empty($this->request->getData()['file'])) {
                $clientFile = $this->request->getData()['file'];
                if (!empty($clientFile->getClientFilename())) {
                    if ($clientFile->getError() !== UPLOAD_ERR_OK) {
                        $this->Flash->error(__('Please upload an excel file less then 2 MB.'));
                        return $this->redirect(['action' => 'uploadLessons']);
                    }

                    //verify the type is excel
                    $typeFormat = explode("/", $clientFile->getClientMediaType());
                    $type = $typeFormat[0];
                    $format = $typeFormat[1];
                    $acceptedFormats = [
                        'vnd.ms-excel',
                        'vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'octet-stream',
                    ];
                    if ($type != 'application' || (!in_array($format, $acceptedFormats))) {
                        $this->Flash->error(__('Please upload an Excel (.xls or .xlsx) file.'));
                        return $this->redirect(['action' => 'uploadLessons']);
                    }

                    //Put the file into an Excel PHP object, then into an array
                    $clientFileTempPath = $clientFile->getStream()->getMetadata('uri');
                    $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($clientFileTempPath);

                    //generate lessons and all nested entities
                    $lessons = $this->makeLessonsFromExcelObj($objPHPExcel);

                    if (!$lessons) {
                        return $this->redirect(['action' => 'uploadLessons']);
                    } else {
                        return $this->batchVerifyOrSave($lessons);
                    }
                } else {
                    $type = 'application';
                    $format = 'vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                }
            } elseif (isset($this->request->getData()['savebtn']) || isset($this->request->getData()['cancelbtn'])) {
                return $this->batchVerifyOrSave();
            } else {
                $this->Flash->error(__('Somthing is wrong with the file size or the file itself. Please try again'));
            }
        }

        $this->set(compact('File'));
        $this->render('upload_lessons');
    }

    //for delete the lesson

    private function makeLessonsFromExcelObj($xl)
    {
        $lessons = [];

        $lessonNames = [];
        foreach ($xl->getWorksheetIterator() as $worksheet) {
            if ($worksheet->getTitle() != 'README') {
                $lessonNames[] = $worksheet->getTitle();
            }
        }

        $dupesWithin = $this->arrayHasDupes($lessonNames);
        if ($dupesWithin) {
            $this->Flash->error(__('Error in Workbook: Lesson names (Worksheet tabs) must all be unique.'));
            return false;
        }

        //runtime O(n) (first loop)
        foreach ($xl->getWorksheetIterator() as $worksheet) {
            //skip README tab in worksheet
            if ($worksheet->getTitle() != 'README') {
                //check if lesson name already exists in the db
                $dup = $this->checkDuplicateLesson($worksheet->getTitle());

                //create new lesson
                $lesson = $this->getLessonsTable()->newEmptyEntity();
                $lesson['name'] = $worksheet->getTitle();

                //no duplicates, make frames
                if (!$dup) {
                    $lessons[] = [
                        'lesson' => $lesson,
                        //frames will have blocks and cards nested within each frame
                        'frames' => $this->makeFramesFromSheetArray($worksheet->toArray())
                    ];

                    //duplicate was found, add error instead of making frames
                } else {
                    $lessons[] = [
                        'lesson' => $lesson,
                        //frames will have blocks and cards nested within each frame
                        'frames' => $this->makeFramesFromSheetArray($worksheet->toArray()),
                        'errors' => ['A lesson already exists with this name.']
                    ];
                }
            }
        }

        $row = 2; //start the row at 2, since sheet ordering starts at 1 and 1 is the header row
        foreach ($lessons as $lesson) {
            if (array_key_exists('errors', $lesson)) {
                foreach ($lesson['errors'] as $error) {
                    $this->Flash->error(__(
                        'Error on Lesson "' . $lesson['lesson']['name']
                        . '": ' . $error
                    ));
                    return false;
                }
            } else {
                foreach ($lesson['frames'] as $frame) {
                    if (array_key_exists('errors', $lesson['frames'])) {
                        foreach ($lesson['frames']['errors'] as $error) {
                            $this->Flash->error(__(
                                'Error on Lesson "' . $lesson['lesson']['name']
                                . '", Frame at row ' . $row . ': ' . $error
                            ));
                            return false;
                        }
                    } else {
                        foreach ($frame['blocks'] as $block) {
                            if (array_key_exists('errors', $block)) {
                                foreach ($block['errors'] as $err) {
                                    $this->Flash->error(__(
                                        'Error on Lesson "' . $lesson['lesson']['name']
                                        . '", Block at row ' . $row . ': ' . $err
                                    ));
                                    return false;
                                }
                            } elseif (array_key_exists('card', $block)) {
                                if ($block['card'] && array_key_exists('errors', $block['card'])) {
                                    foreach ($block['card']['errors'] as $error) {
                                        $this->Flash->error(__(
                                            'Error on Lesson "'
                                            . $lesson['lesson']['name']
                                            . '", Card at row ' . $row . ': '
                                            . $error
                                        ));
                                        return false;
                                    }
                                }
                            }
                            $row++;
                        }
                    }
                }
            }
            //reset the row when iterating to next 'tab'(lesson)
            $row = 2;
        }
        //$this->printDie($lessons);
        return $lessons;
    }

    //for delete the lesson frame

    private function arrayHasDupes($array)
    {
        return count($array) !== count(array_unique($array));
    }

    private function checkDuplicateLesson($name)
    {
        $dups = $this->getLessonsTable()->find()
            ->where(['name' => $name])
            ->toArray();

        if (!empty($dups)) {
            return $dups[0];
        }

        return false;
    }

    //function for sorting when delete.

    private function makeFramesFromSheetArray($arr)
    {
        /*
        lesson_id
        name: Frame X
        number_of_block
        frame_preview: portrait/landscape
        frameorder
        audio_id ------------------- need to sort out being able to add audio to a frame
        */
        $frames = [];
        $currentFrameIdx = -1;
        $numBlocks = 0;
        $errors = [];


        //var_dump($arr);
        //skip the header row
        //loop through the worksheet
        for ($row = 1; $row < count($arr); $row++) {
            $numBlocks = 0;
            /*

                CREATE NEW FRAME

            */
            //this row contains new frame (& block)
            if ($arr[$row][0] != null) {
                //var_dump('frame at row'.($row+1));
                $orientation = strtolower($arr[$row][0]);
                if ($orientation == 'portrait' || $orientation == 'landscape') {
                    //update the current frame index
                    $currentFrameIdx++;
                    //reset the block count
                    $numBlocks = 0;
                    //make a new Frame object and set its orientation
                    $frame = $this->getLessonFramesTable()->newEmptyEntity();
                    $frame['frame_preview'] = $orientation;

                    $audioId = $this->findFileId($arr[$row][1], 'audio');
                    if ($audioId) {
                        $frame['audio_id'] = $audioId;
                    }

                    //make a new entry for this in the frames array
                    $frames[$currentFrameIdx] = [
                        'frame' => $frame,
                        'blocks' => [$this->makeBlockFromRowData($arr[$row])]
                    ];
                    $numBlocks++;
                } else {
                    $errors[] = 'Orientation ' . $orientation . ' does not exist at row ' . ($row + 1);
                }
                //this row contains a new block only
            } elseif ($arr[$row][2] != null) {
                $frames[$currentFrameIdx]['blocks'][] = $this->makeBlockFromRowData($arr[$row]);
                $numBlocks++;
                if ($numBlocks > 3) {
                    $errors[] = 'Frame cannot have more than 3 blocks at row ' . ($row + 1);
                }
            }
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return $this->enumerateFramesAndBlocks($frames);
    }

    //function for sorting when delete.

    private function findFileId($filename, $type)
    {
        $file = $this->getFilesTable()->find()
            ->where([
                'file_name' => $filename,
                'type' => $type
            ])
            ->toArray();
        if (!empty($file)) {
            return $file[0]->id;
        } else {
            return false;
        }
    }


    // general function for save the frame

    private function makeBlockFromRowData($row)
    {
        //there is a block on every row
        /*
        lesson_frame_id
        card_id
        audio_id
        image_id
        video_id
        block_no (block order in this frame)
        type
        is_card_lakota
        is_card_english
        is_card_audio
        is_card_video
        is_card_image
        custom_html
        */

        $errors = [];

        //create new block
        $block = $this->getLessonFrameBlocksTable()->newEmptyEntity();
        $blockType = strtolower($row[2]);
        //make a card for this block since it was specified
        if ($blockType == 'card') {
            $block['type'] = $blockType;
            $block['is_card_lakota'] = $row[13] ? 'Y' : 'N';
            $block['is_card_english'] = $row[14] ? 'Y' : 'N';
            $block['is_card_audio'] = $row[15] ? 'Y' : 'N';
            $block['is_card_image'] = $row[16] ? 'Y' : 'N';
            $block['is_card_video'] = $row[17] ? 'Y' : 'N';

            return [
                'block' => $block,
                'card' => $this->makeCardFromRowData($row)
            ];

            //not a card type block
        } else {
            $block['type'] = $blockType;
            $block['is_card_lakota'] = 'N';
            $block['is_card_english'] = 'N';
            $block['is_card_audio'] = 'N';
            $block['is_card_image'] = 'N';
            $block['is_card_video'] = 'N';

            if ($blockType == 'html') {
                if (!$row[12]) {
                    $errors[] = 'HTML block type must have HTML content.';
                    return ['errors' => $errors];
                }
                $block['custom_html'] = $row[12];
                return [
                    'block' => $block,
                    'card' => null
                ];
            } elseif ($blockType == 'audio') {
                $fileId = $this->findFileId($row[9], 'audio');
                if (!$fileId) {
                    $errors[] = 'Cannot find audio file "' . $row[9] . '"';
                    return ['errors' => $errors];
                }
                $block['audio_id'] = $fileId;
                return [
                    'block' => $block,
                    'card' => null
                ];
            } elseif ($blockType == 'image') {
                $fileId = $this->findFileId($row[10], 'image');
                if (!$fileId) {
                    $errors[] = 'Cannot find image file "' . $row[10] . '"';
                    return ['errors' => $errors];
                }
                $block['image_id'] = $fileId;
                return [
                    'block' => $block,
                    'card' => null
                ];
            } elseif ($blockType == 'video') {
                $fileId = $this->findFileId($row[11], 'video');
                if (!$fileId) {
                    $errors[] = 'Cannot find video file "' . $row[11] . '"';
                    return ['errors' => $errors];
                }
                $block['video_id'] = $fileId;
                return [
                    'block' => $block,
                    'card' => null
                ];
            } else {
                $errors[] = $blockType . ' is not card, html, audio, image, or video';
                return ['errors' => $errors];
            }
        }
    }

    // general function for validation the frame data

    private function makeCardFromRowData($row)
    {

        $errors = [];
        $card = $this->getCardTable()->newEmptyEntity();

        $typeId = $this->findTypeId($row[3]);
        if ($typeId) {
            $card['card_type_id'] = $typeId;
        } else {
            $errors[] = 'Could not find card type id of type ' . $row[3];
        }

        $genders = ['default', 'male', 'female'];
        if (!in_array($row[6], $genders)) {
            $errors[] = 'Gender must be specified as "' . implode('" or "', $genders) . '"';
        } else {
            $card['gender'] = $row[6];
        }

        $card['lakota'] = $row[4];
        $card['english'] = $row[5];

        //if we find a duplicate, use that one instead
        $duplicate = $this->checkDuplicateCard($card);
        if ($duplicate) {
            return $duplicate;
        }

        //if we didnt find a duplicate, get all the other info we need to make a new card
        $card['alt_lakota'] = $row[7];
        $card['alt_english'] = $row[8];
        $card['include_review'] = ($row[18] == 'n' || $row[18] == 'N') ? 0 : 1;

        //find files
        $audioId = $imageId = $videoId = null;

        if ($row[9]) { //audio
            $audioId = $this->findFileId($row[9], 'audio');
            if ($audioId === false) {
                $errors[] = 'Could not find audio file "' . $row[9] . '".';
            }
            $card['audio'] = $audioId;
        }
        if ($row[10]) { //image
            $imageId = $this->findFileId($row[10], 'image');
            if ($imageId === false) {
                $errors[] = 'Could not find image file "' . $row[10] . '".';
            }
            $card['image_id'] = $imageId;
        }
        if ($row[11]) { //video
            $videoId = $this->findFileId($row[11], 'video');
            if ($videoId === false) {
                $errors[] = 'Could not find video file "' . $row[11] . '".';
            }
            $card['video_id'] = $videoId;
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return $card;
    }

    // ajax function for preview the frame data

    private function findTypeId($type)
    {
        $type = $this->getCardtypeTable()->find()->where(['title' => $type])->toArray();
        if (!empty($type)) {
            return $type[0]->id;
        } else {
            return false;
        }
    }

    //ajax builk user action

    private function checkDuplicateCard($card)
    {
        $dups = $this->getCardTable()->find()->where([
            'card_type_id' => $card['card_type_id'],
            'lakota =' => $card['lakota'],
            'english =' => $card['english'],
            'gender =' => $card['gender']
            // 'alt_lakota =' => $card->alt_lakota,
            // 'alt_english =' => $card->alt_english,
            // 'image_id' => $card->image_id,
            // 'video_id is' => $card->video_id,
            // 'audio =' => $card->audio,
        ])->toArray();

        if (!empty($dups)) {
            return $dups[0];
        }

        return false;
    }


    /*

        CARD from SPREADSHEET ROW

    */

    private function enumerateFramesAndBlocks($frames)
    {
        $frameId = $blockId = 1;
        foreach ($frames as $frame) {
            $frame['frame']['frameorder'] = $frameId;
            $frame['frame']['name'] = 'Frame ' . $frameId;
            $frameId++;

            foreach ($frame['blocks'] as $block) {
                $block['block_no'] = $blockId;
                $blockId++;
            }
            $frame['frame']['number_of_block'] = $blockId - 1;
            //reset blockId after looping through current blocks
            $blockId = 1;
        }

        return $frames;
    }


    /*

        BLOCK from SPREADSHEET ROW

    */

    public function batchVerifyOrSave($data = false)
    {
        //data passed from upload function, display them for inspection
        if ($data) {
            $this->set(compact('data'));
            return $this->render('batch_verify');
        }

        //save or cancel pressed
        if ($this->request->is('post')) {
            //save pressed, make the cards
            if (
                isset($this->request->getData()['savebtn'])
                && array_key_exists('datastring', $this->request->getData())
            ) {
                //decode the data string into an array
                $data = json_decode($this->request->getData()['datastring'], true);
                $counter = [
                    'lessons' => 0,
                    'frames' => 0,
                    'blocks' => 0,
                    'cards' => 0,
                    'reusedCards' => 0
                ];
                $firstLessonId = null;
                //loop through the lessons
                for ($lid = 0; $lid < count($data); $lid++) {
                    $lesson = $lesson = $this->getLessonsTable()->newEntity($data[$lid]['lesson']);
                    //save lesson
                    $this->getLessonsTable()->save($lesson);
                    $counter['lessons']++;

                    //get the ID of the first lesson, which will show the user in a redirect
                    if ($firstLessonId === null) {
                        $firstLessonId = $lesson->id;
                    }

                    //loop through the frames
                    for ($fid = 0; $fid < count($data[$lid]['frames']); $fid++) {
                        //put the lesson id in before we generate an entity, so no early headers
                        $data[$lid]['frames'][$fid]['frame']['lesson_id'] = $lesson->id;
                        $frame = $this->getLessonFramesTable()->newEntity($data[$lid]['frames'][$fid]['frame']);
                        $this->getLessonFramesTable()->save($frame);
                        $counter['frames']++;

                        //loop through the blocks
                        $blockNumber = 1;
                        for ($bid = 0; $bid < count($data[$lid]['frames'][$fid]['blocks']); $bid++) {
                            $block = $this->getLessonFrameBlocksTable()
                                ->newEntity($data[$lid]['frames'][$fid]['blocks'][$bid]['block']);

                            $card = false;
                            $cardData = $data[$lid]['frames'][$fid]['blocks'][$bid]['card'];
                            //if weve got card data
                            if (!empty($cardData)) {
                                if (array_key_exists('id', $cardData) && $cardData['id'] > 0) {
                                    //card was listed as a duplicate, even going into the verification page
                                    $card = $this->getCardTable()->findById($cardData['id']);
                                    if (!$card) {
                                        $this->Flash->error(__(
                                            'Error: Could not find card with ID '
                                            . $cardData['id']
                                        ));
                                    }
                                } else {
                                    //check once more to make sure this card doesnt already exist
                                    $dupCard = $this->checkDuplicateCard($cardData);
                                    //card wasnt originally listed as a duplicate, but we just checked
                                    //and we found that now it is a duplicate,
                                    //because a duplicate was uploaded in this batch
                                    if ($dupCard) {
                                        $card = $dupCard;
                                        //card is definitely not a duplicate. save it as a new card
                                    } else {
                                        $card = $this->getCardTable()->newEntity($cardData);
                                    }
                                }
                            }

                            if ($card) {
                                $existedAlready = $card->id ? true : false;
                                //save card
                                $this->getCardTable()->save($card);
                                if (!$existedAlready) {
                                    $counter['cards']++;
                                } else {
                                    $counter['reusedCards']++;
                                }

                                $block->card_id = $card->id;
                            }
                            $block->lesson_frame_id = $frame->id;
                            $numBlocks = $blockNumber;
                            $block->block_no = $blockNumber++;

                            //save block
                            $this->getLessonFrameBlocksTable()->save($block);
                            $counter['blocks']++;
                        }
                    }
                } //end lessons loop

                //everything should be saved now
                $this->Flash->success(
                    $counter['lessons'] . ' Lessons, ' .
                    $counter['frames'] . ' Frames, ' .
                    $counter['blocks'] . ' Blocks, ' .
                    $counter['cards'] . ' Cards created successfully; ' .
                    $counter['reusedCards'] . ' Cards reused'
                );
                return $this->redirect(['action' => 'manageLesson', $firstLessonId]);

                //cancel pressed, return to upload form
            } elseif (isset($this->request->getData()['cancelbtn'])) {
                $this->Flash->success(__('Bulk upload was aborted.'));
                return $this->redirect(['action' => 'uploadLessons']);
            }
        }
        //if they got this far theres something wrong
        $this->Flash->error(__('Error: No Lessons sent.	Please specify a Lessons spreadsheet to verify.'));
        return $this->redirect(['action' => 'uploadLessons']);
    }


    /*

        FRAMES from WORKSHEET

    */

    /*

        LESSONS from WORKBOOK

    */

    public function sorting($frameid = null, $status = null, $currentSort = null)
    {
        if ($status == 'up') {
            $newsort = $currentSort - 1;
        } else {
            $newsort = $currentSort + 1;
        }

        // entity that user actively moved
        $frame = $this->getLessonFramesTable()->get($frameid);
        $frameorder = $frame->frameorder;
        $lession_id = $frame->lesson_id;

        // get entity that was passively moved due to active move of above entity
        $replaseFramesFlag = $this->getLessonFramesTable()
            ->find()
            ->where([
                'lesson_id' => $lession_id,
                'frameorder' => $newsort,
                'id !=' => $frame->id])
            ->first()
            ->toArray();

        // update passively moved entity;
        $rframe = $this->getLessonFramesTable()->get($replaseFramesFlag['id']);
        $rframe->frameorder = $currentSort;
        $this->getLessonFramesTable()->save($rframe);

        //update    actively moved entity
        $frame->frameorder = $newsort;
        $this->getLessonFramesTable()->save($frame);
        $this->renameFrames($lession_id);
        return $this->redirect($this->referer());
    }

    public function renameFrames($lessonId)
    {
        $frames = $this->getLessonFramesTable()
            ->find()
            ->where(['lesson_id' => $lessonId])
            ->order(['frameorder', 'modified DESC'])
            ->toArray();
        $i = 1;
        $name_prefix = '';
        // Rename frames to have consistent numbering
        foreach ($frames as $f) {
            // Rename frame to the prefix plus the number allowing a consistent ordering
            $f->name = FRAME_PREFIX . $i;
            // Save it to the database
            $this->getLessonFramesTable()->save($f);
            $i++;
        }
        return true;
    }

    /**
     * Reorders the frames in the current lesson according to the array passed via a POST request.
     */
    public function reorderAndRenameFrames()
    {
        // Get POST data (the IDs array)
        $idArray = $this->request->getData();

        // Get current lesson for which user is adjusting the frames
        $lessonId = $this->getLessonFramesTable()->get((int)$idArray[0])->lesson_id;

        for ($i = 0; $i < count($idArray); $i++) {
            // Get entity that was passively moved due to active move of above entity
            $tempFrame = $this->getLessonFramesTable()
                ->find()
                ->where(['lesson_id' => $lessonId, 'id' => (int)$idArray[$i]])
                ->first();
            $tempFrame->frameorder = $i + 1;
            $tempFrame->name = FRAME_PREFIX . ($i + 1);
            $this->getLessonFramesTable()->save($tempFrame);
        }
        // make this appear immediately
        return $this->redirect($this->referer());
    }


    //takes in array from excel spreadsheet and creates cards out of it.

    public function deletelesson($lessonid = null)
    {

        $Lessons = $this->getLessonsTable()->get($lessonid);
        if ($this->getLessonsTable()->delete($Lessons)) {
            $this->Flash->success(__('The Lesson has been deleted.'));
        } else {
            $this->Flash->error(__('The Lesson could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'manageLesson']);
    }

    public function deleteframe($frameid = null)
    {
        $LessonFrame = $this->getLessonFramesTable()->get($frameid);
        $frameIndex = $LessonFrame->frameorder;
        $lessonId = $LessonFrame->lesson_id;

        /* Finally delete the lesson frame */
        if ($this->getLessonFramesTable()->delete($LessonFrame)) {
            $this->resorting($lessonId);
            $this->renameFrames($lessonId);
            $this->Flash->success(__('The Lesson Frame has been deleted.'));
        } else {
            $this->Flash->error(__('The Lesson Frame could not be deleted. Please, try again.'));
        }

        /* Delete relevant cards from CardUnits table */
        if ($this->updateCardUnitsAccordingToLessonFrame($LessonFrame) == false) {
            $this->Flash->error(__(
                'CardUnits table failed to update. Invalid LessonFrame or LessonId. '
                . 'Please check error log for more details.'
            ));
        }

        return $this->redirect(['action' => 'manageLesson', $lessonId]);
        //return $this->redirect($this->referer());
    }

    public function resorting($lessonId)
    {
        $Frames = $this->getLessonFramesTable()
            ->find()
            ->where(['lesson_id' => $lessonId])
            ->order(['frameorder', 'modified DESC'])
            ->toArray();
        $i = 1;
        foreach ($Frames as $f) {
            $rframe = $this->getLessonFramesTable()->get($f['id']);
            $rframe->frameorder = $i;
            $this->getLessonFramesTable()->save($rframe);
            $i++;
        }
        return true;
    }

    //format the error so the user can find and fix the mistake in the excel file

    public function previewBlock()
    {
        $data = $_POST;
        $validatedata = $this->processAndValidateBlockData($data);
        if ($validatedata['status'] == 1) {
            if ($data['frame_preview'] == 'landscape') {
                $part = 4;
            }
            if ($data['frame_preview'] == 'portrait') {
                $part = 12;
            }

            if (!empty($data['audio_id'])) {
                $mainaudio = $this->getFilesTable()->get($data['audio_id'], ['contain' => []]); ?>
                <div class="vol-symbol">
                    <div class="vol-symbol-pos">
                        <i class="fa fa-volume-up previewaudio playaudio pull-right" data-id="main"></i>
                        <audio controls id="previewmain" style="display:none;">
                            <source src="<?php echo $mainaudio->FullUrl; ?>" type="audio/ogg">
                            <source src="<?php echo $mainaudio->FullUrl; ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                   </div>
               </div>
            <?php } ?>
            <div class="row">
                <?php $i = 0; foreach ($validatedata['data'] as $blockdata) { ?>
                    <div class="col-sm-<?php echo $part; ?>">
                        <div class="blockdiv">
                            <?php if ($blockdata['type'] == 'html') {
                                echo html_entity_decode($blockdata['custom_html']);
                            }

                            if (
                                $blockdata['type'] == 'audio'
                                || $blockdata['type'] == 'image' || $blockdata['type'] == 'video'
                            ) {
                                if ($blockdata['type'] == 'audio' && !empty($blockdata['audio_id'])) {
                                    $fileId = $blockdata['audio_id'];
                                }
                                if ($blockdata['type'] == 'image' && !empty($blockdata['image_id'])) {
                                    $fileId = $blockdata['image_id'];
                                }
                                if ($blockdata['type'] == 'video' && !empty($blockdata['video_id'])) {
                                    $fileId = $blockdata['video_id'];
                                }
                                $File = $this->getFilesTable()->get($fileId, ['contain' => []]);
                            }
                            if (isset($File)) {
                                if ($blockdata['type'] == 'image') { ?>
                                    <div class="pop-img"><img src="<?php echo $File->FullUrl; ?>" width="100%">
                                    </div>
                                    <?php
                                }
                                if ($blockdata['type'] == 'audio') { ?>
                                    <div class="show-audio">
                                        <i class="fa fa-volume-up previewaudio playaudio"
                                            data-id="<?php echo $i ?>"></i>
                                        <audio controls id="preview<?php echo $i ?>" style="display:none;">
                                            <source src="<?php echo $File->FullUrl; ?>" type="audio/ogg">
                                            <source src="<?php echo $File->FullUrl; ?>" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                    </div>
                                    <?php
                                }
                                if ($blockdata['type'] == 'video') { ?>
                                    <video width="100%" controls>
                                        <source src="<?php echo $File->FullUrl; ?>" type="video/mp4">
                                        <source src="<?php echo $File->FullUrl; ?>" type="video/ogg">
                                        Your browser does not support the video tag.
                                    </video>
                                    <?php
                                }
                            }

                            if ($blockdata['type'] == 'card' && !empty($blockdata['card_id'])) {
                                $Card = $this->getCardTable()->get($blockdata['card_id'], ['contain' => ['image', 'video']]);
                            }
                            if ($blockdata['type'] == 'card' && isset($Card)) { ?>
                                <div class="popbox-detail">
                                <?php
                                if ($blockdata['is_card_lakota'] == 'Y') {
                                    echo $Card->lakota;
                                } ?>

                                </div>
                                <div class="show-audio">
                                    <?php
                                    if (
                                        $blockdata['is_card_audio'] == 'Y'
                                        && !empty($Card->FullAudioUrl)
                                    ) { ?>
                                        <i class="fa fa-volume-up previewaudio playaudio"
                                            data-id="<?php echo $i ?>"></i>
                                        <!--<i class="fa fa-stop previewaudio stopaudio"
                                            data-id="<?php echo $i ?>"></i>-->
                                        <audio controls id="preview<?php echo $i ?>" style="display:none;">
                                            <source src="<?php echo $Card->FullAudioUrl; ?>" type="audio/ogg">
                                            <source src="<?php echo $Card->FullAudioUrl; ?>" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                        <?php
                                    } ?>
                                    </div>
                                    <div>
                                    <?php
                                    if (
                                        $blockdata['is_card_video'] == 'Y'
                                        && !empty($Card->video->FullUrl)
                                    ) { ?>
                                        <video width="100%" controls>
                                            <source src="<?php echo $Card->video->FullUrl; ?>" type="video/mp4">
                                            <source src="<?php echo $Card->video->FullUrl; ?>" type="video/ogg">
                                            Your browser does not support the video tag.
                                        </video>
                                        <?php
                                    } ?>
                                    </div>
                                    <div class="pop-img">
                                    <?php
                                    if (
                                        $blockdata['is_card_image'] == 'Y'
                                        && !empty($Card->image->FullUrl)
                                    ) { ?>
                                        <img src="<?php echo $Card->image->FullUrl; ?>">
                                        <?php
                                    } ?>
                                    </div>
                                    <div class="popbox-detail"><?php
                                    if ($blockdata['is_card_english'] == 'Y') {
                                        echo $Card->english;
                                    }
                                    ?></div>
                                <?php
                            }
                            ?></div>
                       </div>
                    <?php
                    $i++;
                }
                ?>
              </div>
            <?php
        } else { ?>
            <div class="row">
                <?php foreach ($validatedata['data'] as $blockdata) { ?>
                    <div class="col-sm-12"><?php echo $validatedata['message'] ?></div>
                <?php } ?>
            </div>
            <?php
        }
        die;
    }

    //find the ID of a card type, given its title

    public function lessonDeleteWarning()
    {
        $data = $_POST;
        $lessionId = $data['lessonId'];
        $lessonCount = $this->getUnitdetailsTable()
            ->find('all', ['conditions' => array('lesson_id' => $lessionId)])
            ->count();
        if ($lessonCount != 0) {
            $paths = $this->getUnitdetailsTable()
                ->find('all', ['conditions' => array('lesson_id' => $lessionId)])
                ->toArray();
            $linkIds = array();
            foreach ($paths as $p) {
                $element = array();
                $element['learningPathId'] = $p['learningpath_id'];
                $element['unitId'] = $p['unit_id'];

                $LevelFlag = $this->getLevelUnitsTable()
                    ->find('all', [
                        'conditions' => [
                            'learningpath_id' => $p['learningpath_id'],
                            'unit_id' => $p['unit_id']
                        ]])
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
                    This lesson are associated with Some Learning path.
                    Please change the lesson in path and try to delete again.
                </div>
                <div class='col-sm-6 col-md-6'>
                    <h3>Path List</h3>
                    <?php
                    foreach ($linkIds as $l) { ?>
                        <div>
                            <a href="<?php echo Router::url('/admin/learning-path/manage-paths/')
                                . $l['learningPathId'] . '/' . $l['levelId'] . '/' . $l['unitId']; ?>"
                                target="_blank"><i class="fa fa-pencil"></i> <?php echo $l['name']; ?>
                            </a>
                        </div>
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

    //find the ID of a File given a filename and valid formats

    private function err($rowNumber, $value)
    {
        return ['row' => $rowNumber, 'value' => $value];
    }

    //pretty print a thing and die

    private function printDie($thing)
    {
        echo '<br/><pre>';
        var_dump($thing);
        die();
    }
}
