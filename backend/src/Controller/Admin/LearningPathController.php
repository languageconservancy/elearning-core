<?php

namespace App\Controller\Admin;

use App\Lib\UtilLibrary;
use Cake\Event\EventInterface;
use Cake\Routing\Router;
use Cake\Core\Configure;
use Cake\Log\Log;

class LearningPathController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
    }

    public function index()
    {
        return $this->redirect(['action' => 'managePaths']);
    }

    public function managePaths($path_id = null, $level_id = null, $unit_id = null)
    {
        $path = $path_id ? $this->getLearningpathsTable()
            ->get($path_id, ['contain' => ['image']]) : $this->getLearningpathsTable()->newEmptyEntity();
        $level = $level_id
            ? $this->getLevelsTable()->get($level_id, ['contain' => ['image', 'Units' => ['sort' => 'sequence']]])
            : $this->getLevelsTable()->newEmptyEntity();
        $unit = $unit_id ? $this->getUnitsTable()->get($unit_id) : $this->getUnitsTable()->newEmptyEntity();
        $unitDataOptions = array(
            'conditions' => (!empty($unit_id) ? array('unit_id' => $unit_id) : array('unit_id IS NULL')),
            'order' => 'Unitdetails.sequence',
            'contain' => array('Lessons', 'Exercises')
        );
        $unitDataSet = $this->getUnitdetailsTable()->find('all', $unitDataOptions)->toArray();

        foreach ($unitDataSet as $key => $value) {
            if ($value->lesson) {
                $unitDataSet[$key]->type = 'lesson';
            } else {
                $unitDataSet[$key]->type = 'exercise';
            }
        }

        $allPaths = $this->getLearningpathsTable()->find('list', array('keyField' => 'id', 'valueField' => 'label'))->toArray();
        $allLevels = $path->id ? $this->getLevelsInPath($path->id) : array();
        $allUnits = $level->id ? $level->units : array();
        $setTypeVals = array('lesson' => 'Lessons', 'exercise' => 'Exercises');
        $set_type = array('value' => 'lesson', 'label' => 'Lessons');
        $unitDetails = $this->getLessonsTable()->find();

        // if (count($allLevels) > 0 && $level_id == null) {
        //   $keys = array_keys($allLevels);
        //   $level_id = $keys[0];
        //   return $this->redirect(['action' => 'managePaths', $path->id, $keys[0]]);
        // }

        if ($this->request->is(['POST', 'PUT', 'PATCH'])) {
            $data = $this->request->getData();


            switch ($data['form_type']) {
                case 'path':
                    $this->addPath($data, $path, $level);
                    break;
                case 'level':
                    $this->addLevel($data, $path, $level);
                    break;
                case 'unit':
                    $saveUnit = $this->addUnit($data, $path, $level, $unit);
                    break;
                default:
                    break;
            }
        }

        $this->set(compact(
            'path',
            'allPaths',
            'level',
            'allLevels',
            'unit',
            'allUnits',
            'set_type',
            'setTypeVals',
            'unitDetails',
            'unitDataSet'
        ));
        $this->viewBuilder()->setOption('serialize', [
            'path',
            'allPaths',
            'level',
            'allLevels',
            'unit',
            'allUnits',
            'set_type',
            'setTypeVals',
            'unitDetails',
            'unitDataSet'
        ]);
        $this->render('manage_paths');
    }

    public function getLevelsInPath($pathId)
    {
        $path = $this->getLearningpathsTable()->get(
            $pathId,
            [
                'contain' => [
                    'image',
                    'Levels' => [
                        'sort' => 'Pathlevel.sequence'
                    ],
                    'Levels.image'
                ]
            ]
        );
        $levelArray = array();
        foreach ($path->levels as $level) {
            $levelArray[] = [
                'id' => $level->id,
                'name' => $level->name
            ];
        }
        return $levelArray;
    }

    public function addPath($data, $path)
    {
        $pathData = $this->getLearningpathsTable()->patchEntity($path, $data);
        if ($pathSave = $this->getLearningpathsTable()->save($pathData)) {
            $this->Flash->success(__('Path saved successfully.'));
            $pathIDss = $pathSave->id;
            header('Location: ' . Router::url('/admin/learning-path/manage-paths') . '/' . $pathIDss);
            die;
        } else {
            $errors = array_values(array_values($pathData->getErrors()));
            foreach ($errors as $key => $err) {
                foreach ($err as $key1 => $err1) {
                    $this->Flash->error($err1);
                }
            }
        }
    }

    public function addLevel($data, $path, $level)
    {
        $levelData = $this->getLevelsTable()->patchEntity($level, $data);
        $levelID = $level->id ? $level->id : null;
        if ($level = $this->getLevelsTable()->save($levelData)) {
            $forumdata = array('level_id' => $level->id, 'path_id' => $path->id, 'title' => 'Lessons by Unit');
            $this->addForum($forumdata);
            if ($levelID == null) {
                // Figure out sequence number
                $lastPathLevel = $this->getPathlevelTable()->find()
                    ->where(['learningpath_id' => $path->id])
                    ->order(['sequence ASC'])
                    ->all()
                    ->last();
                $pathLevel = $this->getPathlevelTable()->newEmptyEntity();
                $pathLevelData = array(
                    'learningpath_id' => $data['learningpath_id'],
                    'level_id' => $level->id,
                    'sequence' => ($lastPathLevel->sequence ?? 0) + 1
                );
                $pathLevel = $this->getPathlevelTable()->patchEntity($pathLevel, $pathLevelData);
                if ($pathLevel = $this->getPathlevelTable()->save($pathLevel)) {
                    $this->Flash->success(__('Learning path saved successfully.'));
                }
            }
            //return $this->redirect(['action' => 'managePaths', $path->id, $level->id]);
            header('Location: ' . Router::url('/admin/learning-path/manage-paths')
                . '/' . $path->id . '/' . $level->id);
            die;
        }
        $errors = array_values(array_values($levelData->getErrors()));
        foreach ($errors as $key => $err) {
            foreach ($err as $key1 => $err1) {
                $this->Flash->error($err1);
            }
        }
    }

    private function addForum($data)
    {
        $forumdata = array();
        if (isset($data['path_id'])) {
            $forumdata['path_id'] = $data['path_id'];
        }
        if (isset($data['level_id'])) {
            $forumdata['level_id'] = $data['level_id'];
        }
        if (isset($data['unit_id'])) {
            $forumdata['unit_id'] = $data['unit_id'];
        }
        $forumdata['title'] = $data['title'];
        $forumdata['subtitle'] = $data['title'];
        $count = $this->getForumsTable()->find('all', ['conditions' => $forumdata])->count();
        if ($count == 0) {
            $forum = $this->getForumsTable()->newEmptyEntity();
            $forumdataMOdel = $this->getForumsTable()->patchEntity($forum, $forumdata);
            $this->getForumsTable()->save($forumdataMOdel);
        }
        return true;
    }

    public function addUnit($data, $path, $level, $unit)
    {
        $maxSequence = $this->getLevelUnitsTable()
            ->find('all', [
                'conditions' => ['level_id' => $data['level_id']],
                'fields' => ['maxSequence' => 'MAX(sequence)']])
            ->toArray();
        $unitID = $unit->id ? $unit->id : null;
        $unitData = $this->getUnitsTable()->patchEntity($unit, $data);
        if ($unit = $this->getUnitsTable()->save($unitData)) {
            $forumdata = array(
                'level_id' => $level->id, 'unit_id' => $unit->id,
                'path_id' => $path->id, 'title' => $data['name']);
            $this->addForum($forumdata);
            if ($unitID == null) {
                $unitLevel = $this->getLevelUnitsTable()->newEmptyEntity();
                $unitLevelData = array(
                    'learningpath_id' => $data['learningpath_id'],
                    'level_id' => $data['level_id'],
                    'unit_id' => $unit->id,
                    'sequence' => $maxSequence[0]['maxSequence'] + 1
                );

                $unitLevel = $this->getLevelUnitsTable()->patchEntity($unitLevel, $unitLevelData);
                if ($unitLevel = $this->getLevelUnitsTable()->save($unitLevel)) {
                    $this->Flash->success(__('Unit saved successfully.'));
                }
            }
            return $this->redirect(['action' => 'managePaths', $path->id, $level->id, $unit->id]);
        }

        $errors = array_values(array_values($unitData->getErrors()));
        foreach ($errors as $key => $err) {
            foreach ($err as $key1 => $err1) {
                $this->Flash->error($err1);
            }
        }
    }

    public function deletePath($path_id = null)
    {

        $users = $this->getUsersTable()->find('list', ['conditions' => ['learningpath_id' => $path_id]])->toArray();
        $users = array_keys($users);
        $data = array();
        foreach ($users as $u) {
            $udata = $this->getUsersTable()->get($u);
            $udata->learningpath_id = 1;
            $this->getUsersTable()->save($udata);
        }
        $path = $this->getLearningpathsTable()->get($path_id);
        $levels = $this->getPathlevelTable()->find('all', ['conditions' => ['learningpath_id' => $path_id]]);
        foreach ($levels as $key => $levelPath) {
            $this->deleteAllLevelData($levelPath->level_id);
        }
        $this->getPathlevelTable()->deleteAll(['learningpath_id' => $path_id]);
        $this->getUserprogressTable()->deleteAll(['learningpath_id' => $path_id]);
        $this->getLearningpathsTable()->delete($path);

        return $this->redirect(['action' => 'managePaths']);
    }

    private function deleteAllLevelData($levelId = null)
    {
        $levelUnits = $this->getLevelUnitsTable()->find('all', ['conditions' => ['level_id' => $levelId]]);
        $level = $this->getLevelsTable()->get($levelId);

        foreach ($levelUnits as $key => $levelUnit) {
            $this->deleteAllUnitData($levelUnit->unit_id);
        }

        $this->getPathlevelTable()->deleteAll(['level_id' => $levelId]);
        $this->getLevelUnitsTable()->deleteAll(['level_id' => $levelId]);
        $this->getUserprogressTable()->deleteAll(['level_id' => $levelId]);
        $this->getLevelsTable()->delete($level);
    }

    private function deleteAllUnitData($unit_id)
    {
        $levelUnit = $this->getLevelUnitsTable()->find('all', ['conditions' => ['unit_id' => $unit_id]])->first();
        $unit = $this->getUnitsTable()->get($unit_id);

        $this->getLevelUnitsTable()->delete($levelUnit);
        $this->getUserprogressTable()->deleteAll(['unit_id' => $unit_id]);
        $this->getUnitdetailsTable()->deleteAll(['unit_id' => $unit_id]);
        $this->getUnitsTable()->delete($unit);
        /* Delete cards related to that unit in the CardUnits table since the unit doesn't exist    */
        $this->getCardUnitsTable()->deleteAll(['unit_id' => $unit_id]);
    }

    public function deleteLevel(int $pathId = null, int $levelId = null)
    {
        $this->deleteAllLevelData($levelId);
        $this->getPathlevelTable()->resequence($pathId);
        return $this->redirect(['action' => 'managePaths', $pathId]);
    }

    public function makeOptional($unit_id = null, $status = null)
    {
        if ($status == 'yes') {
            $optional = 1;
        } else { //null case handled as not optional
            $optional = 0;
        }
        //getentity
        $unitLevel = $this->getLevelUnitsTable()->find()->where(['unit_id' => $unit_id])->first();
        $unitLevel->optional = $optional;
        $this->getLevelUnitsTable()->save($unitLevel);
        return $this->redirect($this->referer());
    }

    /**
     * Moves a level based on its sequence number ($currentSort) with ID
     * $levelId in path $pathId up or down one, based on the $direction specified.
     * @param {number} $pathId - ID of learning path the level to move is in.
     * @param {number} $levelId - ID of the level to move up or down one.
     * @param {string} $direction - Direction to move level within path
     *   ('up' = closer to beginning), 'down' = closer to end)).
     * @param {number} $currentSort - Sequence number of level in path, before
     *   making this move.
     * @return Reloads page
     */
    public function moveLevel(
        int $pathId = null,
        int $levelId = null,
        string $direction = null,
        int $currentSort = null
    ) {
        // Check args are all valid
        $errorMsg = '';
        if (empty($pathId)) {
            $errorMsg = "Empty path id. ";
        }
        if (empty($levelId)) {
            $errorMsg .= "Empty level id. ";
        }
        if (empty($currentSort)) {
            $errorMsg .= "Empty current sort. ";
        };
        if ($direction != 'up' && $direction != 'down') {
            $errorMsg .= "direction must be 'up' or 'down'";
        }
        if (!empty($errorMsg)) {
            $this->Flash->error(__($errorMsg));
            return $this->redirect($this->referer());
        }

        // Get new sequence number for level user is moving
        if ($direction == 'up') {
            $newSort = $currentSort - 1;
        } else {
            $newSort = $currentSort + 1;
        }

        // Get path level that user is moving
        $pathLevelOne = $this->getPathlevelTable()->find()
            ->where([
                'learningpath_id' => $pathId,
                'level_id' => $levelId
            ])->first();

        // Get path level that will get swapped with path level user is moving
        $pathLevelToSwapWith = $this->getPathlevelTable()->find()
            ->where([
                'learningpath_id' => $pathId,
                'sequence' => $newSort,
                'id !=' => $pathLevelOne->id
            ])->first();

        if (empty($pathLevelToSwapWith)) {
            $this->getPathlevelTable()->resequence($pathId);
            return $this->redirect($this->referer());
        }

        // Update PathLevel that is getting moved due to user moving pathLevelOne;
        $pathLevelTwo = $this->getPathlevelTable()->get($pathLevelToSwapWith->id);
        $pathLevelTwo->sequence = $currentSort;
        $pathLevelTwo = $this->getPathlevelTable()->save($pathLevelTwo);
        if (empty($pathLevelTwo)) {
            Log::error("Error saving pathLevelTwo");
        }

        // Update PathLevel that user moved
        $pathLevelOne->sequence = $newSort;
        $pathLevelOne = $this->getPathlevelTable()->save($pathLevelOne);
        if (empty($pathLevelOne)) {
            Log::error("Error saving pathLevelOne");
        }

        if (!empty($pathId)) {
            $this->getPathlevelTable()->resequence($pathId);
        }

        return $this->redirect($this->referer());
    }

    public function moveUnit(
        int $pathId = null,
        int $levelId = null,
        int $unitId = null,
        string $direction = null,
        int $currentSort = null
    ) {
        // Check args are all valid
        $errorMsg = '';
        if (empty($pathId)) {
            $errorMsg = "Empty path id. ";
        }
        if (empty($levelId)) {
            $errorMsg .= "Empty level id. ";
        }
        if (empty($unitId)) {
            $errorMsg .= "Empty unit it. ";
        }
        if (empty($currentSort)) {
            $errorMsg .= "Empty current sort. ";
        }
        if ($direction != 'up' && $direction != 'down') {
            $errorMsg .= "direction must be 'up' or 'down'";
        }
        if (!empty($errorMsg)) {
            $this->Flash->error(__($errorMsg));
            return $this->redirect($this->referer());
        }

        if ($direction == 'up') {
            $newsort = $currentSort - 1;
        } else {
            $newsort = $currentSort + 1;
        }

        // Get LevelUnit that user wants to move
        $levelUnitOne = $this->getLevelUnitsTable()->find()
            ->where([
                'learningpath_id' => $pathId,
                'level_id' => $levelId,
                'unit_id' => $unitId
            ])->first();

        // Get LevelUnit that needs to swap with unit user is moving
        $levelUnitToSwapWith = $this->getLevelUnitsTable()->find()
            ->where([
                'learningpath_id' => $pathId,
                'level_id' => $levelId,
                'sequence' => $newsort,
                'id !=' => $levelUnitOne->id
            ])->first();

        // Update LevelUnit being swapped with one user is moving
        $levelUnitTwo = $this->getLevelUnitsTable()->get($levelUnitToSwapWith->id);
        $levelUnitTwo->sequence = $currentSort;
        if (!$this->getLevelUnitsTable()->save($levelUnitTwo)) {
            Log::error("Error updating levelUnitTwo");
        }

        // Update LevelUnit user is moving
        $levelUnitOne->sequence = $newsort;
        if (!$this->getLevelUnitsTable()->save($levelUnitOne)) {
            Log::error("Error updating levelUnitOne");
        }

        if (!empty($pathId) && !empty($levelId)) {
            $this->getLevelUnitsTable()->resequence($pathId, $levelId);
        }

        return $this->redirect($this->referer());
    }

    public function deleteUnit($pathId = null, $levelId = null, $unitId = null)
    {
        $this->deleteAllUnitData($unitId);
        $this->getLevelUnitsTable()->resequence($pathId, $levelId);
        return $this->redirect(['action' => 'managePaths', $pathId, $levelId]);
    }

    public function getUnitDetailsData($type)
    {
        switch ($type) {
            case 'lesson':
                $unitDetails = $this->getLessonsTable()->find();
                break;
            case 'exercise':
                $unitDetails = $this->getExercisesTable()->find();
                break;
            default:
                break;
        }
        $response = array('status' => true, 'data' => $unitDetails);
        echo json_encode($response);
        die;
    }

    public function getUnitDetailSingle()
    {
        switch ($_POST['type']) {
            case 'lesson':
                $unitDetails = $this->getLessonsTable()->get(
                    $_POST['id'],
                    ['contain' => [
                        'Lessonframes' => ['sort' => 'frameorder'],
                        'Lessonframes.LessonFrameBlocks'
                    ]]
                );
                break;
            case 'exercise':
                $unitDetails = $this->getExercisesTable()->get($_POST['id']);
                break;
            default:
                break;
        }

        echo json_encode($unitDetails);
        die;
    }

    public function saveUnitDetailsData()
    {
        $this->getUnitdetailsTable()->deleteAll(['unit_id' => $_POST['unit_id']]);
        /* Make sure this is set, otherwise there's nothing to for loop through */
        if (isset($_POST['detailsData'])) {
            foreach ($_POST['detailsData'] as $key => $value) {
                $unitDetail = $this->getUnitdetailsTable()->newEmptyEntity();

                $saveData = array(
                    'learningpath_id' => $_POST['learningpath_id'],
                    'unit_id' => $_POST['unit_id'],
                    'sequence' => $key + 1
                );

                switch ($value['type']) {
                    case 'lesson':
                        $saveData['lesson_id'] = $value['id'];
                        break;
                    case 'exercise':
                        $saveData['exercise_id'] = $value['id'];
                        break;
                    default:
                        break;
                }
                $unitDetail = $this->getUnitdetailsTable()->patchEntity($unitDetail, $saveData);
                $this->getUnitdetailsTable()->save($unitDetail);
            }
        }
        /* Using $_POST['unit_id'] here instead of $saveData['unit_id'] because $saveData is
             not always gonna be set, like in the case where all lessons and exercises are removed
             from the unit. */
        $this->unitCardInsert($_POST['unit_id']);
        echo json_encode(array('status' => true, 'message' => 'Data saved'));
        die;
    }

    private function unitCardInsert($unitId)
    {
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
                'Exercises.Exerciseoptions'
            )
        );
        $unitDetails = $this->getUnitdetailsTable()->find('all', $unitOptions)->toArray();

        $cardIds = array();
        foreach ($unitDetails as $key => $unitActivity) {
            $this->addCardsFromActivityToCardUnits($unitActivity, $cardIds);
        }

        $cardIds = array_values(array_unique($cardIds));

        // Delete all cards from this unit and then add updated list
        $this->getCardUnitsTable()->deleteAll(['unit_id' => $unitId]);

        // Create array of new items to batch save to the database table
        $newCards = [];
        foreach ($cardIds as $cardId) {
            $newCards[] = ['card_id' => $cardId, 'unit_id' => $unitId];
        }
        // Batch create entities from above array and save atomically to database table using
        // cakephp Saving Multiple Entities functionality since v3.2.8
        $entities = $this->getCardUnitsTable()->newEntities($newCards);
        $result = $this->getCardUnitsTable()->saveMany($entities);

        return true;
    }

    private function fileTypeIsExcel(\Laminas\Diactoros\UploadedFile $file)
    {
        $typeFormat = explode("/", $file->getClientMediaType());
        $type = $typeFormat[0];
        $format = $typeFormat[1];
        $acceptedFormats = [
            'vnd.ms-excel',
            'vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'octet-stream',
        ];
        if ($type != 'application' || (!in_array($format, $acceptedFormats))) {
            return false;
        }
        return true;
    }

    public function uploadUnitContents()
    {
        $File = $this->getFilesTable()->newEmptyEntity();
        if (!$this->request->is('post')) {
            // Render the upload form
            $this->set(compact('File'));
            $this->render('upload_unit_contents');
            return;
        }

        $data = $this->request->getData();
        if (!empty($data['file'])) {
            $uploadedFile = $data['file'];
            if (!empty($uploadedFile->getClientFilename())) {
                // Verify no file upload errors
                if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                    $this->Flash->error(__('Please upload an excel file less then 2 MB.'));
                    return $this->redirect(['action' => 'uploadUnitContents']);
                }

                // Verify file is an Excel file
                if ($this->fileTypeIsExcel($uploadedFile) === false) {
                    $this->Flash->error(__('Please upload an Excel (.xls or .xlsx) file.'));
                    return $this->redirect(['action' => 'uploadUnitContents']);
                }

                // Load the uploaded file into a PhpSpreadsheet object
                $tempPath = $uploadedFile->getStream()->getMetadata('uri');
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempPath);

                // Generate Exercises, lessons and all nested entities
                $lessons = $this->makeLessonsFromExcelObj($spreadsheet);

                if (!$lessons) {
                    return $this->redirect(['action' => 'uploadUnitContents']);
                }

                return $this->batchVerifyOrSave($lessons);
            } else {
                $this->Flash->error(__('No file uploaded. Please try again.'));
            }
        } elseif (isset($data['savebtn']) || isset($data['cancelbtn'])) {
            // Handle form submissions for save or cancel buttons
            return $this->batchVerifyOrSave();
        } else {
            $this->Flash->error(__('Somthing is wrong with the file size or the file itself. Please try again'));
        }

        $this->set(compact('File'));
        $this->render('upload_unit_contents');
    }

    private function getNumRowsInSpreadsheet($worksheetArray): int
    {
        $numRows = 0;
        foreach ($worksheetArray as $row) {
            if (empty(array_filter($row))) {
                return $numRows;
            }
            $numRows++;
        }
        return $numRows;
    }

    //the intermediate page where cards are inspected before creation
    //OR user presses 'save all' from that page, leading to saving each entity

    private function makeLessonsFromExcelObj($xl)
    {
        $lessons = [];
        $lessonNames = [];

        // Collect lesson names
        foreach ($xl->getWorksheetIterator() as $worksheet) {
            if ($worksheet->getTitle() != 'README') {
                $lessonNames[] = $worksheet->getTitle();
            }
        }

        // Ensure unique lesson names
        if ($this->arrayHasDupes($lessonNames)) {
            $this->Flash->error(__('Error in Workbook: Lesson names (Worksheet tabs) must all be unique.'));
            return false;
        }

        // Process each worksheet (runtime O(n) (first loop))
        foreach ($xl->getWorksheetIterator() as $worksheet) {
            if ($worksheet->getTitle() != 'README') {
                $lessonCounter = 0;
                $worksheetArray = $worksheet->toArray();
                $lessonArray = [];
                $numRowsInSheet = $this->getNumRowsInSpreadsheet($worksheetArray);

                if ($numRowsInSheet == 0) {
                    $this->Flash->error(__('Error in sheet "' . $worksheet->getTitle() . '": No data found.'));
                    return false;
                }

                for ($rowIdx = 1; $rowIdx < $numRowsInSheet; $rowIdx++) {
                    $row = $worksheetArray[$rowIdx];
                    $nextRow = $worksheetArray[$rowIdx + 1];
                    $rowType = isset($row[0]) ? trim($row[0]) : '';
                    $nextRowType = isset($nextRow[0]) ? trim($nextRow[0]) : '';

                    $lessonArray[] = $row;
                    //create lesson if next row is New Lesson or is last row
                    if ($rowIdx == $numRowsInSheet - 1 || $nextRowType == "New Lesson") {
                        if (!empty($lessonArray)) {
                            $lessonCounter++;
                            $lesson = $this->getLessonsTable()->newEmptyEntity();
                            $lesson['name'] = $worksheet->getTitle() . ' - ' . $lessonCounter;
                            $lessonAlreadyExists = $this->lessonAlreadyExistsInDb($lesson['name']);

                            $lessonData = [
                                'lesson' => $lesson,
                                //frames will have blocks and cards nested within each frame
                                'frames' => $this->makeFramesFromSheetArray($lessonArray),
                                'exercises' => $this->makeExercisesFromSheetArray($lessonArray, $lesson['name']),
                                'cards' => $this->makeCardsFromSheetArray($lessonArray)
                            ];

                            if ($lessonAlreadyExists) {
                                $lessonData['errors'] = ['A lesson already exists with the name: ' . $lesson['name']];
                            }

                            $lessons[] = $lessonData;
                            $lessonArray = [];
                        }
                    }
                }
            }
        }

        $rowIdx = 2; //start the row at 2, since sheet ordering starts at 1 and 1 is the header row
        foreach ($lessons as $lesson) {
            if (isset($lesson['errors'])) {
                foreach ($lesson['errors'] as $error) {
                    $this->Flash->error(__('Error in lesson "'
                        . $lesson['lesson']['name'] . '": ' . $error));
                    return false;
                }
            } else {
                foreach ($lesson['frames'] as $frame) {
                    if (isset($lesson['frames']['errors'])) {
                        foreach ($lesson['frames']['errors'] as $error) {
                            $this->Flash->error(__('Error in lesson "'
                                . $lesson['lesson']['name'] . '", frame at row '
                                . $rowIdx . ': ' . $error));
                            return false;
                        }
                    } else {
                        foreach ($frame['blocks'] as $block) {
                            if (isset($block['errors'])) {
                                foreach ($block['errors'] as $err) {
                                    $this->Flash->error(__('Error in lesson "'
                                        . $lesson['lesson']['name']
                                        . '", block at row ' . $rowIdx . ': ' . $err));
                                    return false;
                                }
                            } elseif (isset($block['card'])) {
                                if ($block['card'] && isset($block['card']['errors'])) {
                                    foreach ($block['card']['errors'] as $error) {
                                        $this->Flash->error(__('Error in lesson "'
                                            . $lesson['lesson']['name']
                                            . '", card at row ' . $rowIdx . ': ' . $error));
                                        return false;
                                    }
                                }
                            }
                            $rowIdx++;
                        }
                    }
                }
                foreach ($lesson['exercises'] as $exercises) {
                    if (isset($lesson['exercises']['errors'])) {
                        foreach ($lesson['exercises']['errors'] as $error) {
                            $this->Flash->error(__('Error in exercise on "'
                                . $lesson['lesson']['name'] . '", Error: ' . $error));
                            return false;
                        }
                    } elseif (isset($exercises['exercise'])) {
                        foreach ($exercises['exercise'] as $exercise) {
                            if (isset($exercise['errors'])) {
                                foreach ($exercise_option['errors'] as $err) {
                                    $this->Flash->error(__('Error in exercise on "'
                                        . $lesson['lesson']['name'] . '", Error: ' . $err));
                                    return false;
                                }
                            }
                        }
                        foreach ($exercises['exercise_options'] as $exercise_option) {
                            if (isset($exercise_option['errors'])) {
                                foreach ($exercise_option['errors'] as $err) {
                                    $this->Flash->error(__('Error in exercise options on "'
                                        . $lesson['lesson']['name'] . '", Error: ' . $err));
                                    return false;
                                }
                            } elseif (isset($exercise_option['card'])) {
                                if ($exercise_option['card'] && isset($exercise_option['card']['errors'])) {
                                    foreach ($exercise_option['card']['errors'] as $error) {
                                        $this->Flash->error(__('Error in exercise option card on "'
                                            . $lesson['lesson']['name'] . '", Error: ' . $error));
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            //reset the row when iterating to next 'tab'(unit)
            $rowIdx = 2;
        }

        return $lessons;
    }

    /*

        CARD from SPREADSHEET ROW

    */

    private function arrayHasDupes($array)
    {
        return count($array) !== count(array_unique($array));
    }

    /*

        BLOCK from SPREADSHEET ROW

    */

    private function lessonAlreadyExistsInDb($name)
    {
        return $this->getLessonsTable()->find()
            ->where(['name' => $name])
            ->first() ?: false;
    }

    /*

        FRAMES from WORKSHEET

    */

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
        //ignore set to true by default so if no frames set, it still makes exercises.
        $ignoreFrame = true;
        $errors = [];

        // Loop through the worksheet
        foreach ($arr as $rowIndex => $row) {
            $numBlocks = 0;
            $rowFirstCell = trim($row[0] ?? '');

            if (!empty($rowFirstCell)) {
                if ($rowFirstCell == 'New Lesson' || $rowFirstCell == 'New Frame') {
                    // This row contains new frame (& block). 'New Lesson' just indicates the first frame of a lesson
                    $currentFrameIdx++;
                    $ignoreFrame = false;
                    $numBlocks = 0;

                    // Make a new Frame object and set its orientation
                    $frame = $this->getLessonFramesTable()->newEmptyEntity();
                    $frame['frame_preview'] = 'portrait';

                    // If frame audio is specified, find the file ID and set it
                    $frameAudioFilename = trim($row[1] ?? '');
                    if (!empty($frameAudioFilename)) {
                        $audioId = $this->findFileId($frameAudioFilename, 'audio');
                        if ($audioId !== false) {
                            $frame['audio_id'] = $audioId;
                        } else {
                            $errors[] = 'Cannot find audio file "' . $frameAudioFilename . '"';
                        }
                    }

                    // Add frame to array and create first block for this frame
                    $frames[$currentFrameIdx] = [
                        'frame' => $frame,
                        'blocks' => [$this->makeBlockFromRowData($row)]
                    ];
                    $numBlocks++;
                } elseif ($rowFirstCell == 'Ignore') {
                    // No blocks will be created for subsequent rows until a frame is created
                    $ignoreFrame = true;
                } else {
                    $errors[] = 'Invalid frame call in first column at row ' . ($rowIndex + 1);
                }
            } elseif (!empty(trim($row[2] ?? '')) && !$ignoreFrame) {
                // This row only contains a new block
                $frames[$currentFrameIdx]['blocks'][] = $this->makeBlockFromRowData($row);
                $numBlocks++;
                if ($numBlocks > 3) {
                    $errors[] = 'Frames cannot have more than 3 blocks. (at row ' . ($rowIndex + 1) . ')';
                }
            }
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return $this->enumerateFramesAndBlocks($frames);
    }

    /*

        CARDS from WORKBOOK

    */

    private function findFileId($filename, $type)
    {
        $file = $this->getFilesTable()->find()
            ->where([
                'file_name' => $filename,
                'type' => $type
            ])->toArray();
        if (!empty($file)) {
            return $file[0]->id;
        } else {
            return false;
        }
    }

    /*

        EXERCISES from WORKBOOK

    */

    /**
     * Creates a lesson frame block array from a row of data from a spreadsheet.
     * Block fields in the database are:
     * lesson_frame_id, card_id, audio_id, image_id, video_id, block_no, type,
     * is_card_lakota, is_card_english, is_card_audio, is_card_video, is_card_image, custom_html
     * @param {array} $row - Row of data from a spreadsheet.
     * @return {array} - Array containing a lesson frame block entity and a card entity.
     */
    private function makeBlockFromRowData($row)
    {
        $errors = [];

        // Create new lesson frame block entity
        $block = $this->getLessonFrameBlocksTable()->newEmptyEntity()->toArray();
        $blockType = strtolower(trim($row[2] ?? ''));

        $blockDefaults = [
            'is_card_lakota' => 'N',
            'is_card_english' => 'N',
            'is_card_audio' => 'N',
            'is_card_video' => 'N',
            'is_card_image' => 'N'
        ];

        //make a card for this block since it was specified
        switch ($blockType) {
            case 'card':
                $block['type'] = $blockType;
                // We only need these for card type, since the other types always display their type
                $block['is_card_lakota'] = $row[13] ? 'Y' : 'N';
                $block['is_card_english'] = $row[14] ? 'Y' : 'N';
                $block['is_card_audio'] = $row[15] ? 'Y' : 'N';
                $block['is_card_image'] = $row[16] ? 'Y' : 'N';
                $block['is_card_video'] = $row[17] ? 'Y' : 'N';
                return [
                    'block' => $block,
                    'card' => $this->makeCardFromRowData($row)
                ];

            case 'html':
                if (empty(trim($row[12] ?? ''))) {
                    $errors[] = 'HTML block type must have HTML content.';
                    return ['errors' => $errors];
                }
                $block['type'] = $blockType;
                $block['custom_html'] = UtilLibrary::replaceInvalidChars(trim($row[12]), Configure::read('LANGUAGE'));
                return [
                    'block' => array_merge($blockDefaults, $block),
                    'card' => null
                ];
            case 'audio':
            case 'image':
            case 'video':
                $fileColumnIndex = ['audio' => 9, 'image' => 10, 'video' => 11][$blockType];
                $fileId = $this->findFileId(trim($row[$fileColumnIndex] ?? ''), $blockType);
                if (!$fileId) {
                    $errors[] = 'Cannot find ' . $blockType . ' file "' . trim($row[fileColumnIndex] ?? '') . '"';
                    return ['errors' => $errors];
                }
                $block[$blockType . '_id'] = $fileId;
                return [
                    'block' => array_merge($blockDefaults, ['type' => $blockType], $block),
                    'card' => null
                ];
            default:
                $errors[] = $blockType . ' is not card, html, audio, image, or video';
                return ['errors' => $errors];
        }
    }

    private function makeCardFromRowData($row)
    {
        $errors = [];
        $card = $this->getCardTable()->newEmptyEntity();

        // Find card type ID from card type title
        $typeId = $this->findTypeId(trim($row[3]));
        if ($typeId) {
            $card['card_type_id'] = $typeId;
        } else {
            $errors[] = 'Could not find card type id of type ' . trim($row[3] ?? '');
        }

        $genders = ['default', 'male', 'female', 'neuter'];
        $gender = trim($row[6] ?? 'default');
        if (!in_array($gender, $genders)) {
            $errors[] = 'Gender must be specified as "' . implode('" or "', $genders) . '"';
        } else {
            $card['gender'] = $gender;
        }

        $card['lakota'] = UtilLibrary::replaceInvalidPunctuation(
            UtilLibrary::replaceInvalidChars(trim($row[4] ?? ''), Configure::read('LANGUAGE')),
            Configure::read('LANGUAGE'));
        $card['english'] = trim($row[5] ?? '');

        // Alternate target language and English text
        //if we didnt find a duplicate, get all the other info we need to make a new card
        $card['alt_lakota'] = UtilLibrary::replaceInvalidPunctuation(trim($row[7] ?? ''), Configure::read('LANGUAGE'),
            Configure::read('LANGUAGE'));
        $card['alt_english'] = trim($row[8] ?? '');
        $includeReview = strtolower(trim($row[18] ?? ''));
        $card['include_review'] = (array_search($includeReview, ['n', 'no', '0', ''])) ? 0 : 1;

        // Audio files are comma separated
        if (trim($row[9] ?? '') != '') {
            $audioFileNames = explode(",", $row[9]);
            foreach ($audioFileNames as $filename) {
                $audioId = $this->findFileId(trim($filename), 'audio');
                if ($audioId === false) {
                    $errors[] = 'Could not find audio file "' . trim($filename) . '"';
                } else {
                    if (!empty($card['audio'])) {
                        $card['audio'] .= ",";
                    }
                    $card['audio'] .= $audioId;
                }
            }
        }

        $fileTypes = [
            'image' => 10,
            'video' => 11
        ];

        foreach ($fileTypes as $type => $index) {
            $file = trim($row[$index] ?? '');
            if ($file) {
                $fileId = $this->findFileId($file, $type);
                if ($fileId === false) {
                    $errors[] = 'Could not find ' . $type . ' file "' . $file . '"';
                    Log::error('Could not find ' . $type . ' file "' . $file . '"');
                } else {
                    $card[$type . '_id'] = $fileId;
                }
            }
        }

        // Return error, if any
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $duplicate = $this->checkDuplicateCard($card);
        if ($duplicate) {
            // If card is a duplicate, return the updated duplicate card
            $duplicate['isNew'] = false;
            return $duplicate;
        }

        // Return new card
        $card['isNew'] = true;
        return $card;
    }

    private function findTypeId($type)
    {
        $type = $this->getCardtypeTable()->find()->where(['title' => $type])->toArray();
        if (!empty($type)) {
            return $type[0]->id;
        } else {
            return false;
        }
    }

    private function checkDuplicateCard($card)
    {
        $similarCardInDb = $this->getCardTable()->find()
            ->where([
                'card_type_id' => trim($card['card_type_id']),
                'lakota =' => trim($card['lakota']),
                'english =' => trim($card['english']),
                'gender =' => trim($card['gender'])
            ])
            ->first();

        if ($similarCardInDb) {
            return $this->duplicateCardUpdate($card, $similarCardInDb);
        }

        return false;
    }

    /**
     * Checks if a value is empty while also considering 0 and '0' as non-empty,
     * since those are valid values for some fields.
     */
    private function isEmpty($value)
    {
        return empty($value) && $value !== 0 && $value !== '0';
    }

    private function duplicateCardUpdate($importedCard, $similarCardInDb)
    {
        $fields = [
            "audio", // is actually a comma separated list of audio IDs
            "image_id",
            "video_id",
            "alt_lakota",
            "alt_english",
            "include_review"
        ];

        foreach ($fields as $field) {
            $importedValue = trim($importedCard->get($field)) ?? null;
            $dbValue = trim($similarCardInDb->get($field)) ?? null;

            if ($this->valueAdded($dbValue, $importedValue)) {
                // Replace empty DB card field with new card field
                $similarCardInDb->set($field, $importedValue);
                $similarCardInDb->set($field . "IsAdded", true);
            } else if ($this->valueUpdated($dbValue, $importedValue)) {
                // If both fields are not empty, and they are different, update DB card field with new card field
                $similarCardInDb->set($field, $importedValue);
                $similarCardInDb->set($field . "IsUpdated", true);
            } else if ($this->valueRemoved($dbValue, $importedValue)) {
                // If DB card has field and new card field is empty, keep DB card field, ignore new card's empty field
                $similarCardInDb->set($field, $importedValue);
                $similarCardInDb->set($field . "IsRemoved", true);
            } else {
                // If both fields are empty, do nothing
            }
        }

        return $similarCardInDb;
    }

    private function valueAdded($existingValue, $newValue): bool
    {
        return $this->isEmpty($existingValue) && !$this->isEmpty($newValue);
    }

    private function valueUpdated($existingValue, $newValue): bool
    {
        return !$this->isEmpty($existingValue) && !$this->isEmpty($newValue) && $existingValue != $newValue;
    }

    private function valueRemoved($existingValue, $newValue): bool
    {
        return !$this->isEmpty($existingValue) && $this->isEmpty($newValue);
    }

    /*

        LESSONS from WORKBOOK

    */

    private function enumerateFramesAndBlocks($frames)
    {
        $frameId = 1;

        foreach ($frames as &$frame) {
            $frame['frame']['frameorder'] = $frameId;
            $frame['frame']['name'] = 'Frame ' . $frameId;

            $blockId = 1;
            foreach ($frame['blocks'] as &$block) {
                $block['block_no'] = $blockId++;
            }

            $frame['frame']['number_of_block'] = $blockId - 1;
            $frameId++;
        }

        return $frames;
    }

    private function makeExercisesFromSheetArray($arr, $baseName)
    {
        $exercises = [];
        $currentExerciseIdx = -1;
        $errors = [];
        //these collect match the pair cards for later creation
        $rowsWithMTPAtoE = [];
        $countMTPAtoE = 0;
        $rowsWithMTPLtoE = [];
        $countMTPLtoE = 0;
        $minMTPCards = 4;
        $maxMTPCards = 8;
        $cards = $this->makeCardsFromSheetArray($arr);
        //skip the header row
        //loop through the worksheet
        for ($row = 0; $row < count($arr); $row++) {
            /*

            CREATE EXERCISES

            */
            //this row contains a card and can therefore create an exercise
            if (trim($arr[$row][2]) == "Card") {
                //set prompt card for this row
                $prompt_card = $this->makeCardFromRowData($arr[$row]);
                $lakota = trim($arr[$row][4]);
                $bracketLakota = $this->makeBracketLakota($lakota);
                $english = trim($arr[$row][5]);
                //Match the Pairs A-E
                if (trim($arr[$row][19]) == 'y') {
                    //must have audio
                    if (trim($arr[$row][9])) {
                        $rowsWithMTPAtoE[] = $row;
                        $countMTPAtoE++;
                    } else {
                        $errors[] = "Cannot Create MTP A-E.	No audio available for row " . $row;
                    }
                }
                //Match the Pairs L-E
                if (trim($arr[$row][20]) == 'y') {
                    $rowsWithMTPLtoE[] = $row;
                    $countMTPLtoE++;
                }
                //Multiple Choice E-L
                if (trim($arr[$row][21]) == 'y') {
                    //update the current exercise index
                    $currentExerciseIdx++;
                    $exercise_options = [];
                    $exercise_options[] = [
                        'exercise_option' => $this->makeExerciseOption('card', 'P'),
                        'card' => $prompt_card
                    ];
                    for ($i = 0; $i < count($cards); $i++) {
                        $exercise_options[] = [
                            'exercise_option' => $this->makeExerciseOption('card', 'O'),
                            'card' => $cards[$i]
                        ];
                    }
                    $exercises[$currentExerciseIdx] = [
                        'exercise' => $this->makeExercise(
                            $baseName . " MCQ E-L " . $lakota . " - " . $english,
                            'multiple-choice',
                            'e-l'
                        ),
                        'exercise_options' => $exercise_options
                    ];
                }
                //Multiple Choice A-E
                if (trim($arr[$row][22]) == 'y') {
                    if ($arr[$row][9]) {
                        //update the current exercise index
                        $currentExerciseIdx++;
                        $exercise_options = [];
                        $exercise_options[] = [
                            'exercise_option' => $this->makeExerciseOption('card', 'P'),
                            'card' => $prompt_card
                        ];
                        for ($i = 0; $i < count($cards); $i++) {
                            $exercise_options[] = [
                                'exercise_option' => $this->makeExerciseOption('card', 'O'),
                                'card' => $cards[$i]
                            ];
                        }
                        $exercises[$currentExerciseIdx] = [
                            'exercise' => $this->makeExercise(
                                $baseName . " MCQ A-E " . $lakota . " - " . $english,
                                'multiple-choice',
                                'a-e'
                            ),
                            'exercise_options' => $exercise_options
                        ];
                    } else {
                        $errors[] = "Cannot Create Multiple Choice A-E.	No audio available for row " . $row;
                    }
                }
                //Multiple Choice L-E
                if (trim($arr[$row][23]) == 'y') {
                    //update the current exercise index
                    $currentExerciseIdx++;
                    $exercise_options = [];
                    $exercise_options[] = [
                        'exercise_option' => $this->makeExerciseOption('card', 'P'),
                        'card' => $prompt_card
                    ];
                    for ($i = 0; $i < count($cards); $i++) {
                        $exercise_options[] = [
                            'exercise_option' => $this->makeExerciseOption('card', 'O'),
                            'card' => $cards[$i]
                        ];
                    }
                    $exercises[$currentExerciseIdx] = [
                        'exercise' => $this->makeExercise(
                            $baseName . " MCQ L-E " . $lakota . " - " . $english,
                            'multiple-choice',
                            'l-e'
                        ),
                        'exercise_options' => $exercise_options
                    ];
                }
                //Multiple Choice I-L
                if (trim($arr[$row][24]) == 'y') {
                    //update the current exercise index
                    if ($arr[$row][10]) {
                        $currentExerciseIdx++;
                        $exercise_options = [];
                        $exercise_options[] = [
                            'exercise_option' => $this->makeExerciseOption('card', 'P'),
                            'card' => $prompt_card
                        ];
                        for ($i = 0; $i < count($cards); $i++) {
                            $exercise_options[] = [
                                'exercise_option' => $this->makeExerciseOption('card', 'O'),
                                'card' => $cards[$i]
                            ];
                        }
                        $exercises[$currentExerciseIdx] = [
                            'exercise' => $this->makeExercise(
                                $baseName . " MCQ I-L " . $lakota . " - " . $english,
                                'multiple-choice',
                                'i-l'
                            ),
                            'exercise_options' => $exercise_options
                        ];
                    } else {
                        $errors[] = "Cannot Create Multiple Choice I-L. No image available for row " . $row;
                    }
                }
                //Anagram E-L
                if (trim($arr[$row][25]) == 'y') {
                    $currentExerciseIdx++;
                    $exercise_options = [];
                    $exercise_options[] = [
                        'exercise_option' => $this->makeExerciseOption('card', 'O', 'e', 'l'),
                        'card' => $prompt_card
                    ];
                    $exercises[$currentExerciseIdx] = [
                        'exercise' => $this->makeExercise(
                            $baseName . " Anagram E-L " . $lakota . " - " . $english,
                            'anagram',
                            'e-l'
                        ),
                        'exercise_options' => $exercise_options
                    ];
                }
                //Anagram A-L
                if (trim($arr[$row][26]) == 'y') {
                    if ($arr[$row][9]) {
                        $currentExerciseIdx++;
                        $exercise_options = [];
                        $exercise_options[] = [
                            'exercise_option' => $this->makeExerciseOption('card', 'O', 'a', 'l'),
                            'card' => $prompt_card
                        ];
                        $exercises[$currentExerciseIdx] = [
                            'exercise' => $this->makeExercise(
                                $baseName . " Anagram A-L " . $lakota . " - " . $english,
                                'anagram',
                                'a-l'
                            ),
                            'exercise_options' => $exercise_options
                        ];
                    } else {
                        $errors[] = "Cannot Create Anagram A-L.	No audio available for row " . $row;
                    }
                }
                //Typing A-L
                if (trim($arr[$row][27]) == 'y') {
                    if ($arr[$row][9]) {
                        $currentExerciseIdx++;
                        $exercise_options = [];
                        //typing requires 2 options cards, a P and an O
                        $exercise_options[] = [
                            'exercise_option' => $this->makeExerciseOption(
                                'card',
                                'P',
                                'a',
                                'l',
                                'typing',
                                $bracketLakota
                            ),
                            'card' => $prompt_card
                        ];
                        $exercise_options[] = [
                            'exercise_option' => $this->makeExerciseOption(
                                'card',
                                'O',
                                'a',
                                'l',
                                'typing',
                                $lakota
                            ),
                            'card' => $prompt_card
                        ];
                        $exercises[$currentExerciseIdx] = [
                            'exercise' => $this->makeExercise(
                                $baseName . " Typing A-L " . $lakota . " - " . $english,
                                'fill_in_the_blanks',
                                'a-l'
                            ),
                            'exercise_options' => $exercise_options
                        ];
                    } else {
                        $errors[] = "Cannot Create Typing A-L.	No audio available for row " . $row;
                    }
                }
                //Typing E-L
                if (trim($arr[$row][28]) == 'y') {
                    $currentExerciseIdx++;
                    $exercise_options = [];
                    //typing requires 2 options cards, a P and an O
                    $exercise_options[] = [
                        'exercise_option' => $this->makeExerciseOption(
                            'card',
                            'P',
                            'e',
                            'l',
                            'typing',
                            $bracketLakota
                        ),
                        'card' => $prompt_card
                    ];
                    $exercise_options[] = [
                        'exercise_option' => $this->makeExerciseOption(
                            'card',
                            'O',
                            'e',
                            'l',
                            'typing',
                            $lakota
                        ),
                        'card' => $prompt_card
                    ];
                    $exercises[$currentExerciseIdx] = [
                        'exercise' => $this->makeExercise(
                            $baseName . " Typing E-L " . $lakota . " - " . $english,
                            'fill_in_the_blanks',
                            'e-l'
                        ),
                        'exercise_options' => $exercise_options
                    ];
                }
                //Fill in the Blank MCQ E->L
                if (!empty($arr[$row][29])) {
                    //looks for leading and closing square brackets without overlap
                    $regex = '/\[[^\[\]]+\]/';
                    $matches = [];
                    preg_match_all($regex, $arr[$row][29], $matches);
                    //must be at least 2 results
                    if (isset($matches[0][1])) {
                        $currentExerciseIdx++;
                        $exercise_options = [];
                        //mcq requires 1 P and as many Os as there are options defined
                        $exercise_options[] = [
                            'exercise_option' => $this->makeExerciseOption(
                                'card',
                                'P',
                                'e',
                                'l',
                                'mcq',
                                trim($arr[$row][29])
                            ),
                            'card' => $prompt_card
                        ];
                        for ($i = 0; $i < count($matches[0]); $i++) {
                            $exercise_options[] = [
                                //preg_replace removes brackets []
                                'exercise_option' => $this->makeExerciseOption(
                                    'card',
                                    'O',
                                    'e',
                                    'l',
                                    'mcq',
                                    preg_replace(
                                        '/[\[\]]/',
                                        "",
                                        $matches[0][$i]
                                    ),
                                    $i + 1
                                ),
                                'card' => $prompt_card
                            ];
                        }
                        $exercises[$currentExerciseIdx] = [
                            'exercise' => $this->makeExercise(
                                $baseName . " Fill in the Blanks E-L " . $lakota . " - " . $english,
                                'fill_in_the_blanks',
                                'e-l'
                            ),
                            'exercise_options' => $exercise_options
                        ];
                    } else {
                        $errors[] = 'Not enough Fill in Blank MCQ options created for ' . $row;
                    }
                }
            }
        }
        //MTP A to E
        if ($countMTPAtoE >= $minMTPCards) {
            //if greater than max, divides up into multiple exercises of length cardBreakCount
            $cardBreakCount = $this->optimalNoofcardsFromCount($countMTPAtoE, $minMTPCards, $maxMTPCards);
            for ($i = 0; $i < $countMTPAtoE; $i++) {
                if ($i == 0 || ($i % $cardBreakCount == 0 && $countMTPAtoE - $i > $cardBreakCount)) {
                    if ($countMTPAtoE - $i < 2 * $cardBreakCount) {
                        $noOfCards = $countMTPAtoE % $cardBreakCount + $cardBreakCount;
                    } else {
                        $noOfCards = $cardBreakCount;
                    }
                    $currentExerciseIdx++;
                    $exercise_options = [];
                }
                $exercise_options[] = [
                    'exercise_option' => $this->makeExerciseOption('card', 'O', 'a', 'e'),
                    'card' => $this->makeCardFromRowData($arr[$rowsWithMTPAtoE[$i]])
                ];
                if (
                    (($i + 1) % $cardBreakCount == 0
                    && $countMTPAtoE - ($i + 1) > $cardBreakCount)
                    || $i + 1 == $countMTPAtoE
                ) {
                    $exercises[$currentExerciseIdx] = [
                        'exercise' => $this->makeExercise(
                            $baseName . " MTP A-E " . $currentExerciseIdx,
                            'match-the-pair',
                            'a-e',
                            $noOfCards
                        ),
                        'exercise_options' => $exercise_options
                    ];
                }
            }
        } else {
            if ($countMTPAtoE > 0) {
                $errors[] = 'Less than ' . $minMTPCards . ' MTP A-E Cards to create an exercise...please add more';
            }
        }
        //MTP L to E
        if ($countMTPLtoE >= $minMTPCards) {
            //if greater than max, divides up into multiple exercises of length cardBreakCount
            $cardBreakCount = $this->optimalNoofcardsFromCount($countMTPLtoE, $minMTPCards, $maxMTPCards);
            for ($i = 0; $i < $countMTPLtoE; $i++) {
                if ($i == 0 || ($i % $cardBreakCount == 0 && $countMTPLtoE - $i > $cardBreakCount)) {
                    if ($countMTPLtoE - $i < 2 * $cardBreakCount) {
                        $noOfCards = $countMTPLtoE % $cardBreakCount + $cardBreakCount;
                    } else {
                        $noOfCards = $cardBreakCount;
                    }
                    $currentExerciseIdx++;
                    $exercise_options = [];
                }
                $exercise_options[] = [
                    'exercise_option' => $this->makeExerciseOption('card', 'O', 'l', 'e'),
                    'card' => $this->makeCardFromRowData($arr[$rowsWithMTPLtoE[$i]])
                ];
                if (
                    (($i + 1) % $cardBreakCount == 0
                    && $countMTPLtoE - ($i + 1) > $cardBreakCount)
                    || $i + 1 == $countMTPLtoE
                ) {
                    $exercises[$currentExerciseIdx] = [
                        'exercise' => $this->makeExercise(
                            $baseName . " MTP L-E " . $currentExerciseIdx,
                            'match-the-pair',
                            'l-e',
                            $noOfCards
                        ),
                        'exercise_options' => $exercise_options
                    ];
                }
            }
        } else {
            if ($countMTPLtoE > 0) {
                $errors[] = 'Less than ' . $minMTPCards
                . ' MTP L-E Cards to create an exercise...please add more';
            }
        }


        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return $exercises;

        //Create card group for options

        //Create exercise from name and type

        //Create Options entry from promptcard and group
    }

    private function makeCardsFromSheetArray($rowsArray)
    {
        $cards = array_filter(array_map(function($row) {
            return trim($row[2] ?? '') == 'Card' ? $this->makeCardFromRowData($row) : null;
        }, $rowsArray));

        return array_values($cards);
    }


    //takes in array from excel spreadsheet and creates cards out of it.

    private function makeBracketLakota($lakota)
    {
        $allWordCharsRegex = UtilLibrary::getLanguageAllWordCharsRegex();
        return preg_replace('/([' . $allWordCharsRegex . ']+)/', '[$1]', $lakota);
    }

    private function makeExerciseOption(
        $type,
        $cardtype,
        $promptpreview = null,
        $responsepreview = null,
        $fillinblanktype = null,
        $textoption = null,
        $optionposition = null
    ) {
        //depending on exercise type, most columns are null
        $exercise_option = $this->getExerciseoptionsTable()->newEmptyEntity();
        $exercise_option['type'] = $type;
        $exercise_option['card_type'] = $cardtype;
        $exercise_option['prompt_preview_option'] = $promptpreview;
        $exercise_option['responce_preview_option'] = $responsepreview;
        $exercise_option['fill_in_the_blank_type'] = $fillinblanktype;
        $exercise_option['text_option'] = $textoption;
        $exercise_option['option_position'] = $optionposition;
        return $exercise_option;
    }

    private function makeExercise($name, $exercisetype, $promptresponsetype, $noofcards = 1)
    {
        $exercise = $this->getExercisesTable()->newEmptyEntity();
        $exercise['name'] = $name;
        $exercise['exercise_type'] = $exercisetype;
        //multiple choice requires card_group be specified due to organization of option cards
        $exercise['card_type'] = ($exercisetype == 'multiple-choice' ? 'card_group' : 'card');
        //grabs instructions from Point References Table
        $pointRef = $this->getPointReferencesTable()
            ->find()
            ->where([
                'exercise' => $exercisetype,
                'prompt_type' => $promptresponsetype[0],
                'response_type' => $promptresponsetype[2]])
            ->first();
        $exercise['instruction'] = $pointRef['instructions'];
        $exercise['bonus'] = 0;
        $exercise['noofcard'] = $noofcards;
        $exercise['promteresponsetype'] = $promptresponsetype;
        //these 2 vars are only used by match the pairs and in the
        //case of generated exercises are redundant with promptresponsetype
        $exercise['promotetype'] = ($exercisetype == 'match-the-pair' ? null : $promptresponsetype[0]);
        $exercise['responsetype'] = ($exercisetype == 'match-the-pair' ? null : $promptresponsetype[2]);
        return $exercise;
    }

    private function optimalNoofcardsFromCount($count, $min, $max)
    {
        if ($count > $max) {
            for ($j = 0; $j <= $max - $min; $j++) {
                $k = $max - $j;
                if ($count % $k <= $j) {
                    return $k;
                }
            }
        } else {
            return $count;
        }
    }

    //format the error so the user can find and fix the mistake in the excel file

    public function batchVerifyOrSave($data = false)
    {
        if ($data) {
            // Import data was passed from the upload function.
            // Display the data for inspection and confirmation.
            $this->set(compact('data'));
            return $this->render('batch_unit_content_verify');
        }

        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            // Save button pressed, create/update/save unit data
            if (
                isset($requestData['savebtn'])
                && isset($requestData['datastring'])
            ) {
                //decode the data string into an array
                $data = json_decode(htmlspecialchars_decode($requestData['datastring']), true);

                //create array that will hold card ids from verify form to be updated
                $isUpdatedFields = $this->populateFields($requestData, "IsUpdated");
                $isRemovedFields = $this->populateFields($requestData, "IsRemoved");
                $isAddedFields = $this->populateFields($requestData, "IsAdded");
                $counter = $this->initializeCounter();
                $firstLessonId = null;

                if (!$data || empty($data) || !is_array($data)) {
                    $this->Flash->error(__('Error: No lessons. Maybe to string to array conversion.'));
                    return $this->redirect(['action' => 'uploadUnitContents']);
                }

                foreach ($data as $lessonData) {
                    $lesson = $this->processLesson($lessonData);
                    if ($lesson) {
                        $firstLessonId = $firstLessonId ?? $lesson->id;
                        $counter['lessons']++;
                    }

                    foreach ($lessonData['cards'] as $cardData) {
                        $this->processCard($cardData, $isUpdatedFields, $isAddedFields, $isRemovedFields, $counter);
                    }

                    foreach ($lessonData['frames'] as $frameData) {
                        $frame = $this->processFrame($frameData, $lesson->id, $counter);

                        if ($frame) {
                            foreach ($frameData['blocks'] as $blockData) {
                                $this->processBlock($blockData, $frame->id, $counter);
                            }
                        }
                    }

                    foreach ($lessonData['exercises'] as $exerciseData) {
                        $exercise = $this->processExercise($exerciseData, $counter);

                        foreach ($exerciseData['exercise_options'] as $optionData) {
                            $this->processExerciseOption($optionData, $exercise->id, $counter);
                        }
                    }
                }

                $this->displaySuccessMessage($counter);

                return $this->redirect(['action' => 'uploadUnitContents']);
            } elseif (isset($requestData['cancelbtn'])) {
                // Upload cancelled by user
                $this->Flash->success(__('Bulk upload was aborted.'));
                return $this->redirect(['action' => 'uploadUnitContents']);
            }
        }

        $this->Flash->error(__('Error: No Lessons uploaded. Please specify a Unit Uploader spreadsheet to verify.'));
        return $this->redirect(['action' => 'uploadUnitContents']);
    }

    private function populateFields($data, $suffix)
    {
        $fields = [
            "audio" . $suffix => [],
            "image_id" . $suffix => [],
            "video_id" . $suffix => [],
            "alt_lakota" . $suffix => [],
            "alt_english" . $suffix => [],
            "include_review" . $suffix => []
        ];

        foreach ($fields as $field => &$cardIds) {
            if (isset($data[$field])) {
                $cardIds = $data[$field];
            }
        }

        return $fields;
    }

    private function initializeCounter()
    {
        return [
            'lessons' => 0,
            'frames' => 0,
            'blocks' => 0,
            'exercises' => 0,
            'exerciseOptions' => 0,
            'cards' => 0,
            'reusedCards' => 0,
            'updatedCards' => 0
        ];
    }

    private function processLesson($lessonData)
    {
        if (empty($lessonData['frames'])) {
            return null;
        }

        $lesson = $this->getLessonsTable()->newEntity($lessonData['lesson']);
        $this->getLessonsTable()->save($lesson);
        return $lesson;
    }

    private function processCard($cardData, $isUpdatedFields, $isAddedFields, $isRemovedFields, &$counter)
    {
        if (empty($cardData)) {
            return;
        }

        $card = $this->findOrCreateCard($cardData, $counter);
        if (!$card) {
            return;
        }

        if ($card['isNew']) {
            $this->saveCard($card, $counter);
        } else {
            $this->updateCardIfNecessary($card, $cardData, $isUpdatedFields, $isAddedFields, $isRemovedFields, $counter);
        }
    }

    private function findOrCreateCard($cardData, &$counter)
    {
        if (isset($cardData['id']) && $cardData['id'] > 0) {
            $card = $this->getCardTable()->findById($cardData['id']);
            if (!$card) {
                $this->Flash->error(__('Error: Could not find card with ID ' . $cardData['id']));
                return null;
            }
            $card['isNew'] = false;
        } else {
            $dupCard = $this->checkDuplicateCard($cardData);
            if ($dupCard) {
                $dupCard['isNew'] = false;
                $card = $dupCard;
            } else {
                $card = $this->getCardTable()->newEntity($cardData);
                $card['isNew'] = true;
            }
        }
        return $card;
    }

    private function saveCard($card, &$counter)
    {
        if ($card) {
            $existedAlready = $card->id ? true : false;
            $this->getCardTable()->save($card);
            if (!$existedAlready) {
                $counter['cards']++;
            } else {
                $counter['reusedCards']++;
            }
        }
    }

    private function updateCardIfNecessary($card, $cardData, $isUpdatedFields, $isAddedFields, $isRemovedFields, &$counter)
    {
        $cardUpdated = false;
        $fields = [
            "audio",
            "image_id",
            "video_id",
            "alt_lakota",
            "alt_english",
            "include_review"
        ];

        foreach ($fields as $field) {
            if (in_array($card->id, $isUpdatedFields[$field . 'IsUpdated'])
                || in_array($card->id, $isAddedFields[$field . 'IsAdded'])
                || in_array($card->id, $isRemovedFields[$field . 'IsRemoved'])) {
                $card[$field] = $cardData[$field];
                $cardUpdated = true;
            }
        }

        if ($cardUpdated) {
            $counter['updatedCards']++;
            $this->getCardTable()->save($card);
        }
    }

    private function processFrame($frameData, $lessonId, &$counter)
    {
        $frameData['frame']['lesson_id'] = $lessonId;
        $frame = $this->getLessonFramesTable()->newEntity($frameData['frame']);
        if ($this->getLessonFramesTable()->save($frame)) {
            $counter['frames']++;
            return $frame;
        } else {
            $this->Flash->error(__('Error: Could not save frame: ' . print_r($frame, true)));
            return null;
        }
    }

    private function processBlock($blockData, $frameId, &$counter)
    {
        $block = $this->getLessonFrameBlocksTable()->newEntity($blockData['block']);
        $card = $this->getCardForBlock($blockData['card']);

        if ($card) {
            $block->card_id = $card->id;
        }
        $block->lesson_frame_id = $frameId;
        $block->block_no = $blockData['block_no'];

        if ($this->getLessonFrameBlocksTable()->save($block)) {
            $counter['blocks']++;
        } else {
            $this->Flash->error(__('Error: Could not save block: ' . print_r($block, true)));
        }
    }

    private function getCardForBlock($cardData)
    {
        if (empty($cardData)) {
            return null;
        }

        if (isset($cardData['id']) && $cardData['id'] > 0) {
            return $this->getCardTable()->findById($cardData['id']);
        } else {
            $dupCard = $this->checkDuplicateCard($cardData);
            if ($dupCard) {
                $dupCard['isNew'] = false;
                return $dupCard;
            } else {
                $card = $this->getCardTable()->newEntity($cardData);
                $card['isNew'] = true;
                $this->getCardTable()->save($card);
                return $card;
            }
        }
    }


    private function processExercise($exerciseData, &$counter)
    {
        $exercise = $this->getExercisesTable()->newEntity($exerciseData['exercise']);
        if ($this->getExercisesTable()->save($exercise)) {
            $counter['exercises']++;
        } else {
            $this->Flash->error(__('Error: Could not save exercise: ' . print_r($exercise, true)));
        }
        return $exercise;
    }

    private function processExerciseOption($optionData, $exerciseId, &$counter)
    {
        $exerciseOption = $this->getExerciseoptionsTable()->newEntity($optionData['exercise_option']);
        $exerciseOption->exercise_id = $exerciseId;
        $card = $this->getCardForExerciseOption($optionData['card']);

        if ($card) {
            if ($exerciseOption->exercise->exercise_type == "match-the-pair") {
                $exerciseOption->responce_card_id = $card->id;
            }
            $exerciseOption->card_id = $card->id;
        }

        $this->getExerciseoptionsTable()->save($exerciseOption);
        $counter['exerciseOptions']++;
    }

    private function getCardForExerciseOption($cardData)
    {
        if (empty($cardData)) {
            return null;
        }

        if (isset($cardData['id']) && $cardData['id'] > 0) {
            return $this->getCardTable()->findById($cardData['id']);
        } else {
            $dupCard = $this->checkDuplicateCard($cardData);
            if ($dupCard) {
                $dupCard['isNew'] = false;
                return $dupCard;
            } else {
                $card = $this->getCardTable()->newEntity($cardData);
                $card['isNew'] = true;
                $this->getCardTable()->save($card);
                return $card;
            }
        }
    }

    private function displaySuccessMessage($counter)
    {
        $this->Flash->success(
            $counter['lessons'] . ' Lesson' . ($counter['lessons'] == 1 ? '': 's') . ', ' .
            $counter['frames'] . ' Frame' . ($counter['frames'] == 1 ? '': 's') . ', ' .
            $counter['blocks'] . ' Block' . ($counter['blocks'] == 1 ? '' : 's') . ', ' .
            $counter['exercises'] . ' Exercise' . ($counter['exercises'] == 1 ? '' : 's') . ', ' .
            $counter['exerciseOptions'] . ' Exercise Option' . ($counter['exerciseOptions'] == 1 ? '' : 's') . ', ' .
            $counter['cards'] . ' Card' . ($counter['cards'] == 1 ? '' : 's') . ' created successfully, ' .
            $counter['reusedCards'] . ' Card' . ($counter['reusedCards'] == 1 ? '' : 's') . ' reused' . ', ' .
            $counter['updatedCards'] . ' Card' . ($counter['updatedCards'] == 1 ? '' : 's') . ' updated.'
        );
    }

    /**
     * Updates the order of units in a learning path.
     * Fetches the level units for the given path ID and updates their sequence based
     * on the provided order data.
     * Displays a success or error message based on the outcome.
     */
    public function updateUnitOrder()
    {
        // Only allow POST and AJAX requests
        $this->request->allowMethod(['post', 'ajax']);

        // Set the response type to JSON
        $this->viewBuilder()->setClassName('Json');
        $this->response = $this->response->withType('application/json');

        // Get the path ID, level ID, and unit order from the request data
        $pathId = $this->request->getData('pathId');
        $levelId = $this->request->getData('levelId');
        $unitOrder = $this->request->getData('unitOrder');

        // Validate the input data
        if (empty($pathId) || empty($levelId)) {
            // Invalid path or level ID
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => __('Error: Invalid path or level ID.')
            ]));
        }

        // Validate the unit order data
        if (empty($unitOrder)) {
            // No unit order data provided
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => __('Error: No unit order data provided.')
            ]));
        }

        // Get current level units and update their sequence
        $levelUnits = $this->getLevelUnitsTable()->find('all')
            ->where([
                'learningpath_id' => $pathId,
                'level_id' => $levelId
            ])
            ->toArray();
        if (empty($levelUnits)) {
            // No level units found for the given path and level ID
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => __('Error: No level units found for the given path and level ID.')
            ]));
        }

        // Update the sequence of each level unit based on the new order
        $levelUnitsByUnitId = [];
        foreach ($levelUnits as $levelUnit) {
            $levelUnitsByUnitId[$levelUnit->unit_id] = $levelUnit;
        }

        foreach ($unitOrder as $sequence => $unitId) {
            if (isset($levelUnitsByUnitId[$unitId])) {
                $levelUnitsByUnitId[$unitId]->sequence = $sequence + 1; // Sequence starts at 1
            }
        }

        // Save the updated level units
        if (!$this->getLevelUnitsTable()->saveMany($levelUnits)) {
            // Error saving level units
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => __('Error: Unable to update unit order.')
            ]));
        }

        // Return success response
        return $this->response->withStringBody(json_encode([
            'success' => true,
            'message' => __(
                'Path ' . $pathId . ' level ' . $levelId . ' unit order updated successfully.'
            )
        ]));
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
