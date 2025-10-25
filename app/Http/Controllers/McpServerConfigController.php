<?php

namespace App\Http\Controllers;

use App\Services\CryptoService;
use App\Services\McpServerManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class McpServerConfigController extends Controller
{
    public function __construct(
        private McpServerManager $mcpManager,
        private CryptoService $cryptoService
    ) {}

    /**
     * Display the MCP server configuration page
     */
    public function show(): Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $server = $user->mcpServer;

        return Inertia::render('Mcp/ServerConfig', [
            'server' => $server ? [
                'id' => $server->id,
                'name' => $server->name,
                'url' => $server->url,
                'status' => $server->status,
                'health' => $server->getHealthStatus(),
                'configured_at' => $server->created_at->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * Store or update MCP server configuration
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'ssl_certificate' => 'nullable|string',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        try {
            DB::beginTransaction();

            // Discover server first
            $serverInfo = $this->mcpManager->discoverServer($request->url);

            // Validate SSL certificate if provided
            if ($request->ssl_certificate) {
                $hostname = parse_url($request->url, PHP_URL_HOST);
                if (! $this->cryptoService->validateSSLCertificate($request->ssl_certificate, $hostname)) {
                    return back()->withErrors(['ssl_certificate' => 'Invalid SSL certificate for the specified hostname']);
                }
            }

            // Create or update server configuration
            $server = $user->mcpServer()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $request->name,
                    'url' => $request->url,
                    'ssl_certificate' => $request->ssl_certificate,
                    'config' => [
                        'version' => $serverInfo['version'],
                        'capabilities' => $serverInfo['capabilities'],
                    ],
                    'status' => 'inactive',
                ]
            );

            // Establish secure connection
            $connection = $this->mcpManager->establishSecureConnection($server);

            DB::commit();

            return redirect()->route('mcp.dashboard')
                ->with('success', 'MCP server configured and connected successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to configure MCP server', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to configure MCP server: '.$e->getMessage()]);
        }
    }

    /**
     * Test MCP server connectivity
     */
    public function test(): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $server = $user->mcpServer;

        if (! $server) {
            return response()->json([
                'success' => false,
                'error' => 'No MCP server configured',
            ], 404);
        }

        try {
            // Test discovery
            $serverInfo = $this->mcpManager->discoverServer($server->url);

            // Test secure connection if not active
            if (! $server->isActive()) {
                $connection = $this->mcpManager->establishSecureConnection($server);
            }

            // Get server status
            $status = $this->mcpManager->getServerStatus($user);

            return response()->json([
                'success' => true,
                'status' => $status,
                'server_info' => $serverInfo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disconnect from MCP server
     */
    public function disconnect(): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $server = $user->mcpServer;

        if ($server) {
            $this->mcpManager->disconnect($server);
        }

        return redirect()->route('mcp.server.config')
            ->with('success', 'Disconnected from MCP server');
    }

    /**
     * Delete MCP server configuration
     */
    public function destroy(): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $server = $user->mcpServer;

        if ($server) {
            // Disconnect first
            $this->mcpManager->disconnect($server);

            // Delete server and all related integrations
            $server->delete();
        }

        return redirect()->route('mcp.server.config')
            ->with('success', 'MCP server configuration deleted');
    }
}
