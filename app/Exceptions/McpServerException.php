<?php

namespace App\Exceptions;

use Exception;

class McpServerException extends Exception
{
    public const SERVICE_UNAVAILABLE = 1001;

    public const SYNC_FAILED = 1002;

    public const TOKEN_REFRESH_FAILED = 1003;

    public const RATE_LIMIT_EXCEEDED = 1004;

    public const MFA_REQUIRED = 1005;

    public const ACCOUNT_LOCKED = 1006;
}
