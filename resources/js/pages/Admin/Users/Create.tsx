import { MonologueCard } from '@/components/ui/MonologueCard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type PageProps } from '@/types';
import type { RoleOption } from '@/types/admin';
import { Head, useForm } from '@inertiajs/react';
import { Save, X } from 'lucide-react';

interface UsersCreateProps extends PageProps {
    roles: RoleOption[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin' },
    { title: 'Users', href: '/admin/users' },
    { title: 'Create', href: '/admin/users/create' },
];

export default function Create({ roles }: UsersCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        role: 'user' as string,
        is_active: true,
        notes: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/users', {
            onSuccess: () => {
                // Handled by Inertia
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create User" />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div>
                    <h1 className="font-monologue-serif text-4xl font-normal tracking-tight text-gray-900 dark:text-white">Create User</h1>
                    <p className="font-monologue-mono mt-2 text-sm tracking-wide text-gray-600 dark:text-gray-400">Add a new user to the system</p>
                </div>

                <MonologueCard variant="elevated">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Name */}
                        <div>
                            <label htmlFor="name" className="font-monologue-mono mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Name *
                            </label>
                            <input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="font-monologue-mono w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required
                            />
                            {errors.name && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.name}</p>}
                        </div>

                        {/* Email */}
                        <div>
                            <label htmlFor="email" className="font-monologue-mono mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email *
                            </label>
                            <input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className="font-monologue-mono w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required
                            />
                            {errors.email && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.email}</p>}
                        </div>

                        {/* Password */}
                        <div>
                            <label htmlFor="password" className="font-monologue-mono mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Password (leave blank to auto-generate)
                            </label>
                            <input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className="font-monologue-mono w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                            {errors.password && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.password}</p>}
                        </div>

                        {/* Role */}
                        <div>
                            <label htmlFor="role" className="font-monologue-mono mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Role *
                            </label>
                            <select
                                id="role"
                                value={data.role}
                                onChange={(e) => setData('role', e.target.value)}
                                className="font-monologue-mono w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required
                            >
                                {roles.map((role) => (
                                    <option key={role.value} value={role.value}>
                                        {role.label}
                                    </option>
                                ))}
                            </select>
                            {errors.role && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.role}</p>}
                        </div>

                        {/* Active Status */}
                        <div className="flex items-center gap-2">
                            <input
                                id="is_active"
                                type="checkbox"
                                checked={data.is_active}
                                onChange={(e) => setData('is_active', e.target.checked)}
                                className="h-4 w-4 rounded border-gray-300 text-cyan-600 focus:ring-cyan-500"
                            />
                            <label htmlFor="is_active" className="font-monologue-mono text-sm font-medium text-gray-700 dark:text-gray-300">
                                Active
                            </label>
                        </div>

                        {/* Notes */}
                        <div>
                            <label htmlFor="notes" className="font-monologue-mono mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notes
                            </label>
                            <textarea
                                id="notes"
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                rows={3}
                                className="font-monologue-mono w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                            {errors.notes && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.notes}</p>}
                        </div>

                        {/* Actions */}
                        <div className="flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center gap-2 rounded-lg bg-cyan-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cyan-600 disabled:opacity-50"
                            >
                                <Save className="h-4 w-4" />
                                {processing ? 'Creating...' : 'Create User'}
                            </button>
                            <button
                                type="button"
                                onClick={() => window.history.back()}
                                className="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                            >
                                <X className="h-4 w-4" />
                                Cancel
                            </button>
                        </div>
                    </form>
                </MonologueCard>
            </div>
        </AppLayout>
    );
}
