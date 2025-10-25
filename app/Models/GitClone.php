<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CloneStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $repository_id
 * @property string $ref
 * @property string $storage_driver
 * @property string $artifact_path
 * @property int|null $size_bytes
 * @property int|null $duration_ms
 * @property CloneStatus $status
 * @property string|null $error
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read GitRepository $repository
 */
class GitClone extends Model
{
    /** @use HasFactory<\Database\Factories\GitCloneFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'repository_id',
        'ref',
        'storage_driver',
        'artifact_path',
        'size_bytes',
        'duration_ms',
        'status',
        'error',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => CloneStatus::class,
        'size_bytes' => 'integer',
        'duration_ms' => 'integer',
    ];

    /**
     * Get the repository that this clone belongs to.
     *
     * @return BelongsTo<GitRepository, $this>
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(GitRepository::class);
    }

    /**
     * Scope a query to only include completed clones.
     *
     * @param  Builder<GitClone>  $query
     * @return Builder<GitClone>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', CloneStatus::COMPLETED);
    }

    /**
     * Scope a query to only include failed clones.
     *
     * @param  Builder<GitClone>  $query
     * @return Builder<GitClone>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', CloneStatus::FAILED);
    }

    /**
     * Scope a query to only include in-progress clones.
     *
     * @param  Builder<GitClone>  $query
     * @return Builder<GitClone>
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->whereIn('status', [CloneStatus::PENDING, CloneStatus::CLONING]);
    }

    /**
     * Mark the clone as started.
     */
    public function markAsStarted(): void
    {
        $this->update(['status' => CloneStatus::CLONING]);
    }

    /**
     * Mark the clone as completed.
     */
    public function markAsCompleted(int $sizeBytes, int $durationMs): void
    {
        $this->update([
            'status' => CloneStatus::COMPLETED,
            'size_bytes' => $sizeBytes,
            'duration_ms' => $durationMs,
            'error' => null,
        ]);
    }

    /**
     * Mark the clone as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => CloneStatus::FAILED,
            'error' => $error,
        ]);
    }

    /**
     * Get human-readable size.
     */
    public function getFormattedSize(): string
    {
        if ($this->size_bytes === null) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($this->size_bytes, 0);
        $pow = floor(($bytes !== 0 ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1024 ** $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Get human-readable duration.
     */
    public function getFormattedDuration(): string
    {
        if ($this->duration_ms === null) {
            return 'N/A';
        }

        $seconds = $this->duration_ms / 1000;

        if ($seconds < 60) {
            return round($seconds, 2).'s';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return "{$minutes}m ".round($remainingSeconds, 0).'s';
    }
}
