import { create } from 'zustand';

export interface Notification {
    id: string;
    title: string;
    message?: string;
    type: 'success' | 'error' | 'warning' | 'info';
    timestamp: Date;
    read: boolean;
    autoClose?: boolean;
    duration?: number;
    action?: {
        label: string;
        onClick: () => void;
    };
}

interface NotificationStore {
    notifications: Notification[];
    unreadCount: number;

    addNotification: (notification: Omit<Notification, 'id' | 'timestamp' | 'read'>) => void;
    removeNotification: (id: string) => void;
    markAsRead: (id: string) => void;
    markAllAsRead: () => void;
    clearAll: () => void;
}

export const useNotificationStore = create<NotificationStore>((set, get) => ({
    notifications: [],
    unreadCount: 0,

    addNotification: (notification) => {
        const id = Math.random().toString(36).substring(2);
        const newNotification: Notification = {
            ...notification,
            id,
            timestamp: new Date(),
            read: false,
        };

        set((state) => ({
            notifications: [newNotification, ...state.notifications],
            unreadCount: state.unreadCount + 1,
        }));

        // Auto-remove notification if autoClose is enabled
        if (notification.autoClose !== false) {
            const duration = notification.duration || 5000;
            setTimeout(() => {
                get().removeNotification(id);
            }, duration);
        }
    },

    removeNotification: (id) => {
        set((state) => {
            const notification = state.notifications.find((n) => n.id === id);
            const unreadDecrement = notification && !notification.read ? 1 : 0;

            return {
                notifications: state.notifications.filter((n) => n.id !== id),
                unreadCount: Math.max(0, state.unreadCount - unreadDecrement),
            };
        });
    },

    markAsRead: (id) => {
        set((state) => ({
            notifications: state.notifications.map((n) => (n.id === id ? { ...n, read: true } : n)),
            unreadCount: Math.max(0, state.unreadCount - 1),
        }));
    },

    markAllAsRead: () => {
        set((state) => ({
            notifications: state.notifications.map((n) => ({ ...n, read: true })),
            unreadCount: 0,
        }));
    },

    clearAll: () => {
        set({
            notifications: [],
            unreadCount: 0,
        });
    },
}));
