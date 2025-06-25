import { cn } from '@/lib/utils';
import { ReactNode } from 'react';

interface SidebarSectionProps {
    title: string;
    children: ReactNode;
    collapsed?: boolean;
    className?: string;
}

export function SidebarSection({ title, children, collapsed = false, className }: SidebarSectionProps) {
    return (
        <div className={cn('mb-6', className)}>
            {!collapsed && title && (
                <h3 className="mx-2 mb-2 px-3 text-xs font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">{title}</h3>
            )}
            <nav className="space-y-1">{children}</nav>
        </div>
    );
}
