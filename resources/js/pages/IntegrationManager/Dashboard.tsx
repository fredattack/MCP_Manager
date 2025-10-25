import React from 'react';
import { Head } from '@inertiajs/react';
import { MonologueCard } from '@/components/ui/MonologueCard';
import { MonologueButton } from '@/components/ui/MonologueButton';
import { MonologueBadge } from '@/components/ui/MonologueBadge';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/card';
import {
    CheckCircle2,
    XCircle,
    AlertCircle,
    Settings,
    TestTube,
    Trash2,
    Plus,
    Info,
    Plug
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { router } from '@inertiajs/react';
import { cn } from '@/lib/utils';

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

const serviceInfo: Record<string, { name: string; description: string; icon: string }> = {
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
        description: 'Project tracking and agile',
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
            return <MonologueBadge variant="muted" size="md">Not Configured</MonologueBadge>;
        }

        switch (integration.status) {
            case 'active':
                return <MonologueBadge variant="success" size="md">Active</MonologueBadge>;
            case 'error':
                return <MonologueBadge variant="default" size="md" className="bg-red-500 text-white">Error</MonologueBadge>;
            default:
                return <MonologueBadge variant="default" size="md">{integration.status}</MonologueBadge>;
        }
    };

    const getHealthIcon = (health: string) => {
        switch (health) {
            case 'healthy':
                return <CheckCircle2 className="w-5 h-5 text-monologue-brand-success" />;
            case 'degraded':
                return <AlertCircle className="w-5 h-5 text-yellow-500" />;
            case 'unhealthy':
                return <XCircle className="w-5 h-5 text-red-500" />;
            default:
                return <AlertCircle className="w-5 h-5 text-gray-400" />;
        }
    };

    const isActive = (integration: Integration) => integration.configured && integration.status === 'active';

    return (
        <AppLayout>
            <Head title="Integration Manager" />

            <div className="container mx-auto py-8">
                {/* Header with Monologue Typography */}
                <div className="mb-8">
                    <h1 className="flex items-center gap-3 font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">
                        <Plug className="h-8 w-8 text-gray-900 dark:text-monologue-brand-primary" />
                        Integration Manager
                    </h1>
                    <p className="mt-2 font-monologue-mono text-sm tracking-wide text-gray-600 dark:text-gray-400">
                        Configure and manage your service integrations through the MCP server
                    </p>
                </div>

                {/* MCP Server Status Alert */}
                <div className="mb-8 flex items-start gap-2 rounded-md bg-monologue-brand-primary/5 dark:bg-monologue-brand-primary/10 px-4 py-3 border border-monologue-brand-primary/10 dark:border-monologue-brand-primary/20">
                    <Info className="h-5 w-5 flex-shrink-0 text-monologue-brand-primary mt-0.5" />
                    <div className="flex-1">
                        <h3 className="font-monologue-mono text-sm font-medium text-gray-900 dark:text-white">
                            MCP Server Connected
                        </h3>
                        <p className="mt-0.5 font-monologue-mono text-xs text-gray-700 dark:text-gray-400">
                            Your integrations are managed through the MCP server at {window.location.hostname === 'localhost' ? 'http://localhost:9978' : 'your production server'}
                        </p>
                    </div>
                </div>

                {/* Integration Cards Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {Object.entries(serviceInfo).map(([service, info]) => {
                        const integration = integrations[service] || {
                            configured: false,
                            status: 'not_configured',
                            health: 'unknown'
                        };

                        const active = isActive(integration);

                        return (
                            <MonologueCard
                                key={service}
                                variant={active ? "elevated" : "default"}
                                className={cn(
                                    "relative group overflow-hidden transition-all duration-fast",
                                    active && "border-monologue-brand-primary/20",
                                    !active && !integration.configured && "opacity-90 hover:opacity-100"
                                )}
                            >
                                {/* Active Indicator - Left Border */}
                                {active && (
                                    <div className="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-monologue-brand-primary to-monologue-brand-accent" />
                                )}

                                <div className="p-6 pl-7">
                                    {/* Header */}
                                    <div className="flex items-start justify-between mb-4">
                                        <div className="flex items-center gap-3">
                                            {/* Icon with Enhanced Styling */}
                                            <div className={cn(
                                                "flex h-12 w-12 items-center justify-center rounded-lg transition-all duration-fast",
                                                active
                                                    ? "bg-monologue-brand-primary/10 text-monologue-brand-primary"
                                                    : "bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                            )}>
                                                <span className="text-2xl">{info.icon}</span>
                                            </div>

                                            {/* Title */}
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <h3 className="font-monologue-serif text-lg font-medium tracking-tight text-gray-900 dark:text-gray-100">
                                                        {info.name}
                                                    </h3>
                                                    {active && <CheckCircle2 className="h-4 w-4 text-monologue-brand-success" />}
                                                </div>
                                                <p className="mt-0.5 font-monologue-mono text-xs tracking-wide text-gray-500 dark:text-gray-400">
                                                    {info.description}
                                                </p>
                                            </div>
                                        </div>

                                        {/* Health Icon */}
                                        {getHealthIcon(integration.health)}
                                    </div>

                                    {/* Status Section */}
                                    <div className="flex items-center justify-between mb-3">
                                        <span className="font-monologue-mono text-xs text-muted-foreground">Status</span>
                                        {getStatusBadge(integration)}
                                    </div>

                                    {/* Last Sync Info */}
                                    {integration.last_sync && (
                                        <div className="mb-3 font-monologue-mono text-xs text-muted-foreground">
                                            Last sync: {new Date(integration.last_sync).toLocaleString()}
                                        </div>
                                    )}

                                    {/* Error Alert */}
                                    {integration.error && (
                                        <div className="mb-3 rounded-md bg-red-50 dark:bg-red-950/20 px-3 py-2 border border-red-200 dark:border-red-900">
                                            <p className="font-monologue-mono text-xs text-red-700 dark:text-red-400">
                                                {integration.error}
                                            </p>
                                        </div>
                                    )}

                                    {/* Actions */}
                                    <div className="flex gap-2 pt-2">
                                        {!integration.configured ? (
                                            <MonologueButton
                                                size="sm"
                                                variant="primary"
                                                className="flex-1"
                                                onClick={() => handleConfigure(service)}
                                                leftIcon={<Plus className="w-4 h-4" />}
                                            >
                                                Configure
                                            </MonologueButton>
                                        ) : (
                                            <>
                                                <MonologueButton
                                                    size="sm"
                                                    variant="ghost"
                                                    onClick={() => handleTest(service)}
                                                    leftIcon={<TestTube className="w-4 h-4" />}
                                                    className="flex-shrink-0"
                                                >
                                                </MonologueButton>
                                                <MonologueButton
                                                    size="sm"
                                                    variant={active ? "ghost" : "secondary"}
                                                    onClick={() => handleConfigure(service)}
                                                    leftIcon={<Settings className="w-4 h-4" />}
                                                    className="flex-shrink-0"
                                                >
                                                </MonologueButton>
                                                <MonologueButton
                                                    size="sm"
                                                    variant="ghost"
                                                    onClick={() => handleRemove(service)}
                                                    leftIcon={<Trash2 className="w-4 h-4" />}
                                                    className="flex-shrink-0 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/20"
                                                >
                                                </MonologueButton>
                                            </>
                                        )}
                                    </div>
                                </div>
                            </MonologueCard>
                        );
                    })}
                </div>
            </div>
        </AppLayout>
    );
}
