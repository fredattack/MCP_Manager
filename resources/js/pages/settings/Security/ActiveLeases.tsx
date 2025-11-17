import { MonologueBadge } from '@/components/ui/MonologueBadge';
import { MonologueButton } from '@/components/ui/MonologueButton';
import { MonologueCard } from '@/components/ui/MonologueCard';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, PageProps } from '@/types';
import { Head, router } from '@inertiajs/react';
import { AlertCircle, Building2, Clock, Eye, Key, Loader2, Search, Server, Shield, Trash2, X } from 'lucide-react';
import { useEffect, useState } from 'react';

interface CredentialLease {
    id: number;
    lease_id: string;
    server_id: string;
    services: string[];
    status: 'active' | 'expired' | 'revoked';
    credential_scope: 'personal' | 'organization' | 'mixed';
    organization_id?: number;
    organization_name?: string;
    expires_at: string;
    created_at: string;
    renewal_count: number;
    max_renewals: number;
    client_info?: Record<string, unknown>;
    client_ip?: string;
    revoked_at?: string;
    revocation_reason?: string;
}

interface ActiveLeasesProps extends PageProps {
    leases: CredentialLease[];
    stats: {
        active_leases: number;
        total_services: number;
        expiring_soon: number;
        organizations_with_leases: number;
    };
    organizations: Array<{ id: number; name: string }>;
    available_services: Array<{ value: string; label: string }>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings' },
    { title: 'Active Leases', href: '/settings/security/active-leases' },
];

// Countdown timer hook
function useCountdown(targetDate: string) {
    const [timeLeft, setTimeLeft] = useState<string>('');

    useEffect(() => {
        const calculateTimeLeft = () => {
            const now = new Date().getTime();
            const target = new Date(targetDate).getTime();
            const difference = target - now;

            if (difference <= 0) {
                return 'Expired';
            }

            const days = Math.floor(difference / (1000 * 60 * 60 * 24));
            const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((difference % (1000 * 60)) / 1000);

            if (days > 0) {
                return `${days}d ${hours}h ${minutes}m`;
            }
            if (hours > 0) {
                return `${hours}h ${minutes}m ${seconds}s`;
            }
            return `${minutes}m ${seconds}s`;
        };

        setTimeLeft(calculateTimeLeft());

        const timer = setInterval(() => {
            setTimeLeft(calculateTimeLeft());
        }, 1000);

        return () => clearInterval(timer);
    }, [targetDate]);

    return timeLeft;
}

function ExpirationCountdown({ expiresAt }: { expiresAt: string }) {
    const countdown = useCountdown(expiresAt);
    const now = new Date().getTime();
    const target = new Date(expiresAt).getTime();
    const minutesLeft = Math.floor((target - now) / (1000 * 60));

    const isExpired = minutesLeft <= 0;
    const isExpiringSoon = minutesLeft > 0 && minutesLeft <= 10;

    return (
        <div className="flex items-center gap-2">
            {isExpired ? (
                <AlertCircle className="h-4 w-4 text-red-500" />
            ) : isExpiringSoon ? (
                <AlertCircle className="h-4 w-4 text-yellow-500" />
            ) : (
                <Clock className="h-4 w-4 text-gray-400" />
            )}
            <span
                className={`font-mono text-sm ${
                    isExpired
                        ? 'text-red-600 dark:text-red-400'
                        : isExpiringSoon
                          ? 'text-yellow-600 dark:text-yellow-400'
                          : 'text-gray-600 dark:text-gray-400'
                }`}
            >
                {countdown}
            </span>
        </div>
    );
}

export default function ActiveLeases({ leases, stats, organizations, available_services }: ActiveLeasesProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [filterStatus, setFilterStatus] = useState<string>('all');
    const [filterOrg, setFilterOrg] = useState<string>('all');
    const [filterService, setFilterService] = useState<string>('all');
    const [selectedLease, setSelectedLease] = useState<CredentialLease | null>(null);
    const [isDetailsOpen, setIsDetailsOpen] = useState(false);
    const [isRevoking, setIsRevoking] = useState(false);
    const [revokeLeaseId, setRevokeLeaseId] = useState<number | null>(null);

    // Filter leases
    const filteredLeases = leases.filter((lease) => {
        const matchesSearch =
            !searchQuery ||
            lease.lease_id.toLowerCase().includes(searchQuery.toLowerCase()) ||
            lease.server_id.toLowerCase().includes(searchQuery.toLowerCase());

        const matchesStatus = filterStatus === 'all' || lease.status === filterStatus;

        const matchesOrg =
            filterOrg === 'all' ||
            (filterOrg === 'personal' && lease.credential_scope === 'personal') ||
            (filterOrg !== 'personal' && lease.organization_id?.toString() === filterOrg);

        const matchesService = filterService === 'all' || lease.services.includes(filterService);

        return matchesSearch && matchesStatus && matchesOrg && matchesService;
    });

    const handleRevoke = (leaseId: number) => {
        if (!confirm('Are you sure you want to revoke this lease? This will immediately invalidate all credentials.')) {
            return;
        }

        setIsRevoking(true);
        setRevokeLeaseId(leaseId);

        router.delete(`/settings/security/active-leases/${leaseId}/revoke`, {
            onFinish: () => {
                setIsRevoking(false);
                setRevokeLeaseId(null);
            },
        });
    };

    const handleViewDetails = (lease: CredentialLease) => {
        setSelectedLease(lease);
        setIsDetailsOpen(true);
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'active':
                return (
                    <MonologueBadge variant="success" size="sm">
                        Active
                    </MonologueBadge>
                );
            case 'expired':
                return (
                    <MonologueBadge variant="default" size="sm" className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                        Expired
                    </MonologueBadge>
                );
            case 'revoked':
                return (
                    <MonologueBadge variant="default" size="sm" className="bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                        Revoked
                    </MonologueBadge>
                );
            default:
                return null;
        }
    };

    const getScopeBadge = (scope: string) => {
        switch (scope) {
            case 'personal':
                return (
                    <MonologueBadge
                        variant="outline"
                        size="sm"
                        className="border-cyan-500 bg-cyan-50 text-cyan-700 dark:bg-cyan-950/20 dark:text-cyan-400"
                    >
                        Personal
                    </MonologueBadge>
                );
            case 'organization':
                return (
                    <MonologueBadge
                        variant="outline"
                        size="sm"
                        className="border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400"
                    >
                        Organization
                    </MonologueBadge>
                );
            case 'mixed':
                return (
                    <MonologueBadge
                        variant="outline"
                        size="sm"
                        className="border-purple-500 bg-purple-50 text-purple-700 dark:bg-purple-950/20 dark:text-purple-400"
                    >
                        Mixed
                    </MonologueBadge>
                );
            default:
                return null;
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Active Leases" />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="font-monologue-serif flex items-center gap-3 text-4xl font-normal tracking-tight text-gray-900 dark:text-white">
                            <Shield className="h-8 w-8 text-gray-900 dark:text-white" />
                            Active Credential Leases
                        </h1>
                        <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">
                            Monitor and manage active credential leases for MCP servers
                        </p>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid auto-rows-min gap-6 md:grid-cols-4">
                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Active Leases</p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">{stats.active_leases}</p>
                            </div>
                            <div className="rounded-lg bg-green-500/10 p-3">
                                <Key className="h-6 w-6 text-green-500" />
                            </div>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Total Services</p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">{stats.total_services}</p>
                            </div>
                            <div className="rounded-lg bg-blue-500/10 p-3">
                                <Server className="h-6 w-6 text-blue-500" />
                            </div>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Expiring Soon</p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">{stats.expiring_soon}</p>
                            </div>
                            <div className="rounded-lg bg-yellow-500/10 p-3">
                                <AlertCircle className="h-6 w-6 text-yellow-500" />
                            </div>
                        </div>
                    </MonologueCard>

                    <MonologueCard variant="elevated">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Organizations</p>
                                <p className="font-monologue-serif mt-2 text-3xl font-normal text-gray-900 dark:text-white">
                                    {stats.organizations_with_leases}
                                </p>
                            </div>
                            <div className="rounded-lg bg-purple-500/10 p-3">
                                <Building2 className="h-6 w-6 text-purple-500" />
                            </div>
                        </div>
                    </MonologueCard>
                </div>

                {/* Filters & Search */}
                <MonologueCard variant="elevated">
                    <div className="flex flex-col gap-4 md:flex-row">
                        {/* Search */}
                        <div className="relative flex-1">
                            <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search by Lease ID or Server ID..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="font-monologue-mono w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        {/* Status Filter */}
                        <Select value={filterStatus} onValueChange={setFilterStatus}>
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="expired">Expired</SelectItem>
                                <SelectItem value="revoked">Revoked</SelectItem>
                            </SelectContent>
                        </Select>

                        {/* Organization Filter */}
                        <Select value={filterOrg} onValueChange={setFilterOrg}>
                            <SelectTrigger className="w-[200px]">
                                <SelectValue placeholder="All Organizations" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Organizations</SelectItem>
                                <SelectItem value="personal">Personal Only</SelectItem>
                                {organizations.map((org) => (
                                    <SelectItem key={org.id} value={org.id.toString()}>
                                        {org.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        {/* Service Filter */}
                        <Select value={filterService} onValueChange={setFilterService}>
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="All Services" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Services</SelectItem>
                                {available_services.map((service) => (
                                    <SelectItem key={service.value} value={service.value}>
                                        {service.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    {/* Results count */}
                    <div className="font-monologue-mono mt-4 text-sm text-gray-600 dark:text-gray-400">
                        Showing {filteredLeases.length} of {leases.length} lease{leases.length !== 1 ? 's' : ''}
                    </div>
                </MonologueCard>

                {/* Leases Table */}
                <MonologueCard variant="elevated">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-gray-200 dark:border-gray-700">
                                    <th className="font-monologue-mono px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Lease ID
                                    </th>
                                    <th className="font-monologue-mono px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Server
                                    </th>
                                    <th className="font-monologue-mono px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Services
                                    </th>
                                    <th className="font-monologue-mono px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Status
                                    </th>
                                    <th className="font-monologue-mono px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Scope
                                    </th>
                                    <th className="font-monologue-mono px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Expires In
                                    </th>
                                    <th className="font-monologue-mono px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Renewals
                                    </th>
                                    <th className="font-monologue-mono px-4 py-3 text-right text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                {filteredLeases.length === 0 ? (
                                    <tr>
                                        <td colSpan={8} className="py-8 text-center text-gray-500 dark:text-gray-400">
                                            No leases found
                                        </td>
                                    </tr>
                                ) : (
                                    filteredLeases.map((lease) => (
                                        <tr key={lease.id} className="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td className="font-monologue-mono px-4 py-3 text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                                {lease.lease_id.substring(0, 16)}...
                                            </td>
                                            <td className="font-monologue-mono px-4 py-3 text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">
                                                {lease.server_id}
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="flex flex-wrap gap-1">
                                                    {lease.services.slice(0, 3).map((service) => (
                                                        <MonologueBadge key={service} variant="outline" size="sm">
                                                            {service}
                                                        </MonologueBadge>
                                                    ))}
                                                    {lease.services.length > 3 && (
                                                        <MonologueBadge variant="outline" size="sm">
                                                            +{lease.services.length - 3}
                                                        </MonologueBadge>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-4 py-3">{getStatusBadge(lease.status)}</td>
                                            <td className="px-4 py-3">{getScopeBadge(lease.credential_scope)}</td>
                                            <td className="px-4 py-3">
                                                {lease.status === 'active' ? (
                                                    <ExpirationCountdown expiresAt={lease.expires_at} />
                                                ) : (
                                                    <span className="font-mono text-sm text-gray-500">—</span>
                                                )}
                                            </td>
                                            <td className="font-monologue-mono px-4 py-3 text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">
                                                {lease.renewal_count} / {lease.max_renewals}
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="flex justify-end gap-2">
                                                    <MonologueButton
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() => handleViewDetails(lease)}
                                                        title="View Details"
                                                    >
                                                        <Eye className="h-4 w-4" />
                                                    </MonologueButton>
                                                    {lease.status === 'active' && (
                                                        <MonologueButton
                                                            size="sm"
                                                            variant="ghost"
                                                            onClick={() => handleRevoke(lease.id)}
                                                            disabled={isRevoking && revokeLeaseId === lease.id}
                                                            title="Revoke Lease"
                                                            className="text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/20"
                                                        >
                                                            {isRevoking && revokeLeaseId === lease.id ? (
                                                                <Loader2 className="h-4 w-4 animate-spin" />
                                                            ) : (
                                                                <Trash2 className="h-4 w-4" />
                                                            )}
                                                        </MonologueButton>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </MonologueCard>
            </div>

            {/* Lease Details Modal */}
            <Dialog open={isDetailsOpen} onOpenChange={setIsDetailsOpen}>
                <DialogContent className="max-w-3xl">
                    <DialogHeader>
                        <DialogTitle className="font-monologue-serif flex items-center gap-2 text-2xl">
                            <Key className="h-6 w-6" />
                            Lease Details
                        </DialogTitle>
                        <DialogDescription>Complete information about this credential lease</DialogDescription>
                    </DialogHeader>

                    {selectedLease && (
                        <div className="space-y-6 py-4">
                            {/* Basic Info */}
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Lease ID
                                    </label>
                                    <p className="font-monologue-mono mt-1 text-sm text-gray-900 dark:text-white">{selectedLease.lease_id}</p>
                                </div>
                                <div>
                                    <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Server ID
                                    </label>
                                    <p className="font-monologue-mono mt-1 text-sm text-gray-900 dark:text-white">{selectedLease.server_id}</p>
                                </div>
                                <div>
                                    <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Status
                                    </label>
                                    <div className="mt-1">{getStatusBadge(selectedLease.status)}</div>
                                </div>
                                <div>
                                    <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Credential Scope
                                    </label>
                                    <div className="mt-1">{getScopeBadge(selectedLease.credential_scope)}</div>
                                </div>
                            </div>

                            {/* Services */}
                            <div>
                                <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                    Services ({selectedLease.services.length})
                                </label>
                                <div className="mt-2 flex flex-wrap gap-2">
                                    {selectedLease.services.map((service) => (
                                        <MonologueBadge key={service} variant="outline">
                                            {service}
                                        </MonologueBadge>
                                    ))}
                                </div>
                            </div>

                            {/* Organization */}
                            {selectedLease.organization_name && (
                                <div>
                                    <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Organization
                                    </label>
                                    <p className="mt-1 text-sm text-gray-900 dark:text-white">{selectedLease.organization_name}</p>
                                </div>
                            )}

                            {/* Timing Info */}
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Created At
                                    </label>
                                    <p className="font-monologue-mono mt-1 text-sm text-gray-900 dark:text-white">
                                        {new Date(selectedLease.created_at).toLocaleString()}
                                    </p>
                                </div>
                                <div>
                                    <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Expires At
                                    </label>
                                    <p className="font-monologue-mono mt-1 text-sm text-gray-900 dark:text-white">
                                        {new Date(selectedLease.expires_at).toLocaleString()}
                                    </p>
                                </div>
                                <div>
                                    <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                        Renewal Count
                                    </label>
                                    <p className="mt-1 text-sm text-gray-900 dark:text-white">
                                        {selectedLease.renewal_count} / {selectedLease.max_renewals}
                                    </p>
                                </div>
                                {selectedLease.status === 'active' && (
                                    <div>
                                        <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Time Remaining
                                        </label>
                                        <div className="mt-1">
                                            <ExpirationCountdown expiresAt={selectedLease.expires_at} />
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Client Info */}
                            {selectedLease.client_ip && (
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                            Client IP
                                        </label>
                                        <p className="font-monologue-mono mt-1 text-sm text-gray-900 dark:text-white">{selectedLease.client_ip}</p>
                                    </div>
                                </div>
                            )}

                            {/* Revocation Info */}
                            {selectedLease.status === 'revoked' && (
                                <div className="rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/20">
                                    <div className="flex items-start gap-2">
                                        <X className="mt-0.5 h-5 w-5 text-red-600 dark:text-red-400" />
                                        <div className="flex-1">
                                            <h4 className="font-medium text-red-900 dark:text-red-300">Lease Revoked</h4>
                                            {selectedLease.revoked_at && (
                                                <p className="font-monologue-mono mt-1 text-sm text-red-700 dark:text-red-400">
                                                    Revoked at: {new Date(selectedLease.revoked_at).toLocaleString()}
                                                </p>
                                            )}
                                            {selectedLease.revocation_reason && (
                                                <p className="mt-1 text-sm text-red-700 dark:text-red-400">
                                                    Reason: {selectedLease.revocation_reason}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Actions */}
                            <div className="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                                <MonologueButton variant="ghost" onClick={() => setIsDetailsOpen(false)}>
                                    Close
                                </MonologueButton>
                                {selectedLease.status === 'active' && (
                                    <MonologueButton
                                        variant="default"
                                        onClick={() => {
                                            setIsDetailsOpen(false);
                                            handleRevoke(selectedLease.id);
                                        }}
                                        className="bg-red-600 text-white hover:bg-red-700"
                                    >
                                        <Trash2 className="mr-2 h-4 w-4" />
                                        Revoke Lease
                                    </MonologueButton>
                                )}
                            </div>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
