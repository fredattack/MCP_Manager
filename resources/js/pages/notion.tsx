import apiClient from '@/lib/api/client';
import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { IntegrationForm } from '../components/integrations/integration-form';
import { Button } from '../components/ui/button';
import { Card } from '../components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '../components/ui/dialog';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { useApiToken } from '../hooks/use-api-token';
import { useIntegrations } from '../hooks/use-integrations';
import { IntegrationAccount, IntegrationStatus, IntegrationType } from '../types/integrations';

interface NotionPage {
    id: string;
    title: string;
    url: string;
}

export default function Notion() {
    const [notionPages, setNotionPages] = useState<NotionPage[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [pageId, setPageId] = useState<string>('');
    const [isSetupDialogOpen, setIsSetupDialogOpen] = useState(false);

    const { apiToken } = useApiToken();
    const { integrations, fetchIntegrations, createIntegration, updateIntegration } = useIntegrations();

    const [hasNotionIntegration, setHasNotionIntegration] = useState(false);
    const [notionIntegration, setNotionIntegration] = useState<IntegrationAccount | null>(null);

    useEffect(() => {
        fetchIntegrations().catch(console.error);
    }, [fetchIntegrations]);

    useEffect(() => {
        const notionIntegration = integrations.find(
            (integration) => integration.type === IntegrationType.NOTION && integration.status === IntegrationStatus.ACTIVE,
        );

        setHasNotionIntegration(!!notionIntegration);
        setNotionIntegration(notionIntegration || null);
    }, [integrations]);

    const fetchNotionPages = async () => {
        if (!hasNotionIntegration) {
            setError('No active Notion integration found. Please set up your Notion integration first.');
            return;
        }

        setLoading(true);
        setError(null);

        try {
            // Include page_id as a query parameter if provided
            const params = pageId ? { page_id: pageId } : {};
            const headers = apiToken ? { Authorization: `Bearer ${apiToken}` } : {};
            const response = await apiClient.get('/api/notion/pages-tree', {
                params,
                headers,
            });
            setNotionPages(response.data);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Failed to fetch Notion pages');
        } finally {
            setLoading(false);
        }
    };

    const handleSetupNotion = async (data: { type: string; access_token: string }) => {
        try {
            if (notionIntegration) {
                await updateIntegration(notionIntegration.id, { access_token: data.access_token });
            } else {
                await createIntegration({
                    type: IntegrationType.NOTION,
                    access_token: data.access_token,
                });
            }
            setIsSetupDialogOpen(false);
        } catch (err) {
            console.error('Failed to setup Notion integration:', err);
        }
    };

    return (
        <>
            <Head title="Notion Pages" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <div className="mb-6 flex items-center justify-between">
                                <h1 className="text-2xl font-semibold">Notion Pages</h1>

                                <Dialog open={isSetupDialogOpen} onOpenChange={setIsSetupDialogOpen}>
                                    <DialogTrigger asChild>
                                        <Button variant={hasNotionIntegration ? 'outline' : 'default'}>
                                            {hasNotionIntegration ? 'Update Notion Integration' : 'Setup Notion Integration'}
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>
                                                {hasNotionIntegration ? 'Update Notion Integration' : 'Setup Notion Integration'}
                                            </DialogTitle>
                                        </DialogHeader>
                                        <IntegrationForm
                                            type={IntegrationType.NOTION}
                                            initialValues={{
                                                access_token: notionIntegration?.access_token || '',
                                            }}
                                            onSubmit={handleSetupNotion}
                                            submitLabel={hasNotionIntegration ? 'Update Integration' : 'Setup Integration'}
                                        />
                                    </DialogContent>
                                </Dialog>
                            </div>

                            {!hasNotionIntegration ? (
                                <Card className="p-6 text-center">
                                    <h2 className="text-lg font-medium">Notion Integration Not Set Up</h2>
                                    <p className="mt-2 text-gray-500 dark:text-gray-400">
                                        You need to set up your Notion integration before you can fetch pages.
                                    </p>
                                    <Button className="mt-4" onClick={() => setIsSetupDialogOpen(true)}>
                                        Setup Notion Integration
                                    </Button>
                                </Card>
                            ) : (
                                <>
                                    <div className="mb-4 space-y-2">
                                        <Label htmlFor="pageId">Page ID (optional - uses default if not provided)</Label>
                                        <Input
                                            id="pageId"
                                            value={pageId}
                                            onChange={(e) => setPageId(e.target.value)}
                                            placeholder="Enter Notion page ID"
                                        />
                                    </div>

                                    <Button onClick={fetchNotionPages} disabled={loading}>
                                        {loading ? 'Loading...' : 'Fetch Notion Pages'}
                                    </Button>

                                    {error && (
                                        <div className="mt-4 rounded-md bg-red-50 p-4 text-red-600 dark:bg-red-900/20 dark:text-red-400">{error}</div>
                                    )}

                                    {notionPages.length > 0 && (
                                        <div className="mt-6">
                                            <h2 className="mb-4 text-xl font-semibold">Results</h2>
                                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                                {notionPages.map((page) => (
                                                    <Card key={page.id} className="overflow-hidden">
                                                        <div className="p-4">
                                                            <h3 className="font-medium">{page.title}</h3>
                                                            <p className="text-sm text-gray-500 dark:text-gray-400">ID: {page.id}</p>
                                                            <a
                                                                href={page.url}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="mt-2 inline-block text-blue-500 hover:underline"
                                                            >
                                                                View Page
                                                            </a>
                                                        </div>
                                                    </Card>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
