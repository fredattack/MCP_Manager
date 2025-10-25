<?php

namespace App\Console\Commands;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\McpIntegration;
use Illuminate\Console\Command;

class SyncMcpIntegrations extends Command
{
    protected $signature = 'mcp:sync-integrations';

    protected $description = 'Sync MCP integrations with IntegrationAccount table';

    public function handle()
    {
        $this->info('Syncing MCP integrations...');

        $mcpIntegrations = McpIntegration::where('enabled', true)
            ->where('status', 'active')
            ->get();

        foreach ($mcpIntegrations as $mcpIntegration) {
            $this->info("Processing {$mcpIntegration->service_name} for user {$mcpIntegration->user_id}");

            // Map service name to IntegrationType
            $type = match ($mcpIntegration->service_name) {
                'todoist' => IntegrationType::TODOIST,
                'notion' => IntegrationType::NOTION,
                default => null,
            };

            if (! $type) {
                $this->warn("Unknown service type: {$mcpIntegration->service_name}");

                continue;
            }

            // Get credentials from config
            $config = $mcpIntegration->config ?? [];
            $token = $config['api_token'] ?? $config['api_key'] ?? null;

            if (! $token) {
                $this->warn("No token found for {$mcpIntegration->service_name}");

                continue;
            }

            // Create or update IntegrationAccount
            IntegrationAccount::updateOrCreate(
                [
                    'user_id' => $mcpIntegration->user_id,
                    'type' => $type,
                ],
                [
                    'access_token' => encrypt($token),
                    'refresh_token' => null,
                    'status' => IntegrationStatus::ACTIVE,
                    'expires_at' => null,
                    'meta' => [
                        'synced_from_mcp' => true,
                        'mcp_integration_id' => $mcpIntegration->id,
                        'synced_at' => now()->toIso8601String(),
                    ],
                ]
            );

            $this->info("✓ Synced {$mcpIntegration->service_name} integration");
        }

        $this->info('Sync complete!');

        return Command::SUCCESS;
    }
}
