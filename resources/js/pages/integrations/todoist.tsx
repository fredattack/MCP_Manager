import { TaskCard } from '@/components/integrations/todoist/components/TaskCard/TaskCard';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    useCreateTodoistTask,
    useTodoistProjects,
    useTodoistTasks,
    useTodoistTodayTasks,
    useTodoistUpcomingTasks,
} from '@/hooks/api/use-todoist-api';
import { useToast } from '@/hooks/ui/use-toast';
import AppLayout from '@/layouts/app-layout';
import { CreateTaskData, TodoistTask } from '@/types/api/todoist.types';
import { Head } from '@inertiajs/react';
import { Calendar, Clock, Inbox, Plus, Search } from 'lucide-react';
import { useState } from 'react';

type ViewFilter = 'all' | 'today' | 'upcoming' | 'inbox';

export default function TodoistPage() {
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedProject, setSelectedProject] = useState<string>('');
    const [newTaskContent, setNewTaskContent] = useState('');
    const [showQuickAdd, setShowQuickAdd] = useState(false);
    const [viewFilter, setViewFilter] = useState<ViewFilter>('all');

    const { toast } = useToast();

    const { data: projects } = useTodoistProjects();

    // Use different hooks based on view filter
    const { data: allTasks, isLoading: allTasksLoading } = useTodoistTasks(selectedProject || undefined);
    const { data: todayTasks, isLoading: todayTasksLoading } = useTodoistTodayTasks();
    const { data: upcomingTasks, isLoading: upcomingTasksLoading } = useTodoistUpcomingTasks();

    const createTask = useCreateTodoistTask();

    // Determine which data and loading state to use based on filter
    const tasks = viewFilter === 'today' ? todayTasks : viewFilter === 'upcoming' ? upcomingTasks : allTasks;
    const tasksLoading = viewFilter === 'today' ? todayTasksLoading : viewFilter === 'upcoming' ? upcomingTasksLoading : allTasksLoading;

    const handleCreateTask = async () => {
        if (!newTaskContent.trim()) return;

        const taskData: CreateTaskData = {
            content: newTaskContent,
            project_id: selectedProject || undefined,
        };

        try {
            await createTask.mutateAsync(taskData);
            setNewTaskContent('');
            setShowQuickAdd(false);
            toast.success('Task created successfully', newTaskContent);
        } catch {
            toast.error('Failed to create task', 'Please try again or check your connection.');
        }
    };

    // Apply search filter only (MCP endpoints handle view filtering)
    let filteredTasks =
        tasks?.filter(
            (task: TodoistTask) =>
                task.content.toLowerCase().includes(searchQuery.toLowerCase()) || task.description?.toLowerCase().includes(searchQuery.toLowerCase()),
        ) || [];

    // Apply inbox filter for client-side filtering (since there's no MCP endpoint for inbox)
    if (viewFilter === 'inbox') {
        filteredTasks = filteredTasks.filter((task: TodoistTask) => !task.completed && !task.project_id);
    }

    const completedTasks = filteredTasks.filter((task: TodoistTask) => task.completed);
    const pendingTasks = filteredTasks.filter((task: TodoistTask) => !task.completed);
    return (
        <AppLayout>
            <Head title="Todoist Integration" />

            <div className="mx-auto max-w-7xl p-6">
                {/* Header */}
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Todoist Tasks</h1>
                        <p className="mt-1 text-gray-600 dark:text-gray-400">Manage your Todoist tasks and projects</p>
                    </div>

                    <Button onClick={() => setShowQuickAdd(true)}>
                        <Plus className="mr-2 h-4 w-4" />
                        New Task
                    </Button>
                </div>

                {/* View Filter Buttons */}
                <div className="mb-4 flex gap-2">
                    <Button variant={viewFilter === 'all' ? 'default' : 'outline'} size="sm" onClick={() => setViewFilter('all')} className="h-8">
                        <Inbox className="mr-2 h-4 w-4" />
                        All Tasks
                    </Button>
                    <Button variant={viewFilter === 'today' ? 'default' : 'outline'} size="sm" onClick={() => setViewFilter('today')} className="h-8">
                        <Calendar className="mr-2 h-4 w-4" />
                        Today
                    </Button>
                    <Button
                        variant={viewFilter === 'upcoming' ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setViewFilter('upcoming')}
                        className="h-8"
                    >
                        <Clock className="mr-2 h-4 w-4" />
                        Upcoming
                    </Button>
                    <Button variant={viewFilter === 'inbox' ? 'default' : 'outline'} size="sm" onClick={() => setViewFilter('inbox')} className="h-8">
                        <Inbox className="mr-2 h-4 w-4" />
                        Inbox
                    </Button>
                </div>

                {/* Search and Project Filters */}
                <div className="mb-6 flex gap-4">
                    <div className="relative max-w-md flex-1">
                        <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
                        <Input placeholder="Search tasks..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pl-10" />
                    </div>

                    <select
                        value={selectedProject}
                        onChange={(e) => setSelectedProject(e.target.value)}
                        className="focus:ring-primary/20 rounded-sm border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:outline-none"
                    >
                        <option value="">All Projects</option>
                        {projects?.map((project) => (
                            <option key={project.id} value={project.id}>
                                {project.name}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Quick Add */}
                {showQuickAdd && (
                    <Card className="mb-6">
                        <CardContent className="pt-6">
                            <div className="flex gap-2">
                                <Input
                                    placeholder="What needs to be done?"
                                    value={newTaskContent}
                                    onChange={(e) => setNewTaskContent(e.target.value)}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') handleCreateTask();
                                        if (e.key === 'Escape') setShowQuickAdd(false);
                                    }}
                                    autoFocus
                                />
                                <Button onClick={handleCreateTask} disabled={!newTaskContent.trim() || createTask.isPending}>
                                    Add
                                </Button>
                                <Button variant="outline" onClick={() => setShowQuickAdd(false)}>
                                    Cancel
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Loading State */}
                {tasksLoading && (
                    <div className="space-y-4">
                        {Array.from({ length: 5 }).map((_, i) => (
                            <div key={i} className="animate-pulse">
                                <div className="h-24 rounded-sm bg-gray-200 dark:bg-gray-800" />
                            </div>
                        ))}
                    </div>
                )}

                {/* Tasks */}
                {!tasksLoading && (
                    <div className="space-y-6">
                        {/* Pending Tasks */}
                        {pendingTasks.length > 0 && (
                            <div>
                                <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {viewFilter === 'today'
                                        ? 'Today'
                                        : viewFilter === 'upcoming'
                                          ? 'Upcoming'
                                          : viewFilter === 'inbox'
                                            ? 'Inbox'
                                            : 'To Do'}{' '}
                                    ({pendingTasks.length})
                                </h2>
                                <div className="space-y-3">
                                    {pendingTasks.map((task: TodoistTask) => (
                                        <TaskCard key={task.id} task={task} />
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Completed Tasks */}
                        {completedTasks.length > 0 && (
                            <div>
                                <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Completed ({completedTasks.length})</h2>
                                <div className="space-y-3">
                                    {completedTasks.map((task: TodoistTask) => (
                                        <TaskCard key={task.id} task={task} compact />
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Empty State */}
                        {filteredTasks.length === 0 && !tasksLoading && (
                            <Card>
                                <CardContent className="py-12 text-center">
                                    <div className="mb-4 text-gray-400">
                                        <svg className="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={1}
                                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                            />
                                        </svg>
                                    </div>
                                    <h3 className="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">No tasks found</h3>
                                    <p className="mb-4 text-gray-600 dark:text-gray-400">
                                        {searchQuery ? 'No tasks match your search criteria.' : "You don't have any tasks yet."}
                                    </p>
                                    <Button onClick={() => setShowQuickAdd(true)}>
                                        <Plus className="mr-2 h-4 w-4" />
                                        Create your first task
                                    </Button>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
