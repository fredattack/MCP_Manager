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

class GitHubClient implements GitProviderClient
{
    private const API_BASE_URL = 'https://api.github.com';

    private const RATE_LIMIT_MAX = 5000;

    private PendingRequest $http;

    public function __construct(
        private readonly string $accessToken
    ) {
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

        $cacheKey = $this->getCacheKey('repos', $filters, $pagination);

        // Try ETag cache first
        $cachedResponse = Cache::get($cacheKey);
        $etag = Cache::get("{$cacheKey}_etag");

        $headers = [];
        if ($etag !== null) {
            $headers['If-None-Match'] = $etag;
        }

        try {
            $this->checkRateLimit();

            $response = $this->http
                ->withHeaders($headers)
                ->get('/user/repos', [
                    'visibility' => $filters['visibility'] ?? 'all',
                    'affiliation' => $filters['affiliation'] ?? 'owner,collaborator,organization_member',
                    'sort' => $filters['sort'] ?? 'updated',
                    'direction' => $filters['direction'] ?? 'desc',
                    'per_page' => $pagination->perPage,
                    'page' => $pagination->page,
                ]);

            // Handle 304 Not Modified (cached response)
            if ($response->status() === 304 && $cachedResponse !== null) {
                Log::info('GitHub API: Using cached response', ['cache_key' => $cacheKey]);

                return $cachedResponse;
            }

            $response->throw();

            $this->updateRateLimitInfo($response);

            // Parse Link header for pagination
            $nextCursor = $this->parseLinkHeader($response->header('Link'));

            $result = [
                'items' => array_map(
                    fn ($repo) => RepositoryData::fromGitHub($repo),
                    $response->json()
                ),
                'pagination' => new PaginationData(
                    page: $pagination->page,
                    perPage: $pagination->perPage,
                    nextCursor: $nextCursor,
                ),
            ];

            // Cache for 60 seconds with ETag
            if ($newEtag = $response->header('ETag')) {
                Cache::put($cacheKey, $result, 60);
                Cache::put("{$cacheKey}_etag", $newEtag, 60);
            }

            return $result;
        } catch (RequestException $e) {
            Log::error('GitHub API error: listRepositories', [
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

            $response = $this->http->get("/repos/{$owner}/{$repo}");
            $response->throw();

            $this->updateRateLimitInfo($response);

            return RepositoryData::fromGitHub($response->json());
        } catch (RequestException $e) {
            Log::error('GitHub API error: getRepository', [
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
            Log::error('GitHub API error: getAuthenticatedUser', [
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
        $remaining = (int) Cache::get('github_rate_limit_remaining', self::RATE_LIMIT_MAX);
        $resetAt = Cache::get('github_rate_limit_reset');

        if ($remaining <= 10 && $resetAt !== null) {
            $waitSeconds = max(0, $resetAt - time());

            if ($waitSeconds > 0) {
                Log::warning('GitHub API rate limit nearly exceeded', [
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
        $limit = $response->header('X-RateLimit-Limit');
        $remaining = $response->header('X-RateLimit-Remaining');
        $reset = $response->header('X-RateLimit-Reset');

        if ($remaining !== null) {
            Cache::put('github_rate_limit_remaining', (int) $remaining, 3600);
        }

        if ($reset !== null) {
            Cache::put('github_rate_limit_reset', (int) $reset, 3600);
        }

        Log::debug('GitHub API rate limit', [
            'limit' => $limit,
            'remaining' => $remaining,
            'reset_at' => $reset ? date('Y-m-d H:i:s', (int) $reset) : null,
        ]);
    }

    /**
     * Parse Link header for next page URL.
     */
    private function parseLinkHeader(?string $linkHeader): ?string
    {
        if ($linkHeader === null) {
            return null;
        }

        // Example: <https://api.github.com/user/repos?page=2>; rel="next"
        if (preg_match('/<([^>]+)>;\s*rel="next"/', $linkHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Generate cache key.
     *
     * @param  array<string, mixed>  $filters
     */
    private function getCacheKey(string $endpoint, array $filters, PaginationData $pagination): string
    {
        return sprintf(
            'github:%s:%s:page%d',
            $endpoint,
            md5(json_encode($filters)),
            $pagination->page
        );
    }
}
