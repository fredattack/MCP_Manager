import { MonologueCard } from '@/components/ui/MonologueCard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Activity, Server, Zap } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                {/* Header with Monologue Typography */}
                <div className="mb-2">
                    <h1 className="font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">Dashboard</h1>
                    <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">
                        Monitor your MCP integrations and system status
                    </p>
                </div>

                {/* Metrics Cards Grid - Monologue Design */}
                <div className="grid auto-rows-min gap-6 md:grid-cols-3">
                    <MonologueCard variant="elevated" className="border-monologue-border-strong">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                    Active Integrations
                                </p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">1</p>
                            </div>
                            <div className="bg-monologue-brand-primary/10 rounded-lg p-3">
                                <Activity className="text-monologue-brand-primary h-6 w-6" />
                            </div>
                        </div>
                        <p className="font-monologue-mono mt-3 text-xs text-gray-600 dark:text-gray-400">Todoist connected</p>
                    </MonologueCard>

                    <MonologueCard variant="elevated" className="border-monologue-border-strong">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">MCP Servers</p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">1</p>
                            </div>
                            <div className="bg-monologue-brand-success/10 rounded-lg p-3">
                                <Server className="text-monologue-brand-success h-6 w-6" />
                            </div>
                        </div>
                        <p className="font-monologue-mono mt-3 text-xs text-gray-600 dark:text-gray-400">Localhost:9978</p>
                    </MonologueCard>

                    <MonologueCard variant="elevated" className="border-monologue-border-strong">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">System Status</p>
                                <p className="font-monologue-serif text-monologue-brand-success mt-2 text-3xl font-normal">Online</p>
                            </div>
                            <div className="bg-monologue-brand-success/10 rounded-lg p-3">
                                <Zap className="text-monologue-brand-success h-6 w-6" />
                            </div>
                        </div>
                        <p className="font-monologue-mono mt-3 text-xs text-gray-600 dark:text-gray-400">All systems operational</p>
                    </MonologueCard>
                </div>

                {/* Main Content Card */}
                <MonologueCard variant="elevated" className="border-monologue-border-strong relative min-h-[50vh] flex-1 md:min-h-min">
                    <h2 className="font-monologue-serif mb-4 text-2xl font-normal tracking-tight text-gray-900 dark:text-white">
                        Welcome to MCP Manager
                    </h2>
                    <p className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                        This is your central hub for managing Model Context Protocol integrations.
                    </p>
                    <div className="font-monologue-mono mt-6 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <p>✓ Configure service integrations (Notion, Todoist, Jira, etc.)</p>
                        <p>✓ Monitor MCP server connections</p>
                        <p>✓ Manage AI integrations (Claude, OpenAI, Mistral)</p>
                        <p>✓ Track integration health and status</p>
                    </div>
                </MonologueCard>
            </div>
        </AppLayout>
    );
}
