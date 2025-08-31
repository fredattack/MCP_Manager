import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { NLPCommandInput } from '@/components/ai/nlp/NLPCommandInput';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ParsedCommand } from '@/lib/nlp';
import { Sparkles, Code, MessageSquare } from 'lucide-react';

export default function NLPDemoPage() {
    const [commandHistory, setCommandHistory] = useState<ParsedCommand[]>([]);
    const [selectedCommand, setSelectedCommand] = useState<ParsedCommand | null>(null);
    const [activeTab, setActiveTab] = useState('Todoist');

    const handleCommand = (command: ParsedCommand) => {
        console.log('Command received:', command);
        setCommandHistory(prev => [command, ...prev].slice(0, 10)); // Keep last 10 commands
        setSelectedCommand(command);
    };

    const exampleCommands = [
        { category: 'Todoist', commands: [
            'Create task "Review PR" for tomorrow with priority P1',
            'Show my tasks for today',
            'Complete task "Write documentation"',
            'Generate daily planning',
        ]},
        { category: 'Notion', commands: [
            'Search in Notion for "API documentation"',
            'Create Notion page "Meeting Notes"',
            'Query tasks database',
        ]},
        { category: 'JIRA', commands: [
            'Create JIRA issue "Fix login bug" in project PROJ',
            'Show current sprint',
            'Move issue to done',
        ]},
        { category: 'Cross-Service', commands: [
            'Convert this email to task',
            'Create JIRA issue from Sentry error',
        ]},
    ];

    return (
        <AppLayout>
            <Head title="NLP Engine Demo" />

            <div className="container mx-auto py-6 space-y-6">
                <div className="flex items-center gap-3 mb-6">
                    <Sparkles className="h-8 w-8 text-purple-600" />
                    <div>
                        <h1 className="text-3xl font-bold">Natural Language Processing Demo</h1>
                        <p className="text-muted-foreground mt-1">
                            Test the NLP engine by typing natural language commands
                        </p>
                    </div>
                </div>

                {/* Command Input */}
                <Card>
                    <CardHeader>
                        <CardTitle>Try a Command</CardTitle>
                        <CardDescription>
                            Type a natural language command to see how it's interpreted
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <NLPCommandInput
                            onCommand={handleCommand}
                            placeholder="e.g., Create task 'Review documentation' for tomorrow"
                            className="w-full"
                        />
                    </CardContent>
                </Card>

                {/* Results Display */}
                <div className="grid md:grid-cols-2 gap-6">
                    {/* Command Analysis */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Code className="h-5 w-5" />
                                Command Analysis
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {selectedCommand ? (
                                <div className="space-y-4">
                                    <div>
                                        <h4 className="font-medium mb-1">Original Text</h4>
                                        <p className="text-sm text-muted-foreground">
                                            {selectedCommand.originalText}
                                        </p>
                                    </div>

                                    <div>
                                        <h4 className="font-medium mb-1">Intent</h4>
                                        <div className="flex items-center gap-2">
                                            <Badge variant="default">
                                                {selectedCommand.intent.intent}
                                            </Badge>
                                            {selectedCommand.intent.service && (
                                                <Badge variant="secondary">
                                                    {selectedCommand.intent.service}
                                                </Badge>
                                            )}
                                            <span className="text-sm text-muted-foreground">
                                                ({Math.round(selectedCommand.confidence * 100)}% confidence)
                                            </span>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 className="font-medium mb-1">Extracted Entities</h4>
                                        <div className="space-y-1">
                                            {selectedCommand.entities.map((entity, idx) => (
                                                <div key={idx} className="flex items-center gap-2 text-sm">
                                                    <Badge variant="outline" className="text-xs">
                                                        {entity.type}
                                                    </Badge>
                                                    <span className="font-mono">{entity.value}</span>
                                                    <span className="text-muted-foreground">
                                                        ({Math.round(entity.confidence * 100)}%)
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    {selectedCommand.params && (
                                        <div>
                                            <h4 className="font-medium mb-1">Parameters</h4>
                                            <pre className="text-xs bg-muted p-2 rounded overflow-auto">
                                                {JSON.stringify(selectedCommand.params, null, 2)}
                                            </pre>
                                        </div>
                                    )}

                                    {selectedCommand.suggestions && selectedCommand.suggestions.length > 0 && (
                                        <div>
                                            <h4 className="font-medium mb-1">Suggestions</h4>
                                            <div className="space-y-1">
                                                {selectedCommand.suggestions.map((suggestion, idx) => (
                                                    <div key={idx} className="text-sm">
                                                        <p className="text-muted-foreground">
                                                            {suggestion.text}
                                                        </p>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <p className="text-muted-foreground text-center py-8">
                                    Enter a command to see the analysis
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Command History */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <MessageSquare className="h-5 w-5" />
                                Command History
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {commandHistory.length > 0 ? (
                                <div className="space-y-2">
                                    {commandHistory.map((cmd, idx) => (
                                        <button
                                            key={idx}
                                            onClick={() => setSelectedCommand(cmd)}
                                            className="w-full text-left p-3 rounded-lg border hover:bg-muted transition-colors"
                                        >
                                            <div className="flex items-center justify-between mb-1">
                                                <Badge variant="secondary" className="text-xs">
                                                    {cmd.intent.service || 'general'}
                                                </Badge>
                                                <span className="text-xs text-muted-foreground">
                                                    {Math.round(cmd.confidence * 100)}%
                                                </span>
                                            </div>
                                            <p className="text-sm truncate">{cmd.originalText}</p>
                                        </button>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-muted-foreground text-center py-8">
                                    No commands yet
                                </p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Example Commands */}
                <Card>
                    <CardHeader>
                        <CardTitle>Example Commands</CardTitle>
                        <CardDescription>
                            Try these commands to see how the NLP engine interprets them
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Tabs value={activeTab} onValueChange={setActiveTab}>
                            <TabsList>
                                {exampleCommands.map(cat => (
                                    <TabsTrigger key={cat.category} value={cat.category}>
                                        {cat.category}
                                    </TabsTrigger>
                                ))}
                            </TabsList>
                            {exampleCommands.map(cat => (
                                <TabsContent key={cat.category} value={cat.category}>
                                    <div className="grid gap-2">
                                        {cat.commands.map((cmd, idx) => (
                                            <button
                                                key={idx}
                                                onClick={() => {
                                                    // Simulate typing the command
                                                    const input = document.querySelector('input[placeholder*="Type a natural language command"]') as HTMLInputElement;
                                                    if (input) {
                                                        input.value = cmd;
                                                        input.dispatchEvent(new Event('input', { bubbles: true }));
                                                    }
                                                }}
                                                className="text-left p-3 rounded-lg border hover:bg-muted transition-colors"
                                            >
                                                <code className="text-sm">{cmd}</code>
                                            </button>
                                        ))}
                                    </div>
                                </TabsContent>
                            ))}
                        </Tabs>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}