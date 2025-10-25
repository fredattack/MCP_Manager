<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Git;

use App\Enums\GitConnectionStatus;
use App\Enums\GitProvider;
use App\Models\GitConnection;
use App\Models\GitRepository;
use App\Models\User;
use App\Services\Git\GitRepositoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('unit')]
#[Group('services')]
class GitRepositoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private GitRepositoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GitRepositoryService;
    }

    public function test_list_repositories_returns_paginated_results(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->count(5)->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        $result = $this->service->listRepositories($user, GitProvider::GITHUB, [], 3);

        $this->assertCount(3, $result->items());
        $this->assertEquals(5, $result->total());
        $this->assertTrue($result->hasMorePages());
    }

    public function test_list_repositories_filters_by_visibility(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'visibility' => 'public',
        ]);

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'visibility' => 'private',
        ]);

        $result = $this->service->listRepositories(
            $user,
            GitProvider::GITHUB,
            ['visibility' => 'private']
        );

        $this->assertCount(1, $result->items());
        $this->assertEquals('private', $result->items()[0]->visibility);
    }

    public function test_list_repositories_filters_by_archived(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'archived' => true,
        ]);

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'archived' => false,
        ]);

        $result = $this->service->listRepositories(
            $user,
            GitProvider::GITHUB,
            ['archived' => true]
        );

        $this->assertCount(1, $result->items());
        $this->assertTrue($result->items()[0]->archived);
    }

    public function test_list_repositories_searches_by_name(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'full_name' => 'user/my-awesome-repo',
        ]);

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'full_name' => 'user/another-repo',
        ]);

        $result = $this->service->listRepositories(
            $user,
            GitProvider::GITHUB,
            ['search' => 'awesome']
        );

        $this->assertCount(1, $result->items());
        $this->assertStringContainsString('awesome', $result->items()[0]->full_name);
    }

    public function test_get_repository_returns_repository(): void
    {
        $user = User::factory()->create();
        $repo = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'external_id' => '12345',
        ]);

        $result = $this->service->getRepository($user, GitProvider::GITHUB, '12345');

        $this->assertNotNull($result);
        $this->assertEquals($repo->id, $result->id);
    }

    public function test_get_repository_returns_null_if_not_found(): void
    {
        $user = User::factory()->create();

        $result = $this->service->getRepository($user, GitProvider::GITHUB, 'nonexistent');

        $this->assertNull($result);
    }

    public function test_get_statistics_returns_correct_counts(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'visibility' => 'public',
            'archived' => false,
        ]);

        GitRepository::factory()->count(2)->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'visibility' => 'private',
            'archived' => false,
        ]);

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'visibility' => 'public',
            'archived' => true,
        ]);

        $result = $this->service->getStatistics($user, GitProvider::GITHUB);

        $this->assertEquals(4, $result['total']);
        $this->assertEquals(2, $result['public']);
        $this->assertEquals(2, $result['private']);
        $this->assertEquals(1, $result['archived']);
        $this->assertEquals(3, $result['active']);
    }

    public function test_get_statistics_returns_zeros_for_no_repos(): void
    {
        $user = User::factory()->create();

        $result = $this->service->getStatistics($user, GitProvider::GITHUB);

        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['public']);
        $this->assertEquals(0, $result['private']);
        $this->assertEquals(0, $result['archived']);
        $this->assertEquals(0, $result['active']);
    }

    public function test_sync_repositories_throws_exception_if_no_active_connection(): void
    {
        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No active github connection found for user');

        $this->service->syncRepositories($user, GitProvider::GITHUB);
    }
}
