<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\WorkflowExecution;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowStatusUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public WorkflowExecution $execution) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("workflows.{$this->execution->workflow_id}"),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->execution->id,
            'workflow_id' => $this->execution->workflow_id,
            'status' => $this->execution->status->value,
            'started_at' => $this->execution->started_at?->toISOString(),
            'completed_at' => $this->execution->completed_at?->toISOString(),
            'error_message' => $this->execution->error_message,
            'result' => $this->execution->result,
        ];
    }

    public function broadcastAs(): string
    {
        return 'workflow.status.updated';
    }
}
