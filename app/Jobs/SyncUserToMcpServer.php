<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\McpServer\McpUserSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncUserToMcpServer implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public User $user,
        public string $action = 'create'
    ) {
        $this->onQueue(config('mcp-server.sync.queue', 'default'));
    }

    public function handle(McpUserSyncService $syncService): void
    {
        if (! config('mcp-server.sync.enabled', true)) {
            Log::channel('mcp')->info('MCP sync is disabled, skipping sync', [
                'user_id' => $this->user->id,
                'action' => $this->action,
            ]);

            return;
        }

        $syncService->syncUser($this->user, $this->action);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('mcp')->error('MCP user sync failed after all retries', [
            'user_id' => $this->user->id,
            'action' => $this->action,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $this->user->mcpServerUser?->markAsError(
            "Sync failed after {$this->tries} attempts: {$exception->getMessage()}"
        );
    }
}
