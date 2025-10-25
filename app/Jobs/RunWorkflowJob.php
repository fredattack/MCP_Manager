<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Workflow\WorkflowEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunWorkflowJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $executionId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WorkflowEngine $engine): void
    {
        Log::info('Running workflow job', [
            'execution_id' => $this->executionId,
            'job_id' => $this->job->getJobId(),
        ]);

        try {
            $engine->execute($this->executionId);
        } catch (\Exception $e) {
            Log::error('Workflow job failed', [
                'execution_id' => $this->executionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the tags for the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['workflow', "execution:{$this->executionId}"];
    }
}
