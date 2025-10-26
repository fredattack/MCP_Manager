interface TypingIndicatorProps {
    className?: string;
}

export function TypingIndicator({ className = '' }: TypingIndicatorProps) {
    return (
        <div className={`flex items-center gap-2 px-4 py-3 ${className}`}>
            <div className="flex items-center gap-1">
                <div className="h-2 w-2 animate-pulse rounded-full bg-blue-500"></div>
                <div className="h-2 w-2 animate-pulse rounded-full bg-blue-500 delay-100"></div>
                <div className="h-2 w-2 animate-pulse rounded-full bg-blue-500 delay-200"></div>
            </div>
            <span className="text-sm text-gray-500 dark:text-gray-400">Claude is thinking...</span>
        </div>
    );
}
