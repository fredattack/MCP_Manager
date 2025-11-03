<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SystemHealthController extends Controller
{
    public function index(): JsonResponse
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => $this->checkDatabase(),
                'mcp_server' => $this->checkMcpServer(),
                'application' => $this->checkApplication(),
            ],
        ];

        $allHealthy = collect($health['services'])->every(fn ($service) => $service['status'] === 'healthy');
        $health['status'] = $allHealthy ? 'healthy' : 'degraded';

        return response()->json($health);
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $driver = DB::connection()->getDriverName();

            return [
                'status' => 'healthy',
                'database' => $dbName,
                'driver' => $driver,
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: '.$e->getMessage(),
            ];
        }
    }

    protected function checkMcpServer(): array
    {
        $mcpServerUrl = config('services.mcp.server_url');

        if (! $mcpServerUrl) {
            return [
                'status' => 'not_configured',
                'message' => 'MCP server URL not configured',
            ];
        }

        try {
            $response = Http::timeout(5)->get($mcpServerUrl.'/health');

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'url' => $mcpServerUrl,
                    'message' => 'MCP server is reachable',
                ];
            }

            return [
                'status' => 'unhealthy',
                'url' => $mcpServerUrl,
                'message' => 'MCP server returned status '.$response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'url' => $mcpServerUrl,
                'message' => 'MCP server unreachable: '.$e->getMessage(),
            ];
        }
    }

    protected function checkApplication(): array
    {
        return [
            'status' => 'healthy',
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }
}
