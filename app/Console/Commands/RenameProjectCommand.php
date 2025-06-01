<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function is_array;
use function is_string;

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
    public function handle(): int
    {
        // Get and validate arguments
        $vendorArg = $this->argument('vendor');
        $nameArg = $this->argument('name');

        if (! is_string($vendorArg) || ! is_string($nameArg)) {
            $this->error('Vendor and name must be strings');

            return Command::FAILURE;
        }

        $vendor = $vendorArg;
        $name = $nameArg;

        // Get and validate options
        $descriptionOpt = $this->option('description');
        $description = is_string($descriptionOpt) && $descriptionOpt !== ''
            ? $descriptionOpt
            : "A {$name} application";

        $displayNameOpt = $this->option('display-name');
        $displayName = is_string($displayNameOpt) && $displayNameOpt !== ''
            ? $displayNameOpt
            : str_replace(['-', '_'], ' ', ucwords($name));

        $databaseNameOpt = $this->option('database-name');
        $databaseName = is_string($databaseNameOpt) && $databaseNameOpt !== ''
            ? $databaseNameOpt
            : str_replace('-', '_', $name);

        $this->info('Renaming project...');

        // Update composer.json
        $this->updateComposerJson($vendor, $name, $description);

        // Update package.json
        $this->updatePackageJson($name);

        // Update .env file
        $this->updateEnvFile($displayName, $databaseName);

        $this->info('Project renamed successfully!');
        $this->info("Vendor: {$vendor}");
        $this->info("Name: {$name}");
        $this->info("Description: {$description}");
        $this->info("Display Name: {$displayName}");
        $this->info("Database Name: {$databaseName}");

        return Command::SUCCESS;
    }

    /**
     * Update the composer.json file.
     */
    private function updateComposerJson(string $vendor, string $name, string $description): void
    {
        $composerJsonPath = base_path('composer.json');

        if (! File::exists($composerJsonPath)) {
            $this->error('composer.json file not found!');

            return;
        }

        // @var string $composerContent
        $composerContent = File::get($composerJsonPath);

        $composerJson = json_decode($composerContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Error parsing composer.json: '.json_last_error_msg());

            return;
        }

        if (! is_array($composerJson)) {
            $this->error('Invalid composer.json structure');

            return;
        }

        $composerJson['name'] = "{$vendor}/{$name}";
        $composerJson['description'] = $description;

        $encodedJson = json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encodedJson === false) {
            $this->error('Failed to encode composer.json');

            return;
        }

        File::put(
            $composerJsonPath,
            $encodedJson
        );

        $this->info('composer.json updated successfully.');
    }

    /**
     * Update the package.json file.
     */
    private function updatePackageJson(string $name): void
    {
        $packageJsonPath = base_path('package.json');

        if (! File::exists($packageJsonPath)) {
            $this->error('package.json file not found!');

            return;
        }

        // @var string $packageContent
        $packageContent = File::get($packageJsonPath);

        $packageJson = json_decode($packageContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Error parsing package.json: '.json_last_error_msg());

            return;
        }

        if (! is_array($packageJson)) {
            $this->error('Invalid package.json structure');

            return;
        }

        $packageJson['name'] = $name;

        $encodedJson = json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encodedJson === false) {
            $this->error('Failed to encode package.json');

            return;
        }

        File::put(
            $packageJsonPath,
            $encodedJson
        );

        $this->info('package.json updated successfully.');
    }

    /**
     * Update the .env file.
     */
    private function updateEnvFile(string $displayName, string $databaseName): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            $this->warn('.env file not found. Trying with .env.example...');
            $envPath = base_path('.env.example');

            if (! File::exists($envPath)) {
                $this->error('.env and .env.example files not found!');

                return;
            }
        }

        // @var string $envContent
        $envContent = File::get($envPath);

        // Update APP_NAME
        $updatedEnv = preg_replace('/APP_NAME=.*/', 'APP_NAME="'.$displayName.'"', $envContent);
        if ($updatedEnv === null) {
            $this->error('Failed to update APP_NAME in .env file');

            return;
        }

        // Update DB_DATABASE
        $finalEnv = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE='.$databaseName, $updatedEnv);
        if ($finalEnv === null) {
            $this->error('Failed to update DB_DATABASE in .env file');

            return;
        }

        $result = File::put($envPath, $finalEnv);
        if ($result === false) {
            $this->error('Failed to write to .env file');

            return;
        }

        $this->info('.env file updated successfully.');
    }
}
