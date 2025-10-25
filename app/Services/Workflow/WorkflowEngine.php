<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Enums\ExecutionStatus;
use App\Enums\StepStatus;
use App\Models\WorkflowExecution;
use App\Models\WorkflowStep;
use App\Services\Workflow\Actions\BaseAction;
use Illuminate\Support\Facades\Log;

class WorkflowEngine
{
    /**
     * Execute a workflow
     */
    public function execute(int $executionId): WorkflowExecution
    {
        $execution = WorkflowExecution::with(['workflow', 'repository'])->findOrFail($executionId);

        Log::info('Starting workflow execution', [
            'execution_id' => $execution->id,
            'workflow_id' => $execution->workflow_id,
            'workflow_name' => $execution->workflow->name,
        ]);

        try {
            $execution->update([
                'status' => ExecutionStatus::Running,
                'started_at' => now(),
            ]);

            // Get the action class from workflow config
            $actionClass = $execution->workflow->config['action_class'] ?? null;

            if (! $actionClass || ! class_exists($actionClass)) {
                throw new \Exception("Invalid action class: {$actionClass}");
            }

            /** @var BaseAction $action */
            $action = app($actionClass);

            if (! ($action instanceof BaseAction)) {
                throw new \Exception('Action must extend BaseAction');
            }

            // Execute the action
            $result = $action->execute($execution);

            // Mark as completed
            $execution->update([
                'status' => ExecutionStatus::Completed,
                'completed_at' => now(),
                'result' => $result,
            ]);

            Log::info('Workflow execution completed successfully', [
                'execution_id' => $execution->id,
                'duration' => $execution->duration,
            ]);

            return $execution->fresh();

        } catch (\Exception $e) {
            Log::error('Workflow execution failed', [
                'execution_id' => $execution->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $execution->update([
                'status' => ExecutionStatus::Failed,
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            // Mark any running steps as failed
            $execution->steps()
                ->where('status', StepStatus::Running)
                ->update([
                    'status' => StepStatus::Failed,
                    'completed_at' => now(),
                    'error_message' => 'Workflow execution failed',
                ]);

            throw $e;
        }
    }

    /**
     * Create a step for an execution
     */
    public function createStep(
        WorkflowExecution $execution,
        string $stepName,
        int $stepOrder
    ): WorkflowStep {
        return WorkflowStep::create([
            'execution_id' => $execution->id,
            'step_name' => $stepName,
            'step_order' => $stepOrder,
            'status' => StepStatus::Pending,
        ]);
    }

    /**
     * Mark a step as running
     */
    public function startStep(WorkflowStep $step): void
    {
        $step->update([
            'status' => StepStatus::Running,
            'started_at' => now(),
        ]);

        Log::info('Step started', [
            'step_id' => $step->id,
            'step_name' => $step->step_name,
            'execution_id' => $step->execution_id,
        ]);
    }

    /**
     * Mark a step as completed
     *
     * @param  array<string, mixed>  $output
     */
    public function completeStep(WorkflowStep $step, array $output = []): void
    {
        $step->update([
            'status' => StepStatus::Completed,
            'completed_at' => now(),
            'output' => $output,
        ]);

        Log::info('Step completed', [
            'step_id' => $step->id,
            'step_name' => $step->step_name,
            'duration' => $step->duration,
        ]);
    }

    /**
     * Mark a step as failed
     */
    public function failStep(WorkflowStep $step, string $errorMessage): void
    {
        $step->update([
            'status' => StepStatus::Failed,
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);

        Log::error('Step failed', [
            'step_id' => $step->id,
            'step_name' => $step->step_name,
            'error' => $errorMessage,
        ]);
    }
}
