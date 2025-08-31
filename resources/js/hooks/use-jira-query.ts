import { useServiceQuery } from './use-service-query';
import { useServiceMutation, useCreateMutation, useUpdateMutation } from './use-service-mutation';
import { queryKeys } from '@/lib/react-query';

// Types
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

interface BoardsResponse {
    values: JiraBoard[];
    startAt: number;
    maxResults: number;
    total: number;
}

interface IssuesResponse {
    issues: JiraIssue[];
    startAt: number;
    maxResults: number;
    total: number;
}

interface SprintsResponse {
    values: JiraSprint[];
    startAt: number;
    maxResults: number;
    total: number;
}

// Query hooks
export function useJiraProjects() {
    return useServiceQuery<JiraProject[]>(
        queryKeys.jiraProjects(),
        '/api/jira/projects'
    );
}

export function useJiraProject(projectKey: string) {
    return useServiceQuery<JiraProject>(
        queryKeys.jiraProject(projectKey),
        `/api/jira/projects/${projectKey}`,
        {
            enabled: !!projectKey,
        }
    );
}

export function useJiraBoards(filters?: { project_key?: string; type?: 'scrum' | 'kanban' }) {
    const params = new URLSearchParams(filters as Record<string, string>).toString();
    const url = params ? `/api/jira/boards?${params}` : '/api/jira/boards';
    
    return useServiceQuery<BoardsResponse>(
        queryKeys.jiraBoards(filters),
        url
    );
}

export function useJiraBoard(boardId: string) {
    return useServiceQuery<JiraBoard>(
        queryKeys.jiraBoard(boardId),
        `/api/jira/boards/${boardId}`,
        {
            enabled: !!boardId,
        }
    );
}

export function useJiraIssues(jql: string, options?: { max_results?: number; start_at?: number }) {
    const params = new URLSearchParams({
        jql,
        ...(options?.max_results && { max_results: options.max_results.toString() }),
        ...(options?.start_at && { start_at: options.start_at.toString() }),
    }).toString();
    
    return useServiceQuery<IssuesResponse>(
        queryKeys.jiraIssues({ jql, ...options }),
        `/api/jira/issues/search?${params}`,
        {
            enabled: !!jql,
        }
    );
}

export function useJiraBoardIssues(boardId: string, filters?: { epic?: string; sprint_id?: string }) {
    const params = new URLSearchParams(filters as Record<string, string>).toString();
    const url = params ? `/api/jira/boards/${boardId}/issues?${params}` : `/api/jira/boards/${boardId}/issues`;
    
    return useServiceQuery<IssuesResponse>(
        queryKeys.jiraIssues({ boardId, ...filters }),
        url,
        {
            enabled: !!boardId,
        }
    );
}

export function useJiraIssue(issueKey: string, expand?: string) {
    const params = expand ? `?expand=${expand}` : '';
    
    return useServiceQuery<JiraIssue>(
        queryKeys.jiraIssue(issueKey),
        `/api/jira/issues/${issueKey}${params}`,
        {
            enabled: !!issueKey,
        }
    );
}

export function useJiraSprints(boardId: string, state?: 'active' | 'closed' | 'future') {
    const params = state ? `?state=${state}` : '';
    
    return useServiceQuery<SprintsResponse>(
        queryKeys.jiraSprints(boardId),
        `/api/jira/boards/${boardId}/sprints${params}`,
        {
            enabled: !!boardId,
        }
    );
}

// Mutation hooks
export function useCreateJiraIssue() {
    return useCreateMutation<JiraIssue, {
        project_key: string;
        issue_type: string;
        summary: string;
        description?: string;
        assignee?: string;
        priority?: string;
        epic_link?: string;
        sprint_id?: string;
        story_points?: number;
    }>('/api/jira/issues', {
        invalidateQueries: [queryKeys.jiraIssues()],
        successMessage: 'Issue created successfully',
    });
}

export function useUpdateJiraIssue() {
    return useUpdateMutation<JiraIssue, {
        issueKey: string;
        summary?: string;
        description?: string;
        assignee?: string;
        priority?: string;
        story_points?: number;
    }>((variables) => `/api/jira/issues/${variables.issueKey}`, {
        invalidateQueries: [queryKeys.jiraIssues()],
        successMessage: 'Issue updated successfully',
    });
}

export function useTransitionJiraIssue() {
    return useServiceMutation<unknown, {
        issueKey: string;
        transition_id: string;
        comment?: string;
    }>((variables) => `/api/jira/issues/${variables.issueKey}/transitions`, 'post', {
        invalidateQueries: [queryKeys.jiraIssues()],
        successMessage: 'Issue status updated',
    });
}

export function useAssignJiraIssue() {
    return useServiceMutation<unknown, {
        issueKey: string;
        assignee: string;
    }>((variables) => `/api/jira/issues/${variables.issueKey}/assign`, 'put', {
        invalidateQueries: [queryKeys.jiraIssues()],
        successMessage: 'Issue assigned successfully',
    });
}

// Convenience hook that combines multiple queries
export function useJiraDashboard() {
    const projects = useJiraProjects();
    const boards = useJiraBoards();
    
    return {
        projects,
        boards,
        isLoading: projects.isLoading || boards.isLoading,
        isError: projects.isError || boards.isError,
    };
}