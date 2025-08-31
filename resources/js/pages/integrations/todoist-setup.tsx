import { useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import { AlertCircle, CheckCircle, ExternalLink } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';

interface TodoistIntegration {
    id: string;
    status: string;
    connected_at: string;
    meta?: {
        email?: string;
        full_name?: string;
        avatar_url?: string;
        last_tested_at?: string;
    };
}

interface Props {
    integration?: TodoistIntegration;
}

export default function TodoistSetup({ integration }: Props) {
    
    const { data, setData, post, processing, errors, clearErrors } = useForm({
        api_token: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('integrations.todoist.connect'), {
            onSuccess: () => {
                setData('api_token', '');
            },
        });
    };

    const handleDisconnect = () => {
        if (confirm('Are you sure you want to disconnect your Todoist account?')) {
            post(route('integrations.todoist.disconnect'));
        }
    };

    const handleTest = () => {
        post(route('integrations.todoist.test'));
    };

    return (
        <AppLayout>
            <Head title="Todoist Setup" />
            
            <div className="container mx-auto py-8 max-w-4xl">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold mb-2">Todoist Integration</h1>
                    <p className="text-muted-foreground">
                        Connect your Todoist account to manage tasks and projects
                    </p>
                </div>

                {integration && integration.status === 'active' ? (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <CheckCircle className="h-5 w-5 text-green-500" />
                                Connected
                            </CardTitle>
                            <CardDescription>
                                Your Todoist account is connected and active
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2">
                                {integration.meta?.email && (
                                    <div>
                                        <Label className="text-sm text-muted-foreground">Email</Label>
                                        <p className="font-medium">{integration.meta.email}</p>
                                    </div>
                                )}
                                {integration.meta?.full_name && (
                                    <div>
                                        <Label className="text-sm text-muted-foreground">Name</Label>
                                        <p className="font-medium">{integration.meta.full_name}</p>
                                    </div>
                                )}
                                <div>
                                    <Label className="text-sm text-muted-foreground">Connected</Label>
                                    <p className="font-medium">
                                        {new Date(integration.connected_at).toLocaleString()}
                                    </p>
                                </div>
                                {integration.meta?.last_tested_at && (
                                    <div>
                                        <Label className="text-sm text-muted-foreground">Last Tested</Label>
                                        <p className="font-medium">
                                            {new Date(integration.meta.last_tested_at).toLocaleString()}
                                        </p>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                        <CardFooter className="gap-2">
                            <Button
                                onClick={handleTest}
                                disabled={processing}
                                variant="outline"
                            >
                                Test Connection
                            </Button>
                            <Button
                                onClick={handleDisconnect}
                                disabled={processing}
                                variant="destructive"
                            >
                                Disconnect
                            </Button>
                            <Button
                                onClick={() => window.location.href = route('integrations.todoist')}
                                className="ml-auto"
                            >
                                Go to Todoist
                            </Button>
                        </CardFooter>
                    </Card>
                ) : (
                    <Card>
                        <CardHeader>
                            <CardTitle>Connect Your Todoist Account</CardTitle>
                            <CardDescription>
                                Enter your Todoist API token to get started
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="api_token">API Token</Label>
                                    <Input
                                        id="api_token"
                                        type="password"
                                        value={data.api_token}
                                        onChange={(e) => {
                                            clearErrors('api_token');
                                            setData('api_token', e.target.value);
                                        }}
                                        placeholder="Enter your Todoist API token"
                                        className={errors.api_token ? 'border-red-500' : ''}
                                        disabled={processing}
                                    />
                                    {errors.api_token && (
                                        <p className="text-sm text-red-500">{errors.api_token}</p>
                                    )}
                                </div>

                                <Alert>
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>
                                        <p className="mb-2">To get your API token:</p>
                                        <ol className="list-decimal list-inside space-y-1 text-sm">
                                            <li>Go to Todoist Settings</li>
                                            <li>Click on "Integrations"</li>
                                            <li>Click on "Developer" tab</li>
                                            <li>Copy your API token</li>
                                        </ol>
                                        <a
                                            href="https://todoist.com/app/settings/integrations/developer"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center gap-1 text-primary hover:underline mt-2"
                                        >
                                            Open Todoist Settings
                                            <ExternalLink className="h-3 w-3" />
                                        </a>
                                    </AlertDescription>
                                </Alert>

                                <Button
                                    type="submit"
                                    disabled={processing || !data.api_token}
                                    className="w-full"
                                >
                                    {processing ? 'Connecting...' : 'Connect Todoist'}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                )}

                {integration && integration.status === 'error' && (
                    <Alert className="mt-4" variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>
                            There was an error with your Todoist connection. Please reconnect your account.
                        </AlertDescription>
                    </Alert>
                )}
            </div>
        </AppLayout>
    );
}