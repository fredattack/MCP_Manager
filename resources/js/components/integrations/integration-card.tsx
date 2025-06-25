import React from 'react';
import { INTEGRATION_TYPES, IntegrationAccount, IntegrationStatus } from '../../types/integrations';
import { Badge } from '../ui/badge';
import { Button } from '../ui/button';
import { Card } from '../ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '../ui/dialog';
import { IntegrationForm } from './integration-form';

interface IntegrationCardProps {
    integration: IntegrationAccount;
    onUpdate: (id: number, data: Partial<IntegrationAccount>) => Promise<void>;
    onDelete: (id: number) => Promise<void>;
}

export function IntegrationCard({ integration, onUpdate, onDelete }: IntegrationCardProps) {
    const [isUpdateDialogOpen, setIsUpdateDialogOpen] = React.useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = React.useState(false);
    const [isLoading, setIsLoading] = React.useState(false);

    const integrationType = INTEGRATION_TYPES[integration.type];
    const isActive = integration.status === IntegrationStatus.ACTIVE;

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
        <Card className="overflow-hidden">
            <div className="p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <span className="text-lg font-semibold">{integrationType?.displayName.charAt(0)}</span>
                        </div>
                        <div>
                            <h3 className="text-lg font-semibold">{integrationType?.displayName}</h3>
                            <p className="text-sm text-gray-500 dark:text-gray-400">{integrationType?.description}</p>
                        </div>
                    </div>
                    <Badge variant={isActive ? 'default' : 'secondary'}>{isActive ? 'Active' : 'Inactive'}</Badge>
                </div>

                <div className="mt-4 flex space-x-2">
                    <Dialog open={isUpdateDialogOpen} onOpenChange={setIsUpdateDialogOpen}>
                        <DialogTrigger asChild>
                            <Button variant="outline" size="sm">
                                Edit
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Update {integrationType?.displayName} Integration</DialogTitle>
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

                    <Button variant={isActive ? 'destructive' : 'default'} size="sm" onClick={handleStatusToggle} disabled={isLoading}>
                        {isActive ? 'Deactivate' : 'Activate'}
                    </Button>

                    <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                        <DialogTrigger asChild>
                            <Button variant="destructive" size="sm">
                                Delete
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Delete Integration</DialogTitle>
                            </DialogHeader>
                            <div className="py-4">
                                <p>Are you sure you want to delete this integration? This action cannot be undone.</p>
                            </div>
                            <div className="flex justify-end space-x-2">
                                <Button variant="outline" onClick={() => setIsDeleteDialogOpen(false)}>
                                    Cancel
                                </Button>
                                <Button variant="destructive" onClick={handleDelete} disabled={isLoading}>
                                    Delete
                                </Button>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </Card>
    );
}
