import { CommandPattern } from './types';

export const todoistPatterns: CommandPattern[] = [
    {
        id: 'todoist.task.create',
        pattern: /\b(create|add|new)\s+(task|todo|tâche)\b/i,
        intent: 'create_task',
        service: 'todoist',
        examples: ['Create task "Review PR"', 'Add new todo for tomorrow', 'New task: Call client at 2pm'],
    },
    {
        id: 'todoist.task.create.simple',
        pattern: /^(task|todo|tâche):\s*(.+)$/i,
        intent: 'create_task',
        service: 'todoist',
        examples: ['Task: Review documentation', 'Todo: Buy groceries'],
    },
    {
        id: 'todoist.task.list',
        pattern: /\b(list|show|display|afficher)\s+(my\s+)?(tasks?|todos?|tâches?)\b/i,
        intent: 'list_tasks',
        service: 'todoist',
        examples: ['List my tasks', 'Show today tasks', 'Display todos for tomorrow'],
    },
    {
        id: 'todoist.task.complete',
        pattern: /\b(complete|finish|done|terminer)\s+(task|todo|tâche)\b/i,
        intent: 'complete_task',
        service: 'todoist',
        examples: ['Complete task "Review PR"', 'Mark done the first task'],
    },
    {
        id: 'todoist.planning',
        pattern: /\b(generate|create|make)\s+(daily\s+)?(planning|plan)\b/i,
        intent: 'generate_planning',
        service: 'todoist',
        examples: ['Generate daily planning', 'Create my plan for today'],
    },
];

export const notionPatterns: CommandPattern[] = [
    {
        id: 'notion.page.create',
        pattern: /\b(create|add|new)\s+(notion\s+)?(page|document)\b/i,
        intent: 'create_page',
        service: 'notion',
        examples: ['Create Notion page "Meeting Notes"', 'New page for project documentation'],
    },
    {
        id: 'notion.page.search',
        pattern: /\b(search|find|look for|chercher)\s+(in\s+)?notion\b/i,
        intent: 'search_notion',
        service: 'notion',
        examples: ['Search in Notion for "API docs"', 'Find Notion pages about React'],
    },
    {
        id: 'notion.database.query',
        pattern: /\b(query|filter|search)\s+(notion\s+)?database\b/i,
        intent: 'query_database',
        service: 'notion',
        examples: ['Query tasks database', 'Filter database by status'],
    },
];

export const jiraPatterns: CommandPattern[] = [
    {
        id: 'jira.issue.create',
        pattern: /\b(create|add|new)\s+(jira\s+)?(issue|ticket|bug|story)\b/i,
        intent: 'create_issue',
        service: 'jira',
        examples: ['Create JIRA issue "Fix login bug"', 'New bug ticket for payment module', 'Add story to sprint'],
    },
    {
        id: 'jira.issue.update',
        pattern: /\b(update|edit|modify)\s+(jira\s+)?(issue|ticket)\b/i,
        intent: 'update_issue',
        service: 'jira',
        examples: ['Update issue PROJ-123', 'Edit ticket status to in progress'],
    },
    {
        id: 'jira.issue.transition',
        pattern: /\b(move|transition|change)\s+(issue|ticket)\s+(to|status)\b/i,
        intent: 'transition_issue',
        service: 'jira',
        examples: ['Move issue to done', 'Change ticket status to review'],
    },
    {
        id: 'jira.sprint.current',
        pattern: /\b(show|list|display)\s+(current\s+)?sprint\b/i,
        intent: 'show_sprint',
        service: 'jira',
        examples: ['Show current sprint', 'List sprint tasks'],
    },
];

export const gmailPatterns: CommandPattern[] = [
    {
        id: 'gmail.email.compose',
        pattern: /\b(send|write|compose|email)\s+(email|mail|message)\b/i,
        intent: 'compose_email',
        service: 'gmail',
        examples: ['Send email to john@example.com', 'Write email about meeting', 'Compose message to team'],
    },
    {
        id: 'gmail.email.search',
        pattern: /\b(search|find|look for)\s+(email|mail|message)s?\b/i,
        intent: 'search_emails',
        service: 'gmail',
        examples: ['Search emails from john', 'Find messages about project X'],
    },
];

export const calendarPatterns: CommandPattern[] = [
    {
        id: 'calendar.event.create',
        pattern: /\b(schedule|create|add)\s+(meeting|event|appointment)\b/i,
        intent: 'create_event',
        service: 'calendar',
        examples: ['Schedule meeting tomorrow at 2pm', 'Create event "Team standup" at 10am', 'Add appointment with client'],
    },
    {
        id: 'calendar.event.list',
        pattern: /\b(show|list|display)\s+(my\s+)?(calendar|schedule|events?)\b/i,
        intent: 'list_events',
        service: 'calendar',
        examples: ['Show my calendar', 'List events for tomorrow', 'Display schedule for next week'],
    },
];

export const crossServicePatterns: CommandPattern[] = [
    {
        id: 'cross.email_to_task',
        pattern: /\b(convert|transform|make)\s+email\s+(to|into)\s+task\b/i,
        intent: 'email_to_task',
        examples: ['Convert this email to task', 'Make email into Todoist task'],
    },
    {
        id: 'cross.sentry_to_jira',
        pattern: /\b(create|make)\s+jira\s+(from|for)\s+sentry\b/i,
        intent: 'sentry_to_jira',
        examples: ['Create JIRA issue from Sentry error', 'Make ticket for this Sentry bug'],
    },
];

export const allPatterns: CommandPattern[] = [
    ...todoistPatterns,
    ...notionPatterns,
    ...jiraPatterns,
    ...gmailPatterns,
    ...calendarPatterns,
    ...crossServicePatterns,
];
