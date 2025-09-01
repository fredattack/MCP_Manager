import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { 
    Activity,
    AlertCircle,
    CheckCircle,
    Clock,
    Download,
    RefreshCw,
    TrendingUp,
    TrendingDown,
    Server,
    Zap,
    Shield,
    BarChart3
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { MetricsChart, MetricCard } from '@/components/mcp/MetricsChart';
import { LogsViewer } from '@/components/mcp/LogsViewer';
import { cn } from '@/lib/utils';
import axios from 'axios';

interface MonitoringProps {
    currentMetrics: any;
    recentLogs: any[];
    metricsHistory: any[];
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
    const fetchMetrics = async () => {
        try {
            const response = await axios.get('/mcp/monitoring/metrics', {
                params: { period: chartPeriod, type: chartType }
            });
            setChartData(response.data.data);
        } catch (error) {
            console.error('Error fetching metrics:', error);
        }
    };

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
    }, [chartPeriod, chartType]);

    // Setup Server-Sent Events for real-time updates
    useEffect(() => {
        const eventSource = new EventSource('/mcp/monitoring/stream');

        eventSource.addEventListener('metrics', (event) => {
            const data = JSON.parse(event.data);
            setMetrics(data);
        });

        eventSource.addEventListener('log', (event) => {
            const newLog = JSON.parse(event.data);
            setLogs(prevLogs => [newLog, ...prevLogs.slice(0, 99)]);
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
                <div className="flex items-center justify-between mb-8">
                    <div>
                        <h1 className="text-3xl font-bold mb-2">MCP Monitoring</h1>
                        <p className="text-muted-foreground">
                            Real-time monitoring and observability dashboard
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            onClick={handleRefresh}
                            disabled={isRefreshing}
                        >
                            <RefreshCw className={cn(
                                "w-4 h-4 mr-2",
                                isRefreshing && "animate-spin"
                            )} />
                            Refresh
                        </Button>
                    </div>
                </div>

                {/* System Health */}
                <Alert className={cn("mb-6", healthStatus.bg)}>
                    <Activity className={cn("h-4 w-4", healthStatus.color)} />
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
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <MetricCard
                        title="Active Integrations"
                        value={`${metrics?.integrations?.active || 0}/${metrics?.integrations?.total || 0}`}
                        description="Currently active integrations"
                        trend={{
                            value: 12,
                            label: "from last week"
                        }}
                        icon={<Zap className="w-4 h-4 text-muted-foreground" />}
                    />
                    <MetricCard
                        title="API Calls Today"
                        value={metrics?.usage?.api_calls_today || 0}
                        description="Total API calls made today"
                        trend={{
                            value: 8,
                            label: "from yesterday"
                        }}
                        icon={<Activity className="w-4 h-4 text-muted-foreground" />}
                    />
                    <MetricCard
                        title="Avg Response Time"
                        value={`${metrics?.performance?.avg_response_time || 0}ms`}
                        description="Average response time"
                        trend={{
                            value: -15,
                            label: "improvement"
                        }}
                        icon={<Clock className="w-4 h-4 text-muted-foreground" />}
                    />
                    <MetricCard
                        title="Error Rate"
                        value={`${metrics?.performance?.error_rate || 0}%`}
                        description="Current error rate"
                        trend={{
                            value: -5,
                            label: "from last hour"
                        }}
                        icon={<AlertCircle className="w-4 h-4 text-muted-foreground" />}
                    />
                </div>

                {/* Main Content Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                    <TabsList className="grid w-full grid-cols-3">
                        <TabsTrigger value="metrics">
                            <BarChart3 className="w-4 h-4 mr-2" />
                            Metrics
                        </TabsTrigger>
                        <TabsTrigger value="logs">
                            <Activity className="w-4 h-4 mr-2" />
                            Audit Logs
                        </TabsTrigger>
                        <TabsTrigger value="alerts">
                            <AlertCircle className="w-4 h-4 mr-2" />
                            Alerts
                        </TabsTrigger>
                    </TabsList>

                    {/* Metrics Tab */}
                    <TabsContent value="metrics" className="space-y-4">
                        {/* Chart Controls */}
                        <div className="flex gap-2 justify-end">
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
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
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
                        <LogsViewer
                            logs={logs}
                            onRefresh={fetchLogs}
                            onExport={handleExportLogs}
                        />
                    </TabsContent>

                    {/* Alerts Tab */}
                    <TabsContent value="alerts" className="space-y-4">
                        {metrics?.alerts?.length > 0 ? (
                            metrics.alerts.map((alert: any, index: number) => (
                                <Alert key={index} variant={alert.level === 'critical' ? 'destructive' : 'default'}>
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertTitle>{alert.title}</AlertTitle>
                                    <AlertDescription>
                                        <div className="flex items-center justify-between">
                                            <span>{alert.message}</span>
                                            <Badge variant={alert.level === 'critical' ? 'destructive' : 'secondary'}>
                                                {alert.level}
                                            </Badge>
                                        </div>
                                    </AlertDescription>
                                </Alert>
                            ))
                        ) : (
                            <Card>
                                <CardContent className="flex flex-col items-center justify-center py-12">
                                    <CheckCircle className="w-12 h-12 text-green-500 mb-4" />
                                    <p className="text-lg font-medium">No Active Alerts</p>
                                    <p className="text-sm text-muted-foreground">
                                        All systems are operating normally
                                    </p>
                                </CardContent>
                            </Card>
                        )}
                    </TabsContent>
                </Tabs>

                {/* Integration Details */}
                <Card className="mt-8">
                    <CardHeader>
                        <CardTitle>Integration Details</CardTitle>
                        <CardDescription>
                            Current status of all configured integrations
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {metrics?.integrations?.by_service?.map((service: any) => (
                                <div key={service.service} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center gap-3">
                                        <div className={cn(
                                            "w-10 h-10 rounded-lg flex items-center justify-center",
                                            service.status === 'active' ? 'bg-green-500/10' : 'bg-red-500/10'
                                        )}>
                                            <Server className={cn(
                                                "w-5 h-5",
                                                service.status === 'active' ? 'text-green-500' : 'text-red-500'
                                            )} />
                                        </div>
                                        <div>
                                            <p className="font-medium">{service.service}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {service.count} instance{service.count !== 1 ? 's' : ''}
                                            </p>
                                        </div>
                                    </div>
                                    <Badge variant={service.status === 'active' ? 'default' : 'destructive'}>
                                        {service.status}
                                    </Badge>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}