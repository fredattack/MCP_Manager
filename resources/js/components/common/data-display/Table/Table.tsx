import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { cn } from '@/lib/utils';
import { ArrowUpDown, ChevronDown, ChevronUp } from 'lucide-react';
import { ReactNode } from 'react';

export interface ColumnDef<T> {
    id: string;
    header: string;
    accessor: keyof T | ((row: T) => unknown);
    cell?: (value: unknown, row: T) => ReactNode;
    sortable?: boolean;
    width?: string;
    align?: 'left' | 'center' | 'right';
}

export interface PaginationConfig {
    pageSize: number;
    currentPage: number;
    totalItems: number;
    onPageChange: (page: number) => void;
}

interface TableProps<T> {
    data: T[];
    columns: ColumnDef<T>[];
    onRowClick?: (row: T) => void;
    selectable?: boolean;
    selectedRows?: T[];
    onSelectionChange?: (selectedRows: T[]) => void;
    sortable?: boolean;
    sortBy?: string;
    sortDirection?: 'asc' | 'desc';
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
    pagination?: PaginationConfig;
    emptyState?: ReactNode;
    loading?: boolean;
    className?: string;
}

export function Table<T extends { id: string | number }>({
    data,
    columns,
    onRowClick,
    selectable = false,
    selectedRows = [],
    onSelectionChange,
    sortBy,
    sortDirection,
    onSort,
    pagination,
    emptyState,
    loading = false,
    className,
}: TableProps<T>) {
    const handleRowSelection = (row: T, checked: boolean) => {
        if (!onSelectionChange) return;

        if (checked) {
            onSelectionChange([...selectedRows, row]);
        } else {
            onSelectionChange(selectedRows.filter((r) => r.id !== row.id));
        }
    };

    const handleSelectAll = (checked: boolean) => {
        if (!onSelectionChange) return;
        onSelectionChange(checked ? data : []);
    };

    const handleSort = (column: ColumnDef<T>) => {
        if (!column.sortable || !onSort) return;

        const newDirection = sortBy === column.id && sortDirection === 'asc' ? 'desc' : 'asc';
        onSort(column.id, newDirection);
    };

    const getCellValue = (row: T, column: ColumnDef<T>) => {
        const value = typeof column.accessor === 'function' ? column.accessor(row) : row[column.accessor];

        return column.cell ? column.cell(value, row) : value;
    };

    const allSelected = data.length > 0 && selectedRows.length === data.length;

    if (loading) {
        return (
            <div className="w-full">
                <div className="animate-pulse">
                    <div className="mb-2 h-12 rounded-t-lg bg-gray-200 dark:bg-gray-800" />
                    {Array.from({ length: 5 }).map((_, i) => (
                        <div key={i} className="mb-1 h-16 bg-gray-100 dark:bg-gray-900" />
                    ))}
                </div>
            </div>
        );
    }

    if (data.length === 0) {
        return (
            <div className="w-full rounded-lg border border-gray-200 dark:border-gray-800">
                <div className="p-8 text-center">
                    {emptyState || (
                        <div>
                            <p className="text-gray-500 dark:text-gray-400">No data available</p>
                            <p className="mt-1 text-sm text-gray-400 dark:text-gray-500">There are no items to display at this time.</p>
                        </div>
                    )}
                </div>
            </div>
        );
    }

    return (
        <div className={cn('w-full', className)}>
            <div className="shadow-atlassian overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                <div className="overflow-x-auto">
                    <table className="w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead className="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                {selectable && (
                                    <th className="w-12 px-4 py-3">
                                        <Checkbox checked={allSelected} onCheckedChange={handleSelectAll} className="rounded border-gray-300" />
                                    </th>
                                )}
                                {columns.map((column) => (
                                    <th
                                        key={column.id}
                                        className={cn(
                                            'px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400',
                                            column.sortable && 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800',
                                            column.align === 'center' && 'text-center',
                                            column.align === 'right' && 'text-right',
                                        )}
                                        style={{ width: column.width }}
                                        onClick={() => handleSort(column)}
                                    >
                                        <div className="flex items-center gap-1">
                                            <span>{column.header}</span>
                                            {column.sortable && (
                                                <div className="flex flex-col">
                                                    {sortBy === column.id ? (
                                                        sortDirection === 'asc' ? (
                                                            <ChevronUp className="h-3 w-3" />
                                                        ) : (
                                                            <ChevronDown className="h-3 w-3" />
                                                        )
                                                    ) : (
                                                        <ArrowUpDown className="h-3 w-3 opacity-50" />
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            {data.map((row) => {
                                const isSelected = selectedRows.some((r) => r.id === row.id);
                                return (
                                    <tr
                                        key={row.id}
                                        className={cn(
                                            'transition-colors hover:bg-gray-50 dark:hover:bg-gray-900',
                                            onRowClick && 'cursor-pointer',
                                            isSelected && 'bg-primary/5 hover:bg-primary/10',
                                        )}
                                        onClick={() => onRowClick?.(row)}
                                    >
                                        {selectable && (
                                            <td className="px-4 py-4">
                                                <Checkbox
                                                    checked={isSelected}
                                                    onCheckedChange={(checked) => handleRowSelection(row, checked as boolean)}
                                                    onClick={(e) => e.stopPropagation()}
                                                    className="rounded border-gray-300"
                                                />
                                            </td>
                                        )}
                                        {columns.map((column) => (
                                            <td
                                                key={column.id}
                                                className={cn(
                                                    'px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100',
                                                    column.align === 'center' && 'text-center',
                                                    column.align === 'right' && 'text-right',
                                                )}
                                            >
                                                {getCellValue(row, column) as ReactNode}
                                            </td>
                                        ))}
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            {pagination && (
                <div className="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-950">
                    <div className="flex items-center text-sm text-gray-700 dark:text-gray-300">
                        Showing {(pagination.currentPage - 1) * pagination.pageSize + 1} to{' '}
                        {Math.min(pagination.currentPage * pagination.pageSize, pagination.totalItems)} of {pagination.totalItems} results
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => pagination.onPageChange(pagination.currentPage - 1)}
                            disabled={pagination.currentPage === 1}
                        >
                            Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => pagination.onPageChange(pagination.currentPage + 1)}
                            disabled={pagination.currentPage * pagination.pageSize >= pagination.totalItems}
                        >
                            Next
                        </Button>
                    </div>
                </div>
            )}
        </div>
    );
}
