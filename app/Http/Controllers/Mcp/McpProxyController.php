<?php

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MCP Proxy Controller
 *
 * This controller acts as a proxy between the Laravel Manager application
 * and the external MCP Server (Python). It forwards authentication and
 * integration requests to the MCP Server.
 *
 * Architecture Flow:
 * ┌─────────────────┐       ┌──────────────────┐       ┌─────────────────┐
 * │ Frontend (React)│  -->  │ McpProxyController│  -->  │ MCP Server (Py) │
 * │ User Interface  │       │ (This Controller) │       │ localhost:9978  │
 * └─────────────────┘       └──────────────────┘       └─────────────────┘
 *
 * Purpose:
 * - Proxy authentication requests from Manager frontend to MCP Server
 * - Forward integration API calls (Todoist, etc.) to MCP Server
 * - Handle errors and provide consistent response format
 * - Centralize MCP Server communication configuration
 *
 * Security:
 * - Uses Bearer token authentication for protected endpoints
 * - Validates user credentials before proxying requests
 * - Logs errors for debugging and monitoring
 *
 * @see \App\Http\Controllers\Api\Mcp\* For MCP Server->Manager API endpoints
 * @see \App\Http\Middleware\ValidateMcpServerToken For MCP Server authentication
 */
class McpProxyController extends Controller
{
    private readonly string $mcpServerUrl;

    public function __construct()
    {
        // MCP Server URL from configuration
        // Default: http://localhost:9978 (Python FastAPI server)
        $configUrl = config('services.mcp.server_url', env('MCP_SERVER_URL', 'http://localhost:9978'));
        $this->mcpServerUrl = is_string($configUrl) ? $configUrl : 'http://localhost:9978';
    }

    /**
     * Handle authentication with the MCP Server
     *
     * This endpoint proxies login requests from the Manager frontend
     * to the MCP Server's authentication endpoint.
     *
     * Flow:
     * 1. Manager frontend sends username/password
     * 2. This method forwards credentials to MCP Server /auth/token
     * 3. MCP Server validates and returns JWT token
     * 4. Token is returned to frontend for subsequent requests
     *
     * Request Body:
     * - username: string (email)
     * - password: string
     *
     * Success Response (200):
     * {
     *   "access_token": "eyJhbGciOiJIUzI1NiIsInR...",
     *   "token_type": "bearer"
     * }
     *
     * Error Response (401):
     * {
     *   "error": "Authentication failed",
     *   "message": "Invalid credentials"
     * }
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Forward authentication request to MCP Server
            $response = Http::asForm()->post("{$this->mcpServerUrl}/auth/token", [
                'username' => $request->input('username'),
                'password' => $request->input('password'),
            ]);

            // Return successful authentication response
            if ($response->successful()) {
                return response()->json($response->json());
            }

            // Handle authentication failure
            $jsonResponse = $response->json();

            return response()->json([
                'error' => 'Authentication failed',
                'message' => (is_array($jsonResponse) && isset($jsonResponse['detail']))
                    ? $jsonResponse['detail']
                    : 'Invalid credentials',
            ], $response->status());

        } catch (\Exception $e) {
            // Log connection errors for debugging
            Log::error('MCP Server login failed', [
                'error' => $e->getMessage(),
                'server_url' => $this->mcpServerUrl,
            ]);

            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to MCP server',
            ], 503);
        }
    }

    /**
     * Get current user info from MCP Server
     *
     * Retrieves authenticated user information from the MCP Server
     * using the Bearer token obtained from login().
     *
     * Headers Required:
     * - Authorization: Bearer <token>
     *
     * Success Response (200):
     * {
     *   "id": 1,
     *   "username": "user@example.com",
     *   "email": "user@example.com"
     * }
     *
     * Error Response (401):
     * {
     *   "error": "Failed to get user info",
     *   "message": "Token invalid"
     * }
     */
    public function me(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'No authorization token provided'], 401);
        }

        try {
            // Forward request with Bearer token to MCP Server
            $response = Http::withToken($token)->get("{$this->mcpServerUrl}/auth/me");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            // Handle token validation failure
            $jsonResponse = $response->json();

            return response()->json([
                'error' => 'Failed to get user info',
                'message' => (is_array($jsonResponse) && isset($jsonResponse['detail']))
                    ? $jsonResponse['detail']
                    : 'Token invalid',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('MCP Server user info failed', [
                'error' => $e->getMessage(),
                'server_url' => $this->mcpServerUrl,
            ]);

            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to MCP server',
            ], 503);
        }
    }

    /**
     * Get today's tasks from MCP Server (Todoist integration)
     *
     * Retrieves tasks due today from the Todoist integration
     * running on the MCP Server.
     *
     * Headers Required:
     * - Authorization: Bearer <token>
     *
     * Success Response (200):
     * {
     *   "tasks": [
     *     {
     *       "id": "123",
     *       "content": "Task description",
     *       "due": {"date": "2025-11-04"}
     *     }
     *   ]
     * }
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
                'message' => (is_array($jsonResponse) && isset($jsonResponse['detail']))
                    ? $jsonResponse['detail']
                    : 'Request failed',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('MCP Today tasks failed', [
                'error' => $e->getMessage(),
                'server_url' => $this->mcpServerUrl,
            ]);

            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to MCP server',
            ], 503);
        }
    }

    /**
     * Get upcoming tasks from MCP Server (Todoist integration)
     *
     * Retrieves tasks due in the upcoming week from the Todoist
     * integration running on the MCP Server.
     *
     * Headers Required:
     * - Authorization: Bearer <token>
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
                'message' => (is_array($jsonResponse) && isset($jsonResponse['detail']))
                    ? $jsonResponse['detail']
                    : 'Request failed',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('MCP Week tasks failed', [
                'error' => $e->getMessage(),
                'server_url' => $this->mcpServerUrl,
            ]);

            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to MCP server',
            ], 503);
        }
    }

    /**
     * Generic Todoist proxy endpoint
     *
     * This method acts as a generic proxy for any Todoist-related
     * endpoint on the MCP Server. It forwards the HTTP method
     * and request data to the corresponding endpoint.
     *
     * Supported Methods: GET, POST, PUT, DELETE
     *
     * Headers Required:
     * - Authorization: Bearer <token>
     *
     * Example Usage:
     * - POST /api/mcp/todoist/tasks → POST {mcpServer}/todoist/tasks
     * - GET /api/mcp/todoist/projects → GET {mcpServer}/todoist/projects
     * - PUT /api/mcp/todoist/tasks/123 → PUT {mcpServer}/todoist/tasks/123
     *
     * @param  string  $path  The path after /todoist/
     *
     * @throws \InvalidArgumentException If HTTP method not supported
     */
    public function todoistProxy(Request $request, string $path = ''): JsonResponse
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'No authorization token provided'], 401);
        }

        try {
            // Get HTTP method and construct full URL
            $method = $request->method();
            $url = "{$this->mcpServerUrl}/todoist/{$path}";
            $data = $request->all();

            // Prepare HTTP client with Bearer token
            $httpRequest = Http::withToken($token);

            // Forward request based on HTTP method
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
                'message' => (is_array($jsonResponse) && isset($jsonResponse['detail']))
                    ? $jsonResponse['detail']
                    : 'Request failed',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('MCP Todoist proxy failed', [
                'path' => $path,
                'method' => $request->method(),
                'error' => $e->getMessage(),
                'server_url' => $this->mcpServerUrl,
            ]);

            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to MCP server',
            ], 503);
        }
    }
}
