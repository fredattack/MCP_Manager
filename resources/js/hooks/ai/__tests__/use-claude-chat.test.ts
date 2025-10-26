import apiClient from '@/lib/api/client';
import { act, renderHook } from '@testing-library/react';
import type { AxiosRequestConfig } from 'axios';
import { useClaudeChat } from '../use-claude-chat';

// Mock the api client
jest.mock('@/lib/api/client');
const mockedApiClient = apiClient as jest.Mocked<typeof apiClient>;

// Mock localStorage
const localStorageMock = {
    getItem: jest.fn(),
    setItem: jest.fn(),
    clear: jest.fn(),
};
Object.defineProperty(window, 'localStorage', { value: localStorageMock });

describe('useClaudeChat', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        localStorageMock.clear();
    });

    test('initializes with default values', () => {
        const { result } = renderHook(() => useClaudeChat());

        expect(result.current.messages).toEqual([]);
        expect(result.current.isLoading).toBe(false);
        expect(result.current.error).toBeNull();
        expect(result.current.streamingMessageId).toBeNull();
        expect(result.current.currentModel).toBe('gpt-4');
    });

    test('initializes with custom options', () => {
        const initialMessages = [
            {
                id: '1',
                role: 'user' as const,
                content: 'Hello',
                timestamp: new Date(),
                status: 'sent' as const,
            },
        ];

        const { result } = renderHook(() =>
            useClaudeChat({
                initialMessages,
                defaultModel: 'claude-3-opus',
                enableStreaming: false,
                autoSave: false,
            }),
        );

        expect(result.current.messages).toEqual(initialMessages);
        expect(result.current.currentModel).toBe('claude-3-opus');
    });

    test('sends a message successfully', async () => {
        mockedApiClient.post.mockResolvedValueOnce({
            status: 200,
            statusText: 'OK',
            headers: {},
            config: {} as AxiosRequestConfig,
            data: {
                content: 'Hello! How can I help you?',
                model: 'gpt-4',
                usage: { total_tokens: 50 },
                metadata: { timestamp: new Date().toISOString() },
            },
        });

        const { result } = renderHook(() => useClaudeChat({ enableStreaming: false }));

        await act(async () => {
            await result.current.sendMessage('Hello AI!');
        });

        expect(result.current.messages).toHaveLength(2);
        expect(result.current.messages[0].role).toBe('user');
        expect(result.current.messages[0].content).toBe('Hello AI!');
        expect(result.current.messages[1].role).toBe('assistant');
        expect(result.current.messages[1].content).toBe('Hello! How can I help you?');
        expect(result.current.isLoading).toBe(false);
        expect(result.current.error).toBeNull();
    });

    test('handles API errors gracefully', async () => {
        mockedApiClient.post.mockRejectedValueOnce(new Error('Network error'));

        const { result } = renderHook(() => useClaudeChat({ enableStreaming: false }));

        await act(async () => {
            await result.current.sendMessage('Hello AI!');
        });

        expect(result.current.messages).toHaveLength(2);
        expect(result.current.messages[1].content).toBe('Sorry, I encountered an error. Please try again.');
        expect(result.current.messages[1].status).toBe('error');
        expect(result.current.error).toBe('Network error');
        expect(result.current.isLoading).toBe(false);
    });

    test('clears messages', () => {
        const { result } = renderHook(() => useClaudeChat());

        act(() => {
            result.current.sendMessage('Test message');
        });

        expect(result.current.messages.length).toBeGreaterThan(0);

        act(() => {
            result.current.clearMessages();
        });

        expect(result.current.messages).toEqual([]);
        expect(result.current.error).toBeNull();
        expect(result.current.streamingMessageId).toBeNull();
    });

    test('changes model', () => {
        const { result } = renderHook(() => useClaudeChat());

        expect(result.current.currentModel).toBe('gpt-4');

        act(() => {
            result.current.setCurrentModel('claude-3-opus');
        });

        expect(result.current.currentModel).toBe('claude-3-opus');
    });

    test('prevents sending empty messages', async () => {
        const { result } = renderHook(() => useClaudeChat());

        await act(async () => {
            await result.current.sendMessage('   ');
        });

        expect(result.current.messages).toHaveLength(0);
        expect(mockedApiClient.post).not.toHaveBeenCalled();
    });

    test('prevents sending while loading', async () => {
        mockedApiClient.post.mockImplementationOnce(() => new Promise((resolve) => setTimeout(resolve, 1000)));

        const { result } = renderHook(() => useClaudeChat({ enableStreaming: false }));

        act(() => {
            result.current.sendMessage('First message');
        });

        expect(result.current.isLoading).toBe(true);

        await act(async () => {
            await result.current.sendMessage('Second message');
        });

        expect(mockedApiClient.post).toHaveBeenCalledTimes(1);
    });

    test('saves messages to localStorage when autoSave is enabled', async () => {
        const { result } = renderHook(() => useClaudeChat({ autoSave: true }));

        await act(async () => {
            await result.current.sendMessage('Test message');
            // Wait for the debounced save
            await new Promise((resolve) => setTimeout(resolve, 1100));
        });

        expect(localStorageMock.setItem).toHaveBeenCalledWith('claude-chat-messages', expect.any(String));
    });

    test('loads messages from localStorage on mount', () => {
        const savedMessages = [
            {
                id: '1',
                role: 'user',
                content: 'Saved message',
                timestamp: new Date().toISOString(),
                status: 'sent',
            },
        ];

        localStorageMock.getItem.mockReturnValueOnce(JSON.stringify(savedMessages));

        const { result } = renderHook(() => useClaudeChat({ autoSave: true }));

        expect(result.current.messages).toHaveLength(1);
        expect(result.current.messages[0].content).toBe('Saved message');
    });
});
