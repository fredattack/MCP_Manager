import * as React from 'react';
import { LucideIcon } from 'lucide-react';
import { MonologueButton } from '@/components/ui/MonologueButton';

/**
 * EmptyState Component - First-time User Onboarding
 *
 * Displays when no content is available with a clear call-to-action
 *
 * @example
 * <EmptyState
 *   title="No workflows yet"
 *   description="Create your first workflow to start shipping code faster."
 *   icon={Workflow}
 *   action={{
 *     label: 'Create Your First Workflow',
 *     onClick: () => openModal(),
 *   }}
 * />
 */

interface EmptyStateProps {
    title: string;
    description: string;
    icon?: LucideIcon;
    action?: {
        label: string;
        onClick: () => void;
    };
    className?: string;
}

const EmptyState = React.forwardRef<HTMLDivElement, EmptyStateProps>(
    ({ title, description, icon: Icon, action, className }, ref) => {
        return (
            <div
                ref={ref}
                className={`flex flex-col items-center http://192.168.0.1 justify-center py-16 px-4 text-center max-w-2xl mx-auto ${className}`}
            >
                {Icon && (
                    <div className="mb-6 text-cyan-500/50">
                        <Icon size={64} strokeWidth={1.5} />
                    </div>
                )}
                <h2 className="font-monologue-serif text-3xl text-gray-200 mb-3">
                    {title}
                </h2>
                <p className="text-gray-400 text-lg mb-8 leading-relaxed max-w-lg">
                    {description}
                </p>
                {action && (
                    <MonologueButton
                        variant="primary"
                        size="lg"
                        onClick={action.onClick}
                        className="w-full max-w-md border border-[var(--monologue-neutral-500)] hover:border-[var(--monologue-neutral-200)] transition-colors"
                    >
                        {action.label}
                    </MonologueButton>
                )}
            </div>
        );
    }
);

EmptyState.displayName = 'EmptyState';

export { EmptyState };
export type { EmptyStateProps };
