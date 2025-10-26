import { MonologueButton } from '@/components/ui/MonologueButton';
import { LogEntry } from '@/hooks/use-workflow-updates';
import { ChevronDown, ChevronUp, Download, Pause, Play } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';

interface Props {
    logs: LogEntry[];
    isLive?: boolean;
    maxHeight?: string;
}

type LogLevel = 'info' | 'warning' | 'error' | 'debug' | 'all';

const LOG_COLORS: Record<Exclude<LogLevel, 'all'>, string> = {
    info: 'text-blue-400',
    warning: 'text-amber-400',
    error: 'text-red-400',
    debug: 'text-gray-400',
};

export function LiveLogViewer({ logs, isLive = true, maxHeight = '500px' }: Props) {
    const [selectedLevel, setSelectedLevel] = useState<LogLevel>('all');
    const [autoScroll, setAutoScroll] = useState(true);
    const [isExpanded, setIsExpanded] = useState(true);
    const logContainerRef = useRef<HTMLDivElement>(null);
    const [userHasScrolled, setUserHasScrolled] = useState(false);

    const filteredLogs = useMemo(() => {
        if (selectedLevel === 'all') {
            return logs;
        }
        return logs.filter((log) => log.level === selectedLevel);
    }, [logs, selectedLevel]);

    // Auto-scroll to bottom when new logs arrive
    useEffect(() => {
        if (autoScroll && !userHasScrolled && logContainerRef.current) {
            logContainerRef.current.scrollTop = logContainerRef.current.scrollHeight;
        }
    }, [filteredLogs, autoScroll, userHasScrolled]);

    const handleScroll = () => {
        if (!logContainerRef.current) {
            return;
        }

        const { scrollTop, scrollHeight, clientHeight } = logContainerRef.current;
        const isAtBottom = scrollHeight - scrollTop - clientHeight < 50;

        if (!isAtBottom) {
            setUserHasScrolled(true);
            setAutoScroll(false);
        } else {
            setUserHasScrolled(false);
            setAutoScroll(true);
        }
    };

    const jumpToLatest = () => {
        if (logContainerRef.current) {
            logContainerRef.current.scrollTop = logContainerRef.current.scrollHeight;
            setAutoScroll(true);
            setUserHasScrolled(false);
        }
    };

    const downloadLogs = () => {
        const logText = filteredLogs.map((log) => `[${log.timestamp}] [${log.level.toUpperCase()}] ${log.message}`).join('\n');

        const blob = new Blob([logText], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `workflow-logs-${new Date().toISOString()}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    const formatTime = (timestamp: string) => {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            fractionalSecondDigits: 3,
        });
    };

    return (
        <div className="bg-monologue-neutral-800 border-monologue-border-default overflow-hidden rounded-lg border">
            {/* Header */}
            <div className="border-monologue-border-default bg-monologue-neutral-900 flex items-center justify-between border-b px-4 py-3">
                <div className="flex items-center gap-2">
                    <button
                        onClick={() => setIsExpanded(!isExpanded)}
                        className="text-gray-400 transition-colors hover:text-gray-200"
                        aria-label={isExpanded ? 'Collapse logs' : 'Expand logs'}
                    >
                        {isExpanded ? <ChevronUp size={20} /> : <ChevronDown size={20} />}
                    </button>
                    <h3 className="font-monologue-serif text-lg text-gray-200">Live Logs</h3>
                    {isLive && (
                        <span className="flex items-center gap-1.5 text-xs text-green-500">
                            <span className="h-2 w-2 animate-pulse rounded-full bg-green-500" />
                            Live
                        </span>
                    )}
                    <span className="text-xs text-gray-500">
                        {filteredLogs.length} {filteredLogs.length === 1 ? 'entry' : 'entries'}
                    </span>
                </div>

                <div className="flex items-center gap-2">
                    {/* Log level filters */}
                    <div className="mr-2 flex items-center gap-1">
                        {(['all', 'info', 'warning', 'error', 'debug'] as LogLevel[]).map((level) => (
                            <button
                                key={level}
                                onClick={() => setSelectedLevel(level)}
                                className={`rounded px-2.5 py-1 text-xs transition-all ${
                                    selectedLevel === level
                                        ? 'border border-cyan-500/50 bg-cyan-500/20 text-cyan-400'
                                        : 'hover:bg-monologue-neutral-700 text-gray-500 hover:text-gray-300'
                                }`}
                            >
                                {level.charAt(0).toUpperCase() + level.slice(1)}
                            </button>
                        ))}
                    </div>

                    <MonologueButton
                        variant="ghost"
                        size="sm"
                        onClick={() => setAutoScroll(!autoScroll)}
                        leftIcon={autoScroll ? <Pause size={14} /> : <Play size={14} />}
                        title={autoScroll ? 'Pause auto-scroll' : 'Resume auto-scroll'}
                    >
                        {autoScroll ? 'Pause' : 'Resume'}
                    </MonologueButton>

                    <MonologueButton variant="ghost" size="sm" onClick={downloadLogs} disabled={filteredLogs.length === 0} title="Download logs">
                        <Download size={14} />
                    </MonologueButton>
                </div>
            </div>

            {/* Logs Container */}
            {isExpanded && (
                <div className="relative">
                    <div
                        ref={logContainerRef}
                        onScroll={handleScroll}
                        className="overflow-y-auto bg-black p-4 font-mono text-sm"
                        style={{ maxHeight }}
                    >
                        {filteredLogs.length === 0 ? (
                            <p className="py-8 text-center text-gray-500">No logs yet. Waiting for workflow execution...</p>
                        ) : (
                            <div className="space-y-1">
                                {filteredLogs.map((log, index) => (
                                    <div key={index} className="flex items-start gap-3 rounded px-2 py-1 transition-colors hover:bg-gray-900/50">
                                        <span className="w-24 shrink-0 text-xs text-gray-600">{formatTime(log.timestamp)}</span>
                                        <span className={`${LOG_COLORS[log.level]} w-16 shrink-0 text-xs font-semibold uppercase`}>
                                            [{log.level}]
                                        </span>
                                        <span className="flex-1 break-all text-gray-300">{log.message}</span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Jump to latest button */}
                    {!autoScroll && userHasScrolled && (
                        <div className="absolute right-4 bottom-4">
                            <MonologueButton variant="primary" size="sm" onClick={jumpToLatest} className="shadow-lg">
                                Jump to Latest
                            </MonologueButton>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
