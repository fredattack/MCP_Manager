import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useMcpWebSocket } from '@/hooks/useMcpWebSocket';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { McpIntegration, ServerStatus } from '@/types/mcp.types';
import { Head, Link } from '@inertiajs/react';
import { Activity, AlertCircle, CheckCircle, Clock, RefreshCw, Settings, Wifi, WifiOff } from 'lucide-react';
import { useState } from 'react';

interface Props {
    integrations: McpIntegration[];
    serverStatus: ServerStatus;
}

export default function McpDashboard({ integrations: initialIntegrations, serverStatus }: Props) {
    const [integrations, setIntegrations] = useState(initialIntegrations);
    const [isTestingIntegration, setIsTestingIntegration] = useState<string | null>(null);

    // Use WebSocket for real-time updates
    const { isConnected, lastMessage } = useMcpWebSocket({
        onIntegrationUpdate: (integrationId, data) => {
            setIntegrations((prev) => prev.map((integration) => (integration.id === integrationId ? { ...integration, ...data } : integration)));
        },
    });

    const getStatusColor = (status: McpIntegration['status']) => {
        switch (status) {
            case 'active':
                return 'bg-green-500';
            case 'inactive':
                return 'bg-gray-500';
            case 'error':
                return 'bg-red-500';
            case 'connecting':
                return 'bg-yellow-500 animate-pulse';
            default:
                return 'bg-gray-500';
        }
    };

    const getStatusIcon = (status: McpIntegration['status']) => {
        switch (status) {
            case 'active':
                return <CheckCircle className="h-4 w-4" />;
            case 'error':
                return <AlertCircle className="h-4 w-4" />;
            case 'connecting':
                return <RefreshCw className="h-4 w-4 animate-spin" />;
            default:
                return <Clock className="h-4 w-4" />;
        }
    };

    const testIntegration = async (integrationId: string, serviceName: string) => {
        setIsTestingIntegration(integrationId);
        try {
            const response = await fetch(`/api/mcp/integrations/${serviceName}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const result = await response.json();

            if (result.success) {
                setIntegrations((prev) =>
                    prev.map((i) => (i.id === integrationId ? { ...i, status: 'active', credentialsValid: true, errorMessage: undefined } : i)),
                );
            } else {
                setIntegrations((prev) =>
                    prev.map((i) => (i.id === integrationId ? { ...i, status: 'error', credentialsValid: false, errorMessage: result.error } : i)),
                );
            }
        } catch (error) {
            console.error('Test failed:', error);
        } finally {
            setIsTestingIntegration(null);
        }
    };

    const toggleIntegration = async (serviceName: string) => {
        try {
            const response = await fetch(`/api/mcp/integrations/${serviceName}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const result = await response.json();

            if (result.success) {
                setIntegrations((prev) =>
                    prev.map((i) =>
                        i.name === serviceName ? { ...i, enabled: result.enabled, status: result.enabled ? 'connecting' : 'inactive' } : i,
                    ),
                );
            }
        } catch (error) {
            console.error('Toggle failed:', error);
        }
    };

    return (
        <AppLayout>
            <Head title="MCP Dashboard" />

            <div className="container mx-auto py-8">
                <div className="mb-8">
                    <h1 className="mb-2 text-3xl font-bold">MCP Dashboard</h1>
                    <p className="text-muted-foreground">Monitor and manage your MCP server integrations</p>
                </div>

                <div className="space-y-6">
                    {/* Server Status Card */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between">
                                <span className="flex items-center gap-2">
                                    {serverStatus.connected ? (
                                        <Wifi className="h-5 w-5 text-green-500" />
                                    ) : (
                                        <WifiOff className="h-5 w-5 text-red-500" />
                                    )}
                                    MCP Server Status
                                </span>
                                <Badge variant={serverStatus.connected ? 'default' : 'destructive'}>
                                    {serverStatus.connected ? 'Connected' : 'Disconnected'}
                                </Badge>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <p className="text-muted-foreground text-sm">Connection Status</p>
                                    <p className="flex items-center gap-2 font-semibold">
                                        <Activity className={cn('h-4 w-4', serverStatus.connected ? 'text-green-500' : 'text-red-500')} />
                                        {serverStatus.connected ? 'Active' : 'Inactive'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground text-sm">Latency</p>
                                    <p className="font-semibold">{serverStatus.latency ? `${serverStatus.latency}ms` : 'N/A'}</p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground text-sm">Last Sync</p>
                                    <p className="font-semibold">
                                        {serverStatus.lastSync ? new Date(serverStatus.lastSync).toLocaleString() : 'Never'}
                                    </p>
                                </div>
                            </div>

                            {serverStatus.error && (
                                <Alert variant="destructive" className="mt-4">
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>{serverStatus.error}</AlertDescription>
                                </Alert>
                            )}

                            <div className="mt-4 flex gap-2">
                                <Link href="/mcp/server/config">
                                    <Button variant="outline" size="sm">
                                        <Settings className="mr-2 h-4 w-4" />
                                        Server Settings
                                    </Button>
                                </Link>
                                {isConnected && (
                                    <Badge variant="outline" className="ml-auto">
                                        <div className="mr-2 h-2 w-2 animate-pulse rounded-full bg-green-500" />
                                        Real-time updates active
                                    </Badge>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Integrations Grid */}
                    <div>
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-2xl font-bold">Integrations</h2>
                            <Link href="/mcp/server/config">
                                <Button>
                                    <Settings className="mr-2 h-4 w-4" />
                                    Add Integration
                                </Button>
                            </Link>
                        </div>

                        {integrations.length === 0 ? (
                            <Card>
                                <CardContent className="py-12 text-center">
                                    <p className="text-muted-foreground mb-4">No integrations configured yet</p>
                                    <Link href="/mcp/server/config">
                                        <Button>Configure Your First Integration</Button>
                                    </Link>
                                </CardContent>
                            </Card>
                        ) : (
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {integrations.map((integration) => (
                                    <Card key={integration.id} className="relative overflow-hidden">
                                        <div className={cn('absolute top-0 right-0 left-0 h-1', getStatusColor(integration.status))} />

                                        <CardHeader className="pb-3">
                                            <CardTitle className="flex items-center justify-between text-lg">
                                                <span className="flex items-center gap-2">
                                                    {getStatusIcon(integration.status)}
                                                    {integration.name}
                                                </span>
                                                <div className={cn('h-3 w-3 rounded-full', getStatusColor(integration.status))} />
                                            </CardTitle>
                                        </CardHeader>

                                        <CardContent className="space-y-3">
                                            <div className="space-y-2 text-sm">
                                                <div className="flex justify-between">
                                                    <span className="text-muted-foreground">Status:</span>
                                                    <span className="font-medium capitalize">{integration.status}</span>
                                                </div>
                                                <div className="flex justify-between">
                                                    <span className="text-muted-foreground">Enabled:</span>
                                                    <Badge variant={integration.enabled ? 'default' : 'secondary'}>
                                                        {integration.enabled ? 'Yes' : 'No'}
                                                    </Badge>
                                                </div>
                                                <div className="flex justify-between">
                                                    <span className="text-muted-foreground">Credentials:</span>
                                                    <span
                                                        className={cn(
                                                            'font-medium',
                                                            integration.credentialsValid ? 'text-green-600' : 'text-red-600',
                                                        )}
                                                    >
                                                        {integration.credentialsValid ? 'Valid' : 'Invalid'}
                                                    </span>
                                                </div>
                                                {integration.lastSync && (
                                                    <div className="flex justify-between">
                                                        <span className="text-muted-foreground">Last Sync:</span>
                                                        <span className="text-xs">{new Date(integration.lastSync).toLocaleTimeString()}</span>
                                                    </div>
                                                )}
                                            </div>

                                            {integration.errorMessage && (
                                                <Alert variant="destructive" className="py-2">
                                                    <AlertDescription className="text-xs">{integration.errorMessage}</AlertDescription>
                                                </Alert>
                                            )}

                                            <div className="flex gap-2 pt-2">
                                                <Link href={`/mcp/integrations/${integration.name}/configure`} className="flex-1">
                                                    <Button variant="outline" size="sm" className="w-full">
                                                        Configure
                                                    </Button>
                                                </Link>
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    onClick={() => testIntegration(integration.id, integration.name)}
                                                    disabled={isTestingIntegration === integration.id}
                                                    className="flex-1"
                                                >
                                                    {isTestingIntegration === integration.id ? (
                                                        <RefreshCw className="h-4 w-4 animate-spin" />
                                                    ) : (
                                                        'Test'
                                                    )}
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant={integration.enabled ? 'destructive' : 'default'}
                                                    onClick={() => toggleIntegration(integration.name)}
                                                    className="flex-1"
                                                >
                                                    {integration.enabled ? 'Disable' : 'Enable'}
                                                </Button>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
