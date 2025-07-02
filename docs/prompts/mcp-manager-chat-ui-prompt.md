# Prompt MCP Manager - Interface Chat Claude

## Contexte

Le MCP Manager (Laravel + React) doit avoir une nouvelle interface de chat qui communique avec l'endpoint enrichi `/llm/chat/with-tools` du MCP Server. Cette interface doit s'intégrer parfaitement dans l'architecture existante sans casser les fonctionnalités actuelles.

## Objectif

Créer une page de chat style Claude.ai avec :
1. Navigation dans la sidebar existante
2. Interface split-view (chat à gauche, canvas à droite)
3. Support des réponses enrichies avec données des services
4. Design Atlassian cohérent avec l'existant
5. Utilisation de Tailwind CSS 4

## Implémentation

### 1. Route et Navigation

**Ajouter** dans `routes/web.php` :

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // ... routes existantes ...
    
    // Nouvelle route pour le chat
    Route::get('/chat', function () {
        return Inertia::render('chat/index');
    })->name('chat.index');
    
    // API route pour l'historique (optionnel)
    Route::get('/api/chat/history', [ChatController::class, 'history'])
        ->name('chat.history');
});
```

**Créer** `app/Http/Controllers/ChatController.php` :

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('chat/index', [
            'mcpServerUrl' => config('services.mcp.server_url'),
            'availableModels' => [
                ['value' => 'gpt-4', 'label' => 'GPT-4'],
                ['value' => 'gpt-3.5-turbo', 'label' => 'GPT-3.5 Turbo'],
                ['value' => 'claude-3-opus', 'label' => 'Claude 3 Opus'],
                ['value' => 'mistral-large', 'label' => 'Mistral Large'],
            ]
        ]);
    }
    
    public function history(Request $request)
    {
        // Optionnel : retourner l'historique des conversations
        return response()->json([
            'conversations' => []
        ]);
    }
}
```

### 2. Mise à jour de la Navigation

**Modifier** `app/Http/Middleware/HandleInertiaRequests.php` :

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        // ... données existantes ...
        'navigation' => [
            // ... items existants ...
            [
                'name' => 'Chat AI',
                'href' => route('chat.index'),
                'icon' => 'MessageSquare',
                'current' => $request->routeIs('chat.*'),
                'badge' => 'Beta', // Optionnel
            ],
            // ... autres items ...
        ],
    ]);
}
```

### 3. Page Chat Principal

**Créer** `resources/js/pages/chat/index.tsx` :

```typescript
import { useState, useRef, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app';
import { ChatPanel } from '@/components/chat/ChatPanel';
import { CanvasPanel } from '@/components/chat/CanvasPanel';
import { useChatWithTools } from '@/hooks/use-chat-with-tools';
import { cn } from '@/lib/utils';

interface Props {
    mcpServerUrl: string;
    availableModels: Array<{ value: string; label: string }>;
}

export default function ChatPage({ mcpServerUrl, availableModels }: Props) {
    const [canvasContent, setCanvasContent] = useState<any>(null);
    const [selectedModel, setSelectedModel] = useState('gpt-4');
    const [showCanvas, setShowCanvas] = useState(true);
    
    const {
        messages,
        sendMessage,
        isLoading,
        currentToolCalls,
        regenerateMessage,
        clearChat
    } = useChatWithTools(mcpServerUrl);

    // Mettre à jour le canvas quand un message avec outils est sélectionné
    const handleMessageSelect = (message: any) => {
        if (message.toolResults) {
            setCanvasContent({
                type: 'tool-results',
                data: message.toolResults,
                message: message.content
            });
        } else if (message.content.includes('```')) {
            setCanvasContent({
                type: 'code',
                data: message.content
            });
        }
    };

    return (
        <AppLayout>
            <Head title="Chat AI" />
            
            <div className="flex h-[calc(100vh-4rem)] bg-gray-50 dark:bg-gray-900">
                {/* Panel Gauche - Chat */}
                <div className={cn(
                    "flex-1 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700",
                    "transition-all duration-300",
                    showCanvas ? "w-1/2" : "w-full"
                )}>
                    <ChatPanel
                        messages={messages}
                        onSendMessage={sendMessage}
                        onMessageSelect={handleMessageSelect}
                        onRegenerateMessage={regenerateMessage}
                        onClearChat={clearChat}
                        isLoading={isLoading}
                        currentToolCalls={currentToolCalls}
                        selectedModel={selectedModel}
                        onModelChange={setSelectedModel}
                        availableModels={availableModels}
                    />
                </div>

                {/* Panel Droit - Canvas */}
                {showCanvas && (
                    <div className="w-1/2 bg-white dark:bg-gray-800">
                        <CanvasPanel
                            content={canvasContent}
                            onClose={() => setShowCanvas(false)}
                        />
                    </div>
                )}

                {/* Bouton Toggle Canvas */}
                {!showCanvas && (
                    <button
                        onClick={() => setShowCanvas(true)}
                        className="fixed right-4 bottom-4 p-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-colors"
                        aria-label="Afficher le canvas"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                )}
            </div>
        </AppLayout>
    );
}
```

### 4. Hook pour la Communication avec MCP Server

**Créer** `resources/js/hooks/use-chat-with-tools.ts` :

```typescript
import { useState, useCallback, useRef } from 'react';
import { usePage } from '@inertiajs/react';
import axios from 'axios';

interface Message {
    id: string;
    role: 'user' | 'assistant' | 'system';
    content: string;
    timestamp: Date;
    toolCalls?: any[];
    toolResults?: any[];
    status?: 'sending' | 'sent' | 'error';
}

export function useChatWithTools(mcpServerUrl: string) {
    const { props } = usePage();
    const [messages, setMessages] = useState<Message[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [currentToolCalls, setCurrentToolCalls] = useState<any[]>([]);
    const abortControllerRef = useRef<AbortController | null>(null);

    // Récupérer le token d'authentification depuis les cookies ou localStorage
    const getAuthToken = () => {
        // Utiliser le token du MCP Manager qui proxy vers MCP Server
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    };

    const sendMessage = useCallback(async (content: string, model: string = 'gpt-4') => {
        // Annuler toute requête en cours
        if (abortControllerRef.current) {
            abortControllerRef.current.abort();
        }

        const newMessage: Message = {
            id: Date.now().toString(),
            role: 'user',
            content,
            timestamp: new Date(),
            status: 'sending'
        };

        setMessages(prev => [...prev, newMessage]);
        setIsLoading(true);
        setCurrentToolCalls([]);

        try {
            abortControllerRef.current = new AbortController();

            // Construire la requête
            const requestBody = {
                messages: [...messages, { role: 'user', content }].map(m => ({
                    role: m.role,
                    content: m.content
                })),
                model,
                temperature: 0.7,
                max_tokens: 4000,
                enable_tools: true
            };

            // Appeler l'endpoint enrichi
            const response = await axios.post(
                `${mcpServerUrl}/llm/chat/with-tools`,
                requestBody,
                {
                    headers: {
                        'Authorization': `Bearer ${getAuthToken()}`,
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getAuthToken()
                    },
                    signal: abortControllerRef.current.signal,
                    onDownloadProgress: (progressEvent) => {
                        // Gérer le streaming si activé
                        if (progressEvent.event?.target?.responseText) {
                            // Parser les chunks SSE
                            handleStreamingResponse(progressEvent.event.target.responseText);
                        }
                    }
                }
            );

            // Message de réponse
            const assistantMessage: Message = {
                id: (Date.now() + 1).toString(),
                role: 'assistant',
                content: response.data.content,
                timestamp: new Date(),
                toolCalls: response.data.tool_calls,
                toolResults: response.data.tool_results,
                status: 'sent'
            };

            setMessages(prev => [...prev, assistantMessage]);

            // Mettre à jour le statut du message utilisateur
            setMessages(prev => prev.map(m => 
                m.id === newMessage.id ? { ...m, status: 'sent' } : m
            ));

        } catch (error) {
            if (axios.isCancel(error)) {
                console.log('Request cancelled');
                return;
            }

            console.error('Chat error:', error);
            
            // Message d'erreur
            const errorMessage: Message = {
                id: (Date.now() + 2).toString(),
                role: 'assistant',
                content: 'Désolé, une erreur est survenue. Veuillez réessayer.',
                timestamp: new Date(),
                status: 'error'
            };

            setMessages(prev => [...prev, errorMessage]);

            // Mettre à jour le statut du message utilisateur
            setMessages(prev => prev.map(m => 
                m.id === newMessage.id ? { ...m, status: 'error' } : m
            ));

        } finally {
            setIsLoading(false);
            abortControllerRef.current = null;
        }
    }, [messages, mcpServerUrl]);

    const handleStreamingResponse = (responseText: string) => {
        // Parser les Server-Sent Events
        const lines = responseText.split('\n');
        
        for (const line of lines) {
            if (line.startsWith('data: ')) {
                try {
                    const data = JSON.parse(line.slice(6));
                    
                    if (data.type === 'tool_call') {
                        setCurrentToolCalls(prev => [...prev, data.tool_call]);
                    }
                } catch (e) {
                    // Ignorer les erreurs de parsing
                }
            }
        }
    };

    const regenerateMessage = useCallback(async (messageId: string) => {
        const messageIndex = messages.findIndex(m => m.id === messageId);
        if (messageIndex === -1 || messageIndex === 0) return;

        // Récupérer le message utilisateur précédent
        const userMessage = messages[messageIndex - 1];
        if (userMessage.role !== 'user') return;

        // Supprimer les messages à partir de celui à régénérer
        setMessages(prev => prev.slice(0, messageIndex - 1));

        // Renvoyer le message
        await sendMessage(userMessage.content);
    }, [messages, sendMessage]);

    const clearChat = useCallback(() => {
        setMessages([]);
        setCurrentToolCalls([]);
    }, []);

    // Quick actions prédéfinies
    const quickActions = [
        {
            label: "Mes tâches du jour",
            query: "Montre-moi mes tâches Todoist pour aujourd'hui",
            icon: "📅"
        },
        {
            label: "Erreurs Sentry",
            query: "Y a-t-il des erreurs critiques dans Sentry ?",
            icon: "🐛"
        },
        {
            label: "Backlog JIRA", 
            query: "Quel est l'état du backlog JIRA ?",
            icon: "📋"
        },
        {
            label: "Rapport quotidien",
            query: "Fais-moi un résumé de mes tâches, issues JIRA et erreurs Sentry",
            icon: "📊"
        }
    ];

    return {
        messages,
        sendMessage,
        regenerateMessage,
        clearChat,
        isLoading,
        currentToolCalls,
        quickActions
    };
}
```

### 5. Composants Chat

**Créer** `resources/js/components/chat/ChatPanel.tsx` :

```typescript
import { useState, useRef, useEffect } from 'react';
import { MessageList } from './MessageList';
import { ChatInput } from './ChatInput';
import { ChatHeader } from './ChatHeader';
import { QuickActions } from './QuickActions';
import { cn } from '@/lib/utils';

interface ChatPanelProps {
    messages: any[];
    onSendMessage: (content: string) => void;
    onMessageSelect: (message: any) => void;
    onRegenerateMessage: (messageId: string) => void;
    onClearChat: () => void;
    isLoading: boolean;
    currentToolCalls?: any[];
    selectedModel: string;
    onModelChange: (model: string) => void;
    availableModels: Array<{ value: string; label: string }>;
}

export function ChatPanel({
    messages,
    onSendMessage,
    onMessageSelect,
    onRegenerateMessage,
    onClearChat,
    isLoading,
    currentToolCalls,
    selectedModel,
    onModelChange,
    availableModels
}: ChatPanelProps) {
    const scrollRef = useRef<HTMLDivElement>(null);
    const [inputValue, setInputValue] = useState('');

    // Auto-scroll vers le bas
    useEffect(() => {
        scrollRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    const handleSend = () => {
        if (inputValue.trim() && !isLoading) {
            onSendMessage(inputValue);
            setInputValue('');
        }
    };

    // Raccourcis clavier
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) {
                handleSend();
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [inputValue]);

    return (
        <div className="flex flex-col h-full">
            {/* Header */}
            <ChatHeader
                selectedModel={selectedModel}
                onModelChange={onModelChange}
                availableModels={availableModels}
                onClearChat={onClearChat}
            />

            {/* Messages ou Quick Actions */}
            <div className="flex-1 overflow-y-auto">
                {messages.length === 0 ? (
                    <div className="p-6">
                        <h2 className="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
                            Comment puis-je vous aider ?
                        </h2>
                        <QuickActions onSelect={onSendMessage} />
                    </div>
                ) : (
                    <MessageList
                        messages={messages}
                        onMessageSelect={onMessageSelect}
                        onRegenerateMessage={onRegenerateMessage}
                        currentToolCalls={currentToolCalls}
                        isLoading={isLoading}
                    />
                )}
                <div ref={scrollRef} />
            </div>

            {/* Input */}
            <ChatInput
                value={inputValue}
                onChange={setInputValue}
                onSend={handleSend}
                disabled={isLoading}
                placeholder="Demandez-moi des informations sur vos tâches, projets ou erreurs..."
            />
        </div>
    );
}
```

**Créer** `resources/js/components/chat/MessageList.tsx` :

```typescript
import { MessageItem } from './MessageItem';
import { ToolCallIndicator } from './ToolCallIndicator';

interface MessageListProps {
    messages: any[];
    onMessageSelect: (message: any) => void;
    onRegenerateMessage: (messageId: string) => void;
    currentToolCalls?: any[];
    isLoading: boolean;
}

export function MessageList({
    messages,
    onMessageSelect,
    onRegenerateMessage,
    currentToolCalls,
    isLoading
}: MessageListProps) {
    return (
        <div className="px-6 py-4 space-y-6">
            {messages.map((message, index) => (
                <MessageItem
                    key={message.id}
                    message={message}
                    onSelect={() => onMessageSelect(message)}
                    onRegenerate={() => onRegenerateMessage(message.id)}
                    showActions={index === messages.length - 1 && message.role === 'assistant'}
                />
            ))}
            
            {/* Indicateur d'appels d'outils en cours */}
            {currentToolCalls && currentToolCalls.length > 0 && (
                <ToolCallIndicator toolCalls={currentToolCalls} />
            )}
            
            {/* Indicateur de chargement */}
            {isLoading && !currentToolCalls?.length && (
                <div className="flex items-center gap-3 text-gray-500">
                    <div className="animate-pulse flex gap-1">
                        <div className="w-2 h-2 bg-gray-400 rounded-full"></div>
                        <div className="w-2 h-2 bg-gray-400 rounded-full animation-delay-200"></div>
                        <div className="w-2 h-2 bg-gray-400 rounded-full animation-delay-400"></div>
                    </div>
                    <span className="text-sm">Claude réfléchit...</span>
                </div>
            )}
        </div>
    );
}
```

### 6. Composants Canvas

**Créer** `resources/js/components/chat/CanvasPanel.tsx` :

```typescript
import { X } from 'lucide-react';
import { ToolResultsCanvas } from './canvas/ToolResultsCanvas';
import { CodeCanvas } from './canvas/CodeCanvas';
import { MarkdownCanvas } from './canvas/MarkdownCanvas';

interface CanvasPanelProps {
    content: any;
    onClose: () => void;
}

export function CanvasPanel({ content, onClose }: CanvasPanelProps) {
    const renderContent = () => {
        if (!content) {
            return (
                <div className="flex items-center justify-center h-full text-gray-500">
                    <p>Sélectionnez un message pour voir les détails</p>
                </div>
            );
        }

        switch (content.type) {
            case 'tool-results':
                return <ToolResultsCanvas results={content.data} />;
            case 'code':
                return <CodeCanvas code={content.data} />;
            case 'markdown':
                return <MarkdownCanvas content={content.data} />;
            default:
                return <pre className="p-4">{JSON.stringify(content, null, 2)}</pre>;
        }
    };

    return (
        <div className="flex flex-col h-full">
            {/* Header */}
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Aperçu
                </h3>
                <button
                    onClick={onClose}
                    className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
                    aria-label="Fermer"
                >
                    <X className="w-5 h-5" />
                </button>
            </div>

            {/* Content */}
            <div className="flex-1 overflow-y-auto">
                {renderContent()}
            </div>
        </div>
    );
}
```

**Créer** `resources/js/components/chat/canvas/ToolResultsCanvas.tsx` :

```typescript
import { TodoistResults } from './TodoistResults';
import { JiraResults } from './JiraResults';
import { SentryResults } from './SentryResults';

interface ToolResultsCanvasProps {
    results: any[];
}

export function ToolResultsCanvas({ results }: ToolResultsCanvasProps) {
    return (
        <div className="p-6 space-y-6">
            {results.map((result, index) => (
                <div key={index} className="space-y-4">
                    <h4 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {getToolTitle(result.tool)}
                    </h4>
                    
                    {result.error ? (
                        <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                            <p className="text-red-800 dark:text-red-200">
                                Erreur : {result.error}
                            </p>
                        </div>
                    ) : (
                        renderToolResult(result.tool, result.result)
                    )}
                </div>
            ))}
        </div>
    );
}

function renderToolResult(tool: string, data: any) {
    if (tool.includes('todoist')) {
        return <TodoistResults data={data} />;
    } else if (tool.includes('jira')) {
        return <JiraResults data={data} />;
    } else if (tool.includes('sentry')) {
        return <SentryResults data={data} />;
    }
    
    return <pre className="text-sm">{JSON.stringify(data, null, 2)}</pre>;
}

function getToolTitle(tool: string): string {
    const titles: Record<string, string> = {
        'get_todoist_tasks': 'Tâches Todoist',
        'get_todoist_projects': 'Projets Todoist',
        'get_jira_issues': 'Issues JIRA',
        'get_jira_backlog': 'Backlog JIRA',
        'get_sentry_issues': 'Erreurs Sentry'
    };
    
    return titles[tool] || tool;
}
```

### 7. Configuration API Client

**Modifier** `resources/js/lib/api.ts` (ou créer) :

```typescript
import axios from 'axios';

// Configuration pour communiquer avec MCP Server
export const mcpClient = axios.create({
    baseURL: window.mcpServerUrl || 'http://localhost:9978',
    timeout: 30000,
    headers: {
        'Content-Type': 'application/json',
    }
});

// Intercepteur pour ajouter le token
mcpClient.interceptors.request.use((config) => {
    const token = localStorage.getItem('mcp_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Gestion des erreurs
mcpClient.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Rediriger vers login ou refresh token
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);
```

### 8. Styles Tailwind Additionnels

**Ajouter** dans `resources/css/app.css` :

```css
/* Animations pour le chat */
@keyframes slideInFromBottom {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-enter {
    animation: slideInFromBottom 0.3s ease-out;
}

/* Delay pour les dots de chargement */
.animation-delay-200 {
    animation-delay: 200ms;
}

.animation-delay-400 {
    animation-delay: 400ms;
}

/* Scrollbar personnalisée pour le chat */
.chat-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.chat-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.chat-scrollbar::-webkit-scrollbar-thumb {
    background: theme('colors.gray.400');
    border-radius: 3px;
}

.chat-scrollbar::-webkit-scrollbar-thumb:hover {
    background: theme('colors.gray.500');
}
```

## Points Critiques à Respecter

1. **NE PAS modifier** l'architecture existante de MCP Manager
2. **UTILISER** les composants UI existants dans `resources/js/components/ui/`
3. **RESPECTER** le système d'authentification Laravel existant
4. **MAINTENIR** la cohérence avec le design Atlassian existant
5. **UTILISER** Inertia.js pour la navigation (pas de routing React)
6. **CONSERVER** les patterns de hooks existants
7. **NE PAS créer** de nouvelles tables sans nécessité

## Configuration Requise

**Ajouter** dans `.env` :

```bash
# URL du MCP Server
MCP_SERVER_URL=http://localhost:9978
MCP_SERVER_TIMEOUT=30
```

**Ajouter** dans `config/services.php` :

```php
'mcp' => [
    'server_url' => env('MCP_SERVER_URL', 'http://localhost:9978'),
    'timeout' => env('MCP_SERVER_TIMEOUT', 30),
],
```

## Tests

**Créer** `tests/Feature/ChatTest.php` :

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ChatTest extends TestCase
{
    public function test_chat_page_requires_authentication()
    {
        $response = $this->get('/chat');
        $response->assertRedirect('/login');
    }
    
    public function test_authenticated_user_can_access_chat()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/chat');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('chat/index')
            ->has('mcpServerUrl')
            ->has('availableModels')
        );
    }
}
```

## Ordre d'Implémentation

1. Créer la route et le contrôleur
2. Mettre à jour la navigation
3. Créer la page principale et le hook
4. Implémenter les composants de base (ChatPanel, MessageList)
5. Ajouter les composants Canvas
6. Tester l'intégration avec MCP Server
7. Ajouter les fonctionnalités avancées (quick actions, régénération)

Cette approche garantit une intégration harmonieuse avec l'architecture existante de MCP Manager tout en ajoutant les nouvelles fonctionnalités de chat.