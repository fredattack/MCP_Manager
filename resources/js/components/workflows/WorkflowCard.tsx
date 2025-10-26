import { StatusBadge } from '@/components/ui/StatusBadge';
import { Workflow } from '@/types';
import { Brain, Clock } from 'lucide-react';
import * as React from 'react';

/**
 * WorkflowCard Component - Workflow Summary Display
 *
 * Displays a workflow summary in the index list with status, description, and metadata
 *
 * @example
 * <WorkflowCard
 *   workflow={workflow}
 *   onClick={() => router.visit(`/workflows/${workflow.id}`)}
 * />
 */

interface WorkflowCardProps {
    workflow: Workflow;
    onClick?: () => void;
    className?: string;
}

const WorkflowCard = React.forwardRef<HTMLDivElement, WorkflowCardProps>(({ workflow, onClick, className }, ref) => {
    const formatRelativeTime = (dateString: string): string => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

        if (diffInSeconds < 60) {
            return 'just now';
        }
        if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago`;
        }
        if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;
        }
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} ${days === 1 ? 'day' : 'days'} ago`;
    };

    return (
        <div
            ref={ref}
            onClick={onClick}
            className={`bg-monologue-neutral-800 border-monologue-border-default cursor-pointer rounded-lg border p-6 transition-all duration-200 hover:border-cyan-500/50 hover:shadow-lg ${className}`}
        >
            {/* Header */}
            <div className="mb-4 flex items-start justify-between gap-4">
                <StatusBadge status={workflow.status} size="sm" />
                <span className="flex-shrink-0 truncate text-sm text-gray-400">{workflow.name}</span>
            </div>

            {/* Task description */}
            <p className="font-monologue-serif mb-4 line-clamp-2 text-lg leading-relaxed text-gray-200">{workflow.description || workflow.name}</p>

            {/* Footer metadata */}
            <div className="flex items-center gap-4 text-sm text-gray-500">
                {workflow.config?.llm_provider && (
                    <span className="flex items-center gap-1.5">
                        <Brain size={14} />
                        <span className="capitalize">{String(workflow.config.llm_provider)}</span>
                    </span>
                )}
                {workflow.duration && (
                    <span className="flex items-center gap-1.5">
                        <Clock size={14} />
                        {workflow.duration}s
                    </span>
                )}
                <span className="ml-auto">{formatRelativeTime(workflow.created_at)}</span>
            </div>
        </div>
    );
});

WorkflowCard.displayName = 'WorkflowCard';

export { WorkflowCard };
export type { WorkflowCardProps };
