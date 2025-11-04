import { IntegrationFormDynamic } from '@/components/integrations/integration-form-dynamic';
import { IntegrationIcon, IntegrationIconWithBackground } from '@/components/integrations/integration-icon';
import { MonologueBadge } from '@/components/ui/MonologueBadge';
import { MonologueButton } from '@/components/ui/MonologueButton';
import { MonologueCard } from '@/components/ui/MonologueCard';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { IntegrationType } from '@/types/integrations';
import { Head, router } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, Info, Plug, Plus, Settings, TestTube, Trash2, XCircle } from 'lucide-react';
import React from 'react';

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

const serviceInfo: Record<string, { name: string; description: string }> = {
    todoist: {
        name: 'Todoist',
        description: 'Task management and productivity',
    },
    notion: {
        name: 'Notion',
        description: 'Notes and knowledge management',
    },
    jira: {
        name: 'Jira',
        description: 'Project tracking and agile',
    },
    sentry: {
        name: 'Sentry',
        description: 'Error tracking and monitoring',
    },
    confluence: {
        name: 'Confluence',
        description: 'Team collaboration and documentation',
    },
    openai: {
        name: 'OpenAI',
        description: 'GPT-4 AI assistant',
    },
    mistral: {
        name: 'Mistral',
        description: 'Mistral AI models',
    },
};

export default function Dashboard({ integrations }: Props) {
    const [isAddDialogOpen, setIsAddDialogOpen] = React.useState(false);
    const [selectedService, setSelectedService] = React.useState<string>('');

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

    const handleConfigure = (service: string) => {
        router.visit(`/integrations/manager/${service}/configure`);
    };

    const handleAddIntegration = async (formData: any) => {
        try {
            router.post(`/integrations/manager/${selectedService}`, formData, {
                onSuccess: () => {
                    setIsAddDialogOpen(false);
                    setSelectedService('');
                },
            });
        } catch (error) {
            console.error('Failed to add integration:', error);
            throw error;
        }
    };

    const getStatusBadge = (integration: Integration) => {
        if (!integration.configured) {
            return (
                <MonologueBadge variant="muted" size="sm">
                    Not Configured
                </MonologueBadge>
            );
        }

        switch (integration.status) {
            case 'active':
                return (
                    <MonologueBadge variant="active" size="sm">
                        Active
                    </MonologueBadge>
                );
            case 'error':
                return (
                    <MonologueBadge variant="error" size="sm">
                        Error
                    </MonologueBadge>
                );
            default:
                return (
                    <MonologueBadge variant="default" size="sm">
                        {integration.status}
                    </MonologueBadge>
                );
        }
    };

    const getHealthIcon = (health: string) => {
        switch (health) {
            case 'healthy':
                return <CheckCircle2 className="text-monologue-brand-success h-4 w-4 flex-shrink-0" />;
            case 'degraded':
                return <AlertCircle className="h-4 w-4 flex-shrink-0 text-yellow-500" />;
            case 'unhealthy':
                return <XCircle className="h-4 w-4 flex-shrink-0 text-red-500" />;
            default:
                return <AlertCircle className="h-4 w-4 flex-shrink-0 text-gray-400" />;
        }
    };

    const isActive = (integration: Integration) => integration.configured && integration.status === 'active';

    // Filter active integrations
    const activeIntegrations = Object.entries(integrations).filter(([_, integration]) => integration.configured && integration.status === 'active');

    // Get available services (not configured yet)
    const availableServices = Object.entries(serviceInfo).filter(([service]) => {
        const integration = integrations[service];
        return !integration || !integration.configured;
    });

    // Map service names to IntegrationType
    const getIntegrationType = (service: string): IntegrationType => {
        const mapping: Record<string, IntegrationType> = {
            notion: IntegrationType.NOTION,
            todoist: IntegrationType.TODOIST,
            jira: IntegrationType.JIRA,
            openai: IntegrationType.OPENAI,
            sentry: IntegrationType.SENTRY,
            gmail: IntegrationType.GMAIL,
        };

        return mapping[service] || IntegrationType.NOTION;
    };

    return (
        <AppLayout>
            <Head title="Integration Manager" />

            <div className="container mx-auto py-8">
                {/* Header with Monologue Typography */}
                <div className="mb-8">
                    <h1 className="font-monologue-serif flex items-center gap-3 text-4xl font-normal tracking-tight text-gray-900 dark:text-white">
                        <Plug className="dark:text-monologue-brand-primary h-8 w-8 text-gray-900" />
                        Integration Manager
                    </h1>
                    <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">
                        Configure and manage your service integrations through the MCP server
                    </p>
                </div>

                {/* MCP Server Status Alert */}
                <div className="bg-monologue-brand-primary/5 dark:bg-monologue-brand-primary/10 border-monologue-brand-primary/10 dark:border-monologue-brand-primary/20 mb-8 flex items-start gap-2 rounded-md border px-4 py-3">
                    <Info className="text-monologue-brand-primary mt-0.5 h-5 w-5 flex-shrink-0" />
                    <div className="flex-1">
                        <h3 className="font-monologue-mono text-sm font-medium text-gray-900 dark:text-white">MCP Server Connected</h3>
                        <p className="font-monologue-mono mt-0.5 text-xs text-gray-700 dark:text-gray-400">
                            Your integrations are managed through the MCP server at{' '}
                            {window.location.hostname === 'localhost' ? 'http://localhost:9978' : 'your production server'}
                        </p>
                    </div>
                </div>

                {/* Active Integrations Section */}
                <div className="mb-8">
                    <div className="mb-4 flex items-center justify-between">
                        <div>
                            <h2 className="font-monologue-serif text-2xl font-normal tracking-tight text-gray-900 dark:text-white">
                                Active Integrations
                                <span className="font-monologue-mono ml-2 text-base text-gray-500 dark:text-gray-400">
                                    ({activeIntegrations.length})
                                </span>
                            </h2>
                        </div>
                        <MonologueButton variant="primary" size="md" onClick={() => setIsAddDialogOpen(true)} leftIcon={<Plus className="h-4 w-4" />}>
                            Add Integration
                        </MonologueButton>
                    </div>

                    {/* Empty State */}
                    {activeIntegrations.length === 0 && (
                        <MonologueCard variant="default" className="py-12">
                            <div className="flex flex-col items-center justify-center gap-4 text-center">
                                <div className="bg-monologue-brand-primary/10 flex h-16 w-16 items-center justify-center rounded-full">
                                    <Plug className="text-monologue-brand-primary h-8 w-8" />
                                </div>
                                <div>
                                    <h3 className="font-monologue-serif mb-1 text-xl font-normal text-gray-900 dark:text-white">
                                        No Integrations Yet
                                    </h3>
                                    <p className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                                        Get started by adding your first integration
                                    </p>
                                </div>
                                <MonologueButton
                                    variant="primary"
                                    size="lg"
                                    onClick={() => setIsAddDialogOpen(true)}
                                    leftIcon={<Plus className="h-4 w-4" />}
                                >
                                    Add Integration
                                </MonologueButton>
                            </div>
                        </MonologueCard>
                    )}

                    {/* Active Integrations List */}
                    {activeIntegrations.length > 0 && (
                        <div className="space-y-3">
                            {activeIntegrations.map(([service, integration]) => {
                                const info = serviceInfo[service];
                                const active = isActive(integration);

                                return (
                                    <MonologueCard
                                        key={service}
                                        variant="default"
                                        className={cn(
                                            'duration-fast group hover:border-monologue-brand-primary/30 relative overflow-hidden transition-all',
                                            active && 'border-monologue-brand-primary/20',
                                        )}
                                    >
                                        {/* Active Indicator - Left Border */}
                                        {active && (
                                            <div className="from-monologue-brand-primary to-monologue-brand-accent absolute top-0 left-0 h-full w-1 bg-gradient-to-b" />
                                        )}

                                        <div className="flex items-center gap-4 p-4 pl-5">
                                            {/* Icon */}
                                            <IntegrationIconWithBackground
                                                service={service}
                                                size={32}
                                                backgroundClassName={cn(
                                                    'duration-fast flex-shrink-0 transition-all',
                                                    active ? 'bg-monologue-brand-primary/10' : 'bg-gray-100 dark:bg-gray-800',
                                                )}
                                            />

                                            {/* Content */}
                                            <div className="flex flex-1 items-start justify-between gap-4">
                                                <div className="flex-1">
                                                    {/* Title & Description */}
                                                    <div className="mb-1 flex items-center gap-2">
                                                        <h3 className="font-monologue-serif text-lg font-medium tracking-tight text-gray-900 dark:text-gray-100">
                                                            {info?.name}
                                                        </h3>
                                                        {getStatusBadge(integration)}
                                                        {getHealthIcon(integration.health)}
                                                    </div>
                                                    <p className="font-monologue-mono text-xs tracking-wide text-gray-500 dark:text-gray-400">
                                                        {info?.description}
                                                    </p>

                                                    {/* Last Sync */}
                                                    {integration.last_sync && (
                                                        <p className="font-monologue-mono mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                            Last sync: {new Date(integration.last_sync).toLocaleString()}
                                                        </p>
                                                    )}

                                                    {/* Error Alert */}
                                                    {integration.error && (
                                                        <div className="mt-2 flex items-start gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-2 dark:border-red-900 dark:bg-red-950/20">
                                                            <AlertCircle className="mt-0.5 h-4 w-4 flex-shrink-0 text-red-600 dark:text-red-400" />
                                                            <p className="font-monologue-mono text-xs text-red-700 dark:text-red-400">
                                                                {integration.error}
                                                            </p>
                                                        </div>
                                                    )}
                                                </div>

                                                {/* Actions */}
                                                <div className="flex flex-shrink-0 items-center gap-2">
                                                    <MonologueButton
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() => handleTest(service)}
                                                        leftIcon={<TestTube className="h-4 w-4" />}
                                                        className="flex-shrink-0"
                                                        title="Test Connection"
                                                    />
                                                    <MonologueButton
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() => handleConfigure(service)}
                                                        leftIcon={<Settings className="h-4 w-4" />}
                                                        className="flex-shrink-0"
                                                        title="Configure"
                                                    />
                                                    <MonologueButton
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() => handleRemove(service)}
                                                        leftIcon={<Trash2 className="h-4 w-4" />}
                                                        className="flex-shrink-0 text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/20"
                                                        title="Remove"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </MonologueCard>
                                );
                            })}
                        </div>
                    )}
                </div>

                {/* Add Integration Dialog */}
                <Dialog open={isAddDialogOpen} onOpenChange={setIsAddDialogOpen}>
                    <DialogContent className="max-w-2xl">
                        <DialogHeader>
                            <DialogTitle className="font-monologue-serif text-2xl">Add Integration</DialogTitle>
                            <DialogDescription className="font-monologue-mono">Select a service and configure your credentials</DialogDescription>
                        </DialogHeader>

                        <div className="space-y-6 py-4">
                            {/* Service Selector */}
                            <div className="space-y-2">
                                <label className="font-monologue-mono text-sm font-medium text-gray-900 dark:text-white">
                                    Select Integration Type <span className="text-red-500">*</span>
                                </label>
                                <Select value={selectedService} onValueChange={setSelectedService}>
                                    <SelectTrigger className="font-monologue-mono">
                                        <SelectValue placeholder="Choose a service..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {availableServices.length === 0 ? (
                                            <div className="p-2 text-center text-sm text-gray-500">All services are already configured</div>
                                        ) : (
                                            availableServices.map(([service, info]) => (
                                                <SelectItem key={service} value={service} className="font-monologue-mono">
                                                    <div className="flex items-center gap-3">
                                                        <IntegrationIcon service={service} size={20} />
                                                        <div>
                                                            <div className="font-medium">{info.name}</div>
                                                            <div className="text-xs text-gray-500">{info.description}</div>
                                                        </div>
                                                    </div>
                                                </SelectItem>
                                            ))
                                        )}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Dynamic Form - Only shows when service is selected */}
                            {selectedService && (
                                <div className="border-monologue-border-default rounded-lg border p-4">
                                    <IntegrationFormDynamic
                                        type={getIntegrationType(selectedService)}
                                        onSubmit={handleAddIntegration}
                                        submitLabel="Add Integration"
                                        organizations={[]}
                                    />
                                </div>
                            )}

                            {!selectedService && (
                                <div className="bg-monologue-brand-primary/5 dark:bg-monologue-brand-primary/10 border-monologue-brand-primary/10 dark:border-monologue-brand-primary/20 flex items-start gap-2 rounded-md border px-4 py-3">
                                    <Info className="text-monologue-brand-primary mt-0.5 h-4 w-4 flex-shrink-0" />
                                    <p className="font-monologue-mono text-xs text-gray-700 dark:text-gray-400">
                                        Select a service above to configure your integration
                                    </p>
                                </div>
                            )}
                        </div>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}
