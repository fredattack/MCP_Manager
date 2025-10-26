import { router } from '@inertiajs/react';
import { AxiosError, AxiosInstance } from 'axios';

export function setupInterceptors(client: AxiosInstance) {
    // Request interceptor for adding authentication
    client.interceptors.request.use(
        (config) => {
            // Add CSRF token if available (refresh each time)
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                config.headers['X-CSRF-TOKEN'] = csrfToken;
            }

            // Ensure we always include credentials
            config.withCredentials = true;

            console.log('Making request to:', (config.baseURL || '') + (config.url || ''), 'with CSRF:', !!csrfToken);
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
            console.log('API Error:', error.response?.status, error.response?.statusText);

            if (error.response?.status === 302) {
                // Handle redirects - likely session expired
                console.warn('Redirect detected - refreshing page to restore session');
                window.location.reload();
            } else if (error.response?.status === 401) {
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
