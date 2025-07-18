<?php
declare(strict_types=1);

namespace App\Lib;

class ErrorCode
{
    public const EMAIL_ALREADY_REGISTERED = 1001;
    public const USERNAME_INAPPROPRIATE = 1002;
    public const USER_SETTINGS_FAILED = 1003;
    public const USER_CREATION_FAILED = 1004;
    public const INVALID_METHOD = 1005;
}
