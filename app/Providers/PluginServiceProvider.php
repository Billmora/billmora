<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PluginManager;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register plugin services into the application container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(PluginManager::class, function ($app) {
            return new PluginManager();
        });
    }

    /**
     * Bootstrap plugin services and auto-load active plugins.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}