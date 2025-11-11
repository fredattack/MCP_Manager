<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // For production, create no default users
        if (app()->environment('production')) {
            $this->command->info('Production environment - no default users created.');
            $this->command->info('Please create your first user via registration or artisan command.');

            return;
        }

        // For development/staging/testing, seed roles, permissions, and users
        if (app()->environment(['local', 'development', 'staging', 'testing'])) {
            // Create roles and permissions first
            $this->call(RoleAndPermissionSeeder::class);

            $this->command->newLine();

            // Then create users with roles
            $this->call(AgentOpsUsersSeeder::class);

            $this->command->newLine();

            // Import credentials from MCP Server .env file
            $this->call(ImportMcpServerCredentialsSeeder::class);

            $this->command->newLine();
            $this->command->warn('⚠️  WARNING: Change these credentials before deploying to production!');
        }
    }
}
