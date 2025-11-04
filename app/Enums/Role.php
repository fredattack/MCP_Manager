<?php

namespace App\Enums;

enum Role: string
{
    // Platform Roles (AgentOps Management)
    case PLATFORM_ADMIN = 'PLATFORM_ADMIN';
    case PLATFORM_MANAGER = 'PLATFORM_MANAGER';
    case PLATFORM_SUPPORT = 'PLATFORM_SUPPORT';
    case PLATFORM_DEVELOPER = 'PLATFORM_DEVELOPER';

    // Organization Roles (Standard Client Roles)
    case ORG_OWNER = 'ORG_OWNER';
    case ORG_ADMIN = 'ORG_ADMIN';
    case ORG_MEMBER = 'ORG_MEMBER';
    case ORG_GUEST = 'ORG_GUEST';

    public function label(): string
    {
        return match ($this) {
            self::PLATFORM_ADMIN => 'Platform Administrator',
            self::PLATFORM_MANAGER => 'Platform Manager',
            self::PLATFORM_SUPPORT => 'Platform Support',
            self::PLATFORM_DEVELOPER => 'Platform Developer',
            self::ORG_OWNER => 'Organization Owner',
            self::ORG_ADMIN => 'Organization Administrator',
            self::ORG_MEMBER => 'Organization Member',
            self::ORG_GUEST => 'Organization Guest',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PLATFORM_ADMIN => 'Full platform access, manage all organizations and system settings',
            self::PLATFORM_MANAGER => 'Manage client organizations, support, and user administration',
            self::PLATFORM_SUPPORT => 'Support access, read-only view of organizations and logs',
            self::PLATFORM_DEVELOPER => 'Technical access, system logs, debugging, and development tools',
            self::ORG_OWNER => 'Full control over organization, members, and resources',
            self::ORG_ADMIN => 'Manage organization members, credentials, and integrations',
            self::ORG_MEMBER => 'Standard access to organization resources',
            self::ORG_GUEST => 'Limited read-only access to organization resources',
        };
    }

    public function isPlatformRole(): bool
    {
        return in_array($this, [
            self::PLATFORM_ADMIN,
            self::PLATFORM_MANAGER,
            self::PLATFORM_SUPPORT,
            self::PLATFORM_DEVELOPER,
        ]);
    }

    public function isOrganizationRole(): bool
    {
        return ! $this->isPlatformRole();
    }
}
