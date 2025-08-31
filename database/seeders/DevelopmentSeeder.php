<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\IntegrationAccount;
use App\Enums\IntegrationType;
use App\Enums\IntegrationStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed the application's database for development.
     * 
     * WARNING: Only use in development environment!
     */
    public function run(): void
    {
        // Only run in non-production environments
        if (app()->environment('production')) {
            $this->command->error('Cannot run development seeder in production!');
            return;
        }

        $this->command->info('Creating development users...');

        // Admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@mcp-manager.local',
            'password' => Hash::make('Admin123!@#'),
            'email_verified_at' => now(),
        ]);

        $this->command->info("Admin created: admin@mcp-manager.local / Admin123!@#");

        // Regular user
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'user@mcp-manager.local',
            'password' => Hash::make('User123!@#'),
            'email_verified_at' => now(),
        ]);

        $this->command->info("User created: user@mcp-manager.local / User123!@#");

        // Test user (unverified email)
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@mcp-manager.local',
            'password' => Hash::make('Test123!@#'),
            'email_verified_at' => null,
        ]);

        $this->command->info("Test user created: test@mcp-manager.local / Test123!@# (unverified)");

        // Create sample integrations for admin
        $this->createSampleIntegrations($admin);

        // Create additional random users if needed
        if ($this->command->confirm('Create 10 additional random users?', false)) {
            User::factory(10)->create();
            $this->command->info('10 random users created');
        }

        $this->command->info('Development seeding completed!');
        $this->command->warn('Remember: These credentials are for DEVELOPMENT ONLY!');
    }

    /**
     * Create sample integrations for a user
     */
    private function createSampleIntegrations(User $user): void
    {
        // Sample Notion integration
        IntegrationAccount::create([
            'user_id' => $user->id,
            'type' => IntegrationType::NOTION,
            'access_token' => encrypt('sample_notion_token_' . uniqid()),
            'status' => IntegrationStatus::ACTIVE,
            'meta' => [
                'workspace_name' => 'Sample Workspace',
                'workspace_id' => 'ws_' . uniqid(),
            ],
        ]);

        // Sample Todoist integration (inactive)
        IntegrationAccount::create([
            'user_id' => $user->id,
            'type' => IntegrationType::TODOIST,
            'access_token' => encrypt('sample_todoist_token_' . uniqid()),
            'status' => IntegrationStatus::INACTIVE,
            'meta' => [
                'user_email' => $user->email,
            ],
        ]);

        $this->command->info("Sample integrations created for {$user->email}");
    }
}