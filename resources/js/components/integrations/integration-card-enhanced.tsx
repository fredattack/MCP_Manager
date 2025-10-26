import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { CheckCircle2, Info, Power, Settings, Trash2 } from 'lucide-react';
import React from 'react';
import { INTEGRATION_TYPES, IntegrationAccount, IntegrationStatus, IntegrationType } from '../../types/integrations';
import { MonologueBadge } from '../ui/MonologueBadge';
import { MonologueButton } from '../ui/MonologueButton';
import { MonologueCard } from '../ui/MonologueCard';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '../ui/dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '../ui/tooltip';
import { IntegrationForm } from './integration-form';

interface IntegrationCardEnhancedProps {
    integration: IntegrationAccount;
    onUpdate: (id: number, data: Partial<IntegrationAccount>) => Promise<void>;
    onDelete: (id: number) => Promise<void>;
}

export function IntegrationCardEnhanced({ integration, onUpdate, onDelete }: IntegrationCardEnhancedProps) {
    const [isUpdateDialogOpen, setIsUpdateDialogOpen] = React.useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = React.useState(false);
    const [isLoading, setIsLoading] = React.useState(false);

    const integrationType = INTEGRATION_TYPES[integration.type];
    const isActive = integration.status === IntegrationStatus.ACTIVE;
    const isConfigured = integration.access_token && integration.access_token.length > 0;

    const handleStatusToggle = async () => {
        setIsLoading(true);
        try {
            await onUpdate(integration.id, {
                status: isActive ? IntegrationStatus.INACTIVE : IntegrationStatus.ACTIVE,
            });
        } finally {
            setIsLoading(false);
        }
    };

    const handleDelete = async () => {
        setIsLoading(true);
        try {
            await onDelete(integration.id);
            setIsDeleteDialogOpen(false);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <MonologueCard
            variant={isActive ? 'elevated' : 'default'}
            className={cn(
                'group duration-fast relative overflow-hidden transition-all',
                isActive && 'border-monologue-brand-primary/20',
                !isActive && 'opacity-90 hover:opacity-100',
            )}
        >
            {/* Active indicator - subtle left border accent */}
            {isActive && <div className="from-monologue-brand-primary to-monologue-brand-accent absolute top-0 left-0 h-full w-1 bg-gradient-to-b" />}

            <div className="p-6 pl-7">
                {/* Header Section */}
                <div className="flex items-start justify-between">
                    <div className="flex items-center space-x-4">
                        {/* Icon with enhanced styling */}
                        <div
                            className={cn(
                                'duration-fast flex h-12 w-12 items-center justify-center rounded-lg transition-all',
                                isActive
                                    ? 'bg-monologue-brand-primary/10 text-monologue-brand-primary'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            )}
                        >
                            <span className="font-monologue-serif text-xl font-medium">{integrationType?.displayName.charAt(0)}</span>
                        </div>

                        {/* Title & Description */}
                        <div className="flex-1">
                            <div className="flex items-center gap-2">
                                <h3 className="font-monologue-serif text-lg font-medium tracking-tight text-gray-900 dark:text-gray-100">
                                    {integrationType?.displayName}
                                </h3>
                                {isActive && <CheckCircle2 className="text-monologue-brand-success h-4 w-4" />}
                            </div>
                            <p className="font-monologue-mono mt-0.5 text-xs tracking-wide text-gray-500 dark:text-gray-400">
                                {integrationType?.description}
                            </p>
                        </div>
                    </div>

                    {/* Status Badge */}
                    <MonologueBadge variant={isActive ? 'success' : isConfigured ? 'default' : 'muted'} size="md">
                        {isActive ? 'Active' : isConfigured ? 'Configured' : 'Not Configured'}
                    </MonologueBadge>
                </div>

                {/* Info Banner for Active Integrations */}
                {isActive && integration.meta && (
                    <div className="bg-monologue-brand-primary/5 border-monologue-brand-primary/10 mt-4 flex items-start gap-2 rounded-md border px-3 py-2">
                        <Info className="text-monologue-brand-primary mt-0.5 h-4 w-4 flex-shrink-0" />
                        <div className="flex-1">
                            <p className="font-monologue-mono text-xs text-gray-700 dark:text-gray-300">
                                Your integrations are managed through the MCP server at your production server
                            </p>
                        </div>
                    </div>
                )}

                {/* Actions Section */}
                <div className="mt-6 flex items-center gap-2">
                    {/* Configure Button */}
                    {integration.type === IntegrationType.TODOIST ? (
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <Link href={route('integrations.todoist.setup')}>
                                        <MonologueButton
                                            variant={isActive ? 'ghost' : 'primary'}
                                            size="sm"
                                            leftIcon={<Settings className="h-3.5 w-3.5" />}
                                        >
                                            Configure
                                        </MonologueButton>
                                    </Link>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p className="font-monologue-mono text-xs">Configure {integrationType?.displayName} settings</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    ) : (
                        <Dialog open={isUpdateDialogOpen} onOpenChange={setIsUpdateDialogOpen}>
                            <DialogTrigger asChild>
                                <MonologueButton variant={isActive ? 'ghost' : 'primary'} size="sm" leftIcon={<Settings className="h-3.5 w-3.5" />}>
                                    {isConfigured ? 'Edit' : 'Configure'}
                                </MonologueButton>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle className="font-monologue-serif text-xl">
                                        Update {integrationType?.displayName} Integration
                                    </DialogTitle>
                                </DialogHeader>
                                <IntegrationForm
                                    type={integration.type}
                                    initialValues={{
                                        access_token: integration.access_token,
                                        meta: integration.meta,
                                    }}
                                    onSubmit={async (data) => {
                                        await onUpdate(integration.id, data);
                                        setIsUpdateDialogOpen(false);
                                    }}
                                    submitLabel="Update Integration"
                                />
                            </DialogContent>
                        </Dialog>
                    )}

                    {/* Activate/Deactivate Toggle */}
                    {isConfigured && (
                        <MonologueButton
                            variant={isActive ? 'secondary' : 'ghost'}
                            size="sm"
                            onClick={handleStatusToggle}
                            disabled={isLoading}
                            leftIcon={<Power className="h-3.5 w-3.5" />}
                        >
                            {isActive ? 'Deactivate' : 'Activate'}
                        </MonologueButton>
                    )}

                    {/* Delete Button */}
                    <div className="ml-auto">
                        <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                            <DialogTrigger asChild>
                                <MonologueButton
                                    variant="ghost"
                                    size="sm"
                                    className="text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/20"
                                    leftIcon={<Trash2 className="h-3.5 w-3.5" />}
                                >
                                    Delete
                                </MonologueButton>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle className="font-monologue-serif text-xl">Delete Integration</DialogTitle>
                                </DialogHeader>
                                <div className="py-4">
                                    <p className="font-monologue-mono text-sm text-gray-700 dark:text-gray-300">
                                        Are you sure you want to delete this integration? This action cannot be undone.
                                    </p>
                                </div>
                                <div className="flex justify-end space-x-2">
                                    <MonologueButton variant="ghost" onClick={() => setIsDeleteDialogOpen(false)}>
                                        Cancel
                                    </MonologueButton>
                                    <MonologueButton
                                        variant="primary"
                                        onClick={handleDelete}
                                        disabled={isLoading}
                                        className="bg-red-600 text-white hover:bg-red-700"
                                    >
                                        {isLoading ? 'Deleting...' : 'Delete'}
                                    </MonologueButton>
                                </div>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>
            </div>
        </MonologueCard>
    );
}
