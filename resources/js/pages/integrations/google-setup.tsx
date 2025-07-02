import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ExternalLink, CheckCircle, XCircle, AlertCircle } from 'lucide-react';

interface GoogleSetupProps {
    config: {
        serverUrl: string;
        redirectUri: string;
    };
    status: {
        serverConnected: boolean;
        oauthConfigured: boolean;
    };
}

export default function GoogleSetup({ config, status }: GoogleSetupProps) {
    const allConfigured = status.serverConnected && status.oauthConfigured;

    return (
        <AppLayout>
            <Head title="Google Integration Setup" />
            
            <div className="max-w-4xl mx-auto p-6 space-y-6">
                <div>
                    <h1 className="text-3xl font-bold">Google Integration Setup</h1>
                    <p className="text-muted-foreground mt-2">
                        Configure Google OAuth credentials to enable Gmail and Calendar integrations
                    </p>
                </div>

                {/* Status Overview */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            Setup Status
                            {allConfigured ? (
                                <Badge variant="default" className="bg-green-100 text-green-800">
                                    <CheckCircle className="w-3 h-3 mr-1" />
                                    Ready
                                </Badge>
                            ) : (
                                <Badge variant="destructive">
                                    <XCircle className="w-3 h-3 mr-1" />
                                    Configuration Required
                                </Badge>
                            )}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex items-center justify-between">
                            <span>MCP Server Connection</span>
                            {status.serverConnected ? (
                                <Badge variant="default" className="bg-green-100 text-green-800">
                                    <CheckCircle className="w-3 h-3 mr-1" />
                                    Connected
                                </Badge>
                            ) : (
                                <Badge variant="destructive">
                                    <XCircle className="w-3 h-3 mr-1" />
                                    Disconnected
                                </Badge>
                            )}
                        </div>
                        <div className="flex items-center justify-between">
                            <span>Google OAuth Configuration</span>
                            {status.oauthConfigured ? (
                                <Badge variant="default" className="bg-green-100 text-green-800">
                                    <CheckCircle className="w-3 h-3 mr-1" />
                                    Configured
                                </Badge>
                            ) : (
                                <Badge variant="destructive">
                                    <XCircle className="w-3 h-3 mr-1" />
                                    Not Configured
                                </Badge>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* Configuration Steps */}
                <Card>
                    <CardHeader>
                        <CardTitle>Configuration Steps</CardTitle>
                        <CardDescription>
                            Follow these steps to set up Google OAuth integration
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        
                        {/* Step 1: Google Console */}
                        <div className="space-y-4">
                            <div className="flex items-center gap-2">
                                <div className="w-6 h-6 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center text-sm font-semibold">
                                    1
                                </div>
                                <h3 className="text-lg font-semibold">Create Google OAuth Application</h3>
                            </div>
                            
                            <div className="ml-8 space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Go to the Google Cloud Console and create OAuth 2.0 credentials.
                                </p>
                                
                                <Button 
                                    variant="outline" 
                                    className="w-fit"
                                    onClick={() => window.open('https://console.cloud.google.com/apis/credentials', '_blank')}
                                >
                                    <ExternalLink className="w-4 h-4 mr-2" />
                                    Open Google Cloud Console
                                </Button>
                                
                                <div className="space-y-2">
                                    <p className="text-sm font-medium">Required configuration:</p>
                                    <ul className="text-sm text-muted-foreground space-y-1 ml-4">
                                        <li>• Application type: Web application</li>
                                        <li>• Authorized redirect URI: <code className="bg-muted px-1 rounded">{config.redirectUri}</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {/* Step 2: Environment Variables */}
                        <div className="space-y-4">
                            <div className="flex items-center gap-2">
                                <div className="w-6 h-6 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center text-sm font-semibold">
                                    2
                                </div>
                                <h3 className="text-lg font-semibold">Configure Environment Variables</h3>
                            </div>
                            
                            <div className="ml-8 space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Add the following environment variables to your <code>.env</code> file:
                                </p>
                                
                                <div className="bg-muted p-4 rounded-lg font-mono text-sm">
                                    <div>GOOGLE_CLIENT_ID=your_client_id_here</div>
                                    <div>GOOGLE_CLIENT_SECRET=your_client_secret_here</div>
                                    <div>GOOGLE_REDIRECT_URI={config.redirectUri}</div>
                                </div>
                            </div>
                        </div>

                        {/* Step 3: API Scopes */}
                        <div className="space-y-4">
                            <div className="flex items-center gap-2">
                                <div className="w-6 h-6 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center text-sm font-semibold">
                                    3
                                </div>
                                <h3 className="text-lg font-semibold">Enable Required APIs</h3>
                            </div>
                            
                            <div className="ml-8 space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Make sure the following APIs are enabled in your Google Cloud project:
                                </p>
                                
                                <ul className="text-sm space-y-1 ml-4">
                                    <li>• Gmail API</li>
                                    <li>• Google Calendar API</li>
                                    <li>• Google+ API (for user info)</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Testing */}
                {allConfigured && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-green-800">Configuration Complete</CardTitle>
                            <CardDescription>
                                Your Google integration is ready to use!
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button onClick={() => window.location.href = '/integrations/google'}>
                                Go to Google Integrations
                            </Button>
                        </CardContent>
                    </Card>
                )}

                {!allConfigured && (
                    <Alert>
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>
                            Complete the configuration steps above, then restart your application to enable Google integrations.
                        </AlertDescription>
                    </Alert>
                )}
            </div>
        </AppLayout>
    );
}