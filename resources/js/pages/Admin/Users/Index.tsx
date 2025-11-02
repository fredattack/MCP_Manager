import AppLayout from '@/layouts/app-layout';
import { MonologueCard } from '@/components/ui/MonologueCard';
import { type BreadcrumbItem, type PageProps } from '@/types';
import type { PaginatedUsers, RoleOption, UserFilters } from '@/types/admin';
import { Head, Link, router } from '@inertiajs/react';
import { Users, Plus, Filter, Search } from 'lucide-react';
import { useState } from 'react';

interface UsersIndexProps extends PageProps {
    users: PaginatedUsers;
    filters: UserFilters;
    roles: RoleOption[];
    can: {
        create: boolean;
        edit: boolean;
        delete: boolean;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin' },
    { title: 'Users', href: '/admin/users' },
];

export default function Index({ users, filters, roles, can }: UsersIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/users', { ...filters, search }, { preserveState: true });
    };

    const getRoleBadgeColor = (role: string) => {
        switch (role) {
            case 'admin':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            case 'manager':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
            case 'read_only':
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
            default:
                return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="User Management" />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">
                            User Management
                        </h1>
                        <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">
                            Manage users, roles, and permissions
                        </p>
                    </div>
                    {can.create && (
                        <Link
                            href="/admin/users/create"
                            className="inline-flex items-center gap-2 rounded-lg bg-cyan-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cyan-600 dark:bg-cyan-600 dark:hover:bg-cyan-700"
                        >
                            <Plus className="h-4 w-4" />
                            Create User
                        </Link>
                    )}
                </div>

                {/* Stats Cards */}
                <div className="grid auto-rows-min gap-6 md:grid-cols-4">
                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Total Users
                                </p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">
                                    {users.total}
                                </p>
                            </div>
                            <div className="rounded-lg bg-cyan-500/10 p-3">
                                <Users className="h-6 w-6 text-cyan-500" />
                            </div>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div>
                            <p className="font-monologue-mono text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Active
                            </p>
                            <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">
                                {users.data.filter(u => u.is_active).length}
                            </p>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div>
                            <p className="font-monologue-mono text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Locked
                            </p>
                            <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">
                                {users.data.filter(u => u.is_locked).length}
                            </p>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div>
                            <p className="font-monologue-mono text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Admins
                            </p>
                            <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">
                                {users.data.filter(u => u.role === 'admin').length}
                            </p>
                        </div>
                    </MonologueCard>
                </div>

                {/* Search and Filters */}
                <MonologueCard variant="elevated">
                    <form onSubmit={handleSearch} className="flex gap-4">
                        <div className="flex-1">
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search by name or email..."
                                    className="font-monologue-mono w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                />
                            </div>
                        </div>
                        <button
                            type="submit"
                            className="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            <Filter className="h-4 w-4" />
                            Search
                        </button>
                    </form>
                </MonologueCard>

                {/* Users Table */}
                <MonologueCard variant="elevated">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-gray-200 dark:border-gray-700">
                                    <th className="font-monologue-mono pb-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        User
                                    </th>
                                    <th className="font-monologue-mono pb-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Role
                                    </th>
                                    <th className="font-monologue-mono pb-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Status
                                    </th>
                                    <th className="font-monologue-mono pb-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Last Login
                                    </th>
                                    <th className="font-monologue-mono pb-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                {users.data.map((user) => (
                                    <tr key={user.id} className="group hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td className="py-4">
                                            <div>
                                                <div className="font-monologue-mono text-sm font-medium text-gray-900 dark:text-white">
                                                    {user.name}
                                                </div>
                                                <div className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">
                                                    {user.email}
                                                </div>
                                            </div>
                                        </td>
                                        <td className="py-4">
                                            <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${getRoleBadgeColor(user.role)}`}>
                                                {user.role.replace('_', ' ').toUpperCase()}
                                            </span>
                                        </td>
                                        <td className="py-4">
                                            <div className="flex gap-2">
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
                                            </div>
                                        </td>
                                        <td className="font-monologue-mono py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {user.last_login_at
                                                ? new Date(user.last_login_at).toLocaleDateString()
                                                : 'Never'}
                                        </td>
                                        <td className="py-4 text-right">
                                            <Link
                                                href={`/admin/users/${user.id}`}
                                                className="font-monologue-mono text-sm font-medium text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {users.last_page > 1 && (
                        <div className="mt-4 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                            <div className="font-monologue-mono text-sm text-gray-500 dark:text-gray-400">
                                Showing {users.from} to {users.to} of {users.total} users
                            </div>
                            <div className="flex gap-2">
                                {users.current_page > 1 && (
                                    <Link
                                        href={`/admin/users?page=${users.current_page - 1}`}
                                        className="rounded-lg bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                    >
                                        Previous
                                    </Link>
                                )}
                                {users.current_page < users.last_page && (
                                    <Link
                                        href={`/admin/users?page=${users.current_page + 1}`}
                                        className="rounded-lg bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                    >
                                        Next
                                    </Link>
                                )}
                            </div>
                        </div>
                    )}
                </MonologueCard>
            </div>
        </AppLayout>
    );
}
