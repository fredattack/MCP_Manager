import { CreateTaskData, TodoistProject, TodoistTask, UpdateTaskData } from '@/types/api/todoist.types';
import apiClient from '../client';
import { getAuthHeaders } from '../mcp-auth';

export const todoistApi = {
    // Projects
    getProjects: async (): Promise<TodoistProject[]> => {
        try {
            // Try MCP server via proxy first
            const response = await apiClient.get('/api/mcp/todoist/projects', {
                headers: getAuthHeaders(),
            });
            return response.data;
        } catch (error) {
            console.warn('MCP server unavailable, using fallback:', error);
            // Fallback to Laravel mock routes
            const response = await apiClient.get('/api/integrations/todoist/projects');
            return response.data;
        }
    },

    // Tasks
    getTasks: async (projectId?: string): Promise<TodoistTask[]> => {
        try {
            const params = projectId ? { project_id: projectId } : {};
            const response = await apiClient.get('/api/mcp/todoist/tasks', {
                params,
                headers: getAuthHeaders(),
            });
            return response.data;
        } catch (error) {
            console.warn('MCP server unavailable, using fallback:', error);
            // Fallback to Laravel mock routes
            const params = projectId ? { project_id: projectId } : {};
            const response = await apiClient.get('/api/integrations/todoist/tasks', { params });
            return response.data;
        }
    },

    createTask: async (data: CreateTaskData): Promise<TodoistTask> => {
        try {
            const response = await apiClient.post('/api/mcp/todoist/tasks', data, {
                headers: getAuthHeaders(),
            });
            return response.data;
        } catch (error) {
            console.warn('MCP server unavailable, using fallback:', error);
            // Fallback to Laravel mock routes
            const response = await apiClient.post('/api/integrations/todoist/tasks', data);
            return response.data;
        }
    },

    updateTask: async (taskId: string, data: UpdateTaskData): Promise<TodoistTask> => {
        try {
            const response = await apiClient.put(`/api/mcp/todoist/tasks/${taskId}`, data, {
                headers: getAuthHeaders(),
            });
            return response.data;
        } catch (error) {
            console.warn('MCP server unavailable, using fallback:', error);
            // Fallback to Laravel mock routes
            const response = await apiClient.put(`/api/integrations/todoist/tasks/${taskId}`, data);
            return response.data;
        }
    },

    deleteTask: async (taskId: string): Promise<void> => {
        try {
            await apiClient.delete(`/api/mcp/todoist/tasks/${taskId}`, {
                headers: getAuthHeaders(),
            });
        } catch (error) {
            console.warn('MCP server unavailable, using fallback:', error);
            // Fallback to Laravel mock routes
            await apiClient.delete(`/api/integrations/todoist/tasks/${taskId}`);
        }
    },

    completeTask: async (taskId: string): Promise<void> => {
        try {
            await apiClient.post(
                `/api/mcp/todoist/tasks/${taskId}/complete`,
                {},
                {
                    headers: getAuthHeaders(),
                },
            );
        } catch (error) {
            console.warn('MCP server unavailable, using fallback:', error);
            // Fallback to Laravel mock routes
            await apiClient.post(`/api/integrations/todoist/tasks/${taskId}/complete`);
        }
    },

    uncompleteTask: async (taskId: string): Promise<void> => {
        try {
            await apiClient.post(
                `/api/mcp/todoist/tasks/${taskId}/uncomplete`,
                {},
                {
                    headers: getAuthHeaders(),
                },
            );
        } catch (error) {
            console.warn('MCP server unavailable, using fallback:', error);
            // Fallback to Laravel mock routes
            await apiClient.post(`/api/integrations/todoist/tasks/${taskId}/uncomplete`);
        }
    },
};
