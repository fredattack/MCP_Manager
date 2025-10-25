<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowExecutionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'user_id' => $this->user_id,
            'repository_id' => $this->repository_id,
            'status' => $this->status->value,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'duration' => $this->duration,
            'result' => $this->result,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'workflow' => new WorkflowResource($this->whenLoaded('workflow')),
            'repository' => $this->whenLoaded('repository', fn () => [
                'id' => $this->repository->id,
                'name' => $this->repository->name,
                'full_name' => $this->repository->full_name,
            ]),
            'steps' => WorkflowStepResource::collection($this->whenLoaded('steps')),
        ];
    }
}
