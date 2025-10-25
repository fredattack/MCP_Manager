<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Git;

readonly class RepositoryData
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $provider,
        public string $externalId,
        public string $fullName,
        public string $defaultBranch,
        public string $visibility,
        public bool $archived,
        public string $httpsUrl,
        public string $sshUrl,
        public array $meta = [],
    ) {}

    /**
     * Create from GitHub API response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromGitHub(array $data): self
    {
        return new self(
            provider: 'github',
            externalId: (string) $data['id'],
            fullName: $data['full_name'],
            defaultBranch: $data['default_branch'] ?? 'main',
            visibility: $data['private'] ? 'private' : 'public',
            archived: $data['archived'] ?? false,
            httpsUrl: $data['clone_url'] ?? $data['html_url'],
            sshUrl: $data['ssh_url'] ?? '',
            meta: [
                'description' => $data['description'] ?? null,
                'language' => $data['language'] ?? null,
                'stars' => $data['stargazers_count'] ?? 0,
                'forks' => $data['forks_count'] ?? 0,
                'open_issues' => $data['open_issues_count'] ?? 0,
                'size_kb' => $data['size'] ?? 0,
                'created_at' => $data['created_at'] ?? null,
                'updated_at' => $data['updated_at'] ?? null,
                'pushed_at' => $data['pushed_at'] ?? null,
            ],
        );
    }

    /**
     * Create from GitLab API response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromGitLab(array $data): self
    {
        $visibility = match ($data['visibility'] ?? 'private') {
            'public' => 'public',
            'internal' => 'internal',
            default => 'private',
        };

        return new self(
            provider: 'gitlab',
            externalId: (string) $data['id'],
            fullName: $data['path_with_namespace'],
            defaultBranch: $data['default_branch'] ?? 'main',
            visibility: $visibility,
            archived: $data['archived'] ?? false,
            httpsUrl: $data['http_url_to_repo'] ?? '',
            sshUrl: $data['ssh_url_to_repo'] ?? '',
            meta: [
                'description' => $data['description'] ?? null,
                'language' => null,
                'stars' => $data['star_count'] ?? 0,
                'forks' => $data['forks_count'] ?? 0,
                'open_issues' => $data['open_issues_count'] ?? 0,
                'created_at' => $data['created_at'] ?? null,
                'updated_at' => $data['last_activity_at'] ?? null,
            ],
        );
    }

    /**
     * Convert to array for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'external_id' => $this->externalId,
            'full_name' => $this->fullName,
            'default_branch' => $this->defaultBranch,
            'visibility' => $this->visibility,
            'archived' => $this->archived,
            'meta' => array_merge($this->meta, [
                'https_url' => $this->httpsUrl,
                'ssh_url' => $this->sshUrl,
            ]),
        ];
    }
}
