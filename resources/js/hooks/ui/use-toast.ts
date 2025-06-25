import { useNotificationStore } from '@/stores/notification-store';

export function useToast() {
    const { addNotification } = useNotificationStore();

    const toast = {
        success: (title: string, message?: string, options?: { action?: { label: string; onClick: () => void } }) => {
            addNotification({
                type: 'success',
                title,
                message,
                ...options,
            });
        },

        error: (title: string, message?: string, options?: { action?: { label: string; onClick: () => void }; autoClose?: boolean }) => {
            addNotification({
                type: 'error',
                title,
                message,
                autoClose: false, // Errors don't auto-close by default
                ...options,
            });
        },

        warning: (title: string, message?: string, options?: { action?: { label: string; onClick: () => void } }) => {
            addNotification({
                type: 'warning',
                title,
                message,
                ...options,
            });
        },

        info: (title: string, message?: string, options?: { action?: { label: string; onClick: () => void } }) => {
            addNotification({
                type: 'info',
                title,
                message,
                ...options,
            });
        },
    };

    return { toast };
}
