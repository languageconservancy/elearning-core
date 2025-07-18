<?php

namespace App\Controller\Api\Login;

use App\Lib\HttpStatusCode;

class InvalidLoginService extends LoginService
{
    public function __construct(array $payload)
    {
        parent::__construct();

        $this->payload = $payload;
    }


    public function login(): ?LoginServiceResponse
    {
        $type = $this->payload['type'];
        return new LoginServiceResponse(
            "Invalid login type: " . $type ?? "",
            false,
            [],
            HttpStatusCode::BAD_REQUEST
        );
    }

    public function signup(): ?LoginServiceResponse
    {
        $type = $this->request->getData()['type'];
        return new LoginServiceResponse(
            "Invalid signup type: " . $type ?? "",
            false,
            [],
            HttpStatusCode::BAD_REQUEST
        );
    }
}
