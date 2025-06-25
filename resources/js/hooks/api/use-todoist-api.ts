import apiClient from '@/lib/api/client';
import { todoistApi } from '@/lib/api/endpoints/todoist';
import { getAuthHeaders } from '@/lib/api/mcp-auth';
import { UpdateTaskData } from '@/types/api/todoist.types';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';

export function useTodoistProjects() {
    return useQuery({
        queryKey: ['todoist', 'projects'],
        queryFn: todoistApi.getProjects,
        staleTime: 5 * 60 * 1000, // 5 minutes
    });
}

export function useTodoistTasks(projectId?: string) {
    return useQuery({
        queryKey: ['todoist', 'tasks', projectId],
        queryFn: () => todoistApi.getTasks(projectId),
        staleTime: 2 * 60 * 1000, // 2 minutes
    });
}

export function useTodoistTodayTasks() {
    return useQuery({
        queryKey: ['todoist', 'tasks', 'today'],
        queryFn: async () => {
            try {
                const response = await apiClient.get('/api/mcp/todoist/tasks/today', {
                    headers: getAuthHeaders(),
                });
                return response.data;
            } catch {
                console.warn('MCP Today tasks unavailable, using fallback');
                // Fallback to regular tasks with client-side filtering
                const allTasks = await todoistApi.getTasks();
                const today = new Date().toISOString().split('T')[0];
                return allTasks.filter((task) => {
                    if (task.completed) return false;
                    if (!task.due?.date) return true; // Include tasks without due dates in "today"
                    const taskDate = task.due.date.split('T')[0];
                    return taskDate === today;
                });
            }
        },
        staleTime: 2 * 60 * 1000, // 2 minutes
    });
}

export function useTodoistUpcomingTasks() {
    return useQuery({
        queryKey: ['todoist', 'tasks', 'upcoming'],
        queryFn: async () => {
            try {
                const response = await apiClient.get('/api/mcp/todoist/tasks/upcoming', {
                    headers: getAuthHeaders(),
                });
                return response.data;
            } catch {
                console.warn('MCP Upcoming tasks unavailable, using fallback');
                // Fallback to regular tasks with client-side filtering
                const allTasks = await todoistApi.getTasks();
                const today = new Date().toISOString().split('T')[0];
                return allTasks.filter((task) => {
                    if (task.completed) return false;
                    if (!task.due?.date) return false;
                    const taskDate = task.due.date.split('T')[0];
                    return taskDate > today;
                });
            }
        },
        staleTime: 2 * 60 * 1000, // 2 minutes
    });
}

export function useCreateTodoistTask() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: todoistApi.createTask,
        onSuccess: (newTask) => {
            // Invalidate tasks queries
            queryClient.invalidateQueries({ queryKey: ['todoist', 'tasks'] });

            // Optimistically add the task to the cache
            queryClient.setQueryData(['todoist', 'tasks', newTask.project_id], (oldData: unknown) => {
                if (!oldData) return [newTask];
                return [...(oldData as unknown[]), newTask];
            });
        },
    });
}

export function useUpdateTodoistTask() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({ taskId, data }: { taskId: string; data: UpdateTaskData }) => todoistApi.updateTask(taskId, data),
        onMutate: async ({ taskId, data }) => {
            // Cancel outgoing refetches
            await queryClient.cancelQueries({ queryKey: ['todoist', 'tasks'] });

            // Snapshot previous values
            const previousTasks = queryClient.getQueriesData({ queryKey: ['todoist', 'tasks'] });

            // Optimistically update all task queries
            queryClient.setQueriesData({ queryKey: ['todoist', 'tasks'] }, (oldData: unknown) => {
                if (!oldData) return oldData;
                return (oldData as unknown[]).map((task: unknown) =>
                    (task as { id: string }).id === taskId ? { ...(task as object), ...data } : task,
                );
            });

            return { previousTasks };
        },
        onError: (err, variables, context) => {
            // Rollback on error
            if (context?.previousTasks) {
                context.previousTasks.forEach(([queryKey, data]) => {
                    queryClient.setQueryData(queryKey, data);
                });
            }
        },
        onSettled: () => {
            queryClient.invalidateQueries({ queryKey: ['todoist', 'tasks'] });
        },
    });
}

export function useDeleteTodoistTask() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: todoistApi.deleteTask,
        onSuccess: (_, taskId) => {
            // Remove task from all queries
            queryClient.setQueriesData({ queryKey: ['todoist', 'tasks'] }, (oldData: unknown) => {
                if (!oldData) return oldData;
                return (oldData as unknown[]).filter((task: unknown) => (task as { id: string }).id !== taskId);
            });
        },
    });
}

export function useToggleTodoistTask() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async ({ taskId, completed }: { taskId: string; completed: boolean }) => {
            return completed ? todoistApi.completeTask(taskId) : todoistApi.uncompleteTask(taskId);
        },
        onMutate: async ({ taskId, completed }) => {
            await queryClient.cancelQueries({ queryKey: ['todoist', 'tasks'] });

            const previousTasks = queryClient.getQueriesData({ queryKey: ['todoist', 'tasks'] });

            queryClient.setQueriesData({ queryKey: ['todoist', 'tasks'] }, (oldData: unknown) => {
                if (!oldData) return oldData;
                return (oldData as unknown[]).map((task: unknown) =>
                    (task as { id: string }).id === taskId ? { ...(task as object), completed } : task,
                );
            });

            return { previousTasks };
        },
        onError: (err, variables, context) => {
            if (context?.previousTasks) {
                context.previousTasks.forEach(([queryKey, data]) => {
                    queryClient.setQueryData(queryKey, data);
                });
            }
        },
        onSettled: () => {
            queryClient.invalidateQueries({ queryKey: ['todoist', 'tasks'] });
        },
    });
}
