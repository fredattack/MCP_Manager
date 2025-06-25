import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Plug, Plus } from 'lucide-react';
import { IntegrationList } from '../components/integrations/integration-list';

export default function Integrations() {
    return (
        <AppLayout>
            <Head title="Integrations" />

            <div className="mx-auto max-w-7xl p-6">
                {/* Header */}
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            <Plug className="h-6 w-6" />
                            Integrations
                        </h1>
                        <p className="mt-1 text-gray-600 dark:text-gray-400">Connect your favorite tools and services to streamline your workflow</p>
                    </div>

                    <Button>
                        <Plus className="mr-2 h-4 w-4" />
                        Browse Integrations
                    </Button>
                </div>

                {/* Integration List */}
                <Card>
                    <CardHeader>
                        <CardTitle>Connected Services</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <IntegrationList />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
