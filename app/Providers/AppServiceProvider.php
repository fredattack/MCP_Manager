<?php

namespace App\Providers;

use App\Services\CryptoService;
use App\Services\MockMcpServerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register CryptoService as singleton
        $this->app->singleton(CryptoService::class, function ($app) {
            return new CryptoService;
        });

        // Register MockMcpServerService as singleton
        $this->app->singleton(MockMcpServerService::class, function ($app) {
            return new MockMcpServerService($app->make(CryptoService::class));
        });

        // Register McpConnectionService as singleton
        $this->app->singleton(\App\Services\McpConnectionService::class, function ($app) {
            return new \App\Services\McpConnectionService;
        });

        // Register RealMcpServerManager as singleton
        $this->app->singleton(\App\Services\RealMcpServerManager::class, function ($app) {
            return new \App\Services\RealMcpServerManager(
                $app->make(\App\Services\McpConnectionService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register User Observer for automatic MCP sync
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\UserCreatedInManager::class,
            \App\Listeners\SyncUserToMcp::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\UserUpdatedInManager::class,
            \App\Listeners\SyncUserToMcp::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\UserDeletedInManager::class,
            \App\Listeners\SyncUserToMcp::class
        );
    }
}
