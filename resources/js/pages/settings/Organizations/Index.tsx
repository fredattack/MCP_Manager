import { MonologueCard } from '@/components/ui/MonologueCard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PageProps } from '@/types';
import type { OrganizationFilters, OrganizationStats, PaginatedOrganizations } from '@/types/organizations';
import { Head, Link, router } from '@inertiajs/react';
import { Building2, Filter, Key, Plus, Search, Shield, Users } from 'lucide-react';
import { useState } from 'react';

interface OrganizationsIndexProps extends PageProps {
    organizations: PaginatedOrganizations;
    filters: OrganizationFilters;
    stats: OrganizationStats;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings' },
    { title: 'Organizations', href: '/settings/organizations' },
];

export default function Index({ organizations, filters, stats }: OrganizationsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/settings/organizations', { ...filters, search }, { preserveState: true });
    };

    const getRoleBadgeColor = (role: string) => {
        switch (role) {
            case 'owner':
                return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
            case 'admin':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
            case 'member':
                return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
            case 'guest':
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
            default:
                return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400';
        }
    };

    const getStatusBadgeColor = (status: string) => {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
            case 'suspended':
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
            case 'deleted':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Organizations" />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">Organizations</h1>
                        <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">
                            Manage your organizations, teams, and shared resources
                        </p>
                    </div>
                    <Link
                        href="/settings/organizations/create"
                        className="inline-flex items-center gap-2 rounded-lg bg-cyan-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cyan-600 dark:bg-cyan-600 dark:hover:bg-cyan-700"
                    >
                        <Plus className="h-4 w-4" />
                        Create Organization
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="grid auto-rows-min gap-6 md:grid-cols-4">
                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                    Total Organizations
                                </p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">
                                    {stats.total_organizations}
                                </p>
                            </div>
                            <div className="rounded-lg bg-cyan-500/10 p-3">
                                <Building2 className="h-6 w-6 text-cyan-500" />
                            </div>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Owned</p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">{stats.owned_count}</p>
                            </div>
                            <div className="rounded-lg bg-purple-500/10 p-3">
                                <Shield className="h-6 w-6 text-purple-500" />
                            </div>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Total Members</p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">{stats.total_members}</p>
                            </div>
                            <div className="rounded-lg bg-blue-500/10 p-3">
                                <Users className="h-6 w-6 text-blue-500" />
                            </div>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                    Shared Credentials
                                </p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">
                                    {stats.shared_credentials}
                                </p>
                            </div>
                            <div className="rounded-lg bg-green-500/10 p-3">
                                <Key className="h-6 w-6 text-green-500" />
                            </div>
                        </div>
                    </MonologueCard>
                </div>

                {/* Search and Filters */}
                <MonologueCard variant="elevated">
                    <form onSubmit={handleSearch} className="flex gap-4">
                        <div className="flex-1">
                            <div className="relative">
                                <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search organizations..."
                                    className="font-monologue-mono w-full rounded-lg border border-gray-300 bg-white py-2 pr-4 pl-10 text-sm text-gray-900 placeholder-gray-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
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

                {/* Organizations Table */}
                <MonologueCard variant="elevated">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-gray-200 dark:border-gray-700">
                                    <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Organization
                                    </th>
                                    <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Status
                                    </th>
                                    <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Members
                                    </th>
                                    <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Credentials
                                    </th>
                                    <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Created
                                    </th>
                                    <th className="font-monologue-mono pb-3 text-right text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                {organizations.data.map((org) => (
                                    <tr key={org.id} className="group hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td className="py-4">
                                            <div>
                                                <div className="font-monologue-mono text-sm font-medium text-gray-900 dark:text-white">
                                                    {org.name}
                                                </div>
                                                <div className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">{org.slug}</div>
                                            </div>
                                        </td>
                                        <td className="py-4">
                                            <span
                                                className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${getStatusBadgeColor(org.status)}`}
                                            >
                                                {org.status.charAt(0).toUpperCase() + org.status.slice(1)}
                                            </span>
                                        </td>
                                        <td className="py-4">
                                            <div className="flex items-center gap-1">
                                                <Users className="h-4 w-4 text-gray-400" />
                                                <span className="font-monologue-mono text-sm text-gray-900 dark:text-white">
                                                    {org.members_count || 0}
                                                </span>
                                                <span className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">
                                                    / {org.max_members}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="py-4">
                                            <div className="flex items-center gap-1">
                                                <Key className="h-4 w-4 text-gray-400" />
                                                <span className="font-monologue-mono text-sm text-gray-900 dark:text-white">
                                                    {org.credentials_count || 0}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="font-monologue-mono py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {new Date(org.created_at).toLocaleDateString()}
                                        </td>
                                        <td className="py-4 text-right">
                                            <Link
                                                href={`/settings/organizations/${org.id}`}
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
                    {organizations.last_page > 1 && (
                        <div className="mt-4 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                            <div className="font-monologue-mono text-sm text-gray-500 dark:text-gray-400">
                                Showing {organizations.from} to {organizations.to} of {organizations.total} organizations
                            </div>
                            <div className="flex gap-2">
                                {organizations.current_page > 1 && (
                                    <Link
                                        href={`/settings/organizations?page=${organizations.current_page - 1}`}
                                        className="rounded-lg bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                    >
                                        Previous
                                    </Link>
                                )}
                                {organizations.current_page < organizations.last_page && (
                                    <Link
                                        href={`/settings/organizations?page=${organizations.current_page + 1}`}
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
