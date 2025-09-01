<?php

namespace App\Services;

use App\Models\McpServer;
use App\Models\User;
use App\Models\IntegrationAccount;
use App\Exceptions\McpConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class McpServerManager
{
    private CryptoService $crypto;
    private ?MockMcpServerService $mockService = null;

    public function __construct(CryptoService $crypto)
    {
        $this->crypto = $crypto;
        
        // Use mock service in development/testing
        if (App::environment(['local', 'testing'])) {
            Log::info('Initializing McpServerManager with mock service', ['env' => App::environment()]);
            $this->mockService = app(MockMcpServerService::class);
        } else {
            Log::info('Initializing McpServerManager without mock service', ['env' => App::environment()]);
        }
    }

    /**
     * Discover and validate MCP server
     */
    public function discoverServer(string $url): array
    {
        // Use mock service in development
        if ($this->mockService) {
            Log::info('Using mock service for discovery', ['url' => $url]);
            $result = $this->mockService->discoverServer($url);
            if ($result['success']) {
                Log::info('Mock discovery successful', ['server_info' => $result['server_info']]);
                return $result['server_info'];
            }
            Log::error('Mock discovery failed', ['error' => $result['error']]);
            throw new McpConnectionException($result['error']);
        }

        try {
            $response = Http::timeout(10)->get($url . '/health');
            
            if (!$response->successful()) {
                throw new McpConnectionException('MCP server is not accessible');
            }

            $data = $response->json();
            
            return [
                'url' => $url,
                'version' => $data['version'] ?? 'unknown',
                'status' => $data['status'] ?? 'unknown',
                'capabilities' => $data['capabilities'] ?? [],
                'public_key' => $data['public_key'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('MCP server discovery failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw new McpConnectionException('Failed to discover MCP server: ' . $e->getMessage());
        }
    }

    /**
     * Establish secure connection with MCP server
     */
    public function establishSecureConnection(McpServer $server): array
    {
        // Use mock service in development
        if ($this->mockService) {
            $result = $this->mockService->establishSecureConnection($server);
            if ($result['success']) {
                $server->server_public_key = $result['server_public_key'];
                $server->session_token = $result['session_token'];
                $server->status = 'active';
                $server->save();
                
                return [
                    'connected' => true,
                    'session_token' => $server->session_token,
                    'expires_at' => $result['expires_at'] ?? null,
                ];
            }
            throw new McpConnectionException('Failed to establish secure connection');
        }

        try {
            // Generate key pair if not exists
            if (!$server->public_key || !$server->private_key) {
                $keyPair = $this->crypto->generateKeyPair();
                $server->public_key = $keyPair['public'];
                $server->private_key = $keyPair['private'];
                $server->save();
            }

            // Exchange public keys with server
            $response = Http::timeout(10)->post($server->url . '/api/handshake', [
                'client_public_key' => $server->public_key,
                'client_id' => $server->id,
            ]);

            if (!$response->successful()) {
                throw new McpConnectionException('Failed to establish secure connection');
            }

            $data = $response->json();
            
            // Store server's public key
            $server->server_public_key = $data['server_public_key'];
            $server->session_token = $data['session_token'] ?? null;
            $server->status = 'active';
            $server->save();

            return [
                'connected' => true,
                'session_token' => $server->session_token,
                'expires_at' => $data['expires_at'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to establish secure connection', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);
            
            $server->status = 'error';
            $server->error_message = $e->getMessage();
            $server->save();
            
            throw new McpConnectionException('Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Configure integration for a user
     */
    public function configureIntegration(
        User $user,
        string $service,
        array $credentials
    ): void {
        $server = $user->mcpServer;
        
        if (!$server || $server->status !== 'active') {
            throw new McpConnectionException('MCP server is not connected');
        }

        // Use mock service in development
        if ($this->mockService) {
            $result = $this->mockService->configureIntegration($server, $service, $credentials);
            if ($result['success']) {
                // Update or create integration record
                IntegrationAccount::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'type' => $service,
                    ],
                    [
                        'status' => 'active',
                        'meta' => array_merge(
                            $result['metadata'] ?? [],
                            [
                                'configured_at' => now()->toIso8601String(),
                                'mcp_server_id' => $server->id,
                            ]
                        ),
                    ]
                );
                return;
            }
            throw new McpConnectionException($result['error'] ?? 'Failed to configure integration');
        }

        // Encrypt credentials with server's public key
        $encryptedCredentials = $this->crypto->encrypt(
            json_encode($credentials),
            $server->server_public_key
        );

        // Send encrypted credentials to MCP server
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $server->session_token,
        ])->post($server->url . '/api/integrations/configure', [
            'user_id' => $user->id,
            'service' => $service,
            'credentials' => $encryptedCredentials,
        ]);

        if (!$response->successful()) {
            throw new McpConnectionException('Failed to configure integration: ' . $response->body());
        }

        // Update or create integration record
        IntegrationAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $service,
            ],
            [
                'status' => 'active',
                'meta' => [
                    'configured_at' => now()->toIso8601String(),
                    'mcp_server_id' => $server->id,
                ],
            ]
        );
    }

    /**
     * Get integrations status from MCP server
     * @return array<string, mixed>
     */
    public function getIntegrationsStatus(User $user): array
    {
        $server = $user->mcpServer;
        
        if (!$server || $server->status !== 'active') {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $server->session_token,
            ])->get($server->url . '/api/users/' . $user->id . '/integrations/status');

            if (!$response->successful()) {
                Log::warning('Failed to get integrations status', [
                    'user_id' => $user->id,
                    'response' => $response->body()
                ]);
                return [];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error getting integrations status', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Test integration connectivity
     * @return array{success: bool, error?: string}
     */
    public function testIntegration(User $user, string $service): array
    {
        $server = $user->mcpServer;
        
        if (!$server || $server->status !== 'active') {
            return [
                'success' => false,
                'error' => 'MCP server is not connected',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $server->session_token,
            ])->post($server->url . '/api/integrations/' . $service . '/test', [
                'user_id' => $user->id,
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Test failed: ' . $response->body(),
                ];
            }

            return $response->json();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get server status
     * @return array{connected: bool, latency: int|null, lastSync: string|null, status?: string, error?: string}
     */
    public function getServerStatus(User $user): array
    {
        $server = $user->mcpServer;
        
        if (!$server) {
            return [
                'connected' => false,
                'latency' => null,
                'lastSync' => null,
            ];
        }

        // Ping server to get latency
        $startTime = microtime(true);
        try {
            $response = Http::timeout(5)->get($server->url . '/health');
            $latency = round((microtime(true) - $startTime) * 1000); // Convert to ms
            
            return [
                'connected' => $response->successful(),
                'latency' => $latency,
                'lastSync' => $server->updated_at->toIso8601String(),
                'status' => $server->status,
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'latency' => null,
                'lastSync' => $server->updated_at->toIso8601String(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Disconnect from MCP server
     */
    public function disconnect(McpServer $server): void
    {
        try {
            if ($server->session_token) {
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . $server->session_token,
                ])->post($server->url . '/api/disconnect');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to properly disconnect from MCP server', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);
        }

        $server->status = 'inactive';
        $server->session_token = null;
        $server->save();
    }
}