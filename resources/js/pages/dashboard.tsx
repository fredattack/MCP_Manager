import { Button } from '@/components/ui/button';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    const [isLoading, setIsLoading] = useState(false);
    const [logs, setLogs] = useState<string[]>([]);
    const [isRunning, setIsRunning] = useState(false);
    const logContainerRef = useRef<HTMLDivElement>(null);
    const eventSourceRef = useRef<EventSource | null>(null);

    const startAdobeFetch = () => {
        setIsLoading(true);
        setLogs([]);
        setIsRunning(true);

        // Call the backend API to start the adobe-fetch script
        router.post('/adobe-fetch/execute', {}, {
            onSuccess: () => {
                setIsLoading(false);
                // Start listening for logs
                connectToLogStream();
            },
            onError: () => {
                setIsLoading(false);
                setIsRunning(false);
                setLogs(prev => [...prev, 'Error starting Adobe fetch script']);
            }
        });
    };

    const connectToLogStream = () => {
        // Close any existing connection
        if (eventSourceRef.current) {
            eventSourceRef.current.close();
        }

        // Connect to the log stream using Server-Sent Events
        const eventSource = new EventSource('/adobe-fetch/logs');
        eventSourceRef.current = eventSource;

        eventSource.onmessage = (event) => {
            // Check if this is a completion message
            if (event.data.includes('[Completed]')) {
                setIsRunning(false);
                eventSource.close();
                eventSourceRef.current = null;
            }

            setLogs(prev => [...prev, event.data]);

            // Auto-scroll to the bottom of the log container
            if (logContainerRef.current) {
                logContainerRef.current.scrollTop = logContainerRef.current.scrollHeight;
            }
        };

        eventSource.onerror = () => {
            // If the connection is closed, stop listening
            eventSource.close();
            eventSourceRef.current = null;
            setIsRunning(false);
        };
    };

    // Clean up the event source when the component unmounts
    useEffect(() => {
        return () => {
            if (eventSourceRef.current) {
                eventSourceRef.current.close();
            }
        };
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border p-4">
                        <h2 className="text-xl font-semibold mb-4">Adobe Invoice Fetcher</h2>
                        <p className="mb-4">Click the button below to start fetching invoices from Adobe.</p>
                        <Button
                            onClick={startAdobeFetch}
                            disabled={isLoading || isRunning}
                            className="w-full"
                        >
                            {isLoading ? 'Starting...' : isRunning ? 'Running...' : 'Fetch Adobe Invoices'}
                        </Button>
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[50vh] flex-1 overflow-hidden rounded-xl border md:min-h-min p-4">
                    <h2 className="text-xl font-semibold mb-4">Adobe Fetch Logs</h2>
                    <div
                        ref={logContainerRef}
                        className="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 h-[calc(100%-3rem)] overflow-y-auto font-mono text-sm"
                    >
                        {logs.length === 0 ? (
                            <p className="text-gray-500 dark:text-gray-400">No logs available. Start the Adobe fetch process to see logs.</p>
                        ) : (
                            logs.map((log, index) => (
                                <div key={index} className="mb-1">
                                    {log}
                                </div>
                            ))
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
