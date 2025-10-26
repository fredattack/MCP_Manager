import type { Message } from '@/types/ai/claude.types';
import { useState } from 'react';

interface MessageItemProps {
    message: Message;
    isSelected?: boolean;
    onSelect?: (messageId: string) => void;
    onRegenerate?: (messageId: string) => void;
    onCopy?: (content: string) => void;
    onEdit?: (messageId: string) => void;
}

export function MessageItem({ message, isSelected = false, onSelect, onRegenerate, onCopy, onEdit }: MessageItemProps) {
    const [showActions, setShowActions] = useState(false);
    const [copied, setCopied] = useState(false);

    const handleCopy = async () => {
        if (onCopy) {
            onCopy(message.content);
        } else {
            await navigator.clipboard.writeText(message.content);
        }
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const isUser = message.role === 'user';
    const isAssistant = message.role === 'assistant';
    const isError = message.status === 'error';
    const isSending = message.status === 'sending';

    return (
        <div
            className={`group relative cursor-pointer p-4 transition-all duration-200 ${isSelected ? 'border-r-2 border-blue-500 bg-blue-50 dark:bg-blue-950/20' : ''} ${isUser ? 'border-l-4 border-blue-500 bg-white dark:bg-gray-800' : ''} ${isAssistant ? 'border-l-4 border-green-500 bg-gray-50 dark:bg-gray-900' : ''} ${isError ? 'border-l-4 border-red-500 bg-red-50 dark:bg-red-950/20' : ''} hover:shadow-sm`}
            onClick={() => onSelect?.(message.id)}
            onMouseEnter={() => setShowActions(true)}
            onMouseLeave={() => setShowActions(false)}
        >
            {/* Header */}
            <div className="mb-2 flex items-center justify-between">
                <div className="flex items-center gap-2">
                    {/* Avatar */}
                    <div
                        className={`flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium ${isUser ? 'bg-blue-500 text-white' : 'bg-green-500 text-white'} `}
                    >
                        {isUser ? '👤' : '🤖'}
                    </div>

                    {/* Role */}
                    <span className="text-sm font-medium text-gray-900 dark:text-gray-100">{isUser ? 'You' : 'Claude'}</span>

                    {/* Status indicator */}
                    {isSending && <div className="h-2 w-2 animate-pulse rounded-full bg-yellow-500"></div>}
                    {isError && <div className="h-2 w-2 rounded-full bg-red-500"></div>}
                </div>

                {/* Timestamp */}
                <span className="text-xs text-gray-500 dark:text-gray-400">
                    {message.timestamp.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                    })}
                </span>
            </div>

            {/* Message Content */}
            <div className="prose prose-sm dark:prose-invert max-w-none">
                {message.content.split('\n').map((line, index) => {
                    // Simple code block detection
                    if (line.startsWith('```')) {
                        return (
                            <pre key={index} className="overflow-x-auto rounded-md bg-gray-900 p-3 text-sm text-gray-100">
                                <code>{line.replace(/^```\w*/, '').replace(/```$/, '')}</code>
                            </pre>
                        );
                    }

                    // Inline code detection
                    if (line.includes('`')) {
                        const parts = line.split('`');
                        return (
                            <p key={index} className="mb-2">
                                {parts.map((part, partIndex) =>
                                    partIndex % 2 === 1 ? (
                                        <code key={partIndex} className="rounded bg-gray-100 px-1 py-0.5 text-sm dark:bg-gray-800">
                                            {part}
                                        </code>
                                    ) : (
                                        part
                                    ),
                                )}
                            </p>
                        );
                    }

                    return line.trim() ? (
                        <p key={index} className="mb-2">
                            {line}
                        </p>
                    ) : (
                        <br key={index} />
                    );
                })}
            </div>

            {/* Metadata */}
            {message.metadata && (
                <div className="mt-2 flex gap-4 text-xs text-gray-500 dark:text-gray-400">
                    {message.metadata.model && <span>Model: {message.metadata.model}</span>}
                    {message.metadata.tokens && <span>Tokens: {message.metadata.tokens}</span>}
                    {message.metadata.processingTime && <span>Time: {message.metadata.processingTime}ms</span>}
                </div>
            )}

            {/* Actions */}
            {showActions && (
                <div className="absolute top-2 right-2 flex items-center gap-1 rounded-md border border-gray-200 bg-white p-1 shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <button
                        onClick={(e) => {
                            e.stopPropagation();
                            handleCopy();
                        }}
                        className="rounded p-1.5 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                        title="Copy message"
                    >
                        {copied ? (
                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                            </svg>
                        ) : (
                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-4 12a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2h8z"
                                />
                            </svg>
                        )}
                    </button>

                    {isAssistant && onRegenerate && (
                        <button
                            onClick={(e) => {
                                e.stopPropagation();
                                onRegenerate(message.id);
                            }}
                            className="rounded p-1.5 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                            title="Regenerate response"
                        >
                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>
                        </button>
                    )}

                    {isUser && onEdit && (
                        <button
                            onClick={(e) => {
                                e.stopPropagation();
                                onEdit(message.id);
                            }}
                            className="rounded p-1.5 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                            title="Edit message"
                        >
                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}
