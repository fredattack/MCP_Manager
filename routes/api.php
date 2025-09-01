<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\IntegrationsController;
use App\Http\Controllers\JiraController;
use App\Http\Controllers\NotionController;
use App\Http\Controllers\NotionIntegrationController;
use App\Http\Controllers\McpIntegrationController;
use Illuminate\Support\Facades\Route;

// Legacy route - will be deprecated
Route::get('notion/fetch', [NotionController::class, 'fetch']);

// AI Chat API routes (protected with auth)
Route::middleware(['auth:web'])->prefix('ai')->group(function () {
    Route::post('chat', [AiChatController::class, 'chat']);
});

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

    // JIRA integration routes
    Route::prefix('jira')->group(function () {
        // Projects
        Route::get('projects', [JiraController::class, 'listProjects']);
        Route::get('projects/{projectKey}', [JiraController::class, 'getProject']);
        
        // Boards
        Route::get('boards', [JiraController::class, 'listBoards']);
        Route::get('boards/{boardId}', [JiraController::class, 'getBoard']);
        Route::get('boards/{boardId}/issues', [JiraController::class, 'listBoardIssues']);
        
        // Issues
        Route::get('issues/search', [JiraController::class, 'searchIssues']);
        Route::get('issues/{issueKey}', [JiraController::class, 'getIssue']);
        Route::post('issues', [JiraController::class, 'createIssue']);
        Route::put('issues/{issueKey}', [JiraController::class, 'updateIssue']);
        Route::post('issues/{issueKey}/transitions', [JiraController::class, 'transitionIssue']);
        Route::get('issues/{issueKey}/transitions', [JiraController::class, 'getTransitions']);
        Route::put('issues/{issueKey}/assign', [JiraController::class, 'assignIssue']);
        
        // Epics
        Route::post('epics', [JiraController::class, 'createEpic']);
        Route::get('epics/{epicKey}/progress', [JiraController::class, 'getEpicProgress']);
        Route::get('epics/{epicKey}/issues', [JiraController::class, 'getEpicIssues']);
        
        // Sprints
        Route::get('boards/{boardId}/sprints', [JiraController::class, 'listSprints']);
        Route::get('sprints/{sprintId}', [JiraController::class, 'getSprint']);
        Route::post('sprints/{sprintId}/start', [JiraController::class, 'startSprint']);
        Route::post('sprints/{sprintId}/complete', [JiraController::class, 'completeSprint']);
        Route::get('sprints/{sprintId}/velocity', [JiraController::class, 'getSprintVelocity']);
        
        // Cross-service
        Route::post('issues/from-sentry', [JiraController::class, 'createFromSentry']);
    });

    // MCP Integration API routes
    Route::prefix('mcp')->group(function () {
        Route::get('integrations/status', [McpIntegrationController::class, 'status']);
        Route::post('integrations/{service}/test', [McpIntegrationController::class, 'test']);
        Route::post('integrations/{service}/toggle', [McpIntegrationController::class, 'toggle']);
    });

});
