<?php

namespace App\Services\McpServer;

use App\Enums\UserRole;
use App\Exceptions\McpServerException;
use App\Models\McpServerUser;
use App\Models\McpSyncLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class McpUserSyncService
{
    public function __construct(
        private readonly McpServerClient $client
    ) {}

    public function syncUser(User $user, string $action = 'create'): bool
    {
        $startTime = microtime(true);

        try {
            $payload = $this->buildUserPayload($user);

            $response = match ($action) {
                'create' => $this->createUser($user, $payload),
                'update' => $this->updateUser($user, $payload),
                'delete' => $this->deleteUser($user),
                default => throw new \InvalidArgumentException("Invalid action: {$action}"),
            };

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->logSync($user, $action, 'success', $payload, $response, null, $durationMs);
            $this->updateSyncStatus($user, 'synced');

            Log::channel('mcp')->info("User {$action} synced successfully", [
                'user_id' => $user->id,
                'email' => $user->email,
                'action' => $action,
            ]);

            return true;

        } catch (McpServerException $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->logSync($user, $action, 'failed', $payload ?? [], null, $e->getMessage(), $durationMs);
            $this->updateSyncStatus($user, 'error', $e->getMessage());

            Log::channel('mcp')->error("User {$action} sync failed", [
                'user_id' => $user->id,
                'email' => $user->email,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function createUser(User $user, array $payload): array
    {
        // First, check if user already exists in MCP Server by email
        try {
            $existingUsers = $this->client->get('/admin/users', ['search' => $user->email]);

            if (isset($existingUsers['users']) && count($existingUsers['users']) > 0) {
                // User already exists, find the exact match by email
                foreach ($existingUsers['users'] as $existingUser) {
                    if ($existingUser['email'] === $user->email) {
                        // User exists, just save the mapping
                        $this->saveSyncMapping($user, $existingUser);

                        return $existingUser;
                    }
                }
            }
        } catch (\Exception $e) {
            // If search fails, proceed with creation
            Log::channel('mcp')->debug("User search failed, proceeding with creation: {$e->getMessage()}");
        }

        // User doesn't exist, create it
        $response = $this->client->post('/admin/users', $payload);

        if (! isset($response['uuid']) || ! isset($response['id'])) {
            throw new McpServerException('Invalid response from MCP Server: missing uuid or id');
        }

        $this->saveSyncMapping($user, $response);

        return $response;
    }

    private function updateUser(User $user, array $payload): array
    {
        $mcpUserId = $this->getMcpUserId($user);

        $response = $this->client->put("/admin/users/{$mcpUserId}", $payload);

        return $response;
    }

    private function deleteUser(User $user): array
    {
        $mcpUserId = $this->getMcpUserId($user);

        $response = $this->client->delete("/admin/users/{$mcpUserId}");

        $user->mcpServerUser()?->delete();

        return $response;
    }

    private function buildUserPayload(User $user): array
    {
        $nameParts = explode(' ', $user->name, 2);

        return [
            'email' => $user->email,
            'username' => $this->generateUsername($user),
            'password' => $this->generateSecurePassword(),
            'first_name' => $nameParts[0] ?? '',
            'last_name' => $nameParts[1] ?? '',
            'role' => $this->mapRole($user->role),
            'is_active' => $user->is_active,
            'is_verified' => $user->email_verified_at !== null,
        ];
    }

    private function generateUsername(User $user): string
    {
        $baseUsername = Str::slug(explode('@', $user->email)[0]);

        return $baseUsername;
    }

    private function generateSecurePassword(): string
    {
        return Str::password(32, letters: true, numbers: true, symbols: true, spaces: false);
    }

    private function mapRole(UserRole $laravelRole): string
    {
        $mapping = config('mcp-server.role_mapping', [
            'admin' => 'admin',
            'manager' => 'manager',
            'user' => 'user',
            'read_only' => 'read_only',
        ]);

        return $mapping[$laravelRole->value] ?? 'user';
    }

    private function saveSyncMapping(User $user, array $response): void
    {
        McpServerUser::updateOrCreate(
            ['user_id' => $user->id],
            [
                'mcp_user_uuid' => $response['uuid'],
                'mcp_user_id' => $response['id'],
                'sync_status' => 'synced',
                'last_sync_at' => now(),
                'sync_error' => null,
                'sync_attempts' => 0,
            ]
        );
    }

    private function getMcpUserId(User $user): int
    {
        $mcpServerUser = $user->mcpServerUser;

        if (! $mcpServerUser) {
            throw new McpServerException("User {$user->id} has no MCP Server mapping");
        }

        return $mcpServerUser->mcp_user_id;
    }

    private function updateSyncStatus(User $user, string $status, ?string $error = null): void
    {
        $mcpServerUser = $user->mcpServerUser;

        if (! $mcpServerUser && $status !== 'error') {
            return;
        }

        if (! $mcpServerUser) {
            McpServerUser::create([
                'user_id' => $user->id,
                'mcp_user_uuid' => null,
                'mcp_user_id' => 0,
                'sync_status' => $status,
                'sync_error' => $error,
                'sync_attempts' => 1,
            ]);

            return;
        }

        if ($status === 'synced') {
            $mcpServerUser->markAsSynced();
        } elseif ($status === 'error') {
            $mcpServerUser->markAsError($error ?? 'Unknown error');
        } else {
            $mcpServerUser->update(['sync_status' => $status]);
        }
    }

    private function logSync(
        User $user,
        string $action,
        string $status,
        array $requestPayload,
        ?array $responsePayload,
        ?string $errorMessage,
        int $durationMs
    ): void {
        if (! config('mcp-server.logging.log_sync', true)) {
            return;
        }

        McpSyncLog::logSync(
            userId: $user->id,
            syncType: $action,
            direction: 'laravel_to_mcp',
            status: $status,
            requestPayload: $requestPayload,
            responsePayload: $responsePayload,
            errorMessage: $errorMessage,
            durationMs: $durationMs
        );
    }

    public function syncMultipleUsers(iterable $users, string $action = 'create'): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($users as $user) {
            if ($this->syncUser($user, $action)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ];
            }
        }

        return $results;
    }

    public function isUserSynced(User $user): bool
    {
        return $user->mcpServerUser?->isSynced() ?? false;
    }

    public function getSyncStatus(User $user): ?string
    {
        return $user->mcpServerUser?->sync_status;
    }

    public function retrySyncForUser(User $user): bool
    {
        $mcpServerUser = $user->mcpServerUser;

        if (! $mcpServerUser) {
            return $this->syncUser($user, 'create');
        }

        if ($mcpServerUser->isSynced()) {
            return true;
        }

        $action = $mcpServerUser->mcp_user_id > 0 ? 'update' : 'create';

        return $this->syncUser($user, $action);
    }
}
