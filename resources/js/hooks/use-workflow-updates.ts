import { WorkflowExecution, WorkflowStep } from '@/types';
import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';

export interface LogEntry {
    level: 'info' | 'warning' | 'error' | 'debug';
    message: string;
    context?: Record<string, unknown>;
    timestamp: string;
}

interface PusherError {
    type: string;
    error: string;
    status?: number;
}

interface WorkflowStatusEventData {
    id: number;
    workflow_id: number;
    status: string;
    started_at: string;
    completed_at: string | null;
    result: unknown;
}

interface StepCompletedEventData {
    id: number;
    execution_id: number;
    step_name: string;
    step_order: number;
    status: string;
    started_at: string;
    completed_at: string | null;
    duration: number | null;
    output: unknown;
    error_message: string | null;
}

interface LogEntryEventData {
    level: 'info' | 'warning' | 'error' | 'debug';
    message: string;
    context?: Record<string, unknown>;
    timestamp: string;
}

interface WorkflowUpdateCallbacks {
    onStatusUpdate?: (execution: WorkflowExecution) => void;
    onStepComplete?: (step: WorkflowStep) => void;
    onLogEntry?: (log: LogEntry) => void;
}

interface ConnectionStatus {
    isConnected: boolean;
    isConnecting: boolean;
    error: string | null;
}

export function useWorkflowUpdates(workflowId: string | number, callbacks?: WorkflowUpdateCallbacks) {
    const [connectionStatus, setConnectionStatus] = useState<ConnectionStatus>({
        isConnected: false,
        isConnecting: true,
        error: null,
    });
    const [logs, setLogs] = useState<LogEntry[]>([]);
    const channelRef = useRef<unknown>(null);
    const reconnectTimeoutRef = useRef<NodeJS.Timeout>();
    const reconnectAttemptsRef = useRef(0);
    const maxReconnectAttempts = 5;

    const addLog = useCallback(
        (log: LogEntry) => {
            setLogs((prev) => [...prev, log]);
            callbacks?.onLogEntry?.(log);
        },
        [callbacks],
    );

    const reconnect = useCallback(() => {
        if (reconnectAttemptsRef.current >= maxReconnectAttempts) {
            setConnectionStatus({
                isConnected: false,
                isConnecting: false,
                error: 'Failed to connect after multiple attempts. Please refresh the page.',
            });
            return;
        }

        reconnectAttemptsRef.current += 1;
        const delay = Math.min(1000 * Math.pow(2, reconnectAttemptsRef.current), 10000);

        reconnectTimeoutRef.current = setTimeout(() => {
            setConnectionStatus((prev) => ({
                ...prev,
                isConnecting: true,
                error: null,
            }));
        }, delay);
    }, []);

    useEffect(() => {
        if (!window.Echo) {
            console.error('Laravel Echo is not initialized');
            setConnectionStatus({
                isConnected: false,
                isConnecting: false,
                error: 'Real-time updates unavailable',
            });
            return;
        }

        const channelName = `workflows.${workflowId}`;
        const channel = window.Echo.private(channelName);
        channelRef.current = channel;

        // Connection status handlers
        channel.on('pusher:subscription_succeeded', () => {
            setConnectionStatus({
                isConnected: true,
                isConnecting: false,
                error: null,
            });
            reconnectAttemptsRef.current = 0;
        });

        channel.on('pusher:subscription_error', (error: PusherError) => {
            console.error('Subscription error:', error);
            setConnectionStatus({
                isConnected: false,
                isConnecting: false,
                error: 'Failed to subscribe to workflow updates',
            });
        });

        // Workflow status updates
        channel.listen('.workflow.status.updated', (data: WorkflowStatusEventData) => {
            const execution: WorkflowExecution = {
                id: data.id,
                workflow_id: data.workflow_id,
                status: data.status,
                started_at: data.started_at,
                completed_at: data.completed_at,
                result: data.result,
            };

            callbacks?.onStatusUpdate?.(execution);

            // Optionally reload page data when status changes to completed/failed
            if (data.status === 'completed' || data.status === 'failed') {
                router.reload({ only: ['workflow'] });
            }
        });

        // Step completion events
        channel.listen('.step.completed', (data: StepCompletedEventData) => {
            const step: WorkflowStep = {
                id: data.id,
                execution_id: data.execution_id,
                step_name: data.step_name,
                step_order: data.step_order,
                status: data.status,
                started_at: data.started_at,
                completed_at: data.completed_at,
                duration: data.duration,
                output: data.output,
                error_message: data.error_message,
            };

            callbacks?.onStepComplete?.(step);

            // Reload to get updated steps
            router.reload({ only: ['workflow'] });
        });

        // Log entries
        channel.listen('.log.entry.created', (data: LogEntryEventData) => {
            const logEntry: LogEntry = {
                level: data.level,
                message: data.message,
                context: data.context,
                timestamp: data.timestamp,
            };

            addLog(logEntry);
        });

        return () => {
            if (channelRef.current) {
                window.Echo.leave(channelName);
                channelRef.current = null;
            }

            if (reconnectTimeoutRef.current) {
                clearTimeout(reconnectTimeoutRef.current);
            }
        };
    }, [workflowId, callbacks, addLog]);

    const clearLogs = useCallback(() => {
        setLogs([]);
    }, []);

    return {
        connectionStatus,
        logs,
        clearLogs,
        reconnect,
    };
}
