import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { MonologueCard } from '@/components/ui/MonologueCard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings' },
    { title: 'Organizations', href: '/settings/organizations' },
    { title: 'Create', href: '/settings/organizations/create' },
];

interface CreateOrganizationForm {
    name: string;
    slug: string;
    billing_email: string;
    max_members: number;
}

export default function Create() {
    const { data, setData, post, processing, errors } = useForm<CreateOrganizationForm>({
        name: '',
        slug: '',
        billing_email: '',
        max_members: 5,
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('settings.organizations.store'));
    };

    const generateSlug = (name: string) => {
        const slug = name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
        setData('slug', slug);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Organization" />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">
                            Create Organization
                        </h1>
                        <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">
                            Set up a new organization to collaborate with your team
                        </p>
                    </div>
                    <Link
                        href="/settings/organizations"
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
                                onChange={(e) => {
                                    setData('name', e.target.value);
                                    if (!data.slug) {
                                        generateSlug(e.target.value);
                                    }
                                }}
                                className="font-monologue-mono"
                                placeholder="Acme Inc."
                                required
                            />
                            <InputError message={errors.name} />
                        </div>

                        {/* Slug */}
                        <div className="space-y-2">
                            <Label htmlFor="slug" className="font-monologue-mono text-sm">
                                Slug <span className="text-red-500">*</span>
                            </Label>
                            <Input
                                id="slug"
                                type="text"
                                value={data.slug}
                                onChange={(e) => setData('slug', e.target.value)}
                                className="font-monologue-mono"
                                placeholder="acme-inc"
                                required
                            />
                            <p className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">
                                Used in URLs. Lowercase letters, numbers, and hyphens only.
                            </p>
                            <InputError message={errors.slug} />
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
                                placeholder="billing@acme.com"
                                required
                            />
                            <p className="font-monologue-mono text-xs text-gray-500 dark:text-gray-400">
                                Email address for billing and administrative notifications
                            </p>
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
                                Maximum number of members allowed in this organization
                            </p>
                            <InputError message={errors.max_members} />
                        </div>

                        {/* Submit Button */}
                        <div className="flex items-center gap-4 pt-4">
                            <Button
                                type="submit"
                                disabled={processing}
                                className="bg-cyan-500 hover:bg-cyan-600 dark:bg-cyan-600 dark:hover:bg-cyan-700"
                            >
                                {processing ? 'Creating...' : 'Create Organization'}
                            </Button>
                            <Link
                                href="/settings/organizations"
                                className="font-monologue-mono text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                </MonologueCard>
            </div>
        </AppLayout>
    );
}
