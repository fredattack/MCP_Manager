<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RequirePermission;
use App\Http\Middleware\RequireRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            // Postman-friendly API routes (web middleware for session, CSRF already excluded via api/*)
            Route::middleware('web')
                ->prefix('api')
                ->group(base_path('routes/postman-api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Exclude Postman routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'api/postman/*',
        ]);

        $middleware->alias([
            'has.notion' => \App\Http\Middleware\HasActiveNotionIntegration::class,
            'has.integration' => \App\Http\Middleware\HasActiveIntegration::class,
            'role' => RequireRole::class,
            'permission' => RequirePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
