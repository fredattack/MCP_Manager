import { EmptyState } from '@/components/ui/EmptyState';
import { MonologueButton } from '@/components/ui/MonologueButton';
import { CreateWorkflowModal } from '@/components/workflows/CreateWorkflowModal';
import { WorkflowCard } from '@/components/workflows/WorkflowCard';
import { WorkflowCardSkeletonGrid } from '@/components/workflows/WorkflowCardSkeleton';
import AppLayout from '@/layouts/app-layout';
import { Workflow } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Plus, Search, Workflow as WorkflowIcon } from 'lucide-react';
import { useState } from 'react';

/**
 * Workflows Index Page
 *
 * Main entry point for the workflows interface. Displays all workflows grouped by status.
 */

interface Repository {
    id: number;
    name: string;
    full_name: string;
    language?: string;
    updated_at: string;
    file_count?: number;
}

interface Props {
    workflows: Workflow[];
    repositories?: Repository[];
    isLoading?: boolean;
}

export default function WorkflowsIndex({ workflows, repositories = [], isLoading = false }: Props) {
    const [searchQuery, setSearchQuery] = useState('');
    const [showCreateModal, setShowCreateModal] = useState(false);

    const filteredWorkflows = workflows.filter(
        (w) =>
            w.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            (w.description && w.description.toLowerCase().includes(searchQuery.toLowerCase())),
    );

    const groupedWorkflows = {
        running: filteredWorkflows.filter((w) => w.status === 'running'),
        completed: filteredWorkflows.filter((w) => w.status === 'completed'),
        failed: filteredWorkflows.filter((w) => w.status === 'failed'),
        pending: filteredWorkflows.filter((w) => w.status === 'pending'),
    };

    const handleCreateWorkflow = () => {
        setShowCreateModal(true);
    };

    return (
        <AppLayout>
            <Head title="Workflows" />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="font-monologue-serif mb-2 text-4xl text-gray-100">Workflows</h1>
                        <p className="text-lg text-gray-400">Ship code 10x faster with AI agents</p>
                    </div>

                    {workflows.length > 0 && (
                        <MonologueButton
                            variant="primary"
                            size="lg"
                            onClick={handleCreateWorkflow}
                            leftIcon={<Plus size={20} />}
                            className="hidden sm:flex"
                        >
                            Create Workflow
                        </MonologueButton>
                    )}
                </div>

                {/* Search */}
                {workflows.length >= 5 && (
                    <div className="relative mb-8">
                        <Search className="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500" size={20} />
                        <input
                            type="text"
                            placeholder="Search workflows..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="bg-monologue-neutral-800 border-monologue-border-default w-full rounded-lg border py-3 pr-4 pl-12 text-gray-200 placeholder-gray-500 transition-all focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                        />
                    </div>
                )}

                {/* Workflows List or Empty State */}
                {isLoading ? (
                    <WorkflowCardSkeletonGrid count={6} />
                ) : filteredWorkflows.length === 0 && searchQuery === '' ? (
                    <EmptyState
                        title="No workflows yet"
                        description="Create your first workflow to start shipping code faster with AI agents. Describe what you want to build in plain English, and watch AI generate, test, and deploy code autonomously."
                        icon={WorkflowIcon}
                        action={{
                            label: 'Create Your First Workflow',
                            onClick: handleCreateWorkflow,
                        }}
                    />
                ) : filteredWorkflows.length === 0 ? (
                    <div className="py-16 text-center">
                        <p className="text-lg text-gray-400">No workflows found matching "{searchQuery}"</p>
                    </div>
                ) : (
                    <div className="space-y-8">
                        {/* Running workflows */}
                        {groupedWorkflows.running.length > 0 && (
                            <section>
                                <h2 className="font-monologue-serif mb-4 text-xl text-gray-300">Running ({groupedWorkflows.running.length})</h2>
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                    {groupedWorkflows.running.map((workflow) => (
                                        <WorkflowCard
                                            key={workflow.id}
                                            workflow={workflow}
                                            onClick={() => router.visit(`/workflows/${workflow.id}`)}
                                        />
                                    ))}
                                </div>
                            </section>
                        )}

                        {/* Pending workflows */}
                        {groupedWorkflows.pending.length > 0 && (
                            <section>
                                <h2 className="font-monologue-serif mb-4 text-xl text-gray-300">Pending ({groupedWorkflows.pending.length})</h2>
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                    {groupedWorkflows.pending.map((workflow) => (
                                        <WorkflowCard
                                            key={workflow.id}
                                            workflow={workflow}
                                            onClick={() => router.visit(`/workflows/${workflow.id}`)}
                                        />
                                    ))}
                                </div>
                            </section>
                        )}

                        {/* Completed workflows */}
                        {groupedWorkflows.completed.length > 0 && (
                            <section>
                                <h2 className="font-monologue-serif mb-4 text-xl text-gray-300">Completed ({groupedWorkflows.completed.length})</h2>
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                    {groupedWorkflows.completed.map((workflow) => (
                                        <WorkflowCard
                                            key={workflow.id}
                                            workflow={workflow}
                                            onClick={() => router.visit(`/workflows/${workflow.id}`)}
                                        />
                                    ))}
                                </div>
                            </section>
                        )}

                        {/* Failed workflows */}
                        {groupedWorkflows.failed.length > 0 && (
                            <section>
                                <h2 className="font-monologue-serif mb-4 text-xl text-gray-300">Failed ({groupedWorkflows.failed.length})</h2>
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                    {groupedWorkflows.failed.map((workflow) => (
                                        <WorkflowCard
                                            key={workflow.id}
                                            workflow={workflow}
                                            onClick={() => router.visit(`/workflows/${workflow.id}`)}
                                        />
                                    ))}
                                </div>
                            </section>
                        )}
                    </div>
                )}
            </div>

            {/* FAB (Floating Action Button) for mobile */}
            {workflows.length > 0 && (
                <button
                    onClick={handleCreateWorkflow}
                    className="fixed right-8 bottom-8 z-[999] flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-cyan-600 text-white shadow-2xl transition-all duration-200 hover:scale-105 hover:shadow-cyan-500/50 sm:hidden"
                    aria-label="Create workflow"
                >
                    <Plus size={24} />
                </button>
            )}

            {/* Create Workflow Modal */}
            <CreateWorkflowModal isOpen={showCreateModal} onClose={() => setShowCreateModal(false)} repositories={repositories} />
        </AppLayout>
    );
}
