<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('integrations', function () {
        return Inertia::render('integrations');
    })->name('integrations');

    Route::get('integrations/todoist', function () {
        return Inertia::render('integrations/todoist');
    })->name('integrations.todoist');

    Route::get('notion', function () {
        return Inertia::render('notion');
    })->name('notion');
    
    // MCP Proxy routes
    Route::prefix('api/mcp')->group(function () {
        Route::post('auth/login', [App\Http\Controllers\McpProxyController::class, 'login']);
        Route::get('auth/me', [App\Http\Controllers\McpProxyController::class, 'me']);
        
        // Specific Todoist endpoints
        Route::get('todoist/tasks/today', [App\Http\Controllers\McpProxyController::class, 'getTodayTasks']);
        Route::get('todoist/tasks/upcoming', [App\Http\Controllers\McpProxyController::class, 'getUpcomingTasks']);
        
        // General Todoist proxy
        Route::any('todoist/{path?}', [App\Http\Controllers\McpProxyController::class, 'todoistProxy'])
            ->where('path', '.*');
    });
    
    // Todoist API routes (fallback mock)
    Route::prefix('api/integrations/todoist')->group(function () {
        Route::get('projects', fn() => response()->json([
            ['id' => '1', 'name' => 'Work', 'color' => 'blue'],
            ['id' => '2', 'name' => 'Personal', 'color' => 'green'],
        ]));
        
        Route::get('tasks', fn() => response()->json([
            [
                'id' => '1',
                'content' => 'Complete MCP Manager frontend',
                'completed' => false,
                'priority' => 1,
                'project_id' => '1',
                'labels' => ['urgent', 'development'],
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ],
            [
                'id' => '2',
                'content' => 'Review pull requests',
                'completed' => false,
                'priority' => 2,
                'project_id' => '1',
                'labels' => ['code-review'],
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ],
        ]));
        
        Route::post('tasks', fn() => response()->json([
            'id' => '3',
            'content' => request('content'),
            'completed' => false,
            'priority' => request('priority', 4),
            'project_id' => request('project_id'),
            'labels' => request('labels', []),
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ]));
        
        Route::put('tasks/{id}', fn($id) => response()->json(['id' => $id, 'updated' => true]));
        Route::delete('tasks/{id}', fn() => response()->noContent());
        Route::post('tasks/{id}/complete', fn() => response()->noContent());
        Route::post('tasks/{id}/uncomplete', fn() => response()->noContent());
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
