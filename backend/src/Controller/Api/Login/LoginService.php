<?php

namespace App\Controller\Api\Login;

use App\Controller\Api\AppController;
use Cake\Log\Log;
use Cake\Http\ServerRequest;
use App\Lib\HttpStatusCode;
use Exception;

abstract class LoginService extends AppController
{
    /**
     * LoginService constructor.
     */
    public function __construct(?ServerRequest $request = null)
    {
        parent::__construct($request);
    }

    // preserve payload
    protected array $payload;

    /**
     * @return LoginServiceResponse|null
     */
    abstract public function login(): ?LoginServiceResponse;

    /**
     * @return LoginServiceResponse|null
     */
    abstract public function signup(): ?LoginServiceResponse;


    /**
     * Get user by social platform and their respective id.
     * Field can be apple, google or fb
     *
     * @param string $id
     * @param string $field
     * @return array|null
     */
    public function getUserByDbField(string $field, string $value, string $registrationType): ?array
    {
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
        $users = $this->getUsersTable()->find('all', ['contain' => $contain])
            ->where(['Users.' . $field . ' =' => $value])->toArray();
        if (!empty($users)) {
            $users['classroom_count'] = $this->getClassroomUsersTable()->find()
                ->distinct('classroom_id')
                ->where(['user_id' => $users[0]['id']])
                ->count();
            if ($users[0][$field] == $value) {
                $users[0]['registration_type'] = $registrationType;
                return $users;
            } else {
                return null;
            }
        }
        return null;
    }

    public function setUpNewUser($userData, $idField, $loginType, $validation = 'default'): LoginServiceResponse
    {
        // Set up new user entity
        $user = $this->getUsersTable()->newEmptyEntity();
        $userData = $this->setUserDefaults($userData);

        // Try to save user entity to database
        $user = $this->getUsersTable()->patchEntity($user, $userData, ['validate' => $validation]);
        $savedUser = $this->getUsersTable()->save($user, ['validate' => $validation]);

        if (empty($savedUser)) {
            $msg = $this->extractObjErrorMsgs($user);
            return new LoginServiceResponse(
                $msg,
                false,
                [],
                HttpStatusCode::BAD_REQUEST
            );
        }

        // Save successful. Set settings defaults
        $status = $this->setUserSettingsDefaults($user['id']);

        if (empty($status)) {
            return new LoginServiceResponse(
                "Account created, but user settings not saved.",
                false,
                [],
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }

        $users = array();
        // Get user that we just saved
        $users = $this->getUserByDbField($idField, $userData[$idField], $loginType);
        if (empty($users)) {
            return new LoginServiceResponse(
                "Account created, but failed to get final user",
                false,
                [],
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }

        $users[0]['firstLogin'] = true;
        $lastLoggedInfo = array('last_logged' => date('Y-m-d H:i:s'));
        $finalUser = $this->updateUserData($user['id'], $lastLoggedInfo);
        if (empty($finalUser)) {
            return new LoginServiceResponse(
                "Account created, but extra info not saved",
                false,
                $users,
                HttpStatusCode::OK
            );
        }

        return new LoginServiceResponse(
            "Account created successfully",
            true,
            $users,
            HttpStatusCode::OK
        );
    }
}
