import { useEffect, useState, useRef, useCallback } from 'react';
import { WebSocketMessage } from '@/types/mcp.types';

interface UseMcpWebSocketOptions {
    onIntegrationUpdate?: (integrationId: string, data: any) => void;
    onServerStatus?: (status: any) => void;
    onError?: (error: string) => void;
    reconnectDelay?: number;
    maxReconnectAttempts?: number;
}

interface UseMcpWebSocketReturn {
    isConnected: boolean;
    lastMessage: WebSocketMessage | null;
    sendMessage: (message: any) => void;
    reconnect: () => void;
    connectionState: 'connecting' | 'connected' | 'disconnected' | 'error';
}

export function useMcpWebSocket({
    onIntegrationUpdate,
    onServerStatus,
    onError,
    reconnectDelay = 3000,
    maxReconnectAttempts = 5,
}: UseMcpWebSocketOptions = {}): UseMcpWebSocketReturn {
    const [isConnected, setIsConnected] = useState(false);
    const [connectionState, setConnectionState] = useState<UseMcpWebSocketReturn['connectionState']>('disconnected');
    const [lastMessage, setLastMessage] = useState<WebSocketMessage | null>(null);
    
    const wsRef = useRef<WebSocket | null>(null);
    const reconnectTimeoutRef = useRef<NodeJS.Timeout | null>(null);
    const reconnectAttemptsRef = useRef(0);
    const mountedRef = useRef(true);

    const getWebSocketUrl = useCallback(() => {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const host = window.location.host;
        return `${protocol}//${host}/ws/mcp-status`;
    }, []);

    const connect = useCallback(() => {
        if (!mountedRef.current) return;
        if (wsRef.current?.readyState === WebSocket.OPEN) return;

        try {
            setConnectionState('connecting');
            const ws = new WebSocket(getWebSocketUrl());
            
            ws.onopen = () => {
                if (!mountedRef.current) return;
                
                console.log('MCP WebSocket connected');
                setIsConnected(true);
                setConnectionState('connected');
                reconnectAttemptsRef.current = 0;
                
                // Send authentication if needed
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    ws.send(JSON.stringify({
                        type: 'auth',
                        token: csrfToken,
                    }));
                }
            };

            ws.onmessage = (event) => {
                if (!mountedRef.current) return;
                
                try {
                    const message: WebSocketMessage = JSON.parse(event.data);
                    setLastMessage(message);
                    
                    switch (message.type) {
                        case 'integration_update':
                            if (onIntegrationUpdate && message.integrationId) {
                                onIntegrationUpdate(message.integrationId, message.data);
                            }
                            break;
                        case 'server_status':
                            if (onServerStatus) {
                                onServerStatus(message.data);
                            }
                            break;
                        case 'error':
                            if (onError) {
                                onError(message.data.error || 'Unknown error');
                            }
                            break;
                    }
                } catch (error) {
                    console.error('Failed to parse WebSocket message:', error);
                }
            };

            ws.onerror = (error) => {
                console.error('MCP WebSocket error:', error);
                setConnectionState('error');
                if (onError) {
                    onError('WebSocket connection error');
                }
            };

            ws.onclose = (event) => {
                if (!mountedRef.current) return;
                
                console.log('MCP WebSocket disconnected', event.code, event.reason);
                setIsConnected(false);
                setConnectionState('disconnected');
                wsRef.current = null;
                
                // Attempt to reconnect if not a normal closure
                if (event.code !== 1000 && reconnectAttemptsRef.current < maxReconnectAttempts) {
                    reconnectAttemptsRef.current++;
                    console.log(`Attempting to reconnect (${reconnectAttemptsRef.current}/${maxReconnectAttempts})...`);
                    
                    reconnectTimeoutRef.current = setTimeout(() => {
                        if (mountedRef.current) {
                            connect();
                        }
                    }, reconnectDelay * Math.min(reconnectAttemptsRef.current, 3)); // Exponential backoff
                }
            };

            wsRef.current = ws;
        } catch (error) {
            console.error('Failed to create WebSocket connection:', error);
            setConnectionState('error');
            if (onError) {
                onError('Failed to establish WebSocket connection');
            }
        }
    }, [getWebSocketUrl, onIntegrationUpdate, onServerStatus, onError, reconnectDelay, maxReconnectAttempts]);

    const disconnect = useCallback(() => {
        if (reconnectTimeoutRef.current) {
            clearTimeout(reconnectTimeoutRef.current);
            reconnectTimeoutRef.current = null;
        }
        
        if (wsRef.current) {
            wsRef.current.close(1000, 'User initiated disconnect');
            wsRef.current = null;
        }
        
        setIsConnected(false);
        setConnectionState('disconnected');
    }, []);

    const sendMessage = useCallback((message: any) => {
        if (wsRef.current?.readyState === WebSocket.OPEN) {
            wsRef.current.send(JSON.stringify(message));
        } else {
            console.warn('Cannot send message: WebSocket is not connected');
        }
    }, []);

    const reconnect = useCallback(() => {
        disconnect();
        reconnectAttemptsRef.current = 0;
        connect();
    }, [connect, disconnect]);

    useEffect(() => {
        mountedRef.current = true;
        connect();

        // Reconnect on visibility change
        const handleVisibilityChange = () => {
            if (document.visibilityState === 'visible' && !isConnected) {
                reconnect();
            }
        };

        // Reconnect on online event
        const handleOnline = () => {
            if (!isConnected) {
                reconnect();
            }
        };

        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('online', handleOnline);

        return () => {
            mountedRef.current = false;
            document.removeEventListener('visibilitychange', handleVisibilityChange);
            window.removeEventListener('online', handleOnline);
            disconnect();
        };
    }, []); // Only run on mount/unmount

    // Ping to keep connection alive
    useEffect(() => {
        if (!isConnected) return;

        const pingInterval = setInterval(() => {
            if (wsRef.current?.readyState === WebSocket.OPEN) {
                wsRef.current.send(JSON.stringify({ type: 'ping' }));
            }
        }, 30000); // Ping every 30 seconds

        return () => clearInterval(pingInterval);
    }, [isConnected]);

    return {
        isConnected,
        lastMessage,
        sendMessage,
        reconnect,
        connectionState,
    };
}