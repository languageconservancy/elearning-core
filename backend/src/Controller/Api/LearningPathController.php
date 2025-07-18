<?php

namespace App\Controller\Api;

use Cake\Core\Configure;
use App\Lib\UtilLibrary;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Log\Log;

const MIN_CARDS_REQUIRED_FOR_REVIEW = 2;

class LearningPathController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    // Api function for all the path and get path by id.
    public function getPath()
    {
        $requestData = $this->request->getData();

        if (isset($requestData['path_id'])) {
            $learningpath_id = $requestData['path_id'];
            $path = $this->getLearningpathsTable()->find()->where(['id =' => $learningpath_id]);
        } else {
            $authUser = $this->getAuthUser();
            if (!isset($authUser)) {
                throw new UnauthorizedException('Invalid username or password');
            }
            $user = $this->getUserById($authUser['id']);
            $userRoleId = $user[0]['role']['id'];
            $privilegedRoleIds = $this->getRolesTable()->getRoleIdsThatCanAccessAllPaths();
            if (in_array($userRoleId, $privilegedRoleIds)) {
                // Get all paths for admins
                $path = $this->getLearningpathsTable()->find()
                    ->where(['OR' => ['admin_access =' => 1, 'user_access =' => 1]]);
            } else {
                // Only get paths that are accessible to users
                $path = $this->getLearningpathsTable()->find()->where(['user_access =' => 1]);
            }
        }
        $this->sendApiData(true, 'Result return successfully.', $path);
    }

    public function getPathDetails()
    {
        $requestData = $this->request->getData();
        $user = $this->getAuthUser();
        list($status, $msg, $path) = $this->handleGetPathDetails($requestData, $user);
        $this->sendApiData($status, $msg, $path);
    }

    private function handleGetPathDetails($requestData, $user)
    {
        if (!isset($requestData['path_id']) && !isset($requestData['type'])) {
            return [false, 'Result could not be fetched due to missing path_id.', []];
        }

        $isClassroom = isset($requestData['type']) && $requestData['type'] == 'classroom';

        if ($isClassroom) {
            $path = $this->getClassroomPath($user);
        } else {
            $path = $this->getLearningPath($requestData['path_id']);
        }

        $this->processPathLevels($path, $user, $isClassroom);

        return [true, 'Retrieved path details successfully.', $path];
    }

    private function getClassroomPath($user)
    {
        $classroomIds = $this->getClassroomUsersTable()
            ->find()
            ->select(['classroom_id'])
            ->where(['user_id' => $user['id']]);

        $classroomLevelIds = $this->getClassroomsTable()
            ->find()
            ->select('level_id')
            ->where(['id IN' => $classroomIds]);

        $pathOptions = [
            'contain' => [
                'Levels' => [
                    'conditions' => ['Levels.id IN' => $classroomLevelIds]
                ],
                'Levels.image',
                'Levels.Units' => ['sort' => 'sequence']
            ]
        ];

        $classroomPath = $this->getLearningpathsTable()->get(Configure::read('CLASSROOMPATHID'), $pathOptions);

        $classrooms = $this->getClassroomsTable()
            ->find()
            ->where(['id IN' => $classroomIds]);

        $classroomLevels = $this->getClassroomLevels($classrooms, $classroomPath);

        return (object)[
            "id" => Configure::read('CLASSROOMPATHID'),
            "label" => "Classrooms",
            "description" => "Levels for students by teachers.",
            "admin_access" => "1",
            "user_access" => "1",
            "image_id" => 4524,
            "owner_id" => null,
            "created" => "2018-08-10T12:05:01+00:00",
            "modified" => "2018-10-04T06:43:12+00:00",
            "levels" => $classroomLevels,
            "FullImageUrl" => "https://owoksape.s3.us-west-2.amazonaws.com/levelTestIcon1528122009.png"
        ];
    }

    private function getClassroomLevels($classrooms, $classroomPath)
    {
        $classroomLevels = [];
        foreach ($classrooms as $classroom) {
            foreach ($classroomPath->levels as $level) {
                if ($classroom->level_id == $level->id) {
                    $classroomLevel = clone $level;
                    break;
                }
            }
            $classroomTeachers = $this->getClassroomTeachers($classroom);
            if (isset($classroomLevel)) {
                $this->populateClassroomLevel($classroom, $classroomLevel, $classroomTeachers);
                $classroomLevels[] = $classroomLevel;
            }
        }
        return $classroomLevels;
    }

    private function getClassroomTeachers($classroom)
    {
        $teacherRoleIds = $this->getSchoolRolesTable()->getRoleIds(
            [UtilLibrary::SCHOOL_ROLE_TEACHER_STR, UtilLibrary::SCHOOL_ROLE_SUBSTITUTE_STR]
        );

        $classroomTeacherOptions = [
            'conditions' => [
                'ClassroomUsers.classroom_id' => $classroom->id,
                'ClassroomUsers.role_id IN' => $teacherRoleIds
            ],
            'contain' => [
                'Users',
                'Users.Usersetting'
            ]
        ];

        return $this->getClassroomUsersTable()->find('all', $classroomTeacherOptions);
    }

    private function populateClassroomLevel($classroom, &$classroomLevel, $classroomTeachers)
    {
        $classroomLevel->id = $classroom['id'];
        $classroomLevel->name = $classroom['name'];
        $classroomLevel->description = $classroom['teacher_message'];
        $classroomLevel->start_date = $classroom['start_date'];
        $classroomLevel->end_date = $classroom['end_date'];
        $classroomLevel->teachers = $classroomTeachers;

        foreach ($classroomLevel->units as $classroomLevelUnit) {
            $classroomLevelUnit->classroomLevelUnits = $this->getClassroomLevelUnitsTable()
                ->find()
                ->where([
                    'level_units_id' => $classroomLevelUnit->_joinData->id,
                    'classroom_id' => $classroomLevel->id
                ])
                ->first();
        }
    }

    private function getLearningPath($pathId)
    {
        $pathOptions = [
            'contain' => [
                'Levels' => ['sort' => 'Pathlevel.sequence'],
                'Levels.image',
                'Levels.Units' => [
                    'sort' => 'sequence',
                    'conditions' => ['learningpath_id' => $pathId]
                ]
            ]
        ];

        return $this->getLearningpathsTable()->get($pathId, $pathOptions);
    }

    private function processPathLevels(&$path, $user, $isClassroom)
    {
        $lc = 1;
        $previousLevelComplete = false;

        foreach ($path->levels as $levelKey => $level) {
            $this->processLevel($level, $user, $isClassroom, $lc, $previousLevelComplete);
            $lc++;
        }
    }

    private function processLevel(&$level, $user, $isClassroom, $lc, &$previousLevelComplete)
    {
        $uc = 1;
        $previousUnitComplete = false;
        $previousReviewComplete = false;
        $previousUnitOptionalAndEnabled = false;
        $previousUnitId = null;
        $allComplete = true;

        $level->enable = ($lc == 1 || $previousLevelComplete);

        $privilegedRoleIds = $this->getRolesTable()->getRoleIdsThatHaveAllContentUnlocked();

        if (
            in_array($user['role_id'], $privilegedRoleIds) && !$isClassroom
        ) {
            $level->enable = true;
        }

        foreach ($level->units as $key => $unit) {
            $this->processUnit($unit, $user, $isClassroom, $lc, $uc, $level, $previousLevelComplete, $previousUnitComplete, $previousReviewComplete, $previousUnitOptionalAndEnabled, $previousUnitId, $allComplete);
            $uc++;
        }

        $previousLevelComplete = $allComplete;
    }

    private function processUnit(&$unit, $user, $isClassroom, $lc, $uc, &$level, &$previousLevelComplete, &$previousUnitComplete, &$previousReviewComplete, &$previousUnitOptionalAndEnabled, &$previousUnitId, &$allComplete)
    {
        $unit->noReview = false;

        // Get unit progress percentage out of 100
        $percentage = $this->getUnitProgressPercentage($unit->id, $user['id']);

        $unit->unitComplete = $percentage >= 100;
        $unit->unitPercentage = $percentage;
        $unit->unitFire = $this->getUnitFire($unit->id, $user['id']);

        if (!$isClassroom && $uc == 1 && ($lc == 1 || $previousLevelComplete)) {
            // Enable first unit of first level
            $unit->enable = true;
            $previousUnitOptionalAndEnabled = $unit->_joinData->optional == 1;
        } else {
            if ($previousLevelComplete) {
                $previousLevelComplete = false;
            }

            if (!$unit->unitComplete && $unit->_joinData->optional != 1) {
                // Unit is incomplete or non-optional
                $allComplete = false;
            }

            if ($previousUnitId && $previousUnitComplete) {
                // Get number of review cards in previous unit
                $numReviewCardsInPreviousUnit = $this->getCardUnitsTable()->numReviewCardsInUnit($previousUnitId);


                // If previous unit complete, check how many review questions user has correctly answered
                $reviewCounterData = $this->getReviewCountersTable()
                    ->find()
                    ->where(['user_id' => $user['id'], 'unit_id' => $previousUnitId])
                    ->first();

                // Compute number of review activities that need to be completed to unlock next unit
                // If < 2, 0
                // If > 2, amount * multiplier
                $numCorrectReviewAnswersToUnlockUnit = UtilLibrary::numCorrectReviewAnswersToUnlockUnit($numReviewCardsInPreviousUnit);

                // Check if required number of correct activities have been completed for previous unit
                $previousReviewComplete = $reviewCounterData && $reviewCounterData['counter'] >= $numCorrectReviewAnswersToUnlockUnit;

                $insufficientCardsInPreviousUnitReview = $numReviewCardsInPreviousUnit < MIN_CARDS_REQUIRED_FOR_REVIEW;
                $level->units[$uc - 2]->numReviewCards = $numReviewCardsInPreviousUnit;

                // If previous does not have enough review cards, disable review for it
                if (!$previousReviewComplete && $insufficientCardsInPreviousUnitReview) {
                    // minus 2 because $uc starts at 1, units starts at 0, and we want to access the previous unit
                    $level->units[$uc - 2]->noReview = true;
                    $previousReviewComplete = true;
                }
            }

            $unit->enable = $previousUnitComplete && $previousReviewComplete || $previousUnitOptionalAndEnabled;
            $previousUnitOptionalAndEnabled = $unit->enable && $unit->_joinData->optional == 1;
        }

        $previousUnitComplete = $unit->unitComplete;
        $previousUnitId = $unit->id;

        $privilegedRoleIds = $this->getRolesTable()->getRoleIdsThatHaveAllContentUnlocked();

        if (
            in_array($user['role_id'], $privilegedRoleIds) && !$isClassroom
        ) {
            $unit->enable = true;
        }
    }

    // Api to get path and all of its details by $id

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

    //first function in api call to load exercises/lessons

    /**
     * Check if there are cards to review for the specified unit.
     * @param $unitId ID of unit of which to check cards
     * @return true if no cards to review for this unit, otherwise false
     */
    private function noReviewCardsInThisUnit($unitId)
    {
        $numReviewCards = $this->getCardUnitsTable()->numReviewCardsInUnit($unitId);
        if ($numReviewCards < MIN_CARDS_REQUIRED_FOR_REVIEW) {
            return true;
        } else {
            return false;
        }
    }

    public function getUnitDetails()
    {
        $requestData = $this->request->getData();
        $user = $this->getAuthUser();

        $isUnitComplete = false;
        $resetUnit = false;

        if (!isset($requestData['path_id'])) {
            $this->sendApiData(false, 'Result could not be fetched.', array('Learning path id is missing.'));
            return;
        }

        $unitActivities = $this->getUnitAttemptIdAndIsCompleted(
            $requestData['level_id'],
            $requestData['unit_id'],
            $user['id']
        );
        $unitCompleteFlag = 1;
        $isUnitComplete = false;

        // If stored unit percentage is 100 everthing is complete always
        if ($unitActivities && isset($unitActivities['percent'])) {
            if ($unitActivities['percent'] >= 100) {
                $isUnitComplete = true;
            }
        }

        // Get exercises and lessons along with cards and frames
        $unitComponents = $this->getUnitComponents($requestData['unit_id']);
        $lastId = $unitActivities['last_id'] ?? 1;

        foreach ($unitComponents as $key => $unit) {
            $activityParams = [
                'unit_id' => $requestData['unit_id'],
                'user_id' => $user['id'],
                'user_unit_activity_id' => $lastId
            ];
            if ($unit->exercise_id != null) {
                $unit->flowType = 'exercise';
                $activityParams['exercise_id'] = $unit->exercise_id;
                $status = $this->isCompleted($activityParams);
                $unit->complete = $status['status'];
                $unit->attempted = $status['attempted'];
                if (!$status['status'] && $unitCompleteFlag != 0 && !$isUnitComplete) {
                    $unitCompleteFlag = 0;
                }
            }

            if ($unit->lesson_id != null) {
                $activityParams['lesson_id'] = $unit->lesson_id;
                $status = $this->isCompleted($activityParams);
                $unit->complete = $status['status'];
                $unit->attempted = $status['attempted'];
                $unit->flowType = 'lesson';
                if (!$status['status'] && $unitCompleteFlag != 0 && !$isUnitComplete) {
                    $unitCompleteFlag = 0;
                }
            }
        }

        /* Check if unit was completed */
        if ($unitCompleteFlag == 1) {
            $isUnitComplete = true;
        }

        if ($isUnitComplete == true) {
            foreach ($unitComponents as $unit) {
                $unit->complete = true;
                $unit->attempted = true;
            }
        }

        $this->sendApiData(true, 'Result return successfully.', $unitComponents);
    }

    private function getUnitComponents($unitId)
    {
        $unitOptions = array(
            'conditions' => array(
                'unit_id IS' => $unitId//, Removed constraint for teacher portal
            ),
            'order' => 'sequence',
            'contain' => array(
                'Lessons',
                'Lessons.Lessonframes',
                'Lessons.Lessonframes.LessonFrameBlocks',
                'Exercises',
            )
        );
        return $this->getUnitdetailsTable()->find('all', $unitOptions);
    }
}
