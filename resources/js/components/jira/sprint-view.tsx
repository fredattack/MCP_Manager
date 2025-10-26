import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { useJira } from '@/hooks/use-jira';
import { format } from 'date-fns';
import { Calendar, Target, Timer } from 'lucide-react';
import { useEffect, useState } from 'react';

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

interface JiraSprintViewProps {
    boards: JiraBoard[];
    loading: boolean;
}

export function JiraSprintView({ boards, loading }: JiraSprintViewProps) {
    const [selectedBoardId, setSelectedBoardId] = useState<string>('');
    const { sprints, fetchSprints, loading: sprintsLoading } = useJira();

    const scrumBoards = boards.filter((board) => board.type === 'scrum');

    useEffect(() => {
        if (selectedBoardId) {
            fetchSprints(selectedBoardId);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedBoardId]);

    const getSprintStateColor = (state: string) => {
        switch (state) {
            case 'active':
                return 'bg-green-500';
            case 'closed':
                return 'bg-gray-500';
            case 'future':
                return 'bg-blue-500';
            default:
                return 'bg-gray-500';
        }
    };

    if (loading) {
        return (
            <div className="space-y-4">
                <Skeleton className="h-10 w-full max-w-xs" />
                <div className="grid gap-4">
                    {[...Array(3)].map((_, i) => (
                        <Card key={i}>
                            <CardHeader>
                                <Skeleton className="mb-2 h-4 w-1/3" />
                                <Skeleton className="h-3 w-1/2" />
                            </CardHeader>
                        </Card>
                    ))}
                </div>
            </div>
        );
    }

    if (scrumBoards.length === 0) {
        return (
            <Card>
                <CardContent className="flex flex-col items-center justify-center py-8">
                    <Timer className="text-muted-foreground mb-4 h-12 w-12" />
                    <p className="text-muted-foreground">No Scrum boards found</p>
                    <p className="text-muted-foreground mt-2 text-sm">Sprints are only available for Scrum boards</p>
                </CardContent>
            </Card>
        );
    }

    return (
        <div className="space-y-4">
            {/* Board Selector */}
            <div className="flex items-center gap-4">
                <Select value={selectedBoardId} onValueChange={setSelectedBoardId}>
                    <SelectTrigger className="w-full max-w-xs">
                        <SelectValue placeholder="Select a Scrum board" />
                    </SelectTrigger>
                    <SelectContent>
                        {scrumBoards.map((board) => (
                            <SelectItem key={board.id} value={board.id.toString()}>
                                {board.name} ({board.location.projectKey})
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            {/* Sprints List */}
            {selectedBoardId && (
                <div className="space-y-4">
                    {sprintsLoading ? (
                        <div className="grid gap-4">
                            {[...Array(3)].map((_, i) => (
                                <Card key={i}>
                                    <CardHeader>
                                        <Skeleton className="mb-2 h-4 w-1/3" />
                                        <Skeleton className="h-3 w-1/2" />
                                    </CardHeader>
                                </Card>
                            ))}
                        </div>
                    ) : sprints.length === 0 ? (
                        <Card>
                            <CardContent className="py-8 text-center">
                                <p className="text-muted-foreground">No sprints found for this board</p>
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-4">
                            {sprints.map((sprint) => (
                                <Card key={sprint.id} className="transition-shadow hover:shadow-md">
                                    <CardHeader>
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <div className="mb-2 flex items-center gap-2">
                                                    <CardTitle className="text-lg">{sprint.name}</CardTitle>
                                                    <Badge variant="outline" className={`${getSprintStateColor(sprint.state)} border-0 text-white`}>
                                                        {sprint.state}
                                                    </Badge>
                                                </div>
                                                {sprint.goal && (
                                                    <CardDescription className="mb-3">
                                                        <div className="flex items-start gap-2">
                                                            <Target className="text-muted-foreground mt-0.5 h-4 w-4" />
                                                            <span>{sprint.goal}</span>
                                                        </div>
                                                    </CardDescription>
                                                )}
                                                <div className="text-muted-foreground flex items-center gap-4 text-sm">
                                                    {sprint.startDate && (
                                                        <div className="flex items-center gap-1">
                                                            <Calendar className="h-3 w-3" />
                                                            <span>
                                                                {format(new Date(sprint.startDate), 'MMM d')} -{' '}
                                                                {sprint.endDate ? format(new Date(sprint.endDate), 'MMM d, yyyy') : 'No end date'}
                                                            </span>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                            {sprint.state === 'active' && (
                                                <Button variant="outline" size="sm">
                                                    View Board
                                                </Button>
                                            )}
                                        </div>
                                    </CardHeader>
                                </Card>
                            ))}
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
