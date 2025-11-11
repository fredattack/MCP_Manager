<?php

namespace Database\Seeders;

use App\Enums\CredentialScope;
use App\Models\User;
use App\Services\CredentialImporter;
use Illuminate\Database\Seeder;

class ImportMcpServerCredentialsSeeder extends Seeder
{
    public function __construct(
        private CredentialImporter $importer
    ) {}

    public function run(): void
    {
        $this->command->info('🔐 Importing MCP Server credentials...');
        $this->command->newLine();

        // Default .env path
        $envPath = '/Users/fred/PhpstormProjects/mcp-server/.env';

        if (! file_exists($envPath)) {
            $this->command->error("❌ File not found: {$envPath}");
            $this->command->warn('Please update the path in the seeder or run:');
            $this->command->line('php artisan mcp:import-credentials --env-path=/path/to/.env');

            return;
        }

        // Get first user or create one
        $user = User::first();

        if (! $user) {
            $this->command->error('❌ No users found in database');
            $this->command->warn('Please create a user first or run McpDevelopmentSeeder');

            return;
        }

        $this->command->info("📄 Reading: {$envPath}");
        $this->command->info("👤 Importing for user: {$user->name} ({$user->email})");
        $this->command->info('📦 Scope: personal');
        $this->command->newLine();

        // Validate first
        $validation = $this->importer->validateEnvFile($envPath);

        if (! $validation['valid']) {
            $this->command->error('❌ No valid credentials found in .env file');

            return;
        }

        $this->command->info("✅ Found {$validation['found_credentials']} valid credentials");

        // Import
        $results = $this->importer->importFromEnvFile(
            $envPath,
            $user,
            CredentialScope::Personal
        );

        // Display results
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('📊 Import Results');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->newLine();

        if ($results['total_imported'] > 0) {
            $this->command->info("✅ Imported: {$results['total_imported']} credentials");

            foreach ($results['imported'] as $service => $count) {
                $this->command->line("  • {$service}");
            }
        }

        if ($results['total_skipped'] > 0) {
            $this->command->warn("\n⏭️  Skipped: {$results['total_skipped']} services (no credentials found)");
        }

        if (! empty($results['errors'])) {
            $this->command->error("\n❌ Errors:");

            foreach ($results['errors'] as $service => $error) {
                $this->command->line("  • {$service}: {$error}");
            }
        }

        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════════');

        if ($results['total_imported'] > 0) {
            $this->command->info('🎉 Import completed successfully!');
        }
    }
}
