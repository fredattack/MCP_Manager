<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log authentication status
        Log::info('Auth Debug', [
            'is_authenticated' => Auth::check(),
            'guard' => Auth::getDefaultDriver(),
            'session_id' => session()->getId(),
            'cookies' => $request->cookies->all(),
            'headers' => $request->headers->all(),
        ]);

        return $next($request);
    }
}
