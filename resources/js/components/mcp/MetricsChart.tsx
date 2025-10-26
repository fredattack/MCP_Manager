import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import React from 'react';
import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Line,
    LineChart,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

interface MetricsChartProps {
    title: string;
    description?: string;
    data: any[];
    type: 'line' | 'area' | 'bar' | 'pie';
    dataKey: string | string[];
    xAxisKey?: string;
    height?: number;
    colors?: string[];
    showLegend?: boolean;
    showGrid?: boolean;
    className?: string;
}

const COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

export function MetricsChart({
    title,
    description,
    data,
    type,
    dataKey,
    xAxisKey = 'name',
    height = 300,
    colors = COLORS,
    showLegend = true,
    showGrid = true,
    className,
}: MetricsChartProps) {
    const renderChart = () => {
        switch (type) {
            case 'line':
                return (
                    <ResponsiveContainer width="100%" height={height}>
                        <LineChart data={data}>
                            {showGrid && <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />}
                            <XAxis dataKey={xAxisKey} className="text-xs" stroke="currentColor" opacity={0.5} />
                            <YAxis className="text-xs" stroke="currentColor" opacity={0.5} />
                            <Tooltip
                                contentStyle={{
                                    backgroundColor: 'hsl(var(--background))',
                                    border: '1px solid hsl(var(--border))',
                                    borderRadius: '6px',
                                }}
                            />
                            {showLegend && <Legend />}
                            {Array.isArray(dataKey) ? (
                                dataKey.map((key, index) => (
                                    <Line
                                        key={key}
                                        type="monotone"
                                        dataKey={key}
                                        stroke={colors[index % colors.length]}
                                        strokeWidth={2}
                                        dot={false}
                                    />
                                ))
                            ) : (
                                <Line type="monotone" dataKey={dataKey} stroke={colors[0]} strokeWidth={2} dot={false} />
                            )}
                        </LineChart>
                    </ResponsiveContainer>
                );

            case 'area':
                return (
                    <ResponsiveContainer width="100%" height={height}>
                        <AreaChart data={data}>
                            {showGrid && <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />}
                            <XAxis dataKey={xAxisKey} className="text-xs" stroke="currentColor" opacity={0.5} />
                            <YAxis className="text-xs" stroke="currentColor" opacity={0.5} />
                            <Tooltip
                                contentStyle={{
                                    backgroundColor: 'hsl(var(--background))',
                                    border: '1px solid hsl(var(--border))',
                                    borderRadius: '6px',
                                }}
                            />
                            {showLegend && <Legend />}
                            {Array.isArray(dataKey) ? (
                                dataKey.map((key, index) => (
                                    <Area
                                        key={key}
                                        type="monotone"
                                        dataKey={key}
                                        stroke={colors[index % colors.length]}
                                        fill={colors[index % colors.length]}
                                        fillOpacity={0.3}
                                    />
                                ))
                            ) : (
                                <Area type="monotone" dataKey={dataKey} stroke={colors[0]} fill={colors[0]} fillOpacity={0.3} />
                            )}
                        </AreaChart>
                    </ResponsiveContainer>
                );

            case 'bar':
                return (
                    <ResponsiveContainer width="100%" height={height}>
                        <BarChart data={data}>
                            {showGrid && <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />}
                            <XAxis dataKey={xAxisKey} className="text-xs" stroke="currentColor" opacity={0.5} />
                            <YAxis className="text-xs" stroke="currentColor" opacity={0.5} />
                            <Tooltip
                                contentStyle={{
                                    backgroundColor: 'hsl(var(--background))',
                                    border: '1px solid hsl(var(--border))',
                                    borderRadius: '6px',
                                }}
                            />
                            {showLegend && <Legend />}
                            {Array.isArray(dataKey) ? (
                                dataKey.map((key, index) => <Bar key={key} dataKey={key} fill={colors[index % colors.length]} />)
                            ) : (
                                <Bar dataKey={dataKey} fill={colors[0]} />
                            )}
                        </BarChart>
                    </ResponsiveContainer>
                );

            case 'pie':
                return (
                    <ResponsiveContainer width="100%" height={height}>
                        <PieChart>
                            <Pie
                                data={data}
                                cx="50%"
                                cy="50%"
                                labelLine={false}
                                label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                                outerRadius={80}
                                fill="#8884d8"
                                dataKey={typeof dataKey === 'string' ? dataKey : dataKey[0]}
                            >
                                {data.map((entry, index) => (
                                    <Cell key={`cell-${index}`} fill={colors[index % colors.length]} />
                                ))}
                            </Pie>
                            <Tooltip
                                contentStyle={{
                                    backgroundColor: 'hsl(var(--background))',
                                    border: '1px solid hsl(var(--border))',
                                    borderRadius: '6px',
                                }}
                            />
                            {showLegend && <Legend />}
                        </PieChart>
                    </ResponsiveContainer>
                );

            default:
                return null;
        }
    };

    return (
        <Card className={className}>
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                {description && <CardDescription>{description}</CardDescription>}
            </CardHeader>
            <CardContent>
                {data && data.length > 0 ? (
                    renderChart()
                ) : (
                    <div className="text-muted-foreground flex h-[200px] items-center justify-center">No data available</div>
                )}
            </CardContent>
        </Card>
    );
}

interface MetricCardProps {
    title: string;
    value: string | number;
    description?: string;
    trend?: {
        value: number;
        label: string;
    };
    icon?: React.ReactNode;
    className?: string;
}

export function MetricCard({ title, value, description, trend, icon, className }: MetricCardProps) {
    return (
        <Card className={className}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">{title}</CardTitle>
                {icon}
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">{value}</div>
                {description && <p className="text-muted-foreground mt-1 text-xs">{description}</p>}
                {trend && (
                    <div className="mt-2 flex items-center">
                        <Badge variant={trend.value >= 0 ? 'default' : 'destructive'} className="text-xs">
                            {trend.value >= 0 ? '+' : ''}
                            {trend.value}%
                        </Badge>
                        <span className="text-muted-foreground ml-2 text-xs">{trend.label}</span>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
