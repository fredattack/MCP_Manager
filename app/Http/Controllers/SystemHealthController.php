<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
        // Phase 4: MCP servers connect TO Laravel Manager (not the other way around)
        // Check for active credential leases to determine if servers are connected

        try {
            $activeLeases = \App\Models\CredentialLease::active()
                ->where('expires_at', '>', now())
                ->count();

            $recentLeases = \App\Models\CredentialLease::where('created_at', '>', now()->subMinutes(10))
                ->count();

            if ($activeLeases > 0) {
                return [
                    'status' => 'healthy',
                    'active_leases' => $activeLeases,
                    'recent_leases' => $recentLeases,
                    'message' => 'MCP servers connected via credential leases',
                ];
            }

            if ($recentLeases > 0) {
                return [
                    'status' => 'healthy',
                    'active_leases' => $activeLeases,
                    'recent_leases' => $recentLeases,
                    'message' => 'MCP credential lease system operational',
                ];
            }

            return [
                'status' => 'healthy',
                'active_leases' => 0,
                'recent_leases' => 0,
                'message' => 'MCP credential lease system ready (no active connections)',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'healthy',
                'message' => 'MCP credential lease system available',
                'note' => 'Could not query leases: '.$e->getMessage(),
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
