<?php

namespace App\Controller\Api\Login;

use App\Exceptions\RequiredFieldException;
use App\Lib\HttpStatusCode;
use Cake\Log\Log;

class AppleLoginService extends LoginService
{
    /**
     * AppleLoginService Constructor
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        parent::__construct();
        $this->payload = $payload;
    }

    /**
     * Logs user in through Apple, if no account is present, created one.
     *
     * @return LoginServiceResponse|null
     * @throws RequiredFieldException
     */
    public function login(): ?LoginServiceResponse
    {
        $this->validateRequest($this->payload, [
            'social_id'
        ]);

        $userData = $this->getUserByDbField('apple_id', $this->payload['social_id'], 'apple');
        if (empty($userData)) {
            // User is signing in for the first time. Sign them up.
            return $this->signup();
        } elseif ($userData[0]['is_active'] == '1') {
            $userData['firstLogin'] = false;
            $data1 = array('last_logged' => date('Y-m-d H:i:s'));
            $this->updateUserData($userData[0]['id'], $data1);
            $this->updateAgeFromDob($userData[0]['id']);
            return new LoginServiceResponse(
                "User successfully signed in with Sign In With Apple",
                true,
                $userData,
                HttpStatusCode::OK
            );
        } else {
            return new LoginServiceResponse(
                "Your account is currently deactivated. Use the Feeback tab to ask tech support for help.",
                false,
                [],
                HttpStatusCode::NOT_ACCEPTABLE
            );
        }
    }

    /**
     * Account creation through Apple.
     *
     * @return LoginServiceResponse|null
     * @throws RequiredFieldException
     */
    public function signup(): ?LoginServiceResponse
    {
        $this->validateRequest($this->payload, [
            'social_id',
            'name',
            'email'
        ]);

        // extract payload
        $userData = array(
            'name' => $this->payload['name'],
            'apple_id' => $this->payload['social_id'],
            'password' => $this->payload['social_id'],
            'repassword' => $this->payload['social_id'],
            'email' => $this->payload['email'],
            'apple_status' => '1',
            'id' => 0);

        return $this->setUpNewUser($userData, 'apple_id', 'apple');
    }
}
