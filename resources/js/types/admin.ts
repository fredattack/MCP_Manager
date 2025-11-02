export type UserRole = 'admin' | 'manager' | 'user' | 'read_only';

export interface User {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    permissions: string[] | null;
    is_active: boolean;
    is_locked: boolean;
    locked_at: string | null;
    locked_reason: string | null;
    last_login_at: string | null;
    last_login_ip: string | null;
    failed_login_attempts: number;
    notes: string | null;
    created_by: number | null;
    created_at: string;
    updated_at: string;
    activity_logs?: UserActivityLog[];
    tokens?: UserToken[];
    integration_accounts_count?: number;
}

export interface UserActivityLog {
    id: number;
    user_id: number;
    performed_by: number | null;
    action: string;
    entity_type: string | null;
    entity_id: number | null;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    description: string | null;
    ip_address: string | null;
    user_agent: string | null;
    created_at: string;
    formatted_description?: string;
    performed_by_user?: User;
}

export interface UserToken {
    id: number;
    user_id: number;
    token_type: string;
    token: string;
    name: string | null;
    scopes: string[] | null;
    expires_at: string | null;
    last_used_at: string | null;
    usage_count: number;
    max_usages: number | null;
    is_active: boolean;
    created_at: string;
    masked_token?: string;
}

export interface UserCredentials {
    password: string;
    api_token: string;
    basic_auth: string;
    basic_auth_header: string;
}

export interface RoleOption {
    value: UserRole;
    label: string;
    description: string;
}

export interface UserFilters {
    search?: string;
    role?: UserRole;
    is_active?: boolean;
    is_locked?: boolean;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
}

export interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}
