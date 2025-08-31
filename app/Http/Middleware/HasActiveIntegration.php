<?php

namespace App\Http\Middleware;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasActiveIntegration
{
    public function handle(Request $request, Closure $next, string $integrationType): Response
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $hasActiveIntegration = Auth::user()->integrationAccounts()
            ->where('type', IntegrationType::from($integrationType))
            ->where('status', IntegrationStatus::ACTIVE)
            ->exists();

        if (! $hasActiveIntegration) {
            $displayName = IntegrationType::from($integrationType)->displayName();

            // For web requests, redirect to setup page
            if ($request->expectsJson()) {
                return response()->json(['message' => "No active {$displayName} integration found"], 403);
            }
            
            // Redirect to the appropriate setup page
            if ($integrationType === 'todoist') {
                return redirect()->route('integrations.todoist.setup')
                    ->with('warning', 'Please connect your Todoist account first.');
            }
            
            return redirect()->route('integrations')
                ->with('warning', "Please connect your {$displayName} account first.");
        }

        return $next($request);
    }
}
