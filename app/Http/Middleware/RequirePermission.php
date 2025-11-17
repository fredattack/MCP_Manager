<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401, 'Unauthenticated');
        }

        foreach ($permissions as $permission) {
            if (! $user->hasPermissionTo($permission)) {
                abort(403, "Insufficient permissions. Required: {$permission}");
            }
        }

        return $next($request);
    }
}
