<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GitConnection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GitConnectionsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $connections = GitConnection::query()
            ->where('user_id', $user->id)
            ->get()
            ->map(function (GitConnection $connection) {
                return [
                    'id' => $connection->id,
                    'provider' => $connection->provider->value,
                    'external_user_id' => $connection->external_user_id,
                    'username' => $connection->meta['username'] ?? null,
                    'email' => $connection->meta['email'] ?? null,
                    'avatar_url' => $connection->meta['avatar_url'] ?? null,
                    'scopes' => $connection->scopes,
                    'status' => $connection->status->value,
                    'expires_at' => $connection->expires_at?->toIso8601String(),
                    'created_at' => $connection->created_at->toIso8601String(),
                ];
            })
            ->toArray();

        return Inertia::render('git/connections', [
            'connections' => $connections,
        ]);
    }
}
