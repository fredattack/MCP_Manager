<?php

namespace App\Http\Middleware;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasActiveNotionIntegration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //        ray('in HasActiveNotionIntegration');
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthenticated+++3'], 401);
        }

        $hasActiveNotionIntegration = Auth::user()->integrationAccounts()
            ->where('type', IntegrationType::NOTION)
            ->where('status', IntegrationStatus::ACTIVE)
            ->exists();

        if (! $hasActiveNotionIntegration) {
            return response()->json(['message' => 'No active Notion integration found'], 403);
        }

        return $next($request);
    }
}
