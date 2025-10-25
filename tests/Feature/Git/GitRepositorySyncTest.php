<?php

declare(strict_types=1);

namespace Tests\Feature\Git;

use App\Enums\GitConnectionStatus;
use App\Enums\GitProvider;
use App\Models\GitConnection;
use App\Models\GitRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('feature')]
#[Group('integration')]

/**
 * @group git
 * @group feature
 */
class GitRepositorySyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_repositories_creates_new_repositories(): void
    {
        $user = User::factory()->create();
        GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        Http::fake([
            'https://api.github.com/user/repos*' => Http::response([
                [
                    'id' => 1,
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
                    'id' => 2,
                    'full_name' => 'user/repo2',
                    'name' => 'repo2',
                    'default_branch' => 'develop',
                    'private' => true,
                    'archived' => false,
                    'html_url' => 'https://github.com/user/repo2',
                    'clone_url' => 'https://github.com/user/repo2.git',
                    'updated_at' => '2024-01-02T12:00:00Z',
                ],
            ], 200, [
                'X-RateLimit-Remaining' => '5000',
            ]),
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/git/repositories/sync', [
                'provider' => 'github',
            ]);

        $response->assertOk()
            ->assertJson([
                'synced' => 2,
                'created' => 2,
                'updated' => 0,
            ]);

        $this->assertDatabaseHas('git_repositories', [
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB->value,
            'external_id' => '1',
            'full_name' => 'user/repo1',
        ]);

        $this->assertDatabaseHas('git_repositories', [
            'user_id' => $user->id,
            'external_id' => '2',
            'full_name' => 'user/repo2',
        ]);
    }

    public function test_sync_repositories_updates_existing_repositories(): void
    {
        $user = User::factory()->create();
        GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        $existingRepo = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'external_id' => '123',
            'full_name' => 'user/old-name',
            'default_branch' => 'master',
            'archived' => false,
        ]);

        Http::fake([
            'https://api.github.com/user/repos*' => Http::response([
                [
                    'id' => 123,
                    'full_name' => 'user/new-name',
                    'name' => 'new-name',
                    'default_branch' => 'main',
                    'private' => false,
                    'archived' => true,
                    'html_url' => 'https://github.com/user/new-name',
                    'clone_url' => 'https://github.com/user/new-name.git',
                    'updated_at' => '2024-01-01T12:00:00Z',
                ],
            ], 200, [
                'X-RateLimit-Remaining' => '5000',
            ]),
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/git/repositories/sync', [
                'provider' => 'github',
            ]);

        $response->assertOk()
            ->assertJson([
                'synced' => 1,
                'created' => 0,
                'updated' => 1,
            ]);

        $existingRepo->refresh();
        $this->assertEquals('user/new-name', $existingRepo->full_name);
        $this->assertEquals('main', $existingRepo->default_branch);
        $this->assertTrue($existingRepo->archived);
    }

    public function test_sync_repositories_fails_without_active_connection(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/git/repositories/sync', [
                'provider' => 'github',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['provider']);
    }

    public function test_list_repositories_returns_paginated_results(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->count(15)->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/repositories?provider=github&per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'external_id',
                        'full_name',
                        'default_branch',
                        'visibility',
                        'archived',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'total',
                    'per_page',
                ],
            ])
            ->assertJsonPath('meta.total', 15)
            ->assertJsonCount(10, 'data');
    }

    public function test_list_repositories_filters_by_visibility(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'visibility' => 'public',
        ]);

        GitRepository::factory()->count(2)->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/repositories?provider=github&visibility=private');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_repositories_filters_by_archived_status(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->count(3)->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'archived' => false,
        ]);

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'archived' => true,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/repositories?provider=github&archived=false');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_list_repositories_searches_by_name(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'full_name' => 'user/awesome-project',
        ]);

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'full_name' => 'user/another-repo',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/repositories?provider=github&search=awesome');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.full_name', 'user/awesome-project');
    }

    public function test_show_repository_returns_single_repository(): void
    {
        $user = User::factory()->create();

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'external_id' => 'repo123',
            'full_name' => 'user/test-repo',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/repositories/repo123?provider=github');

        $response->assertOk()
            ->assertJson([
                'id' => $repository->id,
                'external_id' => 'repo123',
                'full_name' => 'user/test-repo',
            ]);
    }

    public function test_show_repository_returns_404_if_not_found(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/git/repositories/nonexistent?provider=github');

        $response->assertNotFound();
    }

    public function test_refresh_repository_updates_from_api(): void
    {
        $user = User::factory()->create();
        GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'external_id' => '456',
            'full_name' => 'owner/repo',
            'default_branch' => 'master',
        ]);

        Http::fake([
            'https://api.github.com/repos/owner/repo' => Http::response([
                'id' => 456,
                'full_name' => 'owner/repo',
                'name' => 'repo',
                'default_branch' => 'main',
                'private' => false,
                'archived' => false,
                'html_url' => 'https://github.com/owner/repo',
                'clone_url' => 'https://github.com/owner/repo.git',
                'updated_at' => '2024-01-01T12:00:00Z',
            ], 200, [
                'X-RateLimit-Remaining' => '5000',
            ]),
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/git/repositories/456/refresh', [
                'provider' => 'github',
            ]);

        $response->assertOk();

        $repository->refresh();
        $this->assertEquals('main', $repository->default_branch);
        $this->assertNotNull($repository->last_synced_at);
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

        $response = $this->actingAs($user)
            ->getJson('/api/git/repositories/stats?provider=github');

        $response->assertOk()
            ->assertJson([
                'total' => 4,
                'public' => 2,
                'private' => 2,
                'archived' => 1,
                'active' => 3,
            ]);
    }

    public function test_repositories_are_scoped_to_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user1->id,
            'provider' => GitProvider::GITHUB,
        ]);

        GitRepository::factory()->count(2)->create([
            'user_id' => $user2->id,
            'provider' => GitProvider::GITHUB,
        ]);

        $response = $this->actingAs($user1)
            ->getJson('/api/git/repositories?provider=github');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_sync_requires_authentication(): void
    {
        $response = $this->postJson('/api/git/repositories/sync', [
            'provider' => 'github',
        ]);

        $response->assertUnauthorized();
    }

    public function test_list_requires_authentication(): void
    {
        $response = $this->getJson('/api/git/repositories?provider=github');

        $response->assertUnauthorized();
    }
}
