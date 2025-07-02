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

    Route::get('integrations/google', [App\Http\Controllers\GoogleIntegrationController::class, 'index'])->name('integrations.google');
    Route::get('integrations/google/callback', [App\Http\Controllers\GoogleIntegrationController::class, 'callback'])->name('integrations.google.callback');
    Route::get('integrations/google-setup', [App\Http\Controllers\GoogleIntegrationController::class, 'setup'])->name('integrations.google-setup');
    Route::get('integrations/google/{service}/connect', [App\Http\Controllers\GoogleIntegrationController::class, 'connect'])->name('integrations.google.connect');
    Route::post('integrations/google/{service}/disconnect', [App\Http\Controllers\GoogleIntegrationController::class, 'disconnect'])->name('integrations.google.disconnect');

    Route::get('integrations/todoist', function () {
        return Inertia::render('integrations/todoist');
    })->name('integrations.todoist');

    Route::get('notion', function () {
        return Inertia::render('notion');
    })->name('notion');

    Route::get('ai/claude-chat', function () {
        return Inertia::render('ai/claude-chat');
    })->name('ai.claude-chat');

    Route::get('ai/natural-language', function () {
        return Inertia::render('ai/natural-language');
    })->name('ai.natural-language');

    // Gmail routes
    Route::prefix('gmail')->middleware('has.integration:gmail')->group(function () {
        Route::get('/', [App\Http\Controllers\GmailController::class, 'index'])->name('gmail.index');
        Route::get('/{messageId}', [App\Http\Controllers\GmailController::class, 'show'])->name('gmail.show');
        Route::post('/send', [App\Http\Controllers\GmailController::class, 'send'])->name('gmail.send');
        Route::post('/search', [App\Http\Controllers\GmailController::class, 'search'])->name('gmail.search');
        Route::post('/{messageId}/labels', [App\Http\Controllers\GmailController::class, 'modifyLabels'])->name('gmail.labels');
    });

    // Calendar routes
    Route::prefix('calendar')->middleware('has.integration:calendar')->group(function () {
        Route::get('/', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/events', [App\Http\Controllers\CalendarController::class, 'events'])->name('calendar.events');
        Route::post('/events', [App\Http\Controllers\CalendarController::class, 'store'])->name('calendar.events.store');
        Route::put('/events/{eventId}', [App\Http\Controllers\CalendarController::class, 'update'])->name('calendar.events.update');
        Route::delete('/events/{eventId}', [App\Http\Controllers\CalendarController::class, 'destroy'])->name('calendar.events.destroy');
        Route::post('/events/conflicts', [App\Http\Controllers\CalendarController::class, 'checkConflicts'])->name('calendar.conflicts');
        Route::get('/events/week', [App\Http\Controllers\CalendarController::class, 'weekEvents'])->name('calendar.week');
    });
    
    // Natural Language API routes
    Route::prefix('api/natural-language')->group(function () {
        Route::post('command', [App\Http\Controllers\NaturalLanguageController::class, 'processCommand']);
        Route::get('suggestions', [App\Http\Controllers\NaturalLanguageController::class, 'getSuggestions']);
        Route::get('history', [App\Http\Controllers\NaturalLanguageController::class, 'getCommandHistory']);
    });
    
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
