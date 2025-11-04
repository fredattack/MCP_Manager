import { Button } from '@/components/ui/button';
import { MonologueCard } from '@/components/ui/MonologueCard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Building2, Calendar, Mail, Shield } from 'lucide-react';

interface AcceptInvitationProps extends PageProps {
    invitation: {
        token: string;
        email: string;
        role: 'admin' | 'member' | 'guest';
        is_expired: boolean;
        expires_at: string;
        organization: {
            id: number;
            name: string;
            slug: string;
        };
        inviter: {
            name: string;
        };
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Accept Invitation', href: '#' },
];

export default function AcceptInvitation({ invitation }: AcceptInvitationProps) {
    const handleAccept = () => {
        router.post(`/invitations/${invitation.token}/accept`);
    };

    const getRoleBadgeColor = (role: string) => {
        switch (role) {
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Accept Organization Invitation" />

            <div className="flex h-full flex-1 flex-col items-center justify-center gap-6 p-6">
                <div className="w-full max-w-2xl">
                    {/* Header */}
                    <div className="mb-6 text-center">
                        <h1 className="font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">
                            Organization Invitation
                        </h1>
                        <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">
                            You've been invited to join an organization
                        </p>
                    </div>

                    {/* Invitation Card */}
                    <MonologueCard variant="elevated">
                        {invitation.is_expired ? (
                            <div className="text-center">
                                <div className="mb-4 flex justify-center">
                                    <div className="rounded-full bg-red-100 p-4 dark:bg-red-900/30">
                                        <Calendar className="h-8 w-8 text-red-600 dark:text-red-400" />
                                    </div>
                                </div>
                                <h2 className="font-monologue-serif mb-2 text-xl text-red-600 dark:text-red-400">Invitation Expired</h2>
                                <p className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                                    This invitation has expired. Please contact the organization owner for a new invitation.
                                </p>
                                <div className="mt-6">
                                    <Link
                                        href="/dashboard"
                                        className="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                    >
                                        Go to Dashboard
                                    </Link>
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-6">
                                <div className="flex items-center gap-4">
                                    <div className="rounded-lg bg-cyan-100 p-3 dark:bg-cyan-900/30">
                                        <Building2 className="h-8 w-8 text-cyan-600 dark:text-cyan-400" />
                                    </div>
                                    <div>
                                        <h2 className="font-monologue-serif text-2xl text-gray-900 dark:text-white">
                                            {invitation.organization.name}
                                        </h2>
                                        <p className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">{invitation.organization.slug}</p>
                                    </div>
                                </div>

                                <div className="space-y-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                                    <div className="flex items-center gap-3">
                                        <Mail className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">Email</p>
                                            <p className="font-monologue-mono text-sm text-gray-900 dark:text-white">{invitation.email}</p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-3">
                                        <Shield className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">Role</p>
                                            <span
                                                className={`mt-1 inline-flex rounded-full px-2 py-1 text-xs font-medium ${getRoleBadgeColor(invitation.role)}`}
                                            >
                                                {invitation.role.charAt(0).toUpperCase() + invitation.role.slice(1)}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-3">
                                        <Calendar className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">Invited by</p>
                                            <p className="font-monologue-mono text-sm text-gray-900 dark:text-white">{invitation.inviter.name}</p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-3">
                                        <Calendar className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">Expires</p>
                                            <p className="font-monologue-mono text-sm text-gray-900 dark:text-white">
                                                {new Date(invitation.expires_at).toLocaleString()}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button
                                        onClick={handleAccept}
                                        className="flex-1 bg-cyan-500 hover:bg-cyan-600 dark:bg-cyan-600 dark:hover:bg-cyan-700"
                                    >
                                        Accept Invitation
                                    </Button>
                                    <Link
                                        href="/dashboard"
                                        className="font-monologue-mono text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                                    >
                                        Decline
                                    </Link>
                                </div>
                            </div>
                        )}
                    </MonologueCard>
                </div>
            </div>
        </AppLayout>
    );
}
