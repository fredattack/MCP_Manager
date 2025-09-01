import React from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { 
    CheckCircle, 
    XCircle, 
    AlertCircle, 
    Settings,
    TestTube,
    Trash2,
    Plus
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { router } from '@inertiajs/react';

interface Integration {
    configured: boolean;
    status: string;
    last_sync?: string;
    health: string;
    error?: string;
}

interface Props {
    integrations: Record<string, Integration>;
}

const serviceInfo = {
    todoist: {
        name: 'Todoist',
        description: 'Task management and productivity',
        icon: '✅',
    },
    notion: {
        name: 'Notion',
        description: 'Notes and knowledge management',
        icon: '📝',
    },
    jira: {
        name: 'Jira',
        description: 'Project tracking and agile management',
        icon: '🎯',
    },
    sentry: {
        name: 'Sentry',
        description: 'Error tracking and monitoring',
        icon: '🚨',
    },
    confluence: {
        name: 'Confluence',
        description: 'Team collaboration and documentation',
        icon: '📚',
    },
    openai: {
        name: 'OpenAI',
        description: 'GPT-4 AI assistant',
        icon: '🤖',
    },
    mistral: {
        name: 'Mistral',
        description: 'Mistral AI models',
        icon: '🌟',
    },
};

export default function Dashboard({ integrations }: Props) {
    const handleConfigure = (service: string) => {
        router.visit(`/integrations/manager/${service}/configure`);
    };

    const handleTest = async (service: string) => {
        try {
            const response = await fetch(`/integrations/manager/${service}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            
            const data = await response.json();
            if (data.success) {
                alert(`✅ ${data.message}`);
            } else {
                alert(`❌ ${data.message}`);
            }
        } catch (error) {
            alert('Failed to test connection');
        }
    };

    const handleRemove = (service: string) => {
        if (confirm(`Are you sure you want to remove ${serviceInfo[service]?.name || service} integration?`)) {
            router.delete(`/integrations/manager/${service}`);
        }
    };

    const getStatusBadge = (integration: Integration) => {
        if (!integration.configured) {
            return <Badge variant="secondary">Not Configured</Badge>;
        }
        
        switch (integration.status) {
            case 'active':
                return <Badge className="bg-green-500">Active</Badge>;
            case 'error':
                return <Badge variant="destructive">Error</Badge>;
            default:
                return <Badge variant="outline">{integration.status}</Badge>;
        }
    };

    const getHealthIcon = (health: string) => {
        switch (health) {
            case 'healthy':
                return <CheckCircle className="w-5 h-5 text-green-500" />;
            case 'degraded':
                return <AlertCircle className="w-5 h-5 text-yellow-500" />;
            case 'unhealthy':
                return <XCircle className="w-5 h-5 text-red-500" />;
            default:
                return <AlertCircle className="w-5 h-5 text-gray-400" />;
        }
    };

    return (
        <AppLayout>
            <Head title="Integration Manager" />
            
            <div className="container mx-auto py-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold mb-2">Integration Manager</h1>
                    <p className="text-muted-foreground">
                        Configure and manage your service integrations through the MCP server
                    </p>
                </div>

                <Alert className="mb-6">
                    <AlertCircle className="h-4 w-4" />
                    <AlertTitle>MCP Server Connected</AlertTitle>
                    <AlertDescription>
                        Your integrations are managed through the MCP server at {window.location.hostname === 'localhost' ? 'http://localhost:9978' : 'your production server'}
                    </AlertDescription>
                </Alert>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {Object.entries(serviceInfo).map(([service, info]) => {
                        const integration = integrations[service] || { 
                            configured: false, 
                            status: 'not_configured', 
                            health: 'unknown' 
                        };
                        
                        return (
                            <Card key={service} className="relative">
                                <CardHeader>
                                    <div className="flex items-start justify-between">
                                        <div className="flex items-center gap-3">
                                            <span className="text-2xl">{info.icon}</span>
                                            <div>
                                                <CardTitle className="text-lg">{info.name}</CardTitle>
                                                <CardDescription className="text-xs mt-1">
                                                    {info.description}
                                                </CardDescription>
                                            </div>
                                        </div>
                                        {getHealthIcon(integration.health)}
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm text-muted-foreground">Status</span>
                                            {getStatusBadge(integration)}
                                        </div>
                                        
                                        {integration.last_sync && (
                                            <div className="text-xs text-muted-foreground">
                                                Last sync: {new Date(integration.last_sync).toLocaleString()}
                                            </div>
                                        )}
                                        
                                        {integration.error && (
                                            <Alert variant="destructive" className="text-xs">
                                                <AlertDescription>{integration.error}</AlertDescription>
                                            </Alert>
                                        )}
                                        
                                        <div className="flex gap-2 pt-2">
                                            {!integration.configured ? (
                                                <Button
                                                    size="sm"
                                                    className="flex-1"
                                                    onClick={() => handleConfigure(service)}
                                                >
                                                    <Plus className="w-4 h-4 mr-1" />
                                                    Configure
                                                </Button>
                                            ) : (
                                                <>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => handleTest(service)}
                                                    >
                                                        <TestTube className="w-4 h-4" />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => handleConfigure(service)}
                                                    >
                                                        <Settings className="w-4 h-4" />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => handleRemove(service)}
                                                    >
                                                        <Trash2 className="w-4 h-4" />
                                                    </Button>
                                                </>
                                            )}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
            </div>
        </AppLayout>
    );
}