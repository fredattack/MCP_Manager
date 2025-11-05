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
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    Brain,
    Building2,
    Calendar,
    CalendarDays,
    FileText,
    Folder,
    GitBranch,
    LayoutGrid,
    Mail,
    MessageSquare,
    Plug,
    Server,
    Shield,
    User,
    Users,
    Workflow,
} from 'lucide-react';
import AppLogo from './app-logo';

/**
 * MAIN NAVIGATION CONFIGURATION - REORGANIZED
 *
 * This file is the SINGLE SOURCE OF TRUTH for all application navigation.
 *
 * Navigation Structure:
 * - toolsNavItems: Daily-use tools (Dashboard, Workflows, Chat, etc.)
 * - integrationsNavItems: Active integrations with status badges
 * - settingsNavItems: Settings and admin pages
 * - footerNavItems: External links
 */

/**
 * TOOLS - Daily-use items
 * These are the primary actions users perform regularly
 */
const toolsNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Daily Planning',
        href: '/daily-planning',
        icon: CalendarDays,
    },
    {
        title: 'Workflows',
        href: '/workflows',
        icon: Workflow,
        badge: 'New',
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
        title: 'Notion Pages',
        href: '/notion',
        icon: FileText,
    },
];

/**
 * INTEGRATIONS - Active integrations with dynamic status
 * Integration Manager is the entry point, followed by individual integrations
 */
interface IntegrationNavItem extends NavItem {
    service: string;
}

const integrationsNavItems: IntegrationNavItem[] = [
    {
        title: 'Todoist',
        href: '/integrations/todoist',
        service: 'todoist',
        status: 'disconnected',
    },
    {
        title: 'Gmail',
        href: '/gmail',
        service: 'gmail',
        status: 'disconnected',
    },
    {
        title: 'Calendar',
        href: '/calendar',
        service: 'calendar',
        status: 'disconnected',
    },
    {
        title: 'Google',
        href: '/integrations/google',
        service: 'gmail',
        status: 'disconnected',
    },
];

/**
 * SETTINGS - Configuration and admin pages
 * Less frequently accessed, grouped logically
 */
const settingsNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: '/settings/profile',
        icon: User,
    },
    {
        title: 'Organizations',
        href: '/settings/organizations',
        icon: Building2,
    },
    {
        title: 'Admin Users',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'MCP Server Config',
        href: '/mcp/server/config',
        icon: Server,
    },
    {
        title: 'Git Connections',
        href: '/git/connections',
        icon: GitBranch,
    },
    {
        title: 'Security',
        href: '/settings/security/active-leases',
        icon: Shield,
    },
];

/**
 * FOOTER - External links
 */
const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/your-org/mcp-manager',
        icon: Folder,
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
        <SidebarGroup collapsible className="px-2 py-0">
            <SidebarGroupLabel>Integrations</SidebarGroupLabel>
            <SidebarMenu>
                {/* Integration Manager - Entry point */}
                <SidebarMenuItem>
                    <SidebarMenuButton asChild isActive={page.url === '/integrations/manager'} tooltip={{ children: 'Integration Manager' }}>
                        <Link href="/integrations/manager" prefetch>
                            <Plug className="h-4 w-4 flex-shrink-0" />
                            <span>Integration Manager</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                {/* Separator */}
                <SidebarSeparator className="my-2" />

                {/* Individual integrations */}
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

    // Check if user has admin access using Spatie roles
    const hasAdminAccess = user?.roles?.some((role) => ['GOD', 'PLATFORM_ADMIN', 'admin', 'manager'].includes(role)) || user?.permissions?.some((permission) => permission === 'platform.users.*' || permission === '*');

    // Filter settings nav items based on user role
    const filteredSettingsNavItems = settingsNavItems.filter((item) => {
        // Admin Users is only visible for users with admin access
        if (item.href === '/admin/users') {
            return hasAdminAccess;
        }
        return true;
    });

    // Update integration items with dynamic statuses
    const dynamicIntegrationItems = integrationsNavItems.map((item) => {
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
                {/* Tools Section - Default open */}
                <SidebarGroup className="px-2 py-0">
                    <SidebarGroupLabel>Tools</SidebarGroupLabel>
                    <NavMain items={toolsNavItems} />
                </SidebarGroup>

                {/* Integrations Section - Collapsible */}
                <IntegrationsNav items={dynamicIntegrationItems} />

                {/* Settings Section - Collapsible */}
                <SidebarGroup collapsible className="px-2 py-0">
                    <SidebarGroupLabel>Settings</SidebarGroupLabel>
                    <NavMain items={filteredSettingsNavItems} />
                </SidebarGroup>
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
