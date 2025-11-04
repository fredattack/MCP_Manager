<?php

namespace App\Enums;

enum OrganizationStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Deleted = 'deleted';

    public function displayName(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Deleted => 'Deleted',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function canAccess(): bool
    {
        return $this === self::Active;
    }
}
