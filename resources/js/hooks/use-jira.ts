import { useState, useEffect } from 'react';
import { useToast } from '@/hooks/ui/use-toast';
import api from '@/lib/axios';

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

interface JiraIssue {
    id: string;
    key: string;
    fields: {
        summary: string;
        status: {
            name: string;
            statusCategory: {
                key: string;
                colorName: string;
            };
        };
        issuetype: {
            name: string;
            iconUrl: string;
        };
        priority: {
            name: string;
            iconUrl: string;
        };
        assignee?: {
            displayName: string;
            avatarUrls: {
                '48x48': string;
            };
        };
        created: string;
        updated: string;
    };
}

interface JiraSprint {
    id: number;
    name: string;
    state: 'active' | 'closed' | 'future';
    startDate?: string;
    endDate?: string;
    originBoardId: number;
    goal?: string;
}

export function useJira() {
    const [projects, setProjects] = useState<JiraProject[]>([]);
    const [boards, setBoards] = useState<JiraBoard[]>([]);
    const [issues, setIssues] = useState<JiraIssue[]>([]);
    const [sprints, setSprints] = useState<JiraSprint[]>([]);
    const [loading, setLoading] = useState(false);
    const { toast } = useToast();

    // Fetch projects on mount
    useEffect(() => {
        fetchProjects();
        fetchBoards();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const fetchProjects = async () => {
        try {
            setLoading(true);
            const response = await api.get('/api/jira/projects');
            if (response.data.success) {
                setProjects(response.data.data);
            }
        } catch (error) {
            console.error('Failed to fetch projects:', error);
            toast({
                title: 'Failed to fetch projects',
                description: 'Please check your JIRA connection.',
                variant: 'destructive',
            });
        } finally {
            setLoading(false);
        }
    };

    const fetchBoards = async () => {
        try {
            setLoading(true);
            const response = await api.get('/api/jira/boards');
            if (response.data.success) {
                setBoards(response.data.data.values || []);
            }
        } catch (error) {
            console.error('Failed to fetch boards:', error);
            toast({
                title: 'Failed to fetch boards',
                description: 'Please check your JIRA connection.',
                variant: 'destructive',
            });
        } finally {
            setLoading(false);
        }
    };

    const searchIssues = async (jql: string) => {
        try {
            setLoading(true);
            const response = await api.get('/api/jira/issues/search', {
                params: { jql, max_results: 50 }
            });
            if (response.data.success) {
                setIssues(response.data.data.issues || []);
            }
        } catch (error) {
            console.error('Failed to search issues:', error);
            toast({
                title: 'Failed to search issues',
                description: 'Please check your search query.',
                variant: 'destructive',
            });
        } finally {
            setLoading(false);
        }
    };

    const fetchBoardIssues = async (boardId: string) => {
        try {
            setLoading(true);
            const response = await api.get(`/api/jira/boards/${boardId}/issues`);
            if (response.data.success) {
                setIssues(response.data.data.issues || []);
            }
        } catch (error) {
            console.error('Failed to fetch board issues:', error);
            toast({
                title: 'Failed to fetch board issues',
                variant: 'destructive',
            });
        } finally {
            setLoading(false);
        }
    };

    const fetchSprints = async (boardId: string) => {
        try {
            setLoading(true);
            const response = await api.get(`/api/jira/boards/${boardId}/sprints`);
            if (response.data.success) {
                setSprints(response.data.data.values || []);
            }
        } catch (error) {
            console.error('Failed to fetch sprints:', error);
            toast({
                title: 'Failed to fetch sprints',
                variant: 'destructive',
            });
        } finally {
            setLoading(false);
        }
    };

    const createIssue = async (issueData: {
        project_key: string;
        issue_type: string;
        summary: string;
        description?: string;
        assignee?: string;
        priority?: string;
    }) => {
        try {
            setLoading(true);
            const response = await api.post('/api/jira/issues', issueData);
            if (response.data.success) {
                toast({
                    title: 'Issue created',
                    description: `Issue ${response.data.data.key} created successfully.`,
                });
                return response.data.data;
            }
        } catch (error) {
            console.error('Failed to create issue:', error);
            toast({
                title: 'Failed to create issue',
                description: 'Please check your input and try again.',
                variant: 'destructive',
            });
            throw error;
        } finally {
            setLoading(false);
        }
    };

    const updateIssue = async (issueKey: string, updates: Record<string, unknown>) => {
        try {
            setLoading(true);
            const response = await api.put(`/api/jira/issues/${issueKey}`, updates);
            if (response.data.success) {
                toast({
                    title: 'Issue updated',
                    description: `Issue ${issueKey} updated successfully.`,
                });
                return response.data.data;
            }
        } catch (error) {
            console.error('Failed to update issue:', error);
            toast({
                title: 'Failed to update issue',
                variant: 'destructive',
            });
            throw error;
        } finally {
            setLoading(false);
        }
    };

    const transitionIssue = async (issueKey: string, transitionId: string) => {
        try {
            setLoading(true);
            const response = await api.post(`/api/jira/issues/${issueKey}/transitions`, {
                transition_id: transitionId
            });
            if (response.data.success) {
                toast({
                    title: 'Issue transitioned',
                    description: `Issue ${issueKey} status updated.`,
                });
                return response.data.data;
            }
        } catch (error) {
            console.error('Failed to transition issue:', error);
            toast({
                title: 'Failed to transition issue',
                variant: 'destructive',
            });
            throw error;
        } finally {
            setLoading(false);
        }
    };

    return {
        projects,
        boards,
        issues,
        sprints,
        loading,
        refreshProjects: fetchProjects,
        refreshBoards: fetchBoards,
        searchIssues,
        fetchBoardIssues,
        fetchSprints,
        createIssue,
        updateIssue,
        transitionIssue,
    };
}