<?php

namespace App\Controller\Api\Login;

use App\Lib\HttpStatusCode;

class LoginServiceResponse
{
    private string $message;
    private bool $status;
    private $result;
    private int $statusCode;

    /**
     * @param string $message
     * @param bool $status
     * @param $result
     */
    public function __construct(string $message, bool $status, $result, int $statusCode = null)
    {
        $this->message = $message;
        $this->status = $status;
        $this->result = $result;
        $this->statusCode = $statusCode ?? HttpStatusCode::OK;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
