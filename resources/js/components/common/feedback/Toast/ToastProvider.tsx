import { useNotificationStore } from '@/stores/notification-store';
import * as ToastPrimitive from '@radix-ui/react-toast';
import { ReactNode } from 'react';
import { Toast } from './Toast';

interface ToastProviderProps {
    children: ReactNode;
}

export function ToastProvider({ children }: ToastProviderProps) {
    const { notifications, removeNotification } = useNotificationStore();

    return (
        <ToastPrimitive.Provider swipeDirection="right">
            {children}
            {notifications.map((notification) => (
                <Toast key={notification.id} notification={notification} onClose={() => removeNotification(notification.id)} />
            ))}
            <ToastPrimitive.Viewport className="fixed top-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:top-auto sm:right-0 sm:bottom-0 sm:flex-col md:max-w-[420px]" />
        </ToastPrimitive.Provider>
    );
}
