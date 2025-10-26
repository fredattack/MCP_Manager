import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { CheckCircle2, Clock, Zap } from 'lucide-react';

interface Task {
    id: string;
    content: string;
    project_name?: string;
    priority: string;
    duration?: number;
    energy?: string;
    scheduled_time?: string;
}

interface TaskListDisplayProps {
    tasks: Task[];
    title: string;
    variant?: 'primary' | 'secondary';
}

export function TaskListDisplay({ tasks, title, variant = 'primary' }: TaskListDisplayProps) {
    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'P1':
                return 'destructive';
            case 'P2':
                return 'default';
            case 'P3':
                return 'secondary';
            case 'P4':
                return 'outline';
            default:
                return 'outline';
        }
    };

    const getEnergyIcon = (energy?: string) => {
        if (!energy) return null;
        const color = energy === 'high' ? 'text-red-500' : energy === 'low' ? 'text-green-500' : 'text-yellow-500';
        return <Zap className={cn('h-3 w-3', color)} />;
    };

    return (
        <Card className={variant === 'secondary' ? 'opacity-90' : ''}>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <CheckCircle2 className="h-5 w-5" />
                    {title}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="space-y-3">
                    {tasks.map((task, index) => (
                        <div
                            key={task.id}
                            className={cn(
                                'flex items-start gap-3 rounded-lg border p-3 transition-colors',
                                variant === 'primary' ? 'hover:bg-accent hover:border-accent-foreground/20' : 'hover:bg-muted',
                            )}
                        >
                            <div className="bg-primary/10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold">
                                {index + 1}
                            </div>
                            <div className="flex-1 space-y-2">
                                <p className="font-medium">{task.content}</p>
                                <div className="flex flex-wrap gap-2">
                                    {task.project_name && (
                                        <Badge variant="secondary" className="text-xs">
                                            {task.project_name}
                                        </Badge>
                                    )}
                                    <Badge variant={getPriorityColor(task.priority)} className="text-xs">
                                        {task.priority}
                                    </Badge>
                                    {task.duration && (
                                        <Badge variant="outline" className="text-xs">
                                            <Clock className="mr-1 h-3 w-3" />
                                            {task.duration} min
                                        </Badge>
                                    )}
                                    {task.energy && (
                                        <Badge variant="outline" className="text-xs">
                                            {getEnergyIcon(task.energy)}
                                            <span className="ml-1">{task.energy} energy</span>
                                        </Badge>
                                    )}
                                    {task.scheduled_time && (
                                        <Badge variant="outline" className="text-xs">
                                            <Clock className="mr-1 h-3 w-3" />
                                            {task.scheduled_time}
                                        </Badge>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {variant === 'primary' && (
                    <div className="bg-muted mt-4 rounded-lg p-3">
                        <p className="text-muted-foreground text-sm">
                            <strong>Règle d'exécution :</strong> Ne passez à la tâche suivante qu'une fois la précédente terminée.
                        </p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
