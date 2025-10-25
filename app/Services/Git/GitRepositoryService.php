<?php

declare(strict_types=1);

namespace App\Services\Git;

use App\Enums\GitProvider;
use App\Models\GitConnection;
use App\Models\GitRepository;
use App\Models\User;
use App\Services\Git\Clients\GitHubClient;
use App\Services\Git\Contracts\GitProviderClient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GitRepositoryService
{
    /**
     * Sync repositories from provider to database.
     *
     * @return array{synced: int, created: int, updated: int}
     */
    public function syncRepositories(User $user, GitProvider $provider): array
    {
        $connection = $this->getActiveConnection($user, $provider);
        $client = $this->getClient($connection);

        $synced = 0;
        $created = 0;
        $updated = 0;
        $page = 1;
        $hasMore = true;

        Log::info('Starting repository sync', [
            'user_id' => $user->id,
            'provider' => $provider->value,
        ]);

        while ($hasMore) {
            $response = $client->listRepositories([], new \App\DataTransferObjects\Git\PaginationData($page, 100));

            foreach ($response['items'] as $repoData) {
                $repo = GitRepository::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'provider' => $provider,
                        'external_id' => $repoData->externalId,
                    ],
                    $repoData->toArray()
                );

                if ($repo->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }

                $repo->markAsSynced();
                $synced++;
            }

            $hasMore = $response['pagination']->hasNextPage();
            $page++;

            // Safety limit
            if ($page > 50) {
                Log::warning('Sync stopped at page limit', ['page' => $page]);
                break;
            }
        }

        Log::info('Repository sync completed', [
            'user_id' => $user->id,
            'provider' => $provider->value,
            'synced' => $synced,
            'created' => $created,
            'updated' => $updated,
        ]);

        return [
            'synced' => $synced,
            'created' => $created,
            'updated' => $updated,
        ];
    }

    /**
     * List repositories from database with filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listRepositories(
        User $user,
        GitProvider $provider,
        array $filters = [],
        int $perPage = 50
    ): LengthAwarePaginator {
        $query = GitRepository::where('user_id', $user->id)
            ->where('provider', $provider)
            ->orderBy('last_synced_at', 'desc');

        // Apply filters
        if (isset($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        if (isset($filters['archived'])) {
            $query->where('archived', (bool) $filters['archived']);
        }

        if (isset($filters['search'])) {
            $query->where('full_name', 'LIKE', "%{$filters['search']}%");
        }

        return $query->paginate($perPage);
    }

    /**
     * Get a single repository.
     */
    public function getRepository(User $user, GitProvider $provider, string $externalId): ?GitRepository
    {
        return GitRepository::where('user_id', $user->id)
            ->where('provider', $provider)
            ->where('external_id', $externalId)
            ->first();
    }

    /**
     * Refresh a single repository from provider.
     */
    public function refreshRepository(User $user, GitRepository $repository): GitRepository
    {
        $connection = $this->getActiveConnection($user, $repository->provider);
        $client = $this->getClient($connection);

        [$owner, $name] = explode('/', $repository->full_name, 2);
        $repoData = $client->getRepository($owner, $name);

        $repository->fill($repoData->toArray());
        $repository->markAsSynced();
        $repository->save();

        Log::info('Repository refreshed', [
            'user_id' => $user->id,
            'provider' => $repository->provider->value,
            'repository' => $repository->full_name,
        ]);

        return $repository;
    }

    /**
     * Get statistics for user's repositories.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(User $user, GitProvider $provider): array
    {
        $stats = DB::table('git_repositories')
            ->where('user_id', $user->id)
            ->where('provider', $provider->value)
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN visibility = \'private\' THEN 1 END) as private,
                COUNT(CASE WHEN visibility = \'public\' THEN 1 END) as public,
                COUNT(CASE WHEN archived = true THEN 1 END) as archived
            ')
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'private' => $stats->private ?? 0,
            'public' => $stats->public ?? 0,
            'archived' => $stats->archived ?? 0,
            'active' => ($stats->total ?? 0) - ($stats->archived ?? 0),
        ];
    }

    /**
     * Get active connection for user and provider.
     */
    private function getActiveConnection(User $user, GitProvider $provider): GitConnection
    {
        $connection = GitConnection::where('user_id', $user->id)
            ->where('provider', $provider)
            ->active()
            ->first();

        if ($connection === null) {
            throw new \RuntimeException("No active {$provider->value} connection found for user");
        }

        // Refresh token if expired
        if ($connection->isTokenExpired()) {
            $oauthService = app(GitOAuthService::class);
            $connection = $oauthService->refreshToken($connection);
        }

        return $connection;
    }

    /**
     * Get API client for connection.
     */
    private function getClient(GitConnection $connection): GitProviderClient
    {
        return match ($connection->provider) {
            GitProvider::GITHUB => new GitHubClient($connection->getAccessToken()),
            default => throw new \RuntimeException("Provider {$connection->provider->value} not implemented"),
        };
    }
}
