<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $key = 'api'): Response
    {
        // Define rate limits for different endpoints
        $limits = [
            'api' => [60, 1],         // 60 requests per minute
            'auth' => [5, 1],         // 5 login attempts per minute
            'ai_chat' => [10, 1],     // 10 AI chat messages per minute
            'integration' => [30, 1], // 30 integration requests per minute
        ];

        [$maxAttempts, $decayMinutes] = $limits[$key] ?? [60, 1];

        // Create unique key per user or IP
        $rateLimitKey = $key.':'.($request->user()->id ?? $request->ip());

        // Check if too many attempts
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $seconds,
            ], 429)->withHeaders([
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        // Record the attempt
        RateLimiter::hit($rateLimitKey, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers to response
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($rateLimitKey, $maxAttempts));

        return $response;
    }
}
