import React from 'react';
import { IntegrationType } from '../../types/integrations';
import { Organization } from '../../types/organizations';
import { Button } from '../ui/button';
import { Checkbox } from '../ui/checkbox';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { Separator } from '../ui/separator';

// Type definitions for credential fields per service
interface NotionCredentials {
    access_token: string;
    database_id?: string;
}

interface JiraCredentials {
    url: string;
    email: string;
    api_token: string;
    cloud: boolean;
}

interface ConfluenceCredentials {
    url: string;
    email: string;
    api_token: string;
}

interface TodoistCredentials {
    api_token: string;
}

interface SentryCredentials {
    auth_token: string;
    org_slug: string;
    base_url?: string;
    project?: string;
}

interface AnthropicCredentials {
    api_key: string;
    default_model?: string;
    max_tokens?: number;
    temperature?: number;
}

interface GoogleCredentials {
    client_id: string;
    client_secret: string;
    redirect_uri: string;
}

interface GitHubCredentials {
    client_id: string;
    client_secret: string;
    redirect_uri: string;
}

interface GitLabCredentials {
    client_id: string;
    client_secret: string;
    redirect_uri: string;
}

type ServiceCredentials =
    | NotionCredentials
    | JiraCredentials
    | ConfluenceCredentials
    | TodoistCredentials
    | SentryCredentials
    | AnthropicCredentials
    | GoogleCredentials
    | GitHubCredentials
    | GitLabCredentials;

export interface IntegrationFormData {
    type: IntegrationType;
    scope: 'personal' | 'organization';
    organization_id?: number;
    shared_with?: string[];
    credentials: ServiceCredentials;
}

interface IntegrationFormDynamicProps {
    type: IntegrationType;
    initialScope?: 'personal' | 'organization';
    organizationId?: number;
    organizations?: Organization[];
    onSubmit: (data: IntegrationFormData) => Promise<void>;
    submitLabel?: string;
}

interface FieldConfig {
    name: string;
    label: string;
    type: 'text' | 'password' | 'email' | 'url' | 'number' | 'checkbox';
    required: boolean;
    placeholder?: string;
    helpText?: string;
    defaultValue?: string | number | boolean;
}

// Field configurations per service type
const SERVICE_FIELD_CONFIGS: Record<string, FieldConfig[]> = {
    [IntegrationType.NOTION]: [
        {
            name: 'access_token',
            label: 'Access Token',
            type: 'password',
            required: true,
            placeholder: 'secret_...',
            helpText: 'Your Notion Internal Integration Token',
        },
        {
            name: 'database_id',
            label: 'Database ID (Optional)',
            type: 'text',
            required: false,
            placeholder: '32 character database ID',
            helpText: 'Specific database to connect to',
        },
    ],
    [IntegrationType.JIRA]: [
        {
            name: 'url',
            label: 'JIRA URL',
            type: 'url',
            required: true,
            placeholder: 'https://your-domain.atlassian.net',
            helpText: 'Your JIRA instance URL',
        },
        {
            name: 'email',
            label: 'Email',
            type: 'email',
            required: true,
            placeholder: 'your-email@example.com',
            helpText: 'Your Atlassian account email',
        },
        {
            name: 'api_token',
            label: 'API Token',
            type: 'password',
            required: true,
            placeholder: 'Your JIRA API token',
            helpText: 'Generate from Atlassian Account Settings',
        },
        {
            name: 'cloud',
            label: 'JIRA Cloud',
            type: 'checkbox',
            required: false,
            defaultValue: true,
            helpText: 'Check if using JIRA Cloud (not Server)',
        },
    ],
    [IntegrationType.TODOIST]: [
        {
            name: 'api_token',
            label: 'API Token',
            type: 'password',
            required: true,
            placeholder: 'Your Todoist API token',
            helpText: 'Find in Todoist Settings > Integrations',
        },
    ],
    [IntegrationType.SENTRY]: [
        {
            name: 'auth_token',
            label: 'Auth Token',
            type: 'password',
            required: true,
            placeholder: 'Your Sentry auth token',
            helpText: 'Generate from Sentry Settings > Auth Tokens',
        },
        {
            name: 'org_slug',
            label: 'Organization Slug',
            type: 'text',
            required: true,
            placeholder: 'your-org-slug',
            helpText: 'Your Sentry organization identifier',
        },
        {
            name: 'base_url',
            label: 'Base URL (Optional)',
            type: 'url',
            required: false,
            placeholder: 'https://sentry.io/api/0',
            helpText: 'Custom Sentry instance URL',
        },
        {
            name: 'project',
            label: 'Project (Optional)',
            type: 'text',
            required: false,
            placeholder: 'project-name',
            helpText: 'Specific project to monitor',
        },
    ],
    [IntegrationType.OPENAI]: [
        {
            name: 'api_key',
            label: 'API Key',
            type: 'password',
            required: true,
            placeholder: 'sk-...',
            helpText: 'Your Anthropic API key',
        },
        {
            name: 'default_model',
            label: 'Default Model (Optional)',
            type: 'text',
            required: false,
            placeholder: 'claude-3-5-sonnet-20241022',
            helpText: 'Default Claude model to use',
        },
        {
            name: 'max_tokens',
            label: 'Max Tokens (Optional)',
            type: 'number',
            required: false,
            placeholder: '4096',
            helpText: 'Maximum tokens per request',
        },
        {
            name: 'temperature',
            label: 'Temperature (Optional)',
            type: 'number',
            required: false,
            placeholder: '1.0',
            helpText: 'Temperature (0.0 - 1.0)',
        },
    ],
    [IntegrationType.GMAIL]: [
        {
            name: 'client_id',
            label: 'Client ID',
            type: 'text',
            required: true,
            placeholder: 'Your Google OAuth Client ID',
            helpText: 'From Google Cloud Console',
        },
        {
            name: 'client_secret',
            label: 'Client Secret',
            type: 'password',
            required: true,
            placeholder: 'Your Google OAuth Client Secret',
            helpText: 'From Google Cloud Console',
        },
        {
            name: 'redirect_uri',
            label: 'Redirect URI',
            type: 'url',
            required: true,
            placeholder: 'https://your-domain.com/oauth/callback',
            helpText: 'OAuth redirect URI',
        },
    ],
};

// Add configurations for GitHub and GitLab (similar to Gmail)
SERVICE_FIELD_CONFIGS['github'] = [
    {
        name: 'client_id',
        label: 'Client ID',
        type: 'text',
        required: true,
        placeholder: 'Your GitHub OAuth App Client ID',
        helpText: 'From GitHub Developer Settings',
    },
    {
        name: 'client_secret',
        label: 'Client Secret',
        type: 'password',
        required: true,
        placeholder: 'Your GitHub OAuth App Client Secret',
        helpText: 'From GitHub Developer Settings',
    },
    {
        name: 'redirect_uri',
        label: 'Redirect URI',
        type: 'url',
        required: true,
        placeholder: 'https://your-domain.com/oauth/callback',
        helpText: 'OAuth redirect URI',
    },
];

SERVICE_FIELD_CONFIGS['gitlab'] = [
    {
        name: 'client_id',
        label: 'Application ID',
        type: 'text',
        required: true,
        placeholder: 'Your GitLab Application ID',
        helpText: 'From GitLab Applications Settings',
    },
    {
        name: 'client_secret',
        label: 'Secret',
        type: 'password',
        required: true,
        placeholder: 'Your GitLab Application Secret',
        helpText: 'From GitLab Applications Settings',
    },
    {
        name: 'redirect_uri',
        label: 'Redirect URI',
        type: 'url',
        required: true,
        placeholder: 'https://your-domain.com/oauth/callback',
        helpText: 'OAuth redirect URI',
    },
];

export function IntegrationFormDynamic({
    type,
    initialScope = 'personal',
    organizationId,
    organizations = [],
    onSubmit,
    submitLabel = 'Save Integration',
}: IntegrationFormDynamicProps) {
    const [scope, setScope] = React.useState<'personal' | 'organization'>(initialScope);
    const [selectedOrgId, setSelectedOrgId] = React.useState<number | undefined>(organizationId);
    const [sharedWith, setSharedWith] = React.useState<string[]>(['all_members']);
    const [credentials, setCredentials] = React.useState<Record<string, unknown>>({});
    const [isLoading, setIsLoading] = React.useState(false);
    const [error, setError] = React.useState<string | null>(null);

    const fieldConfigs = React.useMemo(() => SERVICE_FIELD_CONFIGS[type] || [], [type]);

    // Initialize default values
    React.useEffect(() => {
        const defaults: Record<string, unknown> = {};
        fieldConfigs.forEach((field) => {
            if (field.defaultValue !== undefined) {
                defaults[field.name] = field.defaultValue;
            }
        });
        setCredentials(defaults);
    }, [fieldConfigs]);

    const handleFieldChange = (fieldName: string, value: unknown) => {
        setCredentials((prev) => ({
            ...prev,
            [fieldName]: value,
        }));
    };

    const validateForm = (): boolean => {
        // Check required fields
        for (const field of fieldConfigs) {
            if (field.required && !credentials[field.name]) {
                setError(`${field.label} is required`);
                return false;
            }
        }

        // Check organization selection if scope is organization
        if (scope === 'organization' && !selectedOrgId) {
            setError('Please select an organization');
            return false;
        }

        return true;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError(null);

        if (!validateForm()) {
            return;
        }

        setIsLoading(true);

        try {
            await onSubmit({
                type,
                scope,
                organization_id: scope === 'organization' ? selectedOrgId : undefined,
                shared_with: scope === 'organization' ? sharedWith : undefined,
                credentials: credentials as ServiceCredentials,
            });
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An error occurred');
        } finally {
            setIsLoading(false);
        }
    };

    const renderField = (field: FieldConfig) => {
        if (field.type === 'checkbox') {
            return (
                <div key={field.name} className="flex items-center space-x-2">
                    <Checkbox
                        id={field.name}
                        checked={!!credentials[field.name]}
                        onCheckedChange={(checked) => handleFieldChange(field.name, checked)}
                    />
                    <Label htmlFor={field.name} className="text-sm font-normal">
                        {field.label}
                    </Label>
                    {field.helpText && <p className="text-xs text-gray-500 dark:text-gray-400">{field.helpText}</p>}
                </div>
            );
        }

        return (
            <div key={field.name} className="space-y-2">
                <Label htmlFor={field.name}>
                    {field.label} {field.required && <span className="text-red-500">*</span>}
                </Label>
                <Input
                    id={field.name}
                    type={field.type}
                    value={(credentials[field.name] as string | number) || ''}
                    onChange={(e) => {
                        const value = field.type === 'number' ? parseFloat(e.target.value) : e.target.value;
                        handleFieldChange(field.name, value);
                    }}
                    placeholder={field.placeholder}
                    required={field.required}
                />
                {field.helpText && <p className="text-xs text-gray-500 dark:text-gray-400">{field.helpText}</p>}
            </div>
        );
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            {/* Scope Selection */}
            <div className="space-y-3">
                <Label className="text-base font-semibold">Credential Scope</Label>
                <div className="flex gap-4">
                    <div
                        onClick={() => setScope('personal')}
                        className={`flex-1 cursor-pointer rounded-lg border-2 p-4 transition-all ${
                            scope === 'personal' ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/20' : 'border-gray-200 dark:border-gray-700'
                        }`}
                    >
                        <div className="flex items-start gap-3">
                            <input
                                type="radio"
                                name="scope"
                                value="personal"
                                checked={scope === 'personal'}
                                onChange={() => setScope('personal')}
                                className="mt-1"
                            />
                            <div>
                                <div className="font-medium">Personal</div>
                                <div className="text-sm text-gray-600 dark:text-gray-400">Only accessible by you</div>
                            </div>
                        </div>
                    </div>
                    <div
                        onClick={() => setScope('organization')}
                        className={`flex-1 cursor-pointer rounded-lg border-2 p-4 transition-all ${
                            scope === 'organization' ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/20' : 'border-gray-200 dark:border-gray-700'
                        }`}
                    >
                        <div className="flex items-start gap-3">
                            <input
                                type="radio"
                                name="scope"
                                value="organization"
                                checked={scope === 'organization'}
                                onChange={() => setScope('organization')}
                                className="mt-1"
                            />
                            <div>
                                <div className="font-medium">Organization</div>
                                <div className="text-sm text-gray-600 dark:text-gray-400">Shared with team members</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Organization Selector */}
            {scope === 'organization' && (
                <>
                    <div className="space-y-2">
                        <Label htmlFor="organization">
                            Select Organization <span className="text-red-500">*</span>
                        </Label>
                        <Select value={selectedOrgId?.toString()} onValueChange={(val) => setSelectedOrgId(parseInt(val, 10))}>
                            <SelectTrigger id="organization">
                                <SelectValue placeholder="Choose an organization" />
                            </SelectTrigger>
                            <SelectContent>
                                {organizations.map((org) => (
                                    <SelectItem key={org.id} value={org.id.toString()}>
                                        {org.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {organizations.length === 0 && (
                            <p className="text-xs text-amber-600 dark:text-amber-400">You need to create or join an organization first.</p>
                        )}
                    </div>

                    {/* Sharing Configuration */}
                    <div className="space-y-3">
                        <Label className="text-base font-semibold">Who can access this credential?</Label>
                        <div className="space-y-2">
                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="all_members"
                                    checked={sharedWith.includes('all_members')}
                                    onCheckedChange={(checked) => {
                                        if (checked) {
                                            setSharedWith(['all_members']);
                                        } else {
                                            setSharedWith(sharedWith.filter((s) => s !== 'all_members'));
                                        }
                                    }}
                                />
                                <Label htmlFor="all_members" className="text-sm font-normal">
                                    All Members
                                    <span className="ml-2 text-xs text-gray-500 dark:text-gray-400">(Everyone in the organization)</span>
                                </Label>
                            </div>
                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="admins_only"
                                    checked={sharedWith.includes('admins_only')}
                                    onCheckedChange={(checked) => {
                                        if (checked) {
                                            setSharedWith(['admins_only']);
                                        } else {
                                            setSharedWith(sharedWith.filter((s) => s !== 'admins_only'));
                                        }
                                    }}
                                />
                                <Label htmlFor="admins_only" className="text-sm font-normal">
                                    Admins Only
                                    <span className="ml-2 text-xs text-gray-500 dark:text-gray-400">(Owners and admins only)</span>
                                </Label>
                            </div>
                        </div>
                    </div>
                </>
            )}

            <Separator />

            {/* Dynamic Credential Fields */}
            <div className="space-y-4">
                <Label className="text-base font-semibold">Credentials</Label>
                {fieldConfigs.length === 0 ? (
                    <div className="rounded-md bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                        No credential fields configured for this integration type yet.
                    </div>
                ) : (
                    fieldConfigs.map(renderField)
                )}
            </div>

            {error && <div className="rounded-md bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">{error}</div>}

            <div className="flex justify-end gap-3">
                <Button type="submit" disabled={isLoading || (scope === 'organization' && !selectedOrgId)}>
                    {isLoading ? 'Saving...' : submitLabel}
                </Button>
            </div>
        </form>
    );
}
