<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class McpAuthService
{
    private readonly ?string $mcpServerUrl;

    private readonly ?string $username;

    private readonly ?string $password;

    public function __construct()
    {
        $this->mcpServerUrl = config('services.mcp.server_url');
        $this->username = config('services.mcp.user');
        $this->password = config('services.mcp.password');
    }

    public function getAccessToken(): string
    {
        $cacheKey = 'mcp_access_token';

        $token = Cache::get($cacheKey);
        if ($token) {
            return $token;
        }

        Log::info('Requesting new MCP access token');

        $response = Http::timeout(10)->asForm()->post("{$this->mcpServerUrl}/token", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if (! $response->successful()) {
            Log::error('MCP authentication failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to authenticate with MCP server: '.$response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;

        if (! $accessToken) {
            throw new \Exception('No access token received from MCP server');
        }

        Cache::put($cacheKey, $accessToken, 3600); // Cache for 1 hour

        Log::info('MCP access token obtained successfully');

        return $accessToken;
    }
}
