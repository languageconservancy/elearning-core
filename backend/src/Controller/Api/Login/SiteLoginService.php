<?php

namespace App\Controller\Api\Login;

use App\Exceptions\RequiredFieldException;
use Cake\Log\Log;
use Cake\Http\ServerRequest;
use App\Lib\HttpStatusCode;
use App\Lib\ErrorCode;

class SiteLoginService extends LoginService
{
    private $user;

    public function __construct($user, ?ServerRequest $request = null)
    {
        parent::__construct($request);
        $this->user = $user;
        $this->payload = $request->getData();
    }

    /**
     * Handles regular login with username/password.
     *
     * @return LoginServiceResponse|null
     */
    public function login(): ?LoginServiceResponse
    {
        if (isset($this->user) && isset($this->user['id'])) {
            if ($this->user['is_active'] == '1') {
                $data = array('last_logged' => date('Y-m-d H:i:s'));
                $result = $this->updateUserData($this->user['id'], $data);
                $userData = $this->getUserById($this->user['id']);
                $this->updateAgeFromDob($this->user['id']);

                return new LoginServiceResponse(
                    "You are logged in.",
                    true,
                    $userData,
                    HttpStatusCode::OK
                );
            } else {
                return new LoginServiceResponse(
                    "Your account is currently deactivated. Please contact us for support using the Feedback tab.",
                    false,
                    [],
                    HttpStatusCode::NOT_ACCEPTABLE
                );
            }
        } else {
            return new LoginServiceResponse(
                "Invalid email or password, please try again.",
                false,
                [],
                HttpStatusCode::BAD_REQUEST
            );
        }
    }

    /**
     * Creates user with username/password.
     *
     * @return LoginServiceResponse|null
     * @throws RequiredFieldException
     */
    public function signup(): ?LoginServiceResponse
    {
        $data = $this->payload;
        $this->validateRequest($data, [
            'name',
            'dob',
            'email',
            'password'
        ]);
        $statusCode = HttpStatusCode::OK;

        // Check if user already exists in db
        $user = $this->getUserByDbField('email', $data['email'], 'site');
        if (!empty($user)) {
            return new LoginServiceResponse(
                "User with this email already exists.",
                false,
                ['errorCode' => ErrorCode::EMAIL_ALREADY_REGISTERED],
                HttpStatusCode::BAD_REQUEST
            );
        }

        if ($this->request->is('post')) {
            if ($this->getBannedWordsTable()->presentInText($data['name'])) {
                return new LoginServiceResponse(
                    "Usernames cannot contain inappropriate language",
                    false,
                    ['errorCode' => ErrorCode::USERNAME_INAPPROPRIATE],
                    HttpStatusCode::BAD_REQUEST
                );
            }
            if (!empty($data['dob'])) {
                $data['dob'] = date('Y-m-d', strtotime($data['dob']));
            }
            // compute age from dob
            $data['age'] = $this->getAgeFromDob($data['dob']);
            //set default path and speed
            $data = $this->setUserDefaults($data);
            $user = $this->getUsersTable()->newEmptyEntity();
            $user = $this->getUsersTable()->patchEntity($user, $data);
            if ($this->getUsersTable()->save($user)) {
                $msg = 'You have registered Successfully.';
                if ($this->setUserSettingsDefaults($user['id'])) {
                    $data = $this->getUserById($user['id']);
                    if (!empty($data)) {
                        $data[0]['firstLogin'] = true;
                    }
                    $status = true;
                    $statusCode = HttpStatusCode::OK;
                } else {
                    $status = false;
                    $data = array();
                    $msg = 'User settings could not be saved.';
                    $statusCode = HttpStatusCode::BAD_REQUEST;
                }
            } else {
                $errors = array_values(array_values($user->getErrors()));
                foreach ($errors as $key => $err) {
                    foreach ($err as $key1 => $err1) {
                        $msg = $err1;
                    }
                }
                $status = false;
                $data = [];
                $statusCode = HttpStatusCode::BAD_REQUEST;
            }
            return new LoginServiceResponse($msg, $status, $data, $statusCode);
        } else {
            Log::error("Expected POST but got " . print_r($this->request->getMethod(), true));
            return new LoginServiceResponse(
                "Signup failure. Please try again later, or use the Feedback tab to ask tech support for help.",
                false,
                [],
                HttpStatusCode::BAD_REQUEST
            );
        }
    }
}
