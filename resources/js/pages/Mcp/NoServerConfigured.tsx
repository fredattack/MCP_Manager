import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { 
    Server, 
    Settings, 
    Shield, 
    ArrowRight,
    Info,
    Zap,
    Lock,
    Globe
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

export default function NoServerConfigured() {
    return (
        <AppLayout>
            <Head title="MCP Server Not Configured" />
            
            <div className="container mx-auto py-8 max-w-4xl">
                <div className="space-y-6">
                        {/* Main Alert */}
                        <Alert>
                            <Info className="h-4 w-4" />
                            <AlertTitle>No MCP Server Configured</AlertTitle>
                            <AlertDescription>
                                You need to configure an MCP server before you can manage integrations.
                            </AlertDescription>
                        </Alert>

                        {/* Setup Card */}
                        <Card className="border-2 border-dashed">
                            <CardHeader className="text-center">
                                <div className="mx-auto mb-4 w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center">
                                    <Server className="w-10 h-10 text-primary" />
                                </div>
                                <CardTitle className="text-2xl">Get Started with MCP Server</CardTitle>
                                <CardDescription className="text-base">
                                    Connect your MCP server to manage all your integrations securely
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="text-center space-y-6">
                                <Link href="/mcp/server/config">
                                    <Button size="lg" className="gap-2">
                                        <Settings className="w-5 h-5" />
                                        Configure MCP Server
                                        <ArrowRight className="w-4 h-4" />
                                    </Button>
                                </Link>
                                
                                <p className="text-sm text-muted-foreground max-w-md mx-auto">
                                    Setting up an MCP server takes just a few minutes and enables secure, 
                                    centralized management of all your integrations.
                                </p>
                            </CardContent>
                        </Card>

                        {/* Features Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <Card>
                                <CardHeader>
                                    <div className="w-12 h-12 rounded-lg bg-green-500/10 flex items-center justify-center mb-3">
                                        <Shield className="w-6 h-6 text-green-500" />
                                    </div>
                                    <CardTitle className="text-lg">Secure</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">
                                        End-to-end RSA encryption ensures your credentials are always protected
                                    </p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <div className="w-12 h-12 rounded-lg bg-blue-500/10 flex items-center justify-center mb-3">
                                        <Zap className="w-6 h-6 text-blue-500" />
                                    </div>
                                    <CardTitle className="text-lg">Real-time</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">
                                        Monitor integration status and receive instant updates via WebSocket
                                    </p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <div className="w-12 h-12 rounded-lg bg-purple-500/10 flex items-center justify-center mb-3">
                                        <Globe className="w-6 h-6 text-purple-500" />
                                    </div>
                                    <CardTitle className="text-lg">Centralized</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">
                                        Manage all your integrations from a single, unified dashboard
                                    </p>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Steps */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Setup Guide</CardTitle>
                                <CardDescription>
                                    Follow these steps to get your MCP server up and running
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <ol className="space-y-4">
                                    <li className="flex gap-3">
                                        <span className="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-sm font-semibold">
                                            1
                                        </span>
                                        <div>
                                            <p className="font-medium">Deploy MCP Server</p>
                                            <p className="text-sm text-muted-foreground">
                                                Deploy the MCP server to your preferred hosting provider
                                            </p>
                                        </div>
                                    </li>
                                    <li className="flex gap-3">
                                        <span className="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-sm font-semibold">
                                            2
                                        </span>
                                        <div>
                                            <p className="font-medium">Configure Connection</p>
                                            <p className="text-sm text-muted-foreground">
                                                Enter your server URL and optional SSL certificate
                                            </p>
                                        </div>
                                    </li>
                                    <li className="flex gap-3">
                                        <span className="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-sm font-semibold">
                                            3
                                        </span>
                                        <div>
                                            <p className="font-medium">Test Connection</p>
                                            <p className="text-sm text-muted-foreground">
                                                Verify the connection is working properly
                                            </p>
                                        </div>
                                    </li>
                                    <li className="flex gap-3">
                                        <span className="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-sm font-semibold">
                                            4
                                        </span>
                                        <div>
                                            <p className="font-medium">Add Integrations</p>
                                            <p className="text-sm text-muted-foreground">
                                                Configure your Todoist, Notion, Jira, and other integrations
                                            </p>
                                        </div>
                                    </li>
                                </ol>
                            </CardContent>
                        </Card>

                        {/* Security Note */}
                        <Alert>
                            <Lock className="h-4 w-4" />
                            <AlertTitle>Security First</AlertTitle>
                            <AlertDescription>
                                Your MCP server uses industry-standard RSA encryption and secure key exchange 
                                protocols. Credentials are never stored locally and all communication is encrypted.
                            </AlertDescription>
                        </Alert>
                    </div>
                </div>
        </AppLayout>
    );
}