import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { Activity, AlertCircle, CheckCircle, Clock, Download, Filter, Info, RefreshCw, Search } from 'lucide-react';
import { useEffect, useState } from 'react';

interface LogEntry {
    id: number;
    action: string;
    entity: string;
    entity_id?: string;
    status: 'success' | 'failed' | 'warning';
    data?: Record<string, unknown>;
    ip_address?: string;
    user_agent?: string;
    created_at: string;
}

interface LogsViewerProps {
    logs: LogEntry[];
    onRefresh?: () => void;
    onFilter?: (filters: Record<string, unknown>) => void;
    onExport?: () => void;
    className?: string;
}

export function LogsViewer({ logs: initialLogs, onRefresh, onExport, className }: LogsViewerProps) {
    const [logs, setLogs] = useState(initialLogs);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState<string>('all');
    const [filterAction, setFilterAction] = useState<string>('all');
    const [isRefreshing, setIsRefreshing] = useState(false);

    useEffect(() => {
        setLogs(initialLogs);
    }, [initialLogs]);

    const filteredLogs = logs.filter((log) => {
        const matchesSearch =
            searchTerm === '' ||
            log.action.toLowerCase().includes(searchTerm.toLowerCase()) ||
            log.entity.toLowerCase().includes(searchTerm.toLowerCase()) ||
            (log.data && JSON.stringify(log.data).toLowerCase().includes(searchTerm.toLowerCase()));

        const matchesStatus = filterStatus === 'all' || log.status === filterStatus;
        const matchesAction = filterAction === 'all' || log.action === filterAction;

        return matchesSearch && matchesStatus && matchesAction;
    });

    const handleRefresh = async () => {
        setIsRefreshing(true);
        if (onRefresh) {
            await onRefresh();
        }
        setTimeout(() => setIsRefreshing(false), 500);
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'success':
                return <CheckCircle className="h-4 w-4 text-green-500" />;
            case 'failed':
                return <AlertCircle className="h-4 w-4 text-red-500" />;
            case 'warning':
                return <Info className="h-4 w-4 text-yellow-500" />;
            default:
                return null;
        }
    };

    const getStatusBadge = (status: string) => {
        const variants: Record<string, 'default' | 'destructive' | 'secondary'> = {
            success: 'default',
            failed: 'destructive',
            warning: 'secondary',
        };

        return (
            <Badge variant={variants[status] || 'secondary'} className="text-xs">
                {status}
            </Badge>
        );
    };

    const formatTimestamp = (timestamp: string) => {
        const date = new Date(timestamp);
        return date.toLocaleString();
    };

    const getActionColor = (action: string) => {
        const colors: Record<string, string> = {
            configuration_changed: 'text-blue-600',
            authentication: 'text-green-600',
            server_connection: 'text-purple-600',
            integration_test: 'text-orange-600',
            credential_update: 'text-red-600',
            sync_operation: 'text-indigo-600',
            security_event: 'text-red-700',
        };
        return colors[action] || 'text-gray-600';
    };

    const uniqueActions = Array.from(new Set(logs.map((log) => log.action)));

    return (
        <Card className={className}>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <CardTitle className="flex items-center gap-2">
                        <Activity className="h-5 w-5" />
                        Audit Logs
                    </CardTitle>
                    <div className="flex gap-2">
                        <Button size="sm" variant="outline" onClick={handleRefresh} disabled={isRefreshing}>
                            <RefreshCw className={cn('mr-2 h-4 w-4', isRefreshing && 'animate-spin')} />
                            Refresh
                        </Button>
                        {onExport && (
                            <Button size="sm" variant="outline" onClick={onExport}>
                                <Download className="mr-2 h-4 w-4" />
                                Export
                            </Button>
                        )}
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                {/* Filters */}
                <div className="mb-4 flex gap-2">
                    <div className="relative flex-1">
                        <Search className="text-muted-foreground absolute top-2.5 left-2 h-4 w-4" />
                        <Input placeholder="Search logs..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="pl-8" />
                    </div>
                    <Select value={filterStatus} onValueChange={setFilterStatus}>
                        <SelectTrigger className="w-[150px]">
                            <Filter className="mr-2 h-4 w-4" />
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="success">Success</SelectItem>
                            <SelectItem value="failed">Failed</SelectItem>
                            <SelectItem value="warning">Warning</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select value={filterAction} onValueChange={setFilterAction}>
                        <SelectTrigger className="w-[200px]">
                            <SelectValue placeholder="Action" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Actions</SelectItem>
                            {uniqueActions.map((action) => (
                                <SelectItem key={action} value={action}>
                                    {action.replace(/_/g, ' ')}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                {/* Logs Table */}
                <div className="rounded-md border">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="bg-muted/50 border-b">
                                    <th className="p-2 text-left text-xs font-medium">Status</th>
                                    <th className="p-2 text-left text-xs font-medium">Time</th>
                                    <th className="p-2 text-left text-xs font-medium">Action</th>
                                    <th className="p-2 text-left text-xs font-medium">Entity</th>
                                    <th className="p-2 text-left text-xs font-medium">Details</th>
                                    <th className="p-2 text-left text-xs font-medium">IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredLogs.length > 0 ? (
                                    filteredLogs.map((log) => (
                                        <tr key={log.id} className="hover:bg-muted/20 border-b">
                                            <td className="p-2">
                                                <div className="flex items-center gap-2">
                                                    {getStatusIcon(log.status)}
                                                    {getStatusBadge(log.status)}
                                                </div>
                                            </td>
                                            <td className="p-2">
                                                <div className="text-muted-foreground flex items-center gap-1 text-xs">
                                                    <Clock className="h-3 w-3" />
                                                    {formatTimestamp(log.created_at)}
                                                </div>
                                            </td>
                                            <td className="p-2">
                                                <span className={cn('text-sm font-medium', getActionColor(log.action))}>
                                                    {log.action.replace(/_/g, ' ')}
                                                </span>
                                            </td>
                                            <td className="p-2">
                                                <div className="text-sm">
                                                    {log.entity}
                                                    {log.entity_id && <span className="text-muted-foreground ml-1 text-xs">#{log.entity_id}</span>}
                                                </div>
                                            </td>
                                            <td className="p-2">
                                                {log.data && (
                                                    <details className="cursor-pointer">
                                                        <summary className="text-muted-foreground text-xs">View details</summary>
                                                        <pre className="bg-muted mt-1 max-w-xs overflow-x-auto rounded p-2 text-xs">
                                                            {JSON.stringify(log.data, null, 2)}
                                                        </pre>
                                                    </details>
                                                )}
                                            </td>
                                            <td className="p-2">
                                                <span className="text-muted-foreground text-xs">{log.ip_address || '-'}</span>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={6} className="text-muted-foreground p-8 text-center">
                                            No logs found
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Summary */}
                {filteredLogs.length > 0 && (
                    <div className="text-muted-foreground mt-4 flex items-center justify-between text-sm">
                        <span>
                            Showing {filteredLogs.length} of {logs.length} entries
                        </span>
                        <div className="flex gap-4">
                            <span className="flex items-center gap-1">
                                <CheckCircle className="h-4 w-4 text-green-500" />
                                {filteredLogs.filter((l) => l.status === 'success').length} Success
                            </span>
                            <span className="flex items-center gap-1">
                                <AlertCircle className="h-4 w-4 text-red-500" />
                                {filteredLogs.filter((l) => l.status === 'failed').length} Failed
                            </span>
                            <span className="flex items-center gap-1">
                                <Info className="h-4 w-4 text-yellow-500" />
                                {filteredLogs.filter((l) => l.status === 'warning').length} Warning
                            </span>
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
