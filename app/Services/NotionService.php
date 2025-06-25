<?php

namespace App\Services;

use App\Models\IntegrationAccount;
use Illuminate\Support\Facades\Http;

class NotionService
{
    /**
     * The MCP Server URL.
     */
    protected string $serverUrl;

    /**
     * The API token for authentication.
     */
    protected string $apiToken;

    /**
     * Create a new NotionService instance.
     */
    public function __construct(?IntegrationAccount $integrationAccount = null)
    {
        $mcpServerUrl = config('services.mcp.url');

        if (! $mcpServerUrl) {
            throw new \Exception('MCP Server URL not configured');
        }

        $this->serverUrl = is_string($mcpServerUrl) ? $mcpServerUrl : '';

        // If an integration account is provided, use its token
        if ($integrationAccount instanceof \App\Models\IntegrationAccount) {
            $this->apiToken = $integrationAccount->access_token;
        } else {
            // Otherwise, use the global token from config
            $mcpApiToken = config('services.mcp.token');

            if (! $mcpApiToken) {
                throw new \Exception('MCP API Token not configured');
            }
            $this->apiToken = is_string($mcpApiToken) ? $mcpApiToken : '';
        }
    }

    /**
     * Make an authenticated request to the MCP Server.
     *
     * @param  string  $endpoint  The API endpoint
     * @param  array<string, mixed>  $params  The query parameters
     * @return array<mixed> The response data
     *
     * @throws \Exception If the request fails
     */
    protected function makeRequest(string $endpoint, array $params = []): array
    {

        $response = Http::withToken($this->apiToken)
            ->get($this->serverUrl.$endpoint, $params);
        if ($response->failed()) {
            throw new \Exception("Failed to fetch from {$endpoint}: ".$response->body());
        }

        $data = $response->json();

        // Ensure we return the correct type
        return is_array($data) ? $data : [];
    }

    /**
     * Fetch Notion pages from the MCP Server.
     *
     * @param  string|null  $pageId  The ID of the page to fetch, or null to use the default from config
     * @return array<mixed> The Notion pages data
     *
     * @throws \Exception If the request fails
     */
    public function fetchNotionPages(?string $pageId = null): array
    {
        $defaultPageId = config('services.mcp.default_page_id');

        // Use provided page_id or fall back to default if available
        $pageId ??= $defaultPageId;

        // Only include page_id in the request if it's not null
        $params = $pageId ? ['page_id' => $pageId] : [];

        return $this->makeRequest('/notion/pages-tree', $params);
    }

    /**
     * Fetch Notion databases from the MCP Server.
     *
     * @return array<mixed> The Notion databases data
     *
     * @throws \Exception If the request fails
     */
    public function fetchNotionDatabases(): array
    {
        return $this->makeRequest('/notion/databases');
    }

    /**
     * Fetch a specific Notion page from the MCP Server.
     *
     * @param  string  $pageId  The ID of the page to fetch
     * @return array<mixed> The Notion page data
     *
     * @throws \Exception If the request fails
     */
    public function fetchNotionPage(string $pageId): array
    {
        return $this->makeRequest('/notion/page/'.$pageId);
    }

    /**
     * Fetch blocks for a specific Notion page from the MCP Server.
     *
     * @param  string  $pageId  The ID of the page to fetch blocks for
     * @return array<mixed> The Notion blocks data
     *
     * @throws \Exception If the request fails
     */
    public function fetchNotionBlocks(string $pageId): array
    {
        return $this->makeRequest('/notion/blocks/'.$pageId);
    }
}
