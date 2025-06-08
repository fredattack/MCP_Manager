<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotionService
{
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
        $mcpServerUrl = config('services.mcp.url');
        $mcpApiToken = config('services.mcp.token');
        $defaultPageId = config('services.mcp.default_page_id');

        if (! $mcpServerUrl || ! $mcpApiToken) {
            throw new \Exception('MCP Server URL or API Token not configured');
        }

        // Use provided page_id or fall back to default if available
        $pageId ??= $defaultPageId;

        // Ensure we have strings for the URL and token
        $serverUrl = is_string($mcpServerUrl) ? $mcpServerUrl : '';
        $apiToken = is_string($mcpApiToken) ? $mcpApiToken : '';

        // Only include page_id in the request if it's not null
        $params = $pageId ? ['page_id' => $pageId] : [];

        $response = Http::withToken($apiToken)
            ->get($serverUrl.'/notion/pages-tree', $params);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch Notion pages: '.$response->body());
        }

        $data = $response->json();

        // Ensure we return the correct type
        return is_array($data) ? $data : [];
    }
}
