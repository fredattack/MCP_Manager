<?php

declare(strict_types=1);

namespace App\Enums;

enum GitConnectionStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ERROR = 'error';
    case EXPIRED = 'expired';

    /**
     * Check if the status is active.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if the status requires re-authentication.
     */
    public function requiresReauth(): bool
    {
        return in_array($this, [self::ERROR, self::EXPIRED], true);
    }
}
