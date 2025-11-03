import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Badge } from '@/components/ui/badge';
import { Database, Server, Activity, CheckCircle2, XCircle, AlertCircle } from 'lucide-react';
import { useEffect, useState } from 'react';
import axios from 'axios';

interface SystemHealth {
    status: 'healthy' | 'degraded' | 'unhealthy';
    timestamp: string;
    services: {
        database: ServiceHealth;
        mcp_server: ServiceHealth;
        application: ApplicationHealth;
    };
}

interface ServiceHealth {
    status: 'healthy' | 'unhealthy' | 'not_configured';
    message: string;
    [key: string]: unknown;
}

interface ApplicationHealth extends ServiceHealth {
    environment: string;
    debug_mode: boolean;
    php_version: string;
    laravel_version: string;
}

interface SystemStatusDrawerProps {
    isOpen: boolean;
    onClose: () => void;
}

export function SystemStatusDrawer({ isOpen, onClose }: SystemStatusDrawerProps) {
    const [health, setHealth] = useState<SystemHealth | null>(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (isOpen) {
            fetchHealth();
        }
    }, [isOpen]);

    const fetchHealth = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/system/health');
            setHealth(response.data);
        } catch (error) {
            console.error('Failed to fetch system health:', error);
        } finally {
            setLoading(false);
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'healthy':
                return <CheckCircle2 className="h-5 w-5 text-green-600 dark:text-green-400" />;
            case 'unhealthy':
                return <XCircle className="h-5 w-5 text-red-600 dark:text-red-400" />;
            case 'not_configured':
                return <AlertCircle className="h-5 w-5 text-yellow-600 dark:text-yellow-400" />;
            default:
                return <Activity className="h-5 w-5 text-gray-600 dark:text-gray-400" />;
        }
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'healthy':
                return (
                    <Badge className="bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        Healthy
                    </Badge>
                );
            case 'unhealthy':
                return (
                    <Badge className="bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                        Unhealthy
                    </Badge>
                );
            case 'degraded':
                return (
                    <Badge className="bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                        Degraded
                    </Badge>
                );
            case 'not_configured':
                return (
                    <Badge className="bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400">
                        Not Configured
                    </Badge>
                );
            default:
                return <Badge>Unknown</Badge>;
        }
    };

    return (
        <Sheet open={isOpen} onOpenChange={onClose}>
            <SheetContent className="w-full overflow-y-auto bg-white dark:bg-gray-900 sm:max-w-xl">
                <SheetHeader>
                    <SheetTitle className="font-monologue-serif text-2xl text-gray-900 dark:text-white">
                        System Status
                    </SheetTitle>
                    <SheetDescription className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                        Monitor the health of all system services
                    </SheetDescription>
                </SheetHeader>

                <div className="mt-6 space-y-6">
                    {loading ? (
                        <div className="flex items-center justify-center py-12">
                            <div className="h-8 w-8 animate-spin rounded-full border-4 border-cyan-500 border-t-transparent" />
                        </div>
                    ) : health ? (
                        <>
                            {/* Overall Status */}
                            <div className="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h3 className="font-monologue-serif text-lg text-gray-900 dark:text-white">
                                            Overall System Status
                                        </h3>
                                        <p className="font-monologue-mono mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Last checked: {new Date(health.timestamp).toLocaleString()}
                                        </p>
                                    </div>
                                    {getStatusBadge(health.status)}
                                </div>
                            </div>

                            {/* Database Status */}
                            <div className="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <div className="mb-3 flex items-center gap-3">
                                    <Database className="h-6 w-6 text-cyan-600 dark:text-cyan-400" />
                                    <div className="flex-1">
                                        <h4 className="font-monologue-serif text-base text-gray-900 dark:text-white">
                                            Database
                                        </h4>
                                    </div>
                                    {getStatusIcon(health.services.database.status)}
                                </div>
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                                            Status:
                                        </span>
                                        {getStatusBadge(health.services.database.status)}
                                    </div>
                                    {health.services.database.database && (
                                        <div className="flex justify-between">
                                            <span className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                                                Database:
                                            </span>
                                            <span className="font-monologue-mono text-sm text-gray-900 dark:text-white">
                                                {String(health.services.database.database)}
                                            </span>
                                        </div>
                                    )}
                                    {health.services.database.driver && (
                                        <div className="flex justify-between">
                                            <span className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                                                Driver:
                                            </span>
                                            <span className="font-monologue-mono text-sm text-gray-900 dark:text-white">
                                                {String(health.services.database.driver)}
                                            </span>
                                        </div>
                                    )}
                                    <p className="font-monologue-mono text-xs text-gray-500 dark:text-gray-500">
                                        {health.services.database.message}
                                    </p>
                                </div>
                            </div>

                            {/* MCP Server Status */}
                            <div className="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <div className="mb-3 flex items-center gap-3">
                                    <Server className="h-6 w-6 text-purple-600 dark:text-purple-400" />
                                    <div className="flex-1">
                                        <h4 className="font-monologue-serif text-base text-gray-900 dark:text-white">
                                            MCP Server
                                        </h4>
                                    </div>
                                    {getStatusIcon(health.services.mcp_server.status)}
                                </div>
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                                            Status:
                                        </span>
                                        {getStatusBadge(health.services.mcp_server.status)}
                                    </div>
                                    {health.services.mcp_server.url && (
                                        <div className="flex justify-between">
                                            <span className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                                                URL:
                                            </span>
                                            <span className="font-monologue-mono text-sm text-gray-900 dark:text-white">
                                                {String(health.services.mcp_server.url)}
                                            </span>
                                        </div>
                                    )}
                                    <p className="font-monologue-mono text-xs text-gray-500 dark:text-gray-500">
                                        {health.services.mcp_server.message}
                                    </p>
                                </div>
                            </div>

                            {/* Application Info */}
                            <div className="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <div className="mb-3 flex items-center gap-3">
                                    <Activity className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                    <div className="flex-1">
                                        <h4 className="font-monologue-serif text-base text-gray-900 dark:text-white">
                                            Application
                                        </h4>
                                    </div>
                                    {getStatusIcon(health.services.application.status)}
                                </div>
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                                            Environment:
                                        </span>
                                        <span className="font-monologue-mono text-sm text-gray-900 dark:text-white">
                                            {health.services.application.environment}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                                            PHP Version:
                                        </span>
                                        <span className="font-monologue-mono text-sm text-gray-900 dark:text-white">
                                            {health.services.application.php_version}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                                            Laravel Version:
                                        </span>
                                        <span className="font-monologue-mono text-sm text-gray-900 dark:text-white">
                                            {health.services.application.laravel_version}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Refresh Button */}
                            <button
                                onClick={fetchHealth}
                                className="w-full rounded-lg bg-cyan-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cyan-600"
                            >
                                Refresh Status
                            </button>
                        </>
                    ) : (
                        <div className="text-center text-gray-500">No health data available</div>
                    )}
                </div>
            </SheetContent>
        </Sheet>
    );
}
