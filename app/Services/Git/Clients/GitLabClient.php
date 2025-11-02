<?php

declare(strict_types=1);

namespace App\Services\Git\Clients;

use App\DataTransferObjects\Git\PaginationData;
use App\DataTransferObjects\Git\RepositoryData;
use App\Services\Git\Contracts\GitProviderClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitLabClient implements GitProviderClient
{
    private const API_BASE_URL = 'https://gitlab.com/api/v4';

    private const RATE_LIMIT_MAX = 2000;

    private readonly string $accessTokenHash;

    private PendingRequest $http;

    public function __construct(
        private readonly string $accessToken
    ) {
        $this->accessTokenHash = hash('sha256', $this->accessToken);

        $this->http = Http::withToken($this->accessToken)
            ->baseUrl(self::API_BASE_URL)
            ->acceptJson()
            ->timeout(30)
            ->retry(3, 100, function ($exception, $request) {
                // Retry on rate limit or server errors
                if ($exception instanceof RequestException) {
                    $status = $exception->response->status();

                    return in_array($status, [429, 500, 502, 503, 504], true);
                }

                return false;
            }, throw: false);
    }

    /**
     * {@inheritDoc}
     */
    public function listRepositories(array $filters = [], ?PaginationData $pagination = null): array
    {
        $pagination ??= new PaginationData;

        $cacheKey = $this->getCacheKey('projects', $filters, $pagination);

        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $this->checkRateLimit();

            $response = $this->http->get('/projects', [
                'membership' => true,
                'visibility' => $filters['visibility'] ?? null,
                'order_by' => $filters['sort'] ?? 'last_activity_at',
                'sort' => $filters['direction'] ?? 'desc',
                'per_page' => $pagination->perPage,
                'page' => $pagination->page,
            ]);

            $response->throw();

            $this->updateRateLimitInfo($response);

            // Parse pagination from headers
            $totalPages = (int) $response->header('X-Total-Pages');
            $currentPage = (int) $response->header('X-Page');
            $nextCursor = $currentPage < $totalPages ? (string) ($currentPage + 1) : null;

            $result = [
                'items' => array_map(
                    fn ($project) => RepositoryData::fromGitLab($project),
                    $response->json()
                ),
                'pagination' => new PaginationData(
                    page: $pagination->page,
                    perPage: $pagination->perPage,
                    nextCursor: $nextCursor,
                ),
            ];

            // Cache for 60 seconds
            Cache::put($cacheKey, $result, 60);

            return $result;
        } catch (RequestException $e) {
            Log::error('GitLab API error: listRepositories', [
                'status' => $e->response->status(),
                'message' => $e->response->json('message'),
            ]);

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository(string $owner, string $repo): RepositoryData
    {
        try {
            $this->checkRateLimit();

            // GitLab uses project ID or namespace/project format
            $projectPath = urlencode("{$owner}/{$repo}");
            $response = $this->http->get("/projects/{$projectPath}");
            $response->throw();

            $this->updateRateLimitInfo($response);

            return RepositoryData::fromGitLab($response->json());
        } catch (RequestException $e) {
            Log::error('GitLab API error: getRepository', [
                'owner' => $owner,
                'repo' => $repo,
                'status' => $e->response->status(),
                'message' => $e->response->json('message'),
            ]);

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthenticatedUser(): array
    {
        try {
            $this->checkRateLimit();

            $response = $this->http->get('/user');
            $response->throw();

            $this->updateRateLimitInfo($response);

            return $response->json();
        } catch (RequestException $e) {
            Log::error('GitLab API error: getAuthenticatedUser', [
                'status' => $e->response->status(),
                'message' => $e->response->json('message'),
            ]);

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function validateToken(): bool
    {
        try {
            $response = $this->http->get('/user');

            return $response->successful();
        } catch (RequestException) {
            return false;
        }
    }

    /**
     * Check rate limit before making request.
     */
    private function checkRateLimit(): void
    {
        $remaining = (int) Cache::get($this->rateLimitCacheKey('remaining'), self::RATE_LIMIT_MAX);
        $resetAt = Cache::get($this->rateLimitCacheKey('reset'));

        if ($remaining <= 10 && $resetAt !== null) {
            $waitSeconds = max(0, $resetAt - time());

            if ($waitSeconds > 0) {
                Log::warning('GitLab API rate limit nearly exceeded', [
                    'remaining' => $remaining,
                    'reset_in_seconds' => $waitSeconds,
                ]);

                // Exponential backoff
                sleep(min($waitSeconds, 60));
            }
        }
    }

    /**
     * Update rate limit information from response headers.
     */
    private function updateRateLimitInfo(\Illuminate\Http\Client\Response $response): void
    {
        $limit = $response->header('RateLimit-Limit');
        $remaining = $response->header('RateLimit-Remaining');
        $reset = $response->header('RateLimit-Reset');

        if ($remaining !== null) {
            Cache::put($this->rateLimitCacheKey('remaining'), (int) $remaining, 3600);
        }

        if ($reset !== null) {
            Cache::put($this->rateLimitCacheKey('reset'), (int) $reset, 3600);
        }

        Log::debug('GitLab API rate limit', [
            'limit' => $limit,
            'remaining' => $remaining,
            'reset_at' => $reset ? date('Y-m-d H:i:s', (int) $reset) : null,
        ]);
    }

    /**
     * Generate cache key.
     *
     * @param  array<string, mixed>  $filters
     */
    private function getCacheKey(string $endpoint, array $filters, PaginationData $pagination): string
    {
        return sprintf(
            'gitlab:%s:%s:%s:page%d:per%d',
            $this->accessTokenHash,
            $endpoint,
            md5(json_encode($filters)),
            $pagination->page,
            $pagination->perPage
        );
    }

    private function rateLimitCacheKey(string $metric): string
    {
        return sprintf(
            'gitlab:rate_limit:%s:%s',
            $this->accessTokenHash,
            $metric
        );
    }
}
