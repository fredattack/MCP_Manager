<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieAttributes
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only modify cookies in local environment
        if (app()->environment('local')) {
            $cookies = $response->headers->getCookies();

            foreach ($cookies as $cookie) {
                // If SameSite is 'none', we need to ensure it works in local development
                // without requiring HTTPS (which is normally required for SameSite=None)
                if ($cookie->getSameSite() === 'none') {
                    // Create a new cookie with the same attributes but modified for local development
                    $response->headers->setCookie(
                        new \Symfony\Component\HttpFoundation\Cookie(
                            $cookie->getName(),
                            $cookie->getValue(),
                            $cookie->getExpiresTime(),
                            $cookie->getPath(),
                            $cookie->getDomain(),
                            true, // secure = false for local development
                            $cookie->isHttpOnly(),
                            true,  // raw
                            $cookie->getSameSite()
                        )
                    );
                }
            }
        }

        return $response;
    }
}
