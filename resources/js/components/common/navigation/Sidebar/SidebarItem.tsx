import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

interface SidebarItemProps {
    href: string;
    icon?: LucideIcon;
    label: string;
    collapsed?: boolean;
    status?: 'connected' | 'disconnected' | 'error';
    active?: boolean;
}

export function SidebarItem({ href, icon: Icon, label, collapsed = false, status, active = false }: SidebarItemProps) {
    const statusColors = {
        connected: 'bg-success text-success-foreground',
        disconnected: 'bg-muted text-muted-foreground',
        error: 'bg-danger text-danger-foreground',
    };

    return (
        <Link
            href={href}
            className={cn(
                'mx-2 flex items-center gap-3 rounded-sm px-3 py-2 transition-colors',
                'hover:bg-gray-100 dark:hover:bg-gray-800',
                'focus:ring-primary/20 focus:ring-2 focus:outline-none',
                active && 'bg-primary/10 text-primary border-primary border-r-2',
                collapsed && 'justify-center px-2',
            )}
        >
            {Icon && <Icon className={cn('h-4 w-4 flex-shrink-0', collapsed && 'h-5 w-5')} />}

            {!collapsed && (
                <>
                    <span className="flex-1 truncate text-sm font-medium">{label}</span>
                    {status && <Badge variant="secondary" className={cn('h-2 w-2 rounded-full p-0', statusColors[status])} />}
                </>
            )}
        </Link>
    );
}
