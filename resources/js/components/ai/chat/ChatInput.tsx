import type { ModelType } from '@/types/ai/claude.types';
import { KeyboardEvent, useEffect, useRef, useState } from 'react';

interface ChatInputProps {
    onSend: (message: string, model?: ModelType) => void;
    onCancel?: () => void;
    disabled?: boolean;
    placeholder?: string;
    currentModel?: ModelType;
    onModelChange?: (model: ModelType) => void;
    showModelSelector?: boolean;
    maxLength?: number;
    className?: string;
}

const MODEL_OPTIONS: { value: ModelType; label: string; description: string }[] = [
    { value: 'gpt-4', label: 'GPT-4', description: 'Most capable, best for complex tasks' },
    { value: 'claude-3-opus', label: 'Claude 3 Opus', description: 'Excellent reasoning and creativity' },
    { value: 'mistral-large', label: 'Mistral Large', description: 'Fast and efficient' },
];

export function ChatInput({
    onSend,
    onCancel,
    disabled = false,
    placeholder = 'Message Claude...',
    currentModel = 'gpt-4',
    onModelChange,
    showModelSelector = true,
    maxLength = 8000,
    className = '',
}: ChatInputProps) {
    const [message, setMessage] = useState('');
    const [isExpanded, setIsExpanded] = useState(false);
    const [showCommands, setShowCommands] = useState(false);
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    // Auto-resize textarea
    useEffect(() => {
        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
            textareaRef.current.style.height = `${textareaRef.current.scrollHeight}px`;
        }
    }, [message]);

    const handleSubmit = () => {
        const trimmedMessage = message.trim();
        if (!trimmedMessage || disabled) return;

        // Handle slash commands
        if (trimmedMessage.startsWith('/')) {
            handleSlashCommand(trimmedMessage);
            return;
        }

        onSend(trimmedMessage);
        setMessage('');
        setIsExpanded(false);
    };

    const handleSlashCommand = (command: string) => {
        const [cmd, ...args] = command.slice(1).split(' ');

        switch (cmd.toLowerCase()) {
            case 'clear':
                // This would need to be handled by parent component
                console.log('Clear command');
                break;
            case 'help':
                setMessage('Available commands:\n/clear - Clear conversation\n/help - Show this help\n/model [model-name] - Change model');
                return;
            case 'model':
                if (args.length > 0 && onModelChange) {
                    const modelName = args[0] as ModelType;
                    if (MODEL_OPTIONS.find((m) => m.value === modelName)) {
                        onModelChange(modelName);
                    }
                }
                break;
            default:
                setMessage(`Unknown command: /${cmd}`);
                return;
        }

        setMessage('');
    };

    const handleKeyDown = (e: KeyboardEvent<HTMLTextAreaElement>) => {
        // Send on Cmd/Ctrl + Enter
        if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
            e.preventDefault();
            handleSubmit();
            return;
        }

        // Show commands on /
        if (e.key === '/' && message === '') {
            setShowCommands(true);
        } else {
            setShowCommands(false);
        }

        // Expand on any key if not expanded
        if (!isExpanded && message.length === 0) {
            setIsExpanded(true);
        }
    };

    const handleCancel = () => {
        setMessage('');
        setIsExpanded(false);
        onCancel?.();
    };

    return (
        <div className={`relative ${className}`}>
            {/* Commands tooltip */}
            {showCommands && (
                <div className="absolute bottom-full left-0 z-10 mb-2 rounded-lg border border-gray-200 bg-white p-3 text-sm shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <div className="mb-2 font-medium">Quick Commands:</div>
                    <div className="space-y-1 text-gray-600 dark:text-gray-400">
                        <div>/clear - Clear conversation</div>
                        <div>/help - Show help</div>
                        <div>/model - Change model</div>
                    </div>
                </div>
            )}

            <div className="flex flex-col rounded-lg border border-gray-200 bg-white shadow-sm focus-within:border-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:focus-within:border-blue-400">
                {/* Model selector */}
                {showModelSelector && isExpanded && (
                    <div className="flex items-center gap-2 border-b border-gray-200 px-3 py-2 dark:border-gray-700">
                        <span className="text-sm text-gray-600 dark:text-gray-400">Model:</span>
                        <select
                            value={currentModel}
                            onChange={(e) => onModelChange?.(e.target.value as ModelType)}
                            className="border-none bg-transparent text-sm text-gray-900 focus:outline-none dark:text-gray-100"
                            disabled={disabled}
                        >
                            {MODEL_OPTIONS.map((model) => (
                                <option key={model.value} value={model.value}>
                                    {model.label}
                                </option>
                            ))}
                        </select>
                    </div>
                )}

                {/* Text input */}
                <div className="relative">
                    <textarea
                        ref={textareaRef}
                        value={message}
                        onChange={(e) => setMessage(e.target.value)}
                        onKeyDown={handleKeyDown}
                        onFocus={() => setIsExpanded(true)}
                        placeholder={placeholder}
                        disabled={disabled}
                        maxLength={maxLength}
                        rows={isExpanded ? 3 : 1}
                        className="w-full resize-none border-none bg-transparent px-3 py-3 text-gray-900 placeholder-gray-500 focus:outline-none dark:text-gray-100 dark:placeholder-gray-400"
                        style={{ minHeight: isExpanded ? '80px' : '48px', maxHeight: '200px' }}
                    />

                    {/* Character counter */}
                    {isExpanded && (
                        <div className="absolute bottom-2 left-3 text-xs text-gray-400">
                            {message.length}/{maxLength}
                        </div>
                    )}
                </div>

                {/* Action buttons */}
                <div className="flex items-center justify-between border-t border-gray-200 px-3 py-2 dark:border-gray-700">
                    <div className="flex items-center gap-2">
                        {/* File attachment (future feature) */}
                        <button
                            type="button"
                            className="p-1.5 text-gray-400 transition-colors hover:text-gray-600 dark:hover:text-gray-300"
                            disabled={disabled}
                            title="Attach file (coming soon)"
                        >
                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                />
                            </svg>
                        </button>
                    </div>

                    <div className="flex items-center gap-2">
                        {/* Cancel button (shown when loading) */}
                        {disabled && (
                            <button
                                type="button"
                                onClick={handleCancel}
                                className="px-3 py-1.5 text-sm text-gray-600 transition-colors hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                Cancel
                            </button>
                        )}

                        {/* Send button */}
                        <button
                            type="button"
                            onClick={handleSubmit}
                            disabled={disabled || !message.trim()}
                            className="flex items-center gap-1.5 rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-300 dark:disabled:bg-gray-600"
                        >
                            {disabled ? (
                                <>
                                    <svg className="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Sending...
                                </>
                            ) : (
                                <>
                                    Send
                                    <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </>
                            )}
                        </button>
                    </div>
                </div>
            </div>

            {/* Keyboard shortcut hint */}
            {isExpanded && !disabled && (
                <div className="mt-1 text-center text-xs text-gray-500 dark:text-gray-400">Press Cmd+Enter to send • / for commands</div>
            )}
        </div>
    );
}
