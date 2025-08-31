import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Calendar, Target, AlertCircle, CheckCircle2, Loader2 } from 'lucide-react';
import { useDailyPlanningFeature } from '@/hooks/use-daily-planning-query';
import { MITDisplay } from '@/components/daily-planning/mit-display';
import { TimeBlocksDisplay } from '@/components/daily-planning/time-blocks-display';
import { TaskListDisplay } from '@/components/daily-planning/task-list-display';
import { UpdateConfirmationDialog } from '@/components/daily-planning/update-confirmation-dialog';
import { PlanningMarkdownView } from '@/components/daily-planning/planning-markdown-view';
import { format } from 'date-fns';

interface DailyPlanningProps {
    today: string;
}

export default function DailyPlanning({ today }: DailyPlanningProps) {
    const { 
        planning, 
        generating,
        updating,
        generatePlanning, 
        updateTodoistTasks 
    } = useDailyPlanningFeature();
    
    const [showUpdateDialog, setShowUpdateDialog] = useState(false);
    const [selectedView, setSelectedView] = useState<'visual' | 'markdown'>('visual');

    const handleGeneratePlanning = async () => {
        const result = await generatePlanning();
        if (result?.success && result.planning) {
            // Planning generated successfully
        }
    };

    const handleUpdateTasks = async (updateType: 'all' | 'partial' | 'none', selected?: string[]) => {
        if (!planning?.planning_id) return;

        const result = await updateTodoistTasks(planning.planning_id, {
            type: updateType,
            selected
        });

        if (result?.success) {
            setShowUpdateDialog(false);
        }
    };

    return (
        <AppLayout>
            <Head title="Daily Planning" />

            <div className="container mx-auto py-6 space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Daily Planning</h1>
                        <p className="text-muted-foreground mt-1">
                            Optimize your day with AI-powered task prioritization
                        </p>
                    </div>
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <Calendar className="h-4 w-4" />
                            {format(new Date(today), 'EEEE, MMMM d, yyyy')}
                        </div>
                    </div>
                </div>

                {/* Action Buttons */}
                {!planning && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Generate Your Daily Plan</CardTitle>
                            <CardDescription>
                                Let AI analyze your Todoist tasks and create an optimized schedule following proven productivity methods
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button 
                                onClick={handleGeneratePlanning}
                                disabled={generating}
                                size="lg"
                                className="w-full sm:w-auto"
                            >
                                {generating ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Analyzing Tasks...
                                    </>
                                ) : (
                                    <>
                                        <Target className="mr-2 h-4 w-4" />
                                        Generate Daily Planning
                                    </>
                                )}
                            </Button>
                        </CardContent>
                    </Card>
                )}

                {/* Planning Display */}
                {planning && planning.planning && (
                    <>
                        {/* View Toggle */}
                        <div className="flex justify-end gap-2">
                            <Button
                                variant={selectedView === 'visual' ? 'default' : 'outline'}
                                size="sm"
                                onClick={() => setSelectedView('visual')}
                            >
                                Visual View
                            </Button>
                            <Button
                                variant={selectedView === 'markdown' ? 'default' : 'outline'}
                                size="sm"
                                onClick={() => setSelectedView('markdown')}
                            >
                                Markdown View
                            </Button>
                        </div>

                        {/* Alerts */}
                        {planning.planning.alerts && planning.planning.alerts.length > 0 && (
                            <div className="space-y-2">
                                {planning.planning.alerts.map((alert, index) => (
                                    <Alert key={index} variant={alert.severity === 'high' ? 'destructive' : 'default'}>
                                        <AlertCircle className="h-4 w-4" />
                                        <AlertDescription>
                                            <strong>{alert.type}:</strong> {alert.message}
                                        </AlertDescription>
                                    </Alert>
                                ))}
                            </div>
                        )}

                        {selectedView === 'visual' ? (
                            <div className="grid gap-6">
                                {/* MIT Section */}
                                {planning.planning.mit && (
                                    <MITDisplay mit={planning.planning.mit} />
                                )}

                                {/* Top 6 Tasks */}
                                <TaskListDisplay 
                                    tasks={planning.planning.top_tasks}
                                    title="Top 6 Tasks (Ivy Lee Method)"
                                />

                                {/* Time Blocks */}
                                <TimeBlocksDisplay 
                                    timeBlocks={planning.planning.time_blocks}
                                />

                                {/* Additional Tasks */}
                                {planning.planning.additional_tasks && planning.planning.additional_tasks.length > 0 && (
                                    <TaskListDisplay 
                                        tasks={planning.planning.additional_tasks}
                                        title="Additional Tasks (if time permits)"
                                        variant="secondary"
                                    />
                                )}

                                {/* Summary Stats */}
                                {planning.planning.summary && (
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Planning Summary</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                                                <div className="text-center">
                                                    <div className="text-2xl font-bold">{planning.planning.summary.total_tasks}</div>
                                                    <div className="text-sm text-muted-foreground">Total Tasks</div>
                                                </div>
                                                <div className="text-center">
                                                    <div className="text-2xl font-bold">{planning.planning.summary.p1_tasks}</div>
                                                    <div className="text-sm text-muted-foreground">P1 Tasks</div>
                                                </div>
                                                <div className="text-center">
                                                    <div className="text-2xl font-bold">{planning.planning.summary.hexeko_tasks}</div>
                                                    <div className="text-sm text-muted-foreground">Hexeko Tasks</div>
                                                </div>
                                                <div className="text-center">
                                                    <div className="text-2xl font-bold">{Math.round(planning.planning.summary.total_work_time / 60)}h</div>
                                                    <div className="text-sm text-muted-foreground">Work Time</div>
                                                </div>
                                                <div className="text-center">
                                                    <div className="text-2xl font-bold">{planning.planning.summary.total_break_time}m</div>
                                                    <div className="text-sm text-muted-foreground">Break Time</div>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                )}

                                {/* Update Todoist Button */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Update Todoist</CardTitle>
                                        <CardDescription>
                                            Apply the generated planning to your Todoist tasks
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <Button
                                            onClick={() => setShowUpdateDialog(true)}
                                            disabled={updating}
                                            className="w-full sm:w-auto"
                                        >
                                            {updating ? (
                                                <>
                                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                    Updating Tasks...
                                                </>
                                            ) : (
                                                <>
                                                    <CheckCircle2 className="mr-2 h-4 w-4" />
                                                    Update Todoist Tasks
                                                </>
                                            )}
                                        </Button>
                                    </CardContent>
                                </Card>
                            </div>
                        ) : (
                            <PlanningMarkdownView markdown={planning.markdown} />
                        )}
                    </>
                )}

                {/* No tasks message */}
                {planning && !planning.planning && (
                    <Alert>
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>
                            No tasks found for today. Add some tasks to your Todoist to generate a planning.
                        </AlertDescription>
                    </Alert>
                )}
            </div>

            {/* Update Confirmation Dialog */}
            {planning && showUpdateDialog && (
                <UpdateConfirmationDialog
                    open={showUpdateDialog}
                    onClose={() => setShowUpdateDialog(false)}
                    onConfirm={handleUpdateTasks}
                    updates={planning.planning.todoist_updates}
                    loading={updating}
                />
            )}
        </AppLayout>
    );
}