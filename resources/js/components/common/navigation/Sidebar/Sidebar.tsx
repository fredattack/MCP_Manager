import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { BarChart3, ChevronLeft, ChevronRight, Home, Plug, Settings } from 'lucide-react';
import { useState } from 'react';
import { SidebarItem } from './SidebarItem';
import { SidebarSection } from './SidebarSection';

interface SidebarProps {
    className?: string;
    defaultCollapsed?: boolean;
}

export function Sidebar({ className, defaultCollapsed = false }: SidebarProps) {
    const [collapsed, setCollapsed] = useState(defaultCollapsed);

    const toggleCollapsed = () => setCollapsed(!collapsed);

    const navigationItems = [
        { id: 'dashboard', label: 'Dashboard', icon: Home, href: '/dashboard' },
        { id: 'integrations', label: 'Integrations', icon: Plug, href: '/integrations' },
        { id: 'analytics', label: 'Analytics', icon: BarChart3, href: '/analytics' },
        { id: 'settings', label: 'Settings', icon: Settings, href: '/settings' },
    ];

    const integrationItems = [
        { id: 'todoist', label: 'Todoist', href: '/integrations/todoist', status: 'connected' },
        { id: 'jira', label: 'JIRA', href: '/integrations/jira', status: 'disconnected' },
        { id: 'sentry', label: 'Sentry', href: '/integrations/sentry', status: 'error' },
        { id: 'notion', label: 'Notion', href: '/integrations/notion', status: 'connected' },
    ];

    return (
        <aside
            className={cn(
                'border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900',
                'transition-all duration-200 ease-in-out',
                'sticky top-0 flex h-screen flex-col',
                collapsed ? 'w-16' : 'w-60',
                className,
            )}
        >
            {/* Header */}
            <div className="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-800">
                {!collapsed && <h1 className="text-lg font-semibold text-gray-900 dark:text-gray-100">MCP Manager</h1>}
                <Button variant="ghost" size="sm" onClick={toggleCollapsed} className="h-8 w-8 p-0">
                    {collapsed ? <ChevronRight className="h-4 w-4" /> : <ChevronLeft className="h-4 w-4" />}
                </Button>
            </div>

            {/* Navigation */}
            <div className="flex-1 overflow-y-auto py-4">
                <SidebarSection title={collapsed ? '' : 'Navigation'} collapsed={collapsed}>
                    {navigationItems.map((item) => (
                        <SidebarItem key={item.id} href={item.href} icon={item.icon} label={item.label} collapsed={collapsed} />
                    ))}
                </SidebarSection>

                <SidebarSection title={collapsed ? '' : 'Integrations'} collapsed={collapsed}>
                    {integrationItems.map((item) => (
                        <SidebarItem
                            key={item.id}
                            href={item.href}
                            label={item.label}
                            collapsed={collapsed}
                            status={item.status as 'connected' | 'disconnected' | 'error'}
                        />
                    ))}
                </SidebarSection>
            </div>
        </aside>
    );
}
