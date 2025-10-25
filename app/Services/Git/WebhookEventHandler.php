<?php

declare(strict_types=1);

namespace App\Services\Git;

use App\Enums\GitProvider;
use App\Models\GitRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebhookEventHandler
{
    /**
     * Handle push event.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handlePush(GitProvider $provider, array $payload): void
    {
        $repoData = $this->extractRepositoryData($provider, $payload);

        if ($repoData === null) {
            Log::warning('Could not extract repository data from push event', [
                'provider' => $provider->value,
            ]);

            return;
        }

        // Find repository in database
        $repository = GitRepository::where('provider', $provider)
            ->where('external_id', $repoData['external_id'])
            ->first();

        if ($repository === null) {
            Log::info('Push event for unknown repository', [
                'provider' => $provider->value,
                'repository' => $repoData['full_name'],
            ]);

            return;
        }

        // Update repository metadata
        $repository->update([
            'default_branch' => $repoData['default_branch'] ?? $repository->default_branch,
            'last_synced_at' => now(),
        ]);

        // Invalidate cache
        $this->invalidateRepositoryCache($provider, $repository->user_id);

        Log::info('Push event processed', [
            'provider' => $provider->value,
            'repository' => $repository->full_name,
            'ref' => $this->extractRef($provider, $payload),
        ]);
    }

    /**
     * Handle pull request / merge request event.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handlePullRequest(GitProvider $provider, array $payload): void
    {
        $repoData = $this->extractRepositoryData($provider, $payload);
        $prData = $this->extractPullRequestData($provider, $payload);

        if ($repoData === null || $prData === null) {
            Log::warning('Could not extract PR data from event', [
                'provider' => $provider->value,
            ]);

            return;
        }

        Log::info('Pull request event received', [
            'provider' => $provider->value,
            'repository' => $repoData['full_name'],
            'action' => $prData['action'],
            'pr_number' => $prData['number'],
            'pr_title' => $prData['title'],
            'state' => $prData['state'],
        ]);

        // Future: Trigger automated workflows, notifications, etc.
    }

    /**
     * Extract repository data from webhook payload.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function extractRepositoryData(GitProvider $provider, array $payload): ?array
    {
        return match ($provider) {
            GitProvider::GITHUB => $this->extractGitHubRepository($payload),
            GitProvider::GITLAB => $this->extractGitLabRepository($payload),
        };
    }

    /**
     * Extract GitHub repository data.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function extractGitHubRepository(array $payload): ?array
    {
        $repo = $payload['repository'] ?? null;

        if ($repo === null) {
            return null;
        }

        return [
            'external_id' => (string) $repo['id'],
            'full_name' => $repo['full_name'],
            'default_branch' => $repo['default_branch'] ?? 'main',
        ];
    }

    /**
     * Extract GitLab repository data.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function extractGitLabRepository(array $payload): ?array
    {
        $project = $payload['project'] ?? null;

        if ($project === null) {
            return null;
        }

        return [
            'external_id' => (string) $project['id'],
            'full_name' => $project['path_with_namespace'],
            'default_branch' => $project['default_branch'] ?? 'main',
        ];
    }

    /**
     * Extract pull request data.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function extractPullRequestData(GitProvider $provider, array $payload): ?array
    {
        return match ($provider) {
            GitProvider::GITHUB => $this->extractGitHubPullRequest($payload),
            GitProvider::GITLAB => $this->extractGitLabMergeRequest($payload),
        };
    }

    /**
     * Extract GitHub pull request data.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function extractGitHubPullRequest(array $payload): ?array
    {
        $pr = $payload['pull_request'] ?? null;

        if ($pr === null) {
            return null;
        }

        return [
            'number' => $pr['number'],
            'title' => $pr['title'],
            'state' => $pr['state'],
            'action' => $payload['action'] ?? 'unknown',
            'author' => $pr['user']['login'] ?? 'unknown',
        ];
    }

    /**
     * Extract GitLab merge request data.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function extractGitLabMergeRequest(array $payload): ?array
    {
        $mr = $payload['object_attributes'] ?? null;

        if ($mr === null) {
            return null;
        }

        return [
            'number' => $mr['iid'],
            'title' => $mr['title'],
            'state' => $mr['state'],
            'action' => $mr['action'] ?? 'unknown',
            'author' => $mr['author']['username'] ?? 'unknown',
        ];
    }

    /**
     * Extract ref from push event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function extractRef(GitProvider $provider, array $payload): ?string
    {
        return match ($provider) {
            GitProvider::GITHUB => $payload['ref'] ?? null,
            GitProvider::GITLAB => $payload['ref'] ?? null,
        };
    }

    /**
     * Invalidate repository cache for user.
     */
    private function invalidateRepositoryCache(GitProvider $provider, int $userId): void
    {
        $cacheKey = sprintf('github:repos:%s:page*', md5(json_encode([])));
        Cache::forget($cacheKey);

        Log::debug('Repository cache invalidated', [
            'provider' => $provider->value,
            'user_id' => $userId,
        ]);
    }
}
