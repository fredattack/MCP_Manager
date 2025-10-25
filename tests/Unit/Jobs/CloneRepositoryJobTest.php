<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Enums\CloneStatus;
use App\Jobs\CloneRepositoryJob;
use App\Models\GitClone;
use App\Models\GitConnection;
use App\Models\GitRepository;
use App\Models\User;
use App\Services\Git\GitCloneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('unit')]
#[Group('jobs')]
class CloneRepositoryJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched_to_git_queue(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create(['repository_id' => $repository->id]);

        $job = new CloneRepositoryJob($clone, $connection);

        $this->assertEquals('git', $job->queue);
    }

    public function test_job_has_correct_retry_configuration(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create(['repository_id' => $repository->id]);

        $job = new CloneRepositoryJob($clone, $connection);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(600, $job->timeout);
    }

    public function test_handle_executes_clone_successfully(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'owner/repo',
        ]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::PENDING,
        ]);

        $mockCloneService = Mockery::mock(GitCloneService::class);
        $mockCloneService->shouldReceive('executeClone')
            ->once()
            ->with(
                Mockery::on(fn ($c) => $c->id === $clone->id),
                Mockery::on(fn ($c) => $c->id === $connection->id)
            )
            ->andReturn($clone);

        Log::shouldReceive('info')
            ->once()
            ->with('Clone job started', Mockery::type('array'));

        Log::shouldReceive('info')
            ->once()
            ->with('Clone job completed successfully', Mockery::type('array'));

        $job = new CloneRepositoryJob($clone, $connection);
        $job->handle($mockCloneService);

        $this->assertTrue(true);
    }

    public function test_handle_logs_start_with_attempt_number(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create(['repository_id' => $repository->id]);

        $mockCloneService = Mockery::mock(GitCloneService::class);
        $mockCloneService->shouldReceive('executeClone')->once()->andReturn($clone);

        Log::shouldReceive('info')
            ->once()
            ->with('Clone job started', Mockery::on(function ($context) use ($clone, $repository) {
                return $context['clone_id'] === $clone->id &&
                       $context['repository'] === $repository->full_name &&
                       isset($context['attempt']);
            }));

        Log::shouldReceive('info')->once();

        $job = new CloneRepositoryJob($clone, $connection);
        $job->handle($mockCloneService);

        $this->assertTrue(true);
    }

    public function test_handle_rethrows_exception_for_retry(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create(['repository_id' => $repository->id]);

        $mockCloneService = Mockery::mock(GitCloneService::class);
        $mockCloneService->shouldReceive('executeClone')
            ->once()
            ->andThrow(new \RuntimeException('Clone failed'));

        Log::shouldReceive('info')->once();
        Log::shouldReceive('error')
            ->once()
            ->with('Clone job failed', Mockery::on(function ($context) use ($clone) {
                return $context['clone_id'] === $clone->id &&
                       $context['error'] === 'Clone failed' &&
                       isset($context['attempt']);
            }));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Clone failed');

        $job = new CloneRepositoryJob($clone, $connection);
        $job->handle($mockCloneService);
    }

    public function test_failed_marks_clone_as_failed_if_in_progress(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::CLONING,
        ]);

        Log::shouldReceive('error')
            ->once()
            ->with('Clone job failed permanently', Mockery::on(function ($context) use ($clone, $repository) {
                return $context['clone_id'] === $clone->id &&
                       $context['repository'] === $repository->full_name &&
                       isset($context['error']);
            }));

        $exception = new \RuntimeException('Permanent failure');

        $job = new CloneRepositoryJob($clone, $connection);
        $job->failed($exception);

        $clone->refresh();
        $this->assertEquals(CloneStatus::FAILED, $clone->status);
        $this->assertStringContainsString('Permanent failure', $clone->error);
    }

    public function test_failed_does_not_update_already_failed_clone(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::FAILED,
            'error' => 'Previous error',
        ]);

        Log::shouldReceive('error')->once();

        $exception = new \RuntimeException('New error');

        $job = new CloneRepositoryJob($clone, $connection);
        $job->failed($exception);

        $clone->refresh();
        $this->assertEquals(CloneStatus::FAILED, $clone->status);
        // Error should remain the original one
        $this->assertEquals('Previous error', $clone->error);
    }

    public function test_failed_does_not_update_completed_clone(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create([
            'repository_id' => $repository->id,
            'status' => CloneStatus::COMPLETED,
            'size_bytes' => 1024,
            'duration_ms' => 5000,
            'error' => null,
        ]);

        Log::shouldReceive('error')->once();

        $exception = new \RuntimeException('Should not affect completed clone');

        $job = new CloneRepositoryJob($clone, $connection);
        $job->failed($exception);

        $clone->refresh();
        $this->assertEquals(CloneStatus::COMPLETED, $clone->status);
        $this->assertNull($clone->error);
    }

    public function test_job_serializes_models_correctly(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create(['repository_id' => $repository->id]);

        $job = new CloneRepositoryJob($clone, $connection);

        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(CloneRepositoryJob::class, $unserialized);
        $this->assertEquals($clone->id, $unserialized->clone->id);
        $this->assertEquals($connection->id, $unserialized->gitConnection->id);
    }

    public function test_job_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create(['repository_id' => $repository->id]);

        CloneRepositoryJob::dispatch($clone, $connection);

        Queue::assertPushed(CloneRepositoryJob::class, function ($job) use ($clone, $connection) {
            return $job->clone->id === $clone->id &&
                   $job->gitConnection->id === $connection->id &&
                   $job->queue === 'git';
        });
    }

    public function test_job_can_be_dispatched_with_delay(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $repository = GitRepository::factory()->create(['user_id' => $user->id]);
        $connection = GitConnection::factory()->create(['user_id' => $user->id]);
        $clone = GitClone::factory()->create(['repository_id' => $repository->id]);

        CloneRepositoryJob::dispatch($clone, $connection)->delay(now()->addMinutes(5));

        Queue::assertPushed(CloneRepositoryJob::class);
    }
}
