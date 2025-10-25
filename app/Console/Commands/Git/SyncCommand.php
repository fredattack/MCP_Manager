<?php

declare(strict_types=1);

namespace App\Console\Commands\Git;

use App\Enums\GitProvider;
use App\Models\GitConnection;
use App\Services\Git\GitRepositoryService;
use Illuminate\Console\Command;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:sync
                            {provider : The git provider (github, gitlab)}
                            {--force : Force full resync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize repositories from a Git provider';

    /**
     * Execute the console command.
     */
    public function handle(GitRepositoryService $repositoryService): int
    {
        $providerStr = $this->argument('provider');
        $force = $this->option('force');

        try {
            $provider = GitProvider::from($providerStr);
        } catch (\ValueError) {
            $this->error("Invalid provider: {$providerStr}");
            $this->info('Available providers: github, gitlab');

            return self::FAILURE;
        }

        // Find active connection for the current user
        // Note: In CLI context, you might want to add a --user option
        // For now, using the first active connection
        $connection = GitConnection::where('provider', $provider->value)
            ->where('status', 'active')
            ->first();

        if ($connection === null) {
            $this->error("No active {$provider->displayName()} connection found.");
            $this->info("Run 'php artisan git:connect {$provider->value}' first.");

            return self::FAILURE;
        }

        $this->info("🔄 Synchronizing repositories from {$provider->displayName()}...");
        $this->newLine();

        try {
            $startTime = microtime(true);

            $stats = $repositoryService->syncRepositories($connection, $force);

            $duration = round((microtime(true) - $startTime) * 1000);

            $this->info('✓ Synchronization completed successfully!');
            $this->newLine();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Repositories', $stats['total']],
                    ['Created', $stats['created']],
                    ['Updated', $stats['updated']],
                    ['Unchanged', $stats['unchanged']],
                    ['Duration', "{$duration}ms"],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Synchronization failed: {$e->getMessage()}");

            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
