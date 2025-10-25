import * as React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

/**
 * MonologueCard Component - Monologue Design System
 *
 * A container component for grouping related content based on monologue.to design patterns.
 * Features dark-first design with subtle borders and smooth transitions.
 *
 * @example
 * // Basic card with default styling
 * <MonologueCard>
 *   <MonologueCard.Header>Integration Settings</MonologueCard.Header>
 *   <MonologueCard.Body>Configure your MCP integration</MonologueCard.Body>
 * </MonologueCard>
 *
 * @example
 * // Elevated card with footer
 * <MonologueCard variant="elevated" padding="lg">
 *   <MonologueCard.Header>Notion Integration</MonologueCard.Header>
 *   <MonologueCard.Body>Connect your Notion workspace</MonologueCard.Body>
 *   <MonologueCard.Footer>
 *     <MonologueButton>Connect</MonologueButton>
 *   </MonologueCard.Footer>
 * </MonologueCard>
 *
 * @example
 * // Ghost card (no background/border)
 * <MonologueCard variant="ghost" padding="none">
 *   <MonologueCard.Body>Minimal content</MonologueCard.Body>
 * </MonologueCard>
 */

const monologueCardVariants = cva(
  'rounded-md border transition-all duration-fast',
  {
    variants: {
      variant: {
        // Default: Dark background with stronger border for better contrast
        default: 'bg-monologue-neutral-800 border-monologue-border-default',
        // Elevated: Lighter dark background with high contrast border
        elevated: 'bg-monologue-neutral-700 border-monologue-border-strong',
        // Ghost: Transparent with no border
        ghost: 'bg-transparent border-transparent',
      },
      padding: {
        none: 'p-0',
        sm: 'p-3',
        md: 'p-5',
        lg: 'p-6',
      },
    },
    defaultVariants: {
      variant: 'default',
      padding: 'md',
    },
  }
);

interface MonologueCardProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof monologueCardVariants> {
  /**
   * Additional CSS classes
   */
  className?: string;
}

const MonologueCard = React.forwardRef<HTMLDivElement, MonologueCardProps>(
  ({ className, variant, padding, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(monologueCardVariants({ variant, padding, className }))}
        {...props}
      />
    );
  }
);

MonologueCard.displayName = 'MonologueCard';

/**
 * MonologueCard.Header
 * Optional header section for the card
 */
interface MonologueCardHeaderProps extends React.HTMLAttributes<HTMLDivElement> {
  className?: string;
}

const MonologueCardHeader = React.forwardRef<HTMLDivElement, MonologueCardHeaderProps>(
  ({ className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn('mb-4 font-monologue-serif text-lg text-monologue-neutral-white', className)}
        {...props}
      />
    );
  }
);

MonologueCardHeader.displayName = 'MonologueCard.Header';

/**
 * MonologueCard.Body
 * Main content area of the card
 */
interface MonologueCardBodyProps extends React.HTMLAttributes<HTMLDivElement> {
  className?: string;
}

const MonologueCardBody = React.forwardRef<HTMLDivElement, MonologueCardBodyProps>(
  ({ className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn('font-monologue-mono text-sm text-monologue-text-secondary', className)}
        {...props}
      />
    );
  }
);

MonologueCardBody.displayName = 'MonologueCard.Body';

/**
 * MonologueCard.Footer
 * Optional footer section for the card (typically for actions)
 */
interface MonologueCardFooterProps extends React.HTMLAttributes<HTMLDivElement> {
  className?: string;
}

const MonologueCardFooter = React.forwardRef<HTMLDivElement, MonologueCardFooterProps>(
  ({ className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn('mt-4 flex items-center gap-2', className)}
        {...props}
      />
    );
  }
);

MonologueCardFooter.displayName = 'MonologueCard.Footer';

// Attach subcomponents to main component
const MonologueCardWithSubcomponents = Object.assign(MonologueCard, {
  Header: MonologueCardHeader,
  Body: MonologueCardBody,
  Footer: MonologueCardFooter,
});

export { MonologueCardWithSubcomponents as MonologueCard, monologueCardVariants };
export type { MonologueCardProps };
