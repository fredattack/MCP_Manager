import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { McpServer } from '@/types/mcp.types';
import { Head, router, useForm } from '@inertiajs/react';
import { AlertCircle, CheckCircle, Info, Key, Loader2, RefreshCw, Server, Shield, Trash2, WifiOff } from 'lucide-react';
import React, { useState } from 'react';

interface Props {
    server: McpServer | null;
}

export default function ServerConfig({ server }: Props) {
    const [isTesting, setIsTesting] = useState(false);
    const [testResult, setTestResult] = useState<{ success: boolean; message: string } | null>(null);
    const [isDisconnecting, setIsDisconnecting] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: server?.name || '',
        url: server?.url || '',
        ssl_certificate: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/mcp/server/config', {
            onSuccess: () => {
                reset('ssl_certificate');
                setTestResult({ success: true, message: 'Server configured successfully!' });
            },
            onError: () => {
                setTestResult({ success: false, message: 'Failed to configure server. Please check your settings.' });
            },
        });
    };

    const testConnection = async () => {
        setIsTesting(true);
        setTestResult(null);

        try {
            const response = await fetch('/mcp/server/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const result = await response.json();

            setTestResult({
                success: result.success,
                message: result.success ? `Connected! Latency: ${result.status?.latency}ms` : result.error || 'Connection failed',
            });
        } catch {
            setTestResult({
                success: false,
                message: 'Failed to test connection',
            });
        } finally {
            setIsTesting(false);
        }
    };

    const handleDisconnect = () => {
        setIsDisconnecting(true);
        router.post(
            '/mcp/server/disconnect',
            {},
            {
                onFinish: () => setIsDisconnecting(false),
            },
        );
    };

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this server configuration? This will remove all associated integrations.')) {
            router.delete('/mcp/server');
        }
    };

    const getStatusBadge = () => {
        if (!server) return null;

        switch (server.status) {
            case 'active':
                return <Badge className="bg-green-500">Active</Badge>;
            case 'error':
                return <Badge variant="destructive">Error</Badge>;
            default:
                return <Badge variant="secondary">Inactive</Badge>;
        }
    };

    return (
        <AppLayout>
            <Head title="MCP Server Configuration" />

            <div className="container mx-auto max-w-4xl py-8">
                <div className="mb-8">
                    <h1 className="mb-2 text-3xl font-bold">MCP Server Configuration</h1>
                    <p className="text-muted-foreground">Configure and manage your MCP server connection</p>
                </div>

                <div className="space-y-6">
                    {/* Current Server Status */}
                    {server && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between">
                                    <span className="flex items-center gap-2">
                                        <Server className="h-5 w-5" />
                                        Current Server Status
                                    </span>
                                    {getStatusBadge()}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="mb-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-muted-foreground text-sm">Server Name</p>
                                        <p className="font-semibold">{server.name}</p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground text-sm">URL</p>
                                        <p className="font-semibold">{server.url}</p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground text-sm">Connection Status</p>
                                        <p className="flex items-center gap-2 font-semibold">
                                            {server.health.connected ? (
                                                <>
                                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                                    Connected
                                                </>
                                            ) : (
                                                <>
                                                    <WifiOff className="h-4 w-4 text-red-500" />
                                                    Disconnected
                                                </>
                                            )}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-muted-foreground text-sm">Configured At</p>
                                        <p className="font-semibold">{new Date(server.configured_at).toLocaleDateString()}</p>
                                    </div>
                                </div>

                                {server.health.error && (
                                    <Alert variant="destructive" className="mb-4">
                                        <AlertCircle className="h-4 w-4" />
                                        <AlertDescription>{server.health.error}</AlertDescription>
                                    </Alert>
                                )}

                                <div className="flex gap-2">
                                    <Button onClick={testConnection} disabled={isTesting} variant="outline">
                                        {isTesting ? (
                                            <>
                                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                Testing...
                                            </>
                                        ) : (
                                            <>
                                                <RefreshCw className="mr-2 h-4 w-4" />
                                                Test Connection
                                            </>
                                        )}
                                    </Button>

                                    {server.status === 'active' && (
                                        <Button onClick={handleDisconnect} disabled={isDisconnecting} variant="secondary">
                                            {isDisconnecting ? (
                                                <>
                                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                    Disconnecting...
                                                </>
                                            ) : (
                                                <>
                                                    <WifiOff className="mr-2 h-4 w-4" />
                                                    Disconnect
                                                </>
                                            )}
                                        </Button>
                                    )}

                                    <Button onClick={handleDelete} variant="destructive" className="ml-auto">
                                        <Trash2 className="mr-2 h-4 w-4" />
                                        Delete Configuration
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Configuration Form */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Shield className="h-5 w-5" />
                                {server ? 'Update' : 'Configure'} MCP Server
                            </CardTitle>
                            <CardDescription>Configure your MCP server connection for secure integration management</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Server Name</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Production MCP Server"
                                        required
                                    />
                                    {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="url">Server URL</Label>
                                    <Input
                                        id="url"
                                        type="url"
                                        value={data.url}
                                        onChange={(e) => setData('url', e.target.value)}
                                        placeholder="https://mcp.example.com"
                                        required
                                    />
                                    {errors.url && <p className="text-sm text-red-500">{errors.url}</p>}
                                    <p className="text-muted-foreground text-xs">The URL of your MCP server instance</p>
                                    <Alert className="mt-2">
                                        <Info className="h-4 w-4" />
                                        <AlertDescription>
                                            <strong>For testing:</strong> Use{' '}
                                            <code className="bg-muted rounded px-1 py-0.5">http://localhost:8000</code> or any URL containing
                                            "localhost", "127.0.0.1", or "test" to use the mock server.
                                        </AlertDescription>
                                    </Alert>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="ssl_certificate">
                                        SSL Certificate (Optional)
                                        <Badge variant="outline" className="ml-2">
                                            <Key className="mr-1 h-3 w-3" />
                                            Advanced
                                        </Badge>
                                    </Label>
                                    <Textarea
                                        id="ssl_certificate"
                                        value={data.ssl_certificate}
                                        onChange={(e) => setData('ssl_certificate', e.target.value)}
                                        placeholder="-----BEGIN CERTIFICATE-----&#10;...&#10;-----END CERTIFICATE-----"
                                        rows={6}
                                        className="font-mono text-xs"
                                    />
                                    {errors.ssl_certificate && <p className="text-sm text-red-500">{errors.ssl_certificate}</p>}
                                    <p className="text-muted-foreground text-xs">
                                        Paste your custom SSL certificate if using self-signed certificates
                                    </p>
                                </div>

                                {testResult && (
                                    <Alert variant={testResult.success ? 'default' : 'destructive'}>
                                        {testResult.success ? <CheckCircle className="h-4 w-4" /> : <AlertCircle className="h-4 w-4" />}
                                        <AlertDescription>{testResult.message}</AlertDescription>
                                    </Alert>
                                )}

                                {errors.error && (
                                    <Alert variant="destructive">
                                        <AlertCircle className="h-4 w-4" />
                                        <AlertDescription>{errors.error}</AlertDescription>
                                    </Alert>
                                )}

                                <div className="flex gap-2">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? (
                                            <>
                                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                Saving...
                                            </>
                                        ) : (
                                            <>
                                                <Shield className="mr-2 h-4 w-4" />
                                                {server ? 'Update' : 'Save'} Configuration
                                            </>
                                        )}
                                    </Button>

                                    {!server && (
                                        <Button type="button" variant="outline" onClick={testConnection} disabled={!data.url || isTesting}>
                                            {isTesting ? (
                                                <>
                                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                    Testing...
                                                </>
                                            ) : (
                                                <>
                                                    <RefreshCw className="mr-2 h-4 w-4" />
                                                    Test Connection
                                                </>
                                            )}
                                        </Button>
                                    )}
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    {/* Security Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Shield className="h-5 w-5" />
                                Security Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-start gap-3">
                                <CheckCircle className="mt-0.5 h-5 w-5 text-green-500" />
                                <div>
                                    <p className="font-semibold">End-to-End Encryption</p>
                                    <p className="text-muted-foreground text-sm">
                                        All credentials are encrypted using RSA public key encryption before transmission
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <CheckCircle className="mt-0.5 h-5 w-5 text-green-500" />
                                <div>
                                    <p className="font-semibold">Secure Key Exchange</p>
                                    <p className="text-muted-foreground text-sm">
                                        Public keys are automatically exchanged with the MCP server for secure communication
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <CheckCircle className="mt-0.5 h-5 w-5 text-green-500" />
                                <div>
                                    <p className="font-semibold">No Local Storage</p>
                                    <p className="text-muted-foreground text-sm">
                                        Integration credentials are never stored locally, only on the secure MCP server
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
