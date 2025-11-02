<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Git\Clients;

use App\DataTransferObjects\Git\PaginationData;
use App\DataTransferObjects\Git\RepositoryData;
use App\Services\Git\Clients\GitLabClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('gitlab')]
#[Group('unit')]
#[Group('services')]
class GitLabClientTest extends TestCase
{
    private string $accessToken = 'test_gitlab_token';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_list_repositories_returns_cached_response_without_making_request(): void
    {
        $pagination = new PaginationData(page: 1, perPage: 50);

        $cacheKey = sprintf(
            'gitlab:%s:projects:%s:page%d:per%d',
            hash('sha256', $this->accessToken),
            md5(json_encode([])),
            $pagination->page,
            $pagination->perPage
        );

        $cachedResult = [
            'items' => [
                RepositoryData::fromGitLab([
                    'id' => 1,
                    'path_with_namespace' => 'user/project',
                    'default_branch' => 'main',
                    'visibility' => 'public',
                    'archived' => false,
                    'http_url_to_repo' => 'https://gitlab.com/user/project',
                    'ssh_url_to_repo' => 'git@gitlab.com:user/project.git',
                ]),
            ],
            'pagination' => $pagination,
        ];

        Cache::put($cacheKey, $cachedResult, 60);

        Http::fake();

        $client = new GitLabClient($this->accessToken);
        $result = $client->listRepositories(pagination: $pagination);

        $this->assertSame($cachedResult, $result);
        Http::assertNothingSent();
    }

    public function test_rate_limit_cache_is_scoped_per_access_token(): void
    {
        $tokenA = 'token-a';
        $tokenB = 'token-b';

        $hashA = hash('sha256', $tokenA);
        $hashB = hash('sha256', $tokenB);

        Http::fake([
            'https://gitlab.com/api/v4/projects*' => Http::sequence()
                ->push([], 200, [
                    'RateLimit-Limit' => '2000',
                    'RateLimit-Remaining' => '0',
                    'RateLimit-Reset' => (string) (time() + 10),
                ])
                ->push([], 200, [
                    'RateLimit-Limit' => '2000',
                    'RateLimit-Remaining' => '1500',
                    'RateLimit-Reset' => (string) (time() + 20),
                ]),
        ]);

        $clientA = new GitLabClient($tokenA);
        $clientA->listRepositories();

        $clientB = new GitLabClient($tokenB);
        $clientB->listRepositories();

        $remainingKeyA = sprintf('gitlab:rate_limit:%s:remaining', $hashA);
        $resetKeyA = sprintf('gitlab:rate_limit:%s:reset', $hashA);
        $remainingKeyB = sprintf('gitlab:rate_limit:%s:remaining', $hashB);
        $resetKeyB = sprintf('gitlab:rate_limit:%s:reset', $hashB);

        $this->assertSame(0, Cache::get($remainingKeyA));
        $this->assertNotNull(Cache::get($resetKeyA));
        $this->assertSame(1500, Cache::get($remainingKeyB));
        $this->assertNotNull(Cache::get($resetKeyB));
    }
}
