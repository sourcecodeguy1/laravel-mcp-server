<?php

namespace Sourcecodeguy1\LaravelMcp;

use Illuminate\Support\ServiceProvider;
use Sourcecodeguy1\LaravelMcp\Commands\McpServeCommand;

class LaravelMcpServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([McpServeCommand::class]);

            $this->publishes([
                __DIR__ . '/../config/laravel-mcp.php' => config_path('laravel-mcp.php'),
            ], 'laravel-mcp-config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-mcp.php', 'laravel-mcp');
    }
}
