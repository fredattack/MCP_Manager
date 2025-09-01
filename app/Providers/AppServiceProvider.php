<?php

namespace App\Providers;

use App\Services\MockMcpServerService;
use App\Services\CryptoService;
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
            return new CryptoService();
        });

        // Register MockMcpServerService as singleton
        $this->app->singleton(MockMcpServerService::class, function ($app) {
            return new MockMcpServerService($app->make(CryptoService::class));
        });
        
        // Register McpConnectionService as singleton
        $this->app->singleton(\App\Services\McpConnectionService::class, function ($app) {
            return new \App\Services\McpConnectionService();
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
        //
    }
}
