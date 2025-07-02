<?php

namespace App\Services;

use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NaturalLanguageService
{
    private array $commands = [
        'todoist' => [
            'patterns' => [
                '/(?:affiche|montre|liste).*(t(?:a|â)ches?|todo).*(aujourd\'?hui|du jour)/i' => 'getTodayTasks',
                '/(?:affiche|montre|liste).*(t(?:a|â)ches?|todo).*(demain|de demain)/i' => 'getTomorrowTasks',
                '/(?:affiche|montre|liste).*(t(?:a|â)ches?|todo).*(semaine|de la semaine)/i' => 'getWeekTasks',
                '/(?:affiche|montre|liste).*(projets?|projet)/i' => 'getProjects',
                '/(?:ajoute|cr[ée]e).*(t(?:a|â)che|todo).*"([^"]+)"/i' => 'createTask',
            ],
        ],
        'notion' => [
            'patterns' => [
                '/(?:affiche|montre|liste).*(pages?|notion)/i' => 'getPages',
                '/(?:affiche|montre|ouvre).*(page).*"([^"]+)"/i' => 'getPageByName',
                '/(?:affiche|montre|liste).*(bases? de donn[ée]es?|databases?)/i' => 'getDatabases',
                '/recherche.*"([^"]+)".*(notion|dans notion)/i' => 'searchNotion',
            ],
        ],
        'claude' => [
            'patterns' => [
                '/.*/' => 'chat',
            ],
        ],
    ];

    private NotionService $notionService;

    private McpAuthService $mcpAuthService;

    public function __construct(NotionService $notionService, McpAuthService $mcpAuthService)
    {
        $this->notionService = $notionService;
        $this->mcpAuthService = $mcpAuthService;
    }

    /**
     * @return array{success: bool, message: string, type?: string, data?: array<string, mixed>, suggestions?: array<string, string[]>, requiresIntegration?: bool, integrationType?: string}
     */
    public function processCommand(string $command, int $userId): array
    {
        $command = trim($command);

        // First, try specific service commands (except Claude)
        foreach ($this->commands as $service => $config) {
            if ($service === 'claude') {
                continue; // Skip Claude, it's our fallback
            }

            foreach ($config['patterns'] as $pattern => $method) {
                if (preg_match($pattern, $command, $matches)) {
                    return $this->executeCommand($service, $method, $matches, $userId);
                }
            }
        }

        // If no specific command matched, use Claude as fallback
        // We don't need an integration for Claude since it's handled by MCP Server
        return $this->executeClaudeCommand($command, $userId);
    }

    private function executeClaudeCommand(string $command, int $userId): array
    {
        try {
            $mcpServerUrl = config('services.mcp.server_url');
            $mcpToken = $this->mcpAuthService->getAccessToken();

            if (! $mcpToken) {
                throw new \Exception('Impossible de s\'authentifier auprès du serveur MCP');
            }

            $payload = [
                'message' => $command,
                'model' => 'claude-3-opus-20240229',
                'conversation_id' => session()->getId(),
                'max_tokens' => 4096,
                'temperature' => 0.7,
                'stream' => false,
            ];

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer '.$mcpToken,
                'Content-Type' => 'application/json',
            ])->post($mcpServerUrl.'/claude/chat', $payload);

            if (! $response->successful()) {
                Log::error('Claude API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $mcpServerUrl.'/claude/chat',
                    'payload' => $payload,
                ]);
                throw new \Exception('Erreur lors de la communication avec Claude via le serveur MCP. Status: '.$response->status());
            }

            $result = $response->json();

            return [
                'success' => true,
                'type' => 'claude_response',
                'data' => [
                    'response' => $result,
                    'conversation_id' => $payload['conversation_id'],
                ],
                'message' => $result['content'] ?? 'Réponse reçue de Claude',
            ];
        } catch (\Exception $e) {
            Log::error('Claude command error', [
                'command' => $command,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la communication avec Claude',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function executeCommand(string $service, string $method, array $matches, int $userId): array
    {
        try {
            $integration = IntegrationAccount::where('user_id', $userId)
                ->where('type', IntegrationType::from(strtolower($service)))
                ->where('status', 'active')
                ->first();

            if (! $integration) {
                return [
                    'success' => false,
                    'message' => "Vous devez d'abord connecter votre compte $service",
                    'requiresIntegration' => true,
                    'integrationType' => $service,
                ];
            }

            $methodName = $service.ucfirst($method);
            if (! method_exists($this, $methodName)) {
                throw new \Exception("Méthode non implémentée : $methodName");
            }

            return $this->$methodName($integration, $matches);
        } catch (\Exception $e) {
            Log::error('Natural language command error', [
                'command' => $method,
                'service' => $service,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'exécution de la commande',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function todoistGetTodayTasks(IntegrationAccount $integration, array $matches): array
    {
        $mcpServerUrl = config('services.mcp.server_url');
        $mcpToken = $this->mcpAuthService->getAccessToken();

        if (! $mcpToken) {
            throw new \Exception('Impossible de s\'authentifier auprès du serveur MCP');
        }

        $response = Http::timeout(10)->withHeaders([
            'Authorization' => 'Bearer '.$mcpToken,
            'X-Todoist-Token' => $integration->access_token,
            'Content-Type' => 'application/json',
        ])->get($mcpServerUrl.'/todoist/tasks', [
            'filter' => 'today | overdue',
        ]);

        if (! $response->successful()) {
            Log::error('Todoist API call failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $mcpServerUrl,
            ]);
            throw new \Exception('Erreur lors de la récupération des tâches depuis le serveur MCP. Status: '.$response->status());
        }

        $tasks = $response->json();

        return [
            'success' => true,
            'type' => 'todoist_tasks',
            'data' => [
                'tasks' => $tasks,
                'count' => count($tasks),
                'filter' => 'today',
            ],
            'message' => count($tasks) > 0
                ? 'Voici vos '.count($tasks)." tâches pour aujourd'hui"
                : "Vous n'avez aucune tâche pour aujourd'hui",
        ];
    }

    private function todoistGetTomorrowTasks(IntegrationAccount $integration, array $matches): array
    {
        $mcpServerUrl = config('services.mcp.server_url');
        $mcpToken = $this->mcpAuthService->getAccessToken();

        if (! $mcpToken) {
            throw new \Exception('Impossible de s\'authentifier auprès du serveur MCP');
        }

        $response = Http::timeout(10)->withHeaders([
            'Authorization' => 'Bearer '.$mcpToken,
            'X-Todoist-Token' => $integration->access_token,
            'Content-Type' => 'application/json',
        ])->get($mcpServerUrl.'/todoist/tasks', [
            'filter' => 'tomorrow',
        ]);

        if (! $response->successful()) {
            Log::error('Todoist API call failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $mcpServerUrl,
            ]);
            throw new \Exception('Erreur lors de la récupération des tâches depuis le serveur MCP. Status: '.$response->status());
        }

        $tasks = $response->json();

        return [
            'success' => true,
            'type' => 'todoist_tasks',
            'data' => [
                'tasks' => $tasks,
                'count' => count($tasks),
                'filter' => 'tomorrow',
            ],
            'message' => count($tasks) > 0
                ? 'Voici vos '.count($tasks).' tâches pour demain'
                : "Vous n'avez aucune tâche prévue pour demain",
        ];
    }

    private function todoistGetWeekTasks(IntegrationAccount $integration, array $matches): array
    {
        $mcpServerUrl = config('services.mcp.server_url');
        $mcpToken = $this->mcpAuthService->getAccessToken();

        if (! $mcpToken) {
            throw new \Exception('Impossible de s\'authentifier auprès du serveur MCP');
        }

        $response = Http::timeout(10)->withHeaders([
            'Authorization' => 'Bearer '.$mcpToken,
            'X-Todoist-Token' => $integration->access_token,
            'Content-Type' => 'application/json',
        ])->get($mcpServerUrl.'/todoist/tasks', [
            'filter' => '7 days',
        ]);

        if (! $response->successful()) {
            Log::error('Todoist API call failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $mcpServerUrl,
            ]);
            throw new \Exception('Erreur lors de la récupération des tâches depuis le serveur MCP. Status: '.$response->status());
        }

        $tasks = $response->json();

        return [
            'success' => true,
            'type' => 'todoist_tasks',
            'data' => [
                'tasks' => $tasks,
                'count' => count($tasks),
                'filter' => 'week',
            ],
            'message' => count($tasks) > 0
                ? 'Voici vos '.count($tasks).' tâches pour les 7 prochains jours'
                : "Vous n'avez aucune tâche prévue pour la semaine",
        ];
    }

    private function todoistGetProjects(IntegrationAccount $integration, array $matches): array
    {
        $mcpServerUrl = config('services.mcp.server_url');
        $mcpToken = $this->mcpAuthService->getAccessToken();

        if (! $mcpToken) {
            throw new \Exception('Impossible de s\'authentifier auprès du serveur MCP');
        }

        $response = Http::timeout(10)->withHeaders([
            'Authorization' => 'Bearer '.$mcpToken,
            'X-Todoist-Token' => $integration->access_token,
            'Content-Type' => 'application/json',
        ])->get($mcpServerUrl.'/todoist/projects');

        if (! $response->successful()) {
            Log::error('Todoist API call failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $mcpServerUrl,
            ]);
            throw new \Exception('Erreur lors de la récupération des projets depuis le serveur MCP. Status: '.$response->status());
        }

        $projects = $response->json();

        return [
            'success' => true,
            'type' => 'todoist_projects',
            'data' => [
                'projects' => $projects,
                'count' => count($projects),
            ],
            'message' => 'Voici vos '.count($projects).' projets Todoist',
        ];
    }

    private function todoistCreateTask(IntegrationAccount $integration, array $matches): array
    {
        $taskContent = (string) ($matches[1] ?? '');

        if (empty($taskContent)) {
            return [
                'success' => false,
                'message' => 'Le contenu de la tâche ne peut pas être vide',
            ];
        }

        $mcpServerUrl = config('services.mcp.server_url');
        $mcpToken = $this->mcpAuthService->getAccessToken();

        if (! $mcpToken) {
            throw new \Exception('Impossible de s\'authentifier auprès du serveur MCP');
        }

        $response = Http::timeout(10)->withHeaders([
            'Authorization' => 'Bearer '.$mcpToken,
            'X-Todoist-Token' => $integration->access_token,
            'Content-Type' => 'application/json',
        ])->post($mcpServerUrl.'/todoist/tasks', [
            'content' => $taskContent,
            'due_string' => 'today',
        ]);

        if (! $response->successful()) {
            Log::error('Todoist API call failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $mcpServerUrl,
            ]);
            throw new \Exception('Erreur lors de la création de la tâche via le serveur MCP. Status: '.$response->status());
        }

        $task = $response->json();

        return [
            'success' => true,
            'type' => 'todoist_task_created',
            'data' => [
                'task' => $task,
            ],
            'message' => "La tâche \"$taskContent\" a été créée avec succès",
        ];
    }

    private function notionGetPages(IntegrationAccount $integration, array $matches): array
    {
        $pages = $this->notionService->fetchNotionPages($integration);

        return [
            'success' => true,
            'type' => 'notion_pages',
            'data' => [
                'pages' => $pages,
                'count' => $this->countPages($pages),
            ],
            'message' => 'Voici vos pages Notion',
        ];
    }

    private function notionGetPageByName(IntegrationAccount $integration, array $matches): array
    {
        $pageName = (string) ($matches[1] ?? '');

        if (empty($pageName)) {
            return [
                'success' => false,
                'message' => 'Veuillez spécifier le nom de la page',
            ];
        }

        $pages = $this->notionService->fetchNotionPages($integration);
        $foundPage = $this->findPageByName($pages, $pageName);

        if (! $foundPage) {
            return [
                'success' => false,
                'message' => "Aucune page trouvée avec le nom \"$pageName\"",
            ];
        }

        $pageContent = $this->notionService->fetchNotionPage($foundPage['id'], $integration);

        return [
            'success' => true,
            'type' => 'notion_page',
            'data' => [
                'page' => $pageContent,
            ],
            'message' => "Voici la page \"$pageName\"",
        ];
    }

    private function notionGetDatabases(IntegrationAccount $integration, array $matches): array
    {
        $databases = $this->notionService->fetchNotionDatabases($integration);

        return [
            'success' => true,
            'type' => 'notion_databases',
            'data' => [
                'databases' => $databases,
                'count' => count($databases),
            ],
            'message' => 'Voici vos '.count($databases).' bases de données Notion',
        ];
    }

    private function notionSearchNotion(IntegrationAccount $integration, array $matches): array
    {
        $searchTerm = (string) ($matches[1] ?? '');

        if (empty($searchTerm)) {
            return [
                'success' => false,
                'message' => 'Veuillez spécifier un terme de recherche',
            ];
        }

        $mcpServerUrl = config('services.mcp.server_url');

        $response = Http::timeout(10)->withHeaders([
            'Authorization' => 'Bearer '.$integration->access_token,
            'Content-Type' => 'application/json',
        ])->post($mcpServerUrl.'/notion/search', [
            'query' => $searchTerm,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Erreur lors de la recherche via le serveur MCP');
        }

        $results = $response->json();

        return [
            'success' => true,
            'type' => 'notion_search',
            'data' => [
                'results' => $results,
                'query' => $searchTerm,
                'count' => is_array($results) ? count($results) : 0,
            ],
            'message' => is_array($results) && count($results) > 0
                ? "J'ai trouvé ".count($results)." résultats pour \"{$searchTerm}\""
                : "Aucun résultat trouvé pour \"{$searchTerm}\"",
        ];
    }

    /**
     * @return array<string, string[]>
     */
    private function getSuggestions(): array
    {
        return [
            'Todoist' => [
                'Affiche mes tâches du jour',
                'Montre mes tâches de demain',
                'Liste mes projets',
                'Ajoute une tâche "Faire les courses"',
            ],
            'Notion' => [
                'Affiche mes pages',
                'Montre la page "Mes notes"',
                'Liste mes bases de données',
                'Recherche "réunion" dans Notion',
            ],
        ];
    }

    /**
     * @param array<int, array{title?: string, children?: array}> $pages
     */
    private function countPages(array $pages): int
    {
        $count = 0;
        foreach ($pages as $page) {
            $count++;
            if (isset($page['children'])) {
                $count += $this->countPages($page['children']);
            }
        }

        return $count;
    }

    /**
     * @param array<int, array{title?: string, children?: array}> $pages
     * @return array{title?: string, children?: array}|null
     */
    private function findPageByName(array $pages, string $name): ?array
    {
        foreach ($pages as $page) {
            if (isset($page['title']) && stripos($page['title'], $name) !== false) {
                return $page;
            }
            if (isset($page['children'])) {
                $found = $this->findPageByName($page['children'], $name);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
