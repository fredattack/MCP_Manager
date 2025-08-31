import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { LayoutGrid, Kanban } from 'lucide-react';

interface JiraBoard {
    id: number;
    name: string;
    type: 'scrum' | 'kanban';
    location: {
        projectId: string;
        projectKey: string;
        projectName: string;
    };
}

interface JiraBoardListProps {
    boards: JiraBoard[];
    loading: boolean;
}

export function JiraBoardList({ boards, loading }: JiraBoardListProps) {
    if (loading) {
        return (
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                {[...Array(6)].map((_, i) => (
                    <Card key={i}>
                        <CardHeader>
                            <Skeleton className="h-4 w-3/4" />
                            <Skeleton className="h-3 w-1/2 mt-2" />
                        </CardHeader>
                    </Card>
                ))}
            </div>
        );
    }

    if (boards.length === 0) {
        return (
            <Card>
                <CardContent className="flex flex-col items-center justify-center py-8">
                    <LayoutGrid className="h-12 w-12 text-muted-foreground mb-4" />
                    <p className="text-muted-foreground">No boards found</p>
                </CardContent>
            </Card>
        );
    }

    return (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {boards.map((board) => (
                <Card key={board.id} className="hover:shadow-lg transition-shadow cursor-pointer">
                    <CardHeader>
                        <div className="flex items-start justify-between">
                            <div className="flex items-center gap-2">
                                {board.type === 'scrum' ? (
                                    <LayoutGrid className="h-5 w-5 text-muted-foreground" />
                                ) : (
                                    <Kanban className="h-5 w-5 text-muted-foreground" />
                                )}
                                <CardTitle className="text-lg">{board.name}</CardTitle>
                            </div>
                            <Badge variant={board.type === 'scrum' ? 'default' : 'secondary'}>
                                {board.type}
                            </Badge>
                        </div>
                        <CardDescription>
                            Project: {board.location.projectName} ({board.location.projectKey})
                        </CardDescription>
                    </CardHeader>
                </Card>
            ))}
        </div>
    );
}