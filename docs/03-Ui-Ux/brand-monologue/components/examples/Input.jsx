import React, { forwardRef } from 'react';
import PropTypes from 'prop-types';

/**
 * Input Component - Monologue Design System
 *
 * A form input component with label, help text, and error states.
 * Based on the design patterns from monologue.to
 *
 * @example
 * <Input label="Email" type="email" placeholder="you@example.com" />
 * <Input label="Name" error="This field is required" />
 * <Input label="Bio" as="textarea" rows={4} />
 */

const Input = forwardRef(({
  label,
  error,
  helpText,
  leftIcon,
  rightIcon,
  className = '',
  containerClassName = '',
  as = 'input',
  ...props
}, ref) => {
  const Component = as;

  const baseClasses = 'w-full bg-background-elevated text-foreground font-mono text-base border border-border-default rounded-md px-3 py-2 transition-all duration-fast placeholder:text-foreground-muted focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent disabled:opacity-48 disabled:cursor-not-allowed';

  const errorClasses = error ? 'border-red-500 focus:ring-red-500' : '';

  const classes = [
    baseClasses,
    errorClasses,
    leftIcon ? 'pl-10' : '',
    rightIcon ? 'pr-10' : '',
    className,
  ].filter(Boolean).join(' ');

  return (
    <div className={`flex flex-col gap-1 ${containerClassName}`}>
      {label && (
        <label className="text-caption text-foreground font-mono">
          {label}
        </label>
      )}
      <div className="relative">
        {leftIcon && (
          <div className="absolute left-3 top-1/2 -translate-y-1/2 text-foreground-muted">
            {leftIcon}
          </div>
        )}
        <Component
          ref={ref}
          className={classes}
          aria-invalid={error ? 'true' : 'false'}
          aria-describedby={error ? `${props.id}-error` : helpText ? `${props.id}-help` : undefined}
          {...props}
        />
        {rightIcon && (
          <div className="absolute right-3 top-1/2 -translate-y-1/2 text-foreground-muted">
            {rightIcon}
          </div>
        )}
      </div>
      {helpText && !error && (
        <p id={`${props.id}-help`} className="text-caption text-foreground-muted">
          {helpText}
        </p>
      )}
      {error && (
        <p id={`${props.id}-error`} className="text-caption text-red-500" role="alert">
          {error}
        </p>
      )}
    </div>
  );
});

Input.displayName = 'Input';

Input.propTypes = {
  label: PropTypes.string,
  error: PropTypes.string,
  helpText: PropTypes.string,
  leftIcon: PropTypes.node,
  rightIcon: PropTypes.node,
  className: PropTypes.string,
  containerClassName: PropTypes.string,
  as: PropTypes.oneOf(['input', 'textarea']),
  id: PropTypes.string,
};

export default Input;
