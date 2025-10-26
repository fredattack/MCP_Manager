import { MonologueButton } from '@/components/ui/MonologueButton';
import { MonologueCard } from '@/components/ui/MonologueCard';
import { StatusBadge } from '@/components/ui/StatusBadge';
import { ConnectionStatus } from '@/components/workflows/ConnectionStatus';
import { LiveLogViewer } from '@/components/workflows/LiveLogViewer';
import { WorkflowExecutionStatus } from '@/components/workflows/WorkflowExecutionStatus';
import { useWorkflowUpdates } from '@/hooks/use-workflow-updates';
import AppLayout from '@/layouts/app-layout';
import { Workflow, WorkflowExecution, WorkflowStep } from '@/types';
import { Head, router } from '@inertiajs/react';
import { CheckCircle2, ChevronLeft, Clock, Pencil, RotateCw, Trash2, XCircle } from 'lucide-react';
import { useState } from 'react';

/**
 * Workflow Detail Page
 *
 * Displays detailed information about a single workflow including:
 * - Header with status and actions
 * - Progress timeline (for running workflows)
 * - Results summary (for completed workflows)
 * - Error information (for failed workflows)
 */

interface Props {
    workflow: Workflow;
}

export default function WorkflowShow({ workflow }: Props) {
    const [localExecution, setLocalExecution] = useState<WorkflowExecution | undefined>(workflow.latest_execution);
    const [localSteps, setLocalSteps] = useState<WorkflowStep[]>(workflow.latest_execution?.steps || []);

    // Real-time updates via WebSockets
    const { connectionStatus, logs, reconnect } = useWorkflowUpdates(workflow.id, {
        onStatusUpdate: (execution) => {
            setLocalExecution(execution);
        },
        onStepComplete: (step) => {
            setLocalSteps((prev) => {
                const index = prev.findIndex((s) => s.id === step.id);
                if (index >= 0) {
                    const updated = [...prev];
                    updated[index] = step;
                    return updated;
                }
                return [...prev, step];
            });
        },
    });

    const handleRerun = () => {
        router.post(
            `/api/workflows/${workflow.id}/rerun`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['workflow'] });
                },
            },
        );
    };

    const handleCancel = () => {
        if (confirm('Are you sure you want to cancel this workflow execution?')) {
            router.post(
                `/api/workflows/${workflow.id}/cancel`,
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.reload({ only: ['workflow'] });
                    },
                },
            );
        }
    };

    const handleEdit = () => {
        router.visit(`/workflows/${workflow.id}/edit`);
    };

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this workflow? This action cannot be undone.')) {
            router.delete(`/api/workflows/${workflow.id}`, {
                onSuccess: () => router.visit('/workflows'),
            });
        }
    };

    const formatDuration = (seconds?: number): string => {
        if (!seconds) {
            return 'N/A';
        }

        if (seconds < 60) {
            return `${seconds}s`;
        }

        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}m ${remainingSeconds}s`;
    };

    const formatDateTime = (dateString?: string): string => {
        if (!dateString) {
            return 'N/A';
        }

        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const latestExecution = localExecution || workflow.latest_execution;
    const steps = localSteps;
    const isRunning = workflow.status === 'running';
    const isCompleted = workflow.status === 'completed';
    const isFailed = workflow.status === 'failed';

    return (
        <AppLayout>
            <Head title={workflow.name} />

            <div className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
                {/* Breadcrumb */}
                <button
                    onClick={() => router.visit('/workflows')}
                    className="mb-6 inline-flex items-center gap-2 text-gray-400 transition-colors hover:text-cyan-500"
                >
                    <ChevronLeft size={20} />
                    <span>Back to Workflows</span>
                </button>

                {/* Header */}
                <div className="mb-8">
                    <div className="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div className="flex-1">
                            <div className="mb-3 flex items-center gap-3">
                                <StatusBadge status={workflow.status} size="lg" />
                            </div>
                            <h1 className="font-monologue-serif mb-3 text-3xl leading-tight text-gray-100 sm:text-4xl">
                                {workflow.description || workflow.name}
                            </h1>
                            <p className="text-base text-gray-400">{workflow.name}</p>
                        </div>

                        {/* Action buttons */}
                        <div className="flex flex-shrink-0 items-center gap-2">
                            {isRunning ? (
                                <MonologueButton
                                    variant="secondary"
                                    size="md"
                                    onClick={handleCancel}
                                    leftIcon={<XCircle size={16} />}
                                    className="text-red-500 hover:text-red-400"
                                >
                                    Cancel
                                </MonologueButton>
                            ) : (
                                <MonologueButton variant="secondary" size="md" onClick={handleRerun} leftIcon={<RotateCw size={16} />}>
                                    Re-run
                                </MonologueButton>
                            )}
                            <MonologueButton variant="ghost" size="md" onClick={handleEdit} disabled={isRunning}>
                                <Pencil size={16} />
                            </MonologueButton>
                            <MonologueButton
                                variant="ghost"
                                size="md"
                                onClick={handleDelete}
                                className="text-red-500 hover:bg-red-500/10 hover:text-red-400"
                            >
                                <Trash2 size={16} />
                            </MonologueButton>
                        </div>
                    </div>

                    {/* Metadata */}
                    <div className="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span className="flex items-center gap-1.5">
                            <Clock size={14} />
                            Created {formatDateTime(workflow.created_at)}
                        </span>
                        {workflow.duration && (
                            <span className="flex items-center gap-1.5">
                                <CheckCircle2 size={14} />
                                Duration: {formatDuration(workflow.duration)}
                            </span>
                        )}
                    </div>
                </div>

                {/* Main Content */}
                <div className="space-y-6">
                    {/* Connection Status (only show when not connected) */}
                    {!connectionStatus.isConnected && isRunning && (
                        <ConnectionStatus
                            isConnected={connectionStatus.isConnected}
                            isConnecting={connectionStatus.isConnecting}
                            error={connectionStatus.error}
                            onReconnect={reconnect}
                        />
                    )}

                    {/* Progress Timeline (for running workflows) */}
                    {isRunning && steps.length > 0 && (
                        <MonologueCard>
                            <MonologueCard.Header>Workflow Progress</MonologueCard.Header>
                            <MonologueCard.Body>
                                <WorkflowExecutionStatus steps={steps} currentStepId={steps.find((s) => s.status === 'running')?.id} />
                            </MonologueCard.Body>
                        </MonologueCard>
                    )}

                    {/* Live Logs (for running workflows) */}
                    {isRunning && <LiveLogViewer logs={logs} isLive={connectionStatus.isConnected} />}

                    {/* Historical Logs (for completed/failed workflows) */}
                    {(isCompleted || isFailed) && logs.length > 0 && <LiveLogViewer logs={logs} isLive={false} />}

                    {/* Completed Summary */}
                    {isCompleted && (
                        <>
                            <MonologueCard variant="elevated">
                                <MonologueCard.Header className="text-green-500">Workflow Completed Successfully</MonologueCard.Header>
                                <MonologueCard.Body>
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between">
                                            <span className="text-gray-400">Total Duration</span>
                                            <span className="font-medium text-gray-200">{formatDuration(workflow.duration)}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-gray-400">Completed At</span>
                                            <span className="font-medium text-gray-200">{formatDateTime(workflow.completed_at)}</span>
                                        </div>
                                        {steps.length > 0 && (
                                            <div className="flex items-center justify-between">
                                                <span className="text-gray-400">Steps Completed</span>
                                                <span className="font-medium text-gray-200">
                                                    {steps.filter((s) => s.status === 'completed').length}/{steps.length}
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                </MonologueCard.Body>
                            </MonologueCard>

                            {steps.length > 0 && (
                                <MonologueCard>
                                    <MonologueCard.Header>Execution Timeline</MonologueCard.Header>
                                    <MonologueCard.Body>
                                        <WorkflowExecutionStatus steps={steps} />
                                    </MonologueCard.Body>
                                </MonologueCard>
                            )}
                        </>
                    )}

                    {/* Failed Summary */}
                    {isFailed && (
                        <>
                            <MonologueCard variant="elevated">
                                <MonologueCard.Header className="text-red-500">Workflow Failed</MonologueCard.Header>
                                <MonologueCard.Body>
                                    <p className="mb-4 text-gray-300">
                                        The workflow encountered an error during execution. Review the steps below to identify the issue.
                                    </p>
                                    {latestExecution?.result && 'error' in latestExecution.result && (
                                        <div className="rounded border border-red-500/30 bg-red-500/10 p-4">
                                            <p className="font-mono text-sm text-red-400">{String(latestExecution.result.error)}</p>
                                        </div>
                                    )}
                                </MonologueCard.Body>
                                <MonologueCard.Footer>
                                    <MonologueButton variant="primary" onClick={handleRerun} leftIcon={<RotateCw size={16} />}>
                                        Retry Workflow
                                    </MonologueButton>
                                </MonologueCard.Footer>
                            </MonologueCard>

                            {steps.length > 0 && (
                                <MonologueCard>
                                    <MonologueCard.Header>Execution Timeline</MonologueCard.Header>
                                    <MonologueCard.Body>
                                        <WorkflowExecutionStatus steps={steps} />
                                    </MonologueCard.Body>
                                </MonologueCard>
                            )}
                        </>
                    )}

                    {/* Pending State */}
                    {workflow.status === 'pending' && (
                        <MonologueCard>
                            <MonologueCard.Header>Workflow Pending</MonologueCard.Header>
                            <MonologueCard.Body>
                                <p className="text-gray-400">
                                    This workflow is queued and will start executing soon. You will see live progress updates once execution begins.
                                </p>
                            </MonologueCard.Body>
                        </MonologueCard>
                    )}

                    {/* Configuration Details */}
                    {workflow.config && Object.keys(workflow.config).length > 0 && (
                        <MonologueCard>
                            <MonologueCard.Header>Configuration</MonologueCard.Header>
                            <MonologueCard.Body>
                                <dl className="space-y-2">
                                    {Object.entries(workflow.config).map(([key, value]) => (
                                        <div key={key} className="flex items-start justify-between">
                                            <dt className="text-gray-400 capitalize">{key.replace(/_/g, ' ')}</dt>
                                            <dd className="font-mono text-sm text-gray-200">
                                                {typeof value === 'object' ? JSON.stringify(value) : String(value)}
                                            </dd>
                                        </div>
                                    ))}
                                </dl>
                            </MonologueCard.Body>
                        </MonologueCard>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
