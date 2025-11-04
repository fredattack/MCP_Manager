<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\UserToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateMcpServerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Missing authorization token',
            ], 401);
        }

        $userToken = UserToken::where('token', $token)->first();

        if (! $userToken) {
            $this->logUnauthorizedAccess($request, 'Invalid token');

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid token',
            ], 401);
        }

        if ($userToken->expires_at && now()->isAfter($userToken->expires_at)) {
            $this->logUnauthorizedAccess($request, 'Token expired', $userToken->user_id);

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Token expired',
            ], 401);
        }

        $user = User::find($userToken->user_id);

        if (! $user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'User not found',
            ], 401);
        }

        $userToken->increment('usage_count');
        $userToken->update(['last_used_at' => now()]);

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('mcp_token', $userToken);

        return $next($request);
    }

    private function logUnauthorizedAccess(Request $request, string $reason, ?int $userId = null): void
    {
        UserActivityLog::create([
            'user_id' => $userId,
            'action' => 'mcp_unauthorized_access',
            'entity_type' => 'ApiRequest',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'reason' => $reason,
                'path' => $request->path(),
                'method' => $request->method(),
            ],
        ]);
    }
}
