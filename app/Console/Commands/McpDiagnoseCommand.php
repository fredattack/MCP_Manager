<?php

namespace App\Console\Commands;

use App\Services\McpServer\McpServerClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class McpDiagnoseCommand extends Command
{
    protected $signature = 'mcp:diagnose
                            {--detailed : Show detailed diagnostics}';

    protected $description = 'Run diagnostics on MCP Server connection';

    public function handle(McpServerClient $client): int
    {
        $this->info('MCP Server Diagnostics');
        $this->line('========================');
        $this->newLine();

        $this->components->task('Checking configuration', function () {
            $url = config('mcp-server.base_url');
            $email = config('mcp-server.service_account.email');
            $password = config('mcp-server.service_account.password');

            if (! $url) {
                throw new \RuntimeException('MCP_SERVER_URL not configured');
            }

            if (! $password) {
                throw new \RuntimeException('MCP_SERVICE_ACCOUNT_PASSWORD not configured');
            }

            if ($this->option('detailed')) {
                $this->line("  URL: {$url}");
                $this->line("  Email: {$email}");
            }

            return true;
        });

        $this->components->task('Testing connectivity', function () use ($client) {
            if ($client->isHealthy()) {
                if ($this->option('detailed')) {
                    $this->line('  MCP Server is responding');
                }

                return true;
            }

            throw new \RuntimeException('MCP Server is not responding');
        });

        $this->components->task('Testing authentication', function () use ($client) {
            try {
                $response = $client->get('/health');

                if ($this->option('detailed')) {
                    $this->line('  Service account authenticated successfully');
                }

                return true;
            } catch (\Exception $e) {
                throw new \RuntimeException('Authentication failed: '.$e->getMessage());
            }
        });

        $this->components->task('Checking database tables', function () {
            $tables = ['mcp_server_users', 'mcp_access_tokens', 'mcp_sync_logs'];

            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    throw new \RuntimeException("Table {$table} does not exist");
                }
            }

            if ($this->option('detailed')) {
                $this->line('  All required tables exist');
            }

            return true;
        });

        $this->components->task('Checking sync statistics', function () {
            $stats = [
                'total_users' => DB::table('users')->count(),
                'synced_users' => DB::table('mcp_server_users')->where('sync_status', 'synced')->count(),
                'pending_syncs' => DB::table('mcp_server_users')->where('sync_status', 'pending')->count(),
                'error_syncs' => DB::table('mcp_server_users')->where('sync_status', 'error')->count(),
                'active_tokens' => DB::table('mcp_access_tokens')->where('expires_at', '>', now())->count(),
            ];

            if ($this->option('detailed')) {
                $this->newLine();
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Total Users', $stats['total_users']],
                        ['Synced Users', $stats['synced_users']],
                        ['Pending Syncs', $stats['pending_syncs']],
                        ['Error Syncs', $stats['error_syncs']],
                        ['Active Tokens', $stats['active_tokens']],
                    ]
                );
            }

            return true;
        });

        $this->newLine();
        $this->info('✓ All diagnostics passed');

        return self::SUCCESS;
    }
}
