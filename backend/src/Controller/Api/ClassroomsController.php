<?php

namespace App\Controller\Api;

use App\Exceptions\RequiredFieldException;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use App\Lib\UtilLibrary;
use App\Lib\HttpStatusCode;
use Cake\Log\Log;

class ClassroomsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    //START Teacher Schools API Calls
    //get school list and roles (user_id)
    public function getSchoolsAndRoles()
    {
        $requestData = $this->request->getData();
        $teachersRoleIds = $this->getSchoolRolesTable()->getRoleIds([
            UtilLibrary::SCHOOL_ROLE_TEACHER_STR,
            UtilLibrary::SCHOOL_ROLE_SUBSTITUTE_STR
        ]);

        if (isset($requestData['user_id'])) {
            $schoolUserOptions = array(
                'conditions' => array(
                    'user_id' => $requestData['user_id'],
                    'role_id IN' => $teachersRoleIds,
                ),
                'contain' => array(
                    'Schools'
                )
            );
            $response = $this->getSchoolUsersTable()->find('all', $schoolUserOptions);
            $this->sendApiData(true, 'School Role Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

//START Teacher Dashboard API Calls
    //gets students and unit details of classroom and school students if
    //school_id present (user_id, classroom_id, school_id)
    public function getTeacherClassroomUnitsAndStudents()
    {
        $requestData = $this->request->getData();
        $response = [
            'units' => [],
            'students' => [],
            'schoolTeachers' => [],
            'schoolStudents' => []
        ];
        if (isset($requestData['user_id'])) {
            if (isset($requestData['classroom_id'])) {
                $unitOptions = array(
                    'conditions' => array(
                        'classroom_id' => $requestData['classroom_id']
                    ),
                    'order' => 'sequence',
                    'contain' => array(
                        'Classrooms',
                        'LevelUnits',
                        'LevelUnits.Units'
                    )
                );
                $response['units'] = $this->getClassroomLevelUnitsTable()->find('all', $unitOptions);
                $studentOptions = array(
                    'conditions' => array(
                        'ClassroomUsers.classroom_id' => $requestData['classroom_id'],
                        'ClassroomUsers.role_id' => $this->getSchoolRolesTable()->getRoleId(UtilLibrary::SCHOOL_ROLE_STUDENT_STR)
                    ),
                    'contain' => array(
                        'Users',
                        'Users.Usersetting'
                    )
                );
                $response['students'] = $this->getClassroomUsersTable()->find('all', $studentOptions);
            }
            //school_id present if call is for Classroom Editor
            if (isset($requestData['school_id'])) {
                if (!isset($requestData['classroom_id'])) {
                    $teachersRoleIds = $this->getSchoolRolesTable()->getRoleIds([
                        UtilLibrary::SCHOOL_ROLE_TEACHER_STR,
                        UtilLibrary::SCHOOL_ROLE_SUBSTITUTE_STR
                    ]);
                    $schoolTeacherOptions = array(
                        'conditions' => array(
                            'SchoolUsers.school_id' => $requestData['school_id'],
                            'SchoolUsers.role_id IN' => $teachersRoleIds,
                        ),
                        'contain' => array(
                            'Users',
                            'Users.Usersetting'
                        )
                    );
                    $schoolStudentOptions = array(
                        'conditions' => array(
                            'SchoolUsers.school_id' => $requestData['school_id'],
                            'SchoolUsers.role_id' => $this->getSchoolRolesTable()->getRoleId(UtilLibrary::SCHOOL_ROLE_STUDENT_STR)
                        ),
                        'contain' => array(
                            'Users',
                            'Users.Usersetting'
                        )
                    );
                    $response['schoolTeachers'] = $this->getSchoolUsersTable()->find('all', $schoolTeacherOptions);
                } else {
                    $classroomStudentsForRemoval = $this->getClassroomUsersTable()
                        ->find()
                        ->select(['user_id'])
                        ->where(['classroom_id' => $requestData['classroom_id']]);
                    $schoolStudentOptions = array(
                        'conditions' => array(
                            'SchoolUsers.school_id' => $requestData['school_id'],
                            'SchoolUsers.role_id' => $this->getSchoolRolesTable()->getRoleId(UtilLibrary::SCHOOL_ROLE_STUDENT_STR),
                            'SchoolUsers.user_id NOT IN' => $classroomStudentsForRemoval
                        ),
                        'contain' => array(
                            'Users',
                            'Users.Usersetting'
                        )
                    );
                }
                $response['schoolStudents'] = $this->getSchoolUsersTable()->find('all', $schoolStudentOptions);
            }
            $this->sendApiData(true, 'Teacher Classroom Detail Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

    //get progress and recent activitity
    public function getStudentActivities()
    {
        $requestData = $this->request->getData();
        $limit = $requestData['limit'] ?? 10;
        $studentSchoolRoleId = $this->getSchoolRolesTable()->getRoleId(UtilLibrary::SCHOOL_ROLE_STUDENT_STR);

        if (isset($requestData['last_modified'])) {
            $lastModified = $requestData['last_modified'];
            $limit = null;
        } else {
            $lastModified = 0;
        }

        if (isset($requestData['classroom_id'])) {
            $classroomIds = [$requestData['classroom_id']];
        } else {
            $classroomIds = $this->getClassroomUsersTable()
                ->find()
                ->select(['classroom_id'])
                ->where([
                    'user_id' => $requestData['user_id'],
                    'role_id IN' => [$studentSchoolRoleId]
                ]);
        }
        if (isset($requestData['user_id'])) {
            //gather classrooms

            //gather students
            $studentIds = $this->getClassroomUsersTable()
                ->find()
                ->select(['user_id'])
                ->where([
                    'classroom_id IN' => $classroomIds,
                    'role_id' => $studentSchoolRoleId
                ]);
            //gather levels
            $levelIds = $this->getClassroomsTable()
                ->find()
                ->select(['level_id'])
                ->where(['id IN' => $classroomIds]);
            $unitIds = $this->getLevelUnitsTable()
                ->find()
                ->select(['unit_id'])
                ->where(['level_id IN' => $levelIds]);
            $studentProgressOptions = array(
                'conditions' => array(
                    'user_id IN' => $studentIds
                ),
                'contain' => array(
                    'UserUnitActivities' => array(
                        'conditions' => array(
                            'unit_id IN' => $unitIds
                        )
                    ),
                    'Usersetting'
                )
            );
            $unitProgress = $this->getUsersTable()->find('all', $studentProgressOptions);
            //gather recent activity from students for class levels

            $studentActivitiesOptions = array(
                'conditions' => array(
                    'user_id IN' => $studentIds
                ),
                'contain' => array(
                    'UserActivities' => array(
                        'conditions' => array(
                            'activity_type = "exercise"',
                            'UserActivities.modified >' . $lastModified
                        ),
                    ),
                    'UserActivities.cards',
                    'Usersetting'
                )
            );
            $activities = $this->getUsersTable()->find('all', $studentActivitiesOptions);
            $response = ['studentActivities' => $activities, 'studentProgress' => $unitProgress];
            $this->sendApiData(true, 'Student Activities Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

    public function updateTeacherMessage()
    {
        $requestData = $this->request->getData();
        if (isset($requestData['user_id'])) {
            $response = ['teacherLevels' => $teacherLevels];
            $this->sendApiData(true, 'Teacher Level Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }
//START Teacher Classroom API Calls (Also see getTeacherClassroomUnitsAndStudents)
    //gets list of classrooms (user_id, school_id)
    public function getTeacherClassrooms()
    {
        $requestData = $this->request->getData();
        if (isset($requestData['user_id']) && isset($requestData['school_id'])) {
            //gather subscribed units
            //find school_ids related to current user
            $teachersRoleIds = $this->getSchoolRolesTable()->getRoleIds([
                UtilLibrary::SCHOOL_ROLE_TEACHER_STR,
                UtilLibrary::SCHOOL_ROLE_SUBSTITUTE_STR
            ]);
            $classroom_ids = $this->getClassroomUsersTable()
                ->find()
                ->select(['classroom_id'])
                ->where([
                    'user_id' => $requestData['user_id'],
                    'role_id IN' => $teachersRoleIds,
                ]);
            $classroomOptions = array(
                'conditions' => array(
                    'Classrooms.id IN' => $classroom_ids,
                    'Classrooms.school_id' => $requestData['school_id']
                ),
                'order' => array(
                    'Classrooms.modified' => 'desc'
                ),
                'contain' => array(
                    'Levels'
                )
            );
            $response = $this->getClassroomsTable()->find('all', $classroomOptions);

            //get units for class

            $this->sendApiData(true, 'Teacher Classroom Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

    public function getClassroomData()
    {
        $requestData = $this->request->getData();
        if (isset($requestData['user_id']) && isset($requestData['type']) && isset($requestData['params'])) {
            $params = $requestData['params'];
            switch ($requestData['type']) {
                case 'checkWordlink':
                    if (isset($params['wordlink'])) {
                        $wordlinkEntry = $this->getWordlinksTable()->find()->where(['wordlink' => $params['wordlink']])->first();
                        if (isset($wordlinkEntry->classroom_id) && $wordlinkEntry->classroom_id != "") {
                            $classroom = $this->getClassroomsTable()->get($wordlinkEntry->classroom_id);
                            $status = true;
                        } else {
                            $classroom = '';
                        }
                        if (isset($wordlinkEntry->school_id) && $wordlinkEntry->school_id != "") {
                            $school = $this->getSchoolsTable()->get($wordlinkEntry->school_id);
                            $status = true;
                        } else {
                            $school = '';
                        }
                        $msg = "Wordlink Success";
                        if (!isset($status)) {
                            $status = false;
                            $msg = "Failed to find wordlink";
                        }
                        $data = ['classroom' => $classroom, 'school' => $school];
                    }
                    break;
                default:
                    $msg = 'Something went wrong';
                    $status = false;
                    $data = array();
                    break;
            }
            $this->sendApiData($status, $msg, $data);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

    // ChatGPT Start
    public function updateClassroomData()
    {
        $requestData = $this->request->getData();
        if (!isset($requestData['user_id'], $requestData['type'], $requestData['params'])) {
            $this->sendApiData(false, 'Not a valid teacher request. Missing user ID.', [], HttpStatusCode::BAD_REQUEST);
            return;
        }

        $params = $requestData['params'];
        $data = [];

        switch ($requestData['type']) {
            case 'newClassroom':
                $classroomEntity = $this->createClassroom($params, $requestData['user_id']);
                if (!$classroomEntity) {
                    $this->sendApiData(false, 'Failed to save classroom.', [], HttpStatusCode::INTERNAL_SERVER_ERROR);
                    return;
                }
                $this->sendApiData(true, 'Classroom saved successfully.', $classroomEntity->toArray());
                return;

            case 'updateClassroom':
                $classroomEntity = $this->getClassroomsTable()->get($params['id']);
                $classroomEntity = $this->getClassroomsTable()->patchEntity($classroomEntity, $params);
                if (!$this->getClassroomsTable()->save($classroomEntity)) {
                    $this->sendApiData(false, $this->getFirstError($classroomEntity), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
                    return;
                }
                $this->sendApiData(true, 'Classroom saved successfully.', $classroomEntity->toArray());
                return;

            case 'updateClassroomUnit':
                $classroomLevelUnitsEntity = $this->getClassroomLevelUnitsTable()->get($params['id']);
                $classroomLevelUnitsEntity = $this->getClassroomLevelUnitsTable()->patchEntity($classroomLevelUnitsEntity, $params);
                if (!$this->getClassroomLevelUnitsTable()->save($classroomLevelUnitsEntity)) {
                    $this->sendApiData(false, 'Failed to save ClassroomLevelUnit.', [], HttpStatusCode::INTERNAL_SERVER_ERROR);
                    return;
                }
                $this->sendApiData(true, 'ClassroomLevelUnit saved successfully.', $classroomLevelUnitsEntity->toArray());
                return;

            case 'updateClassroomStudents':
                $result = $this->updateClassroomStudents($params);
                $statusCode = $result['status'] ? HttpStatusCode::OK : HttpStatusCode::INTERNAL_SERVER_ERROR;
                $this->sendApiData($result['status'], $result['msg'], [], $statusCode);
                return;

            case 'newTeacherLevel':
                $result = $this->createNewTeacherLevel($params, $requestData['user_id']);
                $statusCode = $result['status'] ? HttpStatusCode::CREATED : HttpStatusCode::INTERNAL_SERVER_ERROR;
                $this->sendApiData($result['status'], $result['msg'], [], $statusCode);
                return;

            case 'updateTeacherLevelUnits':
                $result = $this->updateTeacherLevelUnits($requestData);
                $statusCode = $result['status'] ? HttpStatusCode::OK : HttpStatusCode::INTERNAL_SERVER_ERROR;
                $this->sendApiData($result['status'], $result['msg'], [], $statusCode);
                return;

            case 'addNewSchoolUsers':
                $result = $this->addNewSchoolUsers($params);
                $statusCode = $result['status'] ? HttpStatusCode::CREATED : HttpStatusCode::INTERNAL_SERVER_ERROR;
                $this->sendApiData($result['status'], $result['msg'], [], $statusCode);
                return;

            case 'generateWordlink':
                $result = $this->generateAndSaveWordlink($params);
                $statusCode = $result['status'] ? HttpStatusCode::CREATED : HttpStatusCode::INTERNAL_SERVER_ERROR;
                $this->sendApiData($result['status'], $result['msg'], $result['data'], $statusCode);
                return;

            default:
                $this->sendApiData(false, 'Invalid request type', [], HttpStatusCode::BAD_REQUEST);
                return;
        }
    }

    private function createClassroom($params, $userId)
    {
        $classroomEntity = $this->getClassroomsTable()->newEmptyEntity();
        $params['teacher_message'] = "Welcome to " . $params['name'];
        $classroomEntity = $this->getClassroomsTable()->patchEntity($classroomEntity, $params);
        if ($this->getClassroomsTable()->save($classroomEntity)) {
            $this->addClassroomLevelUnits($classroomEntity['id'], $params['level_id'], $params['school_id']);
            $this->addClassroomUser($classroomEntity['id'], $userId, 2);
            return $classroomEntity;
        } else {
            return null;
        }
    }

    private function addClassroomLevelUnits($classroomId, $levelId, $schoolId)
    {
        $levelUnitIds = $this->getLevelUnitsTable()
            ->find()
            ->select('id')
            ->where(['level_id' => $levelId]);

        foreach ($levelUnitIds as $levelUnitId) {
            $classroomLevelUnitsEntity = $this->getClassroomLevelUnitsTable()->newEmptyEntity();
            $classroomLevelUnitsParams = [
                'level_units_id' => $levelUnitId->id,
                'school_id' => $schoolId,
                'classroom_id' => $classroomId
            ];
            $classroomLevelUnitsEntity = $this->getClassroomLevelUnitsTable()
                ->patchEntity($classroomLevelUnitsEntity, $classroomLevelUnitsParams);
            if (!$this->getClassroomLevelUnitsTable()->save($classroomLevelUnitsEntity)) {
                Log::error("Error saving classroom level units. " . $this->getFirstError($classroomLevelUnitsEntity));
            }
        }
    }

    private function addClassroomUser($classroomId, $userId, $roleId)
    {
        // Check if user is already in the classroom
        $existingEntry = $this->getClassroomUsersTable()
            ->find()
            ->where([
                'classroom_id' => $classroomId,
                'user_id' => $userId
            ])
            ->first();

        if ($existingEntry) {
            Log::info("User {$userId} already exists in classroom {$classroomId}.");
            return true;
        }

        // Create new entity
        $classroomUsersEntity = $this->getClassroomUsersTable()->newEmptyEntity();
        $classroomUsersParams = [
            'classroom_id' => $classroomId,
            'user_id' => $userId,
            'role_id' => $roleId
        ];
        $classroomUsersEntity = $this->getClassroomUsersTable()->patchEntity($classroomUsersEntity, $classroomUsersParams);

        // Save entity
        if (!$this->getClassroomUsersTable()->save($classroomUsersEntity)) {
            $errorMessage = $classroomUsersEntity->getErrors()
                ? json_encode($classroomUsersEntity->getErrors())
                : 'Unknown error';
            Log::error("Error saving classroom user. " . $errorMessage);
            return false; // Return failure
        }

        return true; // Return success
    }

    private function updateClassroomStudents($params)
    {
        if (!empty($params)) {
            $classroomId = $params[0]['classroom_id'];
            $paramStudentIds = array_map(function ($value) {
                return $value['id'];
            }, $params);

            $this->getClassroomUsersTable()->deleteAll([
                'classroom_id' => $classroomId,
                'role_id' => $this->getSchoolRolesTable()->getRoleId(UtilLibrary::SCHOOL_ROLE_STUDENT_STR),
                'id NOT IN' => $paramStudentIds
            ]);

            $classroomStudents = $this->getClassroomUsersTable()
                ->find()
                ->where([
                    'classroom_id' => $classroomId,
                    'role_id' => $this->getSchoolRolesTable()->getRoleId(UtilLibrary::SCHOOL_ROLE_STUDENT_STR)
                ])
                ->all()
                ->toList();

            $patchedClassroomStudents = $this->getClassroomUsersTable()
                ->patchEntities($classroomStudents, $params);

            $errorFlag = false;
            foreach ($patchedClassroomStudents as $studentEntity) {
                if (!$this->getClassroomUsersTable()->save($studentEntity)) {
                    Log::error("Error saving student. " . $this->getFirstError($studentEntity));
                    $errorFlag = true;
                }
            }

            if ($errorFlag) {
                return ['status' => false, 'msg' => 'An error occurred while saving students.'];
            } else {
                return ['status' => true, 'msg' => 'Classroom students saved successfully.'];
            }
        } else {
            return ['status' => false, 'msg' => 'Failed to save classroom students.'];
        }
    }

    private function createNewTeacherLevel($params, $userId)
    {
        $levelEntity = $this->getLevelsTable()->newEmptyEntity();
        if (!isset($params['image_id'])) {
            $params['image_id'] = 18;
        }
        if (isset($params['is_all_units']) && $params['is_all_units'] == 1) {
            $params['level_id'] = Configure::read('ALLUNITSLEVELID');
        }
        $levelParams = [
            'name' => $params['name'],
            'description' => $params['description'],
            'image_id' => $params['image_id']
        ];
        $levelEntity = $this->getLevelsTable()->patchEntity($levelEntity, $levelParams);
        if ($this->getLevelsTable()->save($levelEntity)) {
            $schoolLevelEntity = $this->addSchoolLevel($params['school_id'], $levelEntity->id, $userId);
            if ($schoolLevelEntity) {
                $pathLevelEntity = $this->addPathLevel($levelEntity->id);
                if (!$pathLevelEntity) {
                    $msg = 'Error saving path level. ' . $this->getFirstError($pathLevelEntity);
                    Log::error($msg);
                    return ['status' => false, 'msg' => $msg];
                }
                $this->populateLevelUnits($params, $levelEntity);
                return ['status' => true, 'msg' => 'Classroom saved successfully.'];
            } else {
                $msg = 'Error saving school level. ' . $this->getFirstError($schoolLevelEntity) . ' Deleting level.';
                Log::error($msg);
                $this->getLevelsTable()->delete($levelEntity);
                return ['status' => false, 'msg' => $msg];
            }
        } else {
            Log::error('Error saving level. ' . $this->getFirstError($levelEntity));
            return ['status' => false, 'msg' => $this->getFirstError($levelEntity)];
        }
    }

    private function addSchoolLevel($schoolId, $levelId, $ownerId)
    {
        $schoolLevelEntity = $this->getSchoolLevelsTable()->newEmptyEntity();
        $schoolLevelParams = [
            'school_id' => $schoolId,
            'level_id' => $levelId,
            'owner_id' => $ownerId
        ];
        $schoolLevelEntity = $this->getSchoolLevelsTable()->patchEntity($schoolLevelEntity, $schoolLevelParams);
        return $this->getSchoolLevelsTable()->save($schoolLevelEntity) ? $schoolLevelEntity : null;
    }

    private function addPathLevel($levelId)
    {
        $pathLevelsEntity = $this->getPathlevelTable()->newEmptyEntity();
        $pathLevelsParams = [
            'learningpath_id' => Configure::read('CLASSROOMPATHID'),
            'level_id' => $levelId
        ];
        $pathLevelsEntity = $this->getPathlevelTable()->patchEntity($pathLevelsEntity, $pathLevelsParams);
        return $this->getPathlevelTable()->save($pathLevelsEntity) ? $pathLevelsEntity : null;
    }

    private function populateLevelUnits($params, $levelEntity)
    {
        if (isset($params['level_id'])) {
            $levelUnits = $this->getLevelUnitsTable()
                ->find()
                ->where(['level_id' => $params['level_id']])
                ->all()
                ->toList();

            foreach ($levelUnits as $levelUnit) {
                $newLevelUnitParams = [
                    'id' => null,
                    'learningpath_id' => Configure::read('CLASSROOMPATHID'),
                    'level_id' => $levelEntity->id,
                    'unit_id' => $levelUnit->unit_id,
                    'sequence' => $levelUnit->sequence
                ];
                $newLevelUnit = $this->getLevelUnitsTable()->newEmptyEntity();
                $newLevelUnit = $this->getLevelUnitsTable()->patchEntity($newLevelUnit, $newLevelUnitParams);
                if (!$this->getLevelUnitsTable()->save($newLevelUnit)) {
                    $msg = $this->getFirstError($levelEntity);
                    Log::error($msg);
                }
            }
        }
    }

    private function updateTeacherLevelUnits($requestData)
    {
        $params = $requestData['params'];
        if (isset($params['level_units'])) {
            // Get level ID, which is same for all units in the level
            $levelId = $requestData['level_id'];
            $levelUnits = $params['level_units'];
            $levelUnitIds = array_column($levelUnits, 'id');
            $levelBelongsToAClassroom = !empty($params['classrooms']);
            $classroomIds = [];

            // Add learning path ID to each unit for proper saving
            foreach ($params['level_units'] as &$levelUnit) {
                if (is_null($levelUnit['learningpath_id'])) {
                    $levelUnit['learningpath_id'] = Configure::read('CLASSROOMPATHID');
                }
            }

            // Delete classroom level units that are no longer in the teacher's level units
            if ($levelBelongsToAClassroom) {
                $classroomIds = array_column($params['classrooms'], 'id');
                if (!empty($classroomIds)) {
                    if (empty($levelUnitIds)) {
                        $this->getClassroomLevelUnitsTable()->deleteAll(['classroom_id IN' => $paramClassroomIds]);
                    } else {
                        $this->getClassroomLevelUnitsTable()->deleteAll([
                            'classroom_id IN' => $classroomIds,
                            'level_units_id NOT IN' => $levelUnitIds
                        ]);
                    }
                }
            }

            // Delete level units that are no longer in the teacher's level units
            if (empty($levelUnitIds)) {
                $this->getLevelUnitsTable()->deleteAll(['level_id' => $levelId]);
            } else {
                $this->getLevelUnitsTable()->deleteAll([
                    'learningpath_id' => Configure::read('CLASSROOMPATHID'),
                    'level_id' => $levelId,
                    'id NOT IN' => $levelUnitIds
                ]);
            }

            // Get level units in this teacher level
            $levelUnits = $this->getLevelUnitsTable()
                ->find()
                ->where(['level_id' => $levelId])
                ->all()
                ->toList();

            // Patch the level units with the new data
            $patchedLevelUnits = $this->getLevelUnitsTable()->patchEntities($levelUnits, $params['level_units']);
            $errorFlag = false;

            // Save the patched level units
            foreach ($patchedLevelUnits as $LevelUnit) {
                if (!$this->getLevelUnitsTable()->save($LevelUnit)) {
                    $errorFlag = true;
                }
            }

            if ($errorFlag) {
                return ['status' => false, 'msg' => 'An error occurred while saving units.'];
            } else {
                if ($levelBelongsToAClassroom) {
                    $this->updateClassroomLevelUnits($levelId, $classroomIds, $levelUnitIds);
                }
                return ['status' => true, 'msg' => 'Lesson units saved successfully.'];
            }
        } else {
            return ['status' => false, 'msg' => 'Failed to save lesson units.'];
        }
    }

    private function updateClassroomLevelUnits($levelId, $paramClassroomIds, $paramLevelUnitIds)
    {
        $existingClassroomLevelUnitIds = $this->getClassroomLevelUnitsTable()
            ->find()
            ->select('level_units_id')
            ->distinct('level_units_id')
            ->where(['classroom_id IN' => $paramClassroomIds]);

        $newLevelUnitsIds = $this->getLevelUnitsTable()
            ->find()
            ->select('id')
            ->where([
                'level_id' => $levelId,
                'id NOT IN' => $existingClassroomLevelUnitIds
            ]);

        foreach ($newLevelUnitsIds as $newLevelUnitsId) {
            foreach ($paramClassroomIds as $paramClassroomId) {
                $classroomLevelUnitsEntity = $this->getClassroomLevelUnitsTable()->newEmptyEntity();
                $classroomLevelUnitsParams = [
                    'classroom_id' => $paramClassroomId,
                    'level_units_id' => $newLevelUnitsId->id
                ];
                $classroomLevelUnitsEntity = $this->getClassroomLevelUnitsTable()
                    ->patchEntity($classroomLevelUnitsEntity, $classroomLevelUnitsParams);

                if (!$this->getClassroomLevelUnitsTable()->save($classroomLevelUnitsEntity)) {
                    Log::error('An error has occurred during the classroom update. Please contact an administrator');
                }
            }
        }
    }

    private function addNewSchoolUsers($params)
    {
        if (!isset($params['school_id'])) {
            Log::error('Failed to save new users. School ID not provided.');
            return ['status' => false, 'msg' => 'Failed to save new users. School ID not provided.'];
        }

        $userId = null;
        $roleId = null;

        if (!empty($params['registered_user_id']['id'])) {
            $userId = $params['registered_user_id']['id'];
            $roleId = $params['registered_user_id']['role'];
        } elseif (!empty($params['existing_user_email']['email'])) {
            $userIdQuery = $this->getUsersTable()
                ->find()
                ->select(['id'])
                ->where(['email' => $params['existing_user_email']['email']])
                ->first();

            if (!$userIdQuery) {
                Log::error('Failed to add user to school. User not found.');
                return ['status' => false, 'msg' => 'Failed to add user to school. User not found.'];
            }

            $userId = $userIdQuery->id;
            $roleId = $params['existing_user_email']['role'];
        } else {
            Log::error('Failed to add user to school. User id and email not provided.');
            return ['status' => false, 'msg' => 'Failed to add user to school'];
        }

        // Check if user already exists in school
        $schoolUserAlreadyExists = false;
        $schoolUserEntity = $this->getSchoolUserEntity($userId, $params['school_id'], $roleId, $schoolUserAlreadyExists);

        if ($schoolUserAlreadyExists) {
            return ['status' => true, 'msg' => 'User already exists in school'];
        }

        // Save the school user
        $savedSuccessfully = $schoolUserEntity && $this->getSchoolUsersTable()->save($schoolUserEntity);

        // Only add to classroom if school user was saved successfully
        if ($savedSuccessfully && !empty($params['classroom_id'])) {
            $userAddedToClassroom = $this->addClassroomUser($params['classroom_id'], $userId, $roleId);
            return [
                'status' => $classroomUserResult,
                'msg' => $userAddedToClassroom
                    ? 'User added to school and classroom'
                    : 'User added to school. Failed to add user to classroom',
            ];
        }

        return ['status' => true, 'msg' => 'User added to school'];
    }

    private function getSchoolUserEntity($userId, $schoolId, $roleId, &$schoolUserAlreadyExists)
    {
        $preexistingSchoolUser = $this->getSchoolUsersTable()
            ->find()
            ->select(['id', 'role_id'])
            ->where(['user_id' => $userId, 'school_id' => $schoolId])
            ->first();

        if ($preexistingSchoolUser) {
            if ($preexistingSchoolUser->role_id < $roleId) {
                return $this->getSchoolUsersTable()->get($preexistingSchoolUser->id);
            }
            $schoolUserAlreadyExists = true;
            return null;
        }
        $params = [
            'user_id' => $userId,
            'school_id' => $schoolId,
            'role_id' => $roleId
        ];
        $schoolUserEntity = $this->getSchoolUsersTable()->newEmptyEntity();
        return $this->getSchoolUsersTable()->patchEntity($schoolUserEntity, $params);
    }

    private function generateAndSaveWordlink($params)
    {
        if (!isset($params['school_id'])) {
            return ['status' => false, 'msg' => 'Wordlink creation failed.', 'data' => []];
        }

        $wordlink = $this->generateWordlink();
        $wordlinkEntity = $this->getWordlinksTable()->newEmptyEntity();
        $wordlinkParams = [
            'wordlink' => $wordlink,
            'school_id' => $params['school_id']
        ];

        if (isset($params['classroom_id'])) {
            $wordlinkParams['classroom_id'] = $params['classroom_id'];
        }

        $wordlinkEntity = $this->getWordlinksTable()->patchEntity($wordlinkEntity, $wordlinkParams);
        if ($this->getWordlinksTable()->save($wordlinkEntity)) {
            return ['status' => true, 'msg' => 'New Wordlink Created', 'data' => $wordlinkParams];
        }

        return ['status' => false, 'msg' => 'Wordlink creation failed.', 'data' => []];
    }

    private function getFirstError($entity)
    {
        if ($entity === null) {
            return 'Unknown error.';
        }

        $errors = $entity->getErrors();
        if (!empty($errors)) {
            foreach ($errors as $fieldErrors) {
                foreach ($fieldErrors as $error) {
                    return $error;
                }
            }
        }
        return 'Unknown error.';
    }
    // ChatGPT End

    public function generateWordlink()
    {
        $wordLink = "";
        $lakotaWords = $this->getCardTable()->find('all', array(
            'fields' => array('lakota'),
            'order' => 'rand()',
            'limit' => 3,
        ));
        foreach ($lakotaWords as $lakotaWord) {
            preg_match('/^([^\s]+)/', $lakotaWord->lakota, $matches);
            $search = array('Á', 'É', 'Í', 'Ó', 'Ú', 'Ŋ', 'Č', 'Ȟ', 'Š', 'Ȟ', 'Ž', 'Ǧ', '’', '.', ',', '-', '?', 'ʼ');
            $replace = array('A', 'E', 'I', 'O', 'U', 'N', 'C', 'H', 'S', 'H', 'Z', 'G', '', '', '', '', '', '');
            $wordLink .= str_replace($search, $replace, mb_strtoupper($matches[0]));
        }
        return $wordLink;
    }

//START Teacher Lessons API Calls
    //gather levels from user's school with ownership
    /**
     * @throws RequiredFieldException
     */
    public function deleteClassroom(): void
    {
        $requestData = $this->request->getData();

        $this->validateRequest($requestData, [
            'user_id',
            'school_id',
            'classroom_id'
        ]);

        $classroom = $this->getClassroomsTable()->find()->where(['id' => $requestData['classroom_id']])->first();

        // Delete units linked to classroom
        $this->getClassroomLevelUnitsTable()->deleteAll(['classroom_id' => $requestData['classroom_id']]);

        // Delete users linked to classroom
        $this->getClassroomUsersTable()->deleteAll(['classroom_id' => $requestData['classroom_id']]);

        if ($classroom !== null) {
            $this->getClassroomsTable()->delete($classroom);
        } else {
            $this->sendApiData(false, 'Classroom not found');
        }

        //gather subscribed units
        //find school_ids related to current user
        $teachersRoleIds = $this->getSchoolRolesTable()->getRoleIds([
            UtilLibrary::SCHOOL_ROLE_TEACHER_STR,
            UtilLibrary::SCHOOL_ROLE_SUBSTITUTE_STR
        ]);
        $classroomIds = $this->getClassroomUsersTable()
            ->find()
            ->select(['classroom_id'])
            ->where([
                'user_id' => $requestData['user_id'],
                'role_id IN' => $teachersRoleIds,
            ]);
        $classroomOptions = array(
            'conditions' => array(
                'Classrooms.id IN' => $classroomIds,
                'Classrooms.school_id' => $requestData['school_id']
            ),
            'order' => array(
                'Classrooms.modified' => 'desc'
            ),
            'contain' => array(
                'Levels'
            )
        );
        $response = $this->getClassroomsTable()->find('all', $classroomOptions);


        $this->sendApiData(true, 'Teacher Classroom Data.', $response);
    }

    /**
     * @throws RequiredFieldException
     */
    public function archiveClassroom(): void
    {
        $data = $this->request->getData();
        $this->validateRequest($data, ['classroom_id']);

        $classroom = $this->getClassroomsTable()->get($data['classroom_id']);

        if (empty($classroom)) {
            $this->sendApiData(false, "Classroom couldn't be found.", [], HttpStatusCode::BAD_REQUEST);
            return;
        }

        $yesterday = FrozenDate::yesterday();
        $classroom->end_date = $yesterday->toDateString();
        if (!$this->getClassroomsTable()->save($classroom)) {
            $this->sendApiData(
                false,
                'Failed to set classroom end-date to today',
                [],
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }

        $this->sendApiData(true, "Classroom archived", $classroom);
    }

    public function getTeacherLevels()
    {
        $requestData = $this->request->getData();

        if (isset($requestData['user_id']) && isset($requestData['school_id'])) {
            $teachersRoleIds = $this->getSchoolRolesTable()->getRoleIds([
                UtilLibrary::SCHOOL_ROLE_TEACHER_STR,
                UtilLibrary::SCHOOL_ROLE_SUBSTITUTE_STR
            ]);
            $school = $this->getSchoolUsersTable()
                ->find()
                ->select(['school_id'])
                ->where([
                    'user_id' => $requestData['user_id'],
                    'role_id IN' => $teachersRoleIds,
                    'school_id' => $requestData['school_id']]);
            //get level ids for class
            $schoolLevelOptions = array(
                'conditions' => array(
                    'school_id' => $school
                ),
                'contain' => array(
                    'Levels',
                    'Levels.image',
                    'Levels.Classrooms'
                )
            );
            $schoolLevels = $this->getSchoolLevelsTable()->find('all', $schoolLevelOptions);
            $response = ['teacherLevels' => $schoolLevels];
            $this->sendApiData(true, '"Lesson Units Saved Successfully."', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

    public function getTeacherLevelUnits()
    {
        $requestData = $this->request->getData();
        if (isset($requestData['user_id']) && isset($requestData['level_id'])) {
            $teacherPortalPathId = Configure::read('CLASSROOMPATHID', 'failed');
            if ($teacherPortalPathId === 'failed') {
                $this->sendApiData(false, 'Failed to get teacher portal path id', [], HttpStatusCode::INTERNAL_SERVER_ERROR);
                return;
            }

            $levelUnits = $this->getLevelUnitsTable()->find()
                ->contain([
                    'Units' => [
                        'sort' => 'LevelUnits.sequence'
                    ]
                ])
                ->where([
                    'level_id' => $requestData['level_id'],
                    'learningpath_id' => $teacherPortalPathId
                ])
                ->order(['LevelUnits.sequence' => 'ASC']);
            $response = ['teacherLevelUnits' => $levelUnits];
            $this->sendApiData(true, 'Teacher Dashboard Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

    public function updateTeacherLevel()
    {
        $requestData = $this->request->getData();
        if (isset($requestData['user_id'])) {
            $response = ['teacherLevels' => $teacherLevels];
            $this->sendApiData(true, 'Teacher Level Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

    public function getUnitCards()
    {
        $requestData = $this->request->getData();
        if (isset($requestData['unit_id'])) {
            $cardIds = $this->getCardUnitsTable()
                ->find()
                ->select(['card_id'])
                ->where(['unit_id' => $requestData['unit_id']]);
            $cards = $this->getCardTable()
                ->find()
                ->where(['Cards.id IN' => $cardIds])
                ->join([
                    'image' => [
                        'table' => 'files',
                        'type' => 'INNER',
                        'conditions' => 'image.id = Cards.image_id',
                    ]
                ])
                ->toArray();
            $response = ['cards' => $cards];
            $this->sendApiData(true, 'Unit Detail Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

//START Teacher Admin API Calls

    public function getAvailablePaths()
    {
        $requestData = $this->request->getData();
        if (!isset($requestData['user_id'])) {
            $this->sendApiData(false, "Not a valid teacher request. Missing teacher's user ID", [], HttpStatusCode::BAD_REQUEST);
            return;
        }

        $publicPaths = $this->getLearningpathsTable()->find()
            ->contain([
                'Levels' => function ($q) {
                    return $q
                        ->contain([
                            'image', // Association defined in LevelsTable
                            'Units' => function ($q) {
                                // Contain units and order by LevelUnits.sequence
                                return $q
                                    ->select(['Units.id', 'Units.name', 'Units.description'])
                                    ->contain([
                                        'LevelUnits' => function ($q) {
                                            return $q
                                                ->order(['LevelUnits.sequence' => 'ASC'])
                                                ->select(['LevelUnits.sequence', 'LevelUnits.unit_id', 'LevelUnits.level_id', 'LevelUnits.learningpath_id']);
                                        }
                                    ])
                                    ->order(['LevelUnits.sequence' => 'ASC']);
                            },
                            'Pathlevel',

                        ])
                        ->select(['Levels.id', 'Levels.name', 'Levels.image_id'])
                        ->order(['Pathlevel.sequence' => 'ASC']);
                }
            ])
            ->select([
                'Learningpaths.id',
                'Learningpaths.label',
                'Learningpaths.description',
            ])
            ->where(['Learningpaths.user_access' => '1'])
            ->formatResults(function (\Cake\Collection\CollectionInterface $results) {
                return $results->map(function ($row) {
                    // Each row is a Learningpath entity
                    // Remove unnecessary _joinData and simplify the result structure
                    foreach ($row->levels as $level) {
                        unset($level->_joinData);
                        unset($level->pathlevel);
                        foreach ($level->units as $index => $unit) {
                            foreach ($unit->level_units as $levelUnit) {
                                if ($levelUnit->unit_id === $unit->id && $levelUnit->level_id === $level->id && $levelUnit->learningpath_id === $row->id) {
                                    $unit->sequence = $levelUnit->sequence;
                                    break;
                                }
                            }
                            unset($unit->_joinData);
                            unset($unit->level_units);
                        }
                    }
                    return $row;
                });
            })
            ->toArray();

        $this->sendApiData(true, 'Public Paths', ['availablePaths' => $publicPaths]);
    }

    public function searchAvailableStudents()
    {
        $requestData = $this->request->getData();
        if (isset($requestData['user_id'])) {
            $response = ['teacherLevels' => $teacherLevels];
            $this->sendApiData(true, 'Teacher Level Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

    public function getSchoolStudents()
    {
        $requestData = $this->request->getData();
        if (isset($requestData['user_id'])) {
            $response = ['teacherLevels' => $teacherLevels];
            $this->sendApiData(true, 'Teacher Level Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

    public function addSchoolStudents()
    {
        $requestData = $this->request->getData();
        if (isset($requestData['user_id'])) {
            $response = ['teacherLevels' => $teacherLevels];
            $this->sendApiData(true, 'Teacher Level Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }

    public function updateSchoolDetails()
    {
        $requestData = $this->request->getData();
        if (isset($requestData['user_id'])) {
            $response = ['teacherLevels' => $teacherLevels];
            $this->sendApiData(true, 'Teacher Level Data.', $response);
        } else {
            $this->sendApiData(false, 'Not a valid teacher request', '');
        }
    }
}
