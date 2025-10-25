import * as React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

/**
 * MonologueButton Component - Monologue Design System
 *
 * A flexible button component with multiple variants and sizes based on monologue.to design patterns.
 * Features dark-first design with smooth transitions and full accessibility support.
 *
 * @example
 * // Primary button (white background, dark text)
 * <MonologueButton variant="primary">Download for Mac</MonologueButton>
 *
 * @example
 * // Secondary button (transparent with white/12 background)
 * <MonologueButton variant="secondary" size="sm">Learn More</MonologueButton>
 *
 * @example
 * // Ghost button with icon
 * <MonologueButton variant="ghost" leftIcon={<Icon />}>Get Started</MonologueButton>
 *
 * @example
 * // Loading state
 * <MonologueButton loading>Processing...</MonologueButton>
 */

const monologueButtonVariants = cva(
  // Base styles - all buttons share these
  'inline-flex items-center justify-center font-monologue-mono transition-all duration-fast ease-smooth focus:outline-none focus-visible:ring-2 focus-visible:ring-monologue-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-monologue-neutral-900 disabled:opacity-48 disabled:cursor-not-allowed',
  {
    variants: {
      variant: {
        // Primary: White background with dark text (main CTA)
        primary:
          'bg-monologue-neutral-white text-monologue-neutral-700 hover:bg-monologue-neutral-100',
        // Secondary: Semi-transparent white background with white text
        secondary:
          'bg-white/12 text-monologue-neutral-white hover:bg-white/20',
        // Ghost: Transparent with brand color text
        ghost:
          'bg-transparent text-monologue-brand-primary hover:bg-monologue-brand-primary/10',
        // Link: Transparent with underline
        link: 'bg-transparent text-monologue-brand-primary hover:text-monologue-brand-accent underline-offset-4 hover:underline',
      },
      size: {
        sm: 'px-3 py-1 text-xs gap-1 rounded-sm',
        md: 'px-4 py-2 text-xs gap-2 rounded-sm',
        lg: 'px-5 py-3 text-sm gap-2 rounded-md',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'md',
    },
  }
);

interface MonologueButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof monologueButtonVariants> {
  /**
   * Icon to display before the button text
   */
  leftIcon?: React.ReactNode;
  /**
   * Icon to display after the button text
   */
  rightIcon?: React.ReactNode;
  /**
   * Shows a loading spinner and disables the button
   */
  loading?: boolean;
  /**
   * Additional CSS classes
   */
  className?: string;
}

const MonologueButton = React.forwardRef<HTMLButtonElement, MonologueButtonProps>(
  (
    {
      children,
      variant,
      size,
      leftIcon,
      rightIcon,
      loading = false,
      disabled = false,
      className,
      ...props
    },
    ref
  ) => {
    return (
      <button
        ref={ref}
        className={cn(monologueButtonVariants({ variant, size, className }))}
        disabled={disabled || loading}
        {...props}
      >
        {loading && (
          <svg
            className="animate-spin h-4 w-4"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            aria-hidden="true"
          >
            <circle
              className="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              strokeWidth="4"
            />
            <path
              className="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
        )}
        {!loading && leftIcon && <span className="flex-shrink-0">{leftIcon}</span>}
        <span>{children}</span>
        {!loading && rightIcon && <span className="flex-shrink-0">{rightIcon}</span>}
      </button>
    );
  }
);

MonologueButton.displayName = 'MonologueButton';

export { MonologueButton, monologueButtonVariants };
export type { MonologueButtonProps };
