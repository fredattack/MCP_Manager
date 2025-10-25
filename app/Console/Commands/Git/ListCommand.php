<?php

declare(strict_types=1);

namespace App\Console\Commands\Git;

use App\Enums\GitProvider;
use App\Models\GitConnection;
use App\Services\Git\GitRepositoryService;
use Illuminate\Console\Command;

class ListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:list
                            {provider : The git provider (github, gitlab)}
                            {--visibility= : Filter by visibility (public, private, internal)}
                            {--archived : Show only archived repositories}
                            {--search= : Search repositories by name}
                            {--limit=20 : Number of repositories to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List repositories from a Git provider';

    /**
     * Execute the console command.
     */
    public function handle(GitRepositoryService $repositoryService): int
    {
        $providerStr = $this->argument('provider');

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

        // Build filters
        $filters = [];

        if ($this->option('visibility') !== null) {
            $filters['visibility'] = $this->option('visibility');
        }

        if ($this->option('archived')) {
            $filters['archived'] = true;
        }

        if ($this->option('search') !== null) {
            $filters['search'] = $this->option('search');
        }

        $limit = (int) $this->option('limit');

        try {
            $repositories = $repositoryService->listRepositories(
                $connection,
                $filters,
                1,
                $limit
            );

            if ($repositories->isEmpty()) {
                $this->warn('No repositories found matching the criteria.');

                return self::SUCCESS;
            }

            $this->info("📦 Found {$repositories->count()} repositories:");
            $this->newLine();

            $tableData = $repositories->map(function ($repo) {
                return [
                    $repo->full_name,
                    $repo->visibility,
                    $repo->archived ? '✓' : '',
                    $repo->default_branch,
                    $repo->last_synced_at?->diffForHumans() ?? 'Never',
                ];
            })->toArray();

            $this->table(
                ['Repository', 'Visibility', 'Archived', 'Branch', 'Last Synced'],
                $tableData
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to list repositories: {$e->getMessage()}");

            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
