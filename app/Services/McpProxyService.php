<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IntegrationAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class McpProxyService
{
    private readonly string $mcpServerUrl;

    public function __construct()
    {
        $serverUrl = config('services.mcp.server_url', 'http://localhost:3000');
        $this->mcpServerUrl = is_string($serverUrl) ? $serverUrl : 'http://localhost:3000';
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function request(IntegrationAccount $integrationAccount, string $method, array $params = []): array
    {
        try {
            $token = decrypt($integrationAccount->access_token);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.(is_string($token) ? $token : ''),
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post("{$this->mcpServerUrl}/api/mcp/{$integrationAccount->type->value}/{$method}", $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('MCP request failed', [
                'method' => $method,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'Request failed',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('MCP request exception', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            throw new \App\Exceptions\IntegrationException(
                $integrationAccount->type->value,
                'Failed to communicate with MCP server: '.$e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Call an MCP tool method.
     *
     * @param  array<string, mixed>  $params
     */
    public function callMcpTool(IntegrationAccount $integrationAccount, string $tool, array $params = []): mixed
    {
        $result = $this->request($integrationAccount, 'tools/'.$tool, $params);

        if (! $result['success']) {
            throw new \App\Exceptions\IntegrationException(
                $integrationAccount->type->value,
                'MCP tool call failed: '.$tool
            );
        }

        return $result['data'];
    }
}
