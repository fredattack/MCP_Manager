<?php

declare(strict_types=1);

namespace App\Enums;

enum GitProvider: string
{
    case GITHUB = 'github';
    case GITLAB = 'gitlab';

    /**
     * Get the display name for the git provider.
     */
    public function displayName(): string
    {
        return match ($this) {
            self::GITHUB => 'GitHub',
            self::GITLAB => 'GitLab',
        };
    }

    /**
     * Get the description for the git provider.
     */
    public function description(): string
    {
        return match ($this) {
            self::GITHUB => 'Connect to your GitHub repositories',
            self::GITLAB => 'Connect to your GitLab repositories',
        };
    }

    /**
     * Get the OAuth authorization URL for the provider.
     */
    public function getAuthUrl(): string
    {
        return match ($this) {
            self::GITHUB => 'https://github.com/login/oauth/authorize',
            self::GITLAB => 'https://gitlab.com/oauth/authorize',
        };
    }

    /**
     * Get the OAuth token URL for the provider.
     */
    public function getTokenUrl(): string
    {
        return match ($this) {
            self::GITHUB => 'https://github.com/login/oauth/access_token',
            self::GITLAB => 'https://gitlab.com/oauth/token',
        };
    }

    /**
     * Get the API base URL for the provider.
     */
    public function getApiUrl(): string
    {
        return match ($this) {
            self::GITHUB => 'https://api.github.com',
            self::GITLAB => 'https://gitlab.com/api/v4',
        };
    }

    /**
     * Get the default scopes for the provider.
     *
     * @return array<int, string>
     */
    public function getDefaultScopes(): array
    {
        return match ($this) {
            self::GITHUB => ['repo', 'read:user', 'workflow'],
            self::GITLAB => ['api', 'read_repository', 'write_repository', 'read_user'],
        };
    }
}
