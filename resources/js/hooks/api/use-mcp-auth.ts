import { useToast } from '@/hooks/ui/use-toast';
import { McpAuthService } from '@/lib/api/mcp-auth';
import { useEffect, useState } from 'react';

interface User {
    id: string;
    email: string;
    username: string;
    is_active: boolean;
}

interface UseMcpAuthReturn {
    user: User | null;
    isAuthenticated: boolean;
    isLoading: boolean;
    login: (username: string, password: string) => Promise<void>;
    logout: () => void;
    autoLogin: () => Promise<void>;
}

export function useMcpAuth(): UseMcpAuthReturn {
    const [user, setUser] = useState<User | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const { toast } = useToast();

    const isAuthenticated = !!user && McpAuthService.isAuthenticated();

    const login = async (username: string, password: string) => {
        setIsLoading(true);
        try {
            const userData = await McpAuthService.login({ username, password });
            setUser(userData);
            toast.success('Connected to MCP Server', `Welcome, ${userData.username}!`);
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Login failed';
            toast.error('MCP Server Connection Failed', message);
            throw error;
        } finally {
            setIsLoading(false);
        }
    };

    const logout = () => {
        McpAuthService.logout();
        setUser(null);
        toast.info('Disconnected from MCP Server', 'You have been logged out.');
    };

    const autoLogin = async () => {
        setIsLoading(true);
        try {
            const userData = await McpAuthService.autoLogin();
            if (userData) {
                setUser(userData);
                console.log('Auto-logged into MCP Server:', userData.username);
            }
        } catch (error) {
            console.warn('Auto-login failed:', error);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        autoLogin();
    }, []);

    return {
        user,
        isAuthenticated,
        isLoading,
        login,
        logout,
        autoLogin,
    };
}
