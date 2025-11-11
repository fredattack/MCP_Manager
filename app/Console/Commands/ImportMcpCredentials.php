<?php

namespace App\Console\Commands;

use App\Enums\CredentialScope;
use App\Models\User;
use App\Services\CredentialImporter;
use Illuminate\Console\Command;

class ImportMcpCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcp:import-credentials
                            {--env-path= : Path to MCP Server .env file}
                            {--user= : Email of user to import credentials for}
                            {--scope=personal : Credential scope (personal or organization)}
                            {--validate : Only validate .env file without importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import credentials from MCP Server .env file into database';

    /**
     * Execute the console command.
     */
    public function handle(CredentialImporter $importer): int
    {
        $this->info('🔐 MCP Credential Importer');
        $this->newLine();

        // Get .env file path
        $envPath = $this->option('env-path') ?? $this->askForEnvPath();

        if (! file_exists($envPath)) {
            $this->error("❌ File not found: {$envPath}");

            return self::FAILURE;
        }

        $this->info("📄 Reading: {$envPath}");

        // Validate first
        $validation = $importer->validateEnvFile($envPath);

        if (! $validation['valid']) {
            $this->error('❌ No valid credentials found in .env file');

            if (isset($validation['error'])) {
                $this->error($validation['error']);
            }

            return self::FAILURE;
        }

        $this->info("✅ Found {$validation['found_credentials']} valid credentials:");
        foreach ($validation['credentials'] as $credential) {
            $this->line("  • {$credential}");
        }

        if (! empty($validation['placeholders'])) {
            $this->warn("\n⚠️  Found {$validation['placeholders']} placeholder values (will be skipped):");
            foreach ($validation['placeholders'] as $placeholder) {
                $this->line("  • {$placeholder}");
            }
        }

        // If only validating, stop here
        if ($this->option('validate')) {
            $this->newLine();
            $this->info('✅ Validation complete (no import performed)');

            return self::SUCCESS;
        }

        $this->newLine();

        // Get user
        $user = $this->getUser();

        if (! $user) {
            $this->error('❌ User not found or not specified');

            return self::FAILURE;
        }

        $this->info("👤 Importing for user: {$user->name} ({$user->email})");

        // Get scope
        $scope = $this->getScope();
        $this->info("📦 Scope: {$scope->value}");

        // Confirm
        $this->newLine();

        if (! $this->confirm('Proceed with import?', true)) {
            $this->info('Import cancelled');

            return self::SUCCESS;
        }

        // Import
        $this->newLine();
        $this->info('🚀 Importing credentials...');

        $results = $importer->importFromEnvFile($envPath, $user, $scope);

        // Display results
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 Import Results');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        if ($results['total_imported'] > 0) {
            $this->info("✅ Imported: {$results['total_imported']} credentials");

            foreach ($results['imported'] as $service => $count) {
                $this->line("  • {$service}");
            }
        }

        if ($results['total_skipped'] > 0) {
            $this->warn("\n⏭️  Skipped: {$results['total_skipped']} services (no credentials found)");
        }

        if (! empty($results['errors'])) {
            $this->error("\n❌ Errors:");

            foreach ($results['errors'] as $service => $error) {
                $this->line("  • {$service}: {$error}");
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');

        if ($results['total_imported'] > 0) {
            $this->info('🎉 Import completed successfully!');
            $this->newLine();
            $this->info('Next steps:');
            $this->line('  1. Review imported credentials in Settings > Integrations');
            $this->line('  2. Test MCP Server connection');
            $this->line("  3. Run: php artisan db:seed --class=McpDevelopmentSeeder");

            return self::SUCCESS;
        } else {
            $this->warn('⚠️  No credentials were imported');

            return self::FAILURE;
        }
    }

    /**
     * Ask for .env file path.
     */
    private function askForEnvPath(): string
    {
        $defaultPath = '/Users/fred/PhpstormProjects/mcp-server/.env';

        return $this->ask(
            'Path to MCP Server .env file',
            file_exists($defaultPath) ? $defaultPath : null
        );
    }

    /**
     * Get user for import.
     */
    private function getUser(): ?User
    {
        $email = $this->option('user');

        if ($email) {
            return User::where('email', $email)->first();
        }

        // List users
        $users = User::orderBy('created_at', 'desc')->limit(10)->get();

        if ($users->isEmpty()) {
            $this->error('No users found in database');

            return null;
        }

        $this->info('Available users:');

        foreach ($users as $index => $user) {
            $this->line("  [{$index}] {$user->name} ({$user->email})");
        }

        $selection = $this->ask('Select user (number or email)', '0');

        // If number
        if (is_numeric($selection)) {
            return $users[$selection] ?? null;
        }

        // If email
        return User::where('email', $selection)->first();
    }

    /**
     * Get credential scope.
     */
    private function getScope(): CredentialScope
    {
        $scope = $this->option('scope');

        if ($scope === 'organization') {
            return CredentialScope::Organization;
        }

        return CredentialScope::Personal;
    }
}