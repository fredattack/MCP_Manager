#### Table Component (JIRA-style)
```typescript
// components/common/data-display/Table/Table.tsx
interface TableProps<T> {
  data: T[];
  columns: ColumnDef<T>[];
  onRowClick?: (row: T) => void;
  selectable?: boolean;
  sortable?: boolean;
  filterable?: boolean;
  pagination?: PaginationConfig;
  em# MCP Manager Frontend Implementation Guide

## Project Context

You are implementing the frontend for MCP Manager, a comprehensive integration platform that consumes the MCP Server API. The frontend must follow Atlassian's design system (particularly JIRA's UI patterns) and modern React best practices with maximum component decomposition.

## Design System Requirements

### Technology Stack Requirements
- **CSS Framework**: Tailwind CSS 4 (MANDATORY)
  - Use Tailwind 4's new features: built-in dark mode, container queries, 3D transforms
  - Leverage the new color system with automatic shades
  - Utilize the improved spacing scale and fluid typography
  - Take advantage of the new variant system

### Atlassian Design Patterns to Implement

1. **Navigation Structure**
   - Left sidebar navigation (collapsible) with service icons
   - Top navigation bar with search, notifications, and user profile
   - Breadcrumb navigation for deep hierarchies
   - Context-sensitive action bars

2. **Visual Design with Tailwind 4**
   - Color palette: Use Tailwind 4's color system with custom Atlassian colors
     ```css
     /* tailwind.config.js - extend with Atlassian colors */
     primary: { 
       DEFAULT: '#0052CC', // Atlassian blue
       50: '#E6F0FF',
       // ... other shades
     }
     ```
   - Typography: Tailwind 4's font system with `-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto`
   - Spacing: Utilize Tailwind's spacing scale (space-1 through space-96)
   - Border radius: `rounded-sm` (3px) for cards, `rounded-[2px]` for buttons
   - Shadows: Tailwind 4's shadow system with custom Atlassian shadows
     ```css
     'shadow-atlassian': '0 1px 1px rgba(9,30,66,0.25)'
     ```

3. **Component Patterns**
   - Inline editable fields
   - Drag-and-drop interfaces
   - Multi-select with tags
   - Split button dropdowns
   - Contextual drawers/modals
   - Toast notifications
   - Empty states with illustrations

## Architecture Guidelines

### Folder Structure
```
resources/js/
├── components/
│   ├── common/
│   │   ├── navigation/
│   │   │   ├── Sidebar/
│   │   │   │   ├── Sidebar.tsx
│   │   │   │   ├── SidebarItem.tsx
│   │   │   │   ├── SidebarSection.tsx
│   │   │   │   └── index.ts
│   │   │   ├── TopBar/
│   │   │   ├── Breadcrumbs/
│   │   │   └── SearchBar/
│   │   ├── data-display/
│   │   │   ├── Table/
│   │   │   ├── Card/
│   │   │   ├── List/
│   │   │   └── EmptyState/
│   │   ├── feedback/
│   │   │   ├── Toast/
│   │   │   ├── Alert/
│   │   │   └── Progress/
│   │   └── forms/
│   │       ├── Input/
│   │       ├── Select/
│   │       ├── DatePicker/
│   │       └── TagInput/
│   ├── integrations/
│   │   ├── todoist/
│   │   │   ├── TodoistDashboard.tsx
│   │   │   ├── components/
│   │   │   │   ├── TaskList/
│   │   │   │   ├── TaskCard/
│   │   │   │   ├── ProjectSelector/
│   │   │   │   └── QuickAdd/
│   │   │   └── hooks/
│   │   ├── jira/
│   │   │   ├── JiraDashboard.tsx
│   │   │   ├── components/
│   │   │   │   ├── IssueBoard/
│   │   │   │   ├── SprintView/
│   │   │   │   ├── BacklogView/
│   │   │   │   └── VelocityChart/
│   │   │   └── hooks/
│   │   ├── sentry/
│   │   ├── notion/
│   │   └── shared/
│   │       ├── IntegrationCard.tsx
│   │       ├── IntegrationStatus.tsx
│   │       └── SyncIndicator.tsx
│   ├── layouts/
│   │   ├── IntegrationLayout.tsx
│   │   ├── DashboardLayout.tsx
│   │   └── SettingsLayout.tsx
│   └── ui/ (existing shadcn components)
├── hooks/
│   ├── api/
│   │   ├── use-todoist-api.ts
│   │   ├── use-jira-api.ts
│   │   ├── use-sentry-api.ts
│   │   └── use-notion-api.ts
│   ├── ui/
│   │   ├── use-toast.ts
│   │   ├── use-modal.ts
│   │   └── use-drawer.ts
│   └── data/
│       ├── use-pagination.ts
│       ├── use-infinite-scroll.ts
│       └── use-real-time-sync.ts
├── lib/
│   ├── api/
│   │   ├── client.ts
│   │   ├── interceptors.ts
│   │   └── endpoints/
│   ├── utils/
│   │   ├── date-formatters.ts
│   │   ├── priority-helpers.ts
│   │   └── status-mappers.ts
│   └── constants/
│       ├── integration-configs.ts
│       └── ui-constants.ts
├── pages/
│   ├── dashboard.tsx
│   ├── integrations/
│   │   ├── index.tsx
│   │   ├── todoist.tsx
│   │   ├── jira.tsx
│   │   ├── sentry.tsx
│   │   └── notion.tsx
│   └── settings/
│       ├── security.tsx
│       └── integrations.tsx
├── stores/
│   ├── integration-store.ts
│   ├── notification-store.ts
│   └── user-preferences-store.ts
└── types/
    ├── api/
    │   ├── todoist.types.ts
    │   ├── jira.types.ts
    │   ├── sentry.types.ts
    │   └── notion.types.ts
    └── ui/
        └── component.types.ts
```

## Component Implementation Guidelines

### 1. Common Components

#### Sidebar Component
```typescript
// components/common/navigation/Sidebar/Sidebar.tsx
import { useState } from 'react';
import { cn } from '@/lib/utils';
import { SidebarItem } from './SidebarItem';
import { SidebarSection } from './SidebarSection';

interface SidebarProps {
  className?: string;
  defaultCollapsed?: boolean;
}

export function Sidebar({ className, defaultCollapsed = false }: SidebarProps) {
  const [collapsed, setCollapsed] = useState(defaultCollapsed);
  
  // Implementation with Atlassian-style collapse animation using Tailwind 4
  // Use Tailwind 4's new animation utilities and container queries
  return (
    <aside
      className={cn(
        'bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800',
        'transition-all duration-200 ease-in-out',
        'h-screen sticky top-0',
        collapsed ? 'w-16' : 'w-60',
        '@container', // Tailwind 4 container queries
        className
      )}
    >
      {/* Sidebar content with Tailwind 4 styling */}
    </aside>
  );
}
```

#### Table Component (JIRA-style)
```typescript
// components/common/data-display/Table/Table.tsx
interface TableProps<T> {
  data: T[];
  columns: ColumnDef<T>[];
  onRowClick?: (row: T) => void;
  selectable?: boolean;
  sortable?: boolean;
  filterable?: boolean;
  pagination?: PaginationConfig;
  emptyState?: ReactNode;
  loading?: boolean;
}

// Implementation with Tailwind 4 classes
export function Table<T>({ data, columns, ...props }: TableProps<T>) {
  return (
    <div className="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
      <table className="w-full divide-y divide-gray-200 dark:divide-gray-800">
        <thead className="bg-gray-50 dark:bg-gray-900">
          <tr>
            {columns.map((column) => (
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                {column.header}
              </th>
            ))}
          </tr>
        </thead>
        {/* Table body with Tailwind 4 hover states and transitions */}
      </table>
    </div>
  );
}

// Features to implement:
// - Inline editing with Tailwind 4 focus states
// - Bulk selection with Tailwind 4 checkbox styling
// - Column resizing with Tailwind 4 resize utilities
// - Quick filters with Tailwind 4 form styles
// - Sticky header using Tailwind 4 sticky utilities
// - Virtual scrolling with Tailwind 4 overflow utilities
```

### 2. Integration-Specific Components

#### Todoist Integration

```typescript
// components/integrations/todoist/components/TaskCard/TaskCard.tsx
interface TaskCardProps {
  task: TodoistTask;
  onUpdate: (task: Partial<TodoistTask>) => void;
  onDelete: (taskId: string) => void;
  draggable?: boolean;
  compact?: boolean;
}

export function TaskCard({ task, onUpdate, onDelete, draggable, compact }: TaskCardProps) {
  return (
    <div className={cn(
      'group relative bg-white dark:bg-gray-900 rounded-sm border border-gray-200 dark:border-gray-800',
      'hover:shadow-atlassian transition-shadow duration-200',
      'p-4',
      compact && 'p-2',
      draggable && 'cursor-move'
    )}>
      {/* Priority indicator with Tailwind 4 colors */}
      <div className={cn(
        'absolute left-0 top-0 bottom-0 w-1 rounded-l-sm',
        {
          'bg-red-500': task.priority === 1,
          'bg-orange-500': task.priority === 2,
          'bg-blue-500': task.priority === 3,
          'bg-gray-400': task.priority === 4,
        }
      )} />
      
      {/* Task content with Tailwind 4 utilities */}
      <div className="flex items-start gap-3 pl-2">
        <input
          type="checkbox"
          checked={task.completed}
          onChange={(e) => onUpdate({ completed: e.target.checked })}
          className="mt-1 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
        />
        
        {/* Inline editable task name */}
        <div className="flex-1 min-w-0">
          <h4 className="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
            {task.content}
          </h4>
          
          {/* Labels with Tailwind 4 */}
          <div className="flex flex-wrap gap-1 mt-1">
            {task.labels?.map(label => (
              <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                {label}
              </span>
            ))}
          </div>
        </div>
        
        {/* Hover actions with Tailwind 4 opacity utilities */}
        <div className="opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex gap-1">
          <button className="p-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded">
            {/* Edit icon */}
          </button>
          <button className="p-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded text-red-600">
            {/* Delete icon */}
          </button>
        </div>
      </div>
    </div>
  );
}

// Implement with:
// - Tailwind 4's new color system for priority indicators
// - Container queries for responsive design
// - New animation utilities for smooth interactions
// - Dark mode support with Tailwind 4's improved dark variant
```

#### JIRA Integration

```typescript
// components/integrations/jira/components/IssueBoard/IssueBoard.tsx
interface IssueBoardProps {
  projectKey: string;
  boardId: string;
  sprintId?: string;
  onIssueMove: (issueId: string, fromStatus: string, toStatus: string) => void;
}

export function IssueBoard({ projectKey, boardId, sprintId, onIssueMove }: IssueBoardProps) {
  return (
    <div className="h-full flex gap-4 p-4 overflow-x-auto">
      {/* Kanban columns with Tailwind 4 */}
      {columns.map((column) => (
        <div
          key={column.id}
          className={cn(
            'flex-shrink-0 w-80 bg-gray-50 dark:bg-gray-900 rounded-sm',
            'border border-gray-200 dark:border-gray-800',
            '@container' // Tailwind 4 container queries
          )}
        >
          {/* Column header with WIP limit */}
          <div className="p-3 border-b border-gray-200 dark:border-gray-800">
            <div className="flex items-center justify-between">
              <h3 className="font-medium text-sm text-gray-900 dark:text-gray-100">
                {column.name}
              </h3>
              <span className="text-xs text-gray-500 dark:text-gray-400">
                {column.issues.length} / {column.limit}
              </span>
            </div>
          </div>
          
          {/* Droppable area with Tailwind 4 */}
          <div className="p-2 space-y-2 min-h-[calc(100vh-12rem)]">
            {column.issues.map((issue) => (
              <IssueCard
                key={issue.id}
                issue={issue}
                className="bg-white dark:bg-gray-800 shadow-sm hover:shadow-atlassian"
              />
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}

// Implement Kanban board with:
// - Drag-and-drop using Tailwind 4 cursor utilities
// - Swimlanes with Tailwind 4 grid system
// - Quick filters with Tailwind 4 form components
// - Issue avatars with Tailwind 4 avatar utilities
// - Responsive design with Tailwind 4 container queries
```

### 3. Shared Integration Components

```typescript
// components/integrations/shared/IntegrationCard.tsx
interface IntegrationCardProps {
  integration: IntegrationType;
  status: IntegrationStatus;
  lastSync?: Date;
  metrics?: IntegrationMetrics;
  onConfigure: () => void;
  onSync: () => void;
  onDisconnect: () => void;
}

// Card should display:
// - Service logo and name
// - Connection status indicator
// - Last sync time
// - Key metrics (tasks, issues, errors)
// - Quick actions dropdown
// - Sync progress when active
```

## API Integration Patterns

### 1. API Client Setup

```typescript
// lib/api/client.ts
import axios from 'axios';
import { setupInterceptors } from './interceptors';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:9978',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
  },
});

setupInterceptors(apiClient);

export default apiClient;
```

### 2. Custom Hooks Pattern

```typescript
// hooks/api/use-todoist-api.ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { todoistApi } from '@/lib/api/endpoints/todoist';

export function useTodoistProjects() {
  return useQuery({
    queryKey: ['todoist', 'projects'],
    queryFn: todoistApi.getProjects,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

export function useCreateTodoistTask() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: todoistApi.createTask,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['todoist', 'tasks'] });
    },
  });
}

// Implement optimistic updates for better UX
export function useUpdateTodoistTask() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: todoistApi.updateTask,
    onMutate: async (updatedTask) => {
      // Cancel outgoing refetches
      await queryClient.cancelQueries({ queryKey: ['todoist', 'tasks'] });
      
      // Snapshot previous value
      const previousTasks = queryClient.getQueryData(['todoist', 'tasks']);
      
      // Optimistically update
      queryClient.setQueryData(['todoist', 'tasks'], (old) => {
        // Update logic
      });
      
      return { previousTasks };
    },
    onError: (err, newTask, context) => {
      // Rollback on error
      queryClient.setQueryData(['todoist', 'tasks'], context.previousTasks);
    },
    onSettled: () => {
      queryClient.invalidateQueries({ queryKey: ['todoist', 'tasks'] });
    },
  });
}
```

### 3. Real-time Synchronization

```typescript
// hooks/data/use-real-time-sync.ts
import { useEffect } from 'react';
import { useQueryClient } from '@tanstack/react-query';

export function useRealTimeSync(integration: IntegrationType, interval = 30000) {
  const queryClient = useQueryClient();
  
  useEffect(() => {
    const syncInterval = setInterval(() => {
      queryClient.invalidateQueries({ 
        queryKey: [integration],
        refetchType: 'active',
      });
    }, interval);
    
    return () => clearInterval(syncInterval);
  }, [integration, interval, queryClient]);
}
```

## UI/UX Requirements

### 1. Loading States
- Skeleton screens matching content structure
- Progressive loading (header → content → sidebar)
- Optimistic UI updates with rollback on error
- Background refresh indicators

### 2. Error Handling
- Toast notifications for transient errors
- Inline error states for form validation
- Error boundaries with recovery actions
- Retry mechanisms with exponential backoff

### 3. Empty States
- Illustrated empty states with clear CTAs
- Different messages for filtered vs. no data
- Quick action buttons to create first item
- Integration setup wizards

### 4. Responsive Design
- Mobile-first approach
- Collapsible sidebar on tablet/mobile
- Touch-friendly tap targets (min 44px)
- Swipe gestures for common actions
- Bottom sheets instead of modals on mobile

## Performance Optimization

### 1. Code Splitting
```typescript
// Lazy load integration pages
const TodoistPage = lazy(() => import('./pages/integrations/todoist'));
const JiraPage = lazy(() => import('./pages/integrations/jira'));
```

### 2. Data Optimization
- Implement virtual scrolling for large lists
- Use React Query for caching and background refetching
- Paginate or use infinite scroll for data sets
- Debounce search and filter inputs
- Memoize expensive computations

### 3. Bundle Optimization
- Tree-shake unused icons and components
- Use dynamic imports for heavy libraries
- Optimize images with lazy loading
- Implement service worker for offline support

## Testing Strategy

### 1. Component Testing
```typescript
// components/integrations/todoist/components/TaskCard/TaskCard.test.tsx
import { render, screen, userEvent } from '@testing-library/react';
import { TaskCard } from './TaskCard';

describe('TaskCard', () => {
  it('should toggle completion status', async () => {
    const onUpdate = vi.fn();
    render(<TaskCard task={mockTask} onUpdate={onUpdate} />);
    
    const checkbox = screen.getByRole('checkbox');
    await userEvent.click(checkbox);
    
    expect(onUpdate).toHaveBeenCalledWith({
      id: mockTask.id,
      completed: true,
    });
  });
});
```

### 2. Integration Testing
- Test API error scenarios
- Test optimistic updates and rollbacks
- Test real-time sync behavior
- Test cross-integration workflows

## Accessibility Requirements

1. **WCAG 2.1 AA Compliance**
   - Proper ARIA labels and roles
   - Keyboard navigation support
   - Focus management in modals/drawers
   - Screen reader announcements

2. **Keyboard Shortcuts**
   - `/` for search focus
   - `c` for create new item
   - `g` then `i` for go to integrations
   - `?` for keyboard shortcuts help

3. **Color Contrast**
   - Minimum 4.5:1 for normal text
   - 3:1 for large text and UI components
   - Don't rely solely on color for information

## State Management

### 1. Integration Store (Zustand)
```typescript
// stores/integration-store.ts
interface IntegrationStore {
  integrations: Map<IntegrationType, IntegrationConfig>;
  activeIntegration: IntegrationType | null;
  syncStatus: Map<IntegrationType, SyncStatus>;
  
  setActiveIntegration: (type: IntegrationType) => void;
  updateSyncStatus: (type: IntegrationType, status: SyncStatus) => void;
  refreshIntegration: (type: IntegrationType) => Promise<void>;
}
```

### 2. Notification System
```typescript
// stores/notification-store.ts
interface NotificationStore {
  notifications: Notification[];
  unreadCount: number;
  
  addNotification: (notification: Omit<Notification, 'id' | 'timestamp'>) => void;
  markAsRead: (id: string) => void;
  clearAll: () => void;
}
```

## Security Considerations

1. **API Token Management**
   - Never store tokens in localStorage
   - Use httpOnly cookies for auth tokens
   - Implement token refresh logic
   - Clear tokens on logout

2. **Content Security**
   - Sanitize user-generated content
   - Implement CSP headers
   - Validate all inputs client-side
   - Use HTTPS for all API calls

3. **Data Privacy**
   - Implement data minimization
   - Clear sensitive data on unmount
   - Encrypt stored preferences
   - Implement audit logging

## Implementation Priority

### Phase 1: Core Infrastructure
1. Setup routing and layouts
2. Implement authentication flow
3. Create common components (Sidebar, Table, Card)
4. Setup API client and interceptors
5. Implement notification system

### Phase 2: Todoist Integration
1. Project and task management
2. Quick add functionality
3. Bulk operations
4. Label and filter system
5. Cross-service task creation

### Phase 3: JIRA Integration
1. Issue board (Kanban view)
2. Sprint management
3. JQL search interface
4. Epic progress tracking
5. Velocity charts

### Phase 4: Sentry Integration
1. Error dashboard
2. Issue management
3. Performance monitoring
4. Alert configuration
5. Cross-service issue creation

### Phase 5: Advanced Features
1. Real-time synchronization
2. Offline support
3. Advanced analytics
4. Workflow automation
5. Mobile optimization

## Quality Checklist

Before considering any component complete, ensure:

- [ ] TypeScript types are properly defined
- [ ] Component is fully accessible
- [ ] Loading and error states are handled
- [ ] Component is responsive
- [ ] Animations are smooth (60fps)
- [ ] Component is tested
- [ ] Props are documented
- [ ] Performance is optimized
- [ ] Follows Atlassian design patterns
- [ ] Integrates with existing architecture

## Tailwind 4 Configuration

### Required Setup

```javascript
// tailwind.config.js
export default {
  content: [
    "./resources/**/*.{js,jsx,ts,tsx}",
  ],
  darkMode: 'class', // Tailwind 4's improved dark mode
  theme: {
    extend: {
      colors: {
        // Atlassian color palette
        primary: {
          DEFAULT: '#0052CC',
          50: '#E6F0FF',
          100: '#B3D4FF',
          200: '#80B8FF',
          300: '#4D9CFF',
          400: '#1A80FF',
          500: '#0052CC',
          600: '#0041A3',
          700: '#00317A',
          800: '#002152',
          900: '#001029',
        },
        success: {
          DEFAULT: '#00875A',
          light: '#57D9A3',
          dark: '#00633E',
        },
        warning: {
          DEFAULT: '#FF991F',
          light: '#FFE380',
          dark: '#CC7A00',
        },
        danger: {
          DEFAULT: '#DE350B',
          light: '#FF8F73',
          dark: '#BF2600',
        },
      },
      fontFamily: {
        sans: ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', 'sans-serif'],
      },
      boxShadow: {
        'atlassian': '0 1px 1px rgba(9,30,66,0.25), 0 0 0 1px rgba(9,30,66,0.08)',
        'atlassian-lg': '0 8px 16px -4px rgba(9,30,66,0.25), 0 0 0 1px rgba(9,30,66,0.08)',
      },
      animation: {
        'slide-in': 'slideIn 0.2s ease-out',
        'fade-in': 'fadeIn 0.2s ease-out',
      },
    },
  },
  plugins: [
    // Tailwind 4 plugins
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/container-queries'),
  ],
}
```

### CSS Variables for Dynamic Theming

```css
/* resources/css/app.css */
@import 'tailwindcss';

@layer base {
  :root {
    /* Tailwind 4 CSS variables for dynamic theming */
    --color-primary: theme('colors.primary.DEFAULT');
    --color-background: theme('colors.white');
    --color-surface: theme('colors.gray.50');
    --color-border: theme('colors.gray.200');
    --color-text: theme('colors.gray.900');
    --color-text-secondary: theme('colors.gray.600');
  }
  
  .dark {
    --color-background: theme('colors.gray.950');
    --color-surface: theme('colors.gray.900');
    --color-border: theme('colors.gray.800');
    --color-text: theme('colors.gray.100');
    --color-text-secondary: theme('colors.gray.400');
  }
}

/* Custom utilities using Tailwind 4 features */
@layer utilities {
  .scrollbar-thin {
    scrollbar-width: thin;
    scrollbar-color: theme('colors.gray.400') transparent;
  }
  
  .animate-in {
    animation: var(--animation-in) var(--animation-duration, 0.2s) var(--animation-timing, ease-out);
  }
}
```

## Additional Notes

1. **Design Tokens**: Create a comprehensive token system for colors, spacing, typography matching Atlassian's design system

2. **Component Documentation**: Use Storybook to document all components with interactive examples

3. **Performance Monitoring**: Implement Web Vitals tracking and performance budgets

4. **Feature Flags**: Implement feature flag system for gradual rollouts

5. **A/B Testing**: Setup framework for testing UI variations

Remember: The goal is to create a seamless, performant, and intuitive interface that rivals Atlassian's products while leveraging the power of the MCP Server API.