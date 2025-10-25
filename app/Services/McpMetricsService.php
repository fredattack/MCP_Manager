<?php

namespace App\Services;

use App\Models\McpServer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class McpMetricsService
{
    /**
     * Collect all metrics for a user
     */
    public function collectMetrics(User $user): array
    {
        return [
            'server' => $this->getServerMetrics($user),
            'integrations' => $this->getIntegrationMetrics($user),
            'performance' => $this->getPerformanceMetrics($user),
            'usage' => $this->getUsageMetrics($user),
            'alerts' => $this->getActiveAlerts($user),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get server metrics
     */
    public function getServerMetrics(User $user): array
    {
        $server = $user->mcpServer;

        if (! $server) {
            return [
                'status' => 'not_configured',
                'uptime' => 0,
                'latency' => null,
            ];
        }

        // Calculate uptime
        $uptime = $this->calculateUptime($server);

        // Get latency history
        $latencyHistory = $this->getLatencyHistory($server->id);

        return [
            'status' => $server->status,
            'uptime' => $uptime,
            'latency' => [
                'current' => $this->getCurrentLatency($server),
                'average' => $this->getAverageLatency($latencyHistory),
                'min' => min($latencyHistory ?: [0]),
                'max' => max($latencyHistory ?: [0]),
                'history' => $latencyHistory,
            ],
            'last_check' => $server->updated_at->toIso8601String(),
            'connection_count' => $this->getConnectionCount($server),
        ];
    }

    /**
     * Get integration metrics
     */
    public function getIntegrationMetrics(User $user): array
    {
        $integrations = $user->mcpIntegrations;

        $metrics = [
            'total' => $integrations->count(),
            'active' => $integrations->where('status', 'active')->count(),
            'error' => $integrations->where('status', 'error')->count(),
            'inactive' => $integrations->where('status', 'inactive')->count(),
            'by_service' => [],
            'health_score' => 0,
        ];

        // Group by service
        foreach ($integrations->groupBy('service_name') as $service => $serviceIntegrations) {
            $metrics['by_service'][$service] = [
                'count' => $serviceIntegrations->count(),
                'active' => $serviceIntegrations->where('status', 'active')->count(),
                'error' => $serviceIntegrations->where('status', 'error')->count(),
                'last_sync' => $serviceIntegrations->max('last_sync_at'),
                'success_rate' => $this->calculateSuccessRate($service, $user->id),
            ];
        }

        // Calculate overall health score (0-100)
        if ($metrics['total'] > 0) {
            $metrics['health_score'] = round(($metrics['active'] / $metrics['total']) * 100);
        }

        return $metrics;
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(User $user): array
    {
        $timeframe = Carbon::now()->subHours(24);

        return [
            'api_calls' => [
                'total' => $this->getApiCallCount($user->id, $timeframe),
                'successful' => $this->getSuccessfulApiCallCount($user->id, $timeframe),
                'failed' => $this->getFailedApiCallCount($user->id, $timeframe),
                'average_response_time' => $this->getAverageResponseTime($user->id, $timeframe),
            ],
            'sync_operations' => [
                'total' => $this->getSyncOperationCount($user->id, $timeframe),
                'successful' => $this->getSuccessfulSyncCount($user->id, $timeframe),
                'failed' => $this->getFailedSyncCount($user->id, $timeframe),
            ],
            'error_rate' => $this->calculateErrorRate($user->id, $timeframe),
            'throughput' => $this->calculateThroughput($user->id, $timeframe),
        ];
    }

    /**
     * Get usage metrics
     */
    public function getUsageMetrics(User $user): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => [
                'api_calls' => $this->getApiCallCount($user->id, $today),
                'sync_operations' => $this->getSyncOperationCount($user->id, $today),
                'errors' => $this->getErrorCount($user->id, $today),
            ],
            'this_week' => [
                'api_calls' => $this->getApiCallCount($user->id, $thisWeek),
                'sync_operations' => $this->getSyncOperationCount($user->id, $thisWeek),
                'errors' => $this->getErrorCount($user->id, $thisWeek),
            ],
            'this_month' => [
                'api_calls' => $this->getApiCallCount($user->id, $thisMonth),
                'sync_operations' => $this->getSyncOperationCount($user->id, $thisMonth),
                'errors' => $this->getErrorCount($user->id, $thisMonth),
            ],
            'trending' => $this->getUsageTrend($user->id),
        ];
    }

    /**
     * Get active alerts
     */
    public function getActiveAlerts(User $user): array
    {
        $alerts = [];

        // Check server status
        if ($user->mcpServer && $user->mcpServer->status === 'error') {
            $alerts[] = [
                'level' => 'critical',
                'type' => 'server_error',
                'message' => 'MCP server connection error',
                'context' => ['error' => $user->mcpServer->error_message],
                'since' => $user->mcpServer->updated_at->toIso8601String(),
            ];
        }

        // Check integration errors
        $errorIntegrations = $user->mcpIntegrations()->where('status', 'error')->get();
        foreach ($errorIntegrations as $integration) {
            $alerts[] = [
                'level' => 'warning',
                'type' => 'integration_error',
                'message' => "Integration error: {$integration->service_name}",
                'context' => [
                    'service' => $integration->service_name,
                    'error' => $integration->error_message,
                ],
                'since' => $integration->updated_at->toIso8601String(),
            ];
        }

        // Check high latency
        if ($user->mcpServer) {
            $currentLatency = $this->getCurrentLatency($user->mcpServer);
            if ($currentLatency > 500) { // More than 500ms
                $alerts[] = [
                    'level' => 'warning',
                    'type' => 'high_latency',
                    'message' => 'High server latency detected',
                    'context' => ['latency' => $currentLatency],
                    'since' => now()->toIso8601String(),
                ];
            }
        }

        // Check failed sync operations
        $recentFailures = $this->getRecentSyncFailures($user->id);
        if ($recentFailures > 5) {
            $alerts[] = [
                'level' => 'warning',
                'type' => 'sync_failures',
                'message' => 'Multiple sync failures detected',
                'context' => ['count' => $recentFailures],
                'since' => now()->toIso8601String(),
            ];
        }

        return $alerts;
    }

    /**
     * Record metric event
     */
    public function recordMetric(string $type, $userId, array $data = []): void
    {
        DB::table('mcp_metrics')->insert([
            'user_id' => $userId,
            'type' => $type,
            'data' => json_encode($data),
            'created_at' => now(),
        ]);

        // Update cache
        $this->updateMetricsCache($userId);
    }

    /**
     * Record latency measurement
     */
    public function recordLatency($serverId, int $latency): void
    {
        $key = "latency_history:{$serverId}";
        $history = Cache::get($key, []);

        // Add new measurement
        $history[] = [
            'value' => $latency,
            'timestamp' => now()->toIso8601String(),
        ];

        // Keep only last 100 measurements
        $history = array_slice($history, -100);

        Cache::put($key, $history, now()->addHours(24));
    }

    /**
     * Calculate uptime percentage
     */
    private function calculateUptime(McpServer $server): float
    {
        $totalTime = now()->diffInMinutes($server->created_at);
        if ($totalTime === 0) {
            return 0;
        }

        $downtime = DB::table('mcp_metrics')
            ->where('type', 'server_down')
            ->where('data->server_id', $server->id)
            ->where('created_at', '>=', $server->created_at)
            ->sum(DB::raw("CAST(JSON_EXTRACT(data, '$.duration') AS UNSIGNED)"));

        $uptimeMinutes = $totalTime - ($downtime / 60);

        return round(($uptimeMinutes / $totalTime) * 100, 2);
    }

    /**
     * Get current latency
     */
    private function getCurrentLatency(McpServer $server): ?int
    {
        try {
            $start = microtime(true);
            $response = \Http::timeout(5)->get($server->url.'/health');
            $latency = round((microtime(true) - $start) * 1000);

            $this->recordLatency($server->id, $latency);

            return $latency;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get latency history
     */
    private function getLatencyHistory($serverId): array
    {
        $key = "latency_history:{$serverId}";
        $history = Cache::get($key, []);

        return array_map(fn ($item) => $item['value'], $history);
    }

    /**
     * Get average latency
     */
    private function getAverageLatency(array $history): ?float
    {
        if (empty($history)) {
            return null;
        }

        return round(array_sum($history) / count($history), 2);
    }

    /**
     * Get connection count
     */
    private function getConnectionCount(McpServer $server): int
    {
        return DB::table('mcp_metrics')
            ->where('type', 'connection')
            ->where('data->server_id', $server->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(string $service, $userId): float
    {
        $total = DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'sync_operation')
            ->where('data->service', $service)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($total === 0) {
            return 100;
        }

        $successful = DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'sync_operation')
            ->where('data->service', $service)
            ->where('data->status', 'success')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get API call count
     */
    private function getApiCallCount($userId, Carbon $since): int
    {
        return DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'api_call')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get successful API call count
     */
    private function getSuccessfulApiCallCount($userId, Carbon $since): int
    {
        return DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'api_call')
            ->where('data->status', 'success')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get failed API call count
     */
    private function getFailedApiCallCount($userId, Carbon $since): int
    {
        return DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'api_call')
            ->where('data->status', 'failed')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get average response time
     */
    private function getAverageResponseTime($userId, Carbon $since): ?float
    {
        $avg = DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'api_call')
            ->where('created_at', '>=', $since)
            ->avg(DB::raw("CAST(JSON_EXTRACT(data, '$.response_time') AS UNSIGNED)"));

        return $avg ? round($avg, 2) : null;
    }

    /**
     * Get sync operation count
     */
    private function getSyncOperationCount($userId, Carbon $since): int
    {
        return DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'sync_operation')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get successful sync count
     */
    private function getSuccessfulSyncCount($userId, Carbon $since): int
    {
        return DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'sync_operation')
            ->where('data->status', 'success')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get failed sync count
     */
    private function getFailedSyncCount($userId, Carbon $since): int
    {
        return DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'sync_operation')
            ->where('data->status', 'failed')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Calculate error rate
     */
    private function calculateErrorRate($userId, Carbon $since): float
    {
        $total = $this->getApiCallCount($userId, $since);
        if ($total === 0) {
            return 0;
        }

        $failed = $this->getFailedApiCallCount($userId, $since);

        return round(($failed / $total) * 100, 2);
    }

    /**
     * Calculate throughput
     */
    private function calculateThroughput($userId, Carbon $since): float
    {
        $hours = $since->diffInHours(now());
        if ($hours === 0) {
            return 0;
        }

        $operations = $this->getSyncOperationCount($userId, $since);

        return round($operations / $hours, 2);
    }

    /**
     * Get error count
     */
    private function getErrorCount($userId, Carbon $since): int
    {
        return DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'error')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get usage trend
     */
    private function getUsageTrend($userId): array
    {
        $trend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'api_calls' => $this->getApiCallCount($userId, $date->startOfDay()),
                'sync_operations' => $this->getSyncOperationCount($userId, $date->startOfDay()),
                'errors' => $this->getErrorCount($userId, $date->startOfDay()),
            ];
        }

        return $trend;
    }

    /**
     * Get recent sync failures
     */
    private function getRecentSyncFailures($userId): int
    {
        return DB::table('mcp_metrics')
            ->where('user_id', $userId)
            ->where('type', 'sync_operation')
            ->where('data->status', 'failed')
            ->where('created_at', '>=', now()->subHour())
            ->count();
    }

    /**
     * Update metrics cache
     */
    private function updateMetricsCache($userId): void
    {
        // Invalidate cached metrics to force recalculation
        Cache::forget("user_metrics:{$userId}");
    }
}
