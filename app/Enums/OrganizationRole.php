<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';
    case Guest = 'guest';

    public function displayName(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Member => 'Member',
            self::Guest => 'Guest',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Owner => 'Full control over the organization',
            self::Admin => 'Can manage members and credentials',
            self::Member => 'Can use shared credentials',
            self::Guest => 'Limited read-only access',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::Owner => ['*'],
            self::Admin => ['read:credentials', 'write:credentials', 'read:members', 'write:members', 'read:audit'],
            self::Member => ['read:credentials', 'read:own', 'write:own'],
            self::Guest => ['read:own'],
        };
    }

    public function canManageMembers(): bool
    {
        return in_array($this, [self::Owner, self::Admin]);
    }

    public function canManageCredentials(): bool
    {
        return in_array($this, [self::Owner, self::Admin]);
    }
}
