<?php

namespace App\Services;

use App\Enums\CredentialScope;
use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CredentialImporter
{
    private array $parsedEnv = [];

    private array $importedCount = [];

    private array $skippedCount = [];

    private array $errors = [];

    /**
     * Import credentials from MCP Server .env file.
     */
    public function importFromEnvFile(string $envPath, User $user, CredentialScope $scope = CredentialScope::Personal): array
    {
        if (! file_exists($envPath)) {
            throw new \InvalidArgumentException("Environment file not found: {$envPath}");
        }

        $this->parsedEnv = $this->parseEnvFile($envPath);
        $this->importedCount = [];
        $this->skippedCount = [];
        $this->errors = [];

        // Import each service type
        $this->importTodoist($user, $scope);
        $this->importJira($user, $scope);
        $this->importNotion($user, $scope);
        $this->importConfluence($user, $scope);
        $this->importSentry($user, $scope);
        $this->importOpenAI($user, $scope);
        $this->importCalendar($user, $scope);

        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
            'total_imported' => array_sum($this->importedCount),
            'total_skipped' => array_sum($this->skippedCount),
        ];
    }

    /**
     * Parse .env file into key-value array.
     */
    private function parseEnvFile(string $path): array
    {
        $env = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (Str::startsWith(trim($line), '#')) {
                continue;
            }

            // Parse KEY=VALUE
            if (Str::contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes
                $value = trim($value, '"\'');

                $env[$key] = $value;
            }
        }

        return $env;
    }

    /**
     * Get environment variable value.
     */
    private function getEnv(string $key, ?string $default = null): ?string
    {
        $value = $this->parsedEnv[$key] ?? $default;

        // Skip placeholder values
        if ($value && $this->isPlaceholder($value)) {
            return null;
        }

        return $value;
    }

    /**
     * Check if value is a placeholder (not real credential).
     */
    private function isPlaceholder(string $value): bool
    {
        $placeholders = [
            'your_',
            'generate_',
            'change_this',
            'example.',
            'xxxxx',
        ];

        foreach ($placeholders as $placeholder) {
            if (Str::contains(Str::lower($value), $placeholder)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Import Todoist credentials.
     */
    private function importTodoist(User $user, CredentialScope $scope): void
    {
        $token = $this->getEnv('TODOIST_API_TOKEN');

        if (! $token) {
            $this->skippedCount['todoist'] = 1;

            return;
        }

        try {
            IntegrationAccount::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => IntegrationType::TODOIST,
                    'scope' => $scope,
                ],
                [
                    'access_token' => $token,
                    'meta' => [],
                    'status' => IntegrationStatus::ACTIVE,
                ]
            );

            $this->importedCount['todoist'] = 1;
            Log::info("Imported Todoist credentials for user {$user->id}");
        } catch (\Exception $e) {
            $this->errors['todoist'] = $e->getMessage();
            Log::error("Failed to import Todoist: {$e->getMessage()}");
        }
    }

    /**
     * Import JIRA credentials.
     */
    private function importJira(User $user, CredentialScope $scope): void
    {
        $url = $this->getEnv('JIRA_URL');
        $email = $this->getEnv('JIRA_EMAIL');
        $token = $this->getEnv('JIRA_API_TOKEN');
        $cloud = $this->getEnv('JIRA_CLOUD', 'true');

        if (! $url || ! $email || ! $token) {
            $this->skippedCount['jira'] = 1;

            return;
        }

        try {
            IntegrationAccount::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => IntegrationType::JIRA,
                    'scope' => $scope,
                ],
                [
                    'access_token' => $token,
                    'meta' => [
                        'url' => $url,
                        'email' => $email,
                        'cloud' => Str::lower($cloud) === 'true',
                    ],
                    'status' => IntegrationStatus::ACTIVE,
                ]
            );

            $this->importedCount['jira'] = 1;
            Log::info("Imported JIRA credentials for user {$user->id}");
        } catch (\Exception $e) {
            $this->errors['jira'] = $e->getMessage();
            Log::error("Failed to import JIRA: {$e->getMessage()}");
        }
    }

    /**
     * Import Notion credentials.
     */
    private function importNotion(User $user, CredentialScope $scope): void
    {
        $token = $this->getEnv('NOTION_API_TOKEN');
        $databaseId = $this->getEnv('NOTION_DATABASE_ID');

        if (! $token) {
            $this->skippedCount['notion'] = 1;

            return;
        }

        try {
            IntegrationAccount::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => IntegrationType::NOTION,
                    'scope' => $scope,
                ],
                [
                    'access_token' => $token,
                    'meta' => [
                        'database_id' => $databaseId,
                    ],
                    'status' => IntegrationStatus::ACTIVE,
                ]
            );

            $this->importedCount['notion'] = 1;
            Log::info("Imported Notion credentials for user {$user->id}");
        } catch (\Exception $e) {
            $this->errors['notion'] = $e->getMessage();
            Log::error("Failed to import Notion: {$e->getMessage()}");
        }
    }

    /**
     * Import Confluence credentials.
     */
    private function importConfluence(User $user, CredentialScope $scope): void
    {
        $url = $this->getEnv('CONFLUENCE_URL');
        $email = $this->getEnv('CONFLUENCE_EMAIL');
        $token = $this->getEnv('CONFLUENCE_API_TOKEN');

        if (! $url || ! $email || ! $token) {
            $this->skippedCount['confluence'] = 1;

            return;
        }

        try {
            IntegrationAccount::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => IntegrationType::TODOIST, // Note: Should be CONFLUENCE when enum exists
                    'scope' => $scope,
                ],
                [
                    'access_token' => $token,
                    'meta' => [
                        'url' => $url,
                        'email' => $email,
                    ],
                    'status' => IntegrationStatus::ACTIVE,
                ]
            );

            $this->importedCount['confluence'] = 1;
            Log::info("Imported Confluence credentials for user {$user->id}");
        } catch (\Exception $e) {
            $this->errors['confluence'] = $e->getMessage();
            Log::error("Failed to import Confluence: {$e->getMessage()}");
        }
    }

    /**
     * Import Sentry credentials.
     */
    private function importSentry(User $user, CredentialScope $scope): void
    {
        $authToken = $this->getEnv('SENTRY_AUTH_TOKEN');
        $orgSlug = $this->getEnv('SENTRY_ORG_SLUG');
        $baseUrl = $this->getEnv('SENTRY_BASE_URL', 'https://sentry.io/api/0');

        if (! $authToken || ! $orgSlug) {
            $this->skippedCount['sentry'] = 1;

            return;
        }

        try {
            IntegrationAccount::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => IntegrationType::SENTRY,
                    'scope' => $scope,
                ],
                [
                    'access_token' => $authToken,
                    'meta' => [
                        'org_slug' => $orgSlug,
                        'base_url' => $baseUrl,
                    ],
                    'status' => IntegrationStatus::ACTIVE,
                ]
            );

            $this->importedCount['sentry'] = 1;
            Log::info("Imported Sentry credentials for user {$user->id}");
        } catch (\Exception $e) {
            $this->errors['sentry'] = $e->getMessage();
            Log::error("Failed to import Sentry: {$e->getMessage()}");
        }
    }

    /**
     * Import OpenAI credentials.
     */
    private function importOpenAI(User $user, CredentialScope $scope): void
    {
        $apiKey = $this->getEnv('OPENAI_API_KEY');

        if (! $apiKey) {
            $this->skippedCount['openai'] = 1;

            return;
        }

        try {
            IntegrationAccount::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => IntegrationType::OPENAI,
                    'scope' => $scope,
                ],
                [
                    'access_token' => $apiKey,
                    'meta' => [],
                    'status' => IntegrationStatus::ACTIVE,
                ]
            );

            $this->importedCount['openai'] = 1;
            Log::info("Imported OpenAI credentials for user {$user->id}");
        } catch (\Exception $e) {
            $this->errors['openai'] = $e->getMessage();
            Log::error("Failed to import OpenAI: {$e->getMessage()}");
        }
    }

    /**
     * Import Google Calendar credentials.
     */
    private function importCalendar(User $user, CredentialScope $scope): void
    {
        $clientId = $this->getEnv('GOOGLE_CLIENT_ID');
        $clientSecret = $this->getEnv('GOOGLE_CLIENT_SECRET');

        if (! $clientId || ! $clientSecret) {
            $this->skippedCount['calendar'] = 1;

            return;
        }

        try {
            IntegrationAccount::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => IntegrationType::CALENDAR,
                    'scope' => $scope,
                ],
                [
                    'access_token' => $clientSecret, // OAuth requires flow, this is just client creds
                    'meta' => [
                        'client_id' => $clientId,
                        'requires_oauth_flow' => true,
                    ],
                    'status' => IntegrationStatus::INACTIVE, // Needs OAuth flow
                ]
            );

            $this->importedCount['calendar'] = 1;
            Log::info("Imported Google Calendar client credentials for user {$user->id}");
        } catch (\Exception $e) {
            $this->errors['calendar'] = $e->getMessage();
            Log::error("Failed to import Calendar: {$e->getMessage()}");
        }
    }

    /**
     * Validate that credentials are secure (no placeholders).
     */
    public function validateEnvFile(string $envPath): array
    {
        if (! file_exists($envPath)) {
            return [
                'valid' => false,
                'error' => 'File not found',
            ];
        }

        $this->parsedEnv = $this->parseEnvFile($envPath);

        $credentials = [
            'TODOIST_API_TOKEN',
            'JIRA_API_TOKEN',
            'NOTION_API_TOKEN',
            'OPENAI_API_KEY',
            'SENTRY_AUTH_TOKEN',
        ];

        $found = [];
        $placeholders = [];

        foreach ($credentials as $key) {
            $value = $this->parsedEnv[$key] ?? null;

            if ($value) {
                if ($this->isPlaceholder($value)) {
                    $placeholders[] = $key;
                } else {
                    $found[] = $key;
                }
            }
        }

        return [
            'valid' => count($found) > 0,
            'found_credentials' => count($found),
            'credentials' => $found,
            'placeholders' => $placeholders,
            'total_keys' => count($this->parsedEnv),
        ];
    }
}
