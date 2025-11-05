import { IntegrationIcon } from '@/components/integrations/integration-icon';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Badge } from '@/components/ui/badge';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    AlertCircle,
    BookOpen,
    Brain,
    Building2,
    Calendar,
    CalendarDays,
    CheckCircle2,
    FileText,
    Folder,
    GitBranch,
    Key,
    LayoutGrid,
    Mail,
    MessageSquare,
    Plug,
    Server,
    Shield,
    Users,
    Workflow,
    XCircle,
} from 'lucide-react';
import AppLogo from './app-logo';

/**
 * MAIN NAVIGATION CONFIGURATION
 *
 * This file is the SINGLE SOURCE OF TRUTH for all application navigation.
 * To add, remove, or modify navigation items, edit the arrays below.
 *
 * Navigation Structure:
 * - mainNavItems: Primary navigation (Dashboard, Workflows, Git, etc.)
 * - integrationItems: Integration status items (Todoist, Gmail, etc.)
 * - footerNavItems: Footer links (Repository, Documentation)
 */

/**
 * Main Navigation Items
 * These appear in the primary sidebar navigation section
 */
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Workflows',
        href: '/workflows',
        icon: Workflow,
        badge: 'New',
    },
    {
        title: 'MCP Dashboard',
        href: '/mcp/dashboard',
        icon: Server,
    },
    {
        title: 'MCP Server Config',
        href: '/mcp/server/config',
        icon: Shield,
    },
    {
        title: 'Claude Chat',
        href: '/ai/claude-chat',
        icon: MessageSquare,
    },
    {
        title: 'Natural Language',
        href: '/ai/natural-language',
        icon: Brain,
    },
    {
        title: 'Git Connections',
        href: '/git/connections',
        icon: GitBranch,
    },
    {
        title: 'User Management',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Organizations',
        href: '/settings/organizations',
        icon: Building2,
    },
    {
        title: 'Active Leases',
        href: '/settings/security/active-leases',
        icon: Key,
    },
    {
        title: 'Daily Planning',
        href: '/daily-planning',
        icon: CalendarDays,
    },
    {
        title: 'Integration Manager',
        href: '/integrations/manager',
        icon: Plug,
    },
    {
        title: 'Integrations',
        href: '/integrations',
        icon: Plug,
    },
    {
        title: 'Notion Pages',
        href: '/notion',
        icon: FileText,
    },
];

/**
 * Integration Navigation Items
 * These show the status of connected integrations (connected/disconnected/error)
 * Status badges are dynamically updated based on actual connection state
 */
interface IntegrationNavItem extends NavItem {
    service: string; // Service identifier for logo
}

const integrationItems: IntegrationNavItem[] = [
    {
        title: 'Todoist',
        href: '/integrations/todoist',
        icon: CheckCircle2,
        service: 'todoist',
        status: 'connected',
    },
    {
        title: 'Google',
        href: '/integrations/google',
        icon: Plug,
        service: 'gmail', // Use Gmail as the google service logo
        status: 'disconnected',
    },
    {
        title: 'Gmail',
        href: '/gmail',
        icon: Mail,
        service: 'gmail',
        status: 'disconnected',
    },
    {
        title: 'Calendar',
        href: '/calendar',
        icon: Calendar,
        service: 'calendar',
        status: 'disconnected',
    },
    {
        title: 'JIRA',
        href: '/integrations/jira',
        icon: XCircle,
        service: 'jira',
        status: 'disconnected',
    },
    {
        title: 'Sentry',
        href: '/integrations/sentry',
        icon: AlertCircle,
        service: 'sentry',
        status: 'error',
    },
];

/**
 * Footer Navigation Items
 * External links and documentation shown at the bottom of the sidebar
 */
const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/your-org/mcp-manager',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: '/docs',
        icon: BookOpen,
    },
];

function IntegrationsNav({ items }: { items: IntegrationNavItem[] }) {
    const page = usePage();

    const getStatusBadge = (status?: string) => {
        switch (status) {
            case 'connected':
                return <Badge className="bg-success ml-auto h-2 w-2 p-0" aria-label="Status: Connected" role="status" />;
            case 'error':
                return <Badge className="bg-danger ml-auto h-2 w-2 p-0" aria-label="Status: Error" role="status" />;
            case 'disconnected':
            default:
                return <Badge className="ml-auto h-2 w-2 bg-gray-400 p-0" aria-label="Status: Disconnected" role="status" />;
        }
    };

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>Integrations</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton asChild isActive={item.href === page.url} tooltip={{ children: item.title }}>
                            <Link href={item.href} prefetch>
                                <IntegrationIcon service={item.service} size={16} className="flex-shrink-0" />
                                <span>{item.title}</span>
                                {getStatusBadge(item.status)}
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}

export function AppSidebar() {
    const page = usePage();
    const integrationStatuses = (page.props as SharedData).integrationStatuses || {};
    const user = (page.props as SharedData).auth?.user;
    const userRole = user?.role as string | undefined;

    // Filter main nav items based on user role
    const filteredMainNavItems = mainNavItems.filter((item) => {
        // User Management is only visible for admin and manager roles
        if (item.href === '/admin/users') {
            return userRole === 'admin' || userRole === 'manager';
        }
        return true;
    });

    // Update integration items with dynamic statuses
    const dynamicIntegrationItems = integrationItems.map((item) => {
        let statusKey = '';
        if (item.title === 'Todoist') statusKey = 'todoist';
        else if (item.title === 'Gmail') statusKey = 'gmail';
        else if (item.title === 'Calendar') statusKey = 'calendar';

        return {
            ...item,
            status: integrationStatuses[statusKey] || 'disconnected',
        };
    });

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={filteredMainNavItems} />
                <IntegrationsNav items={dynamicIntegrationItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
