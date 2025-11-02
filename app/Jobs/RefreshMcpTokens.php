<?php

namespace App\Jobs;

use App\Services\McpServer\McpTokenManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RefreshMcpTokens implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct() {}

    public function handle(McpTokenManager $tokenManager): void
    {
        if (! config('mcp-server.tokens.auto_refresh', true)) {
            Log::channel('mcp')->debug('Auto token refresh is disabled');

            return;
        }

        Log::channel('mcp')->info('Starting automatic token refresh');

        $results = $tokenManager->refreshExpiringSoonTokens();

        Log::channel('mcp')->info('Token refresh completed', [
            'success' => $results['success'],
            'failed' => $results['failed'],
            'errors' => $results['errors'],
        ]);

        if ($results['failed'] > 0) {
            Log::channel('mcp')->warning('Some tokens failed to refresh', [
                'failed_count' => $results['failed'],
                'errors' => $results['errors'],
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('mcp')->error('Automatic token refresh job failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
