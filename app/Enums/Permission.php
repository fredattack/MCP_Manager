<?php

namespace App\Enums;

enum Permission: string
{
    // God Permission (Super Admin Wildcard)
    case GOD_MODE = '*';

    // Platform Permissions (AgentOps Management)
    case PLATFORM_ORGANIZATIONS_READ = 'platform.organizations.read';
    case PLATFORM_ORGANIZATIONS_CREATE = 'platform.organizations.create';
    case PLATFORM_ORGANIZATIONS_UPDATE = 'platform.organizations.update';
    case PLATFORM_ORGANIZATIONS_DELETE = 'platform.organizations.delete';
    case PLATFORM_ORGANIZATIONS_MANAGE = 'platform.organizations.*';

    case PLATFORM_USERS_READ = 'platform.users.read';
    case PLATFORM_USERS_CREATE = 'platform.users.create';
    case PLATFORM_USERS_UPDATE = 'platform.users.update';
    case PLATFORM_USERS_DELETE = 'platform.users.delete';
    case PLATFORM_USERS_MANAGE = 'platform.users.*';

    case PLATFORM_AUDIT_READ = 'platform.audit.read';
    case PLATFORM_AUDIT_MANAGE = 'platform.audit.*';

    case PLATFORM_SYSTEM_READ = 'platform.system.read';
    case PLATFORM_SYSTEM_UPDATE = 'platform.system.update';
    case PLATFORM_SYSTEM_MANAGE = 'platform.system.*';

    // Organization Permissions (Client Resources)
    case ORG_CREDENTIALS_READ = 'organization.credentials.read';
    case ORG_CREDENTIALS_CREATE = 'organization.credentials.create';
    case ORG_CREDENTIALS_UPDATE = 'organization.credentials.update';
    case ORG_CREDENTIALS_DELETE = 'organization.credentials.delete';
    case ORG_CREDENTIALS_SHARE = 'organization.credentials.share';
    case ORG_CREDENTIALS_MANAGE = 'organization.credentials.*';

    case ORG_MEMBERS_READ = 'organization.members.read';
    case ORG_MEMBERS_INVITE = 'organization.members.invite';
    case ORG_MEMBERS_UPDATE = 'organization.members.update';
    case ORG_MEMBERS_REMOVE = 'organization.members.remove';
    case ORG_MEMBERS_MANAGE = 'organization.members.*';

    case ORG_INTEGRATIONS_READ = 'organization.integrations.read';
    case ORG_INTEGRATIONS_CREATE = 'organization.integrations.create';
    case ORG_INTEGRATIONS_UPDATE = 'organization.integrations.update';
    case ORG_INTEGRATIONS_DELETE = 'organization.integrations.delete';
    case ORG_INTEGRATIONS_MANAGE = 'organization.integrations.*';

    case ORG_LEASES_READ = 'organization.leases.read';
    case ORG_LEASES_CREATE = 'organization.leases.create';
    case ORG_LEASES_APPROVE = 'organization.leases.approve';
    case ORG_LEASES_REVOKE = 'organization.leases.revoke';
    case ORG_LEASES_MANAGE = 'organization.leases.*';

    case ORG_SETTINGS_READ = 'organization.settings.read';
    case ORG_SETTINGS_UPDATE = 'organization.settings.update';
    case ORG_SETTINGS_MANAGE = 'organization.settings.*';

    public function category(): string
    {
        return match (true) {
            $this === self::GOD_MODE => 'God Mode',
            str_starts_with($this->value, 'platform.organizations') => 'Platform Organizations',
            str_starts_with($this->value, 'platform.users') => 'Platform Users',
            str_starts_with($this->value, 'platform.audit') => 'Platform Audit',
            str_starts_with($this->value, 'platform.system') => 'Platform System',
            str_starts_with($this->value, 'organization.credentials') => 'Organization Credentials',
            str_starts_with($this->value, 'organization.members') => 'Organization Members',
            str_starts_with($this->value, 'organization.integrations') => 'Organization Integrations',
            str_starts_with($this->value, 'organization.leases') => 'Organization Leases',
            str_starts_with($this->value, 'organization.settings') => 'Organization Settings',
            default => 'Other',
        };
    }

    public function label(): string
    {
        $parts = explode('.', $this->value);
        $action = end($parts);

        if ($action === '*') {
            $action = 'manage';
        }

        return ucfirst($action);
    }

    public function description(): string
    {
        return match ($this) {
            // God Mode
            self::GOD_MODE => 'Absolute control - bypasses all permission checks',

            // Platform Organizations
            self::PLATFORM_ORGANIZATIONS_READ => 'View all organizations',
            self::PLATFORM_ORGANIZATIONS_CREATE => 'Create new organizations',
            self::PLATFORM_ORGANIZATIONS_UPDATE => 'Update organization details',
            self::PLATFORM_ORGANIZATIONS_DELETE => 'Delete organizations',
            self::PLATFORM_ORGANIZATIONS_MANAGE => 'Full management of all organizations',

            // Platform Users
            self::PLATFORM_USERS_READ => 'View all users across platform',
            self::PLATFORM_USERS_CREATE => 'Create new users',
            self::PLATFORM_USERS_UPDATE => 'Update user details',
            self::PLATFORM_USERS_DELETE => 'Delete users',
            self::PLATFORM_USERS_MANAGE => 'Full user management',

            // Platform Audit
            self::PLATFORM_AUDIT_READ => 'View platform audit logs',
            self::PLATFORM_AUDIT_MANAGE => 'Manage audit logs',

            // Platform System
            self::PLATFORM_SYSTEM_READ => 'View system settings and health',
            self::PLATFORM_SYSTEM_UPDATE => 'Update system settings',
            self::PLATFORM_SYSTEM_MANAGE => 'Full system management',

            // Organization Credentials
            self::ORG_CREDENTIALS_READ => 'View organization credentials',
            self::ORG_CREDENTIALS_CREATE => 'Create new credentials',
            self::ORG_CREDENTIALS_UPDATE => 'Update credentials',
            self::ORG_CREDENTIALS_DELETE => 'Delete credentials',
            self::ORG_CREDENTIALS_SHARE => 'Share credentials with members',
            self::ORG_CREDENTIALS_MANAGE => 'Full credential management',

            // Organization Members
            self::ORG_MEMBERS_READ => 'View organization members',
            self::ORG_MEMBERS_INVITE => 'Invite new members',
            self::ORG_MEMBERS_UPDATE => 'Update member roles',
            self::ORG_MEMBERS_REMOVE => 'Remove members',
            self::ORG_MEMBERS_MANAGE => 'Full member management',

            // Organization Integrations
            self::ORG_INTEGRATIONS_READ => 'View integrations',
            self::ORG_INTEGRATIONS_CREATE => 'Create new integrations',
            self::ORG_INTEGRATIONS_UPDATE => 'Update integrations',
            self::ORG_INTEGRATIONS_DELETE => 'Delete integrations',
            self::ORG_INTEGRATIONS_MANAGE => 'Full integration management',

            // Organization Leases
            self::ORG_LEASES_READ => 'View credential leases',
            self::ORG_LEASES_CREATE => 'Create new leases',
            self::ORG_LEASES_APPROVE => 'Approve lease requests',
            self::ORG_LEASES_REVOKE => 'Revoke active leases',
            self::ORG_LEASES_MANAGE => 'Full lease management',

            // Organization Settings
            self::ORG_SETTINGS_READ => 'View organization settings',
            self::ORG_SETTINGS_UPDATE => 'Update organization settings',
            self::ORG_SETTINGS_MANAGE => 'Full organization settings management',
        };
    }

    public function isPlatformPermission(): bool
    {
        return str_starts_with($this->value, 'platform.');
    }

    public function isOrganizationPermission(): bool
    {
        return str_starts_with($this->value, 'organization.');
    }

    public static function getPlatformPermissions(): array
    {
        return array_filter(
            self::cases(),
            fn (self $permission) => $permission->isPlatformPermission()
        );
    }

    public static function getOrganizationPermissions(): array
    {
        return array_filter(
            self::cases(),
            fn (self $permission) => $permission->isOrganizationPermission()
        );
    }
}
