import apiClient from '@/lib/api/client';
import type { AIModel, ChatRequest, Message, MessageStatus, StreamChunk } from '@/types/ai/claude.types';
import { useCallback, useEffect, useRef, useState } from 'react';

interface UseChatOptions {
    initialMessages?: Message[];
    defaultModel?: AIModel;
    enableStreaming?: boolean;
    autoSave?: boolean;
}

export function useClaudeChat(options: UseChatOptions = {}) {
    const { initialMessages = [], defaultModel = 'gpt-4', enableStreaming = true, autoSave = true } = options;

    const [messages, setMessages] = useState<Message[]>(initialMessages);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [streamingMessageId, setStreamingMessageId] = useState<string | null>(null);
    const [currentModel, setCurrentModel] = useState<AIModel>(defaultModel);

    const abortControllerRef = useRef<AbortController | null>(null);

    const generateMessageId = useCallback(() => {
        return `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }, []);

    const createMessage = useCallback(
        (role: 'user' | 'assistant', content: string, status: MessageStatus = 'sent'): Message => ({
            id: generateMessageId(),
            role,
            content,
            timestamp: new Date(),
            status,
            metadata: {
                model: currentModel,
            },
        }),
        [generateMessageId, currentModel],
    );

    const updateMessage = useCallback((messageId: string, updates: Partial<Message>) => {
        console.log('🔄 [CLAUDE-CHAT] updateMessage called:', { messageId, updates });
        setMessages((prev) => prev.map((msg) => (msg.id === messageId ? { ...msg, ...updates } : msg)));
    }, []);

    const addMessage = useCallback((message: Message) => {
        console.log('➕ [CLAUDE-CHAT] addMessage called:', message);
        setMessages((prev) => [...prev, message]);
    }, []);

    const handleStreamingResponse = useCallback(
        async (messageId: string, request: ChatRequest, retryCount = 0) => {
            const MAX_RETRIES = 3;
            const RETRY_DELAY = 1000; // Start with 1 second

            console.log('🔄 [CLAUDE-CHAT] handleStreamingResponse started:', { messageId, request, retryCount });

            // Get CSRF token from meta tag
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('🔐 [CLAUDE-CHAT] CSRF token found:', !!token);

            // Get base URL from environment or default
            const baseURL = import.meta.env.VITE_API_URL || window.location.origin;
            const url = `${baseURL}/api/ai/chat`;
            console.log('🌐 [CLAUDE-CHAT] Making streaming request to:', url);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'text/event-stream',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'include',
                    body: JSON.stringify(request),
                    signal: abortControllerRef.current?.signal,
                });

                console.log('📡 [CLAUDE-CHAT] Fetch response received:', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok,
                    headers: Object.fromEntries(response.headers.entries()),
                });

                if (!response.ok) {
                    console.error('❌ [CLAUDE-CHAT] Streaming response failed:', response.status, response.statusText);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const reader = response.body?.getReader();
                if (!reader) {
                    throw new Error('No response body');
                }

                const decoder = new TextDecoder();
                let buffer = '';
                let fullContent = '';
                let hasReceivedData = false;

                try {
                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;

                        buffer += decoder.decode(value, { stream: true });
                        const lines = buffer.split('\n');
                        buffer = lines.pop() || '';

                        for (const line of lines) {
                            if (line.startsWith('data: ')) {
                                const data = line.slice(6).trim();
                                if (data === '[DONE]') break;

                                try {
                                    const chunk: StreamChunk = JSON.parse(data);
                                    // Log pour débugger le format
                                    console.log('Received chunk:', chunk);

                                    // Use standardized format
                                    const content = chunk.content || '';

                                    if (content) {
                                        hasReceivedData = true;
                                        fullContent += content;
                                        updateMessage(messageId, {
                                            content: fullContent,
                                            status: 'sent',
                                        });
                                    }
                                } catch (e) {
                                    console.warn('Failed to parse chunk:', data, e);
                                }
                            }
                        }
                    }
                } finally {
                    reader.releaseLock();
                }

                // If no data was received and we haven't exceeded retries, retry
                if (!hasReceivedData && retryCount < MAX_RETRIES) {
                    console.log(`🔁 [CLAUDE-CHAT] No data received, retrying... (${retryCount + 1}/${MAX_RETRIES})`);
                    await new Promise((resolve) => setTimeout(resolve, RETRY_DELAY * Math.pow(2, retryCount)));
                    return handleStreamingResponse(messageId, request, retryCount + 1);
                }

                updateMessage(messageId, { status: 'sent' });
            } catch (error) {
                // If it's an abort error, don't retry
                if (error instanceof Error && error.name === 'AbortError') {
                    throw error;
                }

                // If we haven't exceeded retries, retry with exponential backoff
                if (retryCount < MAX_RETRIES) {
                    console.log(`🔁 [CLAUDE-CHAT] Stream error, retrying... (${retryCount + 1}/${MAX_RETRIES})`, error);
                    await new Promise((resolve) => setTimeout(resolve, RETRY_DELAY * Math.pow(2, retryCount)));
                    return handleStreamingResponse(messageId, request, retryCount + 1);
                }

                // If all retries failed, throw the error
                throw error;
            }
        },
        [updateMessage],
    );

    const handleRegularResponse = useCallback(
        async (messageId: string, request: ChatRequest) => {
            console.log('📨 [CLAUDE-CHAT] handleRegularResponse started:', { messageId, request });

            // Get CSRF token from meta tag
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('🔐 [CLAUDE-CHAT] CSRF token found:', !!token);
            console.log('🌐 [CLAUDE-CHAT] API client baseURL:', apiClient.defaults.baseURL);

            const response = await apiClient.post('/api/ai/chat', request, {
                signal: abortControllerRef.current?.signal,
                headers: {
                    'X-CSRF-TOKEN': token || '',
                },
            });

            console.log('📡 [CLAUDE-CHAT] API response received:', {
                status: response.status,
                statusText: response.statusText,
                headers: response.headers,
                data: response.data,
            });

            const data = response.data;

            // Use standardized format
            const content = data.content || 'No response received';

            console.log('📝 [CLAUDE-CHAT] Extracted content:', {
                contentFound: !!content,
                contentLength: content?.length,
                contentPreview: content?.substring(0, 100) + '...',
                dataKeys: Object.keys(data),
            });

            updateMessage(messageId, {
                content,
                status: 'sent',
                metadata: {
                    model: currentModel,
                    tokens: data.usage?.total_tokens,
                    ...data.metadata,
                },
            });

            console.log('✅ [CLAUDE-CHAT] Message updated successfully');
        },
        [updateMessage, currentModel],
    );

    const sendMessage = useCallback(
        async (content: string, model?: AIModel) => {
            console.log('🚀 [CLAUDE-CHAT] sendMessage called with:', { content: content.trim(), model, isLoading });

            if (!content.trim() || isLoading) {
                console.log('❌ [CLAUDE-CHAT] sendMessage blocked:', { isEmpty: !content.trim(), isLoading });
                return;
            }

            const useModel = model || currentModel;
            const userMessage = createMessage('user', content.trim());
            const assistantMessage = createMessage('assistant', '', 'sending');

            console.log('📝 [CLAUDE-CHAT] Created messages:', { userMessage, assistantMessage, useModel });

            setIsLoading(true);
            setError(null);
            addMessage(userMessage);
            addMessage(assistantMessage);
            setStreamingMessageId(assistantMessage.id);

            console.log('📊 [CLAUDE-CHAT] State updated:', { isLoading: true, streamingMessageId: assistantMessage.id });

            // Cancel any ongoing request
            if (abortControllerRef.current) {
                abortControllerRef.current.abort();
            }

            abortControllerRef.current = new AbortController();

            try {
                const chatRequest: ChatRequest = {
                    messages: [...messages, userMessage].map((msg) => ({
                        role: msg.role,
                        content: msg.content,
                    })),
                    model: useModel,
                    temperature: 0.7,
                    max_tokens: 4000,
                    stream: enableStreaming,
                };

                console.log('📋 [CLAUDE-CHAT] Chat request prepared:', { chatRequest, enableStreaming });

                if (enableStreaming) {
                    console.log('🔄 [CLAUDE-CHAT] Using streaming response');
                    await handleStreamingResponse(assistantMessage.id, chatRequest);
                } else {
                    console.log('📨 [CLAUDE-CHAT] Using regular response');
                    await handleRegularResponse(assistantMessage.id, chatRequest);
                }
            } catch (error: unknown) {
                console.error('❌ [CLAUDE-CHAT] Error in sendMessage:', error);

                if (error instanceof Error && error.name === 'AbortError') {
                    console.log('🛑 [CLAUDE-CHAT] Request was aborted');
                    updateMessage(assistantMessage.id, {
                        content: 'Request cancelled',
                        status: 'error',
                    });
                } else {
                    console.error('💥 [CLAUDE-CHAT] Unexpected error:', error);
                    const errorMessage = error instanceof Error ? error.message : 'Failed to send message';
                    setError(errorMessage);
                    updateMessage(assistantMessage.id, {
                        content: 'Sorry, I encountered an error. Please try again.',
                        status: 'error',
                    });
                }
            } finally {
                console.log('🏁 [CLAUDE-CHAT] sendMessage completed, cleaning up');
                setIsLoading(false);
                setStreamingMessageId(null);
                abortControllerRef.current = null;
            }
        },
        [
            messages,
            isLoading,
            currentModel,
            createMessage,
            addMessage,
            updateMessage,
            enableStreaming,
            handleRegularResponse,
            handleStreamingResponse,
        ],
    );

    const regenerateMessage = useCallback(
        async (messageId: string) => {
            const messageIndex = messages.findIndex((msg) => msg.id === messageId);
            if (messageIndex === -1 || messages[messageIndex].role !== 'assistant') return;

            const previousMessages = messages.slice(0, messageIndex);
            const lastUserMessage = previousMessages.filter((msg) => msg.role === 'user').pop();

            if (lastUserMessage) {
                // Remove the assistant message and regenerate
                setMessages((prev) => prev.filter((msg) => msg.id !== messageId));
                await sendMessage(lastUserMessage.content);
            }
        },
        [messages, sendMessage],
    );

    const clearMessages = useCallback(() => {
        setMessages([]);
        setError(null);
        setStreamingMessageId(null);
    }, []);

    const cancelGeneration = useCallback(() => {
        if (abortControllerRef.current) {
            abortControllerRef.current.abort();
        }
    }, []);

    // Auto-save functionality
    useEffect(() => {
        if (autoSave && messages.length > 0) {
            const timeoutId = setTimeout(() => {
                localStorage.setItem('claude-chat-messages', JSON.stringify(messages));
            }, 1000);

            return () => clearTimeout(timeoutId);
        }
    }, [messages, autoSave]);

    // Load messages from localStorage on mount
    useEffect(() => {
        if (autoSave && messages.length === 0) {
            const saved = localStorage.getItem('claude-chat-messages');
            if (saved) {
                try {
                    const parsedMessages = JSON.parse(saved);
                    setMessages(
                        parsedMessages.map((msg: Message) => ({
                            ...msg,
                            timestamp: new Date(msg.timestamp),
                        })),
                    );
                } catch (e) {
                    console.warn('Failed to load saved messages:', e);
                }
            }
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [autoSave]);

    return {
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
        updateMessage,
    };
}
