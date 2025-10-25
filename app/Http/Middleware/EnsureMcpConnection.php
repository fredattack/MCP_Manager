<?php

namespace App\Http\Middleware;

use App\Services\McpConnectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMcpConnection
{
    private McpConnectionService $mcpConnection;

    public function __construct(McpConnectionService $mcpConnection)
    {
        $this->mcpConnection = $mcpConnection;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            try {
                // Ensure user has MCP server configured
                $this->mcpConnection->ensureServerConfigured(auth()->user());

                // Test connection to ensure token is valid
                $this->mcpConnection->getAuthToken();
            } catch (\Exception $e) {
                // Log error but don't block the request
                \Log::warning('MCP connection check failed', [
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($request);
    }
}
