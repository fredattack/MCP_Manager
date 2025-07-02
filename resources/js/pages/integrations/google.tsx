import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
    Mail, 
    Calendar, 
    Settings, 
    CheckCircle, 
    XCircle, 
    ExternalLink, 
    Zap,
    Users,
    Clock,
    Shield
} from 'lucide-react';

interface GoogleIntegration {
    type: 'gmail' | 'calendar';
    status: 'active' | 'inactive' | 'error';
    email?: string;
    lastSync?: string;
    errorMessage?: string;
}

interface Props {
    integrations: {
        gmail?: GoogleIntegration;
        calendar?: GoogleIntegration;
    };
    authUrl?: string;
}

export default function GoogleIntegrations({ integrations = {}, authUrl }: Props) {
    const [showSetup, setShowSetup] = useState(false);
    
    const { post, processing } = useForm();

    const handleConnect = (service: 'gmail' | 'calendar') => {
        window.location.href = `/integrations/google/${service}/connect`;
    };

    const handleDisconnect = (service: 'gmail' | 'calendar') => {
        post(`/integrations/google/${service}/disconnect`, {
            onSuccess: () => {
                // Refresh page or update state
            },
        });
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active': return 'bg-green-100 text-green-800';
            case 'inactive': return 'bg-gray-100 text-gray-800';
            case 'error': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'active': return <CheckCircle className="h-4 w-4 text-green-600" />;
            case 'error': return <XCircle className="h-4 w-4 text-red-600" />;
            default: return <XCircle className="h-4 w-4 text-gray-400" />;
        }
    };

    const formatLastSync = (dateString?: string) => {
        if (!dateString) return 'Never';
        return new Date(dateString).toLocaleString();
    };

    return (
        <AppLayout>
            <Head title="Google Integrations" />

            <div className="mx-auto max-w-7xl p-6">
                
                {/* Header */}
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Google Integrations
                        </h1>
                        <p className="mt-1 text-gray-600 dark:text-gray-400">
                            Connect your Google services to enhance your workflow
                        </p>
                    </div>
                    <Link href="/integrations/google-setup">
                        <Button variant="outline">
                            <Settings className="h-4 w-4 mr-2" />
                            Setup Guide
                        </Button>
                    </Link>
                </div>
                    
                    {/* Overview Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        
                        {/* Gmail Integration */}
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="p-2 bg-red-100 rounded-lg">
                                            <Mail className="h-6 w-6 text-red-600" />
                                        </div>
                                        <div>
                                            <CardTitle>Gmail</CardTitle>
                                            <CardDescription>
                                                Email management and automation
                                            </CardDescription>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        {integrations.gmail && getStatusIcon(integrations.gmail.status)}
                                        <Badge className={getStatusColor(integrations.gmail?.status || 'inactive')}>
                                            {integrations.gmail?.status || 'Not Connected'}
                                        </Badge>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {integrations.gmail?.status === 'active' ? (
                                    <div className="space-y-4">
                                        <div className="text-sm text-gray-600 dark:text-gray-400">
                                            <p><strong>Connected as:</strong> {integrations.gmail.email}</p>
                                            <p><strong>Last sync:</strong> {formatLastSync(integrations.gmail.lastSync)}</p>
                                        </div>
                                        
                                        <div className="flex gap-2">
                                            <Link href="/gmail">
                                                <Button size="sm">
                                                    <ExternalLink className="h-4 w-4 mr-2" />
                                                    Open Gmail
                                                </Button>
                                            </Link>
                                            <Button 
                                                variant="outline" 
                                                size="sm"
                                                onClick={() => handleDisconnect('gmail')}
                                                disabled={processing}
                                            >
                                                Disconnect
                                            </Button>
                                        </div>
                                        
                                        <div className="text-xs text-gray-500">
                                            <div className="flex items-center gap-2 mb-1">
                                                <Zap className="h-3 w-3" />
                                                <span>Features: Read, Send, Search, Labels</span>
                                            </div>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="space-y-4">
                                        {integrations.gmail?.errorMessage && (
                                            <Alert>
                                                <XCircle className="h-4 w-4" />
                                                <AlertDescription>
                                                    {integrations.gmail.errorMessage}
                                                </AlertDescription>
                                            </Alert>
                                        )}
                                        
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            Connect your Gmail account to manage emails, send messages, 
                                            and automate email workflows.
                                        </p>
                                        
                                        <Button 
                                            onClick={() => handleConnect('gmail')}
                                            disabled={processing || !authUrl}
                                            className="w-full"
                                        >
                                            <Mail className="h-4 w-4 mr-2" />
                                            Connect Gmail
                                        </Button>
                                        
                                        <div className="text-xs text-gray-500">
                                            <div className="flex items-center gap-2 mb-1">
                                                <Shield className="h-3 w-3" />
                                                <span>Secure OAuth 2.0 authentication</span>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Calendar Integration */}
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="p-2 bg-blue-100 rounded-lg">
                                            <Calendar className="h-6 w-6 text-blue-600" />
                                        </div>
                                        <div>
                                            <CardTitle>Google Calendar</CardTitle>
                                            <CardDescription>
                                                Schedule and event management
                                            </CardDescription>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        {integrations.calendar && getStatusIcon(integrations.calendar.status)}
                                        <Badge className={getStatusColor(integrations.calendar?.status || 'inactive')}>
                                            {integrations.calendar?.status || 'Not Connected'}
                                        </Badge>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {integrations.calendar?.status === 'active' ? (
                                    <div className="space-y-4">
                                        <div className="text-sm text-gray-600 dark:text-gray-400">
                                            <p><strong>Connected as:</strong> {integrations.calendar.email}</p>
                                            <p><strong>Last sync:</strong> {formatLastSync(integrations.calendar.lastSync)}</p>
                                        </div>
                                        
                                        <div className="flex gap-2">
                                            <Link href="/calendar">
                                                <Button size="sm">
                                                    <ExternalLink className="h-4 w-4 mr-2" />
                                                    Open Calendar
                                                </Button>
                                            </Link>
                                            <Button 
                                                variant="outline" 
                                                size="sm"
                                                onClick={() => handleDisconnect('calendar')}
                                                disabled={processing}
                                            >
                                                Disconnect
                                            </Button>
                                        </div>
                                        
                                        <div className="text-xs text-gray-500">
                                            <div className="flex items-center gap-2 mb-1">
                                                <Users className="h-3 w-3" />
                                                <span>Features: Events, Invites, Conflicts, Scheduling</span>
                                            </div>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="space-y-4">
                                        {integrations.calendar?.errorMessage && (
                                            <Alert>
                                                <XCircle className="h-4 w-4" />
                                                <AlertDescription>
                                                    {integrations.calendar.errorMessage}
                                                </AlertDescription>
                                            </Alert>
                                        )}
                                        
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            Connect your Google Calendar to manage events, schedule meetings, 
                                            and avoid scheduling conflicts.
                                        </p>
                                        
                                        <Button 
                                            onClick={() => handleConnect('calendar')}
                                            disabled={processing || !authUrl}
                                            className="w-full"
                                        >
                                            <Calendar className="h-4 w-4 mr-2" />
                                            Connect Calendar
                                        </Button>
                                        
                                        <div className="text-xs text-gray-500">
                                            <div className="flex items-center gap-2 mb-1">
                                                <Clock className="h-3 w-3" />
                                                <span>Real-time synchronization</span>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Features Overview */}
                    <Card>
                        <CardHeader>
                            <CardTitle>What you can do with Google Integrations</CardTitle>
                            <CardDescription>
                                Powerful features to streamline your workflow
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 className="font-medium flex items-center gap-2 mb-3">
                                        <Mail className="h-5 w-5 text-red-600" />
                                        Gmail Features
                                    </h3>
                                    <ul className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                        <li>• Read and manage your emails</li>
                                        <li>• Send emails with attachments</li>
                                        <li>• Search through your messages</li>
                                        <li>• Organize with labels and filters</li>
                                        <li>• Automate email workflows</li>
                                        <li>• Reply and forward messages</li>
                                    </ul>
                                </div>
                                <div>
                                    <h3 className="font-medium flex items-center gap-2 mb-3">
                                        <Calendar className="h-5 w-5 text-blue-600" />
                                        Calendar Features
                                    </h3>
                                    <ul className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                        <li>• Create and manage events</li>
                                        <li>• Invite attendees and manage RSVPs</li>
                                        <li>• Check for scheduling conflicts</li>
                                        <li>• View daily and weekly schedules</li>
                                        <li>• Set reminders and notifications</li>
                                        <li>• Bulk operations and automation</li>
                                    </ul>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Setup Guide Modal */}
                    {showSetup && (
                        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                            <Card className="w-full max-w-2xl max-h-[80vh] overflow-y-auto">
                                <CardHeader>
                                    <CardTitle>Google Integration Setup</CardTitle>
                                    <CardDescription>
                                        Follow these steps to configure Google integrations
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    <div>
                                        <h3 className="font-medium mb-2">1. MCP Server Configuration</h3>
                                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            Ensure your MCP server is configured with Google OAuth credentials:
                                        </p>
                                        <pre className="bg-gray-100 dark:bg-gray-800 p-3 rounded text-xs overflow-x-auto">
{`{
  "mcpServers": {
    "google-server": {
      "command": "node",
      "args": ["path/to/mcp-google-server.js"],
      "env": {
        "GOOGLE_CLIENT_ID": "your-client-id",
        "GOOGLE_CLIENT_SECRET": "your-client-secret",
        "GOOGLE_REDIRECT_URI": "http://localhost:9978/auth/callback"
      }
    }
  }
}`}
                                        </pre>
                                    </div>
                                    
                                    <div>
                                        <h3 className="font-medium mb-2">2. Required Scopes</h3>
                                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            Your Google OAuth app needs these scopes:
                                        </p>
                                        <ul className="text-sm space-y-1">
                                            <li>• <code>https://www.googleapis.com/auth/gmail.readonly</code></li>
                                            <li>• <code>https://www.googleapis.com/auth/gmail.send</code></li>
                                            <li>• <code>https://www.googleapis.com/auth/calendar</code></li>
                                            <li>• <code>https://www.googleapis.com/auth/calendar.events</code></li>
                                        </ul>
                                    </div>
                                    
                                    <div>
                                        <h3 className="font-medium mb-2">3. Security</h3>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            All communications use OAuth 2.0 and tokens are encrypted at rest. 
                                            You can revoke access anytime from your Google Account settings.
                                        </p>
                                    </div>
                                    
                                    <div className="flex justify-end">
                                        <Button onClick={() => setShowSetup(false)}>
                                            Got it
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    )}
                </div>
        </AppLayout>
    );
}