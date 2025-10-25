<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\GitClone;
use App\Models\GitConnection;
use App\Services\Git\GitCloneService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CloneRepositoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GitClone $clone,
        public GitConnection $gitConnection
    ) {
        $this->onQueue('git');
    }

    /**
     * Execute the job.
     */
    public function handle(GitCloneService $cloneService): void
    {
        Log::info('Clone job started', [
            'clone_id' => $this->clone->id,
            'repository' => $this->clone->repository->full_name,
            'attempt' => $this->attempts(),
        ]);

        try {
            $cloneService->executeClone($this->clone, $this->gitConnection);

            Log::info('Clone job completed successfully', [
                'clone_id' => $this->clone->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Clone job failed', [
                'clone_id' => $this->clone->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry logic
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Clone job failed permanently', [
            'clone_id' => $this->clone->id,
            'repository' => $this->clone->repository->full_name,
            'error' => $exception->getMessage(),
        ]);

        // Ensure clone is marked as failed
        $this->clone->refresh();
        if ($this->clone->status->isInProgress()) {
            $this->clone->markAsFailed($exception->getMessage());
        }
    }
}
