import * as React from 'react';
import { CheckCircle, XCircle, Loader2, Clock } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * StatusBadge Component - Workflows Status Indicator
 *
 * Displays workflow status with appropriate color and icon
 *
 * @example
 * <StatusBadge status="running" />
 * <StatusBadge status="completed" size="lg" />
 */

interface StatusBadgeProps {
    status: 'pending' | 'running' | 'completed' | 'failed';
    size?: 'sm' | 'md' | 'lg';
    className?: string;
}

const StatusBadge = React.forwardRef<HTMLSpanElement, StatusBadgeProps>(
    ({ status, size = 'md', className }, ref) => {
        const config = {
            pending: {
                color: 'text-gray-500 bg-gray-500/10',
                label: 'Pending',
                icon: Clock,
            },
            running: {
                color: 'text-cyan-500 bg-cyan-500/10',
                label: 'Running',
                icon: Loader2,
                animated: true,
            },
            completed: {
                color: 'text-green-500 bg-green-500/10',
                label: 'Completed',
                icon: CheckCircle,
            },
            failed: {
                color: 'text-red-500 bg-red-500/10',
                label: 'Failed',
                icon: XCircle,
            },
        };

        const { color, label, icon: Icon, animated } = config[status];

        const sizeClasses = {
            sm: 'text-xs px-2 py-1',
            md: 'text-sm px-3 py-1.5',
            lg: 'text-base px-4 py-2',
        };

        const iconSizes = {
            sm: 14,
            md: 16,
            lg: 18,
        };

        return (
            <span
                ref={ref}
                className={cn(
                    'inline-flex items-center gap-2 rounded-full font-medium',
                    color,
                    sizeClasses[size],
                    className
                )}
            >
                <Icon
                    size={iconSizes[size]}
                    className={animated ? 'animate-spin' : ''}
                />
                {label}
            </span>
        );
    }
);

StatusBadge.displayName = 'StatusBadge';

export { StatusBadge };
export type { StatusBadgeProps };
