import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, Save, AlertCircle } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

interface Props {
    service: string;
    status: any;
}

const serviceConfig = {
    todoist: {
        name: 'Todoist',
        icon: '✅',
        fields: [
            { name: 'api_token', label: 'API Token', type: 'password', placeholder: 'Your Todoist API token', required: true },
        ],
        help: 'You can find your API token in Todoist Settings > Integrations > Developer',
    },
    notion: {
        name: 'Notion',
        icon: '📝',
        fields: [
            { name: 'api_key', label: 'API Key', type: 'password', placeholder: 'secret_...', required: true },
            { name: 'database_id', label: 'Database ID (Optional)', type: 'text', placeholder: 'Database ID for default database', required: false },
        ],
        help: 'Create an integration at https://www.notion.so/my-integrations',
    },
    jira: {
        name: 'Jira',
        icon: '🎯',
        fields: [
            { name: 'domain', label: 'Domain', type: 'url', placeholder: 'https://yourcompany.atlassian.net', required: true },
            { name: 'email', label: 'Email', type: 'email', placeholder: 'your@email.com', required: true },
            { name: 'api_token', label: 'API Token', type: 'password', placeholder: 'Your Jira API token', required: true },
        ],
        help: 'Create an API token at https://id.atlassian.com/manage-profile/security/api-tokens',
    },
    openai: {
        name: 'OpenAI',
        icon: '🤖',
        fields: [
            { name: 'api_key', label: 'API Key', type: 'password', placeholder: 'sk-...', required: true },
            { name: 'model', label: 'Model (Optional)', type: 'text', placeholder: 'gpt-4', required: false },
        ],
        help: 'Get your API key from https://platform.openai.com/api-keys',
    },
    mistral: {
        name: 'Mistral',
        icon: '🌟',
        fields: [
            { name: 'api_key', label: 'API Key', type: 'password', placeholder: 'Your Mistral API key', required: true },
            { name: 'model', label: 'Model (Optional)', type: 'text', placeholder: 'mistral-medium', required: false },
        ],
        help: 'Get your API key from https://console.mistral.ai/',
    },
    sentry: {
        name: 'Sentry',
        icon: '🚨',
        fields: [
            { name: 'auth_token', label: 'Auth Token', type: 'password', placeholder: 'Your Sentry auth token', required: true },
            { name: 'organization', label: 'Organization', type: 'text', placeholder: 'your-org', required: true },
            { name: 'project', label: 'Project (Optional)', type: 'text', placeholder: 'your-project', required: false },
        ],
        help: 'Create an auth token at https://sentry.io/settings/account/api/auth-tokens/',
    },
    confluence: {
        name: 'Confluence',
        icon: '📚',
        fields: [
            { name: 'domain', label: 'Domain', type: 'url', placeholder: 'https://yourcompany.atlassian.net', required: true },
            { name: 'email', label: 'Email', type: 'email', placeholder: 'your@email.com', required: true },
            { name: 'api_token', label: 'API Token', type: 'password', placeholder: 'Your Confluence API token', required: true },
        ],
        help: 'Create an API token at https://id.atlassian.com/manage-profile/security/api-tokens',
    },
};

export default function Configure({ service, status }: Props) {
    const config = serviceConfig[service] || { name: service, icon: '🔧', fields: [], help: '' };
    const { data, setData, post, processing, errors } = useForm({});
    const [testingConnection, setTestingConnection] = useState(false);
    const [testResult, setTestResult] = useState<{ success: boolean; message: string } | null>(null);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/integrations/manager/${service}`, {
            onSuccess: () => {
                // Will redirect automatically
            },
        });
    };

    const handleTest = async () => {
        setTestingConnection(true);
        setTestResult(null);
        
        try {
            const response = await fetch(`/integrations/manager/${service}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(data),
            });
            
            const result = await response.json();
            setTestResult({
                success: result.success,
                message: result.message,
            });
        } catch (error) {
            setTestResult({
                success: false,
                message: 'Failed to test connection',
            });
        } finally {
            setTestingConnection(false);
        }
    };

    return (
        <AppLayout>
            <Head title={`Configure ${config.name}`} />
            
            <div className="container mx-auto py-8 max-w-2xl">
                <Button
                    variant="ghost"
                    onClick={() => router.visit('/integrations/manager')}
                    className="mb-4"
                >
                    <ArrowLeft className="w-4 h-4 mr-2" />
                    Back to Integrations
                </Button>

                <Card>
                    <CardHeader>
                        <div className="flex items-center gap-3">
                            <span className="text-3xl">{config.icon}</span>
                            <div>
                                <CardTitle>Configure {config.name}</CardTitle>
                                <CardDescription>
                                    {status?.configured ? 'Update your integration settings' : 'Set up your integration'}
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            {config.help && (
                                <Alert>
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>{config.help}</AlertDescription>
                                </Alert>
                            )}

                            {config.fields.map((field) => (
                                <div key={field.name} className="space-y-2">
                                    <Label htmlFor={field.name}>
                                        {field.label}
                                        {!field.required && <span className="text-muted-foreground ml-1">(Optional)</span>}
                                    </Label>
                                    <Input
                                        id={field.name}
                                        type={field.type}
                                        placeholder={field.placeholder}
                                        value={data[field.name] || ''}
                                        onChange={(e) => setData(field.name, e.target.value)}
                                        required={field.required}
                                    />
                                    {errors[field.name] && (
                                        <p className="text-sm text-red-500">{errors[field.name]}</p>
                                    )}
                                </div>
                            ))}

                            {errors.error && (
                                <Alert variant="destructive">
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>{errors.error}</AlertDescription>
                                </Alert>
                            )}

                            {testResult && (
                                <Alert variant={testResult.success ? 'default' : 'destructive'}>
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>{testResult.message}</AlertDescription>
                                </Alert>
                            )}

                            <div className="flex gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={handleTest}
                                    disabled={testingConnection || processing}
                                >
                                    {testingConnection ? 'Testing...' : 'Test Connection'}
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    <Save className="w-4 h-4 mr-2" />
                                    {processing ? 'Saving...' : 'Save Configuration'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}