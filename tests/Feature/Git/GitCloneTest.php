<?php

declare(strict_types=1);

namespace Tests\Feature\Git;

use App\Enums\CloneStatus;
use App\Enums\GitConnectionStatus;
use App\Enums\GitProvider;
use App\Jobs\CloneRepositoryJob;
use App\Models\GitClone;
use App\Models\GitConnection;
use App\Models\GitRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('feature')]
#[Group('integration')]

/**
 * @group git
 * @group feature
 */
class GitCloneTest extends TestCase
{
    use RefreshDatabase;

    public function test_clone_repository_dispatches_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $connection = GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'full_name' => 'owner/repo',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/git/repositories/{$repository->external_id}/clone", [
                'provider' => 'github',
                'ref' => 'main',
                'storage' => 'local',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'repository_id',
                'ref',
                'storage_driver',
                'status',
                'artifact_path',
            ]);

        $cloneId = $response->json('id');

        // Verify clone was created
        $this->assertDatabaseHas('git_clones', [
            'id' => $cloneId,
            'repository_id' => $repository->id,
            'ref' => 'main',
            'storage_driver' => 'local',
            'status' => CloneStatus::PENDING->value,
        ]);

        // Verify job was dispatched
        Queue::assertPushed(CloneRepositoryJob::class, function ($job) use ($cloneId, $connection) {
            return $job->clone->id === $cloneId &&
                   $job->connection->id === $connection->id &&
                   $job->queue === 'git';
        });
    }

    public function test_clone_repository_defaults_to_main_branch(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/git/repositories/{$repository->external_id}/clone", [
                'provider' => 'github',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('git_clones', [
            'repository_id' => $repository->id,
            'ref' => 'main',
            'storage_driver' => 'local',
        ]);
    }

    public function test_clone_repository_validates_ref(): void
    {
        $user = User::factory()->create();
        GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/git/repositories/{$repository->external_id}/clone", [
                'provider' => 'github',
                'ref' => '', // Empty ref
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ref']);
    }

    public function test_clone_repository_validates_storage_driver(): void
    {
        $user = User::factory()->create();
        GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/git/repositories/{$repository->external_id}/clone", [
                'provider' => 'github',
                'storage' => 'invalid-storage',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['storage']);
    }

    public function test_clone_repository_requires_active_connection(): void
    {
        $user = User::factory()->create();

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/git/repositories/{$repository->external_id}/clone", [
                'provider' => 'github',
            ]);

        $response->assertStatus(422);
    }

    public function test_list_clones_returns_all_clones_for_repository(): void
    {
        $user = User::factory()->create();

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        $clone1 = GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::COMPLETED,
            'ref' => 'main',
        ]);

        $clone2 = GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::PENDING,
            'ref' => 'develop',
        ]);

        // Create clone for different repository (should not be included)
        $otherRepo = GitRepository::factory()->create(['user_id' => $user->id]);
        GitClone::factory()->create(['repository_id' => $otherRepo->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/git/repositories/{$repository->external_id}/clones?provider=github");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $clone1->id])
            ->assertJsonFragment(['id' => $clone2->id]);
    }

    public function test_list_clones_filters_by_status(): void
    {
        $user = User::factory()->create();

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::COMPLETED,
        ]);

        GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::FAILED,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/git/repositories/{$repository->external_id}/clones?provider=github&status=completed");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'completed');
    }

    public function test_show_clone_returns_single_clone(): void
    {
        $user = User::factory()->create();

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        $clone = GitClone::factory()->create([
            'repository_id' => $repository->id,
            'ref' => 'feature-branch',
            'status' => CloneStatus::COMPLETED,
            'size_bytes' => 1024000,
            'duration_ms' => 5000,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/git/clones/{$clone->id}");

        $response->assertOk()
            ->assertJson([
                'id' => $clone->id,
                'repository_id' => $repository->id,
                'ref' => 'feature-branch',
                'status' => 'completed',
                'size_bytes' => 1024000,
                'duration_ms' => 5000,
            ]);
    }

    public function test_show_clone_returns_404_for_nonexistent_clone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/git/clones/99999');

        $response->assertNotFound();
    }

    public function test_show_clone_prevents_unauthorized_access(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $repository = GitRepository::factory()->create([
            'user_id' => $user1->id,
        ]);

        $clone = GitClone::factory()->create([
            'repository_id' => $repository->id,
        ]);

        $response = $this->actingAs($user2)
            ->getJson("/api/git/clones/{$clone->id}");

        $response->assertForbidden();
    }

    public function test_clone_stores_correct_artifact_path_for_local_storage(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'owner/my-repo',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/git/repositories/{$repository->external_id}/clone", [
                'provider' => 'github',
                'ref' => 'develop',
                'storage' => 'local',
            ]);

        $response->assertCreated();

        $artifactPath = $response->json('artifact_path');
        $this->assertStringStartsWith('/data/repos/owner_my-repo/', $artifactPath);
        $this->assertStringContainsString('develop', $artifactPath);
        $this->assertStringEndsWith('.tar.gz', $artifactPath);
    }

    public function test_clone_stores_correct_artifact_path_for_s3_storage(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'owner/my-repo',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/git/repositories/{$repository->external_id}/clone", [
                'provider' => 'github',
                'ref' => 'main',
                'storage' => 's3',
            ]);

        $response->assertCreated();

        $artifactPath = $response->json('artifact_path');
        $this->assertStringStartsWith('repos/owner_my-repo/', $artifactPath);
        $this->assertStringContainsString('main', $artifactPath);
        $this->assertStringEndsWith('.tar.gz', $artifactPath);
    }

    public function test_clone_requires_authentication(): void
    {
        $repository = GitRepository::factory()->create();

        $response = $this->postJson("/api/git/repositories/{$repository->external_id}/clone", [
            'provider' => 'github',
        ]);

        $response->assertUnauthorized();
    }

    public function test_list_clones_paginates_results(): void
    {
        $user = User::factory()->create();

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        GitClone::factory()->count(15)->create([
            'repository_id' => $repository->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/git/repositories/{$repository->external_id}/clones?provider=github&per_page=10");

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 15);
    }

    public function test_clone_job_timeout_is_configured_correctly(): void
    {
        $user = User::factory()->create();
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create(['repository_id' => $repository->id]);

        $job = new CloneRepositoryJob($clone, $connection);

        // Verify 10 minute timeout for large repositories
        $this->assertEquals(600, $job->timeout);
    }
}
