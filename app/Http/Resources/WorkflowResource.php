<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'config' => $this->config,
            'status' => $this->status->value,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'latest_execution' => new WorkflowExecutionResource($this->whenLoaded('latestExecution')),
            'executions_count' => $this->when(
                $this->relationLoaded('executions'),
                fn () => $this->executions->count()
            ),
        ];
    }
}
