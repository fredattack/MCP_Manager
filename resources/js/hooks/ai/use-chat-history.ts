import type { Conversation, ConversationSortOrder, Message } from '@/types/ai/claude.types';
import { useCallback, useEffect, useState } from 'react';

interface UseChatHistoryOptions {
    maxConversations?: number;
    autoSave?: boolean;
    storageKey?: string;
}

export function useChatHistory(options: UseChatHistoryOptions = {}) {
    const { maxConversations = 50, autoSave = true, storageKey = 'claude-chat-conversations' } = options;

    const [conversations, setConversations] = useState<Map<string, Conversation>>(new Map());
    const [activeConversationId, setActiveConversationId] = useState<string | null>(null);
    const [sortOrder, setSortOrder] = useState<ConversationSortOrder>('newest');
    const [searchQuery, setSearchQuery] = useState('');
    const [isLoading, setIsLoading] = useState(false);

    const generateConversationId = useCallback(() => {
        return `conv_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }, []);

    const createConversation = useCallback(
        (title?: string, messages?: Message[]): Conversation => {
            const now = new Date();
            return {
                id: generateConversationId(),
                title: title || 'New Conversation',
                messages: messages || [],
                createdAt: now,
                updatedAt: now,
                metadata: {
                    totalTokens: 0,
                    totalMessages: messages?.length || 0,
                },
            };
        },
        [generateConversationId],
    );

    const addConversation = useCallback(
        (conversation: Conversation) => {
            setConversations((prev) => {
                const newMap = new Map(prev);
                newMap.set(conversation.id, conversation);

                // Limit the number of conversations
                if (newMap.size > maxConversations) {
                    const sortedConversations = Array.from(newMap.values()).sort((a, b) => b.updatedAt.getTime() - a.updatedAt.getTime());

                    const toRemove = sortedConversations.slice(maxConversations);
                    toRemove.forEach((conv) => newMap.delete(conv.id));
                }

                return newMap;
            });
        },
        [maxConversations],
    );

    const updateConversation = useCallback((id: string, updates: Partial<Conversation>) => {
        setConversations((prev) => {
            const conversation = prev.get(id);
            if (!conversation) return prev;

            const newMap = new Map(prev);
            newMap.set(id, {
                ...conversation,
                ...updates,
                updatedAt: new Date(),
            });
            return newMap;
        });
    }, []);

    const deleteConversation = useCallback(
        (id: string) => {
            setConversations((prev) => {
                const newMap = new Map(prev);
                newMap.delete(id);
                return newMap;
            });

            if (activeConversationId === id) {
                setActiveConversationId(null);
            }
        },
        [activeConversationId],
    );

    const newConversation = useCallback(() => {
        const conversation = createConversation();
        addConversation(conversation);
        setActiveConversationId(conversation.id);
        return conversation;
    }, [createConversation, addConversation]);

    const loadConversation = useCallback(
        (id: string) => {
            const conversation = conversations.get(id);
            if (conversation) {
                setActiveConversationId(id);
                return conversation;
            }
            return null;
        },
        [conversations],
    );

    const addMessageToConversation = useCallback(
        (conversationId: string, message: Message) => {
            updateConversation(conversationId, {
                messages: [...(conversations.get(conversationId)?.messages || []), message],
                metadata: {
                    totalMessages: (conversations.get(conversationId)?.messages.length || 0) + 1,
                },
            });
        },
        [conversations, updateConversation],
    );

    const updateMessageInConversation = useCallback(
        (conversationId: string, messageId: string, updates: Partial<Message>) => {
            const conversation = conversations.get(conversationId);
            if (!conversation) return;

            const updatedMessages = conversation.messages.map((msg) => (msg.id === messageId ? { ...msg, ...updates } : msg));

            updateConversation(conversationId, { messages: updatedMessages });
        },
        [conversations, updateConversation],
    );

    const searchConversations = useCallback(
        (query: string) => {
            if (!query.trim()) return Array.from(conversations.values());

            const lowerQuery = query.toLowerCase();
            return Array.from(conversations.values()).filter(
                (conv) =>
                    conv.title.toLowerCase().includes(lowerQuery) || conv.messages.some((msg) => msg.content.toLowerCase().includes(lowerQuery)),
            );
        },
        [conversations],
    );

    const getSortedConversations = useCallback(() => {
        const conversationList = searchQuery ? searchConversations(searchQuery) : Array.from(conversations.values());

        return conversationList.sort((a, b) => {
            switch (sortOrder) {
                case 'newest':
                    return b.updatedAt.getTime() - a.updatedAt.getTime();
                case 'oldest':
                    return a.updatedAt.getTime() - b.updatedAt.getTime();
                case 'mostActive':
                    return (b.metadata?.totalMessages || 0) - (a.metadata?.totalMessages || 0);
                case 'alphabetical':
                    return a.title.localeCompare(b.title);
                default:
                    return 0;
            }
        });
    }, [conversations, searchQuery, sortOrder, searchConversations]);

    const exportConversation = useCallback(
        async (conversationId: string, format: 'json' | 'md' | 'html' | 'pdf') => {
            const conversation = conversations.get(conversationId);
            if (!conversation) return;

            setIsLoading(true);

            try {
                let content = '';
                const timestamp = new Date().toISOString().split('T')[0];
                const filename = `claude-chat-${conversation.title.replace(/[^a-zA-Z0-9]/g, '-')}-${timestamp}`;

                switch (format) {
                    case 'json':
                        content = JSON.stringify(conversation, null, 2);
                        downloadFile(content, `${filename}.json`, 'application/json');
                        break;

                    case 'md':
                        content = convertToMarkdown(conversation);
                        downloadFile(content, `${filename}.md`, 'text/markdown');
                        break;

                    case 'html':
                        content = convertToHTML(conversation);
                        downloadFile(content, `${filename}.html`, 'text/html');
                        break;

                    case 'pdf':
                        // This would require a PDF generation library
                        console.warn('PDF export not implemented yet');
                        break;
                }
            } catch (error) {
                console.error('Export failed:', error);
            } finally {
                setIsLoading(false);
            }
        },
        [conversations, convertToHTML, convertToMarkdown, downloadFile],
    );

    const convertToMarkdown = useCallback((conversation: Conversation) => {
        let markdown = `# ${conversation.title}\n\n`;
        markdown += `*Created: ${conversation.createdAt.toLocaleDateString()}*\n`;
        markdown += `*Updated: ${conversation.updatedAt.toLocaleDateString()}*\n\n`;

        conversation.messages.forEach((message) => {
            const role = message.role === 'user' ? '👤 User' : '🤖 Assistant';
            markdown += `## ${role}\n`;
            markdown += `*${message.timestamp.toLocaleString()}*\n\n`;
            markdown += `${message.content}\n\n`;

            if (message.metadata?.tokens) {
                markdown += `*Tokens: ${message.metadata.tokens}*\n\n`;
            }

            markdown += '---\n\n';
        });

        return markdown;
    }, []);

    const convertToHTML = useCallback((conversation: Conversation) => {
        let html = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${conversation.title}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 40px; }
        .conversation { max-width: 800px; margin: 0 auto; }
        .message { margin: 20px 0; padding: 20px; border-radius: 8px; }
        .user { background: #f3f4f6; border-left: 4px solid #3b82f6; }
        .assistant { background: #fafafa; border-left: 4px solid #10b981; }
        .meta { font-size: 0.9em; color: #6b7280; margin-bottom: 10px; }
        pre { background: #1f2937; color: #f9fafb; padding: 12px; border-radius: 6px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="conversation">
        <h1>${conversation.title}</h1>
        <p class="meta">Created: ${conversation.createdAt.toLocaleDateString()}</p>
        <p class="meta">Updated: ${conversation.updatedAt.toLocaleDateString()}</p>
`;

        conversation.messages.forEach((message) => {
            const roleClass = message.role === 'user' ? 'user' : 'assistant';
            const roleDisplay = message.role === 'user' ? '👤 User' : '🤖 Assistant';

            html += `
        <div class="message ${roleClass}">
            <div class="meta">${roleDisplay} - ${message.timestamp.toLocaleString()}</div>
            <div>${message.content.replace(/\n/g, '<br>')}</div>
        </div>
`;
        });

        html += `
    </div>
</body>
</html>`;

        return html;
    }, []);

    const downloadFile = useCallback((content: string, filename: string, mimeType: string) => {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }, []);

    // Auto-save conversations
    useEffect(() => {
        if (autoSave && conversations.size > 0) {
            const timeoutId = setTimeout(() => {
                const conversationsData = Array.from(conversations.entries()).map(([id, conv]) => [
                    id,
                    {
                        ...conv,
                        createdAt: conv.createdAt.toISOString(),
                        updatedAt: conv.updatedAt.toISOString(),
                        messages: conv.messages.map((msg) => ({
                            ...msg,
                            timestamp: msg.timestamp.toISOString(),
                        })),
                    },
                ]);

                localStorage.setItem(storageKey, JSON.stringify(conversationsData));
            }, 1000);

            return () => clearTimeout(timeoutId);
        }
    }, [conversations, autoSave, storageKey]);

    // Load conversations from localStorage on mount
    useEffect(() => {
        if (autoSave) {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                try {
                    const parsedData = JSON.parse(saved);
                    const restoredConversations = new Map();

                    parsedData.forEach(([id, conv]: [string, unknown]) => {
                        const typedConv = conv as {
                            createdAt: string;
                            updatedAt: string;
                            messages: Array<{ timestamp: string }>;
                        };
                        restoredConversations.set(id, {
                            ...typedConv,
                            createdAt: new Date(typedConv.createdAt),
                            updatedAt: new Date(typedConv.updatedAt),
                            messages: typedConv.messages.map((msg) => ({
                                ...msg,
                                timestamp: new Date(msg.timestamp),
                            })),
                        });
                    });

                    setConversations(restoredConversations);
                } catch (e) {
                    console.warn('Failed to load saved conversations:', e);
                }
            }
        }
    }, [autoSave, storageKey]);

    return {
        conversations: getSortedConversations(),
        activeConversationId,
        sortOrder,
        searchQuery,
        isLoading,
        setSortOrder,
        setSearchQuery,
        newConversation,
        loadConversation,
        deleteConversation,
        addMessageToConversation,
        updateMessageInConversation,
        exportConversation,
        searchConversations,
    };
}
