export interface Organization {
    id: number;
    name: string;
    slug: string;
    owner_id: number;
    billing_email: string;
    status: 'active' | 'suspended' | 'deleted';
    max_members: number;
    settings: Record<string, unknown>;
    created_at: string;
    updated_at: string;
    owner?: {
        id: number;
        name: string;
        email: string;
    };
    members_count?: number;
    credentials_count?: number;
    leases_count?: number;
}

export interface OrganizationMember {
    id: number;
    organization_id: number;
    user_id: number;
    role: 'owner' | 'admin' | 'member' | 'guest';
    permissions: string[];
    invited_by?: number;
    joined_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
    inviter?: {
        id: number;
        name: string;
    };
    is_owner: boolean;
    can_manage_members: boolean;
    can_manage_credentials: boolean;
}

export interface OrganizationInvitation {
    id: number;
    organization_id: number;
    email: string;
    role: 'admin' | 'member' | 'guest';
    token: string;
    invited_by: number;
    expires_at: string;
    accepted_at: string | null;
    created_at: string;
    is_expired: boolean;
    inviter: {
        id: number;
        name: string;
    };
    organization?: {
        id: number;
        name: string;
        slug: string;
    };
}

export interface OrganizationCredential {
    id: number;
    organization_id: number;
    type: string;
    status: 'active' | 'inactive' | 'error';
    scope: 'personal' | 'organization';
    shared_with: string[];
    can_access: boolean;
    created_by: {
        id: number;
        name: string;
    } | null;
    created_at: string;
    updated_at: string;
}

export interface OrganizationFilters {
    search?: string;
    status?: 'active' | 'suspended' | 'deleted';
    role?: 'owner' | 'admin' | 'member' | 'guest';
}

export interface OrganizationStats {
    total_organizations: number;
    owned_count: number;
    total_members: number;
    shared_credentials: number;
}

export interface PaginatedOrganizations {
    data: Organization[];
    current_page: number;
    from: number;
    to: number;
    total: number;
    per_page: number;
    last_page: number;
}
