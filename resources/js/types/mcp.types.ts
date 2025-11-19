export interface McpServer {
    id: number;
    name: string;
    url: string;
    status: 'active' | 'inactive' | 'error';
    health: {
        status: string;
        connected: boolean;
        has_session: boolean;
        error: string | null;
        last_check: string;
    };
    configured_at: string;
}

export interface McpIntegration {
    id: string;
    name: string;
    status: 'active' | 'inactive' | 'error' | 'connecting';
    enabled: boolean;
    lastSync: string | null;
    errorMessage?: string;
    credentialsValid: boolean;
    source?: 'local' | 'remote';
}

export interface ServerStatus {
    connected: boolean;
    latency: number | null;
    lastSync: string | null;
    status?: string;
    error?: string;
}

export interface ServiceConfig {
    name: string;
    fields: ServiceField[];
}

export interface ServiceField {
    name: string;
    type: 'text' | 'email' | 'password' | 'url';
    label: string;
    required: boolean;
    placeholder?: string;
    helperText?: string;
}

export interface IntegrationConfig {
    id?: number;
    enabled: boolean;
    status: string;
    credentials_valid: boolean;
    last_sync: string | null;
    error_message: string | null;
}

export interface McpDashboardProps {
    integrations: McpIntegration[];
    serverStatus: ServerStatus;
}

export interface ServerConfigProps {
    server: McpServer | null;
}

export interface ConfigureIntegrationProps {
    service: string;
    integration: IntegrationConfig | null;
    serviceConfig: ServiceConfig;
}

export interface WebSocketMessage {
    type: 'integration_update' | 'server_status' | 'error';
    integrationId?: string;
    data: unknown;
}
