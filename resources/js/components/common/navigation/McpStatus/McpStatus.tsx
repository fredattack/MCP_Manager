import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useMcpAuth } from '@/hooks/api/use-mcp-auth';
import { Settings, User, Wifi, WifiOff } from 'lucide-react';
import { useState } from 'react';

export function McpStatus() {
    const { user, isAuthenticated, isLoading, login, logout } = useMcpAuth();
    const [showLoginDialog, setShowLoginDialog] = useState(false);
    const [loginForm, setLoginForm] = useState({
        username: '',
        password: '',
    });
    const [isLoggingIn, setIsLoggingIn] = useState(false);

    const handleLogin = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoggingIn(true);

        try {
            await login(loginForm.username, loginForm.password);
            setShowLoginDialog(false);
            setLoginForm({ username: '', password: '' });
        } catch {
            // Error is handled by the hook
        } finally {
            setIsLoggingIn(false);
        }
    };

    const handleAutoLogin = async () => {
        const envUser = import.meta.env.VITE_MCP_SERVER_USER || 'admin@mcp-server.com';
        const envPassword = import.meta.env.VITE_MCP_SERVER_PASSWORD || 'Admin@123!';

        setIsLoggingIn(true);
        try {
            await login(envUser, envPassword);
        } catch {
            // Error is handled by the hook
        } finally {
            setIsLoggingIn(false);
        }
    };

    if (isLoading) {
        return (
            <div className="flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 dark:bg-gray-800">
                <div className="h-3 w-3 animate-spin rounded-full border border-gray-400 border-t-transparent" />
                <span className="text-xs text-gray-600 dark:text-gray-400">Connecting...</span>
            </div>
        );
    }

    return (
        <div className="flex items-center gap-2">
            {isAuthenticated ? (
                <div className="flex items-center gap-2">
                    <Badge variant="default" className="bg-success text-success-foreground">
                        <Wifi className="mr-1 h-3 w-3" />
                        MCP Connected
                    </Badge>
                    <div className="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                        <User className="h-3 w-3" />
                        {user?.username}
                    </div>
                    <Button variant="ghost" size="sm" onClick={logout} className="h-6 px-2 text-xs">
                        Disconnect
                    </Button>
                </div>
            ) : (
                <div className="flex items-center gap-2">
                    <Badge variant="secondary" className="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <WifiOff className="mr-1 h-3 w-3" />
                        MCP Offline
                    </Badge>

                    <Dialog open={showLoginDialog} onOpenChange={setShowLoginDialog}>
                        <DialogTrigger asChild>
                            <Button variant="outline" size="sm" className="h-6 px-2 text-xs">
                                <Settings className="mr-1 h-3 w-3" />
                                Connect
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Connect to MCP Server</DialogTitle>
                            </DialogHeader>

                            <form onSubmit={handleLogin} className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="username">Username</Label>
                                    <Input
                                        id="username"
                                        type="email"
                                        placeholder="admin@mcp-server.com"
                                        value={loginForm.username}
                                        onChange={(e) => setLoginForm((prev) => ({ ...prev, username: e.target.value }))}
                                        required
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password">Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        placeholder="Admin@123!"
                                        value={loginForm.password}
                                        onChange={(e) => setLoginForm((prev) => ({ ...prev, password: e.target.value }))}
                                        required
                                    />
                                </div>

                                <div className="flex gap-2">
                                    <Button type="submit" className="flex-1" disabled={isLoggingIn}>
                                        {isLoggingIn ? 'Connecting...' : 'Connect'}
                                    </Button>
                                    <Button type="button" variant="outline" onClick={handleAutoLogin} disabled={isLoggingIn}>
                                        Use Default
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            )}
        </div>
    );
}
