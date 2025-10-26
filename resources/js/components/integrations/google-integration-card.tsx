import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/react';
import { AlertTriangle, Calendar, CheckCircle, Clock, ExternalLink, Mail, RefreshCw, Settings, XCircle } from 'lucide-react';
import { useState } from 'react';

interface GoogleIntegration {
    type: 'gmail' | 'calendar';
    status: 'active' | 'inactive' | 'error';
    email?: string;
    lastSync?: string;
    errorMessage?: string;
    meta?: {
        scopes?: string[];
        tokenExpiry?: string;
        refreshToken?: boolean;
    };
}

interface Props {
    integration: GoogleIntegration;
    onRefresh?: () => void;
    onDisconnect?: () => void;
    onReconnect?: () => void;
}

export function GoogleIntegrationCard({ integration, onRefresh, onDisconnect, onReconnect }: Props) {
    const [isLoading, setIsLoading] = useState(false);

    const getIcon = () => {
        switch (integration.type) {
            case 'gmail':
                return <Mail className="h-6 w-6 text-red-600" />;
            case 'calendar':
                return <Calendar className="h-6 w-6 text-blue-600" />;
            default:
                return <Settings className="h-6 w-6 text-gray-600" />;
        }
    };

    const getTitle = () => {
        switch (integration.type) {
            case 'gmail':
                return 'Gmail';
            case 'calendar':
                return 'Google Calendar';
            default:
                return 'Google Service';
        }
    };

    const getDescription = () => {
        switch (integration.type) {
            case 'gmail':
                return 'Email management and automation';
            case 'calendar':
                return 'Schedule and event management';
            default:
                return 'Google service integration';
        }
    };

    const getStatusColor = () => {
        switch (integration.status) {
            case 'active':
                return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
            case 'inactive':
                return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
            case 'error':
                return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
        }
    };

    const getStatusIcon = () => {
        switch (integration.status) {
            case 'active':
                return <CheckCircle className="h-4 w-4 text-green-600" />;
            case 'error':
                return <XCircle className="h-4 w-4 text-red-600" />;
            default:
                return <XCircle className="h-4 w-4 text-gray-400" />;
        }
    };

    const formatLastSync = (dateString?: string) => {
        if (!dateString) return 'Never';
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffMins = Math.floor(diffMs / 60000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;
        return date.toLocaleDateString();
    };

    const isTokenExpiring = () => {
        if (!integration.meta?.tokenExpiry) return false;
        const expiry = new Date(integration.meta.tokenExpiry);
        const now = new Date();
        const diffHours = (expiry.getTime() - now.getTime()) / (1000 * 60 * 60);
        return diffHours < 24; // Token expires within 24 hours
    };

    const handleRefresh = async () => {
        setIsLoading(true);
        try {
            await onRefresh?.();
        } finally {
            setIsLoading(false);
        }
    };

    const getActionUrl = () => {
        switch (integration.type) {
            case 'gmail':
                return '/gmail';
            case 'calendar':
                return '/calendar';
            default:
                return '/integrations/google';
        }
    };

    return (
        <Card className="transition-shadow hover:shadow-md">
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="rounded-lg bg-gray-100 p-2 dark:bg-gray-800">{getIcon()}</div>
                        <div>
                            <CardTitle className="text-lg">{getTitle()}</CardTitle>
                            <CardDescription>{getDescription()}</CardDescription>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        {getStatusIcon()}
                        <Badge className={getStatusColor()}>{integration.status}</Badge>
                    </div>
                </div>
            </CardHeader>

            <CardContent className="space-y-4">
                {/* Connection Details */}
                {integration.status === 'active' && (
                    <div className="space-y-2">
                        <div className="text-sm text-gray-600 dark:text-gray-400">
                            <div className="flex justify-between">
                                <span>Account:</span>
                                <span className="font-medium">{integration.email}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>Last sync:</span>
                                <span className="flex items-center gap-1">
                                    <Clock className="h-3 w-3" />
                                    {formatLastSync(integration.lastSync)}
                                </span>
                            </div>
                        </div>

                        {/* Token Expiry Warning */}
                        {isTokenExpiring() && (
                            <Alert>
                                <AlertTriangle className="h-4 w-4" />
                                <AlertDescription className="text-sm">
                                    Access token expires soon. Consider refreshing the connection.
                                </AlertDescription>
                            </Alert>
                        )}
                    </div>
                )}

                {/* Error Message */}
                {integration.status === 'error' && integration.errorMessage && (
                    <Alert>
                        <XCircle className="h-4 w-4" />
                        <AlertDescription className="text-sm">{integration.errorMessage}</AlertDescription>
                    </Alert>
                )}

                {/* Scopes Information */}
                {integration.meta?.scopes && integration.meta.scopes.length > 0 && (
                    <div className="text-xs text-gray-500">
                        <div className="mb-1 font-medium">Permissions:</div>
                        <div className="flex flex-wrap gap-1">
                            {integration.meta.scopes.map((scope) => (
                                <Badge key={scope} variant="outline" className="text-xs">
                                    {scope.split('.').pop()}
                                </Badge>
                            ))}
                        </div>
                    </div>
                )}

                {/* Actions */}
                <div className="flex gap-2 pt-2">
                    {integration.status === 'active' ? (
                        <>
                            <Link href={getActionUrl()}>
                                <Button size="sm" className="flex-1">
                                    <ExternalLink className="mr-2 h-4 w-4" />
                                    Open
                                </Button>
                            </Link>
                            <Button variant="outline" size="sm" onClick={handleRefresh} disabled={isLoading}>
                                <RefreshCw className={`h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
                            </Button>
                            <Button variant="outline" size="sm" onClick={onDisconnect}>
                                Disconnect
                            </Button>
                        </>
                    ) : (
                        <>
                            <Button onClick={onReconnect} className="flex-1" size="sm">
                                {integration.status === 'error' ? 'Reconnect' : 'Connect'}
                            </Button>
                            <Link href="/integrations/google-setup">
                                <Button variant="outline" size="sm">
                                    <Settings className="h-4 w-4" />
                                </Button>
                            </Link>
                        </>
                    )}
                </div>

                {/* Quick Stats */}
                {integration.status === 'active' && (
                    <div className="grid grid-cols-2 gap-4 border-t pt-2 text-xs text-gray-500">
                        <div>
                            <div className="font-medium">Features</div>
                            <div>
                                {integration.type === 'gmail' && 'Read, Send, Search'}
                                {integration.type === 'calendar' && 'Events, Scheduling'}
                            </div>
                        </div>
                        <div>
                            <div className="font-medium">Security</div>
                            <div className="flex items-center gap-1">
                                {integration.meta?.refreshToken ? (
                                    <>
                                        <CheckCircle className="h-3 w-3 text-green-500" />
                                        <span>Auto-refresh</span>
                                    </>
                                ) : (
                                    <>
                                        <AlertTriangle className="h-3 w-3 text-yellow-500" />
                                        <span>Manual refresh</span>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
