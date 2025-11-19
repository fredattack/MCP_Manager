import { LogsViewer } from '@/components/mcp/LogsViewer';
import { MetricCard, MetricsChart } from '@/components/mcp/MetricsChart';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { Activity, AlertCircle, BarChart3, CheckCircle, Clock, RefreshCw, Server, Zap } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

interface Alert {
    level: 'critical' | 'warning' | 'info';
    title: string;
    message: string;
}

interface ServiceMetrics {
    service: string;
    status: 'active' | 'inactive' | 'error';
    total: number;
    active: number;
}

interface MetricsData extends Record<string, unknown> {
    alerts?: Alert[];
    integrations?: {
        by_service?: ServiceMetrics[];
    };
}

interface MonitoringProps {
    currentMetrics: MetricsData;
    recentLogs: Record<string, unknown>[];
    metricsHistory: Record<string, unknown>[];
}

export default function Monitoring({ currentMetrics, recentLogs, metricsHistory }: MonitoringProps) {
    const [metrics, setMetrics] = useState(currentMetrics);
    const [logs, setLogs] = useState(recentLogs);
    const [chartPeriod, setChartPeriod] = useState('24h');
    const [chartType, setChartType] = useState('all');
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [chartData, setChartData] = useState(metricsHistory);
    const [activeTab, setActiveTab] = useState('metrics');

    // Fetch metrics data
    const fetchMetrics = useCallback(async () => {
        try {
            const response = await axios.get('/mcp/monitoring/metrics', {
                params: { period: chartPeriod, type: chartType },
            });
            setChartData(response.data.data);
        } catch (error) {
            console.error('Error fetching metrics:', error);
        }
    }, [chartPeriod, chartType]);

    // Fetch logs
    const fetchLogs = async () => {
        try {
            const response = await axios.get('/mcp/monitoring/logs');
            setLogs(response.data.data);
        } catch (error) {
            console.error('Error fetching logs:', error);
        }
    };

    // Handle refresh
    const handleRefresh = async () => {
        setIsRefreshing(true);
        await Promise.all([fetchMetrics(), fetchLogs()]);
        setTimeout(() => setIsRefreshing(false), 500);
    };

    // Handle export
    const handleExportLogs = () => {
        window.location.href = '/mcp/monitoring/logs/export';
    };

    // Update data when period or type changes
    useEffect(() => {
        fetchMetrics();
    }, [chartPeriod, chartType, fetchMetrics]);

    // Setup Server-Sent Events for real-time updates
    useEffect(() => {
        const eventSource = new EventSource('/mcp/monitoring/stream');

        eventSource.addEventListener('metrics', (event) => {
            const data = JSON.parse(event.data);
            setMetrics(data);
        });

        eventSource.addEventListener('log', (event) => {
            const newLog = JSON.parse(event.data);
            setLogs((prevLogs) => [newLog, ...prevLogs.slice(0, 99)]);
        });

        eventSource.onerror = () => {
            console.error('SSE connection error');
            eventSource.close();
        };

        return () => {
            eventSource.close();
        };
    }, []);

    const getHealthStatus = () => {
        const score = calculateHealthScore();
        if (score >= 80) return { label: 'Healthy', color: 'text-green-500', bg: 'bg-green-500/10' };
        if (score >= 50) return { label: 'Degraded', color: 'text-yellow-500', bg: 'bg-yellow-500/10' };
        return { label: 'Critical', color: 'text-red-500', bg: 'bg-red-500/10' };
    };

    const calculateHealthScore = () => {
        let score = 100;
        if (!metrics?.server?.connected) score -= 30;
        const errorRate = metrics?.performance?.error_rate || 0;
        if (errorRate > 10) score -= 20;
        if (errorRate > 5) score -= 10;
        return score;
    };

    const healthStatus = getHealthStatus();

    return (
        <AppLayout>
            <Head title="MCP Monitoring" />

            <div className="container mx-auto py-8">
                {/* Header */}
                <div className="mb-8 flex items-center justify-between">
                    <div>
                        <h1 className="mb-2 text-3xl font-bold">MCP Monitoring</h1>
                        <p className="text-muted-foreground">Real-time monitoring and observability dashboard</p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={handleRefresh} disabled={isRefreshing}>
                            <RefreshCw className={cn('mr-2 h-4 w-4', isRefreshing && 'animate-spin')} />
                            Refresh
                        </Button>
                    </div>
                </div>

                {/* System Health */}
                <Alert className={cn('mb-6', healthStatus.bg)}>
                    <Activity className={cn('h-4 w-4', healthStatus.color)} />
                    <AlertTitle>System Status: {healthStatus.label}</AlertTitle>
                    <AlertDescription>
                        {metrics?.alerts?.length > 0 ? (
                            <span>There are {metrics.alerts.length} active alerts requiring attention.</span>
                        ) : (
                            <span>All systems are operating normally.</span>
                        )}
                    </AlertDescription>
                </Alert>

                {/* Metrics Overview */}
                <div className="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <MetricCard
                        title="Active Integrations"
                        value={`${metrics?.integrations?.active || 0}/${metrics?.integrations?.total || 0}`}
                        description="Currently active integrations"
                        trend={{
                            value: 12,
                            label: 'from last week',
                        }}
                        icon={<Zap className="text-muted-foreground h-4 w-4" />}
                    />
                    <MetricCard
                        title="API Calls Today"
                        value={metrics?.usage?.api_calls_today || 0}
                        description="Total API calls made today"
                        trend={{
                            value: 8,
                            label: 'from yesterday',
                        }}
                        icon={<Activity className="text-muted-foreground h-4 w-4" />}
                    />
                    <MetricCard
                        title="Avg Response Time"
                        value={`${metrics?.performance?.avg_response_time || 0}ms`}
                        description="Average response time"
                        trend={{
                            value: -15,
                            label: 'improvement',
                        }}
                        icon={<Clock className="text-muted-foreground h-4 w-4" />}
                    />
                    <MetricCard
                        title="Error Rate"
                        value={`${metrics?.performance?.error_rate || 0}%`}
                        description="Current error rate"
                        trend={{
                            value: -5,
                            label: 'from last hour',
                        }}
                        icon={<AlertCircle className="text-muted-foreground h-4 w-4" />}
                    />
                </div>

                {/* Main Content Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                    <TabsList className="grid w-full grid-cols-3">
                        <TabsTrigger value="metrics">
                            <BarChart3 className="mr-2 h-4 w-4" />
                            Metrics
                        </TabsTrigger>
                        <TabsTrigger value="logs">
                            <Activity className="mr-2 h-4 w-4" />
                            Audit Logs
                        </TabsTrigger>
                        <TabsTrigger value="alerts">
                            <AlertCircle className="mr-2 h-4 w-4" />
                            Alerts
                        </TabsTrigger>
                    </TabsList>

                    {/* Metrics Tab */}
                    <TabsContent value="metrics" className="space-y-4">
                        {/* Chart Controls */}
                        <div className="flex justify-end gap-2">
                            <Select value={chartPeriod} onValueChange={setChartPeriod}>
                                <SelectTrigger className="w-[150px]">
                                    <SelectValue placeholder="Period" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1h">Last Hour</SelectItem>
                                    <SelectItem value="24h">Last 24 Hours</SelectItem>
                                    <SelectItem value="7d">Last 7 Days</SelectItem>
                                    <SelectItem value="30d">Last 30 Days</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select value={chartType} onValueChange={setChartType}>
                                <SelectTrigger className="w-[150px]">
                                    <SelectValue placeholder="Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Metrics</SelectItem>
                                    <SelectItem value="api_call">API Calls</SelectItem>
                                    <SelectItem value="sync_operation">Sync Operations</SelectItem>
                                    <SelectItem value="error">Errors</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Charts Grid */}
                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <MetricsChart
                                title="API Activity"
                                description="API calls and sync operations over time"
                                data={chartData}
                                type="area"
                                dataKey={['api_calls', 'sync_operations']}
                                xAxisKey="time"
                                height={300}
                            />
                            <MetricsChart
                                title="Response Times"
                                description="Average response time trends"
                                data={chartData}
                                type="line"
                                dataKey="response_time"
                                xAxisKey="time"
                                height={300}
                            />
                            <MetricsChart
                                title="Error Distribution"
                                description="Errors by type"
                                data={[
                                    { name: 'Connection', value: 12 },
                                    { name: 'Authentication', value: 8 },
                                    { name: 'Timeout', value: 5 },
                                    { name: 'Rate Limit', value: 3 },
                                ]}
                                type="pie"
                                dataKey="value"
                                height={300}
                            />
                            <MetricsChart
                                title="Integration Status"
                                description="Status of all integrations"
                                data={metrics?.integrations?.by_service || []}
                                type="bar"
                                dataKey="count"
                                xAxisKey="service"
                                height={300}
                            />
                        </div>
                    </TabsContent>

                    {/* Logs Tab */}
                    <TabsContent value="logs">
                        <LogsViewer logs={logs} onRefresh={fetchLogs} onExport={handleExportLogs} />
                    </TabsContent>

                    {/* Alerts Tab */}
                    <TabsContent value="alerts" className="space-y-4">
                        {metrics?.alerts && metrics.alerts.length > 0 ? (
                            metrics.alerts.map((alert: Alert, index: number) => (
                                <Alert key={index} variant={alert.level === 'critical' ? 'destructive' : 'default'}>
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertTitle>{alert.title}</AlertTitle>
                                    <AlertDescription>
                                        <div className="flex items-center justify-between">
                                            <span>{alert.message}</span>
                                            <Badge variant={alert.level === 'critical' ? 'destructive' : 'secondary'}>{alert.level}</Badge>
                                        </div>
                                    </AlertDescription>
                                </Alert>
                            ))
                        ) : (
                            <Card>
                                <CardContent className="flex flex-col items-center justify-center py-12">
                                    <CheckCircle className="mb-4 h-12 w-12 text-green-500" />
                                    <p className="text-lg font-medium">No Active Alerts</p>
                                    <p className="text-muted-foreground text-sm">All systems are operating normally</p>
                                </CardContent>
                            </Card>
                        )}
                    </TabsContent>
                </Tabs>

                {/* Integration Details */}
                <Card className="mt-8">
                    <CardHeader>
                        <CardTitle>Integration Details</CardTitle>
                        <CardDescription>Current status of all configured integrations</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {metrics?.integrations?.by_service?.map((service: ServiceMetrics) => (
                                <div key={service.service} className="flex items-center justify-between rounded-lg border p-4">
                                    <div className="flex items-center gap-3">
                                        <div
                                            className={cn(
                                                'flex h-10 w-10 items-center justify-center rounded-lg',
                                                service.status === 'active' ? 'bg-green-500/10' : 'bg-red-500/10',
                                            )}
                                        >
                                            <Server className={cn('h-5 w-5', service.status === 'active' ? 'text-green-500' : 'text-red-500')} />
                                        </div>
                                        <div>
                                            <p className="font-medium">{service.service}</p>
                                            <p className="text-muted-foreground text-sm">
                                                {service.count} instance{service.count !== 1 ? 's' : ''}
                                            </p>
                                        </div>
                                    </div>
                                    <Badge variant={service.status === 'active' ? 'default' : 'destructive'}>{service.status}</Badge>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
