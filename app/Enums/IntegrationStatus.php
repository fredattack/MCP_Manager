<?php

namespace App\Enums;

enum IntegrationStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Get the display name for the integration status.
     */
    public function displayName(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }

    /**
     * Check if the integration is active.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
