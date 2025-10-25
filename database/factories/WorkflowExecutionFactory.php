<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkflowExecution>
 */
class WorkflowExecutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => \App\Models\Workflow::factory(),
            'user_id' => \App\Models\User::factory(),
            'repository_id' => null,
            'status' => \App\Enums\ExecutionStatus::Pending,
            'started_at' => null,
            'completed_at' => null,
            'result' => null,
            'error_message' => null,
        ];
    }

    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Enums\ExecutionStatus::Running,
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Enums\ExecutionStatus::Completed,
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
            'result' => ['success' => true],
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Enums\ExecutionStatus::Failed,
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
            'error_message' => 'Test error message',
        ]);
    }
}
