import { WorkflowStep } from '@/types';
import { CheckCircle, Clock, Loader2, XCircle } from 'lucide-react';
import * as React from 'react';

/**
 * WorkflowExecutionStatus Component - Progress Timeline
 *
 * Displays a vertical timeline of workflow steps with status indicators
 *
 * @example
 * <WorkflowExecutionStatus
 *   steps={workflow.latest_execution?.steps || []}
 *   currentStepId={currentStep?.id}
 * />
 */

interface WorkflowExecutionStatusProps {
    steps: WorkflowStep[];
    currentStepId?: string;
    className?: string;
}

const WorkflowExecutionStatus = React.forwardRef<HTMLDivElement, WorkflowExecutionStatusProps>(({ steps, currentStepId, className }, ref) => {
    const getDuration = (startedAt?: string, completedAt?: string): string => {
        if (!startedAt) {
            return 'Pending';
        }

        const start = new Date(startedAt);
        const end = completedAt ? new Date(completedAt) : new Date();
        const diffInSeconds = Math.floor((end.getTime() - start.getTime()) / 1000);

        if (diffInSeconds < 1) {
            return '< 1s';
        }

        return `${diffInSeconds}s`;
    };

    return (
        <div ref={ref} className={`space-y-4 ${className}`}>
            {steps.map((step, index) => {
                const isActive = step.id === currentStepId;
                const isLast = index === steps.length - 1;

                return (
                    <div key={step.id} className="flex items-start gap-4">
                        {/* Timeline connector */}
                        <div className="flex flex-col items-center">
                            {/* Step icon */}
                            <div
                                className={`flex h-8 w-8 items-center justify-center rounded-full transition-all duration-200 ${
                                    step.status === 'completed' ? 'bg-green-500/10 text-green-500' : ''
                                } ${step.status === 'running' ? 'animate-pulse bg-cyan-500/10 text-cyan-500' : ''} ${
                                    step.status === 'failed' ? 'bg-red-500/10 text-red-500' : ''
                                } ${step.status === 'pending' || step.status === 'skipped' ? 'bg-gray-700 text-gray-500' : ''} `}
                            >
                                {step.status === 'completed' && <CheckCircle size={20} />}
                                {step.status === 'running' && <Loader2 size={20} className="animate-spin" />}
                                {step.status === 'failed' && <XCircle size={20} />}
                                {(step.status === 'pending' || step.status === 'skipped') && <Clock size={20} />}
                            </div>

                            {/* Connecting line */}
                            {!isLast && (
                                <div
                                    className={`h-12 w-0.5 transition-colors duration-200 ${
                                        step.status === 'completed' ? 'bg-green-500/30' : 'bg-gray-700'
                                    }`}
                                />
                            )}
                        </div>

                        {/* Step content */}
                        <div className="flex-1 pb-8">
                            <h3
                                className={`font-monologue-serif text-lg transition-colors duration-200 ${
                                    isActive ? 'text-gray-100' : 'text-gray-400'
                                }`}
                            >
                                {step.step_name}
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                {step.status === 'completed' && `Completed in ${getDuration(step.started_at, step.completed_at)}`}
                                {step.status === 'running' && `Running for ${getDuration(step.started_at)}...`}
                                {step.status === 'pending' && 'Pending'}
                                {step.status === 'failed' && 'Failed'}
                                {step.status === 'skipped' && 'Skipped'}
                            </p>
                            {step.error_message && <p className="mt-2 text-sm text-red-400">{step.error_message}</p>}
                        </div>
                    </div>
                );
            })}
        </div>
    );
});

WorkflowExecutionStatus.displayName = 'WorkflowExecutionStatus';

export { WorkflowExecutionStatus };
export type { WorkflowExecutionStatusProps };
