import { NLPCommandInput } from '@/components/ai/nlp/NLPCommandInput';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import { ParsedCommand } from '@/lib/nlp';
import { Head } from '@inertiajs/react';
import { Code, MessageSquare, Sparkles } from 'lucide-react';
import { useState } from 'react';

export default function NLPDemoPage() {
    const [commandHistory, setCommandHistory] = useState<ParsedCommand[]>([]);
    const [selectedCommand, setSelectedCommand] = useState<ParsedCommand | null>(null);
    const [activeTab, setActiveTab] = useState('Todoist');

    const handleCommand = (command: ParsedCommand) => {
        console.log('Command received:', command);
        setCommandHistory((prev) => [command, ...prev].slice(0, 10)); // Keep last 10 commands
        setSelectedCommand(command);
    };

    const exampleCommands = [
        {
            category: 'Todoist',
            commands: [
                'Create task "Review PR" for tomorrow with priority P1',
                'Show my tasks for today',
                'Complete task "Write documentation"',
                'Generate daily planning',
            ],
        },
        { category: 'Notion', commands: ['Search in Notion for "API documentation"', 'Create Notion page "Meeting Notes"', 'Query tasks database'] },
        { category: 'JIRA', commands: ['Create JIRA issue "Fix login bug" in project PROJ', 'Show current sprint', 'Move issue to done'] },
        { category: 'Cross-Service', commands: ['Convert this email to task', 'Create JIRA issue from Sentry error'] },
    ];

    return (
        <AppLayout>
            <Head title="NLP Engine Demo" />

            <div className="container mx-auto space-y-6 py-6">
                <div className="mb-6 flex items-center gap-3">
                    <Sparkles className="h-8 w-8 text-purple-600" />
                    <div>
                        <h1 className="text-3xl font-bold">Natural Language Processing Demo</h1>
                        <p className="text-muted-foreground mt-1">Test the NLP engine by typing natural language commands</p>
                    </div>
                </div>

                {/* Command Input */}
                <Card>
                    <CardHeader>
                        <CardTitle>Try a Command</CardTitle>
                        <CardDescription>Type a natural language command to see how it's interpreted</CardDescription>
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
                <div className="grid gap-6 md:grid-cols-2">
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
                                        <h4 className="mb-1 font-medium">Original Text</h4>
                                        <p className="text-muted-foreground text-sm">{selectedCommand.originalText}</p>
                                    </div>

                                    <div>
                                        <h4 className="mb-1 font-medium">Intent</h4>
                                        <div className="flex items-center gap-2">
                                            <Badge variant="default">{selectedCommand.intent.intent}</Badge>
                                            {selectedCommand.intent.service && <Badge variant="secondary">{selectedCommand.intent.service}</Badge>}
                                            <span className="text-muted-foreground text-sm">
                                                ({Math.round(selectedCommand.confidence * 100)}% confidence)
                                            </span>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 className="mb-1 font-medium">Extracted Entities</h4>
                                        <div className="space-y-1">
                                            {selectedCommand.entities.map((entity, idx) => (
                                                <div key={idx} className="flex items-center gap-2 text-sm">
                                                    <Badge variant="outline" className="text-xs">
                                                        {entity.type}
                                                    </Badge>
                                                    <span className="font-mono">{entity.value}</span>
                                                    <span className="text-muted-foreground">({Math.round(entity.confidence * 100)}%)</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    {selectedCommand.params && (
                                        <div>
                                            <h4 className="mb-1 font-medium">Parameters</h4>
                                            <pre className="bg-muted overflow-auto rounded p-2 text-xs">
                                                {JSON.stringify(selectedCommand.params, null, 2)}
                                            </pre>
                                        </div>
                                    )}

                                    {selectedCommand.suggestions && selectedCommand.suggestions.length > 0 && (
                                        <div>
                                            <h4 className="mb-1 font-medium">Suggestions</h4>
                                            <div className="space-y-1">
                                                {selectedCommand.suggestions.map((suggestion, idx) => (
                                                    <div key={idx} className="text-sm">
                                                        <p className="text-muted-foreground">{suggestion.text}</p>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <p className="text-muted-foreground py-8 text-center">Enter a command to see the analysis</p>
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
                                            className="hover:bg-muted w-full rounded-lg border p-3 text-left transition-colors"
                                        >
                                            <div className="mb-1 flex items-center justify-between">
                                                <Badge variant="secondary" className="text-xs">
                                                    {cmd.intent.service || 'general'}
                                                </Badge>
                                                <span className="text-muted-foreground text-xs">{Math.round(cmd.confidence * 100)}%</span>
                                            </div>
                                            <p className="truncate text-sm">{cmd.originalText}</p>
                                        </button>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-muted-foreground py-8 text-center">No commands yet</p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Example Commands */}
                <Card>
                    <CardHeader>
                        <CardTitle>Example Commands</CardTitle>
                        <CardDescription>Try these commands to see how the NLP engine interprets them</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Tabs value={activeTab} onValueChange={setActiveTab}>
                            <TabsList>
                                {exampleCommands.map((cat) => (
                                    <TabsTrigger key={cat.category} value={cat.category}>
                                        {cat.category}
                                    </TabsTrigger>
                                ))}
                            </TabsList>
                            {exampleCommands.map((cat) => (
                                <TabsContent key={cat.category} value={cat.category}>
                                    <div className="grid gap-2">
                                        {cat.commands.map((cmd, idx) => (
                                            <button
                                                key={idx}
                                                onClick={() => {
                                                    // Simulate typing the command
                                                    const input = document.querySelector(
                                                        'input[placeholder*="Type a natural language command"]',
                                                    ) as HTMLInputElement;
                                                    if (input) {
                                                        input.value = cmd;
                                                        input.dispatchEvent(new Event('input', { bubbles: true }));
                                                    }
                                                }}
                                                className="hover:bg-muted rounded-lg border p-3 text-left transition-colors"
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
