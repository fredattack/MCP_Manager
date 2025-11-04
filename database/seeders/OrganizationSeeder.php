<?php

namespace Database\Seeders;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating AgentOps organization...');

        // Get the Platform Admin as owner
        $platformAdmin = \App\Models\User::where('email', 'admin@agentops.be')->firstOrFail();

        // Create AgentOps - Super-Tenant Management Organization
        $agentOps = Organization::updateOrCreate(
            ['slug' => 'agentops'],
            [
                'name' => 'AgentOps',
                'owner_id' => $platformAdmin->id,
                'billing_email' => 'billing@agentops.be',
                'status' => OrganizationStatus::Active,
                'max_members' => 50,
                'settings' => [
                    'is_super_tenant' => true,
                    'platform_access' => true,
                    'can_manage_organizations' => true,
                ],
            ]
        );
        $this->command->line("  ✓ {$agentOps->name} (Super-Tenant, Owner: {$platformAdmin->name})");

        $this->command->comment('AgentOps organization created successfully!');
    }
}
