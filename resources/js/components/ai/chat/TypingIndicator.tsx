import React from 'react';

interface TypingIndicatorProps {
  className?: string;
}

export function TypingIndicator({ className = '' }: TypingIndicatorProps) {
  return (
    <div className={`flex items-center gap-2 px-4 py-3 ${className}`}>
      <div className="flex items-center gap-1">
        <div className="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
        <div className="w-2 h-2 bg-blue-500 rounded-full animate-pulse delay-100"></div>
        <div className="w-2 h-2 bg-blue-500 rounded-full animate-pulse delay-200"></div>
      </div>
      <span className="text-sm text-gray-500 dark:text-gray-400">
        Claude is thinking...
      </span>
    </div>
  );
}