import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Skeleton } from '@/components/ui/skeleton';
import { Folder } from 'lucide-react';

interface JiraProject {
    id: string;
    key: string;
    name: string;
    projectTypeKey: string;
    avatarUrls: {
        '48x48': string;
        '32x32': string;
        '16x16': string;
    };
}

interface JiraProjectListProps {
    projects: JiraProject[];
    loading: boolean;
}

export function JiraProjectList({ projects, loading }: JiraProjectListProps) {
    if (loading) {
        return (
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                {[...Array(6)].map((_, i) => (
                    <Card key={i}>
                        <CardHeader>
                            <Skeleton className="h-8 w-8 rounded-full mb-2" />
                            <Skeleton className="h-4 w-3/4" />
                            <Skeleton className="h-3 w-1/2 mt-2" />
                        </CardHeader>
                    </Card>
                ))}
            </div>
        );
    }

    if (projects.length === 0) {
        return (
            <Card>
                <CardContent className="flex flex-col items-center justify-center py-8">
                    <Folder className="h-12 w-12 text-muted-foreground mb-4" />
                    <p className="text-muted-foreground">No projects found</p>
                </CardContent>
            </Card>
        );
    }

    return (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {projects.map((project) => (
                <Card key={project.id} className="hover:shadow-lg transition-shadow cursor-pointer">
                    <CardHeader>
                        <div className="flex items-start justify-between">
                            <Avatar className="h-8 w-8">
                                <AvatarImage src={project.avatarUrls['48x48']} alt={project.name} />
                                <AvatarFallback>{project.key}</AvatarFallback>
                            </Avatar>
                            <Badge variant="outline">{project.key}</Badge>
                        </div>
                        <CardTitle className="text-lg">{project.name}</CardTitle>
                        <CardDescription>
                            Type: {project.projectTypeKey}
                        </CardDescription>
                    </CardHeader>
                </Card>
            ))}
        </div>
    );
}