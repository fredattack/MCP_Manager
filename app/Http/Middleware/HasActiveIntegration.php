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

            return response()->json(['message' => "No active {$displayName} integration found"], 403);
        }

        return $next($request);
    }
}
