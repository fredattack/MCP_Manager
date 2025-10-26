import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Globe, Info, Lock, Server, Settings, Shield, Zap } from 'lucide-react';

export default function NoServerConfigured() {
    return (
        <AppLayout>
            <Head title="MCP Server Not Configured" />

            <div className="container mx-auto max-w-4xl py-8">
                <div className="space-y-6">
                    {/* Main Alert */}
                    <Alert>
                        <Info className="h-4 w-4" />
                        <AlertTitle>No MCP Server Configured</AlertTitle>
                        <AlertDescription>You need to configure an MCP server before you can manage integrations.</AlertDescription>
                    </Alert>

                    {/* Setup Card */}
                    <Card className="border-2 border-dashed">
                        <CardHeader className="text-center">
                            <div className="bg-primary/10 mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full">
                                <Server className="text-primary h-10 w-10" />
                            </div>
                            <CardTitle className="text-2xl">Get Started with MCP Server</CardTitle>
                            <CardDescription className="text-base">Connect your MCP server to manage all your integrations securely</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6 text-center">
                            <Link href="/mcp/server/config">
                                <Button size="lg" className="gap-2">
                                    <Settings className="h-5 w-5" />
                                    Configure MCP Server
                                    <ArrowRight className="h-4 w-4" />
                                </Button>
                            </Link>

                            <p className="text-muted-foreground mx-auto max-w-md text-sm">
                                Setting up an MCP server takes just a few minutes and enables secure, centralized management of all your integrations.
                            </p>
                        </CardContent>
                    </Card>

                    {/* Features Grid */}
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <Card>
                            <CardHeader>
                                <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-green-500/10">
                                    <Shield className="h-6 w-6 text-green-500" />
                                </div>
                                <CardTitle className="text-lg">Secure</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-muted-foreground text-sm">
                                    End-to-end RSA encryption ensures your credentials are always protected
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500/10">
                                    <Zap className="h-6 w-6 text-blue-500" />
                                </div>
                                <CardTitle className="text-lg">Real-time</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-muted-foreground text-sm">Monitor integration status and receive instant updates via WebSocket</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-500/10">
                                    <Globe className="h-6 w-6 text-purple-500" />
                                </div>
                                <CardTitle className="text-lg">Centralized</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-muted-foreground text-sm">Manage all your integrations from a single, unified dashboard</p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Steps */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Setup Guide</CardTitle>
                            <CardDescription>Follow these steps to get your MCP server up and running</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ol className="space-y-4">
                                <li className="flex gap-3">
                                    <span className="bg-primary/10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold">
                                        1
                                    </span>
                                    <div>
                                        <p className="font-medium">Deploy MCP Server</p>
                                        <p className="text-muted-foreground text-sm">Deploy the MCP server to your preferred hosting provider</p>
                                    </div>
                                </li>
                                <li className="flex gap-3">
                                    <span className="bg-primary/10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold">
                                        2
                                    </span>
                                    <div>
                                        <p className="font-medium">Configure Connection</p>
                                        <p className="text-muted-foreground text-sm">Enter your server URL and optional SSL certificate</p>
                                    </div>
                                </li>
                                <li className="flex gap-3">
                                    <span className="bg-primary/10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold">
                                        3
                                    </span>
                                    <div>
                                        <p className="font-medium">Test Connection</p>
                                        <p className="text-muted-foreground text-sm">Verify the connection is working properly</p>
                                    </div>
                                </li>
                                <li className="flex gap-3">
                                    <span className="bg-primary/10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold">
                                        4
                                    </span>
                                    <div>
                                        <p className="font-medium">Add Integrations</p>
                                        <p className="text-muted-foreground text-sm">Configure your Todoist, Notion, Jira, and other integrations</p>
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
                            Your MCP server uses industry-standard RSA encryption and secure key exchange protocols. Credentials are never stored
                            locally and all communication is encrypted.
                        </AlertDescription>
                    </Alert>
                </div>
            </div>
        </AppLayout>
    );
}
