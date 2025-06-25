import React from 'react';
import { INTEGRATION_TYPES, IntegrationType } from '../../types/integrations';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';

interface IntegrationFormProps {
    type: string;
    initialValues?: {
        access_token: string;
        meta?: Record<string, unknown> | null;
    };
    onSubmit: (data: { type: string; access_token: string; meta?: Record<string, unknown> }) => Promise<void>;
    submitLabel?: string;
}

export function IntegrationForm({ type, initialValues = { access_token: '' }, onSubmit, submitLabel = 'Save' }: IntegrationFormProps) {
    const [accessToken, setAccessToken] = React.useState(initialValues.access_token);
    const [isLoading, setIsLoading] = React.useState(false);
    const [error, setError] = React.useState<string | null>(null);

    const integrationType = INTEGRATION_TYPES[type];

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setError(null);

        try {
            await onSubmit({
                type,
                access_token: accessToken,
                meta: initialValues.meta || undefined,
            });
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An error occurred');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
                <Label htmlFor="integration-type">Integration Type</Label>
                <Input id="integration-type" value={integrationType?.displayName || type} disabled className="bg-gray-50 dark:bg-gray-800" />
            </div>

            <div className="space-y-2">
                <Label htmlFor="access-token">Access Token</Label>
                <Input
                    id="access-token"
                    type="password"
                    value={accessToken}
                    onChange={(e) => setAccessToken(e.target.value)}
                    placeholder="Enter your access token"
                    required
                />
                <p className="text-xs text-gray-500 dark:text-gray-400">
                    {type === IntegrationType.NOTION
                        ? 'Enter your Notion API token. You can find this in your Notion integrations settings.'
                        : type === IntegrationType.GMAIL
                          ? 'Enter your Gmail API token. You can find this in your Google Cloud Console.'
                          : 'Enter your API token for this integration.'}
                </p>
            </div>

            {error && <div className="rounded-md bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">{error}</div>}

            <div className="flex justify-end">
                <Button type="submit" disabled={isLoading}>
                    {isLoading ? 'Saving...' : submitLabel}
                </Button>
            </div>
        </form>
    );
}
