<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401, 'Unauthenticated');
        }

        $allowedRoles = array_map(
            static fn (string $role) => UserRole::from($role),
            $roles
        );

        if (! in_array($user->role, $allowedRoles, true)) {
            abort(403, 'Insufficient permissions. Required role: '.implode(', ', array_map(
                static fn (UserRole $role) => $role->label(),
                $allowedRoles
            )));
        }

        return $next($request);
    }
}
