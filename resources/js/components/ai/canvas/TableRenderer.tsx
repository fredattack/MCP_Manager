import { useMemo, useState } from 'react';

interface TableRendererProps {
    content: string;
    className?: string;
}

export function TableRenderer({ content, className = '' }: TableRendererProps) {
    const [sortConfig, setSortConfig] = useState<{
        key: number;
        direction: 'asc' | 'desc';
    } | null>(null);
    const [filterValue, setFilterValue] = useState('');

    const tableData = useMemo(() => {
        const lines = content.split('\n').filter((line) => line.trim().includes('|'));
        if (lines.length < 2) return null;

        const headers = lines[0]
            .split('|')
            .map((cell) => cell.trim())
            .filter((cell) => cell !== '');

        // Skip the separator line (usually contains --- or similar)
        const dataLines = lines.slice(2);
        const rows = dataLines
            .map((line) =>
                line
                    .split('|')
                    .map((cell) => cell.trim())
                    .filter((cell) => cell !== ''),
            )
            .filter((row) => row.length > 0);

        return { headers, rows };
    }, [content]);

    const filteredAndSortedData = useMemo(() => {
        if (!tableData) return null;

        let filteredRows = tableData.rows;

        // Apply filter
        if (filterValue) {
            filteredRows = filteredRows.filter((row) => row.some((cell) => cell.toLowerCase().includes(filterValue.toLowerCase())));
        }

        // Apply sort
        if (sortConfig) {
            filteredRows = [...filteredRows].sort((a, b) => {
                const aValue = a[sortConfig.key] || '';
                const bValue = b[sortConfig.key] || '';

                // Try to parse as numbers for better sorting
                const aNum = parseFloat(aValue);
                const bNum = parseFloat(bValue);

                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return sortConfig.direction === 'asc' ? aNum - bNum : bNum - aNum;
                }

                // String comparison
                const comparison = aValue.localeCompare(bValue);
                return sortConfig.direction === 'asc' ? comparison : -comparison;
            });
        }

        return { ...tableData, rows: filteredRows };
    }, [tableData, filterValue, sortConfig]);

    const handleSort = (columnIndex: number) => {
        setSortConfig((prevConfig) => {
            if (prevConfig?.key === columnIndex) {
                return {
                    key: columnIndex,
                    direction: prevConfig.direction === 'asc' ? 'desc' : 'asc',
                };
            }
            return { key: columnIndex, direction: 'asc' };
        });
    };

    const exportToCSV = () => {
        if (!tableData) return;

        const csvContent = [tableData.headers.join(','), ...tableData.rows.map((row) => row.join(','))].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'table-data.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    if (!filteredAndSortedData) {
        return <div className={`p-4 text-center text-gray-500 dark:text-gray-400 ${className}`}>Invalid table format</div>;
    }

    return (
        <div className={`rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 ${className}`}>
            {/* Table controls */}
            <div className="border-b border-gray-200 p-4 dark:border-gray-700">
                <div className="flex items-center justify-between gap-4">
                    <div className="flex items-center gap-2">
                        <svg className="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"
                            />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 5v14" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 5v14" />
                        </svg>
                        <span className="text-sm font-medium text-gray-900 dark:text-gray-100">Table Data</span>
                        <span className="text-xs text-gray-500 dark:text-gray-400">{filteredAndSortedData.rows.length} rows</span>
                    </div>

                    <div className="flex items-center gap-2">
                        {/* Search */}
                        <div className="relative">
                            <input
                                type="text"
                                placeholder="Filter table..."
                                value={filterValue}
                                onChange={(e) => setFilterValue(e.target.value)}
                                className="w-48 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                            />
                            <svg
                                className="absolute top-1/2 right-2 h-4 w-4 -translate-y-1/2 transform text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        {/* Export */}
                        <button
                            onClick={exportToCSV}
                            className="flex items-center gap-1 rounded-md bg-blue-600 px-3 py-1.5 text-sm text-white transition-colors hover:bg-blue-700"
                            title="Export as CSV"
                        >
                            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                            Export
                        </button>
                    </div>
                </div>
            </div>

            {/* Table */}
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead className="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            {filteredAndSortedData.headers.map((header, index) => (
                                <th
                                    key={index}
                                    onClick={() => handleSort(index)}
                                    className="cursor-pointer px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase transition-colors hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                                >
                                    <div className="flex items-center gap-1">
                                        {header}
                                        {sortConfig?.key === index && (
                                            <svg
                                                className={`h-3 w-3 transition-transform ${sortConfig.direction === 'desc' ? 'rotate-180' : ''}`}
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 15l7-7 7 7" />
                                            </svg>
                                        )}
                                    </div>
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        {filteredAndSortedData.rows.map((row, rowIndex) => (
                            <tr key={rowIndex} className="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
                                {row.map((cell, cellIndex) => (
                                    <td key={cellIndex} className="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {/* Simple detection for numbers to right-align them */}
                                        <div className={!isNaN(parseFloat(cell)) && isFinite(Number(cell)) ? 'text-right font-mono' : ''}>{cell}</div>
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Footer */}
            {filteredAndSortedData.rows.length === 0 && filterValue && (
                <div className="p-8 text-center text-gray-500 dark:text-gray-400">
                    <svg className="mx-auto mb-2 h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    No results found for "{filterValue}"
                </div>
            )}
        </div>
    );
}
