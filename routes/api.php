<?php

use App\Http\Controllers\IntegrationsController;
use App\Http\Controllers\NotionController;
use App\Http\Controllers\NotionIntegrationController;
use Illuminate\Support\Facades\Route;

// Legacy route - will be deprecated
Route::get('notion/fetch', [NotionController::class, 'fetch']);

// Integration management routes
Route::middleware(['auth:web'])->group(function () {
    Route::apiResource('integrations', IntegrationsController::class);

    // Notion integration routes
    Route::prefix('notion')->middleware(['has.notion'])->group(function () {
        Route::get('pages-tree', [NotionIntegrationController::class, 'pagesTree']);
        Route::get('databases', [NotionIntegrationController::class, 'databases']);
        Route::get('page/{pageId}', [NotionIntegrationController::class, 'page']);
        Route::get('blocks/{pageId}', [NotionIntegrationController::class, 'blocks']);
    });

});
