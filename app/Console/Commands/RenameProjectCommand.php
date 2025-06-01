<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RenameProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rename 
                            {vendor : Your organization/username for composer.json (e.g., acme)}
                            {name : The project name (e.g., invoice-app)}
                            {--description= : The project description for composer.json}
                            {--display-name= : The display name for APP_NAME in .env}
                            {--database-name= : The database name for DB_DATABASE in .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rename the project by updating composer.json, package.json, and .env files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vendor = $this->argument('vendor');
        $name = $this->argument('name');
        $description = $this->option('description') ?: "A {$name} application";
        $displayName = $this->option('display-name') ?: str_replace(['-', '_'], ' ', ucwords($name));
        $databaseName = $this->option('database-name') ?: str_replace('-', '_', $name);

        $this->info('Renaming project...');

        // Update composer.json
        $this->updateComposerJson($vendor, $name, $description);

        // Update package.json
        $this->updatePackageJson($name);

        // Update .env file
        $this->updateEnvFile($displayName, $databaseName);

        $this->info('Project renamed successfully!');
        $this->info('Vendor: ' . $vendor);
        $this->info('Name: ' . $name);
        $this->info('Description: ' . $description);
        $this->info('Display Name: ' . $displayName);
        $this->info('Database Name: ' . $databaseName);
    }

    /**
     * Update the composer.json file.
     */
    private function updateComposerJson(string $vendor, string $name, string $description): void
    {
        $composerJsonPath = base_path('composer.json');

        if (!File::exists($composerJsonPath)) {
            $this->error('composer.json file not found!');
            return;
        }

        $composerJson = json_decode(File::get($composerJsonPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Error parsing composer.json: ' . json_last_error_msg());
            return;
        }

        $composerJson['name'] = "{$vendor}/{$name}";
        $composerJson['description'] = $description;

        File::put(
            $composerJsonPath,
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $this->info('composer.json updated successfully.');
    }

    /**
     * Update the package.json file.
     */
    private function updatePackageJson(string $name): void
    {
        $packageJsonPath = base_path('package.json');

        if (!File::exists($packageJsonPath)) {
            $this->error('package.json file not found!');
            return;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Error parsing package.json: ' . json_last_error_msg());
            return;
        }

        $packageJson['name'] = $name;

        File::put(
            $packageJsonPath,
            json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $this->info('package.json updated successfully.');
    }

    /**
     * Update the .env file.
     */
    private function updateEnvFile(string $displayName, string $databaseName): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            $this->warn('.env file not found. Trying with .env.example...');
            $envPath = base_path('.env.example');

            if (!File::exists($envPath)) {
                $this->error('.env and .env.example files not found!');
                return;
            }
        }

        $env = File::get($envPath);

        // Update APP_NAME
        $env = preg_replace('/APP_NAME=.*/', 'APP_NAME="' . $displayName . '"', $env);

        // Update DB_DATABASE
        $env = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $databaseName, $env);

        File::put($envPath, $env);

        $this->info('.env file updated successfully.');
    }
}
