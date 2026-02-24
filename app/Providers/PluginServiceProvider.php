<?php

namespace App\Providers;

use App\Contracts\ModuleInterface;
use App\Models\Plugin;
use Illuminate\Support\ServiceProvider;
use App\Services\PluginManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

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
        if ($this->app->runningInConsole() && !Schema::hasTable('plugins')) {
            return;
        }

        try {
            $pluginManager = $this->app->make(PluginManager::class);
            
            $activePlugins = Plugin::where('is_active', true)->get();

            foreach ($activePlugins as $pluginRecord) {
                $instance = $pluginManager->bootInstance($pluginRecord);

                if ($instance) {
                    $this->app->register($instance);

                    if ($instance instanceof ModuleInterface) {
                        $subscribedEvents = $instance->getSubscribedEvents();

                        foreach ($subscribedEvents as $eventClass => $methodName) {
                            Event::listen($eventClass, [$instance, $methodName]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to boot plugins: " . $e->getMessage());
        }
    }
}