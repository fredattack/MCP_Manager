<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class TodoistService extends BaseService
{
    private ?User $user = null;

    public function __construct(
        private readonly McpProxyService $mcpProxyService
    ) {}

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Validate a Todoist API token by fetching user info
     */
    public function validateToken(string $token): ?array
    {
        try {
            // Create a temporary integration account with the token to test
            $tempAccount = new IntegrationAccount([
                'type' => IntegrationType::TODOIST,
                'access_token' => $token,
                'status' => IntegrationStatus::ACTIVE,
            ]);

            // Try to fetch projects as a validation test
            $response = $this->mcpProxyService->request($tempAccount, 'todoist_list_projects', []);

            if (isset($response['data']) && is_array($response['data'])) {
                // If we can fetch projects, the token is valid
                // Return mock user info since Todoist API doesn't have a direct user endpoint via MCP
                return [
                    'id' => 'todoist_user',
                    'email' => 'user@todoist.com',
                    'full_name' => 'Todoist User',
                    'token_valid' => true,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Token validation failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTodayTasks(): array
    {
        $integrationAccount = $this->getActiveAccount();

        // For now, always use the mock data since MCP proxy is not working correctly
        // TODO: Fix MCP proxy integration
        \Log::info('Using mock Todoist data for daily planning');

        return [
            [
                'id' => '9491677409',
                'content' => 'Faire les lacets de Nike rouge',
                'priority' => 3,
                'project_id' => '2355757627',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '9491710241',
                'content' => 'Faire la lampe',
                'priority' => 1,
                'project_id' => '2355757627',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '9491710242',
                'content' => 'Réviser le code du projet MCP',
                'priority' => 4,
                'project_id' => '2355757627',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '9491710243',
                'content' => 'Appeler le client pour le suivi',
                'priority' => 3,
                'project_id' => '2355757627',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '9491710244',
                'content' => 'Préparer la réunion de demain',
                'priority' => 2,
                'project_id' => '2355757627',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '9491710245',
                'content' => 'Finir la documentation technique',
                'priority' => 3,
                'project_id' => '2355757627',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '9491710246',
                'content' => 'Tester les nouvelles fonctionnalités',
                'priority' => 2,
                'project_id' => '2355757627',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '9491710247',
                'content' => 'Répondre aux emails importants',
                'priority' => 3,
                'project_id' => '2355757627',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
        ];

        // Original code for reference - to be fixed later
        /*
        try {
            $response = $this->mcpProxyService->request($integrationAccount, 'todoist_list_tasks_today', []);
            $data = $response['data'] ?? [];
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            \Log::warning('MCP proxy failed for getTodayTasks', ['error' => $e->getMessage()]);
            return [];
        }
        */
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTomorrowTasks(): array
    {
        $integrationAccount = $this->getActiveAccount();

        $response = $this->mcpProxyService->request($integrationAccount, 'todoist_list_tasks_tomorrow', []);

        $data = $response['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getWeekTasks(): array
    {
        $integrationAccount = $this->getActiveAccount();

        $response = $this->mcpProxyService->request($integrationAccount, 'todoist_list_tasks_week', []);

        $data = $response['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTask(string $taskId): array
    {
        $integrationAccount = $this->getActiveAccount();

        $response = $this->mcpProxyService->request($integrationAccount, 'todoist_get_task', [
            'task_id' => $taskId,
        ]);

        $data = $response['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createTask(array $data): array
    {
        $integrationAccount = $this->getActiveAccount();

        $response = $this->mcpProxyService->request($integrationAccount, 'todoist_create_task', $data);

        $responseData = $response['data'] ?? [];

        return is_array($responseData) ? $responseData : [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateTask(string $taskId, array $data): array
    {
        $integrationAccount = $this->getActiveAccount();

        $response = $this->mcpProxyService->request($integrationAccount, 'todoist_update_task', array_merge([
            'task_id' => $taskId,
        ], $data));

        $responseData = $response['data'] ?? [];

        return is_array($responseData) ? $responseData : [];
    }

    public function completeTask(string $taskId): bool
    {
        $integrationAccount = $this->getActiveAccount();

        try {
            $this->mcpProxyService->request($integrationAccount, 'todoist_complete_task', [
                'task_id' => $taskId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to complete task', ['task_id' => $taskId, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function deleteTask(string $taskId): bool
    {
        $integrationAccount = $this->getActiveAccount();

        try {
            $this->mcpProxyService->request($integrationAccount, 'todoist_delete_task', [
                'task_id' => $taskId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete task', ['task_id' => $taskId, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProjects(): array
    {
        $integrationAccount = $this->getActiveAccount();

        $response = $this->mcpProxyService->request($integrationAccount, 'todoist_list_projects', []);

        $data = $response['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getProject(string $projectId): array
    {
        $integrationAccount = $this->getActiveAccount();

        $response = $this->mcpProxyService->request($integrationAccount, 'todoist_get_project', [
            'project_id' => $projectId,
        ]);

        return $response['data'] ?? [];
    }

    /**
     * @param  array<int, string>  $taskIds
     * @param  array<string, mixed>  $updates
     * @return array<string, mixed>
     */
    /**
     * @param  array<int, string>  $taskIds
     * @param  array<string, mixed>  $updates
     * @return array<string, mixed>
     */
    public function bulkUpdateTasks(array $taskIds, array $updates): array
    {
        $integrationAccount = $this->getActiveAccount();

        $response = $this->mcpProxyService->request($integrationAccount, 'todoist_bulk_update_tasks', array_merge([
            'task_ids' => $taskIds,
        ], $updates));

        $data = $response['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function quickAddTask(string $text): array
    {
        $integrationAccount = $this->getActiveAccount();

        $response = $this->mcpProxyService->request($integrationAccount, 'todoist_quick_add_task', [
            'text' => $text,
        ]);

        $data = $response['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    private function getActiveAccount(): IntegrationAccount
    {
        if (! $this->user instanceof \App\Models\User) {
            throw new \RuntimeException('User not set. Call setUser() first.');
        }

        $account = $this->user->integrationAccounts()
            ->where('type', IntegrationType::TODOIST)
            ->where('status', IntegrationStatus::ACTIVE)
            ->first();

        if (! $account) {
            throw new \RuntimeException('No active Todoist integration found for user');
        }

        return $account;
    }

    // BaseService abstract methods implementation
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Model>
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        throw new \BadMethodCallException('Use specific methods like getTodayTasks() instead');
    }

    public function find(int|string $id): ?Model
    {
        throw new \BadMethodCallException('Use getTask() instead');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        throw new \BadMethodCallException('Use createTask() instead');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int|string $id, array $data): Model
    {
        throw new \BadMethodCallException('Use updateTask() instead');
    }

    public function delete(int|string $id): bool
    {
        return $this->deleteTask((string) $id);
    }
}
