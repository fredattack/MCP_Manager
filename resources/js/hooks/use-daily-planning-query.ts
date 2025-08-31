import { useServiceQuery } from './use-service-query';
import { useServiceMutation } from './use-service-mutation';
import { queryKeys } from '@/lib/react-query';

// Types from the existing hook
interface Task {
    id: string;
    content: string;
    project_name?: string;
    priority: string;
    duration?: number;
    energy?: string;
    scheduled_time?: string;
}

interface TimeBlock {
    start: string;
    end: string;
    duration: number;
    title: string;
    task_id?: string;
    period: 'morning' | 'afternoon';
}

interface Alert {
    type: string;
    severity: 'high' | 'medium' | 'low';
    message: string;
    details?: unknown[];
}

interface Planning {
    planning_id: string;
    planning: {
        has_tasks: boolean;
        date: string;
        mit?: Task;
        top_tasks: Task[];
        time_blocks: TimeBlock[];
        additional_tasks: Task[];
        alerts: Alert[];
        summary: {
            total_tasks: number;
            total_work_time: number;
            total_break_time: number;
            p1_tasks: number;
            hexeko_tasks: number;
        };
        todoist_updates: {
            schedule_updates: Array<{ task_id: string; task_name: string; time: string }>;
            duration_updates: Array<{ task_id: string; task_name: string; duration: number }>;
            order_updates: Array<{ task_id: string; task_name: string; order: number }>;
        };
    };
    markdown: string;
}

// Query hooks
export function useDailyPlanning(date?: string) {
    return useServiceQuery<Planning>(
        queryKeys.dailyPlan(date),
        '/daily-planning',
        {
            // Don't automatically fetch on mount since we want user to trigger generation
            enabled: false,
        }
    );
}

// Mutation hooks
export function useGenerateDailyPlanning() {
    return useServiceMutation<Planning, Record<string, unknown>>(
        '/daily-planning/generate',
        'post',
        {
            invalidateQueries: [queryKeys.dailyPlanning()] as const,
            successMessage: 'Daily planning generated successfully',
            onSuccess: (data) => {
                // Cache the generated planning
                // Dynamic import to avoid circular dependency
                import('@/lib/react-query').then(({ queryClient }) => {
                    queryClient.setQueryData(queryKeys.dailyPlan(data.planning.date), data);
                });
            },
        }
    );
}

export function useUpdateTodoistTasks() {
    return useServiceMutation<
        { success: boolean; message: string },
        { planning_id: string; updates: { type: 'all' | 'partial' | 'none'; selected?: string[] } }
    >(
        '/daily-planning/update-tasks',
        'post',
        {
            successMessage: 'Todoist tasks updated successfully',
        }
    );
}

// Combined hook that provides all functionality
export function useDailyPlanningFeature() {
    const planningQuery = useDailyPlanning();
    const generateMutation = useGenerateDailyPlanning();
    const updateTasksMutation = useUpdateTodoistTasks();

    const generatePlanning = async (options?: Record<string, unknown>) => {
        const result = await generateMutation.mutateAsync(options || {});
        return { success: true, data: result, planning: result };
    };

    const updateTodoistTasks = async (
        planningId: string, 
        updates: { type: 'all' | 'partial' | 'none'; selected?: string[] }
    ) => {
        const result = await updateTasksMutation.mutateAsync({ planning_id: planningId, updates });
        return { success: true, data: result };
    };

    return {
        planning: planningQuery.data || null,
        loading: planningQuery.isLoading,
        generating: generateMutation.isPending,
        updating: updateTasksMutation.isPending,
        generatePlanning,
        updateTodoistTasks,
    };
}