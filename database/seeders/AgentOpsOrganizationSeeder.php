<?php

namespace Database\Seeders;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\IntegrationAccount;
use App\Models\McpServer;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class AgentOpsOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Get the admin user as owner
        $owner = User::where('email', 'admin@agentops.be')->first();

        if (! $owner) {
            $this->command->warn('⚠️  Admin user not found, skipping organization setup');

            return;
        }

        // Create AgentOps Team organization
        $organization = Organization::firstOrCreate(
            ['slug' => 'agentops-team'],
            [
                'name' => 'AgentOps Team',
                'owner_id' => $owner->id,
                'billing_email' => 'admin@agentops.be',
                'status' => OrganizationStatus::Active,
                'max_members' => 50,
                'settings' => [
                    'is_platform_admin' => true,
                    'can_manage_all_integrations' => true,
                    'description' => 'Platform administration team',
                ],
            ]
        );

        $this->command->info("✓ Organization created: {$organization->name}");

        // Add team members with roles
        $members = [
            ['email' => 'admin@agentops.be', 'role' => OrganizationRole::Owner],
            ['email' => 'manager@agentops.be', 'role' => OrganizationRole::Admin],
            ['email' => 'dev@agentops.be', 'role' => OrganizationRole::Admin],
            ['email' => 'support@agentops.be', 'role' => OrganizationRole::Member],
        ];

        foreach ($members as $memberData) {
            $user = User::where('email', $memberData['email'])->first();

            if (! $user) {
                continue;
            }

            OrganizationMember::firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $memberData['role'],
                    'joined_at' => now(),
                    'invited_by' => $organization->owner_id,
                ]
            );

            $this->command->info("  ✓ Added member: {$user->email} ({$memberData['role']->displayName()})");
        }

        // Link existing integration accounts to organization
        $updated = IntegrationAccount::whereNull('organization_id')
            ->update([
                'organization_id' => $organization->id,
                'scope' => 'organization',
            ]);

        if ($updated > 0) {
            $this->command->info("✓ Linked {$updated} integration account(s) to organization");
        }

        // Clean up duplicate MCP servers (keep only the first one)
        $servers = McpServer::orderBy('id')->get();
        if ($servers->count() > 1) {
            $keepServer = $servers->first();
            $duplicateCount = 0;

            foreach ($servers->skip(1) as $duplicate) {
                $duplicate->delete();
                $duplicateCount++;
            }

            $this->command->info("✓ Cleaned up {$duplicateCount} duplicate MCP server(s), kept ID: {$keepServer->id}");
        }

        $this->command->info('✅ AgentOps Team organization setup completed!');
    }
}
