import * as React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

/**
 * MonologueBadge Component - Monologue Design System
 *
 * A small label component for status indicators, tags, or labels based on monologue.to design patterns.
 * Optimized for MCP integration statuses (active, inactive, error, pending).
 *
 * @example
 * // Status badges for MCP integrations
 * <MonologueBadge variant="active">Active</MonologueBadge>
 * <MonologueBadge variant="inactive">Inactive</MonologueBadge>
 * <MonologueBadge variant="error">Error</MonologueBadge>
 * <MonologueBadge variant="pending">Pending</MonologueBadge>
 *
 * @example
 * // Different sizes
 * <MonologueBadge variant="primary" size="sm">Small</MonologueBadge>
 * <MonologueBadge variant="primary" size="md">Medium</MonologueBadge>
 * <MonologueBadge variant="primary" size="lg">Large</MonologueBadge>
 *
 * @example
 * // Brand color variants
 * <MonologueBadge variant="primary">Primary</MonologueBadge>
 * <MonologueBadge variant="accent">Accent</MonologueBadge>
 */

const monologueBadgeVariants = cva(
  'inline-flex items-center font-monologue-mono font-normal rounded-sm transition-all',
  {
    variants: {
      variant: {
        // Default: Semi-transparent white
        default: 'bg-white/12 text-monologue-neutral-white',

        // Brand colors
        primary:
          'bg-monologue-brand-primary/20 text-monologue-brand-primary',
        accent:
          'bg-monologue-brand-accent/20 text-monologue-brand-accent',

        // Status variants for MCP integrations
        active:
          'bg-monologue-brand-success/20 text-monologue-brand-success',
        inactive:
          'bg-monologue-neutral-600 text-monologue-text-muted',
        error:
          'bg-red-500/20 text-red-400',
        pending:
          'bg-yellow-500/20 text-yellow-400',

        // Additional utility variant
        muted:
          'bg-monologue-neutral-600 text-monologue-text-muted',
      },
      size: {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-3 py-1 text-xs',
        lg: 'px-4 py-1.5 text-sm',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  }
);

interface MonologueBadgeProps
  extends React.HTMLAttributes<HTMLSpanElement>,
    VariantProps<typeof monologueBadgeVariants> {
  /**
   * Additional CSS classes
   */
  className?: string;
}

const MonologueBadge = React.forwardRef<HTMLSpanElement, MonologueBadgeProps>(
  ({ className, variant, size, ...props }, ref) => {
    return (
      <span
        ref={ref}
        className={cn(monologueBadgeVariants({ variant, size, className }))}
        {...props}
      />
    );
  }
);

MonologueBadge.displayName = 'MonologueBadge';

export { MonologueBadge, monologueBadgeVariants };
export type { MonologueBadgeProps };
