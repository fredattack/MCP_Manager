<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\CloneStatus;
use App\Models\GitClone;
use App\Models\GitRepository;
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
class GitCloneTest extends TestCase
{
    use RefreshDatabase;

    public function test_clone_belongs_to_repository(): void
    {
        $repository = GitRepository::factory()->create();
        $clone = GitClone::factory()->create([
            'repository_id' => $repository->id,
        ]);

        $this->assertInstanceOf(GitRepository::class, $clone->repository);
        $this->assertEquals($repository->id, $clone->repository->id);
    }

    public function test_clone_casts_status_to_enum(): void
    {
        $clone = GitClone::factory()->create([
            'status' => CloneStatus::COMPLETED,
        ]);

        $this->assertInstanceOf(CloneStatus::class, $clone->status);
        $this->assertEquals(CloneStatus::COMPLETED, $clone->status);
    }

    public function test_clone_casts_size_bytes_to_integer(): void
    {
        $clone = GitClone::factory()->create([
            'size_bytes' => '1024000',
        ]);

        $this->assertIsInt($clone->size_bytes);
        $this->assertEquals(1024000, $clone->size_bytes);
    }

    public function test_clone_casts_duration_ms_to_integer(): void
    {
        $clone = GitClone::factory()->create([
            'duration_ms' => '5000',
        ]);

        $this->assertIsInt($clone->duration_ms);
        $this->assertEquals(5000, $clone->duration_ms);
    }

    public function test_scope_completed_filters_completed_clones(): void
    {
        GitClone::factory()->create(['status' => CloneStatus::COMPLETED]);
        GitClone::factory()->create(['status' => CloneStatus::FAILED]);
        GitClone::factory()->create(['status' => CloneStatus::PENDING]);

        $completedClones = GitClone::completed()->get();

        $this->assertCount(1, $completedClones);
        $this->assertEquals(CloneStatus::COMPLETED, $completedClones->first()->status);
    }

    public function test_scope_failed_filters_failed_clones(): void
    {
        GitClone::factory()->create(['status' => CloneStatus::COMPLETED]);
        GitClone::factory()->create(['status' => CloneStatus::FAILED]);
        GitClone::factory()->create(['status' => CloneStatus::PENDING]);

        $failedClones = GitClone::failed()->get();

        $this->assertCount(1, $failedClones);
        $this->assertEquals(CloneStatus::FAILED, $failedClones->first()->status);
    }

    public function test_scope_in_progress_filters_pending_and_cloning(): void
    {
        GitClone::factory()->create(['status' => CloneStatus::PENDING]);
        GitClone::factory()->create(['status' => CloneStatus::CLONING]);
        GitClone::factory()->create(['status' => CloneStatus::COMPLETED]);
        GitClone::factory()->create(['status' => CloneStatus::FAILED]);

        $inProgressClones = GitClone::inProgress()->get();

        $this->assertCount(2, $inProgressClones);
    }

    public function test_mark_as_started_updates_status(): void
    {
        $clone = GitClone::factory()->create([
            'status' => CloneStatus::PENDING,
        ]);

        $clone->markAsStarted();

        $clone->refresh();
        $this->assertEquals(CloneStatus::CLONING, $clone->status);
    }

    public function test_mark_as_completed_updates_status_and_metadata(): void
    {
        $clone = GitClone::factory()->create([
            'status' => CloneStatus::CLONING,
            'size_bytes' => null,
            'duration_ms' => null,
        ]);

        $clone->markAsCompleted(1024000, 5000);

        $clone->refresh();
        $this->assertEquals(CloneStatus::COMPLETED, $clone->status);
        $this->assertEquals(1024000, $clone->size_bytes);
        $this->assertEquals(5000, $clone->duration_ms);
        $this->assertNull($clone->error);
    }

    public function test_mark_as_failed_updates_status_and_error(): void
    {
        $clone = GitClone::factory()->create([
            'status' => CloneStatus::CLONING,
        ]);

        $errorMessage = 'Clone failed due to network error';
        $clone->markAsFailed($errorMessage);

        $clone->refresh();
        $this->assertEquals(CloneStatus::FAILED, $clone->status);
        $this->assertEquals($errorMessage, $clone->error);
    }

    public function test_get_formatted_size_returns_bytes(): void
    {
        $clone = GitClone::factory()->create([
            'size_bytes' => 512,
        ]);

        $this->assertEquals('512 B', $clone->getFormattedSize());
    }

    public function test_get_formatted_size_returns_kilobytes(): void
    {
        $clone = GitClone::factory()->create([
            'size_bytes' => 1024 * 50, // 50 KB
        ]);

        $this->assertEquals('50 KB', $clone->getFormattedSize());
    }

    public function test_get_formatted_size_returns_megabytes(): void
    {
        $clone = GitClone::factory()->create([
            'size_bytes' => 1024 * 1024 * 5, // 5 MB
        ]);

        $this->assertEquals('5 MB', $clone->getFormattedSize());
    }

    public function test_get_formatted_size_returns_gigabytes(): void
    {
        $clone = GitClone::factory()->create([
            'size_bytes' => 1024 * 1024 * 1024 * 2, // 2 GB
        ]);

        $this->assertEquals('2 GB', $clone->getFormattedSize());
    }

    public function test_get_formatted_size_returns_na_for_null(): void
    {
        $clone = GitClone::factory()->create([
            'size_bytes' => null,
        ]);

        $this->assertEquals('N/A', $clone->getFormattedSize());
    }

    public function test_get_formatted_size_handles_decimal_values(): void
    {
        $clone = GitClone::factory()->create([
            'size_bytes' => 1024 * 1024 * 1.5, // 1.5 MB
        ]);

        $formatted = $clone->getFormattedSize();
        $this->assertStringContainsString('1.5', $formatted);
        $this->assertStringContainsString('MB', $formatted);
    }

    public function test_get_formatted_duration_returns_seconds(): void
    {
        $clone = GitClone::factory()->create([
            'duration_ms' => 3500, // 3.5 seconds
        ]);

        $this->assertEquals('3.5s', $clone->getFormattedDuration());
    }

    public function test_get_formatted_duration_returns_minutes_and_seconds(): void
    {
        $clone = GitClone::factory()->create([
            'duration_ms' => 125000, // 2 minutes 5 seconds
        ]);

        $formatted = $clone->getFormattedDuration();
        $this->assertStringContainsString('2m', $formatted);
        $this->assertStringContainsString('5s', $formatted);
    }

    public function test_get_formatted_duration_returns_na_for_null(): void
    {
        $clone = GitClone::factory()->create([
            'duration_ms' => null,
        ]);

        $this->assertEquals('N/A', $clone->getFormattedDuration());
    }

    public function test_get_formatted_duration_handles_exact_minutes(): void
    {
        $clone = GitClone::factory()->create([
            'duration_ms' => 120000, // Exactly 2 minutes
        ]);

        $formatted = $clone->getFormattedDuration();
        $this->assertStringContainsString('2m', $formatted);
        $this->assertStringContainsString('0s', $formatted);
    }

    public function test_get_formatted_duration_handles_under_one_second(): void
    {
        $clone = GitClone::factory()->create([
            'duration_ms' => 500, // 0.5 seconds
        ]);

        $this->assertEquals('0.5s', $clone->getFormattedDuration());
    }

    public function test_clone_can_be_created_with_factory(): void
    {
        $clone = GitClone::factory()->create();

        $this->assertDatabaseHas('git_clones', [
            'id' => $clone->id,
        ]);
    }

    public function test_clone_has_default_pending_status(): void
    {
        $repository = GitRepository::factory()->create();
        $clone = GitClone::create([
            'repository_id' => $repository->id,
            'ref' => 'main',
            'storage_driver' => 'local',
            'artifact_path' => '/tmp/test.tar.gz',
            'status' => CloneStatus::PENDING,
        ]);

        $this->assertEquals(CloneStatus::PENDING, $clone->status);
    }
}
