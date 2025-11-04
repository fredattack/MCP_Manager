<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\McpServer\McpUserSyncService;
use Illuminate\Support\Facades\Cache;

// Clear cache
Cache::forget('mcp_circuit_breaker_failures');
Cache::forget('mcp_circuit_breaker_last_attempt');
Cache::forget('mcp_service_token');
Cache::forget('mcp_service_token_expires');

$syncService = app(McpUserSyncService::class);
$users = User::all();

$results = [
    'success' => 0,
    'error' => 0,
    'skipped' => 0,
];

echo "Starting sync of {$users->count()} users...\n";
echo str_repeat('=', 50)."\n";

foreach ($users as $index => $user) {
    echo '['.($index + 1)."/{$users->count()}] Syncing {$user->email}... ";

    try {
        $result = $syncService->syncUser($user, 'create');

        if ($result) {
            echo "✓ SUCCESS\n";
            $results['success']++;
        } else {
            echo "✗ FAILED\n";
            $results['error']++;
        }
    } catch (\Exception $e) {
        echo '✗ ERROR: '.substr($e->getMessage(), 0, 100)."\n";
        $results['error']++;
    }

    // Wait 3 seconds between each user to avoid rate limiting
    if ($index < $users->count() - 1) {
        sleep(3);
    }
}

echo str_repeat('=', 50)."\n";
echo "Sync complete!\n";
echo "Success: {$results['success']}\n";
echo "Errors: {$results['error']}\n";
echo "Skipped: {$results['skipped']}\n";
