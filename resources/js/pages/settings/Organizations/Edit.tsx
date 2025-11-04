import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { MonologueCard } from '@/components/ui/MonologueCard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PageProps } from '@/types';
import type { Organization } from '@/types/organizations';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Trash2 } from 'lucide-react';
import { FormEventHandler } from 'react';

interface EditOrganizationProps extends PageProps {
    organization: Organization;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings' },
    { title: 'Organizations', href: '/settings/organizations' },
];

interface EditOrganizationForm {
    name: string;
    billing_email: string;
    max_members: number;
    status: 'active' | 'suspended' | 'deleted';
}

export default function Edit({ organization }: EditOrganizationProps) {
    const { data, setData, put, processing, errors } = useForm<EditOrganizationForm>({
        name: organization.name,
        billing_email: organization.billing_email,
        max_members: organization.max_members,
        status: organization.status,
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('settings.organizations.update', organization.id));
    };

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this organization? This action cannot be undone and will revoke all active leases.')) {
            router.delete(route('settings.organizations.destroy', organization.id));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${organization.name}`} />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">Edit Organization</h1>
                        <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">
                            Update {organization.name} settings
                        </p>
                    </div>
                    <Link
                        href={`/settings/organizations/${organization.id}`}
                        className="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Back
                    </Link>
                </div>

                {/* Form */}
                <MonologueCard variant="elevated" className="max-w-2xl">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Organization Name */}
                        <div className="space-y-2">
                            <Label htmlFor="name" className="font-monologue-mono text-sm">
                                Organization Name <span className="text-red-500">*</span>
                            </Label>
                            <Input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="font-monologue-mono"
                                required
                            />
                            <InputError message={errors.name} />
                        </div>

                        {/* Slug (Read-only) */}
                        <div className="space-y-2">
                            <Label htmlFor="slug" className="font-monologue-mono text-sm">
                                Slug
                            </Label>
                            <Input
                                id="slug"
                                type="text"
                                value={organization.slug}
                                className="font-monologue-mono bg-gray-50 dark:bg-gray-800"
                                disabled
                            />
                            <p className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">Slug cannot be changed after creation</p>
                        </div>

                        {/* Billing Email */}
                        <div className="space-y-2">
                            <Label htmlFor="billing_email" className="font-monologue-mono text-sm">
                                Billing Email <span className="text-red-500">*</span>
                            </Label>
                            <Input
                                id="billing_email"
                                type="email"
                                value={data.billing_email}
                                onChange={(e) => setData('billing_email', e.target.value)}
                                className="font-monologue-mono"
                                required
                            />
                            <InputError message={errors.billing_email} />
                        </div>

                        {/* Max Members */}
                        <div className="space-y-2">
                            <Label htmlFor="max_members" className="font-monologue-mono text-sm">
                                Maximum Members <span className="text-red-500">*</span>
                            </Label>
                            <Input
                                id="max_members"
                                type="number"
                                min="1"
                                max="100"
                                value={data.max_members}
                                onChange={(e) => setData('max_members', parseInt(e.target.value))}
                                className="font-monologue-mono"
                                required
                            />
                            <p className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">
                                Current members: {organization.members_count || 0}
                            </p>
                            <InputError message={errors.max_members} />
                        </div>

                        {/* Status */}
                        <div className="space-y-2">
                            <Label htmlFor="status" className="font-monologue-mono text-sm">
                                Status <span className="text-red-500">*</span>
                            </Label>
                            <select
                                id="status"
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value as 'active' | 'suspended' | 'deleted')}
                                className="font-monologue-mono w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <InputError message={errors.status} />
                        </div>

                        {/* Submit Button */}
                        <div className="flex items-center gap-4 pt-4">
                            <Button
                                type="submit"
                                disabled={processing}
                                className="bg-cyan-500 hover:bg-cyan-600 dark:bg-cyan-600 dark:hover:bg-cyan-700"
                            >
                                {processing ? 'Saving...' : 'Save Changes'}
                            </Button>
                            <Link
                                href={`/settings/organizations/${organization.id}`}
                                className="font-monologue-mono text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                </MonologueCard>

                {/* Danger Zone */}
                <MonologueCard variant="elevated" className="max-w-2xl border-red-200 dark:border-red-900">
                    <div className="space-y-4">
                        <div>
                            <h3 className="font-monologue-serif text-lg font-medium text-red-600 dark:text-red-400">Danger Zone</h3>
                            <p className="font-monologue-mono mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Permanently delete this organization and all associated data
                            </p>
                        </div>
                        <Button type="button" onClick={handleDelete} variant="destructive" className="bg-red-600 hover:bg-red-700">
                            <Trash2 className="mr-2 h-4 w-4" />
                            Delete Organization
                        </Button>
                    </div>
                </MonologueCard>
            </div>
        </AppLayout>
    );
}
