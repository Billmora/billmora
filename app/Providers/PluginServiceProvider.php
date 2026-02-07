<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $pluginPath = base_path('plugin');

        if (!File::exists($pluginPath)) return;

        spl_autoload_register(function ($class) use ($pluginPath) {
            if (str_starts_with($class, 'Plugin\\')) {
                $relativeClass = str_replace('Plugin\\', '', $class);
                $file = $pluginPath . '/' . str_replace('\\', '/', $relativeClass) . '.php';

                if (file_exists($file)) {
                    require $file;
                }
            }
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
