import { MarkdownRenderer } from '@/components/ai/canvas/MarkdownRenderer';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { CheckCircle, History, Lightbulb, Loader2, MessageSquare, Send, XCircle } from 'lucide-react';
import React, { useEffect, useRef, useState } from 'react';

interface Task {
    id: string;
    content: string;
    due_string?: string;
    priority?: number;
}

interface Project {
    id: string;
    name: string;
    color?: string;
}

interface NotionPage {
    id: string;
    name: string;
    icon?: string;
    children?: NotionPage[];
}

interface CommandSuggestions {
    [service: string]: string[];
}

interface CommandResult {
    success: boolean;
    message: string;
    type?: string;
    data?: {
        tasks?: Task[];
        projects?: Project[];
        pages?: NotionPage[];
        count?: number;
        [key: string]: unknown;
    };
    suggestions?: CommandSuggestions;
    requiresIntegration?: boolean;
    integrationType?: string;
}

interface CommandHistory {
    command: string;
    result: CommandResult;
    timestamp: string;
}

export default function NaturalLanguage() {
    const [command, setCommand] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState<CommandResult | null>(null);
    const [suggestions, setSuggestions] = useState<CommandSuggestions>({});
    const [history, setHistory] = useState<CommandHistory[]>([]);
    const [showSuggestions, setShowSuggestions] = useState(true);
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        loadSuggestions();
        loadHistory();
        inputRef.current?.focus();
    }, []);

    const loadSuggestions = async () => {
        try {
            const response = await fetch('/api/natural-language/suggestions');
            const data = await response.json();
            if (data.success) {
                setSuggestions(data.suggestions);
            }
        } catch (error) {
            console.error('Error loading suggestions:', error);
        }
    };

    const loadHistory = async () => {
        try {
            const response = await fetch('/api/natural-language/history');
            const data = await response.json();
            if (data.success) {
                setHistory(data.history.slice(0, 10));
            }
        } catch (error) {
            console.error('Error loading history:', error);
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!command.trim() || isLoading) return;

        setIsLoading(true);
        setResult(null);
        setShowSuggestions(false);

        try {
            const response = await fetch('/api/natural-language/command', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ command }),
            });

            const data = await response.json();
            setResult(data);

            if (data.success) {
                setCommand('');
                loadHistory();
            }

            if (data.requiresIntegration) {
                setTimeout(() => {
                    router.visit('/integrations/manager');
                }, 3000);
            }
        } catch {
            setResult({
                success: false,
                message: 'An error occurred while processing your command',
            });
        } finally {
            setIsLoading(false);
            inputRef.current?.focus();
        }
    };

    const handleSuggestionClick = (suggestion: string) => {
        setCommand(suggestion);
        setShowSuggestions(false);
        inputRef.current?.focus();
    };

    const renderResult = () => {
        if (!result) return null;

        return (
            <Card className="mt-6">
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        {result.success ? <CheckCircle className="h-5 w-5 text-green-500" /> : <XCircle className="h-5 w-5 text-red-500" />}
                        Résultat
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p className="mb-4">{result.message}</p>

                    {result.requiresIntegration && (
                        <Alert className="mb-4">
                            <AlertDescription>Redirection vers les intégrations dans 3 secondes...</AlertDescription>
                        </Alert>
                    )}

                    {result.type === 'todoist_tasks' && result.data && (
                        <div className="space-y-2">
                            <h4 className="font-semibold">Tâches ({result.data.count})</h4>
                            {result.data.tasks.map((task, index) => (
                                <div key={index} className="rounded bg-gray-50 p-3 dark:bg-gray-800">
                                    <div className="flex items-center justify-between">
                                        <span>{task.content}</span>
                                        {task.priority && <Badge variant={task.priority === 4 ? 'secondary' : 'destructive'}>P{task.priority}</Badge>}
                                    </div>
                                    {task.due && (
                                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            Échéance: {new Date(task.due.date).toLocaleDateString()}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}

                    {result.type === 'todoist_projects' && result.data && (
                        <div className="space-y-2">
                            <h4 className="font-semibold">Projets ({result.data.count})</h4>
                            {result.data.projects.map((project, index) => (
                                <div key={index} className="rounded bg-gray-50 p-3 dark:bg-gray-800">
                                    <div className="flex items-center gap-2">
                                        <div className="h-3 w-3 rounded-full" style={{ backgroundColor: project.color }} />
                                        <span>{project.name}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}

                    {result.type === 'notion_pages' && result.data && (
                        <div className="space-y-2">
                            <h4 className="font-semibold">Pages Notion ({result.data.count})</h4>
                            <div className="max-h-64 overflow-y-auto">{renderNotionPages(result.data.pages)}</div>
                        </div>
                    )}

                    {result.type === 'claude_response' && result.data && (
                        <div className="space-y-4">
                            <div className="flex items-center gap-2">
                                <MessageSquare className="h-5 w-5 text-blue-500" />
                                <h4 className="font-semibold">Réponse de Claude</h4>
                            </div>
                            <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                                <MarkdownRenderer
                                    content={result.data.response?.content || result.message}
                                    className="text-gray-800 dark:text-gray-200"
                                />
                            </div>
                            {result.data.response?.usage && (
                                <div className="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span>Tokens d'entrée: {result.data.response.usage.input_tokens}</span>
                                    <span>Tokens de sortie: {result.data.response.usage.output_tokens}</span>
                                    <span>Modèle: {result.data.response.model}</span>
                                </div>
                            )}
                        </div>
                    )}

                    {!result.success && result.suggestions && (
                        <div className="mt-4">
                            <h4 className="mb-2 font-semibold">Suggestions:</h4>
                            <div className="grid gap-2">
                                {Object.entries(result.suggestions).map(([service, commands]) => (
                                    <div key={service}>
                                        <h5 className="text-sm font-medium">{service}</h5>
                                        <div className="mt-1 flex flex-wrap gap-1">
                                            {commands.map((cmd: string, index: number) => (
                                                <Button key={index} variant="outline" size="sm" onClick={() => handleSuggestionClick(cmd)}>
                                                    {cmd}
                                                </Button>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </CardContent>
            </Card>
        );
    };

    const renderNotionPages = (pages: NotionPage[], level = 0) => {
        return pages.map((page, index) => (
            <div key={index} style={{ marginLeft: level * 20 }}>
                <div className="mb-1 rounded bg-gray-50 p-2 dark:bg-gray-800">
                    <span>{page.title}</span>
                </div>
                {page.children && renderNotionPages(page.children, level + 1)}
            </div>
        ));
    };

    return (
        <AppLayout>
            <Head title="Natural Language Commands" />

            <div className="container mx-auto px-6 py-8">
                <div className="mx-auto max-w-4xl">
                    <div className="mb-8">
                        <h1 className="mb-2 text-3xl font-bold">Natural Language Commands</h1>
                        <p className="text-gray-600 dark:text-gray-400">Interact with your integrations using simple natural language commands</p>
                    </div>

                    <form onSubmit={handleSubmit} className="mb-6">
                        <div className="flex gap-2">
                            <Input
                                ref={inputRef}
                                type="text"
                                placeholder="Ex: Show my tasks for today, List my Notion pages..."
                                value={command}
                                onChange={(e) => setCommand(e.target.value)}
                                disabled={isLoading}
                                className="flex-1"
                            />
                            <Button type="submit" disabled={isLoading || !command.trim()}>
                                {isLoading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Send className="h-4 w-4" />}
                            </Button>
                        </div>
                    </form>

                    {renderResult()}

                    {showSuggestions && Object.keys(suggestions).length > 0 && (
                        <Card className="mt-6">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Lightbulb className="h-5 w-5" />
                                    Exemples de commandes
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-4">
                                    {Object.entries(suggestions).map(([service, commands]) => (
                                        <div key={service}>
                                            <h4 className="mb-2 font-semibold">{service}</h4>
                                            <div className="grid gap-2 sm:grid-cols-2">
                                                {commands.map((cmd: string, index: number) => (
                                                    <Button
                                                        key={index}
                                                        variant="outline"
                                                        size="sm"
                                                        className="h-auto justify-start p-3 text-left"
                                                        onClick={() => handleSuggestionClick(cmd)}
                                                    >
                                                        {cmd}
                                                    </Button>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {history.length > 0 && (
                        <Card className="mt-6">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <History className="h-5 w-5" />
                                    Historique des commandes
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    {history.map((item, index) => (
                                        <div key={index} className="rounded bg-gray-50 p-3 dark:bg-gray-800">
                                            <div className="mb-1 flex items-center justify-between">
                                                <span className="font-medium">{item.command}</span>
                                                <div className="flex items-center gap-2">
                                                    {item.result.success ? (
                                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                                    ) : (
                                                        <XCircle className="h-4 w-4 text-red-500" />
                                                    )}
                                                    <span className="text-sm text-gray-500">{new Date(item.timestamp).toLocaleString()}</span>
                                                </div>
                                            </div>
                                            <p className="text-sm text-gray-600 dark:text-gray-400">{item.result.message}</p>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
