import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: 5 * 60 * 1000, // 5 minutes
            gcTime: 10 * 60 * 1000, // 10 minutes (formerly cacheTime)
            retry: 3,
            retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
            refetchOnWindowFocus: false,
            refetchOnMount: true,
        },
        mutations: {
            retry: 1,
            retryDelay: 1000,
        },
    },
});

// Query keys factory for consistent key management
export const queryKeys = {
    all: ['app'] as const,

    // Integration keys
    integrations: () => [...queryKeys.all, 'integrations'] as const,
    integration: (id: string) => [...queryKeys.integrations(), id] as const,

    // Notion keys
    notion: () => [...queryKeys.all, 'notion'] as const,
    notionPages: (filters?: Record<string, unknown>) => [...queryKeys.notion(), 'pages', filters] as const,
    notionPage: (id: string) => [...queryKeys.notion(), 'page', id] as const,
    notionBlocks: (pageId: string) => [...queryKeys.notion(), 'blocks', pageId] as const,
    notionDatabases: () => [...queryKeys.notion(), 'databases'] as const,

    // JIRA keys
    jira: () => [...queryKeys.all, 'jira'] as const,
    jiraProjects: () => [...queryKeys.jira(), 'projects'] as const,
    jiraProject: (key: string) => [...queryKeys.jira(), 'project', key] as const,
    jiraBoards: (filters?: Record<string, unknown>) => [...queryKeys.jira(), 'boards', filters] as const,
    jiraBoard: (id: string) => [...queryKeys.jira(), 'board', id] as const,
    jiraIssues: (filters?: Record<string, unknown>) => [...queryKeys.jira(), 'issues', filters] as const,
    jiraIssue: (key: string) => [...queryKeys.jira(), 'issue', key] as const,
    jiraSprints: (boardId: string) => [...queryKeys.jira(), 'sprints', boardId] as const,

    // Todoist keys
    todoist: () => [...queryKeys.all, 'todoist'] as const,
    todoistTasks: (filters?: Record<string, unknown>) => [...queryKeys.todoist(), 'tasks', filters] as const,
    todoistProjects: () => [...queryKeys.todoist(), 'projects'] as const,

    // Daily Planning keys
    dailyPlanning: () => [...queryKeys.all, 'daily-planning'] as const,
    dailyPlan: (date?: string) => [...queryKeys.dailyPlanning(), 'plan', date] as const,

    // Chat keys
    chat: () => [...queryKeys.all, 'chat'] as const,
    chatHistory: (sessionId?: string) => [...queryKeys.chat(), 'history', sessionId] as const,

    // Natural Language keys
    nlp: () => [...queryKeys.all, 'nlp'] as const,
    nlpSuggestions: (query: string) => [...queryKeys.nlp(), 'suggestions', query] as const,
    nlpHistory: () => [...queryKeys.nlp(), 'history'] as const,
};
