import { Head, router } from '@inertiajs/react';
import { AlertCircle, CheckCircle, XCircle } from 'lucide-react';
import { useEffect, useState } from 'react';

import { IntegrationIcon } from '@/components/integrations/integration-icon';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { useNotificationStore } from '@/stores/notification-store';

interface GitConnection {
    id: number;
    provider: 'github' | 'gitlab';
    external_user_id: string;
    username?: string;
    email?: string;
    avatar_url?: string;
    scopes: string[];
    status: 'active' | 'expired' | 'error';
    expires_at?: string;
    created_at: string;
}

interface ConnectionsProps {
    connections?: GitConnection[];
}

export default function GitConnections({ connections = [] }: ConnectionsProps) {
    const [isConnecting, setIsConnecting] = useState<string | null>(null);
    const { addNotification } = useNotificationStore();

    // Handle OAuth callback success/error from URL params
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);

        if (params.get('github_connected') === 'true') {
            addNotification({
                title: 'GitHub Connected',
                message: 'Your GitHub account has been connected successfully.',
                type: 'success',
            });
            // Clean up URL
            window.history.replaceState({}, '', '/git/connections');
        }

        if (params.get('gitlab_connected') === 'true') {
            addNotification({
                title: 'GitLab Connected',
                message: 'Your GitLab account has been connected successfully.',
                type: 'success',
            });
            // Clean up URL
            window.history.replaceState({}, '', '/git/connections');
        }

        const errorParam = params.get('error');
        if (errorParam) {
            let errorMessage = 'An error occurred during authentication.';

            if (errorParam === 'invalid_token') {
                errorMessage = 'Invalid token. Authentication failed.';
            } else if (errorParam === 'expired_state') {
                errorMessage = 'Session expired. Please try again.';
            } else if (errorParam === 'rate_limit') {
                errorMessage = 'Rate limit exceeded. Too many requests. Please wait and try again.';
            } else if (errorParam === 'insufficient_scope') {
                errorMessage = 'Insufficient permissions required. The authorized scopes are insufficient for this application.';
            }

            addNotification({
                title: 'Connection Failed',
                message: errorMessage,
                type: 'error',
            });
            // Clean up URL
            window.history.replaceState({}, '', '/git/connections');
        }
    }, [addNotification]);

    const githubConnection = connections.find((c) => c.provider === 'github');
    const gitlabConnection = connections.find((c) => c.provider === 'gitlab');

    const handleConnect = async (provider: 'github' | 'gitlab') => {
        setIsConnecting(provider);
        try {
            const response = await fetch(`/api/git/${provider}/oauth/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const data = await response.json();

            if (data.auth_url) {
                // Ouvrir la fenêtre OAuth
                window.location.href = data.auth_url;
            } else if (data.error) {
                alert(`Erreur: ${data.message || data.error}`);
            }
        } catch (error) {
            console.error('OAuth start error:', error);
        } finally {
            setIsConnecting(null);
        }
    };

    const handleDisconnect = async (provider: 'github' | 'gitlab') => {
        if (!confirm(`Êtes-vous sûr de vouloir déconnecter ${provider === 'github' ? 'GitHub' : 'GitLab'} ?`)) {
            return;
        }

        try {
            const response = await fetch(`/api/git/${provider}/disconnect`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const data = await response.json();

            if (data.success) {
                addNotification({
                    title: `${provider === 'github' ? 'GitHub' : 'GitLab'} Disconnected`,
                    message: `Your ${provider === 'github' ? 'GitHub' : 'GitLab'} account has been disconnected successfully.`,
                    type: 'success',
                });

                // Reload page to refresh connection state
                router.reload();
            } else {
                throw new Error(data.message || 'Failed to disconnect');
            }
        } catch (error) {
            console.error('Disconnect error:', error);
            addNotification({
                title: 'Disconnection Failed',
                message: error instanceof Error ? error.message : 'An error occurred while disconnecting.',
                type: 'error',
            });
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'active':
                return <CheckCircle className="h-5 w-5 text-green-500" />;
            case 'expired':
                return <AlertCircle className="h-5 w-5 text-yellow-500" />;
            case 'error':
                return <XCircle className="h-5 w-5 text-red-500" />;
            default:
                return null;
        }
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'active':
                return <Badge variant="default">Connecté</Badge>;
            case 'expired':
                return <Badge variant="secondary">Expiré</Badge>;
            case 'error':
                return <Badge variant="destructive">Erreur</Badge>;
            default:
                return <Badge variant="outline">Inconnu</Badge>;
        }
    };

    const isTokenExpiringSoon = (expiresAt?: string): boolean => {
        if (!expiresAt) {
            return false;
        }

        const expirationDate = new Date(expiresAt);
        // Show warning if token expires in less than 30 minutes
        const thirtyMinutesFromNow = new Date(Date.now() + 30 * 60 * 1000);

        return expirationDate < thirtyMinutesFromNow;
    };

    return (
        <AppLayout>
            <Head title="Connexions Git" />

            <div className="container mx-auto py-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold">Connexions Git</h1>
                    <p className="text-muted-foreground mt-2">Connectez vos comptes GitHub et GitLab pour synchroniser vos repositories</p>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* GitHub Connection */}
                    <Card data-provider="github">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <IntegrationIcon service="github" size={32} />
                                    <CardTitle>GitHub</CardTitle>
                                </div>
                                {githubConnection && getStatusIcon(githubConnection.status)}
                            </div>
                            <CardDescription>Connectez votre compte GitHub pour accéder à vos repositories</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {githubConnection ? (
                                <div className="space-y-4">
                                    <div className="flex items-center gap-3">
                                        {githubConnection.avatar_url && (
                                            <img
                                                src={githubConnection.avatar_url}
                                                alt={githubConnection.username}
                                                className="h-12 w-12 rounded-full"
                                            />
                                        )}
                                        <div>
                                            <p className="font-medium">{githubConnection.username}</p>
                                            <p className="text-muted-foreground text-sm">{githubConnection.email}</p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-2">{getStatusBadge(githubConnection.status)}</div>

                                    {isTokenExpiringSoon(githubConnection.expires_at) && (
                                        <div className="rounded-md bg-yellow-50 p-3 text-sm dark:bg-yellow-900/20">
                                            <div className="flex items-center gap-2">
                                                <AlertCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-500" />
                                                <p className="text-yellow-800 dark:text-yellow-200">
                                                    Token expires soon. Please renew your connection.
                                                </p>
                                            </div>
                                            <Button variant="secondary" size="sm" onClick={() => handleConnect('github')} className="mt-2">
                                                Renew Connection
                                            </Button>
                                        </div>
                                    )}

                                    {githubConnection.scopes && githubConnection.scopes.length > 0 && (
                                        <div>
                                            <p className="mb-2 text-sm font-medium">Scopes autorisés:</p>
                                            <div className="flex flex-wrap gap-2" data-testid="github-scopes">
                                                {githubConnection.scopes.map((scope) => (
                                                    <Badge key={scope} variant="outline">
                                                        {scope}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </div>
                                    )}

                                    <div className="flex gap-2">
                                        <Button variant="outline" onClick={() => handleDisconnect('github')}>
                                            Déconnecter
                                        </Button>
                                        <Button variant="secondary" onClick={() => handleConnect('github')}>
                                            Reconnecter
                                        </Button>
                                    </div>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    <p className="text-muted-foreground text-sm">Aucun compte GitHub connecté</p>
                                    <Button onClick={() => handleConnect('github')} disabled={isConnecting === 'github'}>
                                        {isConnecting === 'github' ? 'Connexion...' : 'Connecter GitHub'}
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* GitLab Connection */}
                    <Card data-provider="gitlab">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <IntegrationIcon service="gitlab" size={32} />
                                    <CardTitle>GitLab</CardTitle>
                                </div>
                                {gitlabConnection && getStatusIcon(gitlabConnection.status)}
                            </div>
                            <CardDescription>Connectez votre compte GitLab pour accéder à vos repositories</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {gitlabConnection ? (
                                <div className="space-y-4">
                                    <div className="flex items-center gap-3">
                                        {gitlabConnection.avatar_url && (
                                            <img
                                                src={gitlabConnection.avatar_url}
                                                alt={gitlabConnection.username}
                                                className="h-12 w-12 rounded-full"
                                            />
                                        )}
                                        <div>
                                            <p className="font-medium">{gitlabConnection.username}</p>
                                            <p className="text-muted-foreground text-sm">{gitlabConnection.email}</p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-2">{getStatusBadge(gitlabConnection.status)}</div>

                                    {isTokenExpiringSoon(gitlabConnection.expires_at) && (
                                        <div className="rounded-md bg-yellow-50 p-3 text-sm dark:bg-yellow-900/20">
                                            <div className="flex items-center gap-2">
                                                <AlertCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-500" />
                                                <p className="text-yellow-800 dark:text-yellow-200">
                                                    Token expires soon. Please renew your connection.
                                                </p>
                                            </div>
                                            <Button variant="secondary" size="sm" onClick={() => handleConnect('gitlab')} className="mt-2">
                                                Renew Connection
                                            </Button>
                                        </div>
                                    )}

                                    {gitlabConnection.scopes && gitlabConnection.scopes.length > 0 && (
                                        <div>
                                            <p className="mb-2 text-sm font-medium">Scopes autorisés:</p>
                                            <div className="flex flex-wrap gap-2" data-testid="gitlab-scopes">
                                                {gitlabConnection.scopes.map((scope) => (
                                                    <Badge key={scope} variant="outline">
                                                        {scope}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </div>
                                    )}

                                    <div className="flex gap-2">
                                        <Button variant="outline" onClick={() => handleDisconnect('gitlab')}>
                                            Déconnecter
                                        </Button>
                                        <Button variant="secondary" onClick={() => handleConnect('gitlab')}>
                                            Reconnecter
                                        </Button>
                                    </div>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    <p className="text-muted-foreground text-sm">Aucun compte GitLab connecté</p>
                                    <Button onClick={() => handleConnect('gitlab')} disabled={isConnecting === 'gitlab'}>
                                        {isConnecting === 'gitlab' ? 'Connexion...' : 'Connecter GitLab'}
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
