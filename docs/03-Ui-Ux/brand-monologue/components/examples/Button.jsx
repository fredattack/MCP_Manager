import React from 'react';
import PropTypes from 'prop-types';

/**
 * Button Component - Monologue Design System
 *
 * A flexible button component with multiple variants and sizes.
 * Based on the design patterns from monologue.to
 *
 * @example
 * <Button variant="primary">Download for Mac</Button>
 * <Button variant="secondary" size="sm">Learn More</Button>
 * <Button variant="ghost" leftIcon={<Icon />}>Get Started</Button>
 */

const Button = ({
  children,
  variant = 'primary',
  size = 'md',
  leftIcon,
  rightIcon,
  disabled = false,
  loading = false,
  className = '',
  ...props
}) => {
  const baseClasses = 'inline-flex items-center justify-center font-mono transition-all duration-fast ease-smooth focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:opacity-48 disabled:cursor-not-allowed';

  const variantClasses = {
    primary: 'bg-neutral-white text-neutral-700 hover:bg-neutral-100',
    secondary: 'bg-white/12 text-neutral-white hover:bg-white/20',
    ghost: 'bg-transparent text-brand-primary hover:bg-brand-primary/10',
    link: 'bg-transparent text-link hover:text-link-hover underline-offset-4 hover:underline',
  };

  const sizeClasses = {
    sm: 'px-3 py-1 text-xs gap-1 rounded-sm',
    md: 'px-4 py-2 text-xs gap-2 rounded-sm',
    lg: 'px-5 py-3 text-sm gap-2 rounded-md',
  };

  const classes = [
    baseClasses,
    variantClasses[variant] || variantClasses.primary,
    sizeClasses[size] || sizeClasses.md,
    className,
  ].filter(Boolean).join(' ');

  return (
    <button
      className={classes}
      disabled={disabled || loading}
      {...props}
    >
      {loading && (
        <svg className="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
      )}
      {!loading && leftIcon && <span className="flex-shrink-0">{leftIcon}</span>}
      <span>{children}</span>
      {!loading && rightIcon && <span className="flex-shrink-0">{rightIcon}</span>}
    </button>
  );
};

Button.propTypes = {
  children: PropTypes.node.isRequired,
  variant: PropTypes.oneOf(['primary', 'secondary', 'ghost', 'link']),
  size: PropTypes.oneOf(['sm', 'md', 'lg']),
  leftIcon: PropTypes.node,
  rightIcon: PropTypes.node,
  disabled: PropTypes.bool,
  loading: PropTypes.bool,
  className: PropTypes.string,
};

export default Button;
