<?php

namespace App\Http\Controllers;

use App\Models\McpAuditLog;
use App\Models\McpMetric;
use App\Services\McpMetricsService;
use App\Services\McpAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class McpMonitoringController extends Controller
{
    public function __construct(
        private McpMetricsService $metricsService,
        private McpAuditService $auditService
    ) {}

    /**
     * Display the monitoring dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Collect real-time metrics
        $metrics = $this->metricsService->collectMetrics($user);
        
        // Get recent audit logs
        $recentLogs = McpAuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        // Get metrics history for charts
        $metricsHistory = $this->getMetricsHistory($user);
        
        return Inertia::render('Mcp/Monitoring', [
            'currentMetrics' => $metrics,
            'recentLogs' => $recentLogs,
            'metricsHistory' => $metricsHistory,
        ]);
    }

    /**
     * Get metrics data for charts
     */
    public function metrics(Request $request)
    {
        $user = auth()->user();
        
        $period = $request->get('period', '24h');
        $type = $request->get('type', 'all');
        
        $startDate = match($period) {
            '1h' => Carbon::now()->subHour(),
            '24h' => Carbon::now()->subDay(),
            '7d' => Carbon::now()->subWeek(),
            '30d' => Carbon::now()->subMonth(),
            default => Carbon::now()->subDay(),
        };
        
        $query = McpMetric::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate);
        
        if ($type !== 'all') {
            $query->where('type', $type);
        }
        
        $metrics = $query->orderBy('created_at', 'asc')->get();
        
        // Format data for charts
        $chartData = $this->formatMetricsForCharts($metrics, $type);
        
        return response()->json([
            'data' => $chartData,
            'period' => $period,
            'type' => $type,
        ]);
    }

    /**
     * Get audit logs with filtering
     */
    public function logs(Request $request)
    {
        $user = auth()->user();
        
        $query = McpAuditLog::where('user_id', $user->id);
        
        // Apply filters
        if ($request->has('action')) {
            $query->where('action', $request->get('action'));
        }
        
        if ($request->has('entity')) {
            $query->where('entity', $request->get('entity'));
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('entity', 'like', "%{$search}%")
                  ->orWhere('data', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->get('start_date'));
        }
        
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->get('end_date'));
        }
        
        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));
        
        return response()->json($logs);
    }

    /**
     * Export logs to CSV
     */
    public function exportLogs(Request $request)
    {
        $user = auth()->user();
        
        $query = McpAuditLog::where('user_id', $user->id);
        
        // Apply same filters as logs endpoint
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->get('start_date'));
        }
        
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->get('end_date'));
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        $csv = "Time,Action,Entity,Status,IP Address,Details\n";
        
        foreach ($logs as $log) {
            $data = $log->data ? json_encode($log->data) : '';
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                $log->created_at->format('Y-m-d H:i:s'),
                $log->action,
                $log->entity,
                $log->status,
                $log->ip_address ?? '-',
                str_replace(',', ';', $data)
            );
        }
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="mcp-audit-logs-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Get system health status
     */
    public function health()
    {
        $user = auth()->user();
        
        // Get current metrics
        $metrics = $this->metricsService->collectMetrics($user);
        
        // Calculate health score
        $healthScore = $this->calculateHealthScore($metrics);
        
        // Get recent errors
        $recentErrors = McpAuditLog::where('user_id', $user->id)
            ->where('status', 'failed')
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();
        
        // Get active alerts
        $activeAlerts = $metrics['alerts'] ?? [];
        
        return response()->json([
            'status' => $healthScore >= 80 ? 'healthy' : ($healthScore >= 50 ? 'degraded' : 'critical'),
            'score' => $healthScore,
            'recent_errors' => $recentErrors,
            'active_alerts' => $activeAlerts,
            'checks' => [
                'server_connectivity' => $metrics['server']['connected'] ?? false,
                'integration_status' => $this->checkIntegrationsHealth($metrics),
                'response_time' => $metrics['performance']['avg_response_time'] ?? 0,
                'error_rate' => $this->calculateErrorRate($user),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get real-time updates via Server-Sent Events
     */
    public function stream()
    {
        $user = auth()->user();
        
        return response()->stream(function() use ($user) {
            while (true) {
                // Get latest metrics
                $metrics = $this->metricsService->collectMetrics($user);
                
                // Send metrics update
                echo "event: metrics\n";
                echo "data: " . json_encode($metrics) . "\n\n";
                
                // Get recent logs
                $recentLog = McpAuditLog::where('user_id', $user->id)
                    ->where('created_at', '>=', Carbon::now()->subSeconds(5))
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($recentLog) {
                    echo "event: log\n";
                    echo "data: " . json_encode($recentLog) . "\n\n";
                }
                
                ob_flush();
                flush();
                
                // Wait 5 seconds before next update
                sleep(5);
                
                // Check if connection is still alive
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get metrics history for charts
     */
    private function getMetricsHistory($user)
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDay();
        
        // Group metrics by hour
        $metrics = McpMetric::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->select([
                DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour'),
                'type',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(JSON_EXTRACT(data, "$.response_time")) as avg_response_time'),
                DB::raw('SUM(CASE WHEN JSON_EXTRACT(data, "$.status") = "error" THEN 1 ELSE 0 END) as error_count'),
            ])
            ->groupBy('hour', 'type')
            ->orderBy('hour')
            ->get();
        
        // Format for charts
        $hourlyData = [];
        foreach ($metrics as $metric) {
            $hour = $metric->hour;
            if (!isset($hourlyData[$hour])) {
                $hourlyData[$hour] = [
                    'time' => $hour,
                    'api_calls' => 0,
                    'sync_operations' => 0,
                    'errors' => 0,
                    'response_time' => 0,
                ];
            }
            
            if ($metric->type === 'api_call') {
                $hourlyData[$hour]['api_calls'] = $metric->count;
                $hourlyData[$hour]['response_time'] = round($metric->avg_response_time ?? 0, 2);
            } elseif ($metric->type === 'sync_operation') {
                $hourlyData[$hour]['sync_operations'] = $metric->count;
            }
            
            $hourlyData[$hour]['errors'] += $metric->error_count ?? 0;
        }
        
        return array_values($hourlyData);
    }

    /**
     * Format metrics for chart display
     */
    private function formatMetricsForCharts($metrics, $type)
    {
        $formatted = [];
        
        foreach ($metrics as $metric) {
            $data = $metric->data ?? [];
            
            $formatted[] = [
                'time' => $metric->created_at->format('H:i'),
                'type' => $metric->type,
                'value' => $data['value'] ?? 1,
                'response_time' => $data['response_time'] ?? null,
                'status' => $data['status'] ?? 'success',
            ];
        }
        
        return $formatted;
    }

    /**
     * Calculate system health score
     */
    private function calculateHealthScore($metrics): int
    {
        $score = 100;
        
        // Check server connectivity
        if (!($metrics['server']['connected'] ?? false)) {
            $score -= 30;
        }
        
        // Check integration failures
        $failedIntegrations = 0;
        foreach ($metrics['integrations']['by_service'] ?? [] as $service) {
            if ($service['status'] === 'failed') {
                $failedIntegrations++;
            }
        }
        $score -= ($failedIntegrations * 10);
        
        // Check response time
        $avgResponseTime = $metrics['performance']['avg_response_time'] ?? 0;
        if ($avgResponseTime > 1000) {
            $score -= 20;
        } elseif ($avgResponseTime > 500) {
            $score -= 10;
        }
        
        // Check error rate
        $errorRate = $metrics['performance']['error_rate'] ?? 0;
        if ($errorRate > 0.1) {
            $score -= 20;
        } elseif ($errorRate > 0.05) {
            $score -= 10;
        }
        
        return max(0, $score);
    }

    /**
     * Check integrations health
     */
    private function checkIntegrationsHealth($metrics): string
    {
        $total = $metrics['integrations']['total'] ?? 0;
        $active = $metrics['integrations']['active'] ?? 0;
        
        if ($total === 0) {
            return 'no_integrations';
        }
        
        $healthPercentage = ($active / $total) * 100;
        
        if ($healthPercentage >= 90) {
            return 'healthy';
        } elseif ($healthPercentage >= 70) {
            return 'degraded';
        } else {
            return 'critical';
        }
    }

    /**
     * Calculate error rate for the last hour
     */
    private function calculateErrorRate($user): float
    {
        $startTime = Carbon::now()->subHour();
        
        $total = McpAuditLog::where('user_id', $user->id)
            ->where('created_at', '>=', $startTime)
            ->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $errors = McpAuditLog::where('user_id', $user->id)
            ->where('created_at', '>=', $startTime)
            ->where('status', 'failed')
            ->count();
        
        return round(($errors / $total) * 100, 2);
    }
}