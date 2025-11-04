export interface IntegrationAccount {
    id: number;
    user_id: number;
    organization_id?: number | null;
    type: string;
    access_token: string;
    meta?: Record<string, unknown> | null;
    status: string;
    scope: 'personal' | 'organization';
    shared_with?: string[] | null; // JSON array: ['all_members', 'admins_only', 'user:123']
    created_by?: number | null;
    created_at: string;
    updated_at: string;
}

export enum IntegrationType {
    NOTION = 'notion',
    GMAIL = 'gmail',
    CALENDAR = 'calendar',
    OPENAI = 'openai',
    TODOIST = 'todoist',
    JIRA = 'jira',
    SENTRY = 'sentry',
}

export enum IntegrationStatus {
    ACTIVE = 'active',
    INACTIVE = 'inactive',
}

export interface IntegrationTypeInfo {
    value: string;
    displayName: string;
    description: string;
    icon: string;
}

export const INTEGRATION_TYPES: Record<string, IntegrationTypeInfo> = {
    [IntegrationType.NOTION]: {
        value: IntegrationType.NOTION,
        displayName: 'Notion',
        description: 'Connect to your Notion workspace',
        icon: 'notion',
    },
    [IntegrationType.GMAIL]: {
        value: IntegrationType.GMAIL,
        displayName: 'Gmail',
        description: 'Connect to your Gmail account',
        icon: 'gmail',
    },
    [IntegrationType.OPENAI]: {
        value: IntegrationType.OPENAI,
        displayName: 'OpenAI',
        description: 'Connect to OpenAI services',
        icon: 'openai',
    },
    [IntegrationType.TODOIST]: {
        value: IntegrationType.TODOIST,
        displayName: 'Todoist',
        description: 'Connect to your Todoist account',
        icon: 'todoist',
    },
    [IntegrationType.JIRA]: {
        value: IntegrationType.JIRA,
        displayName: 'JIRA',
        description: 'Connect to your Atlassian JIRA',
        icon: 'jira',
    },
    [IntegrationType.CALENDAR]: {
        value: IntegrationType.CALENDAR,
        displayName: 'Google Calendar',
        description: 'Connect to your Google Calendar',
        icon: 'calendar',
    },
    [IntegrationType.SENTRY]: {
        value: IntegrationType.SENTRY,
        displayName: 'Sentry',
        description: 'Connect to your Sentry projects',
        icon: 'sentry',
    },
};
