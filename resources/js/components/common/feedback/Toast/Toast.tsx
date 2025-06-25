import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Notification } from '@/stores/notification-store';
import * as ToastPrimitive from '@radix-ui/react-toast';
import { AlertCircle, AlertTriangle, CheckCircle, Info, X } from 'lucide-react';
import { useEffect } from 'react';

interface ToastProps {
    notification: Notification;
    onClose: () => void;
}

const icons = {
    success: CheckCircle,
    error: AlertCircle,
    warning: AlertTriangle,
    info: Info,
};

const styles = {
    success: 'border-success bg-success/10 text-success-dark',
    error: 'border-danger bg-danger/10 text-danger-dark',
    warning: 'border-warning bg-warning/10 text-warning-dark',
    info: 'border-primary bg-primary/10 text-primary-dark',
};

export function Toast({ notification, onClose }: ToastProps) {
    const Icon = icons[notification.type];

    useEffect(() => {
        if (notification.autoClose !== false) {
            const duration = notification.duration || 5000;
            const timer = setTimeout(onClose, duration);
            return () => clearTimeout(timer);
        }
    }, [notification, onClose]);

    return (
        <ToastPrimitive.Root
            className={cn(
                'group shadow-atlassian pointer-events-auto relative flex w-full items-center justify-between space-x-2 overflow-hidden rounded-sm border p-4 pr-8 transition-all',
                'data-[state=open]:animate-in data-[state=closed]:animate-out data-[swipe=end]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full data-[state=open]:sm:slide-in-from-bottom-full data-[swipe=cancel]:translate-x-0 data-[swipe=end]:translate-x-[var(--radix-toast-swipe-end-x)] data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=move]:transition-none',
                styles[notification.type],
            )}
            duration={notification.autoClose === false ? Infinity : notification.duration || 5000}
        >
            <div className="flex items-start gap-3">
                <Icon className="mt-0.5 h-5 w-5 flex-shrink-0" />
                <div className="min-w-0 flex-1">
                    <ToastPrimitive.Title className="text-sm font-semibold">{notification.title}</ToastPrimitive.Title>
                    {notification.message && (
                        <ToastPrimitive.Description className="mt-1 text-sm opacity-90">{notification.message}</ToastPrimitive.Description>
                    )}
                    {notification.action && (
                        <Button variant="ghost" size="sm" className="mt-2 h-7 px-2" onClick={notification.action.onClick}>
                            {notification.action.label}
                        </Button>
                    )}
                </div>
            </div>

            <ToastPrimitive.Close asChild>
                <Button
                    variant="ghost"
                    size="sm"
                    className="absolute top-2 right-2 h-6 w-6 p-0 opacity-0 transition-opacity group-hover:opacity-100"
                    onClick={onClose}
                >
                    <X className="h-4 w-4" />
                </Button>
            </ToastPrimitive.Close>
        </ToastPrimitive.Root>
    );
}
