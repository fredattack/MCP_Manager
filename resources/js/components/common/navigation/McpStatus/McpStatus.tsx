import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Activity, CheckCircle2, Info } from 'lucide-react';
import { useEffect, useState } from 'react';
import { SystemStatusDrawer } from './SystemStatusDrawer';
import axios from 'axios';
import { usePage } from '@inertiajs/react';

interface SystemHealth {
    status: 'healthy' | 'degraded' | 'unhealthy';
}

export function McpStatus() {
    const { props } = usePage();
    const [systemHealth, setSystemHealth] = useState<SystemHealth | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [showStatusDrawer, setShowStatusDrawer] = useState(false);

    // Check if user is admin
    const auth = props.auth as { user?: { role?: string } } | undefined;
    const isAdmin = auth?.user?.role === 'admin';

    useEffect(() => {
        fetchHealth();
        // Poll health every 30 seconds
        const interval = setInterval(fetchHealth, 30000);
        return () => clearInterval(interval);
    }, []);

    const fetchHealth = async () => {
        try {
            const response = await axios.get('/api/system/health');
            setSystemHealth(response.data);
        } catch (error) {
            console.error('Failed to fetch system health:', error);
            setSystemHealth({ status: 'unhealthy' });
        } finally {
            setIsLoading(false);
        }
    };

    if (isLoading) {
        return (
            <div className="flex items-center gap-2">
                <div className="h-3 w-3 animate-spin rounded-full border border-gray-400 border-t-transparent" />
                <span className="text-xs text-gray-600 dark:text-gray-400">Checking...</span>
            </div>
        );
    }

    const getStatusDisplay = () => {
        if (!systemHealth) {
            return {
                icon: <Activity className="mr-1 h-3 w-3" />,
                text: 'Unknown',
                variant: 'secondary' as const,
                className: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            };
        }

        switch (systemHealth.status) {
            case 'healthy':
                return {
                    icon: <CheckCircle2 className="mr-1 h-3 w-3" />,
                    text: 'System Healthy',
                    variant: 'default' as const,
                    className: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                };
            case 'degraded':
                return {
                    icon: <Activity className="mr-1 h-3 w-3" />,
                    text: 'System Degraded',
                    variant: 'secondary' as const,
                    className: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                };
            case 'unhealthy':
                return {
                    icon: <Activity className="mr-1 h-3 w-3" />,
                    text: 'System Issue',
                    variant: 'destructive' as const,
                    className: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                };
            default:
                return {
                    icon: <Activity className="mr-1 h-3 w-3" />,
                    text: 'Unknown',
                    variant: 'secondary' as const,
                    className: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                };
        }
    };

    const statusDisplay = getStatusDisplay();

    return (
        <>
            <div className="flex items-center gap-2">
                <Badge variant={statusDisplay.variant} className={statusDisplay.className}>
                    {statusDisplay.icon}
                    {statusDisplay.text}
                </Badge>

                {isAdmin && (
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setShowStatusDrawer(true)}
                        className="h-6 px-2 text-xs"
                    >
                        <Info className="mr-1 h-3 w-3" />
                        Details
                    </Button>
                )}
            </div>

            {isAdmin && (
                <SystemStatusDrawer isOpen={showStatusDrawer} onClose={() => setShowStatusDrawer(false)} />
            )}
        </>
    );
}
