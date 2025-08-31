import { useState } from 'react';
import { useToast } from '@/hooks/ui/use-toast';
import { api } from '@/lib/api';

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

interface ScheduleUpdateItem {
    task_id: string;
    task_name: string;
    time: string;
}

interface DurationUpdateItem {
    task_id: string;
    task_name: string;
    duration: number;
}

interface OrderUpdateItem {
    task_id: string;
    task_name: string;
    order: number;
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
            schedule_updates: ScheduleUpdateItem[];
            duration_updates: DurationUpdateItem[];
            order_updates: OrderUpdateItem[];
        };
    };
    markdown: string;
}

export function useDailyPlanning() {
    const [planning, setPlanning] = useState<Planning | null>(null);
    const [loading] = useState(false);
    const [generating, setGenerating] = useState(false);
    const [updating, setUpdating] = useState(false);
    const { toast } = useToast();

    const generatePlanning = async (options?: Record<string, unknown>) => {
        try {
            setGenerating(true);
            const response = await api.post('/daily-planning/generate', options || {});
            
            if (response.data.success) {
                setPlanning(response.data.data);
                toast.success('Planning Generated', 'Your daily planning has been created successfully.');
                return response.data;
            } else {
                toast.warning('No Tasks Found', response.data.message || 'No tasks found for today.');
                return response.data;
            }
        } catch (error) {
            console.error('Failed to generate planning:', error);
            const errorMessage = error instanceof Error ? error.message : 
                (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Failed to generate daily planning.';
            toast.error('Generation Failed', errorMessage);
            return null;
        } finally {
            setGenerating(false);
        }
    };

    const updateTodoistTasks = async (planningId: string, updates: { type: 'all' | 'partial' | 'none'; selected?: string[] }) => {
        try {
            setUpdating(true);
            const response = await api.post('/daily-planning/update-tasks', {
                planning_id: planningId,
                updates,
            });

            if (response.data.success) {
                toast.success('Tasks Updated', response.data.data.message || 'Your Todoist tasks have been updated.');
                return response.data;
            } else {
                toast.error('Update Failed', response.data.message || 'Failed to update tasks.');
                return response.data;
            }
        } catch (error) {
            console.error('Failed to update tasks:', error);
            const errorMessage = error instanceof Error ? error.message : 
                (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Failed to update Todoist tasks.';
            toast.error('Update Failed', errorMessage);
            return null;
        } finally {
            setUpdating(false);
        }
    };

    return {
        planning,
        loading,
        generating,
        updating,
        generatePlanning,
        updateTodoistTasks,
    };
}