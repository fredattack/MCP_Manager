<?php

namespace App\Services\McpServer\Proxies;

use App\Models\User;
use App\Services\McpServer\McpServerClient;
use App\Services\McpServer\McpTokenManager;
use Illuminate\Support\Facades\Cache;

class NotionProxy
{
    private int $cacheTtl;

    public function __construct(
        private readonly McpServerClient $client,
        private readonly McpTokenManager $tokenManager,
        private readonly User $user
    ) {
        $this->cacheTtl = config('mcp-server.proxy.services.notion.cache_ttl', 300);
    }

    public function listDatabases(): array
    {
        $cacheKey = "mcp_notion_databases_{$this->user->id}";

        if ($this->cacheTtl > 0) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $token = $this->tokenManager->getValidToken($this->user);
        $response = $this->client->get('/notion/databases', [], $token);

        if ($this->cacheTtl > 0) {
            Cache::put($cacheKey, $response, now()->addSeconds($this->cacheTtl));
        }

        return $response;
    }

    public function getDatabase(string $databaseId): array
    {
        $cacheKey = "mcp_notion_database_{$this->user->id}_{$databaseId}";

        if ($this->cacheTtl > 0) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $token = $this->tokenManager->getValidToken($this->user);
        $response = $this->client->get("/notion/databases/{$databaseId}", [], $token);

        if ($this->cacheTtl > 0) {
            Cache::put($cacheKey, $response, now()->addSeconds($this->cacheTtl));
        }

        return $response;
    }

    public function queryDatabase(string $databaseId, array $filter = [], array $sorts = [], ?int $pageSize = null): array
    {
        $token = $this->tokenManager->getValidToken($this->user);

        $payload = [];

        if (! empty($filter)) {
            $payload['filter'] = $filter;
        }

        if (! empty($sorts)) {
            $payload['sorts'] = $sorts;
        }

        if ($pageSize) {
            $payload['page_size'] = $pageSize;
        }

        return $this->client->post("/notion/databases/{$databaseId}/query", $payload, $token);
    }

    public function getPage(string $pageId): array
    {
        $cacheKey = "mcp_notion_page_{$this->user->id}_{$pageId}";

        if ($this->cacheTtl > 0) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $token = $this->tokenManager->getValidToken($this->user);
        $response = $this->client->get("/notion/pages/{$pageId}", [], $token);

        if ($this->cacheTtl > 0) {
            Cache::put($cacheKey, $response, now()->addSeconds($this->cacheTtl));
        }

        return $response;
    }

    public function createPage(array $properties, ?string $parentId = null, ?string $parentType = 'database_id'): array
    {
        $token = $this->tokenManager->getValidToken($this->user);

        $payload = [
            'properties' => $properties,
        ];

        if ($parentId) {
            $payload['parent'] = [
                $parentType => $parentId,
            ];
        }

        $response = $this->client->post('/notion/pages', $payload, $token);

        $this->clearUserCache();

        return $response;
    }

    public function updatePage(string $pageId, array $properties): array
    {
        $token = $this->tokenManager->getValidToken($this->user);

        $payload = [
            'properties' => $properties,
        ];

        $response = $this->client->put("/notion/pages/{$pageId}", $payload, $token);

        $this->clearPageCache($pageId);

        return $response;
    }

    public function deletePage(string $pageId): array
    {
        $token = $this->tokenManager->getValidToken($this->user);

        $response = $this->client->delete("/notion/pages/{$pageId}", $token);

        $this->clearPageCache($pageId);
        $this->clearUserCache();

        return $response;
    }

    public function search(string $query, ?string $filter = null): array
    {
        $token = $this->tokenManager->getValidToken($this->user);

        $payload = [
            'query' => $query,
        ];

        if ($filter) {
            $payload['filter'] = ['property' => 'object', 'value' => $filter];
        }

        return $this->client->post('/notion/search', $payload, $token);
    }

    public function getUser(string $userId): array
    {
        $cacheKey = "mcp_notion_user_{$this->user->id}_{$userId}";

        if ($this->cacheTtl > 0) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $token = $this->tokenManager->getValidToken($this->user);
        $response = $this->client->get("/notion/users/{$userId}", [], $token);

        if ($this->cacheTtl > 0) {
            Cache::put($cacheKey, $response, now()->addSeconds($this->cacheTtl));
        }

        return $response;
    }

    public function listUsers(): array
    {
        $cacheKey = "mcp_notion_users_{$this->user->id}";

        if ($this->cacheTtl > 0) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $token = $this->tokenManager->getValidToken($this->user);
        $response = $this->client->get('/notion/users', [], $token);

        if ($this->cacheTtl > 0) {
            Cache::put($cacheKey, $response, now()->addSeconds($this->cacheTtl));
        }

        return $response;
    }

    public function clearCache(?string $specificKey = null): void
    {
        if ($specificKey) {
            Cache::forget($specificKey);
        } else {
            $this->clearUserCache();
        }
    }

    private function clearUserCache(): void
    {
        Cache::forget("mcp_notion_databases_{$this->user->id}");
        Cache::forget("mcp_notion_users_{$this->user->id}");
    }

    private function clearPageCache(string $pageId): void
    {
        Cache::forget("mcp_notion_page_{$this->user->id}_{$pageId}");
    }
}
