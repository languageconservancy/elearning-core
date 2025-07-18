<?php

namespace App\Controller\Api\Login;

use App\Exceptions\RequiredFieldException;
use App\Lib\HttpStatusCode;

class FacebookLoginService extends LoginService
{
    /**
     * FacebookLoginService Constructor
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        parent::__construct();
        $this->payload = $payload;
    }

    /**
     * Logs user in through Facebook, if no account is present, create one.
     *
     * @return LoginServiceResponse|null
     * @throws RequiredFieldException
     */
    public function login(): ?LoginServiceResponse
    {
        $this->validateRequest($this->payload, [
            'social_id'
        ]);

        $userData = $this->getUserByDbField('fb_id', $this->payload['social_id'], 'fb');
        if ($userData === null) {
            return $this->signup();
        } elseif ($userData[0]['is_active'] == '1') {
            $userData['firstLogin'] = false;
            $data1 = array('last_logged' => date('Y-m-d H:i:s'));
            $this->updateUserData($userData[0]['id'], $data1);
            $this->updateAgeFromDob($userData[0]['id']);

            return new LoginServiceResponse(
                "",
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
     * Account creation through Facebook.
     *
     * @return LoginServiceResponse|null
     */
    public function signup(): ?LoginServiceResponse
    {
        $validate = $this->validateRequest($this->payload, [
            'social_id',
            'name',
            'email',
        ]);

        // extract payload
        $userData = array(
            'name' => $this->payload['name'],
            'fb_id' => $this->payload['social_id'],
            'password' => $this->payload['social_id'],
            'repassword' => $this->payload['social_id'],
            'email' => $this->payload['email'],
            'fb_status' => 1,
            'id' => 0);

        return $this->setUpNewUser($userData, 'fb_id', 'fb');
    }
}
