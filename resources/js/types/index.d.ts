import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
    status?: 'connected' | 'disconnected' | 'error';
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    integrationStatuses?: Record<string, 'connected' | 'disconnected' | 'error'>;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
    };
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    role?: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

// Workflow Types
export interface Workflow {
    id: string;
    user_id: string;
    name: string;
    description: string | null;
    config: Record<string, unknown> | null;
    status: 'pending' | 'running' | 'completed' | 'failed';
    created_at: string;
    updated_at: string;
    started_at?: string;
    completed_at?: string;
    duration?: number;
    latest_execution?: WorkflowExecution;
}

export interface WorkflowExecution {
    id: string;
    workflow_id: string;
    status: 'pending' | 'running' | 'completed' | 'failed';
    started_at?: string;
    completed_at?: string;
    result?: Record<string, unknown>;
    steps?: WorkflowStep[];
}

export interface WorkflowStep {
    id: string;
    execution_id: string;
    step_name: string;
    step_order: number;
    status: 'pending' | 'running' | 'completed' | 'failed' | 'skipped';
    started_at?: string;
    completed_at?: string;
    duration?: number;
    output?: Record<string, unknown>;
    error_message?: string;
}
