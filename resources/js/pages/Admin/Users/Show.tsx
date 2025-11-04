import { McpSetupDrawer } from '@/components/Admin/McpSetupDrawer';
import { MonologueCard } from '@/components/ui/MonologueCard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PageProps } from '@/types';
import type { User, UserActivityLog } from '@/types/admin';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { Activity, Edit, Lock, Settings, Unlock } from 'lucide-react';
import { useState } from 'react';

interface McpCredentials {
    username: string;
    token_base64: string;
}

interface UsersShowProps extends PageProps {
    user: User & {
        activity_logs: UserActivityLog[];
    };
    mcpServerUrl: string;
    mcpCredentials: McpCredentials;
    can: {
        edit: boolean;
        delete: boolean;
    };
}

export default function Show({ user, can, mcpServerUrl, mcpCredentials }: UsersShowProps) {
    const [isDrawerOpen, setIsDrawerOpen] = useState(false);
    const [copied, setCopied] = useState<string | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Admin', href: '/admin' },
        { title: 'Users', href: '/admin/users' },
        { title: user.name, href: `/admin/users/${user.id}` },
    ];

    const handleLockToggle = async () => {
        try {
            if (user.is_locked) {
                await axios.post(`/admin/users/${user.id}/unlock`);
            } else {
                const reason = prompt('Reason for locking this user:');
                if (reason) {
                    await axios.post(`/admin/users/${user.id}/lock`, { reason });
                }
            }
            router.reload();
        } catch (error) {
            console.error('Failed to toggle lock:', error);
        }
    };

    const copyToClipboard = async (text: string, key: string) => {
        await navigator.clipboard.writeText(text);
        setCopied(key);
        setTimeout(() => setCopied(null), 2000);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`User: ${user.name}`} />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">{user.name}</h1>
                        <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">{user.email}</p>
                    </div>
                    <div className="flex gap-2">
                        <button
                            onClick={() => setIsDrawerOpen(true)}
                            className="inline-flex items-center gap-2 rounded-lg bg-purple-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-purple-600"
                        >
                            <Settings className="h-4 w-4" />
                            MCP Setup
                        </button>
                        <button
                            onClick={handleLockToggle}
                            className="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            {user.is_locked ? <Unlock className="h-4 w-4" /> : <Lock className="h-4 w-4" />}
                            {user.is_locked ? 'Unlock' : 'Lock'}
                        </button>
                        {can.edit && (
                            <button
                                onClick={() => router.visit(`/admin/users/${user.id}/edit`)}
                                className="inline-flex items-center gap-2 rounded-lg bg-cyan-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cyan-600"
                            >
                                <Edit className="h-4 w-4" />
                                Edit
                            </button>
                        )}
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* User Info */}
                    <MonologueCard variant="elevated">
                        <h2 className="font-monologue-serif mb-4 text-2xl font-normal text-gray-900 dark:text-white">User Information</h2>
                        <dl className="space-y-3">
                            <div>
                                <dt className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Role</dt>
                                <dd className="font-monologue-mono mt-1 text-sm text-gray-900 dark:text-white">
                                    {user.role.replace('_', ' ').toUpperCase()}
                                </dd>
                            </div>
                            <div>
                                <dt className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Status</dt>
                                <dd className="mt-1 flex gap-2">
                                    {user.is_locked && (
                                        <span className="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            Locked
                                        </span>
                                    )}
                                    {!user.is_active && (
                                        <span className="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                                            Inactive
                                        </span>
                                    )}
                                    {user.is_active && !user.is_locked && (
                                        <span className="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            Active
                                        </span>
                                    )}
                                </dd>
                            </div>
                            <div>
                                <dt className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Last Login</dt>
                                <dd className="font-monologue-mono mt-1 text-sm text-gray-900 dark:text-white">
                                    {user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never'}
                                </dd>
                            </div>
                            {user.locked_reason && (
                                <div>
                                    <dt className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Lock Reason
                                    </dt>
                                    <dd className="font-monologue-mono mt-1 text-sm text-red-600 dark:text-red-400">{user.locked_reason}</dd>
                                </div>
                            )}
                        </dl>
                    </MonologueCard>

                    {/* MCP Configuration */}
                    <MonologueCard variant="elevated">
                        <h2 className="font-monologue-serif mb-4 text-2xl font-normal text-gray-900 dark:text-white">MCP Configuration</h2>
                        <p className="font-monologue-mono mb-4 text-sm text-gray-600 dark:text-gray-400">
                            Your MCP credentials are ready. Click the "MCP Setup" button above to get the configuration for Claude Desktop, Claude
                            Code, or ChatGPT Desktop.
                        </p>
                        <div className="space-y-3">
                            <div>
                                <label className="font-monologue-mono mb-1 block text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                    Username
                                </label>
                                <code className="font-monologue-mono block rounded bg-gray-100 px-3 py-2 text-sm text-gray-900 dark:bg-gray-800 dark:text-white">
                                    {mcpCredentials.username}
                                </code>
                            </div>
                            <div>
                                <label className="font-monologue-mono mb-1 block text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                    Token (Base64)
                                </label>
                                <div className="flex gap-2">
                                    <code className="font-monologue-mono flex-1 overflow-hidden rounded bg-gray-100 px-3 py-2 text-sm overflow-ellipsis text-gray-900 dark:bg-gray-800 dark:text-white">
                                        {mcpCredentials.token_base64}
                                    </code>
                                    <button
                                        onClick={() => {
                                            navigator.clipboard.writeText(mcpCredentials.token_base64);
                                            setCopied('token');
                                            setTimeout(() => setCopied(null), 2000);
                                        }}
                                        className="rounded bg-gray-200 px-3 py-2 text-xs hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                                    >
                                        {copied === 'token' ? '✓' : 'Copy'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </MonologueCard>
                </div>

                {/* Activity Log */}
                <MonologueCard variant="elevated">
                    <h2 className="font-monologue-serif mb-4 text-2xl font-normal text-gray-900 dark:text-white">Recent Activity</h2>
                    <div className="space-y-3">
                        {user.activity_logs?.slice(0, 10).map((log) => (
                            <div key={log.id} className="flex items-start gap-3 border-b border-gray-200 pb-3 last:border-0 dark:border-gray-700">
                                <div className="rounded-full bg-cyan-100 p-2 dark:bg-cyan-900/30">
                                    <Activity className="h-4 w-4 text-cyan-600 dark:text-cyan-400" />
                                </div>
                                <div className="flex-1">
                                    <p className="font-monologue-mono text-sm text-gray-900 dark:text-white">
                                        {log.formatted_description || log.action}
                                    </p>
                                    <p className="font-monologue-mono mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {new Date(log.created_at).toLocaleString()}
                                        {log.ip_address && ` • ${log.ip_address}`}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </MonologueCard>
            </div>

            <McpSetupDrawer
                isOpen={isDrawerOpen}
                onClose={() => setIsDrawerOpen(false)}
                credentials={mcpCredentials}
                userName={user.name}
                mcpServerUrl={mcpServerUrl}
            />
        </AppLayout>
    );
}
