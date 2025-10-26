import { ChatInputWithNLP } from '@/components/ai/chat/ChatInputWithNLP';
import { VirtualizedMessageList } from '@/components/ai/chat/VirtualizedMessageList';
import { useClaudeChat } from '@/hooks/ai/use-claude-chat';
import { useToast } from '@/hooks/ui/use-toast';
import AppLayout from '@/layouts/app-layout';
import { ParsedCommand } from '@/lib/nlp';
import { cn } from '@/lib/utils';
import { AIModel, CanvasContent } from '@/types/ai/claude.types';
import { Head } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';

export default function ClaudeChatPage() {
    console.log('🎬 [CLAUDE-CHAT-PAGE] Component rendering');

    const {
        messages,
        isLoading,
        error,
        streamingMessageId,
        currentModel,
        setCurrentModel,
        sendMessage,
        regenerateMessage,
        clearMessages,
        cancelGeneration,
    } = useClaudeChat({
        enableStreaming: true,
        autoSave: true,
    });

    console.log('📊 [CLAUDE-CHAT-PAGE] Current state:', {
        messagesCount: messages.length,
        isLoading,
        error,
        streamingMessageId,
        currentModel,
    });

    const [selectedMessageId, setSelectedMessageId] = useState<string | null>(null);
    const [canvasContent, setCanvasContent] = useState<CanvasContent | null>(null);
    const [isCanvasVisible, setIsCanvasVisible] = useState(true);
    const [useVirtualization, setUseVirtualization] = useState(false);

    // Enable virtualization automatically for large message lists
    useEffect(() => {
        setUseVirtualization(messages.length > 50);
    }, [messages.length]);

    // Extract canvas content from selected message
    useEffect(() => {
        if (selectedMessageId) {
            const message = messages.find((m) => m.id === selectedMessageId);
            if (message && message.role === 'assistant') {
                // Parse message content for code blocks, tables, etc.
                const content = extractCanvasContent(message.content);
                setCanvasContent(content);
            }
        }
    }, [selectedMessageId, messages]);

    const extractCanvasContent = (content: string): CanvasContent => {
        // Check if content has code blocks
        const codeBlockRegex = /```(\w+)?\n([\s\S]*?)```/g;
        const codeMatches = content.match(codeBlockRegex);

        if (codeMatches && codeMatches.length > 0) {
            const firstMatch = codeMatches[0];
            const languageMatch = firstMatch.match(/```(\w+)?/);
            const language = languageMatch?.[1] || 'plaintext';
            const code = firstMatch.replace(/```\w*\n?/, '').replace(/```$/, '');

            return {
                type: 'code',
                content: code,
                metadata: { language },
            };
        }

        // Check for tables (simple markdown tables)
        const tableRegex = /\|.*\|[\r\n]+\|[-:\s|]+\|[\r\n]+(\|.*\|[\r\n]+)+/g;
        if (tableRegex.test(content)) {
            return {
                type: 'table',
                content: content,
            };
        }

        // Default to markdown
        return {
            type: 'markdown',
            content: content,
        };
    };

    const handleMessageSelect = useCallback((messageId: string) => {
        console.log('🎯 [CLAUDE-CHAT-PAGE] Message selected:', messageId);
        setSelectedMessageId(messageId);
    }, []);

    const handleKeyDown = useCallback(
        (e: KeyboardEvent) => {
            // Cmd/Ctrl + K - New conversation
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                clearMessages();
            }
            // Cmd/Ctrl + / - Toggle canvas
            if ((e.metaKey || e.ctrlKey) && e.key === '/') {
                e.preventDefault();
                setIsCanvasVisible((prev) => !prev);
            }
            // Escape - Cancel generation
            if (e.key === 'Escape' && isLoading) {
                e.preventDefault();
                cancelGeneration();
            }
        },
        [clearMessages, isLoading, cancelGeneration],
    );

    const { toast } = useToast();

    const handleNLPCommand = useCallback(
        (command: ParsedCommand) => {
            console.log('🤖 [CLAUDE-CHAT-PAGE] NLP Command received:', command);

            // Show command interpretation in chat
            const commandMessage = `Command detected: ${command.intent.intent} for ${command.intent.service || 'general'}`;
            toast.info('Command Detected', commandMessage);

            // You can add custom handling here if needed
            // For now, the command will be executed by the NLP engine
        },
        [toast],
    );

    useEffect(() => {
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [handleKeyDown]);

    return (
        <AppLayout>
            <Head title="Claude Chat" />

            <div className="flex h-[calc(100vh-4rem)] bg-white dark:bg-gray-900">
                {/* Chat Panel - Left */}
                <div
                    className={cn(
                        'flex flex-col border-r border-gray-200 transition-all duration-300 dark:border-gray-700',
                        isCanvasVisible ? 'w-1/2' : 'w-full',
                    )}
                >
                    {/* Chat Header */}
                    <div className="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div className="flex items-center justify-between">
                            <h1 className="text-lg font-semibold text-gray-900 dark:text-gray-100">Claude Assistant</h1>
                            <div className="flex items-center gap-2">
                                {/* Model Selector */}
                                <select
                                    value={currentModel}
                                    onChange={(e) => setCurrentModel(e.target.value as AIModel)}
                                    className="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                >
                                    <option value="gpt-4">GPT-4</option>
                                    <option value="claude-3-opus">Claude 3 Opus</option>
                                    <option value="mistral-large">Mistral Large</option>
                                </select>

                                {/* Action Buttons */}
                                <button
                                    onClick={clearMessages}
                                    className="rounded-md p-2 text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                                    title="New conversation (Cmd/Ctrl + K)"
                                >
                                    <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>

                                <button
                                    onClick={() => setIsCanvasVisible(!isCanvasVisible)}
                                    className="rounded-md p-2 text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                                    title="Toggle canvas (Cmd/Ctrl + /)"
                                >
                                    <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Messages Area */}
                    <div className="flex-1 overflow-y-auto px-6 py-4">
                        {messages.length === 0 ? (
                            <div className="flex h-full items-center justify-center">
                                <div className="text-center">
                                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                        <svg className="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
                                            />
                                        </svg>
                                    </div>
                                    <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">Start a conversation</h3>
                                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">Ask me anything! I'm here to help.</p>
                                </div>
                            </div>
                        ) : useVirtualization ? (
                            <div className="h-full">
                                <VirtualizedMessageList
                                    messages={messages}
                                    onRegenerate={regenerateMessage}
                                    onEdit={(id, content) => console.log('Edit not implemented yet', id, content)}
                                    streamingMessageId={streamingMessageId}
                                    className="h-full"
                                />
                                {isLoading && streamingMessageId === null && (
                                    <div className="p-4">
                                        <TypingIndicator />
                                    </div>
                                )}
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {messages.map((message) => (
                                    <MessageItem
                                        key={message.id}
                                        message={message}
                                        isSelected={selectedMessageId === message.id}
                                        onSelect={() => handleMessageSelect(message.id)}
                                        onRegenerate={() => regenerateMessage(message.id)}
                                        isStreaming={streamingMessageId === message.id}
                                    />
                                ))}
                                {isLoading && streamingMessageId === null && <TypingIndicator />}
                            </div>
                        )}

                        {error && (
                            <div className="mt-4 rounded-md bg-red-50 p-4 dark:bg-red-900/20">
                                <p className="text-sm text-red-800 dark:text-red-200">{error}</p>
                            </div>
                        )}
                    </div>

                    {/* Input Area */}
                    <div className="border-t border-gray-200 p-4 dark:border-gray-700">
                        <ChatInputWithNLP
                            onSend={sendMessage}
                            onCommand={handleNLPCommand}
                            disabled={isLoading}
                            placeholder={`Message ${currentModel}...`}
                            currentModel={currentModel}
                            onModelChange={setCurrentModel}
                            showModelSelector={false}
                        />
                    </div>
                </div>

                {/* Canvas Panel - Right */}
                {isCanvasVisible && (
                    <div className="flex flex-1 flex-col bg-gray-50 dark:bg-gray-800">
                        <CanvasPanel selectedMessage={messages.find((m) => m.id === selectedMessageId)} content={canvasContent} />
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

// Temporary component placeholders - these will be moved to separate files
interface MessageItemProps {
    message: { id: string; role: string; content: string; timestamp: Date; status: string };
    isSelected: boolean;
    onSelect: () => void;
    onRegenerate: () => void;
    isStreaming: boolean;
}

function MessageItem({ message, isSelected, onSelect, onRegenerate, isStreaming }: MessageItemProps) {
    console.log('💬 [MESSAGE-ITEM] Rendering:', {
        messageId: message.id,
        role: message.role,
        contentLength: message.content?.length,
        status: message.status,
        isSelected,
        isStreaming,
    });

    return (
        <div
            className={cn(
                'group relative cursor-pointer rounded-lg p-4 transition-all',
                message.role === 'user'
                    ? 'border-l-4 border-blue-500 bg-white shadow-sm hover:shadow-md dark:bg-gray-800'
                    : 'border-l-4 border-green-500 bg-gray-50 dark:bg-gray-900',
                isSelected && 'ring-2 ring-blue-500',
            )}
            onClick={() => {
                console.log('👆 [MESSAGE-ITEM] Clicked:', message.id);
                onSelect();
            }}
        >
            <div className="flex items-start gap-3">
                <div
                    className={cn(
                        'flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium',
                        message.role === 'user'
                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                            : 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                    )}
                >
                    {message.role === 'user' ? 'U' : 'A'}
                </div>
                <div className="flex-1">
                    <div className="mb-1 flex items-center gap-2">
                        <span className="text-xs font-medium text-gray-700 dark:text-gray-300">{message.role === 'user' ? 'You' : 'Claude'}</span>
                        <span className="text-xs text-gray-500 dark:text-gray-400">{new Date(message.timestamp).toLocaleTimeString()}</span>
                        {message.status === 'error' && <span className="text-xs text-red-600 dark:text-red-400">Error</span>}
                    </div>
                    <div className="text-sm whitespace-pre-wrap text-gray-900 dark:text-gray-100">
                        {isStreaming ? (
                            <>
                                {message.content}
                                <span className="ml-1 inline-block h-4 w-2 animate-pulse bg-gray-900 dark:bg-gray-100" />
                            </>
                        ) : (
                            message.content
                        )}
                    </div>
                    {message.role === 'assistant' && !isStreaming && (
                        <div className="mt-2 flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                            <button
                                onClick={(e) => {
                                    e.stopPropagation();
                                    navigator.clipboard.writeText(message.content);
                                }}
                                className="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                Copy
                            </button>
                            <button
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onRegenerate();
                                }}
                                className="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                Regenerate
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

function TypingIndicator() {
    return (
        <div className="flex items-center gap-2 p-4">
            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-sm font-medium text-green-700 dark:bg-green-900 dark:text-green-300">
                A
            </div>
            <div className="flex gap-1">
                <span className="h-2 w-2 animate-pulse rounded-full bg-gray-400" />
                <span className="h-2 w-2 animate-pulse rounded-full bg-gray-400" style={{ animationDelay: '0.2s' }} />
                <span className="h-2 w-2 animate-pulse rounded-full bg-gray-400" style={{ animationDelay: '0.4s' }} />
            </div>
        </div>
    );
}

interface CanvasPanelProps {
    selectedMessage?: { id: string; role: string; content: string; timestamp: Date };
    content?: CanvasContent | null;
}

function CanvasPanel({ selectedMessage, content }: CanvasPanelProps) {
    if (!selectedMessage || selectedMessage.role !== 'assistant') {
        return (
            <div className="flex h-full items-center justify-center">
                <div className="text-center">
                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                        <svg className="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>
                    <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">Canvas View</h3>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">Select an assistant message to view formatted content</p>
                </div>
            </div>
        );
    }

    return (
        <div className="h-full overflow-auto p-6">
            <div className="mb-4 flex items-center justify-between">
                <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">Canvas</h2>
                <div className="flex items-center gap-2">
                    <button
                        className="rounded-md p-2 text-gray-600 transition-colors hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700"
                        title="Export"
                    >
                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            {content?.type === 'code' && (
                <div className="rounded-lg bg-gray-900 p-4 text-gray-100">
                    <div className="mb-2 flex items-center justify-between">
                        <span className="text-xs text-gray-400">{content.metadata?.language || 'plaintext'}</span>
                        <button onClick={() => navigator.clipboard.writeText(content.content)} className="text-xs text-gray-400 hover:text-gray-200">
                            Copy code
                        </button>
                    </div>
                    <pre className="overflow-x-auto">
                        <code className="text-sm">{content.content}</code>
                    </pre>
                </div>
            )}

            {content?.type === 'markdown' && <div className="prose prose-sm dark:prose-invert max-w-none">{content.content}</div>}

            {content?.type === 'table' && (
                <div className="overflow-x-auto">
                    <div className="rounded-lg border border-gray-200 dark:border-gray-700">
                        {/* Table rendering would go here */}
                        <pre className="p-4 text-sm">{content.content}</pre>
                    </div>
                </div>
            )}
        </div>
    );
}
