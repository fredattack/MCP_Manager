<?php

use Illuminate\Support\Facades\Route;
use App\Actions\DailyPlanning\CreateDailyPlanningAction;

Route::get('/test-planning', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    $action = app(CreateDailyPlanningAction::class);
    $result = $action->handle($user, []);
    
    return response()->json([
        'raw_result' => $result->toArray(),
        'has_planning' => isset($result->toArray()['data']['planning']),
        'structure' => array_keys($result->toArray()['data'] ?? []),
    ]);
})->middleware('auth');