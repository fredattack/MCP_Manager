import { Button } from '@/components/ui/button';
import { MonologueCard } from '@/components/ui/MonologueCard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PageProps } from '@/types';
import type { Organization, OrganizationCredential, OrganizationInvitation, OrganizationMember } from '@/types/organizations';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Clock, Edit, Key, Mail, Plus, Trash2, UserPlus, Users } from 'lucide-react';
import { useState } from 'react';

interface OrganizationShowProps extends PageProps {
    organization: Organization & {
        members: OrganizationMember[];
        credentials: OrganizationCredential[];
        invitations: OrganizationInvitation[];
        leases: Array<{
            id: number;
            user: { id: number; name: string };
            status: string;
            expires_at: string;
            created_at: string;
        }>;
    };
    stats: {
        total_members: number;
        total_credentials: number;
        pending_invitations: number;
        active_leases: number;
    };
    userRole: 'owner' | 'admin' | 'member' | 'guest';
    canManageMembers: boolean;
    canManageCredentials: boolean;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings' },
    { title: 'Organizations', href: '/settings/organizations' },
];

type TabType = 'members' | 'credentials' | 'invitations' | 'leases';

export default function Show({ organization, stats, userRole, canManageMembers, canManageCredentials }: OrganizationShowProps) {
    const [activeTab, setActiveTab] = useState<TabType>('members');

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
            case 'inactive':
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
            case 'error':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
        }
    };

    const handleDeleteMember = (memberId: number) => {
        if (confirm('Are you sure you want to remove this member?')) {
            router.delete(route('settings.organizations.members.destroy', [organization.id, memberId]));
        }
    };

    const handleDeleteCredential = (credentialId: number) => {
        if (confirm('Are you sure you want to delete this credential?')) {
            router.delete(route('settings.organizations.credentials.destroy', [organization.id, credentialId]));
        }
    };

    const handleDeleteInvitation = (invitationId: number) => {
        if (confirm('Are you sure you want to revoke this invitation?')) {
            router.delete(route('settings.organizations.invitations.destroy', [organization.id, invitationId]));
        }
    };

    const TabButton = ({ tab, label, icon: Icon }: { tab: TabType; label: string; icon: React.ElementType }) => (
        <button
            onClick={() => setActiveTab(tab)}
            className={`flex items-center gap-2 border-b-2 px-4 py-2 text-sm font-medium transition-colors ${
                activeTab === tab
                    ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
            }`}
        >
            <Icon className="h-4 w-4" />
            {label}
        </button>
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={organization.name} />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">
                            {organization.name}
                        </h1>
                        <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">{organization.slug}</p>
                    </div>
                    <div className="flex gap-2">
                        {userRole === 'owner' && (
                            <Link
                                href={`/settings/organizations/${organization.id}/edit`}
                                className="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                            >
                                <Edit className="h-4 w-4" />
                                Edit
                            </Link>
                        )}
                        <Link
                            href="/settings/organizations"
                            className="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Back
                        </Link>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid auto-rows-min gap-6 md:grid-cols-4">
                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Members</p>
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
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Credentials</p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">
                                    {stats.total_credentials}
                                </p>
                            </div>
                            <div className="rounded-lg bg-green-500/10 p-3">
                                <Key className="h-6 w-6 text-green-500" />
                            </div>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                    Pending Invitations
                                </p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">
                                    {stats.pending_invitations}
                                </p>
                            </div>
                            <div className="rounded-lg bg-purple-500/10 p-3">
                                <Mail className="h-6 w-6 text-purple-500" />
                            </div>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Active Leases</p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">{stats.active_leases}</p>
                            </div>
                            <div className="rounded-lg bg-cyan-500/10 p-3">
                                <Clock className="h-6 w-6 text-cyan-500" />
                            </div>
                        </div>
                    </MonologueCard>
                </div>

                {/* Tabs */}
                <div className="border-b border-gray-200 dark:border-gray-700">
                    <nav className="flex gap-4">
                        <TabButton tab="members" label="Members" icon={Users} />
                        <TabButton tab="credentials" label="Credentials" icon={Key} />
                        <TabButton tab="invitations" label="Invitations" icon={Mail} />
                        <TabButton tab="leases" label="Active Leases" icon={Clock} />
                    </nav>
                </div>

                {/* Tab Content - Members */}
                {activeTab === 'members' && (
                    <MonologueCard variant="elevated">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="font-monologue-serif text-xl text-gray-900 dark:text-white">Members</h2>
                            {canManageMembers && (
                                <Button size="sm" className="bg-cyan-500 hover:bg-cyan-600 dark:bg-cyan-600 dark:hover:bg-cyan-700">
                                    <UserPlus className="mr-2 h-4 w-4" />
                                    Add Member
                                </Button>
                            )}
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-gray-200 dark:border-gray-700">
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            User
                                        </th>
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Role
                                        </th>
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Joined
                                        </th>
                                        {canManageMembers && (
                                            <th className="font-monologue-mono pb-3 text-right text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                                Actions
                                            </th>
                                        )}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {organization.members.map((member) => (
                                        <tr key={member.id} className="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td className="py-4">
                                                <div>
                                                    <div className="font-monologue-mono text-sm font-medium text-gray-900 dark:text-white">
                                                        {member.user.name}
                                                    </div>
                                                    <div className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">
                                                        {member.user.email}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${getRoleBadgeColor(member.role)}`}
                                                >
                                                    {member.role.charAt(0).toUpperCase() + member.role.slice(1)}
                                                </span>
                                            </td>
                                            <td className="font-monologue-mono py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {new Date(member.joined_at).toLocaleDateString()}
                                            </td>
                                            {canManageMembers && (
                                                <td className="py-4 text-right">
                                                    {!member.is_owner && (
                                                        <button
                                                            onClick={() => handleDeleteMember(member.id)}
                                                            className="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </button>
                                                    )}
                                                </td>
                                            )}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </MonologueCard>
                )}

                {/* Tab Content - Credentials */}
                {activeTab === 'credentials' && (
                    <MonologueCard variant="elevated">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="font-monologue-serif text-xl text-gray-900 dark:text-white">Credentials</h2>
                            {canManageCredentials && (
                                <Button size="sm" className="bg-cyan-500 hover:bg-cyan-600 dark:bg-cyan-600 dark:hover:bg-cyan-700">
                                    <Plus className="mr-2 h-4 w-4" />
                                    Add Credential
                                </Button>
                            )}
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-gray-200 dark:border-gray-700">
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Type
                                        </th>
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Status
                                        </th>
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Shared With
                                        </th>
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Created
                                        </th>
                                        {canManageCredentials && (
                                            <th className="font-monologue-mono pb-3 text-right text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                                Actions
                                            </th>
                                        )}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {organization.credentials.map((credential) => (
                                        <tr key={credential.id} className="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td className="font-monologue-mono py-4 text-sm text-gray-900 dark:text-white">{credential.type}</td>
                                            <td className="py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${getStatusBadgeColor(credential.status)}`}
                                                >
                                                    {credential.status}
                                                </span>
                                            </td>
                                            <td className="font-monologue-mono py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {credential.shared_with.join(', ')}
                                            </td>
                                            <td className="font-monologue-mono py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {new Date(credential.created_at).toLocaleDateString()}
                                            </td>
                                            {canManageCredentials && (
                                                <td className="py-4 text-right">
                                                    <button
                                                        onClick={() => handleDeleteCredential(credential.id)}
                                                        className="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </td>
                                            )}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </MonologueCard>
                )}

                {/* Tab Content - Invitations */}
                {activeTab === 'invitations' && (
                    <MonologueCard variant="elevated">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="font-monologue-serif text-xl text-gray-900 dark:text-white">Pending Invitations</h2>
                            {canManageMembers && (
                                <Button size="sm" className="bg-cyan-500 hover:bg-cyan-600 dark:bg-cyan-600 dark:hover:bg-cyan-700">
                                    <Mail className="mr-2 h-4 w-4" />
                                    Send Invitation
                                </Button>
                            )}
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-gray-200 dark:border-gray-700">
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Email
                                        </th>
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Role
                                        </th>
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Expires
                                        </th>
                                        {canManageMembers && (
                                            <th className="font-monologue-mono pb-3 text-right text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                                Actions
                                            </th>
                                        )}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {organization.invitations.map((invitation) => (
                                        <tr key={invitation.id} className="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td className="font-monologue-mono py-4 text-sm text-gray-900 dark:text-white">{invitation.email}</td>
                                            <td className="py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${getRoleBadgeColor(invitation.role)}`}
                                                >
                                                    {invitation.role}
                                                </span>
                                            </td>
                                            <td className="font-monologue-mono py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {new Date(invitation.expires_at).toLocaleDateString()}
                                            </td>
                                            {canManageMembers && (
                                                <td className="py-4 text-right">
                                                    <button
                                                        onClick={() => handleDeleteInvitation(invitation.id)}
                                                        className="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </td>
                                            )}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </MonologueCard>
                )}

                {/* Tab Content - Leases */}
                {activeTab === 'leases' && (
                    <MonologueCard variant="elevated">
                        <h2 className="font-monologue-serif mb-4 text-xl text-gray-900 dark:text-white">Active Leases</h2>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-gray-200 dark:border-gray-700">
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            User
                                        </th>
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Status
                                        </th>
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Expires
                                        </th>
                                        <th className="font-monologue-mono pb-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Created
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {organization.leases.map((lease) => (
                                        <tr key={lease.id} className="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td className="font-monologue-mono py-4 text-sm text-gray-900 dark:text-white">{lease.user.name}</td>
                                            <td className="py-4">
                                                <span className="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                    {lease.status}
                                                </span>
                                            </td>
                                            <td className="font-monologue-mono py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {new Date(lease.expires_at).toLocaleString()}
                                            </td>
                                            <td className="font-monologue-mono py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {new Date(lease.created_at).toLocaleString()}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </MonologueCard>
                )}
            </div>
        </AppLayout>
    );
}
