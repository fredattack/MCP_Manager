<?php

namespace App\Console\Commands;

use App\Events\UserCreatedInManager;
use App\Models\User;
use Illuminate\Console\Command;

class SyncUsersToMcpCommand extends Command
{
    protected $signature = 'mcp:sync-users
                            {--force : Force sync all users, even if already synced}
                            {--user-id= : Sync specific user by ID}';

    protected $description = 'Sync Laravel users to MCP Server';

    public function handle(): int
    {
        if (! config('mcp-server.sync.enabled', true)) {
            $this->error('MCP sync is disabled in configuration.');

            return self::FAILURE;
        }

        $this->info('MCP User Synchronization');
        $this->line('========================');
        $this->newLine();

        // Get users to sync
        $query = User::query();

        if ($userId = $this->option('user-id')) {
            $query->where('id', $userId);
            $this->info("Syncing specific user ID: {$userId}");
        } else {
            if (! $this->option('force')) {
                // Only sync users not yet synced
                $query->whereDoesntHave('mcpServerUser');
                $this->info('Syncing users not yet synchronized...');
            } else {
                $this->info('Force syncing ALL users...');
            }
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('No users to sync.');

            return self::SUCCESS;
        }

        $this->info("Found {$users->count()} user(s) to sync.");
        $this->newLine();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $synced = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                event(new UserCreatedInManager($user));
                $synced++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to sync user {$user->email}: {$e->getMessage()}");
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('═══════════════════════════════════════════════════');
        $this->info('Sync Complete!');
        $this->info("  ✓ Synced: {$synced}");

        if ($failed > 0) {
            $this->error("  ✗ Failed: {$failed}");
        }

        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        if ($synced > 0) {
            $this->info('📡 Sync jobs dispatched to queue.');
            $this->info('Run: php artisan queue:work to process synchronization.');
        }

        return self::SUCCESS;
    }
}
