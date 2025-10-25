import React from 'react';
import { useIntegrations } from '../../hooks/use-integrations';
import { INTEGRATION_TYPES, IntegrationAccount, IntegrationStatus, IntegrationType } from '../../types/integrations';
import { Button } from '../ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '../ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { IntegrationCard } from './integration-card';
import { IntegrationCardEnhanced } from './integration-card-enhanced';
import { IntegrationForm } from './integration-form';

export function IntegrationList() {
    const { integrations, loading, error, fetchIntegrations, createIntegration, updateIntegration, deleteIntegration } = useIntegrations();

    const [isAddDialogOpen, setIsAddDialogOpen] = React.useState(false);
    const [selectedType, setSelectedType] = React.useState<string>(IntegrationType.NOTION);

    React.useEffect(() => {
        fetchIntegrations().catch(console.error);
    }, [fetchIntegrations]);

    const handleAddIntegration = async (data: { type: string; access_token: string; meta?: Record<string, unknown> }) => {
        await createIntegration({
            type: data.type as IntegrationType,
            access_token: data.access_token,
            meta: data.meta,
        });
        setIsAddDialogOpen(false);
    };

    const handleUpdateIntegration = async (id: number, data: Partial<IntegrationAccount>) => {
        // Convert the data to match the expected type for updateIntegration
        const updateData = {
            access_token: data.access_token,
            meta: data.meta || undefined,
            status: data.status as IntegrationStatus | undefined,
        };
        await updateIntegration(id, updateData);
    };

    const handleDeleteIntegration = async (id: number) => {
        await deleteIntegration(id);
    };
    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <h2 className="text-2xl font-bold">Integrations</h2>
                <Dialog open={isAddDialogOpen} onOpenChange={setIsAddDialogOpen}>
                    <DialogTrigger asChild>
                        <Button>Add Integration</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Add New Integration</DialogTitle>
                        </DialogHeader>
                        <div className="py-4">
                            <div className="mb-4 space-y-2">
                                <label htmlFor="integration-type" className="text-sm font-medium">
                                    Integration Type
                                </label>
                                <Select value={selectedType} onValueChange={setSelectedType}>
                                    <SelectTrigger id="integration-type">
                                        <SelectValue placeholder="Select integration type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.values(INTEGRATION_TYPES).map((type) => (
                                            <SelectItem key={type.value} value={type.value}>
                                                {type.displayName}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <IntegrationForm type={selectedType} onSubmit={handleAddIntegration} submitLabel="Add Integration" />
                        </div>
                    </DialogContent>
                </Dialog>
            </div>

            {loading && <div className="text-center">Loading integrations...</div>}

            {error && (
                <div className="rounded-md bg-red-50 p-4 text-red-600 dark:bg-red-900/20 dark:text-red-400">
                    Error loading integrations: {error.message}
                </div>
            )}

            {!loading && !error && integrations.length === 0 && (
                <div className="rounded-md bg-gray-50 p-8 text-center dark:bg-gray-800">
                    <h3 className="text-lg font-medium">No integrations found</h3>
                    <p className="mt-1 text-gray-500 dark:text-gray-400">Add your first integration to connect with external services.</p>
                    <Button className="mt-4" onClick={() => setIsAddDialogOpen(true)}>
                        Add Integration
                    </Button>
                </div>
            )}

            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                {integrations.map((integration) => (
                    <IntegrationCardEnhanced
                        key={integration.id}
                        integration={integration}
                        onUpdate={handleUpdateIntegration}
                        onDelete={handleDeleteIntegration}
                    />
                ))}
            </div>
        </div>
    );
}
