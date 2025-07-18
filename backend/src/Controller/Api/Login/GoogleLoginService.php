<?php

namespace App\Controller\Api\Login;

use App\Exceptions\RequiredFieldException;
use App\Lib\HttpStatusCode;

class GoogleLoginService extends LoginService
{
    /**
     * GoogleLoginService Constructor
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct();
        $this->payload = $data;
    }

    /**
     * Logs user in through Google, if no account is present, create one.
     *
     * @return LoginServiceResponse|null
     * @throws RequiredFieldException
     */
    public function login(): ?LoginServiceResponse
    {
        $this->validateRequest($this->payload, [
            'social_id'
        ]);

        $userData = $this->getUserByDbField('google_id', $this->payload['social_id'], 'google');
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
     * Account creation through Google.
     *
     * @return LoginServiceResponse|null
     * @throws RequiredFieldException
     */
    public function signup(): ?LoginServiceResponse
    {
        $this->validateRequest($this->payload, [
            'social_id',
            'name',
            'email',
            'profile_image'
        ]);

        // extract payload
        $userData = array(
            'name' => $this->payload['name'],
            'google_id' => $this->payload['social_id'],
            'password' => $this->payload['social_id'],
            'repassword' => $this->payload['social_id'],
            'email' => $this->payload['email'],
            'google_status' => 1,
            'id' => 0);

        return $this->setUpNewUser($userData, 'google_id', 'google');
    }
}
