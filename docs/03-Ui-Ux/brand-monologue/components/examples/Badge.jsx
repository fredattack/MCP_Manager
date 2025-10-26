import React from 'react';
import PropTypes from 'prop-types';

/**
 * Badge Component - Monologue Design System
 *
 * A small label component for status indicators, tags, or labels.
 * Based on the design patterns from monologue.to
 *
 * @example
 * <Badge variant="primary">New</Badge>
 * <Badge variant="success">Active</Badge>
 * <Badge variant="accent">Early Bird</Badge>
 */

const Badge = ({
  children,
  variant = 'default',
  size = 'md',
  className = '',
  ...props
}) => {
  const baseClasses = 'inline-flex items-center font-mono font-normal rounded-sm transition-all';

  const variantClasses = {
    default: 'bg-white/12 text-neutral-white',
    primary: 'bg-brand-primary/20 text-brand-primary',
    accent: 'bg-brand-accent/20 text-brand-accent',
    success: 'bg-brand-success/20 text-brand-success',
    muted: 'bg-neutral-600 text-foreground-muted',
  };

  const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-3 py-1 text-xs',
    lg: 'px-4 py-1.5 text-sm',
  };

  const classes = [
    baseClasses,
    variantClasses[variant] || variantClasses.default,
    sizeClasses[size] || sizeClasses.md,
    className,
  ].filter(Boolean).join(' ');

  return (
    <span className={classes} {...props}>
      {children}
    </span>
  );
};

Badge.propTypes = {
  children: PropTypes.node.isRequired,
  variant: PropTypes.oneOf(['default', 'primary', 'accent', 'success', 'muted']),
  size: PropTypes.oneOf(['sm', 'md', 'lg']),
  className: PropTypes.string,
};

export default Badge;
