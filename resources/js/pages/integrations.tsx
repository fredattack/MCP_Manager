import { MonologueButton } from '@/components/ui/MonologueButton';
import { MonologueCard } from '@/components/ui/MonologueCard';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Plug, Plus } from 'lucide-react';
import { IntegrationList } from '../components/integrations/integration-list';

export default function Integrations() {
    return (
        <AppLayout>
            <Head title="Integrations" />

            <div className="mx-auto max-w-7xl p-6">
                {/* Header with Monologue Typography */}
                <div className="mb-8 flex items-center justify-between">
                    <div>
                        <h1 className="flex items-center gap-3 font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">
                            <Plug className="h-8 w-8 text-gray-900 dark:text-monologue-brand-primary" />
                            Integrations
                        </h1>
                        <p className="mt-2 font-monologue-mono text-sm tracking-wide text-gray-600 dark:text-gray-400">
                            Connect and manage your service integrations
                        </p>
                    </div>

                    <MonologueButton variant="primary" leftIcon={<Plus className="h-4 w-4" />}>
                        Browse Integrations
                    </MonologueButton>
                </div>

                {/* Integration List in Monologue Card */}
                <MonologueCard variant="elevated" className="border-monologue-border-strong" padding="lg">
                    <h2 className="mb-6 font-monologue-serif text-2xl font-normal tracking-tight text-gray-900 dark:text-white">
                        Connected Services
                    </h2>
                    <IntegrationList />
                </MonologueCard>
            </div>
        </AppLayout>
    );
}
