import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { 
    Settings, 
    AlertCircle, 
    CheckCircle, 
    Loader2,
    Key,
    ArrowLeft,
    Eye,
    EyeOff,
    Info,
    Shield
} from 'lucide-react';
import { IntegrationConfig, ServiceConfig } from '@/types/mcp.types';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/app-layout';

interface Props {
    service: string;
    integration: IntegrationConfig | null;
    serviceConfig: ServiceConfig;
}

export default function ConfigureIntegration({ service, integration, serviceConfig }: Props) {
    const [showPasswords, setShowPasswords] = useState<Record<string, boolean>>({});
    const [isTesting, setIsTesting] = useState(false);
    const [testResult, setTestResult] = useState<{ success: boolean; message: string } | null>(null);
    
    // Initialize form data with empty strings for all fields
    const initialData: Record<string, string> = {};
    serviceConfig.fields.forEach(field => {
        initialData[field.name] = '';
    });
    
    const { data, setData, post, processing, errors } = useForm(initialData);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/mcp/integrations/${service}`, {
            onSuccess: () => {
                setTestResult({ success: true, message: 'Integration configured successfully!' });
            },
            onError: () => {
                setTestResult({ success: false, message: 'Failed to configure integration.' });
            }
        });
    };

    const testIntegration = async () => {
        setIsTesting(true);
        setTestResult(null);
        
        try {
            const response = await fetch(`/api/mcp/integrations/${service}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            
            const result = await response.json();
            
            setTestResult({
                success: result.success,
                message: result.success 
                    ? 'Integration test successful!'
                    : result.error || 'Integration test failed'
            });
        } catch (error) {
            setTestResult({
                success: false,
                message: 'Failed to test integration'
            });
        } finally {
            setIsTesting(false);
        }
    };

    const togglePasswordVisibility = (fieldName: string) => {
        setShowPasswords(prev => ({
            ...prev,
            [fieldName]: !prev[fieldName]
        }));
    };

    const getFieldIcon = (type: string) => {
        switch (type) {
            case 'password':
                return <Key className="w-4 h-4 text-muted-foreground" />;
            default:
                return null;
        }
    };

    const getIntegrationLogo = (serviceName: string) => {
        // This could be expanded to show actual logos
        const logos: Record<string, string> = {
            todoist: '✓',
            notion: '📝',
            jira: '🎯',
            sentry: '🚨',
            confluence: '📚',
        };
        return logos[serviceName.toLowerCase()] || '🔗';
    };

    return (
        <AppLayout>
            <Head title={`Configure ${serviceConfig.name} Integration`} />
            
            <div className="container mx-auto py-8 max-w-4xl">
                <div className="mb-8">
                    <Link href="/mcp/dashboard">
                        <Button variant="ghost" size="sm" className="mb-4">
                            <ArrowLeft className="w-4 h-4 mr-2" />
                            Back to Dashboard
                        </Button>
                    </Link>
                    <h1 className="text-3xl font-bold mb-2">Configure {serviceConfig.name}</h1>
                    <p className="text-muted-foreground">
                        Set up your {serviceConfig.name} integration credentials
                    </p>
                </div>
                
                <div className="space-y-6">

                        {/* Current Status */}
                        {integration && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center justify-between">
                                        <span className="flex items-center gap-2">
                                            <span className="text-2xl">{getIntegrationLogo(service)}</span>
                                            Current {serviceConfig.name} Status
                                        </span>
                                        <Badge variant={integration.status === 'active' ? 'default' : 'secondary'}>
                                            {integration.status}
                                        </Badge>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <p className="text-sm text-muted-foreground">Enabled</p>
                                            <p className="font-semibold">
                                                {integration.enabled ? 'Yes' : 'No'}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-sm text-muted-foreground">Credentials</p>
                                            <p className={cn(
                                                "font-semibold",
                                                integration.credentials_valid ? 'text-green-600' : 'text-red-600'
                                            )}>
                                                {integration.credentials_valid ? 'Valid' : 'Invalid'}
                                            </p>
                                        </div>
                                        {integration.last_sync && (
                                            <div>
                                                <p className="text-sm text-muted-foreground">Last Sync</p>
                                                <p className="font-semibold">
                                                    {new Date(integration.last_sync).toLocaleString()}
                                                </p>
                                            </div>
                                        )}
                                        {integration.error_message && (
                                            <div className="col-span-2">
                                                <Alert variant="destructive">
                                                    <AlertCircle className="h-4 w-4" />
                                                    <AlertDescription>{integration.error_message}</AlertDescription>
                                                </Alert>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Configuration Form */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Settings className="w-5 h-5" />
                                    Configure {serviceConfig.name} Integration
                                </CardTitle>
                                <CardDescription>
                                    Enter your {serviceConfig.name} credentials to enable integration
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    {serviceConfig.fields.map(field => (
                                        <div key={field.name} className="space-y-2">
                                            <Label htmlFor={field.name} className="flex items-center gap-2">
                                                {getFieldIcon(field.type)}
                                                {field.label}
                                                {field.required && (
                                                    <span className="text-red-500">*</span>
                                                )}
                                            </Label>
                                            
                                            <div className="relative">
                                                <Input
                                                    id={field.name}
                                                    type={
                                                        field.type === 'password' && !showPasswords[field.name]
                                                            ? 'password'
                                                            : field.type === 'password'
                                                            ? 'text'
                                                            : field.type
                                                    }
                                                    value={data[field.name] || ''}
                                                    onChange={e => setData(field.name, e.target.value)}
                                                    placeholder={field.placeholder}
                                                    required={field.required}
                                                    className={field.type === 'password' ? 'pr-10' : ''}
                                                />
                                                
                                                {field.type === 'password' && (
                                                    <button
                                                        type="button"
                                                        onClick={() => togglePasswordVisibility(field.name)}
                                                        className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                                    >
                                                        {showPasswords[field.name] ? (
                                                            <EyeOff className="w-4 h-4" />
                                                        ) : (
                                                            <Eye className="w-4 h-4" />
                                                        )}
                                                    </button>
                                                )}
                                            </div>
                                            
                                            {field.helperText && (
                                                <p className="text-xs text-muted-foreground">
                                                    {field.helperText}
                                                </p>
                                            )}
                                            
                                            {errors[field.name] && (
                                                <p className="text-sm text-red-500">{errors[field.name]}</p>
                                            )}
                                        </div>
                                    ))}

                                    {testResult && (
                                        <Alert variant={testResult.success ? 'default' : 'destructive'}>
                                            {testResult.success ? (
                                                <CheckCircle className="h-4 w-4" />
                                            ) : (
                                                <AlertCircle className="h-4 w-4" />
                                            )}
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
                                                    <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                                                    Saving...
                                                </>
                                            ) : (
                                                <>
                                                    <Settings className="w-4 h-4 mr-2" />
                                                    Save Configuration
                                                </>
                                            )}
                                        </Button>
                                        
                                        {integration && (
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={testIntegration}
                                                disabled={isTesting}
                                            >
                                                {isTesting ? (
                                                    <>
                                                        <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                                                        Testing...
                                                    </>
                                                ) : (
                                                    'Test Integration'
                                                )}
                                            </Button>
                                        )}
                                    </div>
                                </form>
                            </CardContent>
                        </Card>

                        {/* Help Section */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Info className="w-5 h-5" />
                                    How to Get Your {serviceConfig.name} Credentials
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {service === 'todoist' && (
                                    <ol className="list-decimal list-inside space-y-2 text-sm">
                                        <li>Log in to your Todoist account</li>
                                        <li>Go to Settings → Integrations</li>
                                        <li>Scroll down to "API token"</li>
                                        <li>Copy your personal API token</li>
                                        <li>Paste it in the field above</li>
                                    </ol>
                                )}
                                
                                {service === 'notion' && (
                                    <ol className="list-decimal list-inside space-y-2 text-sm">
                                        <li>Go to <a href="https://www.notion.so/my-integrations" target="_blank" className="text-blue-500 hover:underline">Notion Integrations</a></li>
                                        <li>Click "New integration"</li>
                                        <li>Give it a name and select your workspace</li>
                                        <li>Copy the "Internal Integration Token"</li>
                                        <li>Share your Notion pages with the integration</li>
                                    </ol>
                                )}
                                
                                {service === 'jira' && (
                                    <ol className="list-decimal list-inside space-y-2 text-sm">
                                        <li>Log in to your Atlassian account</li>
                                        <li>Go to <a href="https://id.atlassian.com/manage-profile/security/api-tokens" target="_blank" className="text-blue-500 hover:underline">API tokens</a></li>
                                        <li>Click "Create API token"</li>
                                        <li>Give it a label and create</li>
                                        <li>Copy the token and your email</li>
                                        <li>Enter your Jira domain (e.g., company.atlassian.net)</li>
                                    </ol>
                                )}

                                <Alert>
                                    <Shield className="h-4 w-4" />
                                    <AlertTitle>Security Note</AlertTitle>
                                    <AlertDescription>
                                        Your credentials are encrypted end-to-end and securely transmitted to the MCP server.
                                        They are never stored locally on this application.
                                    </AlertDescription>
                                </Alert>
                            </CardContent>
                        </Card>
                    </div>
                </div>
        </AppLayout>
    );
}