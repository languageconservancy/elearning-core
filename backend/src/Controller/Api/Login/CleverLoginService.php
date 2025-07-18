<?php

namespace App\Controller\Api\Login;

use App\Lib\CleverLibrary;
use App\Lib\UtilLibrary;
use LogicException;
use App\Lib\HttpStatusCode;

class CleverLoginService extends LoginService
{
    public function __construct(array $payload)
    {
        parent::__construct();
        $this->payload = $payload;
    }

    public function login(): ?LoginServiceResponse
    {
        // Container for clever user data
        $cleverUser = array();
        $message = "";

        // Ensure bootstrap Clever constants are all set
        $response = CleverLibrary::checkCleverConsts();
        if (!$response['status']) {
            return new LoginServiceResponse(
                $response['message'],
                false,
                $response['data'],
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }

        // Exchange Clever Single Sign-On code for an access token
        $response = CleverLibrary::getCleverAccessToken($this->payload);
        if (!$response['status']) {
            return new LoginServiceResponse(
                $response['message'],
                false,
                $response['data'],
                HttpStatusCode::BAD_REQUEST
            );
        }

        // Grab access token from return data
        $accessToken = $response['data']['access_token'];

        // Use new access token to get identity of current Clever user
        $response = CleverLibrary::getCleverUserIdentity($accessToken);
        if (!$response['status']) {
            return new LoginServiceResponse(
                $response['message'],
                false,
                $response['data'],
                HttpStatusCode::BAD_REQUEST
            );
        }

        $cleverUserId = $response['data']['data']['id'];

        $response = CleverLibrary::getCleverUserInfo($accessToken, $cleverUserId);
        if (!$response['status']) {
            return new LoginServiceResponse(
                $response['message'],
                false,
                $response['data'],
                HttpStatusCode::BAD_REQUEST
            );
        }

        // Get user information
        $cleverUser = CleverLibrary::setUserInfo($cleverUser, $response['data']['data']);

        // Check for pre-existing user in database with same Clever ID
        $count = $this->getUsersTable()->find()
            ->where(['clever_id' => $cleverUser['id']])->count();
        $user = $this->getUsersTable()->find()
            ->where(['clever_id' => $cleverUser['id'], 'is_active' => 1, 'is_delete' => 0])
            ->toArray();

        // Set students to minor and everyone else to adults
        if (in_array(UtilLibrary::ROLE_TEACHER_STR, $cleverUser['roles'])) {
            $userDob = time() - (20 * 365 * 24 * 60 * 60); // twenty years ago
            $roleId = $this->getRolesTable()->getRoleId(UtilLibrary::ROLE_TEACHER_STR);
        } else {
            $userDob = time() - (10 * 365 * 24 * 60 * 60); // ten years ago
            $roleId = $this->getRolesTable()->getRoleId(UtilLibrary::ROLE_STUDENT_STR);
        }
        $userData = array(
            'name' => $cleverUser['firstname'] . ' ' . $cleverUser['lastname'],
            'clever_id' => $cleverUser['id'],
            'password' => null,
            'repassword' => null,
            'email' => $cleverUser['email'] ?? $cleverUser['id'],
            'dob' => date('Y-m-d', $userDob),
            'role_id' => $roleId
        );
        $users = $this->getUserByDbField('clever_id', $userData['clever_id'], 'clever');
        $users['social_id'] = $cleverUser['id'];
        $message = 'Login successful';
        $status = true;
        $statusCode = HttpStatusCode::OK;

        if ($count === 0) {
            return $this->setUpNewUser($userData, 'clever_id', 'clever', 'ignorepassword');
        }

        $this->updateAgeFromDob($users[0]['id']);

        return new LoginServiceResponse($message, $status, $users, $statusCode);
    }

    public function signup(): ?LoginServiceResponse
    {
        throw new LogicException('Clever has no signup functionality. Only login.');
    }
}
