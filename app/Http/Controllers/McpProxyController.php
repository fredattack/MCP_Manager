<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class McpProxyController extends Controller
{
    private readonly string $mcpServerUrl;

    public function __construct()
    {
        $configUrl = config('services.mcp.server_url', env('MCP_SERVER_URL', 'http://localhost:9978'));
        $this->mcpServerUrl = is_string($configUrl) ? $configUrl : 'http://localhost:9978';
    }

    /**
     * Handle authentication with the MCP server
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $response = Http::asForm()->post("{$this->mcpServerUrl}/auth/token", [
                'username' => $request->input('username'),
                'password' => $request->input('password'),
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            $jsonResponse = $response->json();

            return response()->json([
                'error' => 'Authentication failed',
                'message' => (is_array($jsonResponse) && isset($jsonResponse['detail'])) ? $jsonResponse['detail'] : 'Invalid credentials',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('MCP login failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to MCP server',
            ], 503);
        }
    }

    /**
     * Get current user info from MCP server
     */
    public function me(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'No authorization token provided'], 401);
        }

        try {
            $response = Http::withToken($token)->get("{$this->mcpServerUrl}/auth/me");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            $jsonResponse = $response->json();

            return response()->json([
                'error' => 'Failed to get user info',
                'message' => (is_array($jsonResponse) && isset($jsonResponse['detail'])) ? $jsonResponse['detail'] : 'Token invalid',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('MCP user info failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to MCP server',
            ], 503);
        }
    }

    /**
     * Get today's tasks from MCP server
     */
    public function getTodayTasks(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'No authorization token provided'], 401);
        }

        try {
            $response = Http::withToken($token)->get("{$this->mcpServerUrl}/todoist/tasks/today");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            $jsonResponse = $response->json();

            return response()->json([
                'error' => 'MCP request failed',
                'message' => (is_array($jsonResponse) && isset($jsonResponse['detail'])) ? $jsonResponse['detail'] : 'Request failed',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('MCP Today tasks failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to MCP server',
            ], 503);
        }
    }

    /**
     * Get upcoming tasks from MCP server
     */
    public function getUpcomingTasks(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'No authorization token provided'], 401);
        }

        try {
            $response = Http::withToken($token)->get("{$this->mcpServerUrl}/todoist/tasks/week");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            $jsonResponse = $response->json();

            return response()->json([
                'error' => 'MCP request failed',
                'message' => (is_array($jsonResponse) && isset($jsonResponse['detail'])) ? $jsonResponse['detail'] : 'Request failed',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('MCP Week tasks failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to MCP server',
            ], 503);
        }
    }

    /**
     * Proxy Todoist requests to MCP server
     */
    public function todoistProxy(Request $request, string $path = ''): JsonResponse
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'No authorization token provided'], 401);
        }

        try {
            $method = $request->method();
            $url = "{$this->mcpServerUrl}/todoist/{$path}";
            $data = $request->all();

            $httpRequest = Http::withToken($token);

            $response = match ($method) {
                'GET' => $httpRequest->get($url, $data),
                'POST' => $httpRequest->post($url, $data),
                'PUT' => $httpRequest->put($url, $data),
                'DELETE' => $httpRequest->delete($url, $data),
                default => throw new \InvalidArgumentException("Unsupported method: {$method}")
            };

            if ($response->successful()) {
                return response()->json($response->json());
            }

            $jsonResponse = $response->json();

            return response()->json([
                'error' => 'MCP request failed',
                'message' => (is_array($jsonResponse) && isset($jsonResponse['detail'])) ? $jsonResponse['detail'] : 'Request failed',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('MCP Todoist proxy failed', [
                'path' => $path,
                'method' => $request->method(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to MCP server',
            ], 503);
        }
    }
}
