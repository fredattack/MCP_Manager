<?php

namespace App\Services;

use App\Models\McpIntegration;
use App\Models\McpServer;
use Illuminate\Support\Facades\Log;

/**
 * Mock MCP Server Service for development/testing
 * Simulates MCP server responses without requiring an actual server
 */
class MockMcpServerService
{
    private CryptoService $crypto;

    public function __construct(CryptoService $crypto)
    {
        $this->crypto = $crypto;
    }

    /**
     * Simulate server discovery
     */
    public function discoverServer(string $url): array
    {
        Log::info('Mock: Discovering server', ['url' => $url]);

        // Simulate successful discovery for localhost/development URLs
        $isLocalUrl = str_contains($url, 'localhost') || str_contains($url, '127.0.0.1') || str_contains($url, 'test');
        Log::info('Mock: URL check', ['url' => $url, 'isLocalUrl' => $isLocalUrl]);

        if ($isLocalUrl) {
            return [
                'success' => true,
                'server_info' => [
                    'name' => 'Mock MCP Server',
                    'version' => '1.0.0',
                    'capabilities' => [
                        'todoist' => true,
                        'notion' => true,
                        'jira' => true,
                        'sentry' => true,
                        'confluence' => true,
                        'openai' => true,
                        'mistral' => true,
                    ],
                    'requires_ssl' => false,
                    'public_key' => $this->generateMockPublicKey(),
                ],
            ];
        }

        return [
            'success' => false,
            'error' => 'Server not reachable',
        ];
    }

    /**
     * Simulate secure connection establishment
     */
    public function establishSecureConnection(McpServer $server): array
    {
        Log::info('Mock: Establishing secure connection', ['server_id' => $server->id]);

        // Generate mock keys if not present
        if (! $server->public_key || ! $server->private_key) {
            $keyPair = $this->crypto->generateKeyPair();
            $server->public_key = $keyPair['public'];
            $server->private_key = $keyPair['private'];
            $server->save();
        }

        // Simulate successful handshake
        return [
            'success' => true,
            'connection_id' => 'mock_'.uniqid(),
            'server_public_key' => $this->generateMockPublicKey(),
            'session_token' => base64_encode(random_bytes(32)),
            'expires_at' => now()->addHours(24)->toIso8601String(),
        ];
    }

    /**
     * Simulate integration configuration
     */
    public function configureIntegration(McpServer $server, string $service, array $credentials): array
    {
        Log::info('Mock: Configuring integration', [
            'server_id' => $server->id,
            'service' => $service,
        ]);

        // Simulate credential validation
        if (empty($credentials)) {
            return [
                'success' => false,
                'error' => 'Invalid credentials',
            ];
        }

        // For mock purposes, accept any non-empty credentials
        return [
            'success' => true,
            'integration_id' => 'mock_integration_'.$service.'_'.uniqid(),
            'status' => 'active',
            'metadata' => [
                'service' => $service,
                'configured_at' => now()->toIso8601String(),
                'test_mode' => true,
            ],
        ];
    }

    /**
     * Simulate getting integrations status
     */
    public function getIntegrationsStatus(McpServer $server): array
    {
        Log::info('Mock: Getting integrations status', ['server_id' => $server->id]);

        // Get configured integrations from database
        $integrations = McpIntegration::where('mcp_server_id', $server->id)->get();

        $status = [];
        foreach ($integrations as $integration) {
            $status[$integration->service] = [
                'status' => $integration->status,
                'last_sync' => $integration->last_sync_at?->toIso8601String(),
                'health' => 'healthy',
                'metrics' => [
                    'api_calls_today' => rand(10, 100),
                    'last_response_time' => rand(50, 500).'ms',
                    'error_rate' => rand(0, 5).'%',
                ],
            ];
        }

        // Add some mock data for demonstration
        if (empty($status)) {
            $status = [
                'todoist' => [
                    'status' => 'not_configured',
                    'health' => 'unknown',
                ],
                'notion' => [
                    'status' => 'not_configured',
                    'health' => 'unknown',
                ],
            ];
        }

        return [
            'success' => true,
            'integrations' => $status,
            'server_health' => 'healthy',
            'uptime' => '99.9%',
        ];
    }

    /**
     * Simulate testing connection
     */
    public function testConnection(McpServer $server): array
    {
        Log::info('Mock: Testing connection', ['server_id' => $server->id]);

        // Simulate successful test for development URLs
        if (str_contains($server->url, 'localhost') || str_contains($server->url, '127.0.0.1') || str_contains($server->url, 'test')) {
            return [
                'success' => true,
                'response_time' => rand(50, 200),
                'server_time' => now()->toIso8601String(),
                'version' => '1.0.0',
            ];
        }

        return [
            'success' => false,
            'error' => 'Connection timeout',
        ];
    }

    /**
     * Generate a mock RSA public key
     */
    private function generateMockPublicKey(): string
    {
        return "-----BEGIN PUBLIC KEY-----\n".
               'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA'.base64_encode(random_bytes(32))."\n".
               base64_encode(random_bytes(64))."\n".
               base64_encode(random_bytes(64))."\n".
               base64_encode(random_bytes(64))."\n".
               base64_encode(random_bytes(32))."\n".
               '-----END PUBLIC KEY-----';
    }

    /**
     * Simulate disconnecting from server
     */
    public function disconnect(McpServer $server): array
    {
        Log::info('Mock: Disconnecting from server', ['server_id' => $server->id]);

        return [
            'success' => true,
            'message' => 'Successfully disconnected from mock server',
        ];
    }

    /**
     * Simulate removing integration
     */
    public function removeIntegration(McpServer $server, string $service): array
    {
        Log::info('Mock: Removing integration', [
            'server_id' => $server->id,
            'service' => $service,
        ]);

        return [
            'success' => true,
            'message' => "Integration {$service} removed successfully",
        ];
    }
}
