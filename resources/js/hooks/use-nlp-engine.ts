import { useToast } from '@/hooks/ui/use-toast';
import { Context, nlpEngine, ParsedCommand } from '@/lib/nlp';
import { router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

interface UseNLPEngineOptions {
    onCommandParsed?: (command: ParsedCommand) => void;
    onExecute?: (command: ParsedCommand) => Promise<void>;
}

export function useNLPEngine(options?: UseNLPEngineOptions) {
    const [isProcessing, setIsProcessing] = useState(false);
    const [lastCommand, setLastCommand] = useState<ParsedCommand | null>(null);
    const [context, setContext] = useState<Context>({});
    const { toast } = useToast();

    const parseCommand = useCallback(
        (text: string): ParsedCommand | null => {
            const command = nlpEngine.parse(text, context);

            if (command) {
                setLastCommand(command);
                options?.onCommandParsed?.(command);
            }

            return command;
        },
        [context, options],
    );

    const executeCommand = useCallback(
        async (command: ParsedCommand) => {
            if (!nlpEngine.canExecute(command)) {
                toast.error('Command unclear', 'Please be more specific or choose from the suggestions.');
                return;
            }

            setIsProcessing(true);

            try {
                // Custom execution handler
                if (options?.onExecute) {
                    await options.onExecute(command);
                    return;
                }

                // Default execution based on intent and service
                const { intent } = command;

                switch (intent.service) {
                    case 'todoist':
                        await executeTodoistCommand(command);
                        break;
                    case 'notion':
                        await executeNotionCommand(command);
                        break;
                    case 'jira':
                        await executeJiraCommand(command);
                        break;
                    default:
                        toast.error('Service not supported', `Commands for ${intent.service || 'this service'} are not yet implemented.`);
                }

                // Update context with successful command
                setContext((prev) => ({
                    ...prev,
                    previousCommands: [...(prev.previousCommands || []), command].slice(-5),
                    currentService: intent.service,
                }));
            } catch (error) {
                console.error('Command execution failed:', error);
                toast.error('Command failed', error instanceof Error ? error.message : 'An error occurred while executing the command.');
            } finally {
                setIsProcessing(false);
            }
        },
        [options, toast],
    );

    const executeTodoistCommand = async (command: ParsedCommand) => {
        const { intent, params } = command;

        switch (intent.intent) {
            case 'create_task':
                // Navigate to Todoist page with pre-filled create modal
                router.visit('/todoist', {
                    data: {
                        action: 'create',
                        content: params?.content as string,
                        priority: params?.priority as string,
                        date: params?.date as string,
                    },
                });
                break;

            case 'list_tasks':
                // Navigate to Todoist page with filters
                router.visit('/todoist', {
                    data: {
                        filter: (params?.date as string) || 'today',
                    },
                });
                break;

            case 'generate_planning':
                // Navigate to daily planning page
                router.visit('/daily-planning');
                break;

            default:
                throw new Error(`Unknown Todoist intent: ${intent.intent}`);
        }
    };

    const executeNotionCommand = async (command: ParsedCommand) => {
        const { intent, params } = command;

        switch (intent.intent) {
            case 'search_notion':
                // Navigate to Notion page with search query
                router.visit('/notion', {
                    data: {
                        search: params?.content as string,
                    },
                });
                break;

            case 'create_page':
                // Navigate to Notion page with create action
                router.visit('/notion', {
                    data: {
                        action: 'create',
                        title: params?.content as string,
                    },
                });
                break;

            default:
                throw new Error(`Unknown Notion intent: ${intent.intent}`);
        }
    };

    const executeJiraCommand = async (command: ParsedCommand) => {
        const { intent, params } = command;

        switch (intent.intent) {
            case 'create_issue':
                // Navigate to JIRA page with create action
                router.visit('/jira', {
                    data: {
                        action: 'create',
                        summary: params?.content as string,
                        project: params?.project as string,
                    },
                });
                break;

            case 'show_sprint':
                // Navigate to JIRA page showing current sprint
                router.visit('/jira', {
                    data: {
                        view: 'sprint',
                    },
                });
                break;

            default:
                throw new Error(`Unknown JIRA intent: ${intent.intent}`);
        }
    };

    const processText = useCallback(
        async (text: string) => {
            const command = parseCommand(text);

            if (command && nlpEngine.canExecute(command)) {
                await executeCommand(command);
            }

            return command;
        },
        [parseCommand, executeCommand],
    );

    const clearContext = useCallback(() => {
        setContext({});
        setLastCommand(null);
    }, []);

    return {
        parseCommand,
        executeCommand,
        processText,
        clearContext,
        isProcessing,
        lastCommand,
        context,
    };
}
