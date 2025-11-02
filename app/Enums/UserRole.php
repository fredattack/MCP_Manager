<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case USER = 'user';
    case READ_ONLY = 'read_only';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::USER => 'User',
            self::READ_ONLY => 'Read Only',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Full access to all features including user management',
            self::MANAGER => 'Can manage integrations and workflows but not users',
            self::USER => 'Standard user with access to own resources',
            self::READ_ONLY => 'View-only access, cannot modify anything',
        };
    }

    /**
     * @return array<int, string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::ADMIN => [
                'users.*',
                'mcp_servers.*',
                'integrations.*',
                'workflows.*',
                'logs.*',
                'settings.*',
            ],
            self::MANAGER => [
                'mcp_servers.view',
                'mcp_servers.manage',
                'integrations.*',
                'workflows.*',
                'logs.view',
            ],
            self::USER => [
                'mcp_servers.view',
                'integrations.view',
                'integrations.manage_own',
                'workflows.view',
                'workflows.execute',
            ],
            self::READ_ONLY => [
                'mcp_servers.view',
                'integrations.view',
                'workflows.view',
                'logs.view',
            ],
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<int, array{value:string,label:string,description:string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $role) => [
                'value' => $role->value,
                'label' => $role->label(),
                'description' => $role->description(),
            ],
            self::cases()
        );
    }
}
