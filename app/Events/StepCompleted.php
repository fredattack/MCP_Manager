<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\WorkflowStep;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StepCompleted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public WorkflowStep $step) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $execution = $this->step->execution;

        return [
            new PrivateChannel("workflows.{$execution->workflow_id}"),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->step->id,
            'execution_id' => $this->step->execution_id,
            'step_name' => $this->step->step_name,
            'step_order' => $this->step->step_order,
            'status' => $this->step->status->value,
            'started_at' => $this->step->started_at?->toISOString(),
            'completed_at' => $this->step->completed_at?->toISOString(),
            'duration' => $this->step->duration,
            'output' => $this->step->output,
            'error_message' => $this->step->error_message,
        ];
    }

    public function broadcastAs(): string
    {
        return 'step.completed';
    }
}
