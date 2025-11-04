import { Head } from '@inertiajs/react';
import { Eye, GitBranch, GitFork, Github, Gitlab, RefreshCw, Search, Star } from 'lucide-react';
import { useState } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';

interface GitRepository {
    id: number;
    provider: 'github' | 'gitlab';
    external_id: string;
    name: string;
    full_name: string;
    description?: string;
    url: string;
    default_branch: string;
    is_private: boolean;
    stars_count: number;
    forks_count: number;
    watchers_count: number;
    language?: string;
    last_sync_at?: string;
}

interface RepositoriesProps {
    repositories?: GitRepository[];
}

export default function GitRepositories({ repositories = [] }: RepositoriesProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [visibilityFilter, setVisibilityFilter] = useState<'all' | 'public' | 'private'>('all');
    const [providerFilter, setProviderFilter] = useState<'all' | 'github' | 'gitlab'>('all');
    const [isSyncing, setIsSyncing] = useState(false);

    const filteredRepositories = repositories.filter((repo) => {
        const matchesSearch =
            repo.name.toLowerCase().includes(searchQuery.toLowerCase()) || repo.description?.toLowerCase().includes(searchQuery.toLowerCase());

        const matchesVisibility =
            visibilityFilter === 'all' || (visibilityFilter === 'public' && !repo.is_private) || (visibilityFilter === 'private' && repo.is_private);

        const matchesProvider = providerFilter === 'all' || repo.provider === providerFilter;

        return matchesSearch && matchesVisibility && matchesProvider;
    });

    const handleSync = async () => {
        setIsSyncing(true);
        try {
            await fetch('/api/git/repositories/sync', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            window.location.reload();
        } catch (error) {
            console.error('Sync error:', error);
        } finally {
            setIsSyncing(false);
        }
    };

    const handleClone = async (repositoryId: number) => {
        try {
            await fetch(`/api/git/repositories/${repositoryId}/clone`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            alert('Repository clone initié avec succès');
        } catch (error) {
            console.error('Clone error:', error);
            alert('Erreur lors du clonage du repository');
        }
    };

    const getProviderIcon = (provider: string) => {
        return provider === 'github' ? <Github className="h-4 w-4" /> : <Gitlab className="h-4 w-4" />;
    };

    return (
        <AppLayout>
            <Head title="Repositories Git" />

            <div className="container mx-auto py-8">
                <div className="mb-8 flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Repositories Git</h1>
                        <p className="text-muted-foreground mt-2">Gérez vos repositories GitHub et GitLab</p>
                    </div>
                    <Button onClick={handleSync} disabled={isSyncing}>
                        <RefreshCw className={`mr-2 h-4 w-4 ${isSyncing ? 'animate-spin' : ''}`} />
                        {isSyncing ? 'Synchronisation...' : 'Synchroniser'}
                    </Button>
                </div>

                {/* Filters */}
                <Card className="mb-6">
                    <CardContent className="pt-6">
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="relative">
                                <Search className="text-muted-foreground absolute top-3 left-3 h-4 w-4" />
                                <Input
                                    placeholder="Rechercher un repository..."
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    className="pl-9"
                                />
                            </div>

                            <Select value={visibilityFilter} onValueChange={(value: any) => setVisibilityFilter(value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Visibilité" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Tous</SelectItem>
                                    <SelectItem value="public">Public</SelectItem>
                                    <SelectItem value="private">Privé</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={providerFilter} onValueChange={(value: any) => setProviderFilter(value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Provider" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Tous les providers</SelectItem>
                                    <SelectItem value="github">GitHub</SelectItem>
                                    <SelectItem value="gitlab">GitLab</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Repositories List */}
                {filteredRepositories.length === 0 ? (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <p className="text-muted-foreground">
                                {repositories.length === 0
                                    ? 'Aucun repository synchronisé. Cliquez sur "Synchroniser" pour charger vos repositories.'
                                    : 'Aucun repository ne correspond aux filtres sélectionnés.'}
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-4">
                        {filteredRepositories.map((repo) => (
                            <Card key={repo.id} data-testid="workflow-card">
                                <CardHeader>
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <div className="flex items-center gap-2">
                                                {getProviderIcon(repo.provider)}
                                                <CardTitle className="text-lg">
                                                    <a href={repo.url} target="_blank" rel="noopener noreferrer" className="hover:underline">
                                                        {repo.full_name}
                                                    </a>
                                                </CardTitle>
                                                {repo.is_private && <Badge variant="secondary">Privé</Badge>}
                                            </div>
                                            {repo.description && <CardDescription className="mt-2">{repo.description}</CardDescription>}
                                        </div>
                                        <Button onClick={() => handleClone(repo.id)} size="sm">
                                            Cloner
                                        </Button>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-muted-foreground flex flex-wrap gap-4 text-sm">
                                        {repo.language && (
                                            <div className="flex items-center gap-1">
                                                <div className="h-3 w-3 rounded-full" style={{ backgroundColor: '#3178c6' }} />
                                                <span>{repo.language}</span>
                                            </div>
                                        )}

                                        <div className="flex items-center gap-1">
                                            <Star className="h-4 w-4" />
                                            <span>{repo.stars_count}</span>
                                        </div>

                                        <div className="flex items-center gap-1">
                                            <GitFork className="h-4 w-4" />
                                            <span>{repo.forks_count}</span>
                                        </div>

                                        <div className="flex items-center gap-1">
                                            <Eye className="h-4 w-4" />
                                            <span>{repo.watchers_count}</span>
                                        </div>

                                        <div className="flex items-center gap-1">
                                            <GitBranch className="h-4 w-4" />
                                            <span>{repo.default_branch}</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
