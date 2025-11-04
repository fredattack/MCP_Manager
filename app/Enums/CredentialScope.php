<?php

namespace App\Enums;

enum CredentialScope: string
{
    case Personal = 'personal';
    case Organization = 'organization';

    public function displayName(): string
    {
        return match ($this) {
            self::Personal => 'Personal',
            self::Organization => 'Organization',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Personal => 'Only accessible by the owner',
            self::Organization => 'Shared within the organization',
        };
    }
}
