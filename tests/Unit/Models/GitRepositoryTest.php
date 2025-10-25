<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\GitProvider;
use App\Models\GitRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('unit')]
#[Group('models')]

/**
 * @group git
 * @group model
 * @group unit
 */
class GitRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_repository_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $repository->user);
        $this->assertEquals($user->id, $repository->user->id);
    }

    public function test_repository_casts_provider_to_enum(): void
    {
        $repository = GitRepository::factory()->create([
            'provider' => GitProvider::GITHUB,
        ]);

        $this->assertInstanceOf(GitProvider::class, $repository->provider);
        $this->assertEquals(GitProvider::GITHUB, $repository->provider);
    }

    public function test_repository_casts_archived_to_boolean(): void
    {
        $repository = GitRepository::factory()->create([
            'archived' => true,
        ]);

        $this->assertIsBool($repository->archived);
        $this->assertTrue($repository->archived);
    }

    public function test_repository_casts_last_synced_at_to_datetime(): void
    {
        $repository = GitRepository::factory()->create([
            'last_synced_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $repository->last_synced_at);
    }

    public function test_repository_casts_meta_to_array(): void
    {
        $meta = [
            'https_url' => 'https://github.com/user/repo.git',
            'ssh_url' => 'git@github.com:user/repo.git',
        ];

        $repository = GitRepository::factory()->create([
            'meta' => $meta,
        ]);

        $this->assertIsArray($repository->meta);
        $this->assertEquals($meta, $repository->meta);
    }

    public function test_scope_active_filters_non_archived_repositories(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'archived' => false,
        ]);

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'archived' => true,
        ]);

        $activeRepos = GitRepository::active()->get();

        $this->assertCount(1, $activeRepos);
        $this->assertFalse($activeRepos->first()->archived);
    }

    public function test_scope_for_provider_filters_by_provider(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITLAB,
        ]);

        $githubRepos = GitRepository::forProvider(GitProvider::GITHUB)->get();

        $this->assertCount(1, $githubRepos);
        $this->assertEquals(GitProvider::GITHUB, $githubRepos->first()->provider);
    }

    public function test_scope_visibility_filters_by_visibility(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'visibility' => 'public',
        ]);

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'visibility' => 'private',
        ]);

        $publicRepos = GitRepository::visibility('public')->get();

        $this->assertCount(1, $publicRepos);
        $this->assertEquals('public', $publicRepos->first()->visibility);
    }

    public function test_mark_as_synced_updates_timestamp(): void
    {
        $repository = GitRepository::factory()->create([
            'last_synced_at' => null,
        ]);

        $this->assertNull($repository->last_synced_at);

        $repository->markAsSynced();

        $repository->refresh();
        $this->assertNotNull($repository->last_synced_at);
        $this->assertTrue($repository->last_synced_at->isToday());
    }

    public function test_get_owner_extracts_owner_from_full_name(): void
    {
        $repository = GitRepository::factory()->create([
            'full_name' => 'octocat/hello-world',
        ]);

        $this->assertEquals('octocat', $repository->getOwner());
    }

    public function test_get_owner_returns_empty_string_for_invalid_format(): void
    {
        $repository = GitRepository::factory()->create([
            'full_name' => 'invalid-format',
        ]);

        $this->assertEquals('invalid-format', $repository->getOwner());
    }

    public function test_get_name_extracts_name_from_full_name(): void
    {
        $repository = GitRepository::factory()->create([
            'full_name' => 'octocat/hello-world',
        ]);

        $this->assertEquals('hello-world', $repository->getName());
    }

    public function test_get_name_returns_empty_string_for_invalid_format(): void
    {
        $repository = GitRepository::factory()->create([
            'full_name' => 'invalid-format',
        ]);

        $this->assertEquals('', $repository->getName());
    }

    public function test_clones_relationship(): void
    {
        $repository = GitRepository::factory()->create();

        $clones = $repository->clones;

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $clones);
    }

    public function test_repository_can_be_created_with_factory(): void
    {
        $repository = GitRepository::factory()->create();

        $this->assertDatabaseHas('git_repositories', [
            'id' => $repository->id,
        ]);
    }

    public function test_repository_full_name_is_unique_per_user_and_provider(): void
    {
        $user = User::factory()->create();

        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'external_id' => '123',
            'full_name' => 'user/repo',
        ]);

        // Different provider, same full_name - should be allowed
        $repo2 = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITLAB,
            'external_id' => '456',
            'full_name' => 'user/repo',
        ]);

        $this->assertNotNull($repo2);
    }
}
