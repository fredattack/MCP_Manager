import axios from 'axios';
import { setupInterceptors } from './interceptors';

// MCP Server client for external integrations
export const mcpClient = axios.create({
    baseURL: import.meta.env.VITE_MCP_SERVER_URL || 'http://localhost:9978',
    timeout: 30000,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Laravel API client for internal APIs
const apiClient = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:3978',
    timeout: 30000,
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
});

setupInterceptors(apiClient);
setupInterceptors(mcpClient);

export default apiClient;
