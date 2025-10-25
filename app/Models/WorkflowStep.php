<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StepStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $execution_id
 * @property string $step_name
 * @property int $step_order
 * @property StepStatus $status
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property array|null $output
 * @property string|null $error_message
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read WorkflowExecution $execution
 */
class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'execution_id',
        'step_name',
        'step_order',
        'status',
        'started_at',
        'completed_at',
        'output',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status' => StepStatus::class,
            'step_order' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'output' => 'array',
        ];
    }

    public function execution(): BelongsTo
    {
        return $this->belongsTo(WorkflowExecution::class, 'execution_id');
    }

    public function getDurationAttribute(): ?float
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    public function isRunning(): bool
    {
        return $this->status === StepStatus::Running;
    }

    public function isCompleted(): bool
    {
        return $this->status === StepStatus::Completed;
    }

    public function isFailed(): bool
    {
        return $this->status === StepStatus::Failed;
    }
}
