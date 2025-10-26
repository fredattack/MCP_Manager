import { JiraBoardList } from '@/components/jira/board-list';
import { CreateIssueDialog } from '@/components/jira/create-issue-dialog';
import { JiraIssueList } from '@/components/jira/issue-list';
import { JiraProjectList } from '@/components/jira/project-list';
import { JiraSprintView } from '@/components/jira/sprint-view';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useCreateJiraIssue, useJiraBoards, useJiraIssues, useJiraProjects } from '@/hooks/use-jira-query';
import AppLayout from '@/layouts/app-layout';
import { queryClient, queryKeys } from '@/lib/react-query';
import { Head } from '@inertiajs/react';
import { Plus, RefreshCw } from 'lucide-react';
import { useState } from 'react';

interface JiraProps {
    hasIntegration: boolean;
}

export default function Jira({ hasIntegration }: JiraProps) {
    const [activeTab, setActiveTab] = useState('projects');
    const [showCreateIssue, setShowCreateIssue] = useState(false);
    const [searchJql, setSearchJql] = useState('');

    // React Query hooks
    const { data: projects = [], isLoading: projectsLoading } = useJiraProjects();
    const { data: boardsData, isLoading: boardsLoading } = useJiraBoards();
    const { data: issuesData, refetch: searchIssues } = useJiraIssues(searchJql, {
        max_results: 50,
    });
    const createIssueMutation = useCreateJiraIssue();

    const boards = boardsData?.values || [];
    const issues = issuesData?.issues || [];

    const handleRefresh = () => {
        if (activeTab === 'projects') {
            queryClient.invalidateQueries({ queryKey: queryKeys.jiraProjects() });
        } else if (activeTab === 'boards') {
            queryClient.invalidateQueries({ queryKey: queryKeys.jiraBoards() });
        }
    };

    const handleSearch = (jql: string) => {
        setSearchJql(jql);
        if (jql) {
            searchIssues();
        }
    };

    const handleCreateIssue = async (issueData: Record<string, unknown>) => {
        await createIssueMutation.mutateAsync(issueData);
        setShowCreateIssue(false);
    };

    if (!hasIntegration) {
        return (
            <AppLayout>
                <Head title="JIRA Integration" />
                <div className="container mx-auto py-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>JIRA Integration Not Connected</CardTitle>
                            <CardDescription>Connect your JIRA account to manage issues, boards, and sprints.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button asChild>
                                <a href="/settings/integrations">Connect JIRA</a>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    const loading = projectsLoading || boardsLoading;

    return (
        <AppLayout>
            <Head title="JIRA Integration" />

            <div className="container mx-auto space-y-6 py-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">JIRA Integration</h1>
                        <p className="text-muted-foreground mt-1">Manage your JIRA projects, issues, and sprints</p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" size="sm" onClick={handleRefresh} disabled={loading}>
                            <RefreshCw className="mr-2 h-4 w-4" />
                            Refresh
                        </Button>
                        <Button size="sm" onClick={() => setShowCreateIssue(true)}>
                            <Plus className="mr-2 h-4 w-4" />
                            Create Issue
                        </Button>
                    </div>
                </div>

                {/* Main Content */}
                <Tabs value={activeTab} onValueChange={setActiveTab}>
                    <TabsList className="grid w-full grid-cols-4">
                        <TabsTrigger value="projects">Projects</TabsTrigger>
                        <TabsTrigger value="boards">Boards</TabsTrigger>
                        <TabsTrigger value="issues">Issues</TabsTrigger>
                        <TabsTrigger value="sprints">Sprints</TabsTrigger>
                    </TabsList>

                    <TabsContent value="projects" className="space-y-4">
                        <JiraProjectList projects={projects} loading={projectsLoading} />
                    </TabsContent>

                    <TabsContent value="boards" className="space-y-4">
                        <JiraBoardList boards={boards} loading={boardsLoading} />
                    </TabsContent>

                    <TabsContent value="issues" className="space-y-4">
                        <JiraIssueList issues={issues} loading={loading} onSearch={handleSearch} />
                    </TabsContent>

                    <TabsContent value="sprints" className="space-y-4">
                        <JiraSprintView boards={boards} loading={boardsLoading} />
                    </TabsContent>
                </Tabs>

                {/* Create Issue Dialog */}
                <CreateIssueDialog
                    open={showCreateIssue}
                    onClose={() => setShowCreateIssue(false)}
                    onCreate={handleCreateIssue}
                    projects={projects}
                />
            </div>
        </AppLayout>
    );
}
