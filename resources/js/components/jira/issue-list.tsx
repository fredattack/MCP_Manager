import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { Search, AlertCircle } from 'lucide-react';
import { format } from 'date-fns';

interface JiraIssue {
    id: string;
    key: string;
    fields: {
        summary: string;
        status: {
            name: string;
            statusCategory: {
                key: string;
                colorName: string;
            };
        };
        issuetype: {
            name: string;
            iconUrl: string;
        };
        priority: {
            name: string;
            iconUrl: string;
        };
        assignee?: {
            displayName: string;
            avatarUrls: {
                '48x48': string;
            };
        };
        created: string;
        updated: string;
    };
}

interface JiraIssueListProps {
    issues: JiraIssue[];
    loading: boolean;
    onSearch: (jql: string) => void;
}

export function JiraIssueList({ issues, loading, onSearch }: JiraIssueListProps) {
    const [searchQuery, setSearchQuery] = useState('');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            onSearch(searchQuery);
        }
    };

    const getStatusColor = (colorName: string) => {
        const colorMap: Record<string, string> = {
            'blue-gray': 'bg-blue-500',
            'yellow': 'bg-yellow-500',
            'green': 'bg-green-500',
            'medium-gray': 'bg-gray-500',
        };
        return colorMap[colorName] || 'bg-gray-500';
    };

    return (
        <div className="space-y-4">
            {/* Search Bar */}
            <form onSubmit={handleSearch} className="flex gap-2">
                <Input
                    placeholder="Enter JQL query (e.g., project = PROJ AND status = 'In Progress')"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="flex-1"
                />
                <Button type="submit" disabled={loading}>
                    <Search className="h-4 w-4 mr-2" />
                    Search
                </Button>
            </form>

            {/* Issues List */}
            {loading ? (
                <div className="space-y-2">
                    {[...Array(5)].map((_, i) => (
                        <Card key={i}>
                            <CardHeader className="pb-3">
                                <div className="flex items-center gap-4">
                                    <Skeleton className="h-12 w-12" />
                                    <div className="flex-1">
                                        <Skeleton className="h-4 w-3/4 mb-2" />
                                        <Skeleton className="h-3 w-1/2" />
                                    </div>
                                </div>
                            </CardHeader>
                        </Card>
                    ))}
                </div>
            ) : issues.length === 0 ? (
                <Card>
                    <CardContent className="flex flex-col items-center justify-center py-8">
                        <AlertCircle className="h-12 w-12 text-muted-foreground mb-4" />
                        <p className="text-muted-foreground">No issues found</p>
                        <p className="text-sm text-muted-foreground mt-2">
                            Try searching with a JQL query
                        </p>
                    </CardContent>
                </Card>
            ) : (
                <div className="space-y-2">
                    {issues.map((issue) => (
                        <Card key={issue.id} className="hover:shadow-md transition-shadow">
                            <CardHeader className="pb-3">
                                <div className="flex items-start justify-between">
                                    <div className="flex items-start gap-3">
                                        <img 
                                            src={issue.fields.issuetype.iconUrl} 
                                            alt={issue.fields.issuetype.name}
                                            className="h-5 w-5 mt-0.5"
                                        />
                                        <div className="flex-1">
                                            <div className="flex items-center gap-2 mb-1">
                                                <span className="font-mono text-sm text-muted-foreground">
                                                    {issue.key}
                                                </span>
                                                <Badge 
                                                    variant="outline" 
                                                    className={`${getStatusColor(issue.fields.status.statusCategory.colorName)} text-white border-0`}
                                                >
                                                    {issue.fields.status.name}
                                                </Badge>
                                            </div>
                                            <CardTitle className="text-base font-medium">
                                                {issue.fields.summary}
                                            </CardTitle>
                                            <CardDescription className="mt-1">
                                                <div className="flex items-center gap-4 text-xs">
                                                    <span>Type: {issue.fields.issuetype.name}</span>
                                                    <span>Priority: {issue.fields.priority.name}</span>
                                                    <span>Updated: {format(new Date(issue.fields.updated), 'MMM d, yyyy')}</span>
                                                </div>
                                            </CardDescription>
                                        </div>
                                    </div>
                                    {issue.fields.assignee && (
                                        <div className="flex items-center gap-2">
                                            <Avatar className="h-8 w-8">
                                                <AvatarImage 
                                                    src={issue.fields.assignee.avatarUrls['48x48']} 
                                                    alt={issue.fields.assignee.displayName} 
                                                />
                                                <AvatarFallback>
                                                    {issue.fields.assignee.displayName.charAt(0)}
                                                </AvatarFallback>
                                            </Avatar>
                                        </div>
                                    )}
                                </div>
                            </CardHeader>
                        </Card>
                    ))}
                </div>
            )}
        </div>
    );
}