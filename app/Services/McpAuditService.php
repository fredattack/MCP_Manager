<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class McpAuditService
{
    /**
     * Log an audit event
     */
    public function log(
        string $action,
        User $user,
        string $entity,
        $entityId = null,
        array $data = [],
        string $status = 'success'
    ): void {
        DB::table('mcp_audit_logs')->insert([
            'user_id' => $user->id,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'data' => json_encode($data),
            'status' => $status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        // Also log to Laravel log for immediate visibility
        Log::info("MCP Audit: {$action}", [
            'user_id' => $user->id,
            'entity' => $entity,
            'entity_id' => $entityId,
            'status' => $status,
        ]);
    }

    /**
     * Log configuration change
     */
    public function logConfigurationChange(
        User $user,
        string $entity,
        $entityId,
        array $oldValues,
        array $newValues
    ): void {
        $changes = $this->calculateChanges($oldValues, $newValues);
        
        $this->log(
            'configuration_changed',
            $user,
            $entity,
            $entityId,
            [
                'changes' => $changes,
                'old_values' => $this->sanitizeData($oldValues),
                'new_values' => $this->sanitizeData($newValues),
            ]
        );
    }

    /**
     * Log authentication event
     */
    public function logAuthentication(
        User $user,
        string $service,
        bool $success,
        string $reason = null
    ): void {
        $this->log(
            'authentication',
            $user,
            'integration',
            null,
            [
                'service' => $service,
                'success' => $success,
                'reason' => $reason,
            ],
            $success ? 'success' : 'failed'
        );
    }

    /**
     * Log connection event
     */
    public function logConnection(
        User $user,
        string $serverUrl,
        bool $success,
        array $context = []
    ): void {
        $this->log(
            'server_connection',
            $user,
            'mcp_server',
            null,
            array_merge([
                'server_url' => $serverUrl,
                'success' => $success,
            ], $context),
            $success ? 'success' : 'failed'
        );
    }

    /**
     * Log integration test
     */
    public function logIntegrationTest(
        User $user,
        string $service,
        bool $success,
        array $results = []
    ): void {
        $this->log(
            'integration_test',
            $user,
            'integration',
            null,
            [
                'service' => $service,
                'success' => $success,
                'results' => $results,
            ],
            $success ? 'success' : 'failed'
        );
    }

    /**
     * Log credential update
     */
    public function logCredentialUpdate(
        User $user,
        string $service,
        bool $success
    ): void {
        $this->log(
            'credential_update',
            $user,
            'integration',
            null,
            [
                'service' => $service,
                'success' => $success,
            ],
            $success ? 'success' : 'failed'
        );
    }

    /**
     * Log sync operation
     */
    public function logSyncOperation(
        User $user,
        string $service,
        string $operation,
        bool $success,
        array $details = []
    ): void {
        $this->log(
            'sync_operation',
            $user,
            'integration',
            null,
            [
                'service' => $service,
                'operation' => $operation,
                'success' => $success,
                'details' => $details,
            ],
            $success ? 'success' : 'failed'
        );
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(
        User $user,
        string $event,
        string $severity,
        array $context = []
    ): void {
        $this->log(
            'security_event',
            $user,
            'security',
            null,
            [
                'event' => $event,
                'severity' => $severity,
                'context' => $context,
            ],
            'warning'
        );

        // For critical security events, also send alert
        if ($severity === 'critical') {
            $this->sendSecurityAlert($user, $event, $context);
        }
    }

    /**
     * Get audit logs for user
     */
    public function getUserLogs(User $user, array $filters = []): array
    {
        $query = DB::table('mcp_audit_logs')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['entity'])) {
            $query->where('entity', $filters['entity']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query->paginate($filters['per_page'] ?? 50)->toArray();
    }

    /**
     * Get activity summary
     */
    public function getActivitySummary(User $user, Carbon $since = null): array
    {
        $since = $since ?? now()->subDays(30);

        return [
            'total_actions' => $this->getTotalActions($user, $since),
            'by_action' => $this->getActionBreakdown($user, $since),
            'by_status' => $this->getStatusBreakdown($user, $since),
            'by_entity' => $this->getEntityBreakdown($user, $since),
            'timeline' => $this->getActivityTimeline($user, $since),
            'recent_failures' => $this->getRecentFailures($user),
        ];
    }

    /**
     * Search audit logs
     */
    public function searchLogs(string $query, User $user = null): array
    {
        $dbQuery = DB::table('mcp_audit_logs');

        if ($user) {
            $dbQuery->where('user_id', $user->id);
        }

        $dbQuery->where(function ($q) use ($query) {
            $q->where('action', 'like', "%{$query}%")
              ->orWhere('entity', 'like', "%{$query}%")
              ->orWhere('data', 'like', "%{$query}%");
        });

        return $dbQuery
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->toArray();
    }

    /**
     * Export audit logs
     */
    public function exportLogs(User $user, Carbon $from, Carbon $to, string $format = 'csv'): string
    {
        $logs = DB::table('mcp_audit_logs')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($logs);
            case 'json':
                return $logs->toJson();
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }
    }

    /**
     * Clean old audit logs
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        return DB::table('mcp_audit_logs')
            ->where('created_at', '<', now()->subDays($daysToKeep))
            ->delete();
    }

    /**
     * Calculate changes between old and new values
     */
    private function calculateChanges(array $oldValues, array $newValues): array
    {
        $changes = [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $this->sanitizeValue($oldValue),
                    'new' => $this->sanitizeValue($newValue),
                ];
            }
        }

        return $changes;
    }

    /**
     * Sanitize sensitive data
     */
    private function sanitizeData(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'private_key'];
        
        foreach ($data as $key => $value) {
            foreach ($sensitiveKeys as $sensitiveKey) {
                if (stripos($key, $sensitiveKey) !== false) {
                    $data[$key] = '***REDACTED***';
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Sanitize single value
     */
    private function sanitizeValue($value)
    {
        if (is_string($value) && strlen($value) > 100) {
            return substr($value, 0, 100) . '...';
        }
        return $value;
    }

    /**
     * Send security alert
     */
    private function sendSecurityAlert(User $user, string $event, array $context): void
    {
        // In production, this would send email/SMS/Slack notification
        Log::critical("Security Alert for user {$user->id}: {$event}", $context);
    }

    /**
     * Get total actions count
     */
    private function getTotalActions(User $user, Carbon $since): int
    {
        return DB::table('mcp_audit_logs')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get action breakdown
     */
    private function getActionBreakdown(User $user, Carbon $since): array
    {
        return DB::table('mcp_audit_logs')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();
    }

    /**
     * Get status breakdown
     */
    private function getStatusBreakdown(User $user, Carbon $since): array
    {
        return DB::table('mcp_audit_logs')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get entity breakdown
     */
    private function getEntityBreakdown(User $user, Carbon $since): array
    {
        return DB::table('mcp_audit_logs')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->select('entity', DB::raw('count(*) as count'))
            ->groupBy('entity')
            ->pluck('count', 'entity')
            ->toArray();
    }

    /**
     * Get activity timeline
     */
    private function getActivityTimeline(User $user, Carbon $since): array
    {
        $timeline = [];
        $days = $since->diffInDays(now());

        for ($i = 0; $i <= min($days, 30); $i++) {
            $date = now()->subDays($i);
            $timeline[] = [
                'date' => $date->format('Y-m-d'),
                'count' => DB::table('mcp_audit_logs')
                    ->where('user_id', $user->id)
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        return array_reverse($timeline);
    }

    /**
     * Get recent failures
     */
    private function getRecentFailures(User $user, int $limit = 10): array
    {
        return DB::table('mcp_audit_logs')
            ->where('user_id', $user->id)
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($logs): string
    {
        $csv = "Date,Action,Entity,Status,IP Address\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $log->created_at,
                $log->action,
                $log->entity,
                $log->status,
                $log->ip_address
            );
        }

        return $csv;
    }
}