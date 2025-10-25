<?php

namespace App\Services;

use App\Models\McpServer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to manage automatic MCP server connection
 * Connects to the configured MCP server using environment variables
 */
class McpConnectionService
{
    private string $serverUrl;

    private string $email;

    private string $password;

    private ?string $jwtToken;

    public function __construct()
    {
        $this->serverUrl = config('services.mcp.server_url');
        $this->email = config('services.mcp.email');
        $this->password = config('services.mcp.password');
        $this->jwtToken = config('services.mcp.jwt_token');
    }

    /**
     * Get or refresh JWT token for MCP server authentication
     */
    public function getAuthToken(): string
    {
        // Check if we have a cached token
        $cachedToken = Cache::get('mcp_jwt_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        // If we have a JWT token in config, validate it first
        if ($this->jwtToken) {
            if ($this->validateToken($this->jwtToken)) {
                Cache::put('mcp_jwt_token', $this->jwtToken, now()->addHours(23));

                return $this->jwtToken;
            }
        }

        // Otherwise, authenticate with email/password
        return $this->authenticate();
    }

    /**
     * Authenticate with MCP server and get JWT token
     */
    private function authenticate(): string
    {
        try {
            $response = Http::post($this->serverUrl.'/auth/login', [
                'email' => $this->email,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? $data['token'] ?? null;

                if ($token) {
                    // Cache token for 23 hours (assuming 24h expiry)
                    Cache::put('mcp_jwt_token', $token, now()->addHours(23));
                    Log::info('Successfully authenticated with MCP server');

                    return $token;
                }
            }

            Log::error('Failed to authenticate with MCP server', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new \Exception('Failed to authenticate with MCP server');
        } catch (\Exception $e) {
            Log::error('MCP authentication error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate JWT token with MCP server
     */
    private function validateToken(string $token): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->get($this->serverUrl.'/auth/me');

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Token validation failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Ensure user has MCP server configured
     */
    public function ensureServerConfigured(User $user): McpServer
    {
        $server = $user->mcpServer;

        if (! $server) {
            // Auto-create server configuration from environment
            $server = $user->mcpServer()->create([
                'name' => 'MCP Server',
                'url' => $this->serverUrl,
                'status' => 'active',
                'config' => [
                    'auto_configured' => true,
                    'configured_at' => now()->toIso8601String(),
                ],
            ]);

            Log::info('Auto-configured MCP server for user', [
                'user_id' => $user->id,
                'server_url' => $this->serverUrl,
            ]);
        }

        // Ensure server is active and has valid token
        if ($server->status !== 'active') {
            $server->status = 'active';
            $server->save();
        }

        return $server;
    }

    /**
     * Make authenticated request to MCP server
     */
    public function request(string $method, string $endpoint, array $data = []): array
    {
        $token = $this->getAuthToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->$method($this->serverUrl.$endpoint, $data);

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        // If token expired, refresh and retry
        if ($response->status() === 401) {
            Cache::forget('mcp_jwt_token');
            $token = $this->authenticate();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/json',
            ])->$method($this->serverUrl.$endpoint, $data);

            if ($response->successful()) {
                return $response->json() ?? [];
            }
        }

        Log::error('MCP request failed', [
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'response' => $response->body(),
        ]);

        throw new \Exception('MCP request failed: '.$response->body());
    }

    /**
     * Configure integration on MCP server
     */
    public function configureIntegration(string $service, array $credentials): array
    {
        // Try different endpoint patterns based on what MCP server might expect
        try {
            // First try the tool-based approach
            return $this->request('POST', '/api/mcp/'.$service.'/configure', $credentials);
        } catch (\Exception $e) {
            // If that fails, try a simpler endpoint
            try {
                return $this->request('POST', '/configure/'.$service, $credentials);
            } catch (\Exception $e2) {
                // If both fail, try the original pattern
                return $this->request('POST', '/integrations/'.$service.'/configure', [
                    'credentials' => $credentials,
                ]);
            }
        }
    }

    /**
     * Get integration status from MCP server
     */
    public function getIntegrationStatus(string $service): array
    {
        try {
            // Try tool-based endpoint first
            return $this->request('GET', '/api/mcp/'.$service.'/status');
        } catch (\Exception $e) {
            try {
                // Try simpler endpoint
                return $this->request('GET', '/status/'.$service);
            } catch (\Exception $e2) {
                // Fallback to original
                return $this->request('GET', '/integrations/'.$service.'/status');
            }
        }
    }

    /**
     * Test integration connection
     */
    public function testIntegration(string $service): array
    {
        try {
            // Try tool-based endpoint first
            return $this->request('POST', '/api/mcp/'.$service.'/test');
        } catch (\Exception $e) {
            try {
                // Try simpler endpoint
                return $this->request('POST', '/test/'.$service);
            } catch (\Exception $e2) {
                // Fallback to original
                return $this->request('POST', '/integrations/'.$service.'/test');
            }
        }
    }

    /**
     * Get all integrations status
     */
    public function getAllIntegrationsStatus(): array
    {
        try {
            // Try different endpoint patterns
            return $this->request('GET', '/api/mcp/status');
        } catch (\Exception $e) {
            try {
                return $this->request('GET', '/status');
            } catch (\Exception $e2) {
                // Fallback to original
                return $this->request('GET', '/integrations');
            }
        }
    }

    /**
     * Forward request to specific integration
     */
    public function forwardToIntegration(string $service, string $method, string $endpoint, array $data = []): array
    {
        $fullEndpoint = '/integrations/'.$service.'/proxy'.$endpoint;

        return $this->request($method, $fullEndpoint, $data);
    }
}
