<?php

namespace App\Enums;

enum LeaseStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Revoked = 'revoked';

    public function displayName(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Revoked => 'Revoked',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function canRenew(): bool
    {
        return $this === self::Active;
    }
}
