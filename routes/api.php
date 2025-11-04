<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\Api\GitCloneController;
use App\Http\Controllers\Api\GitOAuthController;
use App\Http\Controllers\Api\GitRepositoryController;
use App\Http\Controllers\Api\WorkflowController;
use App\Http\Controllers\IntegrationsController;
use App\Http\Controllers\JiraController;
use App\Http\Controllers\McpIntegrationController;
use App\Http\Controllers\NotionController;
use App\Http\Controllers\NotionIntegrationController;
use App\Http\Controllers\SystemHealthController;
use Illuminate\Support\Facades\Route;

// System health endpoint
Route::get('system/health', [SystemHealthController::class, 'index']);

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

    // Git Provider OAuth routes
    Route::prefix('git/{provider}/oauth')->group(function () {
        Route::post('start', [GitOAuthController::class, 'start'])->name('api.git.oauth.start');
        Route::get('callback', [GitOAuthController::class, 'callback'])->name('api.git.oauth.callback');
    });

    // Git Repository routes
    Route::prefix('git/{provider}/repos')->group(function () {
        Route::post('sync', [GitRepositoryController::class, 'sync'])->name('api.git.repos.sync');
        Route::get('stats', [GitRepositoryController::class, 'stats'])->name('api.git.repos.stats');
        Route::get('/', [GitRepositoryController::class, 'index'])->name('api.git.repos.index');
        Route::get('{externalId}', [GitRepositoryController::class, 'show'])->name('api.git.repos.show');
        Route::post('{externalId}/refresh', [GitRepositoryController::class, 'refresh'])->name('api.git.repos.refresh');

        // Clone routes
        Route::post('{externalId}/clone', [GitCloneController::class, 'clone'])->name('api.git.repos.clone');
        Route::get('{externalId}/clones', [GitCloneController::class, 'index'])->name('api.git.repos.clones');
    });

    // Git Clone routes
    Route::prefix('git/clones')->group(function () {
        Route::get('{cloneId}', [GitCloneController::class, 'show'])->name('api.git.clones.show');
    });

    // Workflow routes
    Route::apiResource('workflows', WorkflowController::class);
    Route::post('workflows/{workflow}/execute', [WorkflowController::class, 'execute'])->name('api.workflows.execute');
    Route::post('workflows/{workflow}/rerun', [WorkflowController::class, 'rerun'])->name('api.workflows.rerun');
    Route::post('workflows/{workflow}/cancel', [WorkflowController::class, 'cancel'])->name('api.workflows.cancel');
    Route::get('workflows/executions/{execution}', [WorkflowController::class, 'executionStatus'])->name('api.workflows.executions.status');
    Route::get('workflows/executions/{execution}/steps', [WorkflowController::class, 'executionSteps'])->name('api.workflows.executions.steps');

});

// MCP Server API routes (no auth:web, uses Bearer token via mcp.token middleware)
Route::prefix('mcp')->group(function () {
    // Protected endpoints (require MCP server Bearer token)
    Route::middleware(['mcp.token'])->group(function () {
        // Get authenticated user info
        Route::get('me', \App\Http\Controllers\Api\Mcp\GetAuthenticatedUserController::class)
            ->name('api.mcp.me');

        // Credential Lease management
        Route::post('credentials/lease', \App\Http\Controllers\Api\Mcp\CreateCredentialLeaseController::class)
            ->name('api.mcp.lease.create');

        Route::get('credentials/lease/{leaseId}', \App\Http\Controllers\Api\Mcp\ShowCredentialLeaseController::class)
            ->name('api.mcp.lease.show');

        Route::post('credentials/lease/{leaseId}/renew', \App\Http\Controllers\Api\Mcp\RenewCredentialLeaseController::class)
            ->name('api.mcp.lease.renew');

        Route::delete('credentials/lease/{leaseId}', \App\Http\Controllers\Api\Mcp\RevokeCredentialLeaseController::class)
            ->name('api.mcp.lease.revoke');

        // Convenience endpoint to get user credentials
        Route::get('users/{userId}/credentials', \App\Http\Controllers\Api\Mcp\GetUserCredentialsController::class)
            ->name('api.mcp.users.credentials');
    });
});
