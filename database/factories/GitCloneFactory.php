<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CloneStatus;
use App\Models\GitClone;
use App\Models\GitRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GitClone>
 */
class GitCloneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GitClone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(CloneStatus::cases());
        $sizeBytes = $status === CloneStatus::COMPLETED ? $this->faker->numberBetween(1024, 104857600) : null;
        $durationMs = $status->isFinished() ? $this->faker->numberBetween(1000, 60000) : null;

        return [
            'repository_id' => GitRepository::factory(),
            'ref' => $this->faker->randomElement(['main', 'master', 'develop', 'v1.0.0', 'feature/new-api']),
            'storage_driver' => $this->faker->randomElement(['local', 's3']),
            'artifact_path' => '/data/repos/'.$this->faker->uuid().'.tar.gz',
            'size_bytes' => $sizeBytes,
            'duration_ms' => $durationMs,
            'status' => $status,
            'error' => $status === CloneStatus::FAILED ? $this->faker->sentence() : null,
        ];
    }

    /**
     * Indicate that the clone is pending.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitClone>
     */
    public function pending(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => CloneStatus::PENDING,
                'size_bytes' => null,
                'duration_ms' => null,
                'error' => null,
            ];
        });
    }

    /**
     * Indicate that the clone is in progress.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitClone>
     */
    public function cloning(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => CloneStatus::CLONING,
                'size_bytes' => null,
                'duration_ms' => null,
                'error' => null,
            ];
        });
    }

    /**
     * Indicate that the clone is completed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitClone>
     */
    public function completed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => CloneStatus::COMPLETED,
                'size_bytes' => $this->faker->numberBetween(1024, 104857600),
                'duration_ms' => $this->faker->numberBetween(1000, 60000),
                'error' => null,
            ];
        });
    }

    /**
     * Indicate that the clone has failed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitClone>
     */
    public function failed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => CloneStatus::FAILED,
                'size_bytes' => null,
                'duration_ms' => $this->faker->numberBetween(1000, 10000),
                'error' => $this->faker->randomElement([
                    'Authentication failed',
                    'Repository not found',
                    'Network timeout',
                    'Disk quota exceeded',
                    'Invalid ref',
                ]),
            ];
        });
    }

    /**
     * Indicate that the clone uses local storage.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitClone>
     */
    public function local(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'storage_driver' => 'local',
                'artifact_path' => '/data/repos/'.$this->faker->uuid(),
            ];
        });
    }

    /**
     * Indicate that the clone uses S3 storage.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitClone>
     */
    public function s3(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'storage_driver' => 's3',
                'artifact_path' => 's3://bucket/repos/'.$this->faker->uuid().'.tar.gz',
            ];
        });
    }
}
