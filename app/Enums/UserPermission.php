<?php

declare(strict_types=1);

namespace App\Enums;

enum UserPermission: string
{
    // Users
    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_EDIT = 'users.edit';
    case USERS_DELETE = 'users.delete';
    case USERS_MANAGE_ROLES = 'users.manage_roles';

    // MCP Servers
    case MCP_SERVERS_VIEW = 'mcp_servers.view';
    case MCP_SERVERS_CREATE = 'mcp_servers.create';
    case MCP_SERVERS_EDIT = 'mcp_servers.edit';
    case MCP_SERVERS_DELETE = 'mcp_servers.delete';
    case MCP_SERVERS_MANAGE = 'mcp_servers.manage';

    // Integrations
    case INTEGRATIONS_VIEW = 'integrations.view';
    case INTEGRATIONS_CREATE = 'integrations.create';
    case INTEGRATIONS_EDIT = 'integrations.edit';
    case INTEGRATIONS_DELETE = 'integrations.delete';
    case INTEGRATIONS_MANAGE_OWN = 'integrations.manage_own';

    // Workflows
    case WORKFLOWS_VIEW = 'workflows.view';
    case WORKFLOWS_CREATE = 'workflows.create';
    case WORKFLOWS_EDIT = 'workflows.edit';
    case WORKFLOWS_DELETE = 'workflows.delete';
    case WORKFLOWS_EXECUTE = 'workflows.execute';

    // Logs
    case LOGS_VIEW = 'logs.view';
    case LOGS_EXPORT = 'logs.export';
    case LOGS_DELETE = 'logs.delete';

    // Settings
    case SETTINGS_VIEW = 'settings.view';
    case SETTINGS_EDIT = 'settings.edit';

    public function label(): string
    {
        return str_replace(['_', '.'], ' ', ucwords($this->value, '_.'));
    }

    public function category(): string
    {
        return explode('.', $this->value)[0];
    }

    /**
     * @return array<string, array<int, array{value:string,label:string}>>
     */
    public static function groupedByCategory(): array
    {
        $grouped = [];

        foreach (self::cases() as $permission) {
            $category = $permission->category();
            $grouped[$category][] = [
                'value' => $permission->value,
                'label' => $permission->label(),
            ];
        }

        return $grouped;
    }
}
