import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { useDeleteTodoistTask, useToggleTodoistTask } from '@/hooks/api/use-todoist-api';
import { useToast } from '@/hooks/ui/use-toast';
import { cn } from '@/lib/utils';
import { TodoistTask, UpdateTaskData } from '@/types/api/todoist.types';
import { Calendar, Edit2, Flag, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface TaskCardProps {
    task: TodoistTask;
    onUpdate?: (task: Partial<UpdateTaskData>) => void;
    onEdit?: (task: TodoistTask) => void;
    draggable?: boolean;
    compact?: boolean;
    className?: string;
}

const priorityColors = {
    1: 'bg-red-500',
    2: 'bg-orange-500',
    3: 'bg-blue-500',
    4: 'bg-gray-400',
} as const;

const priorityLabels = {
    1: 'P1',
    2: 'P2',
    3: 'P3',
    4: 'P4',
} as const;

export function TaskCard({ task, onEdit, draggable = false, compact = false, className }: TaskCardProps) {
    const [isHovered, setIsHovered] = useState(false);
    const { toast } = useToast();

    const toggleTask = useToggleTodoistTask();
    const deleteTask = useDeleteTodoistTask();

    const handleToggleComplete = async (checked: boolean) => {
        try {
            await toggleTask.mutateAsync({
                taskId: task.id,
                completed: checked,
            });

            toast.success(checked ? 'Task completed!' : 'Task reopened', task.content);
        } catch {
            toast.error('Failed to update task', 'Please try again or check your connection.');
        }
    };

    const handleDelete = async () => {
        try {
            await deleteTask.mutateAsync(task.id);
            toast.success('Task deleted', task.content);
        } catch {
            toast.error('Failed to delete task', 'Please try again or check your connection.');
        }
    };

    const formatDueDate = (due?: TodoistTask['due']) => {
        if (!due) return null;

        const date = new Date(due.date);
        const now = new Date();
        const isOverdue = date < now && !task.completed;
        const isToday = date.toDateString() === now.toDateString();
        const isTomorrow = date.toDateString() === new Date(now.getTime() + 24 * 60 * 60 * 1000).toDateString();

        let label = '';
        if (isToday) label = 'Today';
        else if (isTomorrow) label = 'Tomorrow';
        else label = date.toLocaleDateString();

        return {
            label,
            isOverdue,
            isToday,
            className: cn(
                'rounded-full px-2 py-1 text-xs',
                isOverdue && 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400',
                isToday && 'bg-orange-100 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400',
                !isOverdue && !isToday && 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            ),
        };
    };

    const dueInfo = formatDueDate(task.due);

    return (
        <div
            className={cn(
                'group relative rounded-sm border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900',
                'hover:shadow-atlassian transition-all duration-200',
                'p-4',
                compact && 'p-3',
                draggable && 'cursor-move',
                task.completed && 'opacity-60',
                className,
            )}
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
        >
            {/* Priority indicator */}
            <div className={cn('absolute top-0 bottom-0 left-0 w-1 rounded-l-sm', priorityColors[task.priority])} />

            {/* Task content */}
            <div className="flex items-start gap-3 pl-2">
                <Checkbox
                    checked={task.completed}
                    onCheckedChange={handleToggleComplete}
                    disabled={toggleTask.isPending}
                    className="text-primary focus:ring-primary mt-1 h-4 w-4 rounded border-gray-300"
                />

                <div className="min-w-0 flex-1">
                    {/* Task name */}
                    <h4
                        className={cn(
                            'text-sm font-medium break-words text-gray-900 dark:text-gray-100',
                            task.completed && 'text-gray-500 line-through dark:text-gray-400',
                        )}
                    >
                        {task.content}
                    </h4>

                    {/* Description */}
                    {task.description && <p className="mt-1 text-xs break-words text-gray-600 dark:text-gray-400">{task.description}</p>}

                    {/* Metadata */}
                    <div className="mt-2 flex flex-wrap items-center gap-2">
                        {/* Priority */}
                        {task.priority !== 4 && (
                            <Badge variant="secondary" className="h-5 px-1.5 text-xs">
                                <Flag className="mr-1 h-3 w-3" />
                                {priorityLabels[task.priority]}
                            </Badge>
                        )}

                        {/* Due date */}
                        {dueInfo && (
                            <Badge variant="secondary" className={dueInfo.className}>
                                <Calendar className="mr-1 h-3 w-3" />
                                {dueInfo.label}
                            </Badge>
                        )}

                        {/* Labels */}
                        {task.labels?.map((label) => (
                            <Badge key={label} variant="outline" className="h-5 px-1.5 text-xs">
                                {label}
                            </Badge>
                        ))}
                    </div>
                </div>

                {/* Actions */}
                <div className={cn('flex gap-1 transition-opacity duration-200', isHovered ? 'opacity-100' : 'opacity-0')}>
                    <Button variant="ghost" size="sm" className="h-7 w-7 p-0 hover:bg-gray-100 dark:hover:bg-gray-800" onClick={() => onEdit?.(task)}>
                        <Edit2 className="h-3 w-3" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-7 w-7 p-0 text-red-600 hover:bg-gray-100 dark:hover:bg-gray-800"
                        onClick={handleDelete}
                        disabled={deleteTask.isPending}
                    >
                        <Trash2 className="h-3 w-3" />
                    </Button>
                </div>
            </div>
        </div>
    );
}
