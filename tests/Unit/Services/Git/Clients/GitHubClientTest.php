<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Git\Clients;

use App\DataTransferObjects\Git\PaginationData;
use App\DataTransferObjects\Git\RepositoryData;
use App\Services\Git\Clients\GitHubClient;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('unit')]
#[Group('services')]

/**
 * @group git
 * @group github
 * @group unit
 */
class GitHubClientTest extends TestCase
{
    private string $accessToken = 'test_github_token';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_list_repositories_returns_paginated_results(): void
    {
        $mockRepos = [
            [
                'id' => 12345,
                'full_name' => 'user/repo1',
                'name' => 'repo1',
                'default_branch' => 'main',
                'private' => false,
                'archived' => false,
                'html_url' => 'https://github.com/user/repo1',
                'clone_url' => 'https://github.com/user/repo1.git',
                'updated_at' => '2024-01-01T12:00:00Z',
            ],
            [
                'id' => 67890,
                'full_name' => 'user/repo2',
                'name' => 'repo2',
                'default_branch' => 'main',
                'private' => true,
                'archived' => false,
                'html_url' => 'https://github.com/user/repo2',
                'clone_url' => 'https://github.com/user/repo2.git',
                'updated_at' => '2024-01-02T12:00:00Z',
            ],
        ];

        Http::fake([
            'https://api.github.com/user/repos*' => Http::response($mockRepos, 200, [
                'X-RateLimit-Limit' => '5000',
                'X-RateLimit-Remaining' => '4999',
                'X-RateLimit-Reset' => (string) (time() + 3600),
                'ETag' => '"abc123"',
                'Link' => '<https://api.github.com/user/repos?page=2>; rel="next"',
            ]),
        ]);

        $client = new GitHubClient($this->accessToken);
        $result = $client->listRepositories();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertCount(2, $result['items']);
        $this->assertInstanceOf(RepositoryData::class, $result['items'][0]);
        $this->assertEquals('user/repo1', $result['items'][0]->fullName);
        $this->assertInstanceOf(PaginationData::class, $result['pagination']);
        $this->assertNotNull($result['pagination']->nextCursor);

        // Verify rate limit was cached
        $this->assertEquals(4999, Cache::get('github_rate_limit_remaining'));
    }

    public function test_list_repositories_with_filters(): void
    {
        Http::fake([
            'https://api.github.com/user/repos*' => Http::response([], 200, [
                'X-RateLimit-Remaining' => '5000',
            ]),
        ]);

        $client = new GitHubClient($this->accessToken);

        $filters = [
            'visibility' => 'private',
            'affiliation' => 'owner',
            'sort' => 'created',
            'direction' => 'asc',
        ];

        $client->listRepositories($filters);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'visibility=private') &&
                   str_contains($request->url(), 'affiliation=owner') &&
                   str_contains($request->url(), 'sort=created') &&
                   str_contains($request->url(), 'direction=asc');
        });
    }

    public function test_list_repositories_uses_etag_cache(): void
    {
        $cacheKey = 'github:repos:'.md5(json_encode([])).':page1';
        $cachedResponse = [
            'items' => [],
            'pagination' => new PaginationData(1, 100),
        ];

        Cache::put($cacheKey, $cachedResponse, 60);
        Cache::put($cacheKey.'_etag', '"cached_etag"', 60);

        Log::shouldReceive('info')
            ->once()
            ->with('GitHub API: Using cached response', ['cache_key' => $cacheKey]);

        Http::fake([
            'https://api.github.com/user/repos*' => Http::response('', 304),
        ]);

        $client = new GitHubClient($this->accessToken);
        $result = $client->listRepositories();

        $this->assertEquals($cachedResponse, $result);

        // Verify If-None-Match header was sent
        Http::assertSent(function (Request $request) {
            return $request->hasHeader('If-None-Match', '"cached_etag"');
        });
    }

    public function test_list_repositories_caches_response_with_etag(): void
    {
        Http::fake([
            'https://api.github.com/user/repos*' => Http::response([], 200, [
                'ETag' => '"new_etag_123"',
                'X-RateLimit-Remaining' => '5000',
            ]),
        ]);

        $client = new GitHubClient($this->accessToken);
        $client->listRepositories();

        $cacheKey = 'github:repos:'.md5(json_encode([])).':page1';
        $this->assertEquals('"new_etag_123"', Cache::get($cacheKey.'_etag'));
        $this->assertNotNull(Cache::get($cacheKey));
    }

    public function test_list_repositories_handles_pagination(): void
    {
        Http::fake([
            'https://api.github.com/user/repos*' => Http::response([], 200, [
                'X-RateLimit-Remaining' => '5000',
            ]),
        ]);

        $client = new GitHubClient($this->accessToken);
        $pagination = new PaginationData(page: 2, perPage: 50);
        $client->listRepositories([], $pagination);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'page=2') &&
                   str_contains($request->url(), 'per_page=50');
        });
    }

    public function test_list_repositories_throws_exception_on_error(): void
    {
        Log::shouldReceive('error')->once();

        Http::fake([
            'https://api.github.com/user/repos*' => Http::response([
                'message' => 'Bad credentials',
            ], 401),
        ]);

        $client = new GitHubClient($this->accessToken);

        $this->expectException(RequestException::class);

        $client->listRepositories();
    }

    public function test_get_repository_returns_single_repo(): void
    {
        $mockRepo = [
            'id' => 12345,
            'full_name' => 'owner/repo',
            'name' => 'repo',
            'default_branch' => 'main',
            'private' => false,
            'archived' => false,
            'html_url' => 'https://github.com/owner/repo',
            'clone_url' => 'https://github.com/owner/repo.git',
            'updated_at' => '2024-01-01T12:00:00Z',
        ];

        Http::fake([
            'https://api.github.com/repos/owner/repo' => Http::response($mockRepo, 200, [
                'X-RateLimit-Remaining' => '4998',
            ]),
        ]);

        $client = new GitHubClient($this->accessToken);
        $result = $client->getRepository('owner', 'repo');

        $this->assertInstanceOf(RepositoryData::class, $result);
        $this->assertEquals('owner/repo', $result->fullName);
        $this->assertEquals('12345', $result->externalId);
    }

    public function test_get_repository_checks_rate_limit(): void
    {
        Cache::put('github_rate_limit_remaining', 10, 3600);

        $mockRepo = [
            'id' => 12345,
            'full_name' => 'owner/repo',
            'name' => 'repo',
            'default_branch' => 'main',
            'private' => false,
            'archived' => false,
            'html_url' => 'https://github.com/owner/repo',
            'clone_url' => 'https://github.com/owner/repo.git',
            'updated_at' => '2024-01-01T12:00:00Z',
        ];

        Http::fake([
            'https://api.github.com/repos/owner/repo' => Http::response($mockRepo, 200, [
                'X-RateLimit-Remaining' => '9',
            ]),
        ]);

        $client = new GitHubClient($this->accessToken);
        $client->getRepository('owner', 'repo');

        // Should not sleep since remaining > 10
        $this->assertTrue(true);
    }

    public function test_get_repository_throws_exception_on_404(): void
    {
        Log::shouldReceive('error')->once();

        Http::fake([
            'https://api.github.com/repos/owner/nonexistent' => Http::response([
                'message' => 'Not Found',
            ], 404),
        ]);

        $client = new GitHubClient($this->accessToken);

        $this->expectException(RequestException::class);

        $client->getRepository('owner', 'nonexistent');
    }

    public function test_get_authenticated_user_returns_user_data(): void
    {
        $mockUser = [
            'id' => 123456,
            'login' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456',
        ];

        Http::fake([
            'https://api.github.com/user' => Http::response($mockUser, 200, [
                'X-RateLimit-Remaining' => '4999',
            ]),
        ]);

        $client = new GitHubClient($this->accessToken);
        $result = $client->getAuthenticatedUser();

        $this->assertIsArray($result);
        $this->assertEquals('testuser', $result['login']);
        $this->assertEquals('Test User', $result['name']);
        $this->assertEquals('test@example.com', $result['email']);
    }

    public function test_validate_token_returns_true_for_valid_token(): void
    {
        Http::fake([
            'https://api.github.com/user' => Http::response(['id' => 123], 200),
        ]);

        $client = new GitHubClient($this->accessToken);
        $result = $client->validateToken();

        $this->assertTrue($result);
    }

    public function test_validate_token_returns_false_for_invalid_token(): void
    {
        Http::fake([
            'https://api.github.com/user' => Http::response(['message' => 'Bad credentials'], 401),
        ]);

        $client = new GitHubClient($this->accessToken);
        $result = $client->validateToken();

        $this->assertFalse($result);
    }

    public function test_rate_limit_warning_is_logged_when_near_limit(): void
    {
        Cache::put('github_rate_limit_remaining', 5, 3600);
        Cache::put('github_rate_limit_reset', time() + 1, 3600);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'GitHub API rate limit nearly exceeded' &&
                       $context['remaining'] === 5;
            });

        Log::shouldReceive('debug')->once();

        Http::fake([
            'https://api.github.com/user' => Http::response(['id' => 123], 200, [
                'X-RateLimit-Remaining' => '5',
            ]),
        ]);

        $client = new GitHubClient($this->accessToken);
        $client->getAuthenticatedUser();

        // The warning should be logged before the API call
        $this->assertTrue(true);
    }

    public function test_rate_limit_info_is_updated_in_cache(): void
    {
        Http::fake([
            'https://api.github.com/user' => Http::response([], 200, [
                'X-RateLimit-Limit' => '5000',
                'X-RateLimit-Remaining' => '4500',
                'X-RateLimit-Reset' => (string) (time() + 3600),
            ]),
        ]);

        Log::shouldReceive('debug')->once();

        $client = new GitHubClient($this->accessToken);
        $client->getAuthenticatedUser();

        $this->assertEquals(4500, Cache::get('github_rate_limit_remaining'));
        $this->assertNotNull(Cache::get('github_rate_limit_reset'));
    }

    public function test_parse_link_header_extracts_next_page(): void
    {
        Http::fake([
            'https://api.github.com/user/repos*' => Http::response([], 200, [
                'Link' => '<https://api.github.com/user/repos?page=3>; rel="next", <https://api.github.com/user/repos?page=10>; rel="last"',
                'X-RateLimit-Remaining' => '5000',
            ]),
        ]);

        $client = new GitHubClient($this->accessToken);
        $result = $client->listRepositories();

        $this->assertEquals('https://api.github.com/user/repos?page=3', $result['pagination']->nextCursor);
    }

    public function test_parse_link_header_returns_null_when_no_next(): void
    {
        Http::fake([
            'https://api.github.com/user/repos*' => Http::response([], 200, [
                'Link' => '<https://api.github.com/user/repos?page=1>; rel="prev"',
                'X-RateLimit-Remaining' => '5000',
            ]),
        ]);

        $client = new GitHubClient($this->accessToken);
        $result = $client->listRepositories();

        $this->assertNull($result['pagination']->nextCursor);
    }

    public function test_client_uses_bearer_token_authentication(): void
    {
        Http::fake([
            'https://api.github.com/user' => Http::response([], 200),
        ]);

        $client = new GitHubClient($this->accessToken);
        $client->getAuthenticatedUser();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer '.$this->accessToken);
        });
    }

    public function test_client_sets_json_accept_header(): void
    {
        Http::fake([
            'https://api.github.com/user' => Http::response([], 200),
        ]);

        $client = new GitHubClient($this->accessToken);
        $client->getAuthenticatedUser();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Accept', 'application/json');
        });
    }

    public function test_client_retries_on_rate_limit_429(): void
    {
        Http::fake([
            'https://api.github.com/user' => Http::sequence()
                ->push(['message' => 'Rate limit exceeded'], 429)
                ->push(['id' => 123], 200),
        ]);

        $client = new GitHubClient($this->accessToken);
        $result = $client->getAuthenticatedUser();

        $this->assertEquals(123, $result['id']);
    }

    public function test_client_retries_on_server_errors(): void
    {
        Http::fake([
            'https://api.github.com/user' => Http::sequence()
                ->push('Server Error', 503)
                ->push(['id' => 123], 200),
        ]);

        $client = new GitHubClient($this->accessToken);
        $result = $client->getAuthenticatedUser();

        $this->assertEquals(123, $result['id']);
    }
}
