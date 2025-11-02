import AppLayout from '@/layouts/app-layout';
import { MonologueCard } from '@/components/ui/MonologueCard';
import { type BreadcrumbItem, type PageProps } from '@/types';
import type { User, UserActivityLog, UserCredentials } from '@/types/admin';
import { Head, router } from '@inertiajs/react';
import { Key, Lock, Unlock, Edit, Calendar, Activity } from 'lucide-react';
import { useState } from 'react';
import axios from 'axios';

interface UsersShowProps extends PageProps {
    user: User & {
        activity_logs: UserActivityLog[];
    };
    can: {
        edit: boolean;
        delete: boolean;
    };
}

export default function Show({ user, can }: UsersShowProps) {
    const [credentials, setCredentials] = useState<UserCredentials | null>(null);
    const [loading, setLoading] = useState(false);
    const [copied, setCopied] = useState<string | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Admin', href: '/admin' },
        { title: 'Users', href: '/admin/users' },
        { title: user.name, href: `/admin/users/${user.id}` },
    ];

    const handleGenerateCredentials = async () => {
        try {
            setLoading(true);
            const response = await axios.post(`/admin/users/${user.id}/credentials`);
            setCredentials(response.data.credentials);
        } catch (error) {
            console.error('Failed to generate credentials:', error);
        } finally {
            setLoading(false);
        }
    };

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
                        <h1 className="font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">
                            {user.name}
                        </h1>
                        <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">
                            {user.email}
                        </p>
                    </div>
                    <div className="flex gap-2">
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
                        <h2 className="font-monologue-serif mb-4 text-2xl font-normal text-gray-900 dark:text-white">
                            User Information
                        </h2>
                        <dl className="space-y-3">
                            <div>
                                <dt className="font-monologue-mono text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Role
                                </dt>
                                <dd className="font-monologue-mono mt-1 text-sm text-gray-900 dark:text-white">
                                    {user.role.replace('_', ' ').toUpperCase()}
                                </dd>
                            </div>
                            <div>
                                <dt className="font-monologue-mono text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Status
                                </dt>
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
                                <dt className="font-monologue-mono text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Last Login
                                </dt>
                                <dd className="font-monologue-mono mt-1 text-sm text-gray-900 dark:text-white">
                                    {user.last_login_at
                                        ? new Date(user.last_login_at).toLocaleString()
                                        : 'Never'}
                                </dd>
                            </div>
                            {user.locked_reason && (
                                <div>
                                    <dt className="font-monologue-mono text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Lock Reason
                                    </dt>
                                    <dd className="font-monologue-mono mt-1 text-sm text-red-600 dark:text-red-400">
                                        {user.locked_reason}
                                    </dd>
                                </div>
                            )}
                        </dl>
                    </MonologueCard>

                    {/* Credential Generator */}
                    <MonologueCard variant="elevated">
                        <h2 className="font-monologue-serif mb-4 text-2xl font-normal text-gray-900 dark:text-white">
                            Credentials
                        </h2>

                        {!credentials ? (
                            <button
                                onClick={handleGenerateCredentials}
                                disabled={loading}
                                className="inline-flex items-center gap-2 rounded-lg bg-cyan-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cyan-600 disabled:opacity-50"
                            >
                                <Key className="h-4 w-4" />
                                {loading ? 'Generating...' : 'Generate New Credentials'}
                            </button>
                        ) : (
                            <div className="space-y-4">
                                {/* Password */}
                                <div>
                                    <label className="font-monologue-mono mb-2 block text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Password
                                    </label>
                                    <div className="flex gap-2">
                                        <code className="font-monologue-mono flex-1 rounded bg-gray-100 px-3 py-2 text-sm text-gray-900 dark:bg-gray-800 dark:text-white">
                                            {credentials.password}
                                        </code>
                                        <button
                                            onClick={() => copyToClipboard(credentials.password, 'password')}
                                            className="rounded bg-gray-200 px-3 py-2 text-xs hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                                        >
                                            {copied === 'password' ? '✓' : 'Copy'}
                                        </button>
                                    </div>
                                </div>

                                {/* API Token */}
                                <div>
                                    <label className="font-monologue-mono mb-2 block text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        API Token
                                    </label>
                                    <div className="flex gap-2">
                                        <code className="font-monologue-mono flex-1 overflow-hidden overflow-ellipsis rounded bg-gray-100 px-3 py-2 text-sm text-gray-900 dark:bg-gray-800 dark:text-white">
                                            {credentials.api_token}
                                        </code>
                                        <button
                                            onClick={() => copyToClipboard(credentials.api_token, 'token')}
                                            className="rounded bg-gray-200 px-3 py-2 text-xs hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                                        >
                                            {copied === 'token' ? '✓' : 'Copy'}
                                        </button>
                                    </div>
                                </div>

                                {/* Base64 Basic Auth */}
                                <div>
                                    <label className="font-monologue-mono mb-2 block text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Basic Auth (Base64)
                                    </label>
                                    <div className="flex gap-2">
                                        <code className="font-monologue-mono flex-1 overflow-hidden overflow-ellipsis rounded bg-gray-100 px-3 py-2 text-sm text-gray-900 dark:bg-gray-800 dark:text-white">
                                            {credentials.basic_auth}
                                        </code>
                                        <button
                                            onClick={() => copyToClipboard(credentials.basic_auth, 'basic')}
                                            className="rounded bg-gray-200 px-3 py-2 text-xs hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                                        >
                                            {copied === 'basic' ? '✓' : 'Copy'}
                                        </button>
                                    </div>
                                </div>

                                {/* curl Example */}
                                <div>
                                    <label className="font-monologue-mono mb-2 block text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        curl Example
                                    </label>
                                    <code className="font-monologue-mono block overflow-x-auto rounded bg-gray-900 px-3 py-3 text-xs text-green-400">
                                        curl -X POST http://localhost:9978/mcp \{'\n'}
                                        {'  '}-H "{credentials.basic_auth_header}" \{'\n'}
                                        {'  '}-d '{`{"jsonrpc":"2.0","id":1,"method":"tools/list"}`}'
                                    </code>
                                </div>

                                <button
                                    onClick={() => setCredentials(null)}
                                    className="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                >
                                    Generate New
                                </button>
                            </div>
                        )}
                    </MonologueCard>
                </div>

                {/* Activity Log */}
                <MonologueCard variant="elevated">
                    <h2 className="font-monologue-serif mb-4 text-2xl font-normal text-gray-900 dark:text-white">
                        Recent Activity
                    </h2>
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
        </AppLayout>
    );
}
