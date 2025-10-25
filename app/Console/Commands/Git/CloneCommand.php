<?php

declare(strict_types=1);

namespace App\Console\Commands\Git;

use App\Enums\GitProvider;
use App\Jobs\CloneRepositoryJob;
use App\Models\GitConnection;
use App\Models\GitRepository;
use App\Services\Git\GitCloneService;
use Illuminate\Console\Command;

class CloneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:clone
                            {provider : The git provider (github, gitlab)}
                            {repository : Repository full name (owner/repo)}
                            {--ref=HEAD : Branch, tag, or commit to clone}
                            {--storage=local : Storage driver (local, s3)}
                            {--async : Clone asynchronously using job queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clone a repository from a Git provider';

    /**
     * Execute the console command.
     */
    public function handle(GitCloneService $cloneService): int
    {
        $providerStr = $this->argument('provider');
        $repositoryName = $this->argument('repository');
        $ref = $this->option('ref');
        $storageDriver = $this->option('storage');
        $async = $this->option('async');

        try {
            $provider = GitProvider::from($providerStr);
        } catch (\ValueError) {
            $this->error("Invalid provider: {$providerStr}");
            $this->info('Available providers: github, gitlab');

            return self::FAILURE;
        }

        // Find active connection
        $connection = GitConnection::where('provider', $provider->value)
            ->where('status', 'active')
            ->first();

        if ($connection === null) {
            $this->error("No active {$provider->displayName()} connection found.");
            $this->info("Run 'php artisan git:connect {$provider->value}' first.");

            return self::FAILURE;
        }

        // Find repository
        $repository = GitRepository::where('user_id', $connection->user_id)
            ->where('provider', $provider->value)
            ->where('full_name', $repositoryName)
            ->first();

        if ($repository === null) {
            $this->error("Repository '{$repositoryName}' not found.");
            $this->info("Run 'php artisan git:sync {$provider->value}' to sync repositories first.");

            return self::FAILURE;
        }

        $this->info("📦 Cloning {$repository->full_name}...");
        $this->newLine();

        try {
            if ($async) {
                // Async clone via job queue
                $clone = $cloneService->initializeClone(
                    $repository,
                    $connection,
                    $ref,
                    $storageDriver
                );

                CloneRepositoryJob::dispatch($clone, $connection);

                $this->info("✓ Clone job dispatched (ID: {$clone->id})");
                $this->info("Monitor progress: php artisan git:clone:status {$clone->id}");

                return self::SUCCESS;
            }

            // Synchronous clone
            $startTime = microtime(true);

            $clone = $cloneService->initializeClone(
                $repository,
                $connection,
                $ref,
                $storageDriver
            );

            $clone->markAsStarted();

            $result = $cloneService->executeClone($clone, $connection);

            $duration = round((microtime(true) - $startTime) * 1000);

            $this->info('✓ Clone completed successfully!');
            $this->newLine();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Clone ID', $clone->id],
                    ['Repository', $repository->full_name],
                    ['Reference', $ref],
                    ['Storage Path', $result['artifact_path']],
                    ['Size', $clone->getFormattedSize()],
                    ['Duration', "{$duration}ms"],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Clone failed: {$e->getMessage()}");

            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
