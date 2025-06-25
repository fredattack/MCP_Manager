export interface TodoistTask {
    id: string;
    content: string;
    description?: string;
    completed: boolean;
    priority: 1 | 2 | 3 | 4;
    project_id: string;
    section_id?: string;
    parent_id?: string;
    order: number;
    labels: string[];
    due?: {
        date: string;
        datetime?: string;
        timezone?: string;
        string: string;
        lang: string;
        is_recurring: boolean;
    };
    created_at: string;
    updated_at: string;
}

export interface TodoistProject {
    id: string;
    name: string;
    color: string;
    comment_count: number;
    order: number;
    shared: boolean;
    favorite: boolean;
    inbox_project: boolean;
    team_inbox: boolean;
    sync_id?: number;
    parent_id?: string;
    child_order: number;
    collapsed: boolean;
    view_style: 'list' | 'board';
    url: string;
    created_at: string;
    updated_at: string;
}

export interface CreateTaskData {
    content: string;
    description?: string;
    project_id?: string;
    section_id?: string;
    parent_id?: string;
    order?: number;
    labels?: string[];
    priority?: 1 | 2 | 3 | 4;
    due_string?: string;
    due_date?: string;
    due_datetime?: string;
    due_lang?: string;
}

export interface UpdateTaskData {
    content?: string;
    description?: string;
    labels?: string[];
    priority?: 1 | 2 | 3 | 4;
    due_string?: string;
    due_date?: string;
    due_datetime?: string;
    due_lang?: string;
}
