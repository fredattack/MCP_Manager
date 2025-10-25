<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Git;

use App\Enums\CloneStatus;
use App\Models\GitClone;
use App\Models\GitConnection;
use App\Models\GitRepository;
use App\Models\User;
use App\Services\Git\GitCloneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('unit')]
#[Group('services')]

/**
 * @group git
 * @group unit
 */
class GitCloneServiceTest extends TestCase
{
    use RefreshDatabase;

    private GitCloneService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GitCloneService;
    }

    public function test_initialize_clone_creates_pending_clone(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'owner/repo',
        ]);

        Log::shouldReceive('info')->once();

        $clone = $this->service->initializeClone($repository, 'main', 'local');

        $this->assertInstanceOf(GitClone::class, $clone);
        $this->assertEquals($repository->id, $clone->repository_id);
        $this->assertEquals('main', $clone->ref);
        $this->assertEquals('local', $clone->storage_driver);
        $this->assertEquals(CloneStatus::PENDING, $clone->status);
        $this->assertStringContainsString('owner_repo', $clone->artifact_path);
        $this->assertStringContainsString('main', $clone->artifact_path);
        $this->assertStringEndsWith('.tar.gz', $clone->artifact_path);
    }

    public function test_initialize_clone_generates_local_storage_path(): void
    {
        $repository = GitRepository::factory()->create([
            'full_name' => 'test/repository',
        ]);

        Log::shouldReceive('info')->once();

        $clone = $this->service->initializeClone($repository, 'develop', 'local');

        $this->assertStringStartsWith('/data/repos/test_repository/', $clone->artifact_path);
        $this->assertStringContainsString('develop', $clone->artifact_path);
    }

    public function test_initialize_clone_generates_s3_storage_path(): void
    {
        $repository = GitRepository::factory()->create([
            'full_name' => 'test/repository',
        ]);

        Log::shouldReceive('info')->once();

        $clone = $this->service->initializeClone($repository, 'main', 's3');

        $this->assertStringStartsWith('repos/test_repository/', $clone->artifact_path);
        $this->assertStringContainsString('main', $clone->artifact_path);
    }

    public function test_execute_clone_marks_as_failed_on_exception(): void
    {
        $user = User::factory()->create();
        $connection = GitConnection::factory()->create([
            'user_id' => $user->id,
        ]);

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'meta' => ['https_url' => 'https://github.com/owner/repo.git'],
        ]);

        $clone = GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::PENDING,
        ]);

        // Make git clone fail
        Process::fake([
            'git clone*' => Process::result(errorOutput: 'Clone failed', exitCode: 1),
        ]);

        Log::shouldReceive('info')->once();
        Log::shouldReceive('error')->once();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Git clone failed');

        try {
            $this->service->executeClone($clone, $connection);
        } catch (\RuntimeException $e) {
            $clone->refresh();
            $this->assertEquals(CloneStatus::FAILED, $clone->status);
            $this->assertNotNull($clone->error);
            throw $e;
        }
    }

    public function test_execute_clone_throws_exception_if_repo_too_large(): void
    {
        $user = User::factory()->create();
        $connection = GitConnection::factory()->create([
            'user_id' => $user->id,
        ]);

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'meta' => ['https_url' => 'https://github.com/owner/repo.git'],
        ]);

        $clone = GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::PENDING,
        ]);

        Process::fake([
            'git clone*' => Process::result(exitCode: 0),
            // 3GB - exceeds 2GB limit
            'du -sb*' => Process::result(output: '3221225472', exitCode: 0),
        ]);

        Log::shouldReceive('info')->once();
        Log::shouldReceive('debug')->once(); // Git clone success log
        Log::shouldReceive('error')->once();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Repository size');
        $this->expectExceptionMessage('exceeds maximum allowed');

        $this->service->executeClone($clone, $connection);
    }

    public function test_execute_clone_throws_on_missing_https_url(): void
    {
        $user = User::factory()->create();
        $connection = GitConnection::factory()->create([
            'user_id' => $user->id,
        ]);

        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'meta' => [], // No HTTPS URL
        ]);

        $clone = GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::PENDING,
        ]);

        Log::shouldReceive('info')->once();
        Log::shouldReceive('error')->once();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Repository HTTPS URL not found');

        $this->service->executeClone($clone, $connection);
    }
}
