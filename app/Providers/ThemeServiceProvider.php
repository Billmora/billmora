<?php

namespace App\Providers;

use App\Services\BillmoraService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Register default theme information, view namespaces, and Blade anonymous components
     * for each predefined type (admin, client, portal, email, invoice).
     *
     * This dynamically shares theme metadata with views, registers paths for Blade templates,
     * and auto-registers component aliases found under each theme’s components directory.
     *
     * @return void
     */
    public function boot(): void
    {
        $types = ['admin', 'client', 'portal', 'email', 'invoice'];

        foreach ($types as $type) {
            try {
                $activeTheme = BillmoraService::getActiveThemeModel($type);
                $provider = $activeTheme ? strtolower($activeTheme->provider) : 'moraine';
                
                $themeConfig = BillmoraService::getThemeConfig($type);
                
            } catch (\Exception $e) {
                $provider = 'moraine';
                $themeConfig = [];
            }

            $basePath = resource_path("themes/{$type}/{$provider}");
            $jsonPath = "{$basePath}/theme.json";

            $themeInfo = File::exists($jsonPath)
                ? json_decode(File::get($jsonPath), true)
                : [
                    'name' => ucfirst($provider),
                    'version' => '1.0.0',
                    'author' => 'Billmora',
                    'assets' => "/themes/{$type}/{$provider}",
                ];

            View::share("{$type}Theme", $themeInfo);
            
            View::share("{$type}ThemeConfig", $themeConfig);

            $viewPath = "{$basePath}/views";
            if (File::exists($viewPath)) {
                View::addNamespace($type, $viewPath);
            }

            $componentPath = "{$basePath}/views/components";
            if (File::exists($componentPath)) {
                Blade::anonymousComponentPath($componentPath, $type);

                collect(File::allFiles($componentPath))->each(function ($file) use ($componentPath, $type) {
                    $relativePath = Str::of($file->getRealPath())
                        ->replace('\\', '/')
                        ->after(Str::of($componentPath)->replace('\\', '/') . '/')
                        ->before('.blade.php')
                        ->replace('/', '.');

                    $view  = "{$type}::components.{$relativePath}";
                    $alias = "{$type}.{$relativePath}";

                    Blade::component($view, $alias);
                });
            }
        }
    }
}
