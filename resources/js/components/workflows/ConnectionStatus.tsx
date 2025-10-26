import { MonologueButton } from '@/components/ui/MonologueButton';
import { AlertCircle, Wifi, WifiOff } from 'lucide-react';

interface Props {
    isConnected: boolean;
    isConnecting: boolean;
    error: string | null;
    onReconnect?: () => void;
}

export function ConnectionStatus({ isConnected, isConnecting, error, onReconnect }: Props) {
    if (isConnected) {
        return (
            <div className="flex items-center gap-2 text-xs text-green-500">
                <Wifi size={14} />
                <span>Connected</span>
            </div>
        );
    }

    if (isConnecting) {
        return (
            <div className="flex items-center gap-2 text-xs text-amber-500">
                <div className="h-3 w-3 animate-spin rounded-full border-2 border-amber-500 border-t-transparent" />
                <span>Connecting...</span>
            </div>
        );
    }

    return (
        <div className="rounded-lg border border-red-500/30 bg-red-500/10 p-3">
            <div className="flex items-start gap-3">
                <AlertCircle className="mt-0.5 shrink-0 text-red-500" size={18} />
                <div className="min-w-0 flex-1">
                    <div className="mb-1 flex items-center gap-2">
                        <WifiOff size={14} className="text-red-500" />
                        <p className="text-sm font-medium text-red-400">Connection Lost</p>
                    </div>
                    <p className="text-xs text-red-300/80">{error || 'Unable to connect to real-time updates. Retrying...'}</p>
                </div>
                {onReconnect && (
                    <MonologueButton variant="ghost" size="sm" onClick={onReconnect} className="text-red-400 hover:text-red-300">
                        Retry
                    </MonologueButton>
                )}
            </div>
        </div>
    );
}
