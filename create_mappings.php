<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\McpServerUser;
use App\Services\McpServer\McpServerClient;

$client = app(McpServerClient::class);

// Get ALL users from MCP Server (paginated)
$allMcpUsers = [];
$page = 1;

do {
    $response = $client->get('/admin/users', ['per_page' => 100, 'page' => $page]);
    $allMcpUsers = array_merge($allMcpUsers, $response['users'] ?? []);
    $page++;
} while (count($response['users'] ?? []) > 0 && count($allMcpUsers) < ($response['total'] ?? 0));

echo "Found " . count($allMcpUsers) . " users in MCP Server\n";
echo str_repeat('=', 50) . "\n";

// Create mappings for each Laravel user
$users = User::all();
$results = [
    'created' => 0,
    'updated' => 0,
    'not_found' => 0,
];

foreach ($users as $user) {
    // Find matching MCP user by email
    $mcpUser = collect($allMcpUsers)->firstWhere('email', $user->email);

    if ($mcpUser) {
        // Create or update mapping
        $mcpServerUser = McpServerUser::updateOrCreate(
            ['user_id' => $user->id],
            [
                'mcp_user_uuid' => $mcpUser['uuid'],
                'mcp_user_id' => $mcpUser['id'],
                'sync_status' => 'synced',
                'last_sync_at' => now(),
                'sync_error' => null,
                'sync_attempts' => 0,
            ]
        );

        if ($mcpServerUser->wasRecentlyCreated) {
            echo "✓ Created mapping for {$user->email} -> MCP ID {$mcpUser['id']}\n";
            $results['created']++;
        } else {
            echo "↻ Updated mapping for {$user->email} -> MCP ID {$mcpUser['id']}\n";
            $results['updated']++;
        }
    } else {
        echo "✗ No MCP user found for {$user->email}\n";
        $results['not_found']++;
    }
}

echo str_repeat('=', 50) . "\n";
echo "Mapping complete!\n";
echo "Created: {$results['created']}\n";
echo "Updated: {$results['updated']}\n";
echo "Not found: {$results['not_found']}\n";
