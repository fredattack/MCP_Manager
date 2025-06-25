import { router } from '@inertiajs/react';
import { AxiosError, AxiosInstance } from 'axios';

export function setupInterceptors(client: AxiosInstance) {
    // Request interceptor for adding authentication
    client.interceptors.request.use(
        (config) => {
            // Add CSRF token if available
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                config.headers['X-CSRF-TOKEN'] = csrfToken;
            }

            return config;
        },
        (error) => {
            return Promise.reject(error);
        },
    );

    // Response interceptor for handling errors
    client.interceptors.response.use(
        (response) => {
            return response;
        },
        (error: AxiosError) => {
            if (error.response?.status === 401) {
                // Redirect to login on 401
                router.visit('/login');
            } else if (error.response?.status === 419) {
                // CSRF token mismatch - reload page
                window.location.reload();
            } else if (error.response && error.response.status >= 500) {
                // Server error - show notification
                console.error('Server error:', error);
            }

            return Promise.reject(error);
        },
    );
}
