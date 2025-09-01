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
import { AlertCircle, BookOpen, CheckCircle2, FileText, Folder, LayoutGrid, Plug, XCircle, MessageSquare, Brain, Mail, Calendar, CalendarDays, Kanban, Server, Shield } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
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
        title: 'Commandes Naturelles',
        href: '/ai/natural-language',
        icon: Brain,
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
        badge: 'New',
    },
    {
        title: 'Integrations (Old)',
        href: '/integrations',
        icon: Plug,
    },
    {
        title: 'Notion Pages',
        href: '/notion',
        icon: FileText,
    },
    {
        title: 'JIRA',
        href: '/jira',
        icon: Kanban,
    },
];

const integrationItems: NavItem[] = [
    {
        title: 'Todoist',
        href: '/integrations/todoist',
        icon: CheckCircle2,
        status: 'connected',
    },
    {
        title: 'Google',
        href: '/integrations/google',
        icon: Plug,
        status: 'disconnected',
    },
    {
        title: 'Gmail',
        href: '/gmail',
        icon: Mail,
        status: 'disconnected',
    },
    {
        title: 'Calendar',
        href: '/calendar',
        icon: Calendar,
        status: 'disconnected',
    },
    {
        title: 'JIRA',
        href: '/integrations/jira',
        icon: XCircle,
        status: 'disconnected',
    },
    {
        title: 'Sentry',
        href: '/integrations/sentry',
        icon: AlertCircle,
        status: 'error',
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

function IntegrationsNav({ items }: { items: NavItem[] }) {
    const page = usePage();

    const getStatusBadge = (status?: string) => {
        switch (status) {
            case 'connected':
                return <Badge className="bg-success ml-auto h-2 w-2 p-0" />;
            case 'error':
                return <Badge className="bg-danger ml-auto h-2 w-2 p-0" />;
            case 'disconnected':
            default:
                return <Badge className="ml-auto h-2 w-2 bg-gray-400 p-0" />;
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
                                {item.icon && <item.icon className="h-4 w-4" />}
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
    
    // Update integration items with dynamic statuses
    const dynamicIntegrationItems = integrationItems.map(item => {
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
                <NavMain items={mainNavItems} />
                <IntegrationsNav items={dynamicIntegrationItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
