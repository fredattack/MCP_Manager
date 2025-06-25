import apiClient from './client';

interface LoginCredentials {
    username: string;
    password: string;
}

interface TokenResponse {
    access_token: string;
    token_type: string;
    expires_in?: number;
}

interface User {
    id: string;
    email: string;
    username: string;
    is_active: boolean;
}

export class McpAuthService {
    private static readonly TOKEN_KEY = 'mcp_token';
    private static readonly TOKEN_EXPIRY_KEY = 'mcp_token_expiry';
    private static readonly USER_KEY = 'mcp_user';

    // Check if we have a valid token
    static isAuthenticated(): boolean {
        const token = localStorage.getItem(this.TOKEN_KEY);
        const expiry = localStorage.getItem(this.TOKEN_EXPIRY_KEY);

        if (!token) return false;

        if (expiry && new Date() > new Date(expiry)) {
            this.logout();
            return false;
        }

        return true;
    }

    // Get the stored token
    static getToken(): string | null {
        if (!this.isAuthenticated()) return null;
        return localStorage.getItem(this.TOKEN_KEY);
    }

    // Get the stored user info
    static getUser(): User | null {
        const userJson = localStorage.getItem(this.USER_KEY);
        return userJson ? JSON.parse(userJson) : null;
    }

    // Login with username/password
    static async login(credentials: LoginCredentials): Promise<User> {
        try {
            const response = await apiClient.post<TokenResponse>('/api/mcp/auth/login', {
                username: credentials.username,
                password: credentials.password,
            });

            const { access_token, expires_in } = response.data;

            // Store the token
            localStorage.setItem(this.TOKEN_KEY, access_token);

            // Calculate expiry if provided
            if (expires_in) {
                const expiryDate = new Date(Date.now() + expires_in * 1000);
                localStorage.setItem(this.TOKEN_EXPIRY_KEY, expiryDate.toISOString());
            }

            // Get user info
            const user = await this.getCurrentUser();
            localStorage.setItem(this.USER_KEY, JSON.stringify(user));

            return user;
        } catch (error) {
            console.error('MCP login failed:', error);
            throw new Error('Login failed. Please check your credentials.');
        }
    }

    // Login with pre-existing token (from env)
    static async loginWithToken(token: string): Promise<User> {
        try {
            localStorage.setItem(this.TOKEN_KEY, token);

            // Test the token by getting user info
            const user = await this.getCurrentUser();
            localStorage.setItem(this.USER_KEY, JSON.stringify(user));

            return user;
        } catch (error) {
            console.error('MCP token login failed:', error);
            localStorage.removeItem(this.TOKEN_KEY);
            throw new Error('Token authentication failed.');
        }
    }

    // Get current user from API
    static async getCurrentUser(): Promise<User> {
        const token = this.getToken();
        if (!token) throw new Error('No authentication token');

        const response = await apiClient.get<User>('/api/mcp/auth/me', {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });

        return response.data;
    }

    // Logout and clear stored data
    static logout(): void {
        localStorage.removeItem(this.TOKEN_KEY);
        localStorage.removeItem(this.TOKEN_EXPIRY_KEY);
        localStorage.removeItem(this.USER_KEY);
    }

    // Auto-login using environment token on app startup
    static async autoLogin(): Promise<User | null> {
        // First check if we already have a valid token
        if (this.isAuthenticated()) {
            return this.getUser();
        }

        // Try to use the token from environment
        const envToken = import.meta.env.VITE_MCP_API_TOKEN;
        if (envToken) {
            try {
                return await this.loginWithToken(envToken);
            } catch (error) {
                console.warn('Auto-login with env token failed:', error);
            }
        }

        return null;
    }
}

// Auth headers helper for API calls
export const getAuthHeaders = () => {
    const token = McpAuthService.getToken();
    return token ? { Authorization: `Bearer ${token}` } : {};
};
